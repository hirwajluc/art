<?php
// views/email_campaigns.php
// $currentUser, $recentCampaigns, $statsNotSubmitted, $statsSubmitted passed from router
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Email Campaigns</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .email-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        @media(max-width:900px){ .email-section { grid-template-columns:1fr; } }

        .email-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .email-card-header {
            padding: 22px 28px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .email-card-header.no-submit  { background: linear-gradient(135deg,#f97316,#ef4444); }
        .email-card-header.submitted   { background: linear-gradient(135deg,#10b981,#059669); }
        .email-card-header i { font-size: 24px; }
        .email-card-header h3 { font-size: 18px; font-weight: 600; }
        .email-card-header .badge {
            margin-left: auto;
            background: rgba(255,255,255,.25);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        .email-card-body { padding: 28px; }

        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .form-input, .form-textarea {
            width: 100%;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: #1f2937;
            transition: border-color .2s;
            background: #f9fafb;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
        }
        .form-textarea { resize: vertical; min-height: 160px; font-family: inherit; }

        .char-count { font-size: 11px; color: #9ca3af; text-align: right; margin-top: 4px; }

        .preview-box {
            background: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 16px;
            font-size: 13px;
            color: #374151;
            min-height: 80px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .btn-send {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            color: #fff;
        }
        .btn-send.orange { background: linear-gradient(135deg,#f97316,#ef4444); }
        .btn-send.green  { background: linear-gradient(135deg,#10b981,#059669); }
        .btn-send:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.15); }
        .btn-send:disabled { background: #9ca3af; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Log table */
        .log-section { background: #fff; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,.08); overflow: hidden; }
        .log-header { background: var(--primary,#1E90FF); color: #fff; padding: 20px 28px; display:flex; align-items:center; gap:10px; }
        .log-header h3 { font-size: 18px; font-weight: 600; }
        .log-body { padding: 0; }

        table.log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.log-table th { background:#f9fafb; color:#6b7280; font-weight:600; padding:12px 16px; text-align:left; border-bottom:1px solid #e5e7eb; }
        table.log-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; color:#374151; vertical-align:top; }
        table.log-table tr:last-child td { border-bottom:none; }
        table.log-table tr:hover td { background:#f9fafb; }

        .badge-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-type.no-sub  { background:#fee2e2; color:#b91c1c; }
        .badge-type.sub-all { background:#d1fae5; color:#065f46; }

        /* Alert */
        .alert { border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#d1fae5; color:#065f46; border-left: 4px solid #10b981; }
        .alert-error   { background:#fee2e2; color:#b91c1c; border-left: 4px solid #ef4444; }
        .alert-info    { background:#dbeafe; color:#1e40af; border-left: 4px solid #3b82f6; }

        /* Modal overlay */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.45); z-index:1000;
            align-items:center; justify-content:center;
        }
        .modal-overlay.show { display:flex; }
        .modal {
            background:#fff; border-radius:16px; padding:32px;
            max-width:480px; width:92%; box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .modal h4 { font-size:20px; font-weight:700; color:#1f2937; margin-bottom:12px; }
        .modal p  { color:#4b5563; font-size:15px; line-height:1.6; margin-bottom:24px; }
        .modal-actions { display:flex; gap:12px; justify-content:flex-end; }
        .btn-modal-cancel { padding:10px 22px; background:#f3f4f6; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; color:#374151; }
        .btn-modal-confirm { padding:10px 22px; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; color:#fff; }
        .btn-modal-confirm.orange { background:linear-gradient(135deg,#f97316,#ef4444); }
        .btn-modal-confirm.green  { background:linear-gradient(135deg,#10b981,#059669); }

        .spinner-inline {
            display:inline-block; width:16px; height:16px;
            border:2px solid rgba(255,255,255,.4);
            border-top-color:#fff;
            border-radius:50%;
            animation: spin .7s linear infinite;
            vertical-align:middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <div style="padding: 0 0 30px;">
            <h2 style="font-size:24px; font-weight:700; color:#1f2937; margin-bottom:6px;">
                <i class="fas fa-envelope" style="color:#667eea;"></i> Email Campaigns
            </h2>
            <p style="color:#6b7280; font-size:15px;">Send custom emails to participants</p>
        </div>

        <!-- Flash messages -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <!-- Stats strip -->
        <div style="display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div style="background:#fff; border-radius:12px; padding:18px 24px; box-shadow:0 3px 12px rgba(0,0,0,.07); display:flex; align-items:center; gap:14px; flex:1; min-width:200px;">
                <div style="width:48px;height:48px;background:linear-gradient(135deg,#f97316,#ef4444);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-times" style="color:#fff; font-size:20px;"></i>
                </div>
                <div>
                    <div style="font-size:28px; font-weight:700; color:#1f2937;"><?php echo (int)$statsNotSubmitted; ?></div>
                    <div style="font-size:13px; color:#6b7280;">Registered, No Submission</div>
                </div>
            </div>
            <div style="background:#fff; border-radius:12px; padding:18px 24px; box-shadow:0 3px 12px rgba(0,0,0,.07); display:flex; align-items:center; gap:14px; flex:1; min-width:200px;">
                <div style="width:48px;height:48px;background:linear-gradient(135deg,#10b981,#059669);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-check-double" style="color:#fff; font-size:20px;"></i>
                </div>
                <div>
                    <div style="font-size:28px; font-weight:700; color:#1f2937;"><?php echo (int)$statsSubmitted; ?></div>
                    <div style="font-size:13px; color:#6b7280;">Submitted Work</div>
                </div>
            </div>
        </div>

        <!-- Email compose cards -->
        <div class="email-section">

            <!-- Card 1: No submission -->
            <div class="email-card">
                <div class="email-card-header no-submit">
                    <i class="fas fa-user-clock"></i>
                    <h3>Remind: No Submission Yet</h3>
                    <span class="badge"><?php echo (int)$statsNotSubmitted; ?> recipients</span>
                </div>
                <div class="email-card-body">
                    <p style="color:#6b7280; font-size:13px; margin-bottom:20px;">
                        Send a reminder email to all participants who registered but have <strong>not yet submitted</strong> their artwork.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Email Subject *</label>
                        <input type="text" class="form-input" id="ns_subject" maxlength="255"
                               placeholder="e.g., Don't forget to submit your artwork!"
                               oninput="updateCharCount('ns_subject','ns_subject_count',255)">
                        <div class="char-count"><span id="ns_subject_count">0</span>/255</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Body *</label>
                        <textarea class="form-textarea" id="ns_body" maxlength="5000"
                                  placeholder="Write your custom message here...&#10;&#10;You can use {name} to personalise with the participant's name."
                                  oninput="updateCharCount('ns_body','ns_body_count',5000); updatePreview('ns_body','ns_preview')"></textarea>
                        <div class="char-count"><span id="ns_body_count">0</span>/5000</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preview</label>
                        <div class="preview-box" id="ns_preview">Your message preview will appear here…</div>
                    </div>
                    <button class="btn-send orange" id="btn_ns" onclick="confirmSend('no_submission')">
                        <i class="fas fa-paper-plane"></i> Send to <?php echo (int)$statsNotSubmitted; ?> Participants
                    </button>
                </div>
            </div>

            <!-- Card 2: Submitted -->
            <div class="email-card">
                <div class="email-card-header submitted">
                    <i class="fas fa-images"></i>
                    <h3>Message Submitters</h3>
                    <span class="badge"><?php echo (int)$statsSubmitted; ?> recipients</span>
                </div>
                <div class="email-card-body">
                    <p style="color:#6b7280; font-size:13px; margin-bottom:20px;">
                        Send a message to all participants who have <strong>already submitted</strong> their artwork.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Email Subject *</label>
                        <input type="text" class="form-input" id="sub_subject" maxlength="255"
                               placeholder="e.g., Thank you for your submission!"
                               oninput="updateCharCount('sub_subject','sub_subject_count',255)">
                        <div class="char-count"><span id="sub_subject_count">0</span>/255</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Body *</label>
                        <textarea class="form-textarea" id="sub_body" maxlength="5000"
                                  placeholder="Write your custom message here...&#10;&#10;You can use {name} to personalise with the participant's name."
                                  oninput="updateCharCount('sub_body','sub_body_count',5000); updatePreview('sub_body','sub_preview')"></textarea>
                        <div class="char-count"><span id="sub_body_count">0</span>/5000</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preview</label>
                        <div class="preview-box" id="sub_preview">Your message preview will appear here…</div>
                    </div>
                    <button class="btn-send green" id="btn_sub" onclick="confirmSend('submitted')">
                        <i class="fas fa-paper-plane"></i> Send to <?php echo (int)$statsSubmitted; ?> Participants
                    </button>
                </div>
            </div>
        </div>

        <!-- Campaign log -->
        <div class="log-section">
            <div class="log-header">
                <i class="fas fa-history"></i>
                <h3>Recent Email Campaigns</h3>
            </div>
            <div class="log-body">
                <?php if (empty($recentCampaigns)): ?>
                    <div style="text-align:center; padding:40px; color:#9ca3af;">
                        <i class="fas fa-inbox" style="font-size:40px; margin-bottom:12px;"></i>
                        <p>No email campaigns sent yet.</p>
                    </div>
                <?php else: ?>
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Audience</th>
                                <th>Sent</th>
                                <th>Failed</th>
                                <th>Sent By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentCampaigns as $c): ?>
                            <tr>
                                <td style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($c['subject']); ?>">
                                    <?php echo htmlspecialchars($c['subject']); ?>
                                </td>
                                <td>
                                    <?php if ($c['recipient_type'] === 'no_submission'): ?>
                                        <span class="badge-type no-sub">No Submission</span>
                                    <?php else: ?>
                                        <span class="badge-type sub-all">Submitters</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:#065f46; font-weight:600;"><?php echo (int)$c['sent_count']; ?></td>
                                <td style="color:<?php echo $c['failed_count'] > 0 ? '#b91c1c' : '#9ca3af'; ?>; font-weight:600;"><?php echo (int)$c['failed_count']; ?></td>
                                <td><?php echo htmlspecialchars($c['admin_name'] ?? 'Admin'); ?></td>
                                <td style="white-space:nowrap; color:#9ca3af;"><?php echo date('M j, Y H:i', strtotime($c['sent_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <h4 id="modal_title">Confirm Send</h4>
        <p id="modal_body">Are you sure you want to send this email campaign?</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-modal-confirm" id="modal_confirm_btn" onclick="doSend()">
                <i class="fas fa-paper-plane"></i> Send Now
            </button>
        </div>
    </div>
</div>

<script>
let pendingType = null;

function updateCharCount(inputId, countId, max) {
    const val = document.getElementById(inputId).value.length;
    document.getElementById(countId).textContent = val;
}

function updatePreview(bodyId, previewId) {
    const body = document.getElementById(bodyId).value;
    const preview = document.getElementById(previewId);
    preview.textContent = body || 'Your message preview will appear here…';
}

function confirmSend(type) {
    const subjectId = type === 'no_submission' ? 'ns_subject' : 'sub_subject';
    const bodyId    = type === 'no_submission' ? 'ns_body'    : 'sub_body';
    const subject   = document.getElementById(subjectId).value.trim();
    const body      = document.getElementById(bodyId).value.trim();

    if (!subject) { alert('Please enter an email subject.'); document.getElementById(subjectId).focus(); return; }
    if (!body)    { alert('Please write the email body.'); document.getElementById(bodyId).focus(); return; }
    if (body.length < 20) { alert('Email body is too short (minimum 20 characters).'); return; }

    const count = type === 'no_submission'
        ? <?php echo (int)$statsNotSubmitted; ?>
        : <?php echo (int)$statsSubmitted; ?>;

    if (count === 0) {
        alert('There are no recipients for this campaign right now.');
        return;
    }

    pendingType = type;
    const label = type === 'no_submission' ? 'participants who have not submitted' : 'participants who have submitted';
    document.getElementById('modal_title').textContent = 'Confirm Email Campaign';
    document.getElementById('modal_body').innerHTML =
        `You are about to send "<strong>${escapeHtml(subject)}</strong>" to <strong>${count} ${label}</strong>.<br><br>This action cannot be undone.`;

    const btn = document.getElementById('modal_confirm_btn');
    btn.className = 'btn-modal-confirm ' + (type === 'no_submission' ? 'orange' : 'green');
    document.getElementById('confirmModal').classList.add('show');
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('show');
    pendingType = null;
}

async function doSend() {
    if (!pendingType) return;
    const type      = pendingType;
    const subjectId = type === 'no_submission' ? 'ns_subject' : 'sub_subject';
    const bodyId    = type === 'no_submission' ? 'ns_body'    : 'sub_body';
    const btnSend   = document.getElementById(type === 'no_submission' ? 'btn_ns' : 'btn_sub');
    const confirmBtn = document.getElementById('modal_confirm_btn');

    closeModal();

    btnSend.disabled = true;
    btnSend.innerHTML = '<span class="spinner-inline"></span> Sending…';

    try {
        const resp = await fetch('email_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type:    type,
                subject: document.getElementById(subjectId).value.trim(),
                body:    document.getElementById(bodyId).value.trim()
            })
        });
        const data = await resp.json();

        if (data.success) {
            showAlert('success', `✅ Campaign sent! ${data.sent} delivered, ${data.failed} failed.`);
            document.getElementById(subjectId).value = '';
            document.getElementById(bodyId).value    = '';
            document.getElementById(type === 'no_submission' ? 'ns_preview' : 'sub_preview').textContent = 'Your message preview will appear here…';
            setTimeout(() => location.reload(), 2500);
        } else {
            showAlert('error', '❌ ' + (data.message || 'Failed to send campaign.'));
        }
    } catch (e) {
        showAlert('error', '❌ Network error. Please try again.');
    } finally {
        btnSend.disabled = false;
        btnSend.innerHTML = `<i class="fas fa-paper-plane"></i> Send`;
    }
}

function showAlert(type, msg) {
    const div = document.createElement('div');
    div.className = 'alert alert-' + type;
    div.innerHTML = msg;
    div.style.position = 'fixed';
    div.style.top = '20px';
    div.style.right = '20px';
    div.style.zIndex = '9999';
    div.style.minWidth = '320px';
    div.style.boxShadow = '0 8px 30px rgba(0,0,0,.15)';
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 5000);
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on backdrop click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
