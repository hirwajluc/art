<?php
// views/export.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Export Data</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error" style="margin-bottom:20px;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>

            <div class="export-container">
                <div class="export-grid">
                    <!-- Registrations Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-users"></i> Export Registrations</h3>
                        </div>
                        <div class="card-content">
                            <p>Export all registration data including participant details, categories, and registration dates.</p>
                            <div class="export-options" style="color:var(--gray); font-size:13px;">
                                <i class="fas fa-file-csv"></i> CSV format
                            </div>
                            <a href="?page=export&type=registrations" class="btn btn-primary" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                                <i class="fas fa-download"></i>
                                Download Registrations CSV
                            </a>
                        </div>
                    </div>

                    <!-- Submissions Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-images"></i> Export Submissions</h3>
                        </div>
                        <div class="card-content">
                            <p>Export submission data including artwork details, scores, and review status.</p>
                            <div class="export-options" style="color:var(--gray); font-size:13px;">
                                <i class="fas fa-file-csv"></i> CSV format
                            </div>
                            <a href="?page=export&type=submissions" class="btn btn-success" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                                <i class="fas fa-download"></i>
                                Download Submissions CSV
                            </a>
                        </div>
                    </div>

                    <!-- Jury Results Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-trophy"></i> Export Jury Results</h3>
                        </div>
                        <div class="card-content">
                            <p>Download jury evaluation results including rankings, average scores, and judge counts.</p>
                            <div class="export-options" style="color:var(--gray); font-size:13px;">
                                <i class="fas fa-file-csv"></i> CSV format
                            </div>
                            <a href="?page=judging_results&export=csv" class="btn btn-warning" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none; margin-top:16px;">
                                <i class="fas fa-download"></i>
                                Download Jury Results CSV
                            </a>
                        </div>
                    </div>

                    <!-- Submissions ZIP Export -->
                    <div class="export-card">
                        <div class="card-header" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">
                            <h3><i class="fas fa-file-archive"></i> Export Artwork Files (ZIP)</h3>
                        </div>
                        <div class="card-content">
                            <p>Download all original artwork files packaged into a ZIP archive. Files are organised into folders by category.</p>
                            <div style="background:#fef9c3; border-left:4px solid #f59e0b; border-radius:4px; padding:10px 14px; margin-bottom:18px; font-size:13px; color:#92400e;">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Large files:</strong> Videos can be 300MB+ each. This may take several minutes to generate.
                            </div>
                            <form method="GET" action="">
                                <input type="hidden" name="page" value="export">
                                <input type="hidden" name="type" value="submissions_zip">
                                <div style="margin-bottom:14px;">
                                    <label style="display:block; font-size:13px; font-weight:600; color:var(--dark); margin-bottom:6px;">Filter by Category (optional)</label>
                                    <select name="category" style="width:100%; padding:9px 12px; border:2px solid #e2e8f0; border-radius:8px; font-size:14px;">
                                        <option value="">All Categories</option>
                                        <option value="photography_paint">Photography / Paint only</option>
                                        <option value="short_video">Short Video only</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#7c3aed,#4f46e5); width:100%; justify-content:center; display:flex; align-items:center; gap:8px; border:none; padding:12px;">
                                    <i class="fas fa-file-archive"></i> Generate &amp; Download ZIP
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="export-card">
                        <div class="card-header" style="background: linear-gradient(135deg,#6c757d,#495057);">
                            <h3><i class="fas fa-chart-bar"></i> Quick Statistics</h3>
                        </div>
                        <div class="card-content">
                            <p>Current competition snapshot:</p>
                            <ul style="margin:0 0 16px; padding-left:18px; color:var(--dark); line-height:2;">
                                <li><strong><?php echo $totalRegs ?? 0; ?></strong> registrations</li>
                                <li><strong><?php echo $totalSubs ?? 0; ?></strong> submissions</li>
                            </ul>
                            <p style="color:var(--gray); font-size:13px;">Use the Registrations and Submissions exports above to download the full data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <style>
        .export-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .export-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 25px;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-content {
            padding: 25px;
        }

        .card-content p {
            color: var(--gray);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .export-options {
            margin-bottom: 20px;
        }

        .export-options label {
            display: block;
            margin-bottom: 10px;
            color: var(--dark);
            cursor: pointer;
        }

        .export-options input[type="radio"] {
            margin-right: 8px;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    </style>
</body>
</html>