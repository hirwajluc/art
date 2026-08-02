<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jury Rankings - GREATER Art Competition</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .rank-badge { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:15px; flex-shrink:0; }
        .rank-1 { background:#FFD700; color:#000; }
        .rank-2 { background:#C0C0C0; color:#000; }
        .rank-3 { background:#CD7F32; color:#fff; }
        .rank-n { background:#f0f4ff; color:var(--primary); }
        .rank-none { background:#f5f5f5; color:#aaa; }
        .score-bar-wrap { background:#f0f4ff; border-radius:6px; height:8px; min-width:80px; }
        .score-bar { background:linear-gradient(90deg,var(--primary),var(--accent)); border-radius:6px; height:8px; }
        .detail-panel { background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1); padding:28px; margin-bottom:24px; }
        .judge-block { border:1px solid #e9ecef; border-radius:10px; padding:20px; margin-bottom:16px; }
        .judge-block h4 { font-size:15px; color:var(--dark); margin-bottom:12px; display:flex; justify-content:space-between; }
        .score-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin-bottom:12px; }
        .score-item { background:#f8f9fa; border-radius:8px; padding:12px 14px; }
        .score-item .crit-name { font-size:12px; color:var(--gray); margin-bottom:4px; }
        .score-item .crit-score { font-size:18px; font-weight:700; color:var(--primary); }
        .score-item .crit-max { font-size:12px; color:var(--gray); }
        .avg-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin:16px 0; }
        .avg-item { background:linear-gradient(135deg,#f0f4ff,#e8f0fe); border-radius:8px; padding:14px; }
        .avg-item .a-name { font-size:12px; color:var(--primary); margin-bottom:4px; font-weight:600; }
        .avg-item .a-score { font-size:22px; font-weight:800; color:var(--dark); }
        .avg-item .a-max { font-size:12px; color:var(--gray); }
        .verdict-complete { background:#d4edda; color:#155724; border-radius:8px; padding:8px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; }
        .verdict-pending  { background:#fff3cd; color:#856404; border-radius:8px; padding:8px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; }
        .notes-section { margin-top:12px; }
        .notes-section h5 { font-size:13px; color:var(--gray); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .notes-section p { font-size:14px; color:var(--dark); line-height:1.6; white-space:pre-wrap; }
        .back-btn { margin-bottom:20px; display:inline-flex; align-items:center; gap:8px; }
        .overall-score-badge { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:12px; padding:8px 20px; font-size:22px; font-weight:800; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(urldecode($_GET['success'])); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
        <?php endif; ?>

        <!-- ── Detail View ── -->
        <?php if ($detailId > 0 && $detailSubmission): ?>

        <a href="?page=judging_results" class="btn btn-secondary back-btn"><i class="fas fa-arrow-left"></i> Back to Rankings</a>

        <div class="detail-panel">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
                <div>
                    <div style="font-size:12px; color:var(--gray); text-transform:uppercase; letter-spacing:1px;">Competition Code</div>
                    <div style="font-size:28px; font-weight:800; color:var(--primary); letter-spacing:2px;"><?php echo htmlspecialchars($detailSubmission['competition_code']); ?></div>
                    <div style="margin-top:6px;">
                        <strong><?php echo htmlspecialchars($detailSubmission['artwork_name']); ?></strong>
                        &nbsp;·&nbsp;
                        <span class="status-badge status-pending"><?php echo $detailSubmission['category'] === 'photography_paint' ? 'Photography / Paint' : 'Short Video'; ?></span>
                    </div>
                </div>
                <?php
                $allSubmitted  = count($detailEvaluations) > 0 && array_sum(array_column($detailEvaluations, 'status') === 'submitted' ? [1] : []) > 0;
                $submittedEvals = array_filter($detailEvaluations, fn($e) => $e['status'] === 'submitted');
                $avgTotal = count($submittedEvals) > 0 ? array_sum(array_column(array_values($submittedEvals), 'total_score')) / count($submittedEvals) : null;
                ?>
                <?php if ($avgTotal !== null): ?>
                <div style="text-align:right;">
                    <div style="font-size:12px; color:var(--gray); margin-bottom:6px;">Average Score</div>
                    <div class="overall-score-badge"><?php echo round($avgTotal, 2); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Per-criterion averages -->
            <?php if (!empty($criterionAverages)): ?>
            <h4 style="margin-bottom:12px; color:var(--dark);"><i class="fas fa-chart-bar"></i> Average per Criterion</h4>
            <div class="avg-grid">
                <?php foreach ($criterionAverages as $ca): ?>
                <div class="avg-item">
                    <div class="a-name"><?php echo htmlspecialchars($ca['name']); ?></div>
                    <div class="a-score"><?php echo $ca['avg_score'] !== null ? number_format((float)$ca['avg_score'], 1) : '–'; ?></div>
                    <div class="a-max">/ <?php echo $ca['max_score']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Individual judge evaluations -->
            <h4 style="margin:24px 0 12px; color:var(--dark);"><i class="fas fa-users"></i> Judge Evaluations (<?php echo count($detailEvaluations); ?>)</h4>
            <?php if (empty($detailEvaluations)): ?>
            <p style="color:var(--gray);">No evaluations yet.</p>
            <?php else: ?>
            <?php foreach ($detailEvaluations as $idx => $eval): ?>
            <div class="judge-block">
                <h4>
                    <span>Judge <?php echo $idx + 1; ?>: <?php echo htmlspecialchars($eval['judge_name']); ?></span>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <?php if ($eval['status'] === 'submitted'): ?>
                        <span class="verdict-complete"><i class="fas fa-check-circle"></i> Submitted</span>
                        <strong style="font-size:18px; color:var(--primary);"><?php echo $eval['total_score']; ?> pts</strong>
                        <a href="?page=reopen_evaluation&id=<?php echo $eval['id']; ?>&submission=<?php echo $detailId; ?>"
                           class="btn btn-secondary" style="font-size:12px; padding:4px 10px;"
                           onclick="return confirm('Reopen this evaluation for editing?')">
                            <i class="fas fa-lock-open"></i> Reopen
                        </a>
                        <?php else: ?>
                        <span class="verdict-pending"><i class="fas fa-edit"></i> Draft</span>
                        <?php endif; ?>
                    </div>
                </h4>

                <?php if (!empty($eval['scores'])): ?>
                <div class="score-grid">
                    <?php foreach ($eval['scores'] as $sc): ?>
                    <div class="score-item">
                        <div class="crit-name"><?php echo htmlspecialchars($sc['name']); ?></div>
                        <div class="crit-score"><?php echo $sc['score']; ?><span class="crit-max"> / <?php echo $sc['max_score']; ?></span></div>
                        <div class="score-bar-wrap" style="margin-top:6px;">
                            <div class="score-bar" style="width:<?php echo round(($sc['score']/$sc['max_score'])*100); ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="notes-section">
                    <?php if ($eval['strengths']): ?>
                    <h5>Strengths</h5><p><?php echo htmlspecialchars($eval['strengths']); ?></p>
                    <?php endif; ?>
                    <?php if ($eval['weaknesses']): ?>
                    <h5 style="margin-top:12px;">Weaknesses</h5><p><?php echo htmlspecialchars($eval['weaknesses']); ?></p>
                    <?php endif; ?>
                    <?php if ($eval['recommendations']): ?>
                    <h5 style="margin-top:12px;">Recommendations</h5><p><?php echo htmlspecialchars($eval['recommendations']); ?></p>
                    <?php endif; ?>
                    <?php if ($eval['overall_comments']): ?>
                    <h5 style="margin-top:12px;">Overall Comments</h5><p><?php echo htmlspecialchars($eval['overall_comments']); ?></p>
                    <?php endif; ?>
                    <?php if ($eval['reopened_at']): ?>
                    <p style="margin-top:10px; font-size:12px; color:var(--warning);">
                        <i class="fas fa-lock-open"></i> Reopened by admin on <?php echo date('M j, Y H:i', strtotime($eval['reopened_at'])); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- ── Rankings Table ── -->

        <!-- Stats row -->
        <div class="stats-grid" style="margin-bottom:24px; grid-template-columns:repeat(4,1fr);">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-images"></i></div>
                <div class="stat-number"><?php echo $resultStats['total_submissions']; ?></div>
                <div class="stat-label">Total Artworks</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-number"><?php echo $resultStats['complete_artworks']; ?></div>
                <div class="stat-label">Fully Judged</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?php echo $resultStats['pending_artworks']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gavel"></i></div>
                <div class="stat-number"><?php echo $resultStats['active_judges']; ?></div>
                <div class="stat-label">Active Judges</div>
            </div>
        </div>

        <!-- Filters + export -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title"><i class="fas fa-trophy"></i> Rankings</div>
                <div class="search-box">
                    <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap;" action="">
                        <input type="hidden" name="page" value="judging_results">
                        <input class="search-input" type="text" name="search" placeholder="Search code or title…" value="<?php echo htmlspecialchars($searchFilter); ?>">
                        <select name="category" class="form-select" style="padding:8px 12px; border:none; border-radius:5px;">
                            <option value="">All Categories</option>
                            <option value="photography_paint" <?php echo $catFilter === 'photography_paint' ? 'selected' : ''; ?>>Photography/Paint</option>
                            <option value="short_video"       <?php echo $catFilter === 'short_video'       ? 'selected' : ''; ?>>Short Video</option>
                        </select>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                        <?php if ($catFilter || $searchFilter): ?>
                        <a href="?page=judging_results" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                        <a href="?page=judging_results&export=csv<?php echo $catFilter ? '&category='.urlencode($catFilter) : ''; ?><?php echo $searchFilter ? '&search='.urlencode($searchFilter) : ''; ?>" class="btn btn-success">
                            <i class="fas fa-download"></i> CSV
                        </a>
                    </form>
                </div>
            </div>

            <?php if (empty($results)): ?>
            <div style="text-align:center; padding:60px; color:var(--gray);">
                <i class="fas fa-inbox" style="font-size:48px; color:#ddd; display:block; margin-bottom:16px;"></i>
                No artworks found.
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Code</th>
                        <th>Artwork</th>
                        <th>Category</th>
                        <th>Judges</th>
                        <th>Avg Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td>
                        <?php if ($r['rank'] !== null): ?>
                        <div class="rank-badge rank-<?php echo $r['rank'] <= 3 ? $r['rank'] : 'n'; ?>">
                            <?php echo $r['rank']; ?>
                        </div>
                        <?php if ($r['tied']): ?><div style="font-size:10px; color:var(--warning); text-align:center; margin-top:2px;">TIED</div><?php endif; ?>
                        <?php else: ?>
                        <div class="rank-badge rank-none">–</div>
                        <?php endif; ?>
                    </td>
                    <td style="font-family:monospace; font-weight:700; color:var(--primary); font-size:15px; letter-spacing:1px;">
                        <?php echo htmlspecialchars($r['competition_code']); ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($r['artwork_name']); ?></strong></td>
                    <td>
                        <span class="status-badge" style="background:#e8f0fe; color:var(--primary);">
                            <?php echo $r['category'] === 'photography_paint' ? 'Photo/Paint' : 'Short Video'; ?>
                        </span>
                    </td>
                    <td>
                        <strong><?php echo (int)$r['judges_submitted']; ?></strong>
                        / <?php echo (int)$r['active_judges']; ?>
                        <?php if ($r['judges_submitted'] > 0 && $r['judges_submitted'] < $r['active_judges']): ?>
                        <div style="font-size:11px; color:var(--warning);"><i class="fas fa-clock"></i> In progress</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['avg_score'] !== null): ?>
                        <strong style="font-size:18px; color:var(--primary);"><?php echo number_format($r['avg_score'], 2); ?></strong>
                        <?php if ($r['max_score'] !== null && $r['min_score'] !== null): ?>
                        <div style="font-size:11px; color:var(--gray);">
                            Hi: <?php echo $r['max_score']; ?> / Lo: <?php echo $r['min_score']; ?>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span style="color:var(--gray);">–</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['is_complete']): ?>
                        <span class="verdict-complete" style="font-size:12px;"><i class="fas fa-check-circle"></i> Complete</span>
                        <?php else: ?>
                        <span class="verdict-pending" style="font-size:12px;"><i class="fas fa-clock"></i> Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=judging_results&detail=<?php echo $r['id']; ?><?php echo $catFilter ? '&category='.urlencode($catFilter) : ''; ?><?php echo $searchFilter ? '&search='.urlencode($searchFilter) : ''; ?>" class="btn btn-primary" style="font-size:12px; padding:6px 12px;">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
