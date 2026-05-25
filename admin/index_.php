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