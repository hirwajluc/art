<?php
// views/winners.php
// Variables provided by index.php: $topByCategory, $totalMaxScore, $currentUser
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winners — GREATER Art Competition</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .winners-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
        .winners-header h1 { font-size:24px; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:10px; }

        .category-section { margin-bottom:40px; }
        .category-title { font-size:17px; font-weight:700; color:var(--primary); margin-bottom:16px; display:flex; align-items:center; gap:8px; padding-bottom:10px; border-bottom:2px solid #e8f0fe; }

        .winners-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 3px 12px rgba(0,0,0,.07); }
        .winners-table th { background:#f8faff; font-size:12px; font-weight:700; color:var(--gray); text-transform:uppercase; letter-spacing:.6px; padding:12px 16px; text-align:left; border-bottom:1px solid #e5e7eb; }
        .winners-table td { padding:14px 16px; border-bottom:1px solid #f3f4f6; vertical-align:middle; font-size:14px; color:var(--dark); }
        .winners-table tr:last-child td { border-bottom:none; }
        .winners-table tr:hover td { background:#fafbff; }

        .rank-badge { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; }
        .rank-1 { background:linear-gradient(135deg,#ffd700,#ffb300); color:#7a4e00; }
        .rank-2 { background:linear-gradient(135deg,#c0c0c0,#9e9e9e); color:#fff; }
        .rank-3 { background:linear-gradient(135deg,#cd7f32,#a0522d); color:#fff; }
        .rank-other { background:#e8f0fe; color:var(--primary); }

        .score-pill { background:#e8f0fe; color:var(--primary); font-weight:700; font-size:13px; padding:4px 12px; border-radius:20px; white-space:nowrap; }
        .score-none { color:#9ca3af; font-size:13px; font-style:italic; }
        .code-badge { font-family:monospace; font-size:12px; font-weight:700; letter-spacing:1px; background:#f3f4f6; color:var(--gray); padding:3px 10px; border-radius:6px; }

        .empty-cat { text-align:center; padding:40px 20px; color:var(--gray); }
        .empty-cat i { font-size:40px; display:block; margin-bottom:12px; opacity:.3; }

        .legend { font-size:12px; color:#9ca3af; margin-top:8px; }
        .legend span { display:inline-flex; align-items:center; gap:4px; margin-right:14px; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <div class="winners-header">
            <h1><i class="fas fa-trophy" style="color:#ffd700;"></i> Competition Winners</h1>
            <div class="legend">
                <span><i class="fas fa-circle" style="color:#ffd700;font-size:8px;"></i> 1st</span>
                <span><i class="fas fa-circle" style="color:#c0c0c0;font-size:8px;"></i> 2nd</span>
                <span><i class="fas fa-circle" style="color:#cd7f32;font-size:8px;"></i> 3rd</span>
                <span style="color:#6b7280;">Top 8 per category · ranked by jury average score (out of <?php echo $totalMaxScore; ?>)</span>
            </div>
        </div>

        <?php foreach ($topByCategory as $catKey => $cat): ?>
        <div class="category-section">
            <div class="category-title">
                <i class="fas fa-<?php echo $catKey === 'photography_paint' ? 'image' : 'film'; ?>"></i>
                <?php echo htmlspecialchars($cat['label']); ?>
                <span style="font-weight:400; font-size:13px; color:var(--gray); margin-left:6px;">(<?php echo count($cat['rows']); ?> approved)</span>
            </div>

            <?php if (empty($cat['rows'])): ?>
            <div class="empty-cat">
                <i class="fas fa-inbox"></i>
                No approved submissions in this category yet.
            </div>
            <?php else: ?>
            <table class="winners-table">
                <thead>
                    <tr>
                        <th style="width:50px;">Rank</th>
                        <th style="width:110px;">Code</th>
                        <th>Artwork</th>
                        <th style="width:120px;">Avg Score</th>
                        <th style="width:100px;">Judges</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cat['rows'] as $i => $row):
                    $rank = $i + 1;
                    $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
                ?>
                <tr>
                    <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span></td>
                    <td><span class="code-badge"><?php echo htmlspecialchars($row['competition_code']); ?></span></td>
                    <td>
                        <a href="?page=submission_detail&id=<?php echo (int)$row['id']; ?>" style="font-weight:600; color:var(--dark); text-decoration:none;">
                            <?php echo htmlspecialchars($row['artwork_name']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($row['avg_score'] !== null): ?>
                            <span class="score-pill"><?php echo $row['avg_score']; ?> / <?php echo $totalMaxScore; ?></span>
                        <?php else: ?>
                            <span class="score-none">Not yet scored</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--gray); font-size:13px;">
                        <?php echo (int)$row['judges_submitted']; ?> submitted
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </main>
</div>
</body>
</html>
