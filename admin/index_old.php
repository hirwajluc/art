<?php
/**
 * GREATER Art Competition Admin Panel
 * Clean Entry Point - Debug Version
 */

// Debug: Add this to see if index.php is called multiple times
error_log("admin/index.php loaded at: " . date('Y-m-d H:i:s'));

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent multiple inclusions
if (defined('ADMIN_INDEX_LOADED')) {
    exit('Index already loaded');
}
define('ADMIN_INDEX_LOADED', true);

// Simple routing without AdminController for debugging
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'dashboard':
        // Include database and models directly for debugging
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Analytics.php';
        
        try {
            $analytics = new Analytics();
            $stats = $analytics->getDashboardStats();
            $countryStats = $analytics->getCountryStats();
            
            // Set currentUser for views
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? 1,
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/dashboard.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
            
            // Set currentUser for views
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? 1,
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/submission_detail.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
                $reviewedBy = $_SESSION['user_id'] ?? 1;
                
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
            
            // Set currentUser for views
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? 1,
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/registrations.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
            
            // Set currentUser for views
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? 1,
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/registration_detail.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
            
            // Set currentUser for views
            $currentUser = [
                'id' => $_SESSION['user_id'] ?? 1,
                'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@greaterproject.eu',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ];
            
            include __DIR__ . '/views/submissions.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        break;
        
    case 'login':
        include __DIR__ . '/login.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: login.php');
        exit;
        break;
        
    default:
        header('Location: ?page=dashboard');
        exit;
}

// Stop execution completely
exit;

/**
 * Utility functions
 */
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
?>