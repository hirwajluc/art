<?php
/**
 * Update Submission Handler
 * Allows authenticated participants to upload a new version of their artwork
 * as long as the submission deadline has not passed.
 */
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0); // never expose errors in JSON API

function sendResponse(bool $success, string $message, array $data = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function logMsg(string $msg): void {
    error_log("[UpdateSubmission] " . date('Y-m-d H:i:s') . " - $msg");
}

// ── Auth ──────────────────────────────────────────────────────────────────────
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    sendResponse(false, 'Not authenticated. Please log in again.');
}
if (isset($_SESSION['auth_time']) && (time() - $_SESSION['auth_time']) > 86400) {
    session_destroy();
    sendResponse(false, 'Session expired. Please log in again.');
}

// ── Method ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// ── Detect post_max_size overflow ─────────────────────────────────────────────
// When the upload body exceeds post_max_size PHP silently empties $_POST and
// $_FILES, making the CSRF check below produce a misleading error message.
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
    $postMaxBytes = (int) preg_replace_callback(
        '/^\s*(\d+)\s*([kmg]?)\s*$/i',
        function($m) {
            $units = ['k' => 1024, 'm' => 1048576, 'g' => 1073741824];
            return $m[1] * ($units[strtolower($m[2])] ?? 1);
        },
        ini_get('post_max_size')
    );
    $maxMB = round($postMaxBytes / 1048576);
    sendResponse(false, "Your file is too large for the server to accept (server limit: {$maxMB}MB). Please contact info@greaterproject.eu if your file is within the allowed size.");
}

// ── CSRF ──────────────────────────────────────────────────────────────────────
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    sendResponse(false, 'Invalid security token. Please refresh and try again.');
}

// ── DB ────────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/db.php';

// ── Check submission deadline ─────────────────────────────────────────────────
try {
    $dStmt = $pdo->prepare("SELECT setting_value FROM competition_settings WHERE setting_key = 'submission_deadline'");
    $dStmt->execute();
    $deadlineRow = $dStmt->fetchColumn();
    if ($deadlineRow && strtotime($deadlineRow) < time()) {
        sendResponse(false, 'The submission deadline has passed. No more updates are accepted.');
    }
} catch (PDOException $e) {
    // If settings table doesn't exist yet, allow submission
    logMsg("Deadline check failed (table may not exist yet): " . $e->getMessage());
}

