<?php
// views/winners.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Winners</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
            <div class="winners-container">
                <div class="winners-card">
                    <div class="card-header">
                        <h3><i class="fas fa-trophy"></i> Competition Winners</h3>
                        <button class="btn btn-primary" onclick="selectWinners()">
                            <i class="fas fa-star"></i>
                            Select Winners
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="coming-soon">
                            <div class="coming-soon-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h2>Winners Selection</h2>
                            <p>The winners selection feature is coming soon!</p>
                            <p>This page will allow you to:</p>
                            <ul>
                                <li>Select winners for each category</li>
                                <li>Generate winner announcements</li>
                                <li>Send notification emails</li>
                                <li>Manage prize distribution</li>
                            </ul>
                            <div class="temp-actions">
                                <a href="?page=submissions" class="btn btn-primary">
                                    <i class="fas fa-images"></i>
                                    Review Submissions
                                </a>
                                <a href="?page=dashboard" class="btn btn-secondary">
                                    <i class="fas fa-chart-pie"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function selectWinners() {
            alert('Winners selection feature coming soon!');
        }
    </script>

    <style>
        .winners-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .winners-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-content {
            padding: 60px 40px;
        }

        .coming-soon {
            text-align: center;
        }

        .coming-soon-icon {
            font-size: 64px;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .coming-soon h2 {
            color: var(--dark);
            margin-bottom: 15px;
            font-size: 28px;
        }

        .coming-soon p {
            color: var(--gray);
            margin-bottom: 20px;
            font-size: 16px;
        }

        .coming-soon ul {
            text-align: left;
            max-width: 400px;
            margin: 20px auto 40px;
            color: var(--dark);
        }

        .coming-soon li {
            margin-bottom: 8px;
        }

        .temp-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</body>
</html>