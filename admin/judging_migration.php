<?php
/**
 * Judging System Migration
 * Creates: jury_criteria, jury_evaluations, jury_scores
 * Adds:    role='jury' support is already in admin_users — no schema change needed
 *
 * Run once via browser, then the lock prevents re-runs.
 * Delete cache/judging_migration.lock to force re-run (not recommended).
 */

$lockFile = __DIR__ . '/../cache/judging_migration.lock';
if (!is_dir(__DIR__ . '/../cache')) {
    @mkdir(__DIR__ . '/../cache', 0755, true);
}
if (file_exists($lockFile)) {
    die('<h2 style="color:green;font-family:sans-serif;">✅ Judging migration already applied. Delete <code>cache/judging_migration.lock</code> to re-run.</h2>');
}

require_once __DIR__ . '/../db.php'; // gives $pdo

$log    = [];
$errors = [];

function jstep(string $label, callable $fn) use (&$log, &$errors): void {
    try {
        $result = $fn();
        $log[] = "✅ $label" . ($result ? " — $result" : '');
    } catch (PDOException $e) {
        $errors[] = "❌ $label — " . $e->getMessage();
    }
}

// ── 1. jury_criteria ─────────────────────────────────────────────────────────
jstep('Create table: jury_criteria', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `jury_criteria` (
            `id`            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name`          VARCHAR(100) NOT NULL,
            `description`   VARCHAR(255) NULL,
            `max_score`     INT          NOT NULL DEFAULT 20,
            `display_order` INT          NOT NULL DEFAULT 0,
            `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
            `created_by`    INT          NULL,
            `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_order` (`display_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
});

// ── 2. Seed default criteria ──────────────────────────────────────────────────
jstep('Seed default scoring criteria', function() use ($pdo) {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM jury_criteria")->fetchColumn();
    if ($count > 0) return 'Already seeded — skipped';
    $pdo->exec("
        INSERT INTO `jury_criteria` (`name`, `description`, `max_score`, `display_order`) VALUES
            ('Creativity',     'Originality and creative expression',           20, 1),
            ('Technique',      'Technical skill and execution quality',         20, 2),
            ('Originality',    'Unique perspective and fresh approach',         20, 3),
            ('Presentation',   'Overall presentation and visual impact',        20, 4),
            ('Overall Impact', 'Emotional resonance and lasting impression',    20, 5)
    ");
    return '5 default criteria inserted';
});

// ── 3. jury_evaluations ───────────────────────────────────────────────────────
jstep('Create table: jury_evaluations', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `jury_evaluations` (
            `id`                INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `submission_id`     INT          NOT NULL,
            `judge_id`          INT          NOT NULL,
            `status`            ENUM('draft','submitted') NOT NULL DEFAULT 'draft',
            `strengths`         TEXT         NULL,
            `weaknesses`        TEXT         NULL,
            `recommendations`   TEXT         NULL,
            `overall_comments`  TEXT         NULL,
            `total_score`       DECIMAL(6,2) NULL,
            `submitted_at`      DATETIME     NULL,
            `reopened_by`       INT          NULL,
            `reopened_at`       DATETIME     NULL,
            `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY  `uq_judge_submission` (`submission_id`, `judge_id`),
            INDEX `idx_judge_id`     (`judge_id`),
            INDEX `idx_submission_id`(`submission_id`),
            INDEX `idx_status`       (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
});

// ── 4. jury_scores ────────────────────────────────────────────────────────────
jstep('Create table: jury_scores', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `jury_scores` (
            `id`            INT  NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `evaluation_id` INT  NOT NULL,
            `criterion_id`  INT  NOT NULL,
            `score`         INT  NOT NULL DEFAULT 0,
            `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_eval_criterion` (`evaluation_id`, `criterion_id`),
            INDEX `idx_evaluation_id` (`evaluation_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
});

// ── 5. Ensure admin_activity_logs exists (used by judging audit trail) ────────
jstep('Verify admin_activity_logs table exists', function() use ($pdo) {
    $r = $pdo->query("SHOW TABLES LIKE 'admin_activity_logs'")->fetchColumn();
    if (!$r) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
                `id`          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id`     INT          NULL,
                `action`      VARCHAR(100) NOT NULL,
                `description` TEXT         NULL,
                `ip_address`  VARCHAR(45)  NULL,
                `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_action`  (`action`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        return 'Created';
    }
    return 'Already exists';
});

// ── 6. Add password_setup_token columns to admin_users ────────────────────────
jstep('Add password_setup_token columns to admin_users', function() use ($pdo) {
    $col = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'password_setup_token'")->fetchAll();
    if (empty($col)) {
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN password_setup_token VARCHAR(64) NULL, ADD COLUMN token_expires_at DATETIME NULL");
        return 'Columns added';
    }
    return 'Already exists — skipped';
});

// ── Write lock ────────────────────────────────────────────────────────────────
if (empty($errors)) {
    @file_put_contents($lockFile, date('Y-m-d H:i:s') . "\n");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Judging System Migration</title>
  <style>
    body  { font-family: Arial, sans-serif; max-width: 720px; margin: 60px auto; padding: 0 20px; background:#f9fafb; }
    h1    { font-size: 24px; color: #1f2937; margin-bottom: 24px; }
    .box  { background:#fff; border-radius:12px; padding:24px; box-shadow:0 4px 16px rgba(0,0,0,.08); }
    .step { padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-size: 15px; }
    .ok   { background: #d1fae5; color: #065f46; }
    .err  { background: #fee2e2; color: #b91c1c; }
    .sum  { margin-top: 24px; padding: 18px 22px; border-radius: 10px; font-size: 16px; font-weight: 600; }
    .sum.ok  { background: #d1fae5; color: #065f46; }
    .sum.err { background: #fee2e2; color: #b91c1c; }
    a     { color: #1d4ed8; }
  </style>
</head>
<body>
  <h1>🏛 GREATER — Judging System Migration</h1>
  <div class="box">
    <?php foreach ($log as $l): ?>
      <div class="step ok"><?php echo htmlspecialchars($l); ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
      <div class="step err"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <div class="sum <?php echo empty($errors) ? 'ok' : 'err'; ?>">
      <?php if (empty($errors)): ?>
        ✅ Migration completed. <a href="index.php?page=judging_criteria">Configure scoring criteria →</a>
      <?php else: ?>
        ⚠️ Migration completed with errors — check the red steps above.
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
