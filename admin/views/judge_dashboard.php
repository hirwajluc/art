<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Artworks - GREATER Judging Portal</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .progress-card { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:14px; padding:28px 32px; margin-bottom:28px; display:grid; grid-template-columns:1fr auto; gap:24px; align-items:center; }
        .progress-text h2 { font-size:22px; margin-bottom:6px; }
        .progress-text p  { opacity:.85; font-size:15px; }
        .progress-ring { width:100px; height:100px; flex-shrink:0; }
        .progress-ring circle { fill:none; stroke:rgba(255,255,255,.2); stroke-width:10; }
        .progress-ring .fg { stroke:#fff; stroke-linecap:round; transition:stroke-dashoffset .8s ease; }
        .art-card { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.07); overflow:hidden; display:flex; flex-direction:column; transition:transform .2s, box-shadow .2s; }
        .art-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,.12); }
        .art-thumb { width:100%; height:180px; object-fit:cover; background:#f0f4ff; display:flex; align-items:center; justify-content:center; font-size:48px; color:#ccc; }
        .art-thumb img, .art-thumb video { width:100%; height:100%; object-fit:cover; display:block; }
        .art-info { padding:16px; flex:1; }
        .art-code { font-size:12px; font-family:monospace; color:var(--primary); font-weight:700; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:6px; }
        .art-title { font-size:15px; font-weight:600; color:var(--dark); margin-bottom:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .art-footer { padding:12px 16px; background:#fafafa; border-top:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center; }
        .eval-badge-done    { background:#d4edda; color:#155724; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; }
        .eval-badge-draft   { background:#fff3cd; color:#856404; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; }
        .eval-badge-pending { background:#f8d7da; color:#721c24; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; }
        .mini-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        .mini-stat { background:#fff; border-radius:10px; padding:16px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .mini-stat .n { font-size:28px; font-weight:800; color:var(--primary); }
        .mini-stat .l { font-size:12px; color:var(--gray); text-transform:uppercase; margin-top:2px; }
        .artwork-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px; }
        @media(max-width:600px) { .mini-stats { grid-template-columns:repeat(2,1fr); } .artwork-grid { grid-template-columns:1fr 1fr; } }
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

        <!-- Progress banner -->
        <div class="progress-card">
            <div class="progress-text">
                <h2>Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?></h2>
                <p>
                    You have evaluated <strong><?php echo $stats['submitted']; ?></strong> of
                    <strong><?php echo $stats['total']; ?></strong> artworks
                    (<?php echo $stats['pct']; ?>% complete).
                    <?php if ($stats['drafted'] > 0): ?>
                    &nbsp;· <?php echo $stats['drafted']; ?> draft(s) saved.
                    <?php endif; ?>
                </p>
            </div>
            <?php $pct = $stats['pct']; $r = 45; $circ = 2*M_PI*$r; $dash = $circ * (1 - $pct/100); ?>
            <svg class="progress-ring" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="<?php echo $r; ?>"/>
                <circle cx="50" cy="50" r="<?php echo $r; ?>" class="fg"
                    stroke-dasharray="<?php echo round($circ,2); ?>"
                    stroke-dashoffset="<?php echo round($dash,2); ?>"
                    transform="rotate(-90 50 50)"/>
                <text x="50" y="55" text-anchor="middle" fill="white" font-size="18" font-weight="800"><?php echo $pct; ?>%</text>
            </svg>
        </div>

        <!-- Mini stats -->
        <div class="mini-stats">
            <div class="mini-stat"><div class="n"><?php echo $stats['total']; ?></div><div class="l">Total</div></div>
            <div class="mini-stat"><div class="n" style="color:#28a745;"><?php echo $stats['submitted']; ?></div><div class="l">Submitted</div></div>
            <div class="mini-stat"><div class="n" style="color:#d97706;"><?php echo $stats['drafted']; ?></div><div class="l">In Draft</div></div>
            <div class="mini-stat"><div class="n" style="color:#dc3545;"><?php echo $stats['pending']; ?></div><div class="l">Pending</div></div>
        </div>

        <!-- Filters -->
        <div style="background:#fff; border-radius:10px; padding:18px 20px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,.06); display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <form method="GET" action="" style="display:contents;">
                <input type="hidden" name="page" value="judge_dashboard">
                <input class="form-input" type="text" name="search" placeholder="Search by code or title…" value="<?php echo htmlspecialchars($search); ?>" style="max-width:260px;">
                <select name="category" class="form-select" style="max-width:180px;">
                    <option value="">All Categories</option>
                    <option value="photography_paint" <?php echo $catFilter === 'photography_paint' ? 'selected' : ''; ?>>Photography / Paint</option>
                    <option value="short_video"       <?php echo $catFilter === 'short_video'       ? 'selected' : ''; ?>>Short Video</option>
                </select>
                <select name="status" class="form-select" style="max-width:160px;">
                    <option value="">All Status</option>
                    <option value="not_started" <?php echo $evalFilter === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="draft"       <?php echo $evalFilter === 'draft'       ? 'selected' : ''; ?>>Draft Saved</option>
                    <option value="submitted"   <?php echo $evalFilter === 'submitted'   ? 'selected' : ''; ?>>Submitted</option>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($search || $catFilter || $evalFilter): ?>
                <a href="?page=judge_dashboard" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <div style="margin-left:auto; color:var(--gray); font-size:14px;">
                <?php echo count($submissions); ?> artwork(s) shown
            </div>
        </div>

        <!-- Artwork grid -->
        <?php if (empty($submissions)): ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06);">
            <i class="fas fa-inbox" style="font-size:48px; color:#ddd; display:block; margin-bottom:16px;"></i>
            <p style="color:var(--gray); font-size:16px;">No artworks match your filters.</p>
            <a href="?page=judge_dashboard" class="btn btn-primary" style="margin-top:12px;">View All</a>
        </div>
        <?php else: ?>
        <div class="artwork-grid">
        <?php foreach ($submissions as $s):
            $isImage = strpos($s['fileType'] ?? '', 'image') !== false;
            $fileBasename = basename($s['filePath'] ?? $s['fileName'] ?? '');
            $webPath = $fileBasename ? '../uploads/' . $fileBasename : '';
            $evalStatus = $s['eval_status'] ?? null;
        ?>
        <div class="art-card">
            <!-- Anonymous thumbnail -->
            <div class="art-thumb">
                <?php if ($webPath && $isImage): ?>
                    <img src="<?php echo htmlspecialchars($webPath); ?>" alt="Artwork" loading="lazy">
                <?php elseif ($webPath && !$isImage): ?>
                    <video src="<?php echo htmlspecialchars($webPath); ?>" muted preload="metadata" style="pointer-events:none;"></video>
                <?php else: ?>
                    <i class="fas fa-image"></i>
                <?php endif; ?>
            </div>

            <!-- Info — strictly anonymous -->
            <div class="art-info">
                <div class="art-code"><?php echo htmlspecialchars($s['competition_code']); ?></div>
                <div class="art-title"><?php echo htmlspecialchars($s['artwork_name']); ?></div>
                <div>
                    <span class="status-badge" style="background:#e8f0fe; color:var(--primary); font-size:11px;">
                        <?php echo $s['category'] === 'photography_paint' ? 'Photo/Paint' : 'Short Video'; ?>
                    </span>
                </div>
            </div>

            <div class="art-footer">
                <?php if ($evalStatus === 'submitted'): ?>
                    <span class="eval-badge-done"><i class="fas fa-check-circle"></i> Submitted (<?php echo $s['total_score']; ?> pts)</span>
                    <a href="?page=judge_evaluate&id=<?php echo $s['id']; ?>" class="btn btn-secondary" style="font-size:12px; padding:5px 10px;">
                        <i class="fas fa-eye"></i> View
                    </a>
                <?php elseif ($evalStatus === 'draft'): ?>
                    <span class="eval-badge-draft"><i class="fas fa-edit"></i> Draft</span>
                    <a href="?page=judge_evaluate&id=<?php echo $s['id']; ?>" class="btn btn-warning" style="font-size:12px; padding:5px 10px;">
                        <i class="fas fa-pen"></i> Continue
                    </a>
                <?php else: ?>
                    <span class="eval-badge-pending"><i class="fas fa-clock"></i> Pending</span>
                    <a href="?page=judge_evaluate&id=<?php echo $s['id']; ?>" class="btn btn-primary" style="font-size:12px; padding:5px 10px;">
                        <i class="fas fa-gavel"></i> Evaluate
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
