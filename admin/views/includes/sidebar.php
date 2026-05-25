<?php
// views/includes/sidebar.php
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$currentUser = [
    'id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['user_username'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'full_name' => $_SESSION['user_full_name'] ?? 'Admin User',
    'role' => $_SESSION['user_role'] ?? 'jury'
];
?>
<nav class="sidebar">
    <div class="logo">
        <h1>GREATER</h1>
        <p>Art Competition Admin Panel</p>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="?page=dashboard" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=registrations" class="nav-link <?php echo $currentPage === 'registrations' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                Registrations
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=submissions" class="nav-link <?php echo $currentPage === 'submissions' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i>
                Submissions
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=winners" class="nav-link <?php echo $currentPage === 'winners' ? 'active' : ''; ?>">
                <i class="fas fa-trophy"></i>
                Winners
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=export" class="nav-link <?php echo $currentPage === 'export' ? 'active' : ''; ?>">
                <i class="fas fa-download"></i>
                Export Data
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=email_campaigns" class="nav-link <?php echo $currentPage === 'email_campaigns' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                Email Campaigns
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=settings" class="nav-link <?php echo in_array($currentPage, ['settings','save_settings']) ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div class="nav-divider"></div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="?page=logout" class="nav-link logout-link" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>