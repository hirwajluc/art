<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password - GREATER Judging Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#1E90FF 0%,#00BFFF 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { background:#fff; border-radius:16px; padding:40px 48px; max-width:440px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .logo { text-align:center; margin-bottom:28px; }
        .logo img { max-height:60px; }
        h2 { font-size:22px; color:#2C3E50; margin-bottom:6px; text-align:center; }
        .subtitle { text-align:center; color:#6C757D; font-size:14px; margin-bottom:28px; }
        .form-group { margin-bottom:18px; }
        label { display:block; font-size:13px; font-weight:600; color:#2C3E50; margin-bottom:6px; }
        input[type="password"] { width:100%; padding:12px 14px; border:2px solid #e2e8f0; border-radius:8px; font-size:15px; transition:.2s; }
        input[type="password"]:focus { border-color:#1E90FF; outline:none; box-shadow:0 0 0 3px rgba(30,144,255,.15); }
        .btn { width:100%; padding:13px; background:#1E90FF; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:600; cursor:pointer; margin-top:4px; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
        .btn:hover { background:#1877e8; }
        .alert-error { background:#fee2e2; color:#b91c1c; border-radius:8px; padding:12px 16px; margin-bottom:18px; font-size:14px; display:flex; align-items:center; gap:8px; }
        .alert-invalid { background:#fff3cd; color:#856404; border-radius:10px; padding:24px; text-align:center; }
        .alert-invalid i { font-size:40px; margin-bottom:12px; display:block; }
        .hint { font-size:12px; color:#aaa; margin-top:6px; }
        .name-greeting { background:#f0f9ff; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:14px; color:#0c4a6e; display:flex; align-items:center; gap:10px; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <img src="../Greater_full_logo.png" alt="GREATER" style="filter:none;">
    </div>

    <?php if ($tokenError || !$judge): ?>
        <div class="alert-invalid">
            <i class="fas fa-link"></i>
            <strong>Invalid or Expired Link</strong>
            <p style="margin-top:8px; font-size:14px; color:#92400e;"><?php echo htmlspecialchars($tokenError); ?></p>
            <p style="margin-top:12px; font-size:13px; color:#6b7280;">Please contact the administrator to get a new invitation.</p>
        </div>
    <?php else: ?>
        <h2>Set Your Password</h2>
        <p class="subtitle">Create a secure password to access the judging portal.</p>

        <div class="name-greeting">
            <i class="fas fa-user-circle" style="font-size:20px; color:#1E90FF;"></i>
            Welcome, <strong><?php echo htmlspecialchars($judge['full_name']); ?></strong>
        </div>

        <?php if ($flashError): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <form method="POST" action="?page=do_set_password">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required minlength="6" placeholder="At least 6 characters" autofocus>
                <div class="hint">Minimum 6 characters</div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" required minlength="6" placeholder="Repeat your password">
            </div>
            <button type="submit" class="btn"><i class="fas fa-lock"></i> Set Password &amp; Login</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
