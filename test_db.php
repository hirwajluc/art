<?php
// test_db.php - Simple database test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Include your db connection
require_once 'db.php';

try {
    // Test 1: Basic connection
    echo "<h3>Test 1: Connection Test</h3>";
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✓ Database connection successful<br>";
    
    // Test 2: Check if registrations table exists
    echo "<h3>Test 2: Table Structure</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'registrations'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Registrations table exists<br>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE registrations");
        $columns = $stmt->fetchAll();
        echo "<strong>Table structure:</strong><br>";
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} " . 
                 ($col['Null'] == 'NO' ? '(NOT NULL)' : '(NULL)') . 
                 ($col['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . "<br>";
        }
    } else {
        echo "❌ Registrations table does NOT exist<br>";
        echo "<strong>Creating table...</strong><br>";
        
        $createTable = "
        CREATE TABLE registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fullName VARCHAR(100) NOT NULL,
            birthDate DATE NOT NULL,
            nationality VARCHAR(50) NOT NULL,
            idNumber VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            category ENUM('photography_paint', 'short_video') NOT NULL,
            userCode VARCHAR(10) NOT NULL UNIQUE,
            registrationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ipAddress VARCHAR(45)
        )";
        
        $pdo->exec($createTable);
        echo "✓ Registrations table created<br>";
    }
    
    // Test 3: Try inserting a test record
    echo "<h3>Test 3: Insert Test</h3>";
    
    // Generate test user code
    $testUserCode = "GAC" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO registrations 
        (fullName, birthDate, nationality, idNumber, email, phone, category, userCode, registrationDate, ipAddress) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    
    $testData = [
        'Test User ' . rand(1000, 9999),
        '1990-01-01',
        'Test Country',
        'TEST' . rand(10000, 99999),
        'test' . rand(1000, 9999) . '@example.com',
        '+1234567890',
        'photography_paint',
        $testUserCode,
        '127.0.0.1'
    ];
    
    $result = $stmt->execute($testData);
    
    if ($result) {
        $insertId = $pdo->lastInsertId();
        echo "✓ Test record inserted successfully with ID: $insertId<br>";
        echo "✓ User code: $testUserCode<br>";
        
        // Verify the record was inserted
        $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
        $stmt->execute([$insertId]);
        $record = $stmt->fetch();
        
        if ($record) {
            echo "✓ Record verified in database<br>";
            echo "<strong>Inserted data:</strong><br>";
            foreach ($record as $key => $value) {
                if (!is_numeric($key)) {
                    echo "- $key: $value<br>";
                }
            }
        } else {
            echo "❌ Record not found after insertion<br>";
        }
        
    } else {
        echo "❌ Failed to insert test record<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2] . "<br>";
    }
    
    // Test 4: Count total records
    echo "<h3>Test 4: Record Count</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM registrations");
    $count = $stmt->fetch();
    echo "Total registrations in database: " . $count['total'] . "<br>";
    
    // Test 5: Show recent records
    echo "<h3>Test 5: Recent Records</h3>";
    $stmt = $pdo->query("SELECT id, fullName, email, userCode, registrationDate FROM registrations ORDER BY registrationDate DESC LIMIT 5");
    $records = $stmt->fetchAll();
    
    if ($records) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>User Code</th><th>Date</th></tr>";
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['fullName']}</td>";
            echo "<td>{$record['email']}</td>";
            echo "<td>{$record['userCode']}</td>";
            echo "<td>{$record['registrationDate']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No records found<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
    echo "SQL State: " . $e->errorInfo[0] . "<br>";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Additional Debugging Info</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>