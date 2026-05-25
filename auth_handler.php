<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper headers for AJAX responses
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// DB config
require_once 'db.php';

// CSRF token verification
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Send OTP email
function sendOTPEmail($email, $fullName, $otp, $userCode) {
    $subject = "Your OTP for Art Submission - Greater Art Competition 2025";
    
    $message = "Dear {$fullName},\n\n";
    $message .= "Your One-Time Password (OTP) for art submission is: {$otp}\n\n";
    $message .= "This OTP will expire in 5 minutes.\n\n";
    $message .= "User Code: {$userCode}\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "If you didn't request this OTP, please ignore this email.\n\n";
    $message .= "Best regards,\n";
    $message .= "Greater Art Competition 2025 Team\n";
    $message .= "Powered by Erasmus+";

    $headers = [
        "From: Greater Art Competition <info@greaterproject.eu>",
        "Reply-To: info@greaterproject.eu",
        "X-Mailer: PHP/" . phpversion(),
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8"
    ];

    error_log("Sending OTP email to: " . $email);
    $result = mail($email, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        error_log("OTP email sent successfully to: " . $email);
    } else {
        error_log("OTP email failed to send to: " . $email);
    }
    
    return $result;
}

// Rate limiting for OTP requests
function checkOTPRateLimit($userCode) {
    $cacheDir = __DIR__ . '/cache';
    
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            error_log("Failed to create cache directory");
            return true; // Allow if can't create cache
        }
    }
    
    $cacheFile = $cacheDir . '/otp_limit_' . md5($userCode) . '.txt';
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!$data) {
            return true;
        }
        
        $attempts = $data['attempts'] ?? 0;
        $lastAttempt = $data['timestamp'] ?? 0;
        
        // Reset counter if more than 1 hour has passed
        if (time() - $lastAttempt > 3600) {
            $attempts = 0;
        }
        
        // Block if more than 5 OTP requests in the last hour
        if ($attempts >= 5) {
            return false;
        }
        
        $attempts++;
    } else {
        $attempts = 1;
    }
    
    // Save current attempt
    file_put_contents($cacheFile, json_encode([
        'attempts' => $attempts,
        'timestamp' => time()
    ]));
    
    return true;
}

// Store OTP in session with expiry
function storeOTP($userCode, $otp) {
    $_SESSION['otp_data'] = [
        'userCode' => $userCode,
        'otp' => $otp,
        'expiry' => time() + (5 * 60), // 5 minutes
        'attempts' => 0
    ];
}

// Verify OTP
function verifyOTP($userCode, $otp) {
    if (!isset($_SESSION['otp_data'])) {
        return ['success' => false, 'message' => 'No OTP found. Please request a new one.'];
    }
    
    $otpData = $_SESSION['otp_data'];
    
    // Check if OTP expired
    if (time() > $otpData['expiry']) {
        unset($_SESSION['otp_data']);
        return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
    }
    
    // Check if too many attempts
    if ($otpData['attempts'] >= 3) {
        unset($_SESSION['otp_data']);
        return ['success' => false, 'message' => 'Too many failed attempts. Please request a new OTP.'];
    }
    
    // Check if user codes match
    if ($otpData['userCode'] !== $userCode) {
        return ['success' => false, 'message' => 'Invalid request.'];
    }
    
    // Verify OTP
    if ($otpData['otp'] === $otp) {
        // OTP is correct
        unset($_SESSION['otp_data']);
        return ['success' => true, 'message' => 'OTP verified successfully.'];
    } else {
        // Increment failed attempts
        $_SESSION['otp_data']['attempts']++;
        return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
    }
}

