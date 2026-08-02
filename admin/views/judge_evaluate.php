<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Artwork - GREATER Judging Portal</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .eval-layout { display:grid; grid-template-columns:1fr 420px; gap:24px; align-items:start; }
        @media(max-width:1024px) { .eval-layout { grid-template-columns:1fr; } }

        /* Artwork preview panel */
        .artwork-panel { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.08); overflow:hidden; position:sticky; top:80px; }
        .artwork-media { width:100%; background:#1a1a2e; display:flex; align-items:center; justify-content:center; min-height:280px; }
        .artwork-media img  { width:100%; max-height:480px; object-fit:contain; display:block; }
        .artwork-media video{ width:100%; max-height:480px; display:block; }
        .artwork-meta { padding:20px; }
        .anon-code { font-family:monospace; font-size:13px; font-weight:800; letter-spacing:2px; color:var(--primary); text-transform:uppercase; background:#e8f0fe; display:inline-block; padding:4px 12px; border-radius:20px; margin-bottom:12px; }
        .artwork-title-display { font-size:19px; font-weight:700; color:var(--dark); margin-bottom:8px; }
        .artwork-desc { color:var(--gray); font-size:14px; line-height:1.6; margin-bottom:12px; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--primary); text-decoration:none; font-size:14px; font-weight:600; margin-bottom:20px; }
        .back-link:hover { text-decoration:underline; }

        /* Score form panel */
        .score-panel { display:flex; flex-direction:column; gap:18px; }

        .criterion-card { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.07); padding:20px 24px; }
        .criterion-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
        .criterion-name { font-size:15px; font-weight:700; color:var(--dark); }
        .criterion-desc { font-size:13px; color:var(--gray); margin-top:3px; }
        .score-display { font-size:24px; font-weight:800; color:var(--primary); white-space:nowrap; }
        .score-display .max { font-size:14px; color:var(--gray); font-weight:400; }

        input[type="range"] { width:100%; -webkit-appearance:none; height:6px; border-radius:3px; background:#e2e8f0; outline:none; cursor:pointer; margin-top:6px; }
        input[type="range"]::-webkit-slider-thumb { -webkit-appearance:none; width:20px; height:20px; border-radius:50%; background:var(--primary); cursor:pointer; box-shadow:0 2px 6px rgba(30,144,255,.4); }
        input[type="range"]::-moz-range-thumb { width:20px; height:20px; border-radius:50%; background:var(--primary); cursor:pointer; border:none; }

        .score-input-row { display:flex; align-items:center; gap:12px; margin-top:10px; }
        .score-number-input { width:70px; text-align:center; font-size:18px; font-weight:700; color:var(--primary); border:2px solid #e2e8f0; border-radius:8px; padding:6px; }
        .score-number-input:focus { border-color:var(--primary); outline:none; }

        /* Notes section */
        .notes-card { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.07); padding:24px; }
        .notes-card h3 { font-size:16px; font-weight:700; margin-bottom:18px; color:var(--dark); }
        .notes-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        @media(max-width:700px) { .notes-grid { grid-template-columns:1fr; } }
        textarea.form-input { resize:vertical; min-height:90px; }

        /* Totals & actions */
        .total-bar { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:12px; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; }
        .total-bar .total-label { font-size:14px; opacity:.85; }
        .total-bar .total-value { font-size:32px; font-weight:800; }
        .action-bar { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.07); padding:20px 24px; display:flex; gap:12px; flex-wrap:wrap; justify-content:space-between; align-items:center; }
        .action-bar .left  { display:flex; gap:10px; flex-wrap:wrap; }
        .btn-draft  { background:#ffc107; color:#212529; border:none; }
        .btn-draft:hover { background:#e0a800; }
        .btn-submit { background:#28a745; color:#fff; border:none; }
        .btn-submit:hover { background:#218838; }

        /* Read-only overlay */
        .readonly-banner { background:#d4edda; color:#155724; border-radius:10px; padding:14px 20px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-weight:600; }
        .score-slider-wrap.disabled input[type="range"] { pointer-events:none; opacity:.5; }
        .score-slider-wrap.disabled input[type="number"] { pointer-events:none; background:#f8f9fa; }
        textarea:disabled { background:#f8f9fa; color:#6c757d; }
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

        <a href="?page=judge_dashboard" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Artworks</a>

        <?php
        $isReadonly = ($evaluation && ($evaluation['status'] ?? '') === 'submitted');
        $isImage  = strpos($submission['fileType'] ?? '', 'image') !== false;
        $fileBase = basename($submission['filePath'] ?? $submission['fileName'] ?? '');
        $webPath  = $fileBase ? '../uploads/' . $fileBase : '';
        $totalMax = array_sum(array_column($criteria, 'max_score'));
        ?>

        <?php if ($isReadonly): ?>
        <div class="readonly-banner">
            <i class="fas fa-lock"></i>
            Your evaluation for this artwork has been submitted and is now read-only.
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="?page=reopen_evaluation&id=<?php echo $evaluation['id']; ?>" class="btn btn-warning btn-sm" style="margin-left:auto;">Reopen</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="eval-layout">

            <!-- Left: scoring form -->
            <form method="POST" action="?page=judge_save" id="evalForm">
                <input type="hidden" name="submission_id" value="<?php echo (int)$submission['id']; ?>">
                <input type="hidden" name="action" id="formAction" value="draft">

                <div class="score-panel">

                    <?php foreach ($criteria as $c):
                        $cid  = (int)$c['id'];
                        $max  = (int)$c['max_score'];
                        $val  = (int)($scores[$cid] ?? 0);
                    ?>
                    <div class="criterion-card">
                        <div class="criterion-header">
                            <div>
                                <div class="criterion-name"><?php echo htmlspecialchars($c['name']); ?></div>
                                <?php if (!empty($c['description'])): ?>
                                <div class="criterion-desc"><?php echo htmlspecialchars($c['description']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="score-display">
                                <span id="disp_<?php echo $cid; ?>"><?php echo $val; ?></span>
                                <span class="max"> / <?php echo $max; ?></span>
                            </div>
                        </div>
                        <div class="score-slider-wrap <?php echo $isReadonly ? 'disabled' : ''; ?>">
                            <input type="range"
                                   name="scores[<?php echo $cid; ?>]"
                                   id="slider_<?php echo $cid; ?>"
                                   min="0" max="<?php echo $max; ?>"
                                   value="<?php echo $val; ?>"
                                   <?php echo $isReadonly ? 'disabled' : ''; ?>
                                   oninput="syncScore(<?php echo $cid; ?>, <?php echo $max; ?>, 'slider')">
                            <div class="score-input-row">
                                <span style="font-size:13px; color:var(--gray);">0</span>
                                <input type="number"
                                       class="score-number-input"
                                       id="num_<?php echo $cid; ?>"
                                       min="0" max="<?php echo $max; ?>"
                                       value="<?php echo $val; ?>"
                                       <?php echo $isReadonly ? 'disabled' : ''; ?>
                                       oninput="syncScore(<?php echo $cid; ?>, <?php echo $max; ?>, 'num')">
                                <span style="font-size:13px; color:var(--gray);"><?php echo $max; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Running total -->
                    <div class="total-bar">
                        <div>
                            <div class="total-label">Your Total Score</div>
                            <div class="total-value"><span id="runningTotal">0</span> / <?php echo $totalMax; ?></div>
                        </div>
                        <div style="text-align:right; opacity:.8; font-size:13px;">
                            Sum across all <?php echo count($criteria); ?> criteria
                        </div>
                    </div>

                    <!-- Judge's notes -->
                    <div class="notes-card">
                        <h3><i class="fas fa-sticky-note"></i> Judge Notes (Visible to admin only)</h3>
                        <div class="notes-grid">
                            <div class="form-group">
                                <label class="form-label">Strengths</label>
                                <textarea class="form-input" name="strengths" <?php echo $isReadonly ? 'disabled' : ''; ?> placeholder="What stands out positively?"><?php echo htmlspecialchars($evaluation['strengths'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Weaknesses</label>
                                <textarea class="form-input" name="weaknesses" <?php echo $isReadonly ? 'disabled' : ''; ?> placeholder="What could be improved?"><?php echo htmlspecialchars($evaluation['weaknesses'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:4px;">
                            <label class="form-label">Recommendations</label>
                            <textarea class="form-input" name="recommendations" <?php echo $isReadonly ? 'disabled' : ''; ?> placeholder="Any specific recommendations?" style="min-height:70px;"><?php echo htmlspecialchars($evaluation['recommendations'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Overall Comments</label>
                            <textarea class="form-input" name="overall_comments" <?php echo $isReadonly ? 'disabled' : ''; ?> placeholder="General comments about this artwork…" style="min-height:80px;"><?php echo htmlspecialchars($evaluation['overall_comments'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <?php if (!$isReadonly): ?>
                    <div class="action-bar">
                        <div class="left">
                            <button type="button" class="btn btn-draft" onclick="submitForm('draft')">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="button" class="btn btn-submit" onclick="confirmSubmit()">
                                <i class="fas fa-check-double"></i> Final Submit
                            </button>
                        </div>
                        <a href="?page=judge_dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="action-bar">
                        <span style="color:#155724; font-weight:600;"><i class="fas fa-lock"></i> Submitted on <?php echo date('M j, Y g:i A', strtotime($evaluation['submitted_at'])); ?></span>
                        <a href="?page=judge_dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <?php endif; ?>

                </div><!-- .score-panel -->
            </form>

            <!-- Right: anonymous artwork -->
            <div class="artwork-panel">
                <div class="artwork-media">
                    <?php if ($webPath && $isImage): ?>
                        <img src="<?php echo htmlspecialchars($webPath); ?>" alt="Artwork">
                    <?php elseif ($webPath): ?>
                        <video src="<?php echo htmlspecialchars($webPath); ?>" controls preload="metadata"></video>
                    <?php else: ?>
                        <div style="color:#555; padding:40px; text-align:center;"><i class="fas fa-file" style="font-size:60px; display:block; margin-bottom:12px;"></i>Media not available</div>
                    <?php endif; ?>
                </div>
                <div class="artwork-meta">
                    <div class="anon-code"><?php echo htmlspecialchars($submission['competition_code']); ?></div>
                    <div class="artwork-title-display"><?php echo htmlspecialchars($submission['artwork_name']); ?></div>
                    <div style="margin-bottom:10px;">
                        <span class="status-badge" style="background:#e8f0fe; color:var(--primary);">
                            <?php echo $submission['category'] === 'photography_paint' ? 'Photography / Paint' : 'Short Video'; ?>
                        </span>
                    </div>
                    <?php if (!empty($submission['description'])): ?>
                    <div class="artwork-desc"><?php echo nl2br(htmlspecialchars($submission['description'])); ?></div>
                    <?php endif; ?>
                    <div style="font-size:12px; color:#aaa;">Submitted <?php echo date('M j, Y', strtotime($submission['submissionDate'])); ?></div>
                </div>
            </div>

        </div><!-- .eval-layout -->

    </main>
</div>

<script>
const MAX_SCORES = <?php echo json_encode(array_column($criteria, 'max_score', 'id')); ?>;

function syncScore(cid, max, source) {
    const slider = document.getElementById('slider_' + cid);
    const num    = document.getElementById('num_'    + cid);
    const disp   = document.getElementById('disp_'  + cid);

    let v = parseInt(source === 'slider' ? slider.value : num.value) || 0;
    v = Math.max(0, Math.min(max, v));

    slider.value = v;
    num.value    = v;
    disp.textContent = v;
    updateTotal();
}

function updateTotal() {
    let total = 0;
    Object.keys(MAX_SCORES).forEach(cid => {
        const el = document.getElementById('num_' + cid);
        if (el) total += parseInt(el.value) || 0;
    });
    document.getElementById('runningTotal').textContent = total;
}

function submitForm(action) {
    document.getElementById('formAction').value = action;
    document.getElementById('evalForm').submit();
}

function confirmSubmit() {
    if (confirm('Final submit cannot be edited after confirmation.\n\nAre you sure you want to submit your evaluation?')) {
        submitForm('submit');
    }
}

// Init running total on page load
updateTotal();
</script>
</body>
</html>
