<?php
// views/includes/topbar.php
$pageTitle = 'Dashboard';
$currentUser = [
    'id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['user_username'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
    'role' => $_SESSION['user_role'] ?? 'jury'
];

switch($currentPage) {
    case 'registrations':
        $pageTitle = 'Registrations';
        break;
    case 'registration_detail':
        $pageTitle = 'Registration Details';
        break;
    case 'submissions':
        $pageTitle = 'Submissions';
        break;
    case 'submission_detail':
        $pageTitle = 'Submission Details';
        break;
    case 'winners':
        $pageTitle = 'Winners';
        break;
    case 'export':
        $pageTitle = 'Export Data';
        break;
    case 'settings':
        $pageTitle = 'Settings';
        break;
}
?>
<div class="top-bar">
    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
    <div class="user-info">
        <?php if ($currentUser): ?>
            <div class="user-welcome">
                <span>Welcome back, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong></span>
                <small><?php
// Safe way to get login time
$loginTime = $_SESSION['login_time'] ?? time();
$lastLogin = date('M j, Y g:i A', $loginTime);
?> Last login: <?php echo $lastLogin; ?></small>
            </div>
            <div class="user-avatar"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></div>
        <?php else: ?>
            <span>Welcome, Guest</span>
            <div class="user-avatar">G</div>
        <?php endif; ?>
    </div>
</div>