// ── Input validation ──────────────────────────────────────────────────────────
$userCode    = trim($_POST['userCode']    ?? '');
$artworkName = trim($_POST['artworkName'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($userCode) || empty($artworkName) || empty($description)) {
    sendResponse(false, 'All fields are required.');
}
if ($userCode !== ($_SESSION['user_code'] ?? '')) {
    sendResponse(false, 'Invalid user code.');
}
if (strlen($artworkName) > 100) {
    sendResponse(false, 'Artwork name is too long (max 100 characters).');
}
if (strlen($description) < 20 || strlen($description) > 1000) {
    sendResponse(false, 'Description must be between 20 and 1000 characters.');
}

// ── Fetch existing submission ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE userCode = ? LIMIT 1");
$stmt->execute([$userCode]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
    sendResponse(false, 'No existing submission found for your account. Please use the main submission form.');
}

$submissionId = (int)$existing['id'];
$category     = $existing['category'];

// ── File upload ───────────────────────────────────────────────────────────────
if (!isset($_FILES['artworkFile']) || $_FILES['artworkFile']['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE  => 'File exceeds server size limit.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL   => 'File upload was incomplete.',
        UPLOAD_ERR_NO_FILE   => 'No file was selected.',
        UPLOAD_ERR_NO_TMP_DIR=> 'Server error: missing temp folder.',
        UPLOAD_ERR_CANT_WRITE=> 'Server error: cannot write file.',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension.',
    ];
    $code = $_FILES['artworkFile']['error'] ?? UPLOAD_ERR_NO_FILE;
    sendResponse(false, $errMap[$code] ?? 'Unknown upload error.');
}

$file          = $_FILES['artworkFile'];
$origName      = $file['name'];
$fileSize      = $file['size'];
$fileTmpPath   = $file['tmp_name'];
$fileType      = $file['type'];

// Validate MIME / size based on category
if ($category === 'photography_paint') {
    $allowedTypes = ['image/jpeg','image/jpg','image/png','image/tiff'];
    $maxSize      = 50 * 1024 * 1024; // 50 MB
    if (!in_array($fileType, $allowedTypes)) {
        sendResponse(false, 'Invalid file type. Upload JPG, PNG, or TIFF images only.');
    }
    if (getimagesize($fileTmpPath) === false) {
        sendResponse(false, 'Invalid image file – file may be corrupted.');
    }
} elseif ($category === 'short_video') {
    $allowedTypes = ['video/mp4','video/avi','video/quicktime','video/x-msvideo'];
    $maxSize      = 500 * 1024 * 1024; // 500 MB
    if (!in_array($fileType, $allowedTypes)) {
        sendResponse(false, 'Invalid file type. Upload MP4, MOV, or AVI videos only.');
    }
} else {
    sendResponse(false, 'Unknown submission category.');
}

if ($fileSize > $maxSize) {
    sendResponse(false, 'File size exceeds the ' . ($maxSize / 1024 / 1024) . ' MB limit.');
}
if (!is_uploaded_file($fileTmpPath)) {
    sendResponse(false, 'Security error: invalid file upload.');
}

// ── Determine version number ──────────────────────────────────────────────────
try {
    $vStmt = $pdo->prepare("SELECT MAX(version_number) FROM submission_versions WHERE submission_id = ?");
    $vStmt->execute([$submissionId]);
    $latestVersion = (int)($vStmt->fetchColumn() ?? 0);
} catch (PDOException $e) {
    // Table may not exist yet – treat as version 0 (first explicit version)
    $latestVersion = 0;
}
$newVersion = $latestVersion + 1;

// ── If this is v1 upload, also archive the ORIGINAL submission as v1 ──────────
// (so we always have a full history)
if ($latestVersion === 0) {
    try {
        $archiveStmt = $pdo->prepare("
            INSERT INTO submission_versions
                (submission_id, version_number, artwork_name, description, file_name,
                 original_filename, file_size, file_type, file_path, uploaded_at, ip_address)
            VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $archiveStmt->execute([
            $submissionId,
            $existing['artworkName'],
            $existing['description'],
            $existing['fileName'],
            $existing['originalFileName'],
            $existing['fileSize'],
            $existing['fileType'],
            $existing['filePath'],
            $existing['submissionDate'],
            $existing['ipAddress'] ?? null,
        ]);
        $newVersion = 2;
    } catch (PDOException $e) {
        logMsg("Could not archive original as v1: " . $e->getMessage());
        // Continue anyway – we'll just store as v1
    }
}

// ── Save new file ─────────────────────────────────────────────────────────────
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$ext         = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$newFileName = $userCode . '_v' . $newVersion . '_' . uniqid() . '_' . time() . '.' . $ext;
$uploadPath  = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
    logMsg("Failed to move uploaded file to $uploadPath");
    sendResponse(false, 'Server error: could not save the uploaded file.');
}

// ── DB transaction: insert version + update main submission ───────────────────
try {
    $pdo->beginTransaction();

    // Insert into submission_versions
    $vInsert = $pdo->prepare("
        INSERT INTO submission_versions
            (submission_id, version_number, artwork_name, description, file_name,
             original_filename, file_size, file_type, file_path, uploaded_at, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $vInsert->execute([
        $submissionId,
        $newVersion,
        $artworkName,
        $description,
        $newFileName,
        $origName,
        $fileSize,
        $fileType,
        'uploads/' . $newFileName,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);

    // Update the main submissions row so admins always see the latest
    $uStmt = $pdo->prepare("
        UPDATE submissions SET
            artworkName       = ?,
            description       = ?,
            fileName          = ?,
            originalFileName  = ?,
            fileSize          = ?,
            fileType          = ?,
            filePath          = ?,
            submissionDate    = NOW()
        WHERE id = ?
    ");
    $uStmt->execute([
        $artworkName,
        $description,
        $newFileName,
        $origName,
        $fileSize,
        $fileType,
        'uploads/' . $newFileName,
        $submissionId,
    ]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    // Clean up the uploaded file
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    logMsg("DB error on version insert: " . $e->getMessage());
    sendResponse(false, 'Database error. Please try again.');
}

logMsg("User $userCode uploaded version $newVersion (submission #$submissionId)");

sendResponse(true, "Version $newVersion uploaded successfully! This is now the active submission.", [
    'versionNumber' => $newVersion,
    'artworkName'   => $artworkName,
]);
