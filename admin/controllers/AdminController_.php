<?php
session_start();
require_once 'models/Analytics.php';
require_once 'models/Registration.php';
require_once 'models/Submission.php';

class AdminController {
    private $analytics;
    private $registration;
    private $submission;
    
    public function __construct() {
        $this->analytics = new Analytics();
        $this->registration = new Registration();
        $this->submission = new Submission();
    }
    
    public function dashboard() {
        $stats = $this->analytics->getDashboardStats();
        $countryStats = $this->analytics->getCountryStats();
        
        include 'views/dashboard.php';
    }
    
    public function registrations() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $registrations = $this->registration->getAllRegistrations($limit, $offset, $search);
        $totalCount = $this->registration->getTotalCount($search);
        $totalPages = ceil($totalCount / $limit);
        
        include 'views/registrations.php';
    }
    
    public function registrationDetail($id) {
        $registration = $this->registration->getRegistrationById($id);
        if (!$registration) {
            header('Location: ?page=registrations&error=Registration not found');
            exit;
        }
        
        include 'views/registration_detail.php';
    }
    
    public function submissions() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $submissions = $this->submission->getAllSubmissions($limit, $offset, $search, $status);
        $totalCount = $this->submission->getTotalCount($search, $status);
        $totalPages = ceil($totalCount / $limit);
        
        include 'views/submissions.php';
    }
    
    public function submissionDetail($id) {
        $submission = $this->submission->getSubmissionById($id);
        if (!$submission) {
            header('Location: ?page=submissions&error=Submission not found');
            exit;
        }
        
        include 'views/submission_detail.php';
    }
    
    public function updateSubmissionStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $score = !empty($_POST['score']) ? $_POST['score'] : null;
            $feedback = !empty($_POST['feedback']) ? $_POST['feedback'] : null;
            $reviewedBy = $_SESSION['user_id'] ?? 1; // Default to admin user
            
            $success = $this->submission->updateSubmissionStatus($id, $status, $score, $feedback, $reviewedBy);
            
            if ($success) {
                header('Location: ?page=submission_detail&id=' . $id . '&success=Status updated successfully');
            } else {
                header('Location: ?page=submission_detail&id=' . $id . '&error=Failed to update status');
            }
            exit;
        }
    }
}

// Router
$controller = new AdminController();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'registrations':
        $controller->registrations();
        break;
    case 'registration_detail':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $controller->registrationDetail($id);
        break;
    case 'submissions':
        $controller->submissions();
        break;
    case 'submission_detail':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $controller->submissionDetail($id);
        break;
    case 'update_submission':
        $controller->updateSubmissionStatus();
        break;
    default:
        $controller->dashboard();
        break;
}
?>