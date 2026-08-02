<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Judges - GREATER Art Competition</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .judge-card { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.08); margin-bottom:16px; overflow:hidden; }
        .judge-card-header { display:flex; align-items:center; gap:16px; padding:18px 24px; border-bottom:1px solid #f0f0f0; }
        .judge-avatar { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:bold; flex-shrink:0; }
        .judge-meta { flex:1; }
        .judge-meta h3 { font-size:16px; font-weight:600; color:var(--dark); margin-bottom:3px; }
        .judge-meta small { color:var(--gray); font-size:13px; }
        .judge-stats { display:flex; gap:24px; padding:14px 24px; background:#fafafa; }
        .j-stat { text-align:center; }
        .j-stat .num { font-size:22px; font-weight:700; color:var(--primary); }
        .j-stat .lbl { font-size:11px; color:var(--gray); text-transform:uppercase; }
        .judge-actions { display:flex; gap:8px; padding:12px 24px; border-top:1px solid #f0f0f0; flex-wrap:wrap; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9000; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:#fff; border-radius:12px; padding:32px; max-width:480px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .modal h2 { font-size:20px; margin-bottom:20px; color:var(--dark); }
        .status-active   { background:#d4edda; color:#155724; }
        .status-inactive { background:#f8d7da; color:#721c24; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <?php if (!empty($newInviteLink)): ?>
        <div style="background:#fef9c3; border:2px solid #f59e0b; border-radius:12px; padding:20px 24px; margin-bottom:24px;">
            <div style="font-weight:700; color:#92400e; margin-bottom:10px;"><i class="fas fa-envelope-open-text"></i> Invitation Link (share this with the judge)</div>
            <p style="color:#78350f; font-size:13px; margin-bottom:12px;">Email delivery is not guaranteed on this server. Copy the link below and share it directly with the judge.</p>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="text" id="inviteLinkBox" value="<?php echo htmlspecialchars($newInviteLink); ?>" readonly
                       style="flex:1; padding:10px 14px; border:1.5px solid #f59e0b; border-radius:8px; font-size:13px; background:#fff; font-family:monospace;">
                <button onclick="copyInviteLink()" class="btn btn-warning" style="white-space:nowrap;">
                    <i class="fas fa-copy"></i> Copy Link
                </button>
            </div>
            <p style="color:#92400e; font-size:12px; margin-top:8px;"><i class="fas fa-clock"></i> Link expires in 48 hours.</p>
        </div>
        <?php endif; ?>

        <!-- Header action -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <p style="color:var(--gray);"><?php echo count($judges); ?> judge account(s) registered</p>
            <button class="btn btn-primary" onclick="openModal('createModal')">
                <i class="fas fa-plus"></i> Add Judge
            </button>
        </div>

        <!-- Judge list -->
        <?php if (empty($judges)): ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06);">
            <i class="fas fa-gavel" style="font-size:48px; color:#ddd; margin-bottom:16px;"></i>
            <p style="color:var(--gray); font-size:16px;">No judges yet. Click <strong>Add Judge</strong> to create the first account.</p>
        </div>
        <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:16px;">
        <?php foreach ($judges as $judge):
            $hasPendingInvite = !empty($judge['password_setup_token']) &&
                                !empty($judge['token_expires_at']) &&
                                strtotime($judge['token_expires_at']) > time();
            $accepted = !$hasPendingInvite; // Judge has set their password
            $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl  = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            $judgeInviteLink = $hasPendingInvite
                ? $baseUrl . '/admin/?page=set_password&token=' . $judge['password_setup_token']
                : '';
        ?>
        <div class="judge-card">
            <!-- Header row -->
            <div class="judge-card-header" style="flex-wrap:wrap; gap:12px;">
                <div class="judge-avatar" style="<?php echo $hasPendingInvite ? 'background:linear-gradient(135deg,#f59e0b,#d97706);' : ''; ?>">
                    <?php echo $hasPendingInvite ? '✉' : strtoupper(substr($judge['full_name'],0,1)); ?>
                </div>
                <div class="judge-meta" style="min-width:200px;">
                    <h3><?php echo htmlspecialchars($judge['full_name']); ?></h3>
                    <small>@<?php echo htmlspecialchars($judge['username']); ?> &nbsp;·&nbsp; <?php echo htmlspecialchars($judge['email']); ?></small>
                </div>

                <!-- Status badge -->
                <div style="margin-left:auto; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <?php if ($hasPendingInvite): ?>
                    <span class="status-badge" style="background:#fef9c3; color:#92400e; border:1px solid #f59e0b;">
                        <i class="fas fa-envelope"></i> Invite Pending
                    </span>
                    <?php else: ?>
                    <span class="status-badge <?php echo $judge['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                        <i class="fas fa-<?php echo $judge['status'] === 'active' ? 'check-circle' : 'ban'; ?>"></i>
                        <?php echo $judge['status'] === 'active' ? 'Active' : 'Disabled'; ?>
                    </span>
                    <?php endif; ?>

                    <!-- Stats inline -->
                    <span style="font-size:13px; color:var(--gray);">
                        <i class="fas fa-gavel"></i> <?php echo (int)$judge['evaluated_count']; ?> evaluated
                        &nbsp;·&nbsp;
                        <i class="fas fa-check-double"></i> <?php echo (int)$judge['submitted_count']; ?> submitted
                    </span>
                </div>
            </div>

            <!-- Pending invite: show copyable link -->
            <?php if ($hasPendingInvite): ?>
            <div style="padding:14px 24px; background:#fffbeb; border-top:1px solid #fde68a; border-bottom:1px solid #fde68a;">
                <p style="font-size:13px; color:#92400e; margin-bottom:10px;">
                    <i class="fas fa-info-circle"></i> This judge has not accepted the invitation yet. Share the link below directly:
                </p>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" value="<?php echo htmlspecialchars($judgeInviteLink); ?>" readonly
                           id="link_<?php echo $judge['id']; ?>"
                           style="flex:1; font-size:12px; padding:8px 12px; border:1.5px solid #f59e0b; border-radius:8px; background:#fff; font-family:monospace; min-width:0;">
                    <button onclick="copyLink('link_<?php echo $judge['id']; ?>')" class="btn btn-warning" style="white-space:nowrap;">
                        <i class="fas fa-copy"></i> Copy Link
                    </button>
                </div>
                <p style="font-size:12px; color:#b45309; margin-top:8px;">
                    <i class="fas fa-clock"></i> Expires: <?php echo date('M j, Y g:i A', strtotime($judge['token_expires_at'])); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Action bar -->
            <div class="judge-actions" style="padding:14px 24px;">
                <button class="btn btn-primary" onclick="openEditModal(<?php echo $judge['id']; ?>, '<?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>', '<?php echo htmlspecialchars(addslashes($judge['email'])); ?>')">
                    <i class="fas fa-edit"></i> Edit
                </button>

                <?php if ($accepted): ?>
                    <!-- Only show password reset for judges who have already accepted -->
                    <button class="btn btn-warning" onclick="openResetModal(<?php echo $judge['id']; ?>, '<?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>')">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    <!-- Disable / Enable only for judges who accepted -->
                    <a href="?page=toggle_judge&id=<?php echo $judge['id']; ?>"
                       class="btn <?php echo $judge['status'] === 'active' ? 'btn-secondary' : 'btn-success'; ?>"
                       onclick="return confirm('<?php echo $judge['status'] === 'active' ? 'Disable' : 'Enable'; ?> this judge?')">
                        <i class="fas fa-<?php echo $judge['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                        <?php echo $judge['status'] === 'active' ? 'Disable' : 'Enable'; ?>
                    </a>
                <?php endif; ?>

                <!-- Remove: always allowed when no submitted evals -->
                <?php if ((int)$judge['submitted_count'] === 0): ?>
                <a href="?page=delete_judge&id=<?php echo $judge['id']; ?>" class="btn btn-danger"
                   onclick="return confirm('Permanently remove <?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>?\n\nThis cannot be undone.')">
                    <i class="fas fa-trash"></i> <?php echo $hasPendingInvite ? 'Revoke & Remove' : 'Remove'; ?>
                </a>
                <?php endif; ?>

                <span style="margin-left:auto; font-size:12px; color:var(--gray);">
                    Added <?php echo date('M j, Y', strtotime($judge['created_at'])); ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Create Judge Modal -->
        <div class="modal-overlay" id="createModal">
            <div class="modal">
                <h2><i class="fas fa-user-plus"></i> Add Judge Account</h2>
                <p style="color:var(--gray); font-size:13px; margin-bottom:16px;">
                    <i class="fas fa-envelope"></i> An invitation email will be sent to the judge so they can set their own password.
                </p>
                <form method="POST" action="?page=create_judge">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input class="form-input" type="text" name="full_name" required placeholder="e.g. Dr. Marie Curie">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input class="form-input" type="text" name="username" required placeholder="e.g. judge_marie">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input class="form-input" type="email" name="email" required placeholder="judge@example.com">
                    </div>
                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-paper-plane"></i> Create & Send Invite</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Judge Modal -->
        <div class="modal-overlay" id="editModal">
            <div class="modal">
                <h2><i class="fas fa-edit"></i> Edit Judge</h2>
                <form method="POST" action="?page=edit_judge">
                    <input type="hidden" name="id" id="editJudgeId">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input class="form-input" type="text" name="full_name" id="editFullName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input class="form-input" type="email" name="email" id="editEmail" required>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div class="modal-overlay" id="resetModal">
            <div class="modal">
                <h2><i class="fas fa-key"></i> Reset Password</h2>
                <p style="color:var(--gray); margin-bottom:20px;">Set a new temporary password for <strong id="resetJudgeName"></strong>.</p>
                <form method="POST" action="?page=reset_judge_password">
                    <input type="hidden" name="id" id="resetJudgeId">
                    <div class="form-group">
                        <label class="form-label">New Password *</label>
                        <input class="form-input" type="password" name="new_password" required minlength="6">
                    </div>
                    <div style="display:flex; gap:10px; margin-top:8px;">
                        <button type="submit" class="btn btn-warning" style="flex:1;"><i class="fas fa-key"></i> Reset Password</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('resetModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</div>
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openEditModal(id, name, email) {
    document.getElementById('editJudgeId').value  = id;
    document.getElementById('editFullName').value = name;
    document.getElementById('editEmail').value    = email;
    openModal('editModal');
}
function openResetModal(id, name) {
    document.getElementById('resetJudgeId').value  = id;
    document.getElementById('resetJudgeName').textContent = name;
    openModal('resetModal');
}
// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

function copyLink(inputId) {
    const el = document.getElementById(inputId);
    el.select(); el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value).then(() => {
        const btn = el.nextElementSibling;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

function copyInviteLink() {
    const el = document.getElementById('inviteLinkBox');
    el.select(); el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value).then(() => {
        const btn = el.nextElementSibling;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}
</script>
</body>
</html>
