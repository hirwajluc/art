<?php
// Include the Database class
require_once __DIR__ . '/config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        // Get database connection using your Database class
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Initialize database connection if needed (removed - not needed anymore)
     */
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Alias for isAuthenticated() - for compatibility
     */
    public function check() {
        return $this->isAuthenticated();
    }
    
    /**
     * Static method to check authentication
     */
    public static function checkStatic() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $rememberMe = false) {
        try {
            if (!$this->db) {
                return ['success' => false, 'message' => 'Database connection failed'];
            }
            
            $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND status = 'active'");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                // Log activity
                $this->logActivity($user['id'], 'LOGIN', 'User logged in successfully');
                
                // Handle remember me
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    
                    // Store token in database
                    $stmt = $this->db->prepare("INSERT INTO admin_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60))]);
                }
                
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                // Log failed attempt
                if ($user) {
                    $this->logActivity($user['id'], 'LOGIN_FAILED', 'Failed login attempt');
                }
                return ['success' => false, 'message' => 'Invalid username/email or password'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isAuthenticated()) {
            $this->logActivity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
        }
        
        // Clear remember me cookie and database token
        if (isset($_COOKIE['remember_token'])) {
            $stmt = $this->db->prepare("DELETE FROM admin_sessions WHERE session_token = ?");
            $stmt->execute([$_COOKIE['remember_token']]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        session_start(); // Restart for flash messages
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['user_username'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'full_name' => $_SESSION['user_full_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'jury',
            'login_time' => $_SESSION['login_time'] ?? time()
        ];
    }
    
    /**
     * Alias for getCurrentUser() - for compatibility
     */
    public function user() {
        return $this->getCurrentUser();
    }
    
    /**
     * Static method to get current user (for use in views)
     */
    public static function getCurrentUserStatic() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['user_username'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'full_name' => $_SESSION['user_full_name'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'jury',
            'login_time' => $_SESSION['login_time'] ?? time()
        ];
    }
    
    /**
     * Check if user has admin role
     */
    public function isAdmin() {
        return $this->isAuthenticated() && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Update user profile
     */
    public function updateUserProfile($userId, $fullName, $email, $currentPassword = '', $newPassword = '') {
        try {
            // If changing password, verify current password first
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    return false;
                }
                
                $stmt = $this->db->prepare("SELECT password FROM admin_users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user || !password_verify($currentPassword, $user['password'])) {
                    return false;
                }
                
                // Update with new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE admin_users SET full_name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$fullName, $email, $hashedPassword, $userId]);
            } else {
                // Update without password change
                $stmt = $this->db->prepare("UPDATE admin_users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$fullName, $email, $userId]);
            }
            
            if ($result) {
                $this->logActivity($userId, 'PROFILE_UPDATED', 'User updated profile information');
            }
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Create new user (admin only)
     */
    public function createUser($username, $email, $password, $fullName, $role = 'jury') {
        try {
            // Check if username or email already exists
            $stmt = $this->db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                return false; // User already exists
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO admin_users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashedPassword, $fullName, $role]);
            
            if ($result) {
                $this->logActivity($_SESSION['user_id'], 'USER_CREATED', "Created new $role user: $username");
            }
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all users (admin only)
     */
    public function getAllUsers() {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, full_name, role, status, created_at FROM admin_users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Check remember me token
     */
    public function checkRememberToken() {
        if (isset($_COOKIE['remember_token']) && !$this->isAuthenticated()) {
            $token = $_COOKIE['remember_token'];
            
            $stmt = $this->db->prepare("
                SELECT u.* FROM admin_users u 
                JOIN admin_sessions s ON u.id = s.user_id 
                WHERE s.session_token = ? AND s.expires_at > NOW() AND u.status = 'active'
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Auto login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                return true;
            } else {
                // Invalid token, remove cookie
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
        
        return false;
    }
    
    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description = '') {
        try {
            // Check if database connection is available
            if (!$this->db) {
                return; // Silently fail if no database connection
            }
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->db->prepare("INSERT INTO admin_activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $description, $ipAddress]);
        } catch (Exception $e) {
            // Log error silently - don't break the main flow
        }
    }
}
?>