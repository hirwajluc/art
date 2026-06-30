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
$publicPages = ['login', 'authenticate'];

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
                $id = (int)$_POST['id'];
                $status = $_POST['status'] ?? '';
                $score = !empty($_POST['score']) ? (float)$_POST['score'] : null;
                $feedback = !empty($_POST['feedback']) ? trim($_POST['feedback']) : null;
                $reviewedBy = $_SESSION['user_id'];

                $success = $submissionModel->updateSubmissionStatus($id, $status, $score, $feedback, $reviewedBy);

                if ($success) {
                    header('Location: ?page=submission_detail&id=' . $id . '&success=Review submitted successfully');
                } else {
                    header('Location: ?page=submission_detail&id=' . $id . '&error=Failed to update submission');
                }
            } catch (Exception $e) {
                header('Location: ?page=submissions&error=' . urlencode('Error updating submission: ' . $e->getMessage()));
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

                $allowed = ['registration_deadline', 'submission_deadline', 'competition_title', 'winner_announcement'];
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