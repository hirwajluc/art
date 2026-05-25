<?php
// Don't start session here - it should be started in index.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Submission.php';

class AdminController {
    private $analytics;
    private $registration;
    private $submission;
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->analytics = new Analytics();
        $this->registration = new Registration();
        $this->submission = new Submission();
        
        // Check authentication for all methods except login/logout
        $this->checkAuthentication();
    }
    
    private function checkAuthentication() {
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        
        // Allow login and logout without authentication check
        if (in_array($page, ['login', 'logout', 'authenticate'])) {
            return;
        }
        
        // For all other pages, require authentication
        if (!$this->auth->isAuthenticated()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function login() {
        // If already authenticated, redirect to dashboard
        if ($this->auth->isAuthenticated()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        include 'views/login.php';
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            if (empty($username) || empty($password)) {
                header('Location: login.php?error=Please fill in all fields');
                exit;
            }
            
            $result = $this->auth->login($username, $password, $rememberMe);
            
            if ($result['success']) {
                // Redirect to intended page or dashboard
                $redirect = $_SESSION['intended_url'] ?? 'index.php?page=dashboard';
                unset($_SESSION['intended_url']);
                header('Location: ' . $redirect);
                exit;
            } else {
                header('Location: login.php?error=' . urlencode($result['message']));
                exit;
            }
        } else {
            header('Location: login.php');
            exit;
        }
    }
    
    public function logout() {
        if ($this->auth->isAuthenticated()) {
            $this->auth->logout();
        }
        
        header('Location: login.php?message=You have been logged out successfully');
        exit;
    }
    
    public function dashboard() {
        $this->requireAuthentication();
        
        $stats = $this->analytics->getDashboardStats();
        $countryStats = $this->analytics->getCountryStats();
        
        // Get current user info for display
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/dashboard.php';
    }
    
    public function registrations() {
        $this->requireAuthentication();
        
        // Fix the page parameter name conflict
        $currentPage = max(1, (int)($_GET['p'] ?? 1));  // Use 'p' instead of 'page' to avoid conflict
        $limit = 20;
        $offset = ($currentPage - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $registrations = $this->registration->getAllRegistrations($limit, $offset, $search);
        $totalCount = $this->registration->getTotalCount($search);
        $totalPages = max(1, ceil($totalCount / $limit));
        
        // Get current user info for display
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/registrations.php';
    }
    
    public function registrationDetail($id) {
        $this->requireAuthentication();
        
        $registration = $this->registration->getRegistrationById($id);
        if (!$registration) {
            header('Location: index.php?page=registrations&error=Registration not found');
            exit;
        }
        
        // Get current user info for display
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/registration_detail.php';
    }
    
    public function submissions() {
        $this->requireAuthentication();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $submissions = $this->submission->getAllSubmissions($limit, $offset, $search, $status);
        $totalCount = $this->submission->getTotalCount($search, $status);
        $totalPages = ceil($totalCount / $limit);
        
        // Get current user info for display
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/submissions.php';
    }
    
    public function submissionDetail($id) {
        $this->requireAuthentication();
        
        $submission = $this->submission->getSubmissionById($id);
        if (!$submission) {
            header('Location: index.php?page=submissions&error=Submission not found');
            exit;
        }
        
        // Get current user info for display
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/submission_detail.php';
    }
    
    public function updateSubmissionStatus() {
        $this->requireAuthentication();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $score = !empty($_POST['score']) ? $_POST['score'] : null;
            $feedback = !empty($_POST['feedback']) ? $_POST['feedback'] : null;
            $reviewedBy = $_SESSION['user_id'];
            
            $success = $this->submission->updateSubmissionStatus($id, $status, $score, $feedback, $reviewedBy);
            
            if ($success) {
                header('Location: index.php?page=submission_detail&id=' . $id . '&success=Status updated successfully');
            } else {
                header('Location: index.php?page=submission_detail&id=' . $id . '&error=Failed to update status');
            }
            exit;
        }
    }
    
    public function profile() {
        $this->requireAuthentication();
        
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/profile.php';
    }
    
    public function updateProfile() {
        $this->requireAuthentication();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $userId = $_SESSION['user_id'];
            
            // Validate inputs
            if (empty($fullName) || empty($email)) {
                header('Location: index.php?page=profile&error=Please fill in all required fields');
                exit;
            }
            
            // If changing password, validate
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    header('Location: index.php?page=profile&error=Current password is required to change password');
                    exit;
                }
                
                if ($newPassword !== $confirmPassword) {
                    header('Location: index.php?page=profile&error=New passwords do not match');
                    exit;
                }
                
                if (strlen($newPassword) < 6) {
                    header('Location: index.php?page=profile&error=New password must be at least 6 characters');
                    exit;
                }
            }
            
            // Update profile using Auth class
            $success = $this->auth->updateUserProfile($userId, $fullName, $email, $currentPassword, $newPassword);
            
            if ($success) {
                // Update session data
                $_SESSION['user_full_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                
                header('Location: index.php?page=profile&success=Profile updated successfully');
            } else {
                header('Location: index.php?page=profile&error=Failed to update profile');
            }
            exit;
        }
    }
    
    public function users() {
        $this->requireAuthentication();
        $this->requireAdminRole();
        
        // Get users from admin_users table using Auth class
        $users = $this->auth->getAllUsers();
        $currentUser = $this->auth->getCurrentUser();
        
        include 'views/users.php';
    }
    
    public function createUser() {
        $this->requireAuthentication();
        $this->requireAdminRole();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'jury';
            
            if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
                header('Location: index.php?page=users&error=Please fill in all fields');
                exit;
            }
            
            $success = $this->auth->createUser($username, $email, $password, $fullName, $role);
            
            if ($success) {
                header('Location: index.php?page=users&success=User created successfully');
            } else {
                header('Location: index.php?page=users&error=Failed to create user or user already exists');
            }
            exit;
        }
    }
    
    public function exports() {
        $this->requireAuthentication();
        
        $type = $_GET['type'] ?? '';
        
        switch ($type) {
            case 'registrations':
                $this->exportRegistrations();
                break;
            case 'submissions':
                $this->exportSubmissions();
                break;
            default:
                header('Location: index.php?page=dashboard&error=Invalid export type');
                exit;
        }
    }
    
    private function exportRegistrations() {
        $registrations = $this->registration->getAllRegistrations(null, 0, '');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="greater_registrations_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'Full Name', 'Birth Date', 'Nationality', 'ID Number', 
            'Email', 'Phone', 'Category', 'User Code', 'Registration Date'
        ]);
        
        // CSV data
        foreach ($registrations as $reg) {
            fputcsv($output, [
                $reg['id'],
                $reg['fullName'],
                $reg['birthDate'],
                $reg['nationality'],
                $reg['idNumber'],
                $reg['email'],
                $reg['phone'],
                $reg['category'],
                $reg['userCode'],
                $reg['registrationDate']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportSubmissions() {
        $submissions = $this->submission->getAllSubmissions(null, 0, '', '');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="greater_submissions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'User Code', 'User Name', 'Email', 'Category', 
            'Artwork Name', 'Status', 'Score', 'Submission Date'
        ]);
        
        // CSV data
        foreach ($submissions as $sub) {
            fputcsv($output, [
                $sub['id'],
                $sub['userCode'],
                $sub['userName'],
                $sub['userEmail'],
                $sub['category'],
                $sub['artworkName'],
                $sub['status'],
                $sub['score'],
                $sub['submissionDate']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function requireAuthentication() {
        if (!$this->auth->isAuthenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: login.php');
            exit;
        }
    }
    
    private function requireAdminRole() {
        $currentUser = $this->auth->getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            header('Location: index.php?page=dashboard&error=Access denied. Admin privileges required.');
            exit;
        }
    }
}

// Router
$controller = new AdminController();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'login':
        $controller->login();
        break;
    case 'authenticate':
        $controller->authenticate();
        break;
    case 'logout':
        $controller->logout();
        break;
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
    case 'profile':
        $controller->profile();
        break;
    case 'update_profile':
        $controller->updateProfile();
        break;
    case 'users':
        $controller->users();
        break;
    case 'create_user':
        $controller->createUser();
        break;
    case 'exports':
        $controller->exports();
        break;
    default:
        $controller->dashboard();
        break;
}
?>