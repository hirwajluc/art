<?php
// minimal_register_test.php - Simplified registration test
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Registration...</h2>";
    
    try {
        // Get form data
        $fullName = trim($_POST['fullName'] ?? '');
        $birthDate = $_POST['birthDate'] ?? '';
        $nationality = trim($_POST['nationality'] ?? '');
        $idNumber = trim($_POST['idNumber'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $category = $_POST['category'] ?? '';
        
        echo "Received data:<br>";
        echo "- Name: $fullName<br>";
        echo "- Email: $email<br>";
        echo "- Category: $category<br><br>";
        
        // Generate simple user code
        $userCode = "GAC" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        echo "Generated user code: $userCode<br><br>";
        
        // Simple validation
        if (empty($fullName) || empty($email) || empty($category)) {
            throw new Exception("Required fields are missing");
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already registered");
        }
        
        echo "Validation passed<br><br>";
        
        // Insert into database
        echo "Attempting database insert...<br>";
        
        $stmt = $pdo->prepare("INSERT INTO registrations 
            (fullName, birthDate, nationality, idNumber, email, phone, category, userCode, registrationDate, ipAddress) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        
        $params = [
            $fullName,
            $birthDate ?: '1990-01-01',
            $nationality ?: 'Unknown',
            $idNumber ?: 'TEST123',
            $email,
            $phone ?: '+1234567890',
            $category,
            $userCode,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];
        
        echo "Parameters: " . json_encode($params) . "<br><br>";
        
        $result = $stmt->execute($params);
        
        if ($result) {
            $insertId = $pdo->lastInsertId();
            echo "✅ SUCCESS! Record inserted with ID: $insertId<br>";
            echo "✅ User code: $userCode<br>";
            
            // Verify insertion
            $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
            $stmt->execute([$insertId]);
            $record = $stmt->fetch();
            
            if ($record) {
                echo "<br><strong>Verified record:</strong><br>";
                foreach ($record as $key => $value) {
                    if (!is_numeric($key)) {
                        echo "- $key: $value<br>";
                    }
                }
            }
            
        } else {
            echo "❌ Insert failed<br>";
            $errorInfo = $stmt->errorInfo();
            echo "Error: " . print_r($errorInfo, true) . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    // Show form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Registration Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .form-group { margin: 10px 0; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input, select { padding: 8px; width: 300px; border: 1px solid #ccc; }
            button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
            button:hover { background: #005a87; }
        </style>
    </head>
    <body>
        <h2>Minimal Registration Test</h2>
        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="fullName" required>
            </div>
            
            <div class="form-group">
                <label>Birth Date</label>
                <input type="date" name="birthDate">
            </div>
            
            <div class="form-group">
                <label>Nationality</label>
                <input type="text" name="nationality">
            </div>
            
            <div class="form-group">
                <label>ID Number</label>
                <input type="text" name="idNumber">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="photography_paint">Photography & Paint</option>
                    <option value="short_video">Short Video</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit">Test Registration</button>
            </div>
        </form>
    </body>
    </html>
    <?php
}
?>