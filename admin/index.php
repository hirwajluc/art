<?php
/**
 * GREATER Art Competition Admin Panel
 * SECURE PRODUCTION VERSION
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get requested page
$page = $_GET['page'] ?? 'dashboard';

// Define public pages that don't require authentication
$publicPages = ['login', 'authenticate', 'set_password', 'do_set_password'];

// SECURITY GATEWAY - Protect entire system
if (!in_array($page, $publicPages)) {
    // Check authentication
    if (!isset($_SESSION['user_id']) || 
        empty($_SESSION['user_id']) || 
        !is_numeric($_SESSION['user_id']) || 
        (int)$_SESSION['user_id'] <= 0) {
        
        // Store intended URL for redirect after login
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        
        // Force redirect to login
        header('Location: login.php?error=Authentication required');
        exit('Access Denied - Please login');
    }
    
    // Additional security: Verify user exists in database
    try {
        require_once __DIR__ . '/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT id, status FROM admin_users WHERE id = ?");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || $user['status'] !== 'active') {
            // User doesn't exist or inactive
            session_destroy();
            session_start();
            header('Location: login.php?error=Your account is no longer valid');
            exit('Account Invalid');
        }
    } catch (Exception $e) {
        error_log("Auth database check failed: " . $e->getMessage());
    }
}

// Handle session clearing for testing
if (isset($_GET['clear_session']) && $_GET['clear_session'] === '1') {
    session_destroy();
    session_start();
    header('Location: login.php?message=Session cleared');
    exit;
}

// Prevent multiple inclusions
if (defined('ADMIN_INDEX_LOADED')) {
    exit('Index already loaded');
}
define('ADMIN_INDEX_LOADED', true);

// ── Role-based routing ────────────────────────────────────────────────────────
// Pages only judges (jury) may access
$judgePages = ['judge_dashboard', 'judge_evaluate', 'judge_save'];
// Pages only admins may access
$adminOnlyPages = [
    'registrations', 'registration_detail', 'submissions', 'submission_detail',
    'update_submission', 'winners', 'export', 'email_campaigns', 'email_send',
    'settings', 'save_settings', 'send_test_email', 'submission_versions',
    'judges', 'create_judge', 'edit_judge', 'delete_judge', 'toggle_judge', 'reset_judge_password',
    'judging_criteria', 'save_criteria', 'judging_results', 'reopen_evaluation',
];

if (!in_array($page, $publicPages)) {
    $userRole = $_SESSION['user_role'] ?? 'jury';
    // Jury users may only access judge pages + dashboard (which redirects them)
    if ($userRole === 'jury' && !in_array($page, $judgePages) && $page !== 'dashboard') {
        header('Location: ?page=judge_dashboard');
        exit;
    }
    // Non-admins cannot access admin-only pages
    if ($userRole !== 'admin' && in_array($page, $adminOnlyPages)) {
        header('Location: ?page=judge_dashboard');
        exit;
    }
    // Redirect jury users landing on admin dashboard → judge dashboard
    if ($userRole === 'jury' && $page === 'dashboard') {
        header('Location: ?page=judge_dashboard');
        exit;
    }
}

// Router - All routes are now secure
switch ($page) {
    case 'login':
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id']) && 
            !empty($_SESSION['user_id']) && 
            is_numeric($_SESSION['user_id']) && 
            (int)$_SESSION['user_id'] > 0) {
            header('Location: ?page=dashboard');
            exit;
        }
        include __DIR__ . '/login.php';
        break;
        
    case 'authenticate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/auth.php';
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (!empty($username) && !empty($password)) {
                $auth = new Auth();
                $result = $auth->login($username, $password, $remember);
                
                if ($result['success']) {
                    $redirectUrl = $_SESSION['intended_url'] ?? '?page=dashboard';
                    unset($_SESSION['intended_url']);
                    header('Location: ' . $redirectUrl);
                    exit;
                } else {
                    header('Location: login.php?error=' . urlencode($result['message']));
                    exit;
                }
            } else {
                header('Location: login.php?error=Please fill in all fields');
                exit;
            }
        } else {
            header('Location: login.php');
            exit;
        }
        break;
        
    case 'logout':
        require_once __DIR__ . '/auth.php';
        $auth = new Auth();
        $auth->logout();
        header('Location: login.php?message=You have been logged out successfully');
        exit;
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Analytics.php';
        
        try {
            $analytics = new Analytics();
            $stats = $analytics->getDashboardStats();
            $countryStats = $analytics->getCountryStats();
            
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/dashboard.php';
        } catch (Exception $e) {
            echo "Error loading dashboard: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'registrations':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Registration.php';
        
        try {
            $registrationModel = new Registration();
            $currentPage = max(1, (int)($_GET['p'] ?? 1));
            $limit = 20;
            $offset = ($currentPage - 1) * $limit;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            $registrations = $registrationModel->getAllRegistrations($limit, $offset, $search);
            $totalCount = $registrationModel->getTotalCount($search);
            $totalPages = max(1, ceil($totalCount / $limit));

            // FIX: pass $currentPage as $page to the view so pagination
            // links work correctly (the router's $page variable = 'registrations'
            // which would break the numeric page comparisons in the view)
            $page = $currentPage;
            
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/registrations.php';
        } catch (Exception $e) {
            echo "Error loading registrations: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'registration_detail':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Registration.php';
        
        try {
            $registrationModel = new Registration();
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                header('Location: ?page=registrations&error=Invalid registration ID');
                exit;
            }
            
            $registration = $registrationModel->getRegistrationById($id);
            
            if (!$registration) {
                header('Location: ?page=registrations&error=Registration not found');
                exit;
            }
            
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/registration_detail.php';
        } catch (Exception $e) {
            echo "Error loading registration details: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'submissions':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Submission.php';
        
        try {
            $submissionModel = new Submission();
            $currentPage = max(1, (int)($_GET['p'] ?? 1));
            $limit = 20;
            $offset = ($currentPage - 1) * $limit;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            
            $submissions = $submissionModel->getAllSubmissions($limit, $offset, $search, $status);
            $totalCount = $submissionModel->getTotalCount($search, $status);
            $totalPages = max(1, ceil($totalCount / $limit));

            // FIX: $page holds the string 'submissions' from the router above.
            // The view uses $page as the numeric current page for pagination
            // comparisons (e.g. if ($i == $page)) and for building prev/next
            // links (e.g. p=<?php echo ($page - 1)). Overwrite it here with
            // the numeric value so the view works correctly.
            $page = $currentPage;
            
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/submissions.php';
        } catch (Exception $e) {
            echo "Error loading submissions: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'submission_detail':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Submission.php';
        
        try {
            $submissionModel = new Submission();
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                header('Location: ?page=submissions&error=Invalid submission ID');
                exit;
            }
            
            $submission = $submissionModel->getSubmissionById($id);
            
            if (!$submission) {
                header('Location: ?page=submissions&error=Submission not found');
                exit;
            }
            
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/submission_detail.php';
        } catch (Exception $e) {
            echo "Error loading submission details: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'update_submission':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Submission.php';
            try {
                $submissionModel = new Submission();
                $id     = (int)($_POST['id'] ?? 0);
                $action = $_POST['action'] ?? 'note';

                if ($action === 'status') {
                    // Status-only change (for Winners page classification)
                    $allowed = ['pending', 'approved', 'rejected'];
                    $status  = in_array($_POST['status'] ?? '', $allowed) ? $_POST['status'] : 'pending';
                    $ok = $submissionModel->updateStatus($id, $status);
                    $msg = $ok ? 'Status updated.' : 'Failed to update status.';
                    $param = $ok ? 'success' : 'error';
                } else {
                    // Admin note for judges — never touches score
                    $note = trim($_POST['note'] ?? '');
                    $ok   = $submissionModel->saveAdminNote($id, $note ?: null, (int)$_SESSION['user_id']);
                    $msg  = $ok ? 'Note saved.' : 'Failed to save note.';
                    $param = $ok ? 'success' : 'error';
                }
                header('Location: ?page=submission_detail&id=' . $id . '&' . $param . '=' . urlencode($msg));
            } catch (Exception $e) {
                header('Location: ?page=submissions&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=submissions');
        }
        exit;
        break;

    // -----------------------------------------------------------------------
    // Email Campaigns
    // -----------------------------------------------------------------------
    case 'email_campaigns':
        require_once __DIR__ . '/config/database.php';
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Count registered but not submitted
            $s = $db->query("
                SELECT COUNT(*) FROM registrations r
                LEFT JOIN submissions s ON s.userCode = r.userCode
                WHERE s.id IS NULL AND r.email IS NOT NULL AND r.email <> ''
            ");
            $statsNotSubmitted = (int)$s->fetchColumn();

            // Count submitters (distinct emails)
            $s = $db->query("
                SELECT COUNT(DISTINCT userEmail) FROM submissions
                WHERE userEmail IS NOT NULL AND userEmail <> ''
            ");
            $statsSubmitted = (int)$s->fetchColumn();

            // Recent campaigns (last 20)
            try {
                $s = $db->query("
                    SELECT ec.*, au.full_name AS admin_name
                    FROM email_campaigns ec
                    LEFT JOIN admin_users au ON au.id = ec.sent_by
                    ORDER BY ec.sent_at DESC
                    LIMIT 20
                ");
                $recentCampaigns = $s->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $recentCampaigns = [];
            }

            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';

            $currentUser = [
                'id'        => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email'     => $_SESSION['user_email']     ?? 'admin@greaterproject.eu',
                'role'      => $_SESSION['user_role']      ?? 'admin',
            ];
            include __DIR__ . '/views/email_campaigns.php';
        } catch (Exception $e) {
            echo "Error loading email campaigns: " . htmlspecialchars($e->getMessage());
        }
        break;

    // -----------------------------------------------------------------------
    // Competition Settings (view + save)
    // -----------------------------------------------------------------------
    case 'settings':
        require_once __DIR__ . '/config/database.php';
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Load all settings
            try {
                $s = $db->query("SELECT setting_key, setting_value FROM competition_settings");
                $rawSettings = $s->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (Exception $e) {
                $rawSettings = [];
            }

            $competitionSettings = [
                'registration_deadline' => $rawSettings['registration_deadline'] ?? '2025-12-31 23:59:59',
                'submission_deadline'   => $rawSettings['submission_deadline']   ?? '2025-12-31 23:59:59',
                'competition_title'     => $rawSettings['competition_title']     ?? 'GREATER Art Competition',
                'winner_announcement'   => $rawSettings['winner_announcement']   ?? '2026-02-01',
            ];

            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';

            $currentUser = [
                'id'        => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email'     => $_SESSION['user_email']     ?? 'admin@greaterproject.eu',
                'role'      => $_SESSION['user_role']      ?? 'admin',
            ];
            include __DIR__ . '/views/settings.php';
        } catch (Exception $e) {
            echo "Error loading settings: " . htmlspecialchars($e->getMessage());
        }
        break;

    case 'save_settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            try {
                $database = new Database();
                $db = $database->getConnection();

                $allowed = [
                    'registration_deadline', 'submission_deadline', 'competition_title', 'winner_announcement',
                    'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
                    'smtp_from_email', 'smtp_from_name',
                ];
                $stmt = $db->prepare("
                    INSERT INTO competition_settings (setting_key, setting_value, updated_at, updated_by)
                    VALUES (?, ?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value),
                                            updated_at    = NOW(),
                                            updated_by    = VALUES(updated_by)
                ");
                foreach ($allowed as $key) {
                    if (isset($_POST[$key])) {
                        $val = trim($_POST[$key]);
                        // Convert date inputs to datetime if needed
                        if (in_array($key, ['registration_deadline','submission_deadline']) && strlen($val) === 10) {
                            $val .= ' 23:59:59';
                        }
                        $stmt->execute([$key, $val, $_SESSION['user_id']]);
                    }
                }
                header('Location: ?page=settings&success=' . urlencode('Settings saved successfully'));
            } catch (Exception $e) {
                header('Location: ?page=settings&error=' . urlencode('Error saving settings: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=settings');
        }
        exit;
        break;

    // -----------------------------------------------------------------------
    // Send test email
    // -----------------------------------------------------------------------
    case 'send_test_email':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/helpers/Mailer.php';
        try {
            $database = new Database();
            $db = $database->getConnection();
            $mailer = Mailer::fromDB($db);
            $toEmail = $_SESSION['user_email'] ?? 'admin@greaterproject.eu';
            $result  = $mailer->send(
                $toEmail,
                $_SESSION['user_full_name'] ?? 'Admin',
                'GREATER — SMTP Test Email',
                '<p>This is a test email from the GREATER Art Competition admin panel.</p><p>If you received this, your SMTP configuration is working correctly.</p>'
            );
            if ($result === true) {
                header('Location: ?page=settings&success=' . urlencode('Test email sent successfully to ' . $toEmail));
            } else {
                header('Location: ?page=settings&error=' . urlencode('SMTP error: ' . $result));
            }
        } catch (Exception $e) {
            header('Location: ?page=settings&error=' . urlencode('Error: ' . $e->getMessage()));
        }
        exit; break;

    // -----------------------------------------------------------------------
    // Winners
    // -----------------------------------------------------------------------
    case 'winners':
        require_once __DIR__ . '/config/database.php';
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Fetch approved (top-scored) submissions
            $s = $db->query("
                SELECT s.*, r.fullName AS participantName, r.nationality, r.birthDate
                FROM submissions s
                LEFT JOIN registrations r ON r.userCode = s.userCode
                WHERE s.status = 'approved'
                ORDER BY s.score DESC, s.submissionDate ASC
                LIMIT 20
            ");
            $approvedSubmissions = $s ? $s->fetchAll(PDO::FETCH_ASSOC) : [];

            $currentUser = [
                'id'        => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email'     => $_SESSION['user_email']     ?? 'admin@greaterproject.eu',
                'role'      => $_SESSION['user_role']      ?? 'admin',
            ];
            $currentPage = 'winners';
            include __DIR__ . '/views/winners.php';
        } catch (Exception $e) {
            echo "Error loading winners: " . htmlspecialchars($e->getMessage());
        }
        break;

    // -----------------------------------------------------------------------
    // Export Data
    // -----------------------------------------------------------------------
    case 'export':
        require_once __DIR__ . '/config/database.php';
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Handle CSV download requests
            $exportType = $_GET['type'] ?? '';
            if (in_array($exportType, ['registrations', 'submissions'])) {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $exportType . '_' . date('Y-m-d') . '.csv"');
                header('Pragma: no-cache');

                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

                if ($exportType === 'registrations') {
                    fputcsv($out, ['ID','Full Name','Birth Date','Nationality','ID Number','Email','Phone','Category','User Code','Registration Date','IP Address']);
                    $rows = $db->query("SELECT id,fullName,birthDate,nationality,idNumber,email,phone,category,userCode,registrationDate,ipAddress FROM registrations ORDER BY registrationDate DESC");
                } else {
                    fputcsv($out, ['ID','User Code','Participant Name','Email','Category','Artwork Name','Description','File Name','File Size','Status','Score','Submission Date']);
                    $rows = $db->query("SELECT id,userCode,userName,userEmail,category,artworkName,description,originalFileName,fileSize,status,score,submissionDate FROM submissions ORDER BY submissionDate DESC");
                }

                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
                exit;
            }

            // Handle ZIP export of submission files
            if ($exportType === 'submissions_zip') {
                if (!class_exists('ZipArchive')) {
                    die('ZipArchive extension is not available on this server.');
                }

                $category = trim($_GET['category'] ?? '');
                set_time_limit(600); // large files may take a while
                ini_set('memory_limit', '512M');

                // Build query
                $baseDir = dirname(__DIR__); // /var/www/html/art
                $where   = $category ? "WHERE category = " . $db->quote($category) : '';
                $rows    = $db->query("SELECT userCode, artworkName, category, originalFileName, filePath FROM submissions $where ORDER BY userCode ASC")->fetchAll(PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    header('Location: ?page=export&error=' . urlencode('No submissions found' . ($category ? " for category: $category" : '') . '.'));
                    exit;
                }

                // Build ZIP into a temp file
                $zipName = 'submissions' . ($category ? '_' . $category : '') . '_' . date('Y-m-d') . '.zip';
                $tmpPath = sys_get_temp_dir() . '/' . uniqid('art_export_') . '.zip';

                $zip = new ZipArchive();
                if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    header('Location: ?page=export&error=' . urlencode('Could not create ZIP file.'));
                    exit;
                }

                $added   = 0;
                $missing = 0;
                foreach ($rows as $row) {
                    $absPath = $baseDir . '/' . ltrim($row['filePath'], '/');
                    if (!file_exists($absPath)) { $missing++; continue; }

                    // Sanitise the name used inside the ZIP
                    $ext      = pathinfo($row['originalFileName'], PATHINFO_EXTENSION);
                    $safeName = preg_replace('/[^\w\-. ()]/', '_', $row['artworkName']);
                    $safeName = mb_substr($safeName, 0, 80);
                    $folder   = $row['category'] === 'photography_paint' ? 'Photography_Paint' : 'Short_Video';
                    $entryName = $folder . '/' . $row['userCode'] . ' - ' . $safeName . '.' . $ext;

                    $zip->addFile($absPath, $entryName);
                    $added++;
                }
                $zip->close();

                if ($added === 0) {
                    @unlink($tmpPath);
                    header('Location: ?page=export&error=' . urlencode('No files found on disk to package.'));
                    exit;
                }

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zipName . '"');
                header('Content-Length: ' . filesize($tmpPath));
                header('Pragma: no-cache');
                readfile($tmpPath);
                @unlink($tmpPath);
                exit;
            }

            // Count stats for the view
            $totalRegs  = (int)$db->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
            $totalSubs  = (int)$db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

            $currentUser = [
                'id'        => $_SESSION['user_id'],
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email'     => $_SESSION['user_email']     ?? 'admin@greaterproject.eu',
                'role'      => $_SESSION['user_role']      ?? 'admin',
            ];
            $currentPage = 'export';
            include __DIR__ . '/views/export.php';
        } catch (Exception $e) {
            echo "Error loading export: " . htmlspecialchars($e->getMessage());
        }
        break;

    // -----------------------------------------------------------------------
    // Submission version history (AJAX)
    // -----------------------------------------------------------------------
    case 'submission_versions':
        header('Content-Type: application/json');
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Submission.php';
        try {
            $database = new Database();
            $db = $database->getConnection();
            $subId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($subId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }
            $s = $db->prepare("
                SELECT * FROM submission_versions
                WHERE submission_id = ?
                ORDER BY version_number ASC
            ");
            $s->execute([$subId]);
            $versions = $s->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'versions' => $versions]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
        break;

    // -----------------------------------------------------------------------
    // Judge Management (admin only)
    // -----------------------------------------------------------------------
    case 'judges':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging = new Judging();
            $judges  = $judging->getJudges();
            $currentUser = [
                'id' => $_SESSION['user_id'], 'full_name' => $_SESSION['user_full_name'] ?? 'Admin',
                'email' => $_SESSION['user_email'] ?? '', 'role' => $_SESSION['user_role'] ?? 'admin',
            ];
            $currentPage  = 'judges';
            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';
            // Consume the one-time invite link stored after judge creation
            $newInviteLink = $_SESSION['new_invite_link'] ?? null;
            unset($_SESSION['new_invite_link']);
            include __DIR__ . '/views/judges.php';
        } catch (Exception $e) {
            header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
        }
        break;

    case 'create_judge':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Judging.php';
            try {
                $judging  = new Judging();
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email']    ?? '');
                $fullName = trim($_POST['full_name'] ?? '');
                if (empty($username) || empty($email) || empty($fullName)) {
                    header('Location: ?page=judges&error=' . urlencode('All fields are required.'));
                } else {
                    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    $result  = $judging->createJudge($username, $email, $fullName, (int)$_SESSION['user_id'], $baseUrl);
                    $param   = $result['success'] ? 'success' : 'error';
                    // Store invite link in session so it survives the redirect and can be shown in the UI
                    if ($result['success'] && isset($result['token'])) {
                        $_SESSION['new_invite_link'] = $baseUrl . '/admin/?page=set_password&token=' . $result['token'];
                    }
                    header('Location: ?page=judges&' . $param . '=' . urlencode($result['message']));
                }
            } catch (Exception $e) {
                header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=judges');
        }
        exit; break;

    case 'edit_judge':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Judging.php';
            try {
                $judging  = new Judging();
                $id       = (int)($_POST['id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                if (!$id || empty($fullName) || empty($email)) {
                    header('Location: ?page=judges&error=' . urlencode('All fields are required.'));
                } else {
                    $result = $judging->updateJudge($id, $fullName, $email, (int)$_SESSION['user_id']);
                    $param  = $result['success'] ? 'success' : 'error';
                    header('Location: ?page=judges&' . $param . '=' . urlencode($result['message']));
                }
            } catch (Exception $e) {
                header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=judges');
        }
        exit; break;

    case 'toggle_judge':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging = new Judging();
            $id      = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            $result  = $judging->toggleJudgeStatus($id, (int)$_SESSION['user_id']);
            $param   = $result['success'] ? 'success' : 'error';
            header('Location: ?page=judges&' . $param . '=' . urlencode($result['message']));
        } catch (Exception $e) {
            header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
        }
        exit; break;

    case 'reset_judge_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Judging.php';
            try {
                $judging  = new Judging();
                $id       = (int)($_POST['id'] ?? 0);
                $password = $_POST['new_password'] ?? '';
                if (!$id || strlen($password) < 6) {
                    header('Location: ?page=judges&error=' . urlencode('Password must be at least 6 characters.'));
                } else {
                    $result = $judging->resetJudgePassword($id, $password, (int)$_SESSION['user_id']);
                    $param  = $result['success'] ? 'success' : 'error';
                    header('Location: ?page=judges&' . $param . '=' . urlencode($result['message']));
                }
            } catch (Exception $e) {
                header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=judges');
        }
        exit; break;

    case 'delete_judge':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging = new Judging();
            $id      = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            $result  = $judging->deleteJudge($id, (int)$_SESSION['user_id']);
            $param   = $result['success'] ? 'success' : 'error';
            header('Location: ?page=judges&' . $param . '=' . urlencode($result['message']));
        } catch (Exception $e) {
            header('Location: ?page=judges&error=' . urlencode('Error: ' . $e->getMessage()));
        }
        exit; break;

    // -----------------------------------------------------------------------
    // Judging Criteria (admin only)
    // -----------------------------------------------------------------------
    case 'judging_criteria':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging  = new Judging();
            $criteria = $judging->getCriteria(false); // all, including inactive
            $currentUser = [
                'id' => $_SESSION['user_id'], 'full_name' => $_SESSION['user_full_name'] ?? 'Admin',
                'email' => $_SESSION['user_email'] ?? '', 'role' => $_SESSION['user_role'] ?? 'admin',
            ];
            $currentPage  = 'judging_criteria';
            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';
            include __DIR__ . '/views/judging_criteria.php';
        } catch (Exception $e) {
            echo "Error loading criteria: " . htmlspecialchars($e->getMessage());
        }
        break;

    case 'save_criteria':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Judging.php';
            try {
                $judging  = new Judging();
                $names    = $_POST['name']        ?? [];
                $descs    = $_POST['description'] ?? [];
                $maxes    = $_POST['max_score']   ?? [];
                $ids      = $_POST['criterion_id'] ?? [];
                $criteria = [];
                foreach ($names as $i => $name) {
                    if (!empty(trim($name))) {
                        $criteria[] = [
                            'id'          => !empty($ids[$i]) ? (int)$ids[$i] : null,
                            'name'        => $name,
                            'description' => $descs[$i] ?? '',
                            'max_score'   => $maxes[$i] ?? 20,
                        ];
                    }
                }
                if (empty($criteria)) {
                    header('Location: ?page=judging_criteria&error=' . urlencode('At least one criterion is required.'));
                } else {
                    $ok = $judging->saveCriteria($criteria, (int)$_SESSION['user_id']);
                    if ($ok) header('Location: ?page=judging_criteria&success=' . urlencode('Scoring criteria saved successfully.'));
                    else     header('Location: ?page=judging_criteria&error='   . urlencode('Failed to save criteria.'));
                }
            } catch (Exception $e) {
                header('Location: ?page=judging_criteria&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=judging_criteria');
        }
        exit; break;

    // -----------------------------------------------------------------------
    // Judging Results (admin only)
    // -----------------------------------------------------------------------
    case 'judging_results':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging  = new Judging();
            $catFilter    = trim($_GET['category'] ?? '');
            $searchFilter = trim($_GET['search']   ?? '');
            $results      = $judging->getResults($catFilter, $searchFilter);
            $resultStats  = $judging->getAdminResultStats();
            $criteria     = $judging->getCriteria();

            // If viewing a specific submission detail
            $detailId         = isset($_GET['detail']) ? (int)$_GET['detail'] : 0;
            $detailEvaluations = [];
            $detailSubmission  = null;
            $criterionAverages = [];
            if ($detailId > 0) {
                $detailSubmission  = $judging->getAnonymousSubmission($detailId);
                $detailEvaluations = $judging->getSubmissionResultDetail($detailId);
                $criterionAverages = $judging->getCriterionAverages($detailId);
            }

            // CSV export
            if (isset($_GET['export']) && $_GET['export'] === 'csv') {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="judging_results_' . date('Y-m-d') . '.csv"');
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['Rank','Competition Code','Artwork Title','Category','Judges Submitted','Average Score','Max Score','Min Score','Status']);
                foreach ($results as $r) {
                    fputcsv($out, [
                        $r['rank'] ?? '-',
                        $r['competition_code'],
                        $r['artwork_name'],
                        getCategoryName($r['category']),
                        $r['judges_submitted'] . '/' . $r['active_judges'],
                        $r['avg_score'] ?? '-',
                        $r['max_score'] ?? '-',
                        $r['min_score'] ?? '-',
                        $r['is_complete'] ? 'Complete' : 'In Progress',
                    ]);
                }
                fclose($out);
                exit;
            }

            $currentUser = [
                'id' => $_SESSION['user_id'], 'full_name' => $_SESSION['user_full_name'] ?? 'Admin',
                'email' => $_SESSION['user_email'] ?? '', 'role' => $_SESSION['user_role'] ?? 'admin',
            ];
            $currentPage = 'judging_results';
            include __DIR__ . '/views/judging_results.php';
        } catch (Exception $e) {
            echo "Error loading results: " . htmlspecialchars($e->getMessage());
        }
        break;

    case 'reopen_evaluation':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging = new Judging();
            $evalId  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            $backTo  = (int)($_GET['submission'] ?? 0);
            $result  = $judging->reopenEvaluation($evalId, (int)$_SESSION['user_id']);
            $param   = $result['success'] ? 'success' : 'error';
            $dest    = $backTo > 0 ? "?page=judging_results&detail=$backTo" : '?page=judging_results';
            header('Location: ' . $dest . '&' . $param . '=' . urlencode($result['message']));
        } catch (Exception $e) {
            header('Location: ?page=judging_results&error=' . urlencode('Error: ' . $e->getMessage()));
        }
        exit; break;

    // -----------------------------------------------------------------------
    // Judge Dashboard (jury role only)
    // -----------------------------------------------------------------------
    case 'judge_dashboard':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging  = new Judging();
            $judgeId  = (int)$_SESSION['user_id'];
            $search   = trim($_GET['search']   ?? '');
            $catFilter  = trim($_GET['category'] ?? '');
            $evalFilter = trim($_GET['status']   ?? '');
            $submissions = $judging->getSubmissionsForJudge($judgeId, $search, $catFilter, $evalFilter);
            $stats       = $judging->getJudgeStats($judgeId);
            $currentUser = [
                'id' => $judgeId, 'full_name' => $_SESSION['user_full_name'] ?? 'Judge',
                'email' => $_SESSION['user_email'] ?? '', 'role' => 'jury',
                'username' => $_SESSION['user_username'] ?? 'judge',
            ];
            $currentPage  = 'judge_dashboard';
            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';
            include __DIR__ . '/views/judge_dashboard.php';
        } catch (Exception $e) {
            echo "Error loading judge dashboard: " . htmlspecialchars($e->getMessage());
        }
        break;

    // -----------------------------------------------------------------------
    // Judge Evaluate (jury role only)
    // -----------------------------------------------------------------------
    case 'judge_evaluate':
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Judging.php';
        try {
            $judging      = new Judging();
            $judgeId      = (int)$_SESSION['user_id'];
            $submissionId = (int)($_GET['id'] ?? 0);

            if ($submissionId <= 0) {
                header('Location: ?page=judge_dashboard&error=' . urlencode('Invalid artwork.'));
                exit;
            }

            $submission = $judging->getAnonymousSubmission($submissionId);
            if (!$submission) {
                header('Location: ?page=judge_dashboard&error=' . urlencode('Artwork not found.'));
                exit;
            }

            $criteria   = $judging->getCriteria();
            $evaluation = $judging->getEvaluation($submissionId, $judgeId);
            $scores     = [];
            if ($evaluation) {
                foreach ($judging->getScores($evaluation['id']) as $s) {
                    $scores[$s['criterion_id']] = $s['score'];
                }
            }

            $currentUser = [
                'id' => $judgeId, 'full_name' => $_SESSION['user_full_name'] ?? 'Judge',
                'email' => $_SESSION['user_email'] ?? '', 'role' => 'jury',
                'username' => $_SESSION['user_username'] ?? 'judge',
            ];
            $currentPage  = 'judge_evaluate';
            $flashSuccess = isset($_GET['success']) ? urldecode($_GET['success']) : '';
            $flashError   = isset($_GET['error'])   ? urldecode($_GET['error'])   : '';
            include __DIR__ . '/views/judge_evaluate.php';
        } catch (Exception $e) {
            echo "Error loading evaluation form: " . htmlspecialchars($e->getMessage());
        }
        break;

    // -----------------------------------------------------------------------
    // Judge Save (draft or final submit — jury role only)
    // -----------------------------------------------------------------------
    case 'judge_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            require_once __DIR__ . '/models/Judging.php';
            try {
                $judging      = new Judging();
                $judgeId      = (int)$_SESSION['user_id'];
                $submissionId = (int)($_POST['submission_id'] ?? 0);
                $action       = $_POST['action'] ?? 'draft'; // 'draft' or 'submit'
                $status       = ($action === 'submit') ? 'submitted' : 'draft';

                $rawScores = $_POST['scores'] ?? [];
                $scores    = [];
                foreach ($rawScores as $critId => $score) {
                    $scores[(int)$critId] = (int)$score;
                }

                $result = $judging->saveEvaluation(
                    $submissionId,
                    $judgeId,
                    $scores,
                    trim($_POST['strengths']        ?? ''),
                    trim($_POST['weaknesses']       ?? ''),
                    trim($_POST['recommendations']  ?? ''),
                    trim($_POST['overall_comments'] ?? ''),
                    $status
                );

                $param = $result['success'] ? 'success' : 'error';
                header('Location: ?page=judge_evaluate&id=' . $submissionId . '&' . $param . '=' . urlencode($result['message']));
            } catch (Exception $e) {
                header('Location: ?page=judge_dashboard&error=' . urlencode('Error: ' . $e->getMessage()));
            }
        } else {
            header('Location: ?page=judge_dashboard');
        }
        exit; break;

    // -----------------------------------------------------------------------
    // Set Password (public — judge invitation flow)
    // -----------------------------------------------------------------------
    case 'set_password':
        $token = trim($_GET['token'] ?? '');
        $judge = null;
        $tokenError = '';
        if ($token) {
            try {
                require_once __DIR__ . '/config/database.php';
                $database = new Database();
                $db = $database->getConnection();
                $stmt = $db->prepare("SELECT id, full_name, email FROM admin_users WHERE password_setup_token = ? AND token_expires_at > NOW() AND status = 'active'");
                $stmt->execute([$token]);
                $judge = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$judge) $tokenError = 'This link is invalid or has expired. Please ask the admin to resend your invitation.';
            } catch (Exception $e) {
                $tokenError = 'A system error occurred. Please try again later.';
            }
        } else {
            $tokenError = 'No invitation token provided.';
        }
        $flashError = isset($_GET['error']) ? urldecode($_GET['error']) : '';
        include __DIR__ . '/views/set_password.php';
        break;

    case 'do_set_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config/database.php';
            try {
                $database = new Database();
                $db = $database->getConnection();
                $token    = trim($_POST['token'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm  = $_POST['confirm']  ?? '';

                if (strlen($password) < 6) {
                    header('Location: ?page=set_password&token=' . urlencode($token) . '&error=' . urlencode('Password must be at least 6 characters.'));
                    exit;
                }
                if ($password !== $confirm) {
                    header('Location: ?page=set_password&token=' . urlencode($token) . '&error=' . urlencode('Passwords do not match.'));
                    exit;
                }

                $stmt = $db->prepare("SELECT id FROM admin_users WHERE password_setup_token = ? AND token_expires_at > NOW() AND status = 'active'");
                $stmt->execute([$token]);
                $judge = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$judge) {
                    header('Location: ?page=set_password&token=' . urlencode($token) . '&error=' . urlencode('This link is invalid or expired.'));
                    exit;
                }

                $hash = password_hash($password, PASSWORD_BCRYPT);
                $upd  = $db->prepare("UPDATE admin_users SET password = ?, password_setup_token = NULL, token_expires_at = NULL WHERE id = ?");
                $upd->execute([$hash, $judge['id']]);
                header('Location: login.php?message=' . urlencode('Password set successfully. You can now log in.'));
            } catch (Exception $e) {
                header('Location: ?page=set_password&error=' . urlencode('System error. Please try again.'));
            }
        } else {
            header('Location: login.php');
        }
        exit; break;

    default:
        // For unknown pages, redirect to login if not authenticated, otherwise dashboard
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: login.php');
        } else {
            header('Location: ?page=dashboard');
        }
        exit;
}

// Utility functions for views
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function formatDate($date, $format = 'M j, Y H:i') {
    return date($format, strtotime($date));
}

function getCategoryName($category) {
    return $category === 'photography_paint' ? 'Photography/Painting' : 'Short Video';
}

function getStatusClass($status) {
    switch ($status) {
        case 'approved': return 'status-approved';
        case 'rejected': return 'status-rejected';
        case 'pending':
        default: return 'status-pending';
    }
}

// Stop execution
exit;
?>