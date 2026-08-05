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

        /* Media loader */
        .media-loader { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:280px; background:#1a1a2e; gap:14px; padding:30px; }
        .spinner { width:48px; height:48px; border:4px solid rgba(255,255,255,.15); border-top-color:var(--primary); border-radius:50%; animation:spin .8s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .load-text { color:rgba(255,255,255,.6); font-size:13px; }
        .load-bar-wrap { width:160px; height:4px; background:rgba(255,255,255,.1); border-radius:2px; overflow:hidden; }
        .load-bar { height:100%; width:0; background:var(--primary); border-radius:2px; transition:width .3s; }

        /* Fullscreen button on media */
        .artwork-media { position:relative; }
        .artwork-media img { cursor:zoom-in; }
        .fs-btn { position:absolute; bottom:12px; right:12px; background:rgba(0,0,0,.65); color:#fff; border:none; border-radius:8px; padding:8px 14px; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px; transition:background .2s; backdrop-filter:blur(4px); }
        .fs-btn:hover { background:rgba(30,144,255,.85); }

        /* Fullscreen overlay */
        .fs-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.95); z-index:9999; flex-direction:column; align-items:center; justify-content:center; }
        .fs-overlay.active { display:flex; }
        .fs-content { max-width:95vw; max-height:90vh; display:flex; align-items:center; justify-content:center; }
        .fs-content img  { max-width:95vw; max-height:88vh; object-fit:contain; border-radius:4px; box-shadow:0 0 60px rgba(0,0,0,.8); }
        .fs-content video{ max-width:95vw; max-height:88vh; border-radius:4px; box-shadow:0 0 60px rgba(0,0,0,.8); }
        .fs-close { position:fixed; top:20px; right:24px; background:rgba(255,255,255,.1); border:none; color:#fff; width:44px; height:44px; border-radius:50%; font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; z-index:10000; }
        .fs-close:hover { background:rgba(255,80,80,.7); }
        .fs-hint { position:fixed; bottom:18px; color:rgba(255,255,255,.4); font-size:12px; }
        .fs-hint kbd { background:rgba(255,255,255,.15); padding:2px 7px; border-radius:4px; font-size:11px; }
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

                    <?php if (!empty($submission['admin_note'])): ?>
                    <div style="background:#fffbeb; border-left:4px solid #f59e0b; border-radius:10px; padding:16px 20px;">
                        <div style="font-size:12px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">
                            <i class="fas fa-comment-alt"></i> Note from Administrator
                        </div>
                        <div style="color:#374151; font-size:14px; line-height:1.7;"><?php echo nl2br(htmlspecialchars($submission['admin_note'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($criteria)): ?>
                    <div style="background:#fff3cd; border-left:4px solid #ffc107; border-radius:10px; padding:16px 20px;">
                        <div style="font-weight:700; color:#856404; margin-bottom:6px;"><i class="fas fa-exclamation-triangle"></i> Judging criteria not yet configured</div>
                        <div style="font-size:14px; color:#533f03;">The administrator has not set up scoring criteria yet. Please check back later or contact the competition organiser.</div>
                    </div>
                    <?php endif; ?>

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

                    <?php if (!empty($criteria)): ?>
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
                    <?php else: ?>
                    <div class="action-bar">
                        <a href="?page=judge_dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                    <?php endif; ?>

                </div><!-- .score-panel -->
            </form>

            <!-- Right: anonymous artwork -->
            <div class="artwork-panel">

                <!-- Loading spinner -->
                <div class="media-loader" id="mediaLoader">
                    <div class="spinner"></div>
                    <div class="load-text">Loading artwork…</div>
                    <div class="load-bar-wrap"><div class="load-bar" id="loadBar"></div></div>
                </div>

                <div class="artwork-media" id="artworkMedia" style="display:none;">
                    <?php if ($webPath && $isImage): ?>
                        <img src="<?php echo htmlspecialchars($webPath); ?>"
                             alt="Artwork"
                             id="artImg"
                             onclick="openFullscreen('image')"
                             title="Click to view fullscreen">
                        <button class="fs-btn" onclick="openFullscreen('image')" title="View fullscreen">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                    <?php elseif ($webPath): ?>
                        <video src="<?php echo htmlspecialchars($webPath); ?>"
                               controls
                               preload="metadata"
                               id="artVideo"
                               onloadedmetadata="mediaReady()"></video>
                        <button class="fs-btn" onclick="openFullscreen('video')" title="View fullscreen">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                    <?php else: ?>
                        <div style="color:#555; padding:40px; text-align:center;">
                            <i class="fas fa-file" style="font-size:60px; display:block; margin-bottom:12px;"></i>
                            Media not available
                        </div>
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

            <!-- Fullscreen lightbox -->
            <div class="fs-overlay" id="fsOverlay" onclick="closeFullscreen()">
                <button class="fs-close" onclick="closeFullscreen()"><i class="fas fa-times"></i></button>
                <div class="fs-content" onclick="event.stopPropagation()">
                    <img id="fsImg" src="" alt="" style="display:none;">
                    <video id="fsVideo" controls style="display:none;"></video>
                </div>
                <div class="fs-hint">Click outside or press <kbd>Esc</kbd> to close</div>
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

// ── Media loading with progress bar ──────────────────────────────────────────
const isImage = <?php echo $isImage ? 'true' : 'false'; ?>;
const webPath = <?php echo $webPath ? json_encode($webPath) : 'null'; ?>;

function mediaReady() {
    document.getElementById('mediaLoader').style.display  = 'none';
    document.getElementById('artworkMedia').style.display = 'flex';
}

function simulateProgress(onDone) {
    const bar = document.getElementById('loadBar');
    let pct = 0;
    const iv = setInterval(() => {
        pct = Math.min(pct + Math.random() * 18, 90);
        bar.style.width = pct + '%';
    }, 120);
    return { stop() { clearInterval(iv); bar.style.width = '100%'; setTimeout(onDone, 200); } };
}

if (webPath) {
    if (isImage) {
        const progress = simulateProgress(mediaReady);
        const img = document.getElementById('artImg');
        if (img) {
            if (img.complete && img.naturalWidth) {
                progress.stop();
            } else {
                img.addEventListener('load',  () => progress.stop());
                img.addEventListener('error', () => { progress.stop(); });
            }
        }
    } else {
        // Video: show real progress via XMLHttpRequest range if possible, else simulate
        const video = document.getElementById('artVideo');
        if (video) {
            const progress = simulateProgress(mediaReady);
            video.addEventListener('canplay', () => progress.stop());
            video.addEventListener('error',   () => progress.stop());
        }
    }
} else {
    mediaReady(); // no media — just show the panel
}

// ── Fullscreen lightbox ───────────────────────────────────────────────────────
function openFullscreen(type) {
    const overlay  = document.getElementById('fsOverlay');
    const fsImg    = document.getElementById('fsImg');
    const fsVideo  = document.getElementById('fsVideo');

    if (type === 'image') {
        fsImg.src            = document.getElementById('artImg').src;
        fsImg.style.display  = 'block';
        fsVideo.style.display = 'none';
        fsVideo.pause && fsVideo.pause();
    } else {
        const src             = document.getElementById('artVideo').src;
        fsVideo.src           = src;
        fsVideo.style.display = 'block';
        fsImg.style.display   = 'none';
        fsVideo.play();
    }
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeFullscreen() {
    const overlay = document.getElementById('fsOverlay');
    const fsVideo = document.getElementById('fsVideo');
    overlay.classList.remove('active');
    fsVideo.pause && fsVideo.pause();
    fsVideo.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFullscreen(); });
</script>
</body>
</html>
