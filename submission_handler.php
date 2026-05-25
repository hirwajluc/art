<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Function to log errors
function logError($message) {
    error_log("[Submission Handler] " . date('Y-m-d H:i:s') . " - " . $message);
}

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
    }

    // Check if user is authenticated
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        sendResponse(false, 'User not authenticated');
    }

    // Check session validity (24 hours)
    if (isset($_SESSION['auth_time']) && (time() - $_SESSION['auth_time']) > (24 * 60 * 60)) {
        session_destroy();
        sendResponse(false, 'Session expired. Please login again.');
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        sendResponse(false, 'Invalid security token. Please refresh the page and try again.');
    }

    // Validate required fields
    $required_fields = ['userCode', 'category', 'artworkName', 'description'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            sendResponse(false, "Missing required field: $field");
        }
    }

    // Sanitize and validate input data
    $userCode = trim($_POST['userCode']);
    $category = trim($_POST['category']);
    $artworkName = trim($_POST['artworkName']);
    $description = trim($_POST['description']);

    // Validate user code matches session
    if ($userCode !== $_SESSION['user_code']) {
        sendResponse(false, 'Invalid user code');
    }

    // Validate category
    $valid_categories = ['photography_paint', 'short_video'];
    if (!in_array($category, $valid_categories)) {
        sendResponse(false, 'Invalid category');
    }

    // Validate artwork name length
    if (strlen($artworkName) > 100) {
        sendResponse(false, 'Artwork name is too long (maximum 100 characters)');
    }

    // Validate description length
    if (strlen($description) < 50 || strlen($description) > 1000) {
        sendResponse(false, 'Description must be between 50 and 1000 characters');
    }

    // Check if file was uploaded
    if (!isset($_FILES['artworkFile']) || $_FILES['artworkFile']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Server error: cannot write file',
            UPLOAD_ERR_EXTENSION => 'Server error: upload blocked by extension'
        ];
        
        $error_code = $_FILES['artworkFile']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Unknown upload error';
        sendResponse(false, "File upload error: $error_message");
    }

    $uploadedFile = $_FILES['artworkFile'];
    $originalFileName = $uploadedFile['name'];
    $fileSize = $uploadedFile['size'];
    $fileTmpPath = $uploadedFile['tmp_name'];
    $fileType = $uploadedFile['type'];

    // Validate file type and size based on category
    if ($category === 'photography_paint') {
        // Image validation
        $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/tiff'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($fileType, $allowedImageTypes)) {
            sendResponse(false, 'Invalid file type. Please upload JPG, PNG, or TIFF images only.');
        }
        
        // Additional check using getimagesize for security
        $imageInfo = getimagesize($fileTmpPath);
        if ($imageInfo === false) {
            sendResponse(false, 'Invalid image file. File may be corrupted.');
        }
        
    } else if ($category === 'short_video') {
        // Video validation
        $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
        $maxSize = 500 * 1024 * 1024; // 500MB
        
        if (!in_array($fileType, $allowedVideoTypes)) {
            sendResponse(false, 'Invalid file type. Please upload MP4, MOV, or AVI videos only.');
        }
    }

    // Check file size
    if ($fileSize > $maxSize) {
        $maxSizeMB = $maxSize / (1024 * 1024);
        sendResponse(false, "File size exceeds {$maxSizeMB}MB limit");
    }

    // Check if file is actually uploaded
    if (!is_uploaded_file($fileTmpPath)) {
        sendResponse(false, 'Security error: Invalid file upload');
    }

    // Connect to database
    require_once 'db.php';

    // Check submission deadline
    try {
        $dStmt = $pdo->prepare("SELECT setting_value FROM competition_settings WHERE setting_key = 'submission_deadline'");
        $dStmt->execute();
        $deadlineVal = $dStmt->fetchColumn();
        if ($deadlineVal && strtotime($deadlineVal) < time()) {
            sendResponse(false, 'The submission deadline has passed. New submissions are no longer accepted.');
        }
    } catch (PDOException $e) {
        // Table may not exist yet – allow submission
    }

    // Check if user has already submitted
    $stmt = $pdo->prepare("SELECT id FROM submissions WHERE userCode = ?");
    $stmt->execute([$userCode]);
    if ($stmt->fetch()) {
        sendResponse(false, 'You have already submitted an artwork for this competition');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            logError("Failed to create upload directory: $uploadDir");
            sendResponse(false, 'Server error: Cannot create upload directory');
        }
    }

    // Generate unique filename
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $newFileName = $userCode . '_' . uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFileName;

    // Move uploaded file
    if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
        logError("Failed to move uploaded file from $fileTmpPath to $uploadPath");
        sendResponse(false, 'Server error: Failed to save uploaded file');
    }

    // Insert submission into database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO submissions (
                userCode, userName, userEmail, category, artworkName, 
                description, fileName, originalFileName, fileSize, 
                fileType, filePath, submissionDate, ipAddress
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        
        $result = $stmt->execute([
            $userCode,
            $_SESSION['user_name'],
            $_SESSION['user_email'],
            $category,
            $artworkName,
            $description,
            $newFileName,
            $originalFileName,
            $fileSize,
            $fileType,
            $uploadPath,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        if (!$result) {
            // If database insert fails, delete the uploaded file
            unlink($uploadPath);
            sendResponse(false, 'Database error: Failed to save submission');
        }

        $submissionId = $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        // If database error, delete the uploaded file
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        logError("Database error: " . $e->getMessage());
        sendResponse(false, 'Database error: Failed to save submission');
    }

    // Send confirmation email
    $emailSent = sendConfirmationEmail(
        $_SESSION['user_email'],
        $_SESSION['user_name'],
        $artworkName,
        $category,
        $submissionId
    );

    if (!$emailSent) {
        logError("Failed to send confirmation email to " . $_SESSION['user_email']);
        // Don't fail the submission if email fails, just log it
    }

    // Log successful submission
    logError("Successful submission: User $userCode, Artwork '$artworkName', File: $newFileName");

    sendResponse(true, 'Artwork submitted successfully! You will receive a confirmation email shortly.', [
        'submissionId' => $submissionId,
        'artworkName' => $artworkName,
        'emailSent' => $emailSent
    ]);

} catch (Exception $e) {
    logError("Unexpected error: " . $e->getMessage());
    sendResponse(false, 'An unexpected error occurred. Please try again.');
}

