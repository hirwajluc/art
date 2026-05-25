<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB config - update with your actual credentials
require_once 'db.php';

// CSRF token generation and verification
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Generate unique user code starting with GAC + 4 digit number (0001 to 9999)
function generateUserCode($pdo) {
    $maxAttempts = 100;
    $prefix = "GAC";
    
    for ($i = 0; $i < $maxAttempts; $i++) {
        $number = random_int(1, 9999);
        $code = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE userCode = ?");
            $stmt->execute([$code]);
            
            if ($stmt->fetchColumn() == 0) {
                return $code;
            }
        } catch (PDOException $e) {
            error_log("Error checking user code: " . $e->getMessage());
            throw new Exception("Database error while generating user code");
        }
    }
    throw new Exception("Failed to generate unique user code after {$maxAttempts} attempts");
}

// Sanitize and validate input function
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Enhanced email validation
function validateEmail($email) {
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$email) return false;
    
    $domain = substr(strrchr($email, "@"), 1);
    $disposable_domains = ['10minutemail.com', 'tempmail.org', 'guerrillamail.com'];
    
    return !in_array(strtolower($domain), $disposable_domains) ? $email : false;
}

// Age validation function
function validateAge($birthDate) {
    try {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        $age = $today->diff($birth)->y;
        
        return $age >= 13 && $age <= 120;
    } catch (Exception $e) {
        return false;
    }
}

// Enhanced phone validation
function validatePhone($phone) {
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    
    if (preg_match('/^\+?[1-9]\d{9,14}$/', $cleaned)) {
        return $cleaned;
    }
    return false;
}

// Send registration email with user code
function sendRegistrationEmail($email, $fullName, $userCode, $category) {
    $subject = "Your Greater Art Competition 2025 Registration";
    
    $categoryText = $category === 'photography_paint' ? 'Photography & Paint' : 'Short Video';
    
    $message = "Dear {$fullName},\n\n";
    $message .= "Thank you for registering for the Greater Art Competition 2025!\n\n";
    $message .= "Registration Details:\n";
    $message .= "- Category: {$categoryText}\n";
    $message .= "- Your unique user code: {$userCode}\n\n";
    $message .= "IMPORTANT: Please save this code safely. You will need it to:\n";
    $message .= "- Submit your artwork\n";
    $message .= "- Check your submission status\n";
    $message .= "- Access competition updates\n\n";
    $message .= "Next Steps:\n";
    $message .= "1. Use your user code to submit your artwork\n";
    $message .= "2. Follow submission guidelines for your category\n";
    $message .= "3. Submit before the deadline\n\n";
    $message .= "Competition Guidelines:\n";
    $message .= "- Photography & Paint: Submit high-resolution images (min 300 DPI)\n";
    $message .= "- Short Video: Maximum 3 minutes, MP4 format preferred\n\n";
    $message .= "For support, contact us at: info@greaterproject.eu\n\n";
    $message .= "Good luck with your submission!\n\n";
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

    // Log email attempt
    error_log("Attempting to send email to: " . $email);
    
    $result = mail($email, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        error_log("Email sent successfully to: " . $email);
    } else {
        error_log("Email failed to send to: " . $email);
        error_log("Last error: " . (error_get_last()['message'] ?? 'Unknown error'));
    }
    
    return $result;
}

// Log registration attempt for security monitoring
function logRegistrationAttempt($email, $success, $error = null) {
    $logsDir = __DIR__ . '/logs';
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logsDir)) {
        if (!mkdir($logsDir, 0755, true)) {
            error_log("Failed to create logs directory");
            return false;
        }
    }
    
    $logFile = $logsDir . '/registration_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "[{$timestamp}] IP: {$ip} | Email: {$email} | Success: " . ($success ? 'YES' : 'NO');
    if ($error) {
        $logEntry .= " | Error: {$error}";
    }
    $logEntry .= " | User-Agent: {$userAgent}\n";
    
    if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
        error_log("Failed to write to log file: " . $logFile);
        return false;
    }
    
    return true;
}

// Rate limiting check
function checkRateLimit($email) {
    //return true;
    $cacheDir = __DIR__ . '/cache';
    
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true)) {
            error_log("Failed to create cache directory");
            return true; // Allow if can't create cache
        }
    }
    
    $cacheFile = $cacheDir . '/rate_limit_' . md5($email) . '.txt';
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!$data) {
            return true; // Allow if can't read cache
        }
        
        $attempts = $data['attempts'] ?? 0;
        $lastAttempt = $data['timestamp'] ?? 0;
        
        // Reset counter if more than 1 hour has passed
        if (time() - $lastAttempt > 3600) {
            $attempts = 0;
        }
        
        // Block if more than 3 attempts in the last hour
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

