<?php
/**
 * EMERGENCY DEBUG - COMPLETE LOCKDOWN
 */

// Force session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IMMEDIATE DEBUG OUTPUT
echo "<h1>SECURITY DEBUG</h1>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID in Session: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>Requested Page: " . ($_GET['page'] ?? 'NOT SET') . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// COMPLETE LOCKDOWN - NO EXCEPTIONS
$page = $_GET['page'] ?? 'dashboard';

// Show what we're checking
echo "<h2>Authentication Check</h2>";
echo "<p>Requested page: <strong>$page</strong></p>";

if ($page === 'login' || $page === 'authenticate') {
    echo "<p style='color: green;'>✅ Public page - access allowed</p>";
} else {
    echo "<p style='color: red;'>🔒 Protected page - checking authentication...</p>";
    
    // Check session variables
    $userId = $_SESSION['user_id'] ?? null;
    $hasUserId = isset($_SESSION['user_id']);
    $notEmpty = !empty($_SESSION['user_id']);
    $isNumeric = is_numeric($_SESSION['user_id'] ?? '');
    $isPositive = ((int)($_SESSION['user_id'] ?? 0)) > 0;
    
    echo "<ul>";
    echo "<li>Has user_id: " . ($hasUserId ? "✅ YES" : "❌ NO") . "</li>";
    echo "<li>Not empty: " . ($notEmpty ? "✅ YES" : "❌ NO") . "</li>";
    echo "<li>Is numeric: " . ($isNumeric ? "✅ YES" : "❌ NO") . "</li>";
    echo "<li>Is positive: " . ($isPositive ? "✅ YES" : "❌ NO") . "</li>";
    echo "</ul>";
    
    if (!$hasUserId || !$notEmpty || !$isNumeric || !$isPositive) {
        echo "<h2 style='color: red;'>🚫 ACCESS DENIED - REDIRECTING TO LOGIN</h2>";
        echo "<p>If you can see this message and are not being redirected, there's a serious issue.</p>";
        
        // Force redirect with multiple methods
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        
        echo "<script>window.location.href = 'login.php?error=Authentication required';</script>";
        echo "<meta http-equiv='refresh' content='0; url=login.php?error=Authentication required'>";
        header('Location: login.php?error=Authentication required');
        exit('<h1>ACCESS DENIED</h1><p><a href="login.php">Click here to login</a></p>');
    } else {
        echo "<p style='color: green;'>✅ Authentication passed - user is logged in</p>";
    }
}

// Show all session data for debugging
echo "<h2>Complete Session Data</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Show server variables
echo "<h2>Server Variables</h2>";
echo "<p>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "</p>";
echo "<p>SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "</p>";
echo "<p>REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . "</p>";

// STOP HERE - Don't load any views for debugging
echo "<h2>🛑 STOPPING HERE FOR DEBUG</h2>";
echo "<p>If you can see this page without being logged in, the session system is not working properly.</p>";
echo "<p><a href='?page=login'>Go to Login</a> | <a href='?clear=1'>Clear Session</a></p>";

// Handle session clear
if (isset($_GET['clear'])) {
    session_destroy();
    echo "<p style='color: red;'>Session destroyed. <a href='?page=dashboard'>Try accessing dashboard again</a></p>";
}

exit;
?>