function sendConfirmationEmail($email, $name, $artworkName, $category, $submissionId) {
    try {
        $subject = "Artwork Submission Confirmation - Greater Art Competition 2025";
        
        $categoryDisplay = $category === 'photography_paint' ? 'Photography & Paint' : 'Short Video';
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .highlight { color: #667eea; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Submission Confirmed!</h1>
                    <p>Greater Art Competition 2025</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    
                    <p>Thank you for submitting your artwork to the Greater Art Competition 2025! We have successfully received your submission.</p>
                    
                    <div class='details'>
                        <h3>Submission Details:</h3>
                        <p><strong>Submission ID:</strong> <span class='highlight'>#" . str_pad($submissionId, 6, '0', STR_PAD_LEFT) . "</span></p>
                        <p><strong>Artwork Name:</strong> " . htmlspecialchars($artworkName) . "</p>
                        <p><strong>Category:</strong> " . htmlspecialchars($categoryDisplay) . "</p>
                        <p><strong>Submitted:</strong> " . date('F j, Y \a\t g:i A') . "</p>
                        <p><strong>Status:</strong> <span style='color: #28a745;'>✅ Received</span></p>
                    </div>
                    
                    <h3>What's Next?</h3>
                    <ul>
                        <li>Our panel will review all submissions</li>
                        <li>Winners will be announced on our website</li>
                        <li>You'll receive an email notification if you're selected</li>
                    </ul>
                    
                    <p><strong>Important:</strong> Please keep this email as confirmation of your submission. Your submission ID is <span class='highlight'>#" . str_pad($submissionId, 6, '0', STR_PAD_LEFT) . "</span></p>
                    
                    <p>If you have any questions, please don't hesitate to contact us at <a href='mailto:info@greaterproject.eu'>info@greaterproject.eu</a></p>
                    
                    <p>Best of luck with your submission!</p>
                    
                    <div class='footer'>
                        <p>Greater Art Competition 2025<br>
                        <a href='mailto:info@greaterproject.eu'>info@greaterproject.eu</a></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Greater Art Competition <info@greaterproject.eu>" . "\r\n";
        $headers .= "Reply-To: info@greaterproject.eu" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return mail($email, $subject, $message, $headers);
        
    } catch (Exception $e) {
        logError("Email sending error: " . $e->getMessage());
        return false;
    }
}
?>