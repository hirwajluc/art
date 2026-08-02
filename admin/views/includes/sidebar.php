<?php
// views/includes/sidebar.php
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$currentUser = [
    'id'        => $_SESSION['user_id']        ?? null,
    'username'  => $_SESSION['user_username']  ?? '',
    'email'     => $_SESSION['user_email']     ?? '',
    'full_name' => $_SESSION['user_full_name'] ?? 'User',
    'role'      => $_SESSION['user_role']      ?? 'jury',
];
$isAdmin = ($currentUser['role'] === 'admin');
?>
<nav class="sidebar">
    <div class="logo">
        <img src="../Greater_full_logo.png" alt="GREATER" style="max-height:50px; filter:brightness(0) invert(1); display:block; margin-bottom:6px;">
        <p><?php echo $isAdmin ? 'Art Competition Admin' : 'Judging Portal'; ?></p>
    </div>

    <ul class="nav-menu">
        <?php if ($isAdmin): ?>
        <!-- ── Admin Navigation ── -->
        <li class="nav-item">
            <a href="?page=dashboard" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=registrations" class="nav-link <?php echo in_array($currentPage, ['registrations','registration_detail']) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Registrations
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=submissions" class="nav-link <?php echo in_array($currentPage, ['submissions','submission_detail']) ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Submissions
            </a>
        </li>

        <li class="nav-item" style="padding: 8px 25px 4px; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,0.5);">
            Judging
        </li>
        <li class="nav-item">
            <a href="?page=judges" class="nav-link <?php echo in_array($currentPage, ['judges','create_judge','edit_judge']) ? 'active' : ''; ?>">
                <i class="fas fa-gavel"></i> Manage Judges
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=judging_criteria" class="nav-link <?php echo in_array($currentPage, ['judging_criteria','save_criteria']) ? 'active' : ''; ?>">
                <i class="fas fa-sliders-h"></i> Scoring Criteria
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=judging_results" class="nav-link <?php echo $currentPage === 'judging_results' ? 'active' : ''; ?>">
                <i class="fas fa-trophy"></i> Results &amp; Rankings
            </a>
        </li>

        <li class="nav-item" style="padding: 8px 25px 4px; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,0.5);">
            Platform
        </li>
        <li class="nav-item">
            <a href="?page=winners" class="nav-link <?php echo $currentPage === 'winners' ? 'active' : ''; ?>">
                <i class="fas fa-medal"></i> Winners
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=export" class="nav-link <?php echo $currentPage === 'export' ? 'active' : ''; ?>">
                <i class="fas fa-download"></i> Export Data
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=email_campaigns" class="nav-link <?php echo $currentPage === 'email_campaigns' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Email Campaigns
            </a>
        </li>
        <li class="nav-item">
            <a href="?page=settings" class="nav-link <?php echo in_array($currentPage, ['settings','save_settings']) ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>

        <?php else: ?>
        <!-- ── Judge Navigation ── -->
        <li class="nav-item">
            <a href="?page=judge_dashboard" class="nav-link <?php echo $currentPage === 'judge_dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-th-list"></i> My Artworks
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="nav-divider"></div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="?page=logout" class="nav-link logout-link" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
        <div style="padding: 10px 25px; font-size:11px; color:rgba(255,255,255,0.4);">
            Logged in as <strong style="color:rgba(255,255,255,0.7);"><?php echo htmlspecialchars($currentUser['username']); ?></strong>
            <span style="display:block; margin-top:2px;"><?php echo $isAdmin ? '🔑 Admin' : '⚖️ Judge'; ?></span>
        </div>
    </div>
</nav>
