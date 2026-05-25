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
            
            <div class="export-container">
                <div class="export-grid">
                    <!-- Registrations Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-users"></i> Export Registrations</h3>
                        </div>
                        <div class="card-content">
                            <p>Export all registration data including participant details, categories, and registration dates.</p>
                            <div class="export-options">
                                <label>
                                    <input type="radio" name="reg_format" value="csv" checked> CSV Format
                                </label>
                                <label>
                                    <input type="radio" name="reg_format" value="excel"> Excel Format
                                </label>
                                <label>
                                    <input type="radio" name="reg_format" value="pdf"> PDF Report
                                </label>
                            </div>
                            <button class="btn btn-primary" onclick="exportData('registrations')">
                                <i class="fas fa-download"></i>
                                Export Registrations
                            </button>
                        </div>
                    </div>

                    <!-- Submissions Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-images"></i> Export Submissions</h3>
                        </div>
                        <div class="card-content">
                            <p>Export submission data including artwork details, scores, and review status.</p>
                            <div class="export-options">
                                <label>
                                    <input type="radio" name="sub_format" value="csv" checked> CSV Format
                                </label>
                                <label>
                                    <input type="radio" name="sub_format" value="excel"> Excel Format
                                </label>
                                <label>
                                    <input type="radio" name="sub_format" value="pdf"> PDF Report
                                </label>
                            </div>
                            <button class="btn btn-success" onclick="exportData('submissions')">
                                <i class="fas fa-download"></i>
                                Export Submissions
                            </button>
                        </div>
                    </div>

                    <!-- Analytics Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> Export Analytics</h3>
                        </div>
                        <div class="card-content">
                            <p>Export dashboard statistics and analytics data for reporting purposes.</p>
                            <div class="export-options">
                                <label>
                                    <input type="radio" name="analytics_format" value="pdf" checked> PDF Report
                                </label>
                                <label>
                                    <input type="radio" name="analytics_format" value="csv"> CSV Data
                                </label>
                            </div>
                            <button class="btn btn-warning" onclick="exportData('analytics')">
                                <i class="fas fa-download"></i>
                                Export Analytics
                            </button>
                        </div>
                    </div>

                    <!-- Complete Export -->
                    <div class="export-card">
                        <div class="card-header">
                            <h3><i class="fas fa-database"></i> Complete Export</h3>
                        </div>
                        <div class="card-content">
                            <p>Export all competition data in a comprehensive package including files and database.</p>
                            <div class="export-options">
                                <label>
                                    <input type="radio" name="complete_format" value="zip" checked> ZIP Archive
                                </label>
                            </div>
                            <button class="btn btn-danger" onclick="exportData('complete')">
                                <i class="fas fa-download"></i>
                                Export All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function exportData(type) {
            // Get selected format
            let format = 'csv';
            const formatInputs = document.querySelectorAll(`input[name="${type}_format"]:checked`);
            if (formatInputs.length > 0) {
                format = formatInputs[0].value;
            }

            // Show loading
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
            button.disabled = true;

            // Simulate export process
            setTimeout(() => {
                alert(`Exporting ${type} in ${format} format...\n\nNote: This is a demo. In production, this would generate and download the actual file.`);
                
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
    </script>

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