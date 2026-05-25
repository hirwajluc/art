<?php
// views/error.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Error</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
            <div class="error-container">
                <div class="error-card">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2>Oops! Something went wrong</h2>
                    <p><?php echo isset($error) ? htmlspecialchars($error) : 'An unexpected error occurred.'; ?></p>
                    <div class="error-actions">
                        <a href="?page=dashboard" class="btn btn-primary">
                            <i class="fas fa-home"></i>
                            Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Go Back
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .error-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
        }

        .error-card {
            background: white;
            padding: 60px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }

        .error-icon {
            font-size: 64px;
            color: var(--warning);
            margin-bottom: 20px;
        }

        .error-card h2 {
            color: var(--dark);
            margin-bottom: 15px;
            font-size: 24px;
        }

        .error-card p {
            color: var(--gray);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</body>
</html>