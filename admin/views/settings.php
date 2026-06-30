<?php
// views/settings.php
// Variables available: $competitionSettings, $currentUser, $flashSuccess, $flashError
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Settings</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .settings-container { max-width: 900px; margin: 0 auto; }
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
        @media(max-width:800px){ .settings-grid { grid-template-columns:1fr; } }
        .settings-card { background:#fff; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,.1); overflow:hidden; }
        .card-header { background:var(--primary,#1E90FF); color:#fff; padding:20px 25px; }
        .card-header h3 { font-size:18px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .card-content { padding:25px; }

        .alert { border-radius:10px; padding:14px 18px; margin-bottom:24px; font-size:14px; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#d1fae5; color:#065f46; border-left:4px solid #10b981; }
        .alert-error   { background:#fee2e2; color:#b91c1c; border-left:4px solid #ef4444; }

        .deadline-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
            margin-left: 10px;
        }
        .deadline-status.open   { background:#d1fae5; color:#065f46; }
        .deadline-status.closed { background:#fee2e2; color:#b91c1c; }

        .info-grid { display:grid; grid-template-columns:1fr; gap:15px; }
        .info-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee; }
        .info-item:last-child { border-bottom:none; }
        .info-item label { font-weight:500; color:var(--gray,#888); }
        .info-item span  { color:var(--dark,#333); font-weight:500; }

        .admin-user { display:flex; justify-content:space-between; align-items:center; padding:15px 0; border-bottom:1px solid #eee; }
        .admin-user:last-child { border-bottom:none; }
        .user-info { display:flex; flex-direction:column; }
        .user-info strong { color:var(--dark,#333); margin-bottom:2px; }
        .user-info small  { color:var(--gray,#888); font-size:12px; }
        .role-badge { padding:4px 10px; border-radius:12px; font-size:11px; font-weight:500; text-transform:uppercase; }
        .role-badge.admin { background:#e3f2fd; color:#1565c0; }

        .maintenance-actions { display:flex; flex-direction:column; gap:15px; }
        .maintenance-actions .btn { justify-content:center; }

        /* full-width card for competition settings */
        .settings-card.full { grid-column: 1 / -1; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-grid">

                <!-- ── Competition Deadlines (full width) ── -->
                <div class="settings-card full">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Competition Deadlines &amp; Title</h3>
                    </div>
                    <div class="card-content">
                        <?php
                            $regDeadline = $competitionSettings['registration_deadline'] ?? '2025-12-31 23:59:59';
                            $subDeadline = $competitionSettings['submission_deadline']   ?? '2025-12-31 23:59:59';
                            $now = date('Y-m-d H:i:s');
                            $regOpen = $regDeadline > $now;
                            $subOpen = $subDeadline > $now;
                        ?>
                        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:20px;">
                            <span>Registration:
                                <span class="deadline-status <?php echo $regOpen ? 'open' : 'closed'; ?>">
                                    <?php echo $regOpen ? '✅ Open' : '🔒 Closed'; ?>
                                </span>
                            </span>
                            <span>Submission:
                                <span class="deadline-status <?php echo $subOpen ? 'open' : 'closed'; ?>">
                                    <?php echo $subOpen ? '✅ Open' : '🔒 Closed'; ?>
                                </span>
                            </span>
                        </div>

                        <form method="POST" action="?page=save_settings">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                                        Competition Title
                                    </label>
                                    <input type="text" name="competition_title" class="form-input"
                                           value="<?php echo htmlspecialchars($competitionSettings['competition_title'] ?? ''); ?>"
                                           style="width:100%; padding:10px 14px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                                        Winner Announcement Date
                                    </label>
                                    <input type="date" name="winner_announcement" class="form-input"
                                           value="<?php echo htmlspecialchars(substr($competitionSettings['winner_announcement'] ?? '', 0, 10)); ?>"
                                           style="width:100%; padding:10px 14px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                                        Registration Deadline
                                        <small style="font-weight:400; color:#9ca3af;">(closes registration form)</small>
                                    </label>
                                    <input type="datetime-local" name="registration_deadline" class="form-input"
                                           value="<?php echo htmlspecialchars(str_replace(' ','T', substr($regDeadline,0,16))); ?>"
                                           style="width:100%; padding:10px 14px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                                        Submission Deadline
                                        <small style="font-weight:400; color:#9ca3af;">(closes uploads &amp; updates)</small>
                                    </label>
                                    <input type="datetime-local" name="submission_deadline" class="form-input"
                                           value="<?php echo htmlspecialchars(str_replace(' ','T', substr($subDeadline,0,16))); ?>"
                                           style="width:100%; padding:10px 14px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px;">
                                </div>
                            </div>

                            <div style="margin-top:10px;">
                                <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">
                                    <i class="fas fa-info-circle"></i>
                                    Setting a deadline in the past will immediately close registrations / submissions.
                                    The last submission version before the deadline is the one considered by the jury.
                                </p>
                                <button type="submit" class="btn btn-primary" style="padding:12px 32px; font-size:15px;">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ── Admin Users ── -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users-cog"></i> Admin Users</h3>
                    </div>
                    <div class="card-content">
                        <div class="admin-user">
                            <div class="user-info">
                                <strong>System Administrator</strong>
                                <small>info@greaterproject.eu</small>
                            </div>
                            <span class="role-badge admin">Admin</span>
                        </div>
                        <div class="admin-user">
                            <div class="user-info">
                                <strong>HIRWA Jean Luc</strong>
                                <small>hirwajeanluc@gmail.com</small>
                            </div>
                            <span class="role-badge admin">Admin</span>
                        </div>
                        <a href="?page=users" class="btn btn-primary" style="margin-top:20px; display:inline-flex; align-items:center; gap:8px; text-decoration:none;">
                            <i class="fas fa-users-cog"></i> Manage Admin Users
                        </a>
                    </div>
                </div>

                <!-- ── System Information ── -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> System Information</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-grid">
                            <div class="info-item"><label>PHP Version</label><span><?php echo phpversion(); ?></span></div>
                            <div class="info-item"><label>Server Software</label><span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span></div>
                            <div class="info-item"><label>Upload Max Size</label><span><?php echo ini_get('upload_max_filesize'); ?></span></div>
                            <div class="info-item"><label>Memory Limit</label><span><?php echo ini_get('memory_limit'); ?></span></div>
                            <div class="info-item"><label>Server Time</label><span><?php echo date('Y-m-d H:i:s'); ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- ── Backup & Maintenance ── -->
                <div class="settings-card full">
                    <div class="card-header">
                        <h3><i class="fas fa-tools"></i> Backup &amp; Maintenance</h3>
                    </div>
                    <div class="card-content">
                        <div class="maintenance-actions" style="flex-direction:row; flex-wrap:wrap;">
                            <a href="?page=export&type=registrations" class="btn btn-success">
                                <i class="fas fa-download"></i> Export Registrations CSV
                            </a>
                            <a href="?page=export&type=submissions" class="btn btn-primary">
                                <i class="fas fa-download"></i> Export Submissions CSV
                            </a>
                        </div>
                    </div>
                </div>

            </div><!-- /settings-grid -->
        </div><!-- /settings-container -->
    </main>
</div>
</body>
</html>