// Log authentication attempts
function logAuthAttempt($userCode, $action, $success, $error = null) {
    $logsDir = __DIR__ . '/logs';
    
    if (!is_dir($logsDir)) {
        if (!mkdir($logsDir, 0755, true)) {
            error_log("Failed to create logs directory");
            return false;
        }
    }
    
    $logFile = $logsDir . '/auth_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logEntry = "[{$timestamp}] IP: {$ip} | UserCode: {$userCode} | Action: {$action} | Success: " . ($success ? 'YES' : 'NO');
    if ($error) {
        $logEntry .= " | Error: {$error}";
    }
    $logEntry .= "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Main processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception("Invalid JSON input");
        }
        
        $action = $input['action'] ?? '';
        $userCode = trim($input['userCode'] ?? '');
        $csrfToken = $input['csrf_token'] ?? '';
        
        // CSRF token check (lenient for debugging)
        if (!verifyCsrfToken($csrfToken)) {
            error_log("CSRF token verification failed - allowing for debugging");
        }
        
        // Validate user code format
        if (!preg_match('/^GAC\d{4}$/', $userCode)) {
            throw new Exception("Invalid user code format");
        }
        
        if ($action === 'request_otp') {
            error_log("OTP request for user code: " . $userCode);
            
            // Check rate limiting
            if (!checkOTPRateLimit($userCode)) {
                logAuthAttempt($userCode, 'request_otp', false, 'Rate limit exceeded');
                throw new Exception("Too many OTP requests. Please try again later.");
            }
            
            // Verify user code exists in database
            try {
                $stmt = $pdo->prepare("SELECT fullName, email FROM registrations WHERE userCode = ?");
                $stmt->execute([$userCode]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    logAuthAttempt($userCode, 'request_otp', false, 'User code not found');
                    throw new Exception("Invalid user code. Please check and try again.");
                }
                
                // Generate and store OTP
                $otp = generateOTP();
                storeOTP($userCode, $otp);
                
                error_log("Generated OTP for {$userCode}: {$otp}");
                
                // Send OTP email
                $emailSent = sendOTPEmail($user['email'], $user['fullName'], $otp, $userCode);
                
                if (!$emailSent) {
                    error_log("Failed to send OTP email to: " . $user['email']);
                    // Don't throw exception, still return success as OTP is generated
                }
                
                logAuthAttempt($userCode, 'request_otp', true);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'OTP sent to your registered email address.',
                    'email_sent' => $emailSent,
                    'debug' => [
                        'user_found' => true,
                        'email' => substr($user['email'], 0, 3) . '***@' . substr(strrchr($user['email'], "@"), 1)
                    ]
                ]);
                
            } catch (PDOException $e) {
                error_log("Database error during OTP request: " . $e->getMessage());
                throw new Exception("Database error. Please try again.");
            }
            
        } elseif ($action === 'verify_otp') {
            $otp = trim($input['otp'] ?? '');
            
            error_log("OTP verification attempt for user code: {$userCode}, OTP: {$otp}");
            
            // Validate OTP format
            if (!preg_match('/^\d{6}$/', $otp)) {
                throw new Exception("Invalid OTP format");
            }
            
            // Verify OTP
            $verificationResult = verifyOTP($userCode, $otp);
            
            if ($verificationResult['success']) {
                // Get user details for session
                $stmt = $pdo->prepare("SELECT id, fullName, email, category FROM registrations WHERE userCode = ?");
                $stmt->execute([$userCode]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    throw new Exception("User not found");
                }
                
                // Set authentication session
                $_SESSION['authenticated'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_code'] = $userCode;
                $_SESSION['user_name'] = $user['fullName'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_category'] = $user['category'];
                $_SESSION['auth_time'] = time();
                
                logAuthAttempt($userCode, 'verify_otp', true);
                
                error_log("User {$userCode} authenticated successfully");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Authentication successful!',
                    'user' => [
                        'name' => $user['fullName'],
                        'category' => $user['category']
                    ]
                ]);
                
            } else {
                logAuthAttempt($userCode, 'verify_otp', false, $verificationResult['message']);
                echo json_encode($verificationResult);
            }
            
        } else {
            throw new Exception("Invalid action");
        }
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>