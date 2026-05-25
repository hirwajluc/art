<?php
/**
 * Optimized File Handler for GREATER Admin Panel
 * Handles fast serving of images and videos with proper headers
 */

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['user_id']) || 
    empty($_SESSION['user_id']) || 
    !is_numeric($_SESSION['user_id']) || 
    (int)$_SESSION['user_id'] <= 0) {
    
    http_response_code(403);
    exit('Access Denied');
}

// Get file parameter
$file = $_GET['file'] ?? '';
$action = $_GET['action'] ?? 'view'; // view, download, thumbnail

if (empty($file)) {
    http_response_code(400);
    exit('File parameter required');
}

// Security: Prevent directory traversal
$file = basename($file);
$uploadsPath = __DIR__ . '/../uploads/';
$filePath = $uploadsPath . $file;

// Check if file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('File not found');
}

// Get file info
$fileSize = filesize($filePath);
$mimeType = mime_content_type($filePath);
$fileName = basename($filePath);

// Security: Only allow specific file types
$allowedTypes = [
    // Images
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/tiff',
    // Videos
    'video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov', 'video/quicktime',
    // Documents (if needed)
    'application/pdf'
];

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(403);
    exit('File type not allowed');
}

// Handle thumbnail generation for images
if ($action === 'thumbnail' && strpos($mimeType, 'image/') === 0) {
    serveThumbnail($filePath, $mimeType);
    exit;
}

// Set appropriate headers for fast loading
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');

// Cache headers for better performance
$etag = md5_file($filePath);
$lastModified = filemtime($filePath);

header('ETag: "' . $etag . '"');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

// Check if client has cached version
$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
$ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

if (($ifNoneMatch && $ifNoneMatch === '"' . $etag . '"') ||
    ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified)) {
    http_response_code(304); // Not Modified
    exit;
}

// Handle download vs inline display
if ($action === 'download') {
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
} else {
    header('Content-Disposition: inline; filename="' . $fileName . '"');
}

// Handle range requests for large files (especially videos)
if (isset($_SERVER['HTTP_RANGE'])) {
    serveRangeRequest($filePath, $fileSize, $mimeType);
} else {
    // Serve entire file
    readfile($filePath);
}

/**
 * Handle HTTP Range requests for large files
 */
function serveRangeRequest($filePath, $fileSize, $mimeType) {
    $range = $_SERVER['HTTP_RANGE'];
    
    // Parse range header
    if (!preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        http_response_code(416); // Range Not Satisfiable
        exit;
    }
    
    $start = intval($matches[1]);
    $end = $matches[2] ? intval($matches[2]) : $fileSize - 1;
    
    // Validate range
    if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
        http_response_code(416);
        exit;
    }
    
    $length = $end - $start + 1;
    
    // Set partial content headers
    http_response_code(206); // Partial Content
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $length);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
    header('Accept-Ranges: bytes');
    
    // Serve the requested range
    $handle = fopen($filePath, 'rb');
    fseek($handle, $start);
    
    $bufferSize = 8192; // 8KB chunks
    $remaining = $length;
    
    while ($remaining > 0 && !feof($handle)) {
        $chunkSize = min($bufferSize, $remaining);
        echo fread($handle, $chunkSize);
        $remaining -= $chunkSize;
        
        // Flush output to prevent memory issues
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
    
    fclose($handle);
}

/**
 * Generate and serve thumbnails for images
 */
function serveThumbnail($filePath, $mimeType) {
    $maxWidth = 300;
    $maxHeight = 300;
    
    // Create image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            http_response_code(400);
            exit('Unsupported image type for thumbnail');
    }
    
    if (!$source) {
        http_response_code(500);
        exit('Failed to create image resource');
    }
    
    // Get original dimensions
    $originalWidth = imagesx($source);
    $originalHeight = imagesy($source);
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = intval($originalWidth * $ratio);
    $newHeight = intval($originalHeight * $ratio);
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG/GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
    }
    
    // Resize image
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Set headers for thumbnail
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
    
    // Output thumbnail
    imagejpeg($thumbnail, null, 85); // 85% quality
    
    // Clean up memory
    imagedestroy($source);
    imagedestroy($thumbnail);
}
?>