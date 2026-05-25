<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Admin Dashboard</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_registrations']; ?></div>
                    <div class="stat-label">Total Registrations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_submissions']; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pending_submissions']; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['approved_submissions']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-container">
                <div class="chart-card">
                    <h3 class="chart-title">Registrations by Category</h3>
                    <canvas id="registrationChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Participants by Country</h3>
                    <canvas id="countryChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Recent Activity Timeline</h3>
                    <a href="?page=submissions" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        View All Submissions
                    </a>
                </div>
                <div class="timeline-container">
                    <?php if (!empty($stats['recent_registrations'])): ?>
                        <?php foreach (array_reverse($stats['recent_registrations']) as $day): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M j', strtotime($day['date'])); ?></div>
                                <div class="timeline-content">
                                    <strong><?php echo $day['count']; ?></strong> new registrations
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const dashboardData = {
            registrations: <?php echo json_encode($stats['registrations_by_category']); ?>,
            submissions: <?php echo json_encode($stats['submissions_by_category']); ?>,
            countries: <?php echo json_encode($countryStats); ?>,
            recentRegistrations: <?php echo json_encode($stats['recent_registrations']); ?>
        };

        // Initialize charts
        function initCharts() {
            // Registration by category chart
            const regLabels = [];
            const regData = [];
            
            dashboardData.registrations.forEach(item => {
                regLabels.push(item.category === 'photography_paint' ? 'Photography/Painting' : 'Short Video');
                regData.push(parseInt(item.count));
            });

            const regCtx = document.getElementById('registrationChart').getContext('2d');
            new Chart(regCtx, {
                type: 'doughnut',
                data: {
                    labels: regLabels,
                    datasets: [{
                        data: regData,
                        backgroundColor: ['#1E90FF', '#FFD700'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Country distribution chart
            const countryLabels = [];
            const countryData = [];
            
            dashboardData.countries.slice(0, 8).forEach(item => {
                countryLabels.push(item.nationality);
                countryData.push(parseInt(item.count));
            });

            const countryCtx = document.getElementById('countryChart').getContext('2d');
            new Chart(countryCtx, {
                type: 'bar',
                data: {
                    labels: countryLabels,
                    datasets: [{
                        label: 'Registrations',
                        data: countryData,
                        backgroundColor: '#00BFFF',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>

    <style>
        .timeline-container {
            padding: 20px;
            max-height: 300px;
            overflow-y: auto;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-date {
            min-width: 80px;
            font-weight: 600;
            color: var(--primary);
            font-size: 14px;
        }

        .timeline-content {
            flex: 1;
            margin-left: 15px;
            color: var(--gray);
        }
    </style>
</body>
</html>