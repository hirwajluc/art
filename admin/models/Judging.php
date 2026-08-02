<?php
require_once 'config/database.php';

/**
 * Judging model — all read/write operations for the anonymous judging system.
 * Reads registration/submission tables (never writes to them).
 * Manages: jury_criteria, jury_evaluations, jury_scores, admin_users (jury role).
 */
class Judging {

    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // ── Scoring Criteria ──────────────────────────────────────────────────────

    /** Return all active criteria ordered by display_order. */
    public function getCriteria(bool $activeOnly = true): array {
        try {
            $where = $activeOnly ? 'WHERE is_active = 1' : '';
            $stmt  = $this->db->query("SELECT * FROM jury_criteria $where ORDER BY display_order ASC, id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Judging::getCriteria — " . $e->getMessage());
            return [];
        }
    }

    /** Upsert a full set of criteria from the admin form. */
    public function saveCriteria(array $criteria, int $adminId): bool {
        try {
            $this->db->beginTransaction();

            // Deactivate all first, then re-activate/insert what was submitted
            $this->db->exec("UPDATE jury_criteria SET is_active = 0");

            $upsert = $this->db->prepare("
                INSERT INTO jury_criteria (id, name, description, max_score, display_order, is_active, created_by)
                VALUES (:id, :name, :desc, :max, :ord, 1, :by)
                ON DUPLICATE KEY UPDATE
                    name          = VALUES(name),
                    description   = VALUES(description),
                    max_score     = VALUES(max_score),
                    display_order = VALUES(display_order),
                    is_active     = 1
            ");

            foreach ($criteria as $i => $c) {
                $upsert->execute([
                    ':id'   => !empty($c['id']) ? (int)$c['id'] : null,
                    ':name' => trim($c['name']),
                    ':desc' => trim($c['description'] ?? ''),
                    ':max'  => max(1, min(100, (int)($c['max_score'] ?? 20))),
                    ':ord'  => $i + 1,
                    ':by'   => $adminId,
                ]);
            }

            $this->db->commit();
            $this->logActivity($adminId, 'CRITERIA_UPDATED', 'Scoring criteria updated');
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Judging::saveCriteria — " . $e->getMessage());
            return false;
        }
    }

    /** Sum of max_score across all active criteria. */
    public function getTotalMaxScore(): int {
        try {
            $r = $this->db->query("SELECT COALESCE(SUM(max_score),100) FROM jury_criteria WHERE is_active = 1")->fetchColumn();
            return (int)$r;
        } catch (PDOException $e) {
            return 100;
        }
    }

    // ── Judge (admin_users with role='jury') Management ───────────────────────

    public function getJudges(): array {
        // Exclude soft-deleted entries (username contains _deleted_)
        $notDeleted = "u.username NOT LIKE '%\_deleted\_%'";

        try {
            $stmt = $this->db->query("
                SELECT u.id, u.username, u.email, u.full_name, u.status, u.created_at,
                       u.password_setup_token, u.token_expires_at,
                       COUNT(DISTINCT je.submission_id)                           AS evaluated_count,
                       SUM(CASE WHEN je.status = 'submitted' THEN 1 ELSE 0 END)  AS submitted_count
                FROM admin_users u
                LEFT JOIN jury_evaluations je ON je.judge_id = u.id
                WHERE u.role = 'jury' AND $notDeleted
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { /* jury_evaluations may not exist yet */ }

        try {
            $stmt = $this->db->query("
                SELECT id, username, email, full_name, status, created_at,
                       password_setup_token, token_expires_at,
                       0 AS evaluated_count, 0 AS submitted_count
                FROM admin_users
                WHERE role = 'jury' AND username NOT LIKE '%\_deleted\_%'
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { /* token columns may not exist yet */ }

        try {
            $stmt = $this->db->query("
                SELECT id, username, email, full_name, status, created_at,
                       NULL AS password_setup_token, NULL AS token_expires_at,
                       0 AS evaluated_count, 0 AS submitted_count
                FROM admin_users
                WHERE role = 'jury' AND username NOT LIKE '%\_deleted\_%'
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Judging::getJudges — " . $e->getMessage());
            return [];
        }
    }

    public function getJudgeById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, full_name, status FROM admin_users WHERE id = ? AND role = 'jury'");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function countActiveJudges(): int {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM admin_users WHERE role='jury' AND status='active'")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function createJudge(string $username, string $email, string $fullName, int $adminId, string $baseUrl = ''): array {
        try {
            // Ensure token columns exist (added in migration step 6)
            try {
                $cols = $this->db->query("SHOW COLUMNS FROM admin_users LIKE 'password_setup_token'")->fetchAll();
                if (empty($cols)) {
                    $this->db->exec("ALTER TABLE admin_users ADD COLUMN password_setup_token VARCHAR(64) NULL, ADD COLUMN token_expires_at DATETIME NULL");
                }
            } catch (PDOException $e) { /* already exists or no permission — continue */ }

            // Exclude soft-deleted remnants from the uniqueness check
            $stmt = $this->db->prepare("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND username NOT LIKE '%\_deleted\_%'");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists.'];
            }

            // Hard-delete any leftover soft-deleted records with the same email so they don't accumulate
            $this->db->prepare("DELETE FROM admin_users WHERE email = ? AND username LIKE '%\_deleted\_%'")->execute([$email]);

            // Generate setup token (48-hour expiry) — placeholder password until judge sets one
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 48 * 3600);
            $placeholder = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO admin_users (username, email, password, full_name, role, status, password_setup_token, token_expires_at)
                VALUES (?, ?, ?, ?, 'jury', 'active', ?, ?)
            ");
            $stmt->execute([$username, $email, $placeholder, $fullName, $token, $expires]);

            // Send invitation email via SMTP (Aruba smtp.aruba.it)
            $setupLink = rtrim($baseUrl, '/') . '/?page=set_password&token=' . $token;
            $subject   = 'You have been invited as a judge — GREATER Art Competition';
            $htmlBody  = $this->buildJudgeInviteHtml($fullName, $username, $setupLink);

            $emailSent  = false;
            $emailError = '';
            try {
                require_once __DIR__ . '/../helpers/Mailer.php';
                $result = (new Mailer())->send($email, $fullName, $subject, $htmlBody);
                if ($result === true) {
                    $emailSent = true;
                } else {
                    $emailError = $result;
                    error_log("[Judging::createJudge] Email failed: $emailError");
                }
            } catch (Exception $me) {
                $emailError = $me->getMessage();
                error_log("[Judging::createJudge] Mailer exception: $emailError");
            }

            $this->logActivity($adminId, 'JUDGE_CREATED', "Created judge account: $username — invite sent to $email");
            $msg = $emailSent
                 ? "Judge account created. An invitation email has been sent to {$email}."
                 : "Judge account created. Email could not be sent ({$emailError}) — share the invite link below manually.";
            return ['success' => true, 'message' => $msg, 'token' => $token];
        } catch (PDOException $e) {
            error_log("Judging::createJudge — " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error creating judge.'];
        }
    }

    public function updateJudge(int $id, string $fullName, string $email, int $adminId): array {
        try {
            $stmt = $this->db->prepare("SELECT id FROM admin_users WHERE (email = ?) AND id != ? AND role = 'jury'");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already in use by another judge.'];
            }
            $stmt = $this->db->prepare("UPDATE admin_users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ? AND role = 'jury'");
            $stmt->execute([$fullName, $email, $id]);
            $this->logActivity($adminId, 'JUDGE_UPDATED', "Updated judge ID: $id");
            return ['success' => true, 'message' => 'Judge updated successfully.'];
        } catch (PDOException $e) {
            error_log("Judging::updateJudge — " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error updating judge.'];
        }
    }

    public function toggleJudgeStatus(int $id, int $adminId): array {
        try {
            $stmt = $this->db->prepare("SELECT status, username FROM admin_users WHERE id = ? AND role = 'jury'");
            $stmt->execute([$id]);
            $judge = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$judge) return ['success' => false, 'message' => 'Judge not found.'];
            $newStatus = $judge['status'] === 'active' ? 'inactive' : 'active';
            $this->db->prepare("UPDATE admin_users SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $id]);
            $this->logActivity($adminId, 'JUDGE_STATUS_CHANGED', "Judge {$judge['username']} set to $newStatus");
            return ['success' => true, 'message' => "Judge set to $newStatus."];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error.'];
        }
    }

    public function resetJudgePassword(int $id, string $newPassword, int $adminId): array {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt   = $this->db->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ? AND role = 'jury'");
            $stmt->execute([$hashed, $id]);
            $this->logActivity($adminId, 'JUDGE_PASSWORD_RESET', "Reset password for judge ID: $id");
            return ['success' => true, 'message' => 'Password reset successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error resetting password.'];
        }
    }

    public function deleteJudge(int $id, int $adminId): array {
        try {
            $stmt = $this->db->prepare("SELECT username FROM admin_users WHERE id = ? AND role = 'jury'");
            $stmt->execute([$id]);
            $judge = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$judge) return ['success' => false, 'message' => 'Judge not found.'];

            // Delete child records first to avoid FK constraint violations
            try {
                $this->db->prepare("DELETE FROM jury_scores WHERE evaluation_id IN (SELECT id FROM jury_evaluations WHERE judge_id = ?)")->execute([$id]);
            } catch (PDOException $e) { /* table may not exist yet */ }
            try {
                $this->db->prepare("DELETE FROM jury_evaluations WHERE judge_id = ?")->execute([$id]);
            } catch (PDOException $e) { /* table may not exist yet */ }

            // Now safe to delete the judge account
            $this->db->prepare("DELETE FROM admin_users WHERE id = ? AND role = 'jury'")->execute([$id]);

            $this->logActivity($adminId, 'JUDGE_DELETED', "Deleted judge: {$judge['username']}");
            return ['success' => true, 'message' => "Judge \"{$judge['username']}\" removed."];
        } catch (PDOException $e) {
            error_log("Judging::deleteJudge — " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ── Submissions for Judging (ANONYMOUS — no participant PII) ─────────────

    /**
     * Return all submissions visible to a judge, enriched with this judge's
     * evaluation status. NEVER returns participant name/email/phone.
     */
    public function getSubmissionsForJudge(int $judgeId, string $search = '', string $category = '', string $evalStatus = ''): array {
        try {
            $where  = ['1=1'];
            $params = [':judge_id' => $judgeId];

            if (!empty($search)) {
                $where[] = "(s.userCode LIKE :search OR s.artworkName LIKE :search)";
                $params[':search'] = "%$search%";
            }
            if (!empty($category)) {
                $where[] = "s.category = :category";
                $params[':category'] = $category;
            }

            $havingClause = '';
            if ($evalStatus === 'not_started') $havingClause = 'HAVING eval_status IS NULL';
            elseif ($evalStatus === 'draft')     $havingClause = "HAVING eval_status = 'draft'";
            elseif ($evalStatus === 'submitted') $havingClause = "HAVING eval_status = 'submitted'";

            $whereStr = implode(' AND ', $where);

            // Try with jury_evaluations JOIN first; fall back to submissions-only if table doesn't exist yet
            try {
                $sql = "
                    SELECT
                        s.id,
                        s.userCode        AS competition_code,
                        s.artworkName     AS artwork_name,
                        s.category,
                        s.fileType,
                        s.filePath,
                        s.fileName,
                        s.submissionDate,
                        je.id             AS evaluation_id,
                        je.status         AS eval_status,
                        je.total_score,
                        je.submitted_at
                    FROM submissions s
                    LEFT JOIN jury_evaluations je
                        ON je.submission_id = s.id AND je.judge_id = :judge_id
                    WHERE $whereStr
                    $havingClause
                    ORDER BY s.submissionDate ASC
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // jury_evaluations table not yet created — return submissions with null eval columns
                if (!empty($havingClause)) return []; // can't filter by eval status without the table
                unset($params[':judge_id']);
                $sql2 = "
                    SELECT
                        s.id,
                        s.userCode    AS competition_code,
                        s.artworkName AS artwork_name,
                        s.category,
                        s.fileType,
                        s.filePath,
                        s.fileName,
                        s.submissionDate,
                        NULL AS evaluation_id,
                        NULL AS eval_status,
                        NULL AS total_score,
                        NULL AS submitted_at
                    FROM submissions s
                    WHERE $whereStr
                    ORDER BY s.submissionDate ASC
                ";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute($params);
                return $stmt2->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Judging::getSubmissionsForJudge — " . $e->getMessage());
            return [];
        }
    }

    /**
     * Return a single submission's anonymous display data for the evaluate page.
     * NEVER returns participant name, email, or phone.
     */
    public function getAnonymousSubmission(int $submissionId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, userCode AS competition_code, artworkName AS artwork_name,
                       category, fileType, filePath, fileName, description, submissionDate,
                       jury_feedback AS admin_note
                FROM submissions WHERE id = ?
            ");
            $stmt->execute([$submissionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // ── Evaluations ───────────────────────────────────────────────────────────

    public function getEvaluation(int $submissionId, int $judgeId): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM jury_evaluations WHERE submission_id = ? AND judge_id = ?");
            $stmt->execute([$submissionId, $judgeId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getEvaluationById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM jury_evaluations WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /** Get all criterion scores for an evaluation. */
    public function getScores(int $evaluationId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT js.*, jc.name, jc.max_score, jc.display_order
                FROM jury_scores js
                JOIN jury_criteria jc ON jc.id = js.criterion_id
                WHERE js.evaluation_id = ?
                ORDER BY jc.display_order ASC
            ");
            $stmt->execute([$evaluationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Save draft or submit final evaluation.
     * $scores = ['criterion_id' => score_value, ...]
     */
    public function saveEvaluation(
        int    $submissionId,
        int    $judgeId,
        array  $scores,
        string $strengths,
        string $weaknesses,
        string $recommendations,
        string $overallComments,
        string $status   // 'draft' | 'submitted'
    ): array {
        try {
            // Block editing submitted evaluations (unless reopened)
            $existing = $this->getEvaluation($submissionId, $judgeId);
            if ($existing && $existing['status'] === 'submitted' && $status !== 'draft') {
                return ['success' => false, 'message' => 'This evaluation has been submitted and is now read-only.'];
            }

            // Validate scores against criteria
            $criteria   = $this->getCriteria();
            $totalScore = 0;
            foreach ($criteria as $c) {
                $s = isset($scores[$c['id']]) ? (int)$scores[$c['id']] : 0;
                if ($s < 0 || $s > $c['max_score']) {
                    return ['success' => false, 'message' => "Score for '{$c['name']}' must be 0–{$c['max_score']}."];
                }
                $totalScore += $s;
            }

            $this->db->beginTransaction();

            if ($existing) {
                // Update existing
                $stmt = $this->db->prepare("
                    UPDATE jury_evaluations SET
                        strengths = ?, weaknesses = ?, recommendations = ?,
                        overall_comments = ?, total_score = ?, status = ?,
                        submitted_at = CASE WHEN ? = 'submitted' THEN NOW() ELSE submitted_at END,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$strengths, $weaknesses, $recommendations, $overallComments, $totalScore, $status, $status, $existing['id']]);
                $evalId = $existing['id'];
            } else {
                // Insert new
                $stmt = $this->db->prepare("
                    INSERT INTO jury_evaluations
                        (submission_id, judge_id, strengths, weaknesses, recommendations,
                         overall_comments, total_score, status, submitted_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?,
                        CASE WHEN ? = 'submitted' THEN NOW() ELSE NULL END)
                ");
                $stmt->execute([$submissionId, $judgeId, $strengths, $weaknesses, $recommendations, $overallComments, $totalScore, $status, $status]);
                $evalId = (int)$this->db->lastInsertId();
            }

            // Upsert individual scores
            $upsertScore = $this->db->prepare("
                INSERT INTO jury_scores (evaluation_id, criterion_id, score)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = NOW()
            ");
            foreach ($criteria as $c) {
                $s = isset($scores[$c['id']]) ? (int)$scores[$c['id']] : 0;
                $upsertScore->execute([$evalId, $c['id'], $s]);
            }

            $this->db->commit();

            $action = $status === 'submitted' ? 'EVALUATION_SUBMITTED' : 'EVALUATION_DRAFT_SAVED';
            $this->logActivity($judgeId, $action, "Submission ID: $submissionId, Score: $totalScore");

            return [
                'success' => true,
                'message' => $status === 'submitted'
                    ? 'Evaluation submitted successfully. It is now read-only.'
                    : 'Draft saved. You can continue later.',
                'evaluation_id' => $evalId,
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Judging::saveEvaluation — " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error saving evaluation.'];
        }
    }

    /** Admin reopens a submitted evaluation so the judge can edit it again. */
    public function reopenEvaluation(int $evaluationId, int $adminId): array {
        try {
            $stmt = $this->db->prepare("UPDATE jury_evaluations SET status = 'draft', reopened_by = ?, reopened_at = NOW(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$adminId, $evaluationId]);
            $this->logActivity($adminId, 'EVALUATION_REOPENED', "Evaluation ID: $evaluationId reopened by admin $adminId");
            return ['success' => true, 'message' => 'Evaluation reopened for editing.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error.'];
        }
    }

    // ── Results ───────────────────────────────────────────────────────────────

    /**
     * Return all submissions with judging progress, average scores, and ranking.
     * Only submissions where ALL active judges have submitted are considered "complete".
     */
    public function getResults(string $category = '', string $search = ''): array {
        try {
            $activeJudges = $this->countActiveJudges();
            $where  = ['1=1'];
            $params = [];

            if (!empty($category)) {
                $where[] = "s.category = ?";
                $params[] = $category;
            }
            if (!empty($search)) {
                $where[] = "(s.userCode LIKE ? OR s.artworkName LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $whereStr = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT
                    s.id,
                    s.userCode     AS competition_code,
                    s.artworkName  AS artwork_name,
                    s.category,
                    s.filePath,
                    s.fileType,
                    COUNT(je.id)                                         AS judges_evaluated,
                    SUM(CASE WHEN je.status='submitted' THEN 1 ELSE 0 END) AS judges_submitted,
                    AVG(CASE WHEN je.status='submitted' THEN je.total_score END) AS avg_score,
                    MAX(CASE WHEN je.status='submitted' THEN je.total_score END) AS max_score,
                    MIN(CASE WHEN je.status='submitted' THEN je.total_score END) AS min_score
                FROM submissions s
                LEFT JOIN jury_evaluations je ON je.submission_id = s.id
                WHERE $whereStr
                GROUP BY s.id
                ORDER BY avg_score DESC, s.userCode ASC
            ");
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Assign ranks (only to complete artworks = all active judges submitted)
            $rank = 0;
            $prevScore = null;
            $tied = false;
            foreach ($rows as &$row) {
                $row['is_complete']     = ($activeJudges > 0 && (int)$row['judges_submitted'] >= $activeJudges);
                $row['active_judges']   = $activeJudges;
                $row['avg_score']       = $row['avg_score'] !== null ? round((float)$row['avg_score'], 2) : null;
                if ($row['is_complete'] && $row['avg_score'] !== null) {
                    if ($prevScore === null || $row['avg_score'] < $prevScore) {
                        $rank++;
                        $tied = false;
                    } else {
                        $tied = true; // same score as previous — tied
                    }
                    $row['rank'] = $rank;
                    $row['tied'] = $tied;
                    $prevScore   = $row['avg_score'];
                } else {
                    $row['rank'] = null;
                    $row['tied'] = false;
                }
            }
            unset($row);
            return $rows;
        } catch (PDOException $e) {
            error_log("Judging::getResults — " . $e->getMessage());
            return [];
        }
    }

    /** Full per-judge breakdown for one submission. */
    public function getSubmissionResultDetail(int $submissionId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    je.id, je.judge_id, je.status, je.total_score, je.submitted_at,
                    je.strengths, je.weaknesses, je.recommendations, je.overall_comments,
                    je.reopened_by, je.reopened_at,
                    u.full_name AS judge_name
                FROM jury_evaluations je
                JOIN admin_users u ON u.id = je.judge_id
                WHERE je.submission_id = ?
                ORDER BY je.submitted_at ASC, u.full_name ASC
            ");
            $stmt->execute([$submissionId]);
            $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Attach criterion scores to each evaluation
            foreach ($evaluations as &$eval) {
                $eval['scores'] = $this->getScores($eval['id']);
            }
            unset($eval);
            return $evaluations;
        } catch (PDOException $e) {
            error_log("Judging::getSubmissionResultDetail — " . $e->getMessage());
            return [];
        }
    }

    /** Per-criterion averages across all submitted evaluations for a submission. */
    public function getCriterionAverages(int $submissionId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT jc.name, jc.max_score, jc.display_order,
                       AVG(js.score) AS avg_score,
                       COUNT(js.id)  AS judge_count
                FROM jury_criteria jc
                LEFT JOIN jury_scores js ON js.criterion_id = jc.id
                LEFT JOIN jury_evaluations je ON je.id = js.evaluation_id
                    AND je.submission_id = ? AND je.status = 'submitted'
                WHERE jc.is_active = 1
                GROUP BY jc.id
                ORDER BY jc.display_order ASC
            ");
            $stmt->execute([$submissionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // ── Dashboard Stats ───────────────────────────────────────────────────────

    public function getJudgeDashboardStats(int $judgeId): array {
        try {
            $total     = (int)$this->db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
            $submitted = (int)$this->db->prepare("SELECT COUNT(*) FROM jury_evaluations WHERE judge_id = ? AND status = 'submitted'")->execute([$judgeId]) ? (int)$this->db->query("SELECT COUNT(*) FROM jury_evaluations WHERE judge_id = $judgeId AND status = 'submitted'")->fetchColumn() : 0;
            $drafted   = (int)$this->db->query("SELECT COUNT(*) FROM jury_evaluations WHERE judge_id = $judgeId AND status = 'draft'")->fetchColumn();
            return [
                'total'      => $total,
                'submitted'  => $submitted,
                'drafted'    => $drafted,
                'pending'    => max(0, $total - $submitted - $drafted),
                'pct'        => $total > 0 ? round(($submitted / $total) * 100) : 0,
            ];
        } catch (PDOException $e) {
            return ['total' => 0, 'submitted' => 0, 'drafted' => 0, 'pending' => 0, 'pct' => 0];
        }
    }

    /** Proper version using prepared statements */
    public function getJudgeStats(int $judgeId): array {
        $total = 0;
        try {
            $total = (int)$this->db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
        } catch (PDOException $e) {}

        $submitted = 0;
        $drafted   = 0;
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM jury_evaluations WHERE judge_id = ? AND status = 'submitted'");
            $stmt->execute([$judgeId]);
            $submitted = (int)$stmt->fetchColumn();

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM jury_evaluations WHERE judge_id = ? AND status = 'draft'");
            $stmt->execute([$judgeId]);
            $drafted = (int)$stmt->fetchColumn();
        } catch (PDOException $e) { /* jury_evaluations not yet created */ }

        return [
            'total'     => $total,
            'submitted' => $submitted,
            'drafted'   => $drafted,
            'pending'   => max(0, $total - $submitted - $drafted),
            'pct'       => $total > 0 ? round(($submitted / $total) * 100) : 0,
        ];
    }

    public function getAdminResultStats(): array {
        // Query submissions count first — this table always exists
        $totalSubs = 0;
        try {
            $totalSubs = (int)$this->db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
        } catch (PDOException $e) { /* ignore */ }

        // Query jury-specific stats separately — jury tables may not exist yet (migration pending)
        $totalEvals   = 0;
        $complete     = 0;
        $inProgress   = 0;
        $activeJudges = 0;
        try {
            $activeJudges = $this->countActiveJudges();
            $totalEvals   = (int)$this->db->query("SELECT COUNT(*) FROM jury_evaluations WHERE status = 'submitted'")->fetchColumn();
            if ($activeJudges > 0) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM (
                        SELECT submission_id FROM jury_evaluations
                        WHERE status = 'submitted'
                        GROUP BY submission_id
                        HAVING COUNT(*) >= ?
                    ) AS x
                ");
                $stmt->execute([$activeJudges]);
                $complete = (int)$stmt->fetchColumn();
            }
            // In Progress = submissions touched by at least 1 judge (any status) but not fully judged
            $touched    = (int)$this->db->query("SELECT COUNT(DISTINCT submission_id) FROM jury_evaluations")->fetchColumn();
            $inProgress = max(0, $touched - $complete);
        } catch (PDOException $e) { /* jury tables not yet created — leave at 0 */ }

        return [
            'total_submissions'  => $totalSubs,
            'total_evaluations'  => $totalEvals,
            'complete_artworks'  => $complete,
            'in_progress'        => $inProgress,
            'active_judges'      => $activeJudges,
        ];
    }

    // ── Audit Logging ─────────────────────────────────────────────────────────

    private function buildJudgeInviteHtml(string $fullName, string $username, string $setupLink): string {
        $name     = htmlspecialchars($fullName);
        $uname    = htmlspecialchars($username);
        $link     = htmlspecialchars($setupLink);
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Judge Invitation — GREATER Art Competition</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg,#667eea,#764ba2);padding:32px 40px;text-align:center;">
              <div style="color:#fff;font-size:26px;font-weight:700;letter-spacing:1px;">GREATER</div>
              <div style="color:rgba(255,255,255,0.85);font-size:14px;margin-top:4px;">Art Competition 2025</div>
            </td>
          </tr>
          <tr>
            <td style="padding:36px 40px;">
              <p style="font-size:16px;color:#374151;margin:0 0 18px;">Dear <strong>{$name}</strong>,</p>
              <p style="font-size:15px;color:#4b5563;line-height:1.7;margin:0 0 16px;">
                You have been invited to serve as a judge for the <strong>GREATER Art Competition</strong>.
              </p>
              <p style="font-size:15px;color:#4b5563;margin:0 0 8px;">
                <strong>Your username:</strong> {$uname}
              </p>
              <p style="font-size:15px;color:#4b5563;line-height:1.7;margin:0 0 28px;">
                Please click the button below to set your password and access the judging portal. This link is valid for <strong>48 hours</strong>.
              </p>
              <p style="text-align:center;margin:0 0 28px;">
                <a href="{$link}" style="background:#1E90FF;color:#fff;padding:14px 36px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;display:inline-block;">Set My Password</a>
              </p>
              <hr style="border:none;border-top:1px solid #e5e7eb;margin:28px 0;">
              <p style="font-size:12px;color:#9ca3af;margin:0 0 8px;">If the button doesn't work, copy this link into your browser:</p>
              <p style="font-size:12px;color:#6b7280;word-break:break-all;margin:0;">{$link}</p>
              <hr style="border:none;border-top:1px solid #e5e7eb;margin:28px 0;">
              <p style="font-size:13px;color:#9ca3af;margin:0;">
                Questions? Contact us at <a href="mailto:info@greaterproject.eu" style="color:#667eea;">info@greaterproject.eu</a>
              </p>
            </td>
          </tr>
          <tr>
            <td style="background:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
              <p style="font-size:12px;color:#9ca3af;margin:0;">© 2025 GREATER Art Competition · Co-funded by Erasmus+</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    public function logActivity(int $userId, string $action, string $description = ''): void {
        try {
            $ip   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->db->prepare("INSERT INTO admin_activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $description, $ip]);
        } catch (PDOException $e) {
            // Never break the main flow for logging
        }
    }
}
