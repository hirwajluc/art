<?php
// Database configuration
$host = '31.11.39.158';  // Your database host
$dbname = 'Sql1800295_4';  // Your database name
$username = 'Sql1800295';  // Your database username
$password = 'GreaterPass123!';  // Replace with your actual password

// PDO options for better security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_TIMEOUT            => 30,
    PDO::ATTR_PERSISTENT         => false
];

try {
    // Create PDO connection
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
    // Log successful connection (remove in production)
    error_log("Database connection established successfully");
    
} catch (PDOException $e) {
    // Log the error (don't expose in production)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Return a user-friendly error
    http_response_code(500);
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX request - return JSON error
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please try again later.',
            'error_code' => 'DB_CONNECTION_FAILED'
        ]);
    } else {
        // Regular request - show error page
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Service Temporarily Unavailable</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error { color: #721c24; background: #f8d7da; padding: 20px; border-radius: 8px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='error'>
                <h2>Service Temporarily Unavailable</h2>
                <p>We're experiencing technical difficulties. Please try again later.</p>
            </div>
        </body>
        </html>";
    }
    exit;
}

// Function to test database connection
function testDatabaseConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM registrations");
        return [
            'success' => true,
            'total_registrations' => $stmt->fetchColumn()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>