// Check if request is AJAX
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Send JSON response
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    echo json_encode($data);
    exit;
}

// Main processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("=== REGISTRATION ATTEMPT STARTED ===");

        // Get JSON input for AJAX requests
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $_POST = $input;
            error_log("JSON input detected and processed");
        }

        // ── Check registration deadline ────────────────────────────────────
        try {
            require_once __DIR__ . '/db.php';
            $dStmt = $pdo->prepare("SELECT setting_value FROM competition_settings WHERE setting_key = 'registration_deadline'");
            $dStmt->execute();
            $regDeadline = $dStmt->fetchColumn();
            if ($regDeadline && strtotime($regDeadline) < time()) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Registration is now closed. The registration deadline has passed.'
                ]);
            }
        } catch (PDOException $e) {
            // competition_settings table may not exist yet – allow registration
            error_log("Deadline check skipped (table may not exist): " . $e->getMessage());
        }

        // Debug: Log all received data
        error_log("Raw POST data: " . json_encode($_POST));

        // Clean and validate inputs
        $fullName = cleanInput($_POST['fullName'] ?? '');
        $birthDate = $_POST['birthDate'] ?? '';
        $nationality = cleanInput($_POST['nationality'] ?? '');
        $idNumber = cleanInput($_POST['idNumber'] ?? '');
        $email = validateEmail($_POST['email'] ?? '');
        $phone = validatePhone($_POST['phone'] ?? '');
        $category = $_POST['category'] ?? '';

        // Create cleaned data array for debugging
        $cleanedData = [
            'fullName' => $fullName,
            'birthDate' => $birthDate,
            'nationality' => $nationality,
            'idNumber' => $idNumber,
            'email' => $email,
            'phone' => $phone,
            'category' => $category
        ];

        error_log("CLEANED DATA FOR SAVING: " . json_encode($cleanedData, JSON_PRETTY_PRINT));

        // CSRF token check (more lenient for debugging)
        if (empty($_POST['csrf_token'])) {
            error_log("WARNING: CSRF token missing - allowing for debugging");
        } elseif (!verifyCsrfToken($_POST['csrf_token'])) {
            error_log("WARNING: CSRF token invalid - allowing for debugging");
        } else {
            error_log("CSRF token validated successfully");
        }

        // Comprehensive validation
        $errors = [];

        // Full name validation
        if (!$fullName) {
            $errors[] = "Full name is required.";
        } elseif (strlen($fullName) < 2) {
            $errors[] = "Full name must be at least 2 characters long.";
        } elseif (strlen($fullName) > 100) {
            $errors[] = "Full name is too long.";
        } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/u', $fullName)) {
            $errors[] = "Full name contains invalid characters.";
        }

        // Birth date validation
        if (!$birthDate) {
            $errors[] = "Date of birth is required.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
            $errors[] = "Invalid date format.";
        } elseif (!validateAge($birthDate)) {
            $errors[] = "You must be at least 13 years old to participate.";
        }

        // Nationality validation
        if (!$nationality) {
            $errors[] = "Nationality is required.";
        } elseif (!preg_match('/^[a-zA-Z\s]+$/u', $nationality)) {
            $errors[] = "Nationality contains invalid characters.";
        }

        // ID number validation
        if (!$idNumber) {
            $errors[] = "ID or Passport number is required.";
        } elseif (strlen($idNumber) < 5 || strlen($idNumber) > 20) {
            $errors[] = "ID number must be between 5 and 20 characters.";
        }

        // Email validation
        if (!$email) {
            $errors[] = "Valid email address is required.";
        } else {
            // Check for duplicate email
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "This email address is already registered.";
                }
            } catch (PDOException $e) {
                error_log("Error checking duplicate email: " . $e->getMessage());
                $errors[] = "Database error while checking email.";
            }
            
            // Rate limiting check
            if (!checkRateLimit($email)) {
                $errors[] = "Too many registration attempts. Please try again later.";
            }
        }

        // Phone validation
        if (!$phone) {
            $errors[] = "Valid phone number is required.";
        }

        // Category validation
        if (!$category || !in_array($category, ['photography_paint', 'short_video'])) {
            $errors[] = "Please select a valid competition category.";
        }

        // Check for duplicate ID number
        if (!$errors && $idNumber) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE idNumber = ?");
                $stmt->execute([$idNumber]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "This ID/Passport number is already registered.";
                }
            } catch (PDOException $e) {
                error_log("Error checking duplicate ID: " . $e->getMessage());
                $errors[] = "Database error while checking ID number.";
            }
        }

        if (count($errors) > 0) {
            error_log("VALIDATION ERRORS: " . implode('; ', $errors));
            logRegistrationAttempt($email ?: 'unknown', false, implode('; ', $errors));
            
            // Create a single error message from all errors - FIXED FORMAT
            $errorMessage = "Please correct the following errors:\n• " . implode("\n• ", $errors);
            
            sendJsonResponse([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $errors,
                'debug_data' => $cleanedData
            ]);
        }

        error_log("All validations passed - proceeding to database insert");

        // Generate unique user code
        $userCode = generateUserCode($pdo);
        error_log("Generated user code: " . $userCode);

        // Prepare data for database insertion
        $dbData = [
            $fullName,
            $birthDate,
            $nationality,
            $idNumber,
            $email,
            $phone,
            $category,
            $userCode,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];

        error_log("DATA TO BE INSERTED: " . json_encode([
            'fullName' => $fullName,
            'birthDate' => $birthDate,
            'nationality' => $nationality,
            'idNumber' => $idNumber,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'userCode' => $userCode,
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ], JSON_PRETTY_PRINT));

        // Begin transaction for data integrity
        $pdo->beginTransaction();
        error_log("Database transaction started");

        try {
            // Insert registration into database
            $sql = "INSERT INTO registrations 
                (fullName, birthDate, nationality, idNumber, email, phone, category, userCode, registrationDate, ipAddress) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            error_log("SQL Query: " . $sql);
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($dbData);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL execution failed: " . json_encode($errorInfo));
                throw new Exception("Failed to insert registration data: " . $errorInfo[2]);
            }

            $registrationId = $pdo->lastInsertId();
            error_log("Registration inserted successfully with ID: " . $registrationId);

            // Verify the insertion
            $verifyStmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
            $verifyStmt->execute([$registrationId]);
            $insertedRecord = $verifyStmt->fetch();
            
            if ($insertedRecord) {
                error_log("VERIFIED INSERTED RECORD: " . json_encode($insertedRecord));
            } else {
                error_log("WARNING: Could not verify inserted record");
            }

            // Commit transaction
            $pdo->commit();
            error_log("Database transaction committed successfully");

            // Send confirmation email
            error_log("Attempting to send confirmation email...");
            $emailSent = sendRegistrationEmail($email, $fullName, $userCode, $category);
            error_log("Email sending result: " . ($emailSent ? 'SUCCESS' : 'FAILED'));

            // Log successful registration
            logRegistrationAttempt($email, true);

            error_log("=== REGISTRATION COMPLETED SUCCESSFULLY ===");

            // Return success response - FIXED FORMAT
            sendJsonResponse([
                'success' => true,
                'message' => 'Registration successful! Check your email for your user code.',
                'userCode' => $userCode,
                'emailSent' => $emailSent,
                'details' => [
                    'name' => $fullName,
                    'category' => $category,
                    'registrationTime' => date('Y-m-d H:i:s'),
                    'registrationId' => $registrationId
                ]
            ]);

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            error_log("Database transaction rolled back due to error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw new Exception("Database error during registration: " . $e->getMessage());
        }

    } catch (Exception $e) {
        error_log("REGISTRATION FAILED: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        logRegistrationAttempt($_POST['email'] ?? 'unknown', false, $e->getMessage());
        
        sendJsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => [$e->getMessage()]
        ]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['csrf_token'])) {
    // Return CSRF token for AJAX requests
    sendJsonResponse([
        'csrf_token' => $_SESSION['csrf_token']
    ]);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['debug'])) {
    // Debug endpoint to check database status
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM registrations");
        $count = $stmt->fetch();
        
        $stmt = $pdo->query("SELECT * FROM registrations ORDER BY registrationDate DESC LIMIT 5");
        $recent = $stmt->fetchAll();
        
        sendJsonResponse([
            'database_status' => 'connected',
            'total_registrations' => $count['total'],
            'recent_registrations' => $recent,
            'server_time' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        sendJsonResponse([
            'database_status' => 'error',
            'error' => $e->getMessage()
        ]);
    }
    
} else {
    // Invalid request method or regular page load
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // This is a regular page load, not an AJAX request
        // You can redirect to the registration form or show a simple message
        header('Location: registration.php');
        exit;
    } else {
        // Invalid request method for API calls
        http_response_code(405);
        sendJsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
}
?>