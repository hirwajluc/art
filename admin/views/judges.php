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
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px,1fr)); gap:20px;">
        <?php foreach ($judges as $judge): ?>
        <div class="judge-card">
            <div class="judge-card-header">
                <div class="judge-avatar"><?php echo strtoupper(substr($judge['full_name'],0,1)); ?></div>
                <div class="judge-meta">
                    <h3><?php echo htmlspecialchars($judge['full_name']); ?></h3>
                    <small>@<?php echo htmlspecialchars($judge['username']); ?> · <?php echo htmlspecialchars($judge['email']); ?></small>
                </div>
                <span class="status-badge <?php echo $judge['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo ucfirst($judge['status']); ?>
                </span>
            </div>
            <div class="judge-stats">
                <div class="j-stat"><div class="num"><?php echo (int)$judge['evaluated_count']; ?></div><div class="lbl">Evaluated</div></div>
                <div class="j-stat"><div class="num"><?php echo (int)$judge['submitted_count']; ?></div><div class="lbl">Submitted</div></div>
                <div class="j-stat"><div class="num"><?php echo date('M j, Y', strtotime($judge['created_at'])); ?></div><div class="lbl">Created</div></div>
            </div>
            <div class="judge-actions">
                <button class="btn btn-primary" onclick="openEditModal(<?php echo $judge['id']; ?>, '<?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>', '<?php echo htmlspecialchars(addslashes($judge['email'])); ?>')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-warning" onclick="openResetModal(<?php echo $judge['id']; ?>, '<?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>')">
                    <i class="fas fa-key"></i> Reset Password
                </button>
                <a href="?page=toggle_judge&id=<?php echo $judge['id']; ?>" class="btn <?php echo $judge['status'] === 'active' ? 'btn-secondary' : 'btn-success'; ?>"
                   onclick="return confirm('<?php echo $judge['status'] === 'active' ? 'Disable' : 'Enable'; ?> this judge?')">
                    <i class="fas fa-<?php echo $judge['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                    <?php echo $judge['status'] === 'active' ? 'Disable' : 'Enable'; ?>
                </a>
                <?php if ((int)$judge['submitted_count'] === 0): ?>
                <a href="?page=delete_judge&id=<?php echo $judge['id']; ?>" class="btn btn-danger"
                   onclick="return confirm('Remove judge <?php echo htmlspecialchars(addslashes($judge['full_name'])); ?>? This cannot be undone.')">
                    <i class="fas fa-trash"></i> Remove
                </a>
                <?php endif; ?>
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
</script>
</body>
</html>
