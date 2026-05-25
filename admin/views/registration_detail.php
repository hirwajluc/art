<?php
// views/registration_detail.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Registration Details</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Registration Details</h1>
                <div class="user-info">
                    <a href="?page=registrations" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Registrations
                    </a>
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>
            
            <!-- Participant Header Card -->
            <div class="participant-header">
                <div class="participant-avatar">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($registration['fullName'], 0, 2)); ?>
                    </div>
                </div>
                <div class="participant-info">
                    <h2><?php echo htmlspecialchars($registration['fullName']); ?></h2>
                    <p class="participant-meta">
                        <span class="user-code-large"><?php echo htmlspecialchars($registration['userCode']); ?></span>
                        <span class="category-badge-large category-<?php echo $registration['category']; ?>">
                            <i class="fas fa-<?php echo $registration['category'] === 'photography_paint' ? 'camera' : 'video'; ?>"></i>
                            <?php echo getCategoryName($registration['category']); ?>
                        </span>
                    </p>
                    <div class="participant-stats">
                        <div class="stat-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Registered <?php echo date('M j, Y', strtotime($registration['registrationDate'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-flag"></i>
                            <span><?php echo htmlspecialchars($registration['nationality']); ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-birthday-cake"></i>
                            <span><?php echo date('M j, Y', strtotime($registration['birthDate'])); ?> (<?php echo date('Y') - date('Y', strtotime($registration['birthDate'])); ?> years old)</span>
                        </div>
                    </div>
                </div>
                <div class="participant-actions">
                    <button class="action-btn email-btn" onclick="sendEmail('<?php echo htmlspecialchars($registration['email']); ?>')">
                        <i class="fas fa-envelope"></i>
                        <span>Send Email</span>
                    </button>
                    <button class="action-btn export-btn" onclick="exportRegistration()">
                        <i class="fas fa-download"></i>
                        <span>Export Data</span>
                    </button>
                    <button class="action-btn phone-btn" onclick="callParticipant('<?php echo htmlspecialchars($registration['phone']); ?>')">
                        <i class="fas fa-phone"></i>
                        <span>Call</span>
                    </button>
                </div>
            </div>
            
            <div class="detail-container">
                <div class="detail-grid">
                    <!-- Personal Information Card -->
                    <div class="detail-card personal-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Personal Information</h3>
                                    <p>Basic participant details</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-signature"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Full Name</label>
                                        <span><?php echo htmlspecialchars($registration['fullName']); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Date of Birth</label>
                                        <span><?php echo formatDate($registration['birthDate'], 'M j, Y'); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Nationality</label>
                                        <span class="nationality-badge"><?php echo htmlspecialchars($registration['nationality']); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>ID Number</label>
                                        <span class="id-number"><?php echo htmlspecialchars($registration['idNumber']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="detail-card contact-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-address-book"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Contact Information</h3>
                                    <p>Communication details</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Email Address</label>
                                        <span>
                                            <a href="mailto:<?php echo htmlspecialchars($registration['email']); ?>" class="email-link">
                                                <?php echo htmlspecialchars($registration['email']); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Phone Number</label>
                                        <span class="phone-number"><?php echo htmlspecialchars($registration['phone']); ?></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <label>Registration Location</label>
                                        <span class="ip-address"><?php echo htmlspecialchars($registration['ipAddress']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Competition Information Card -->
                    <div class="detail-card competition-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Competition Details</h3>
                                    <p>Participation information</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="competition-overview">
                                <div class="competition-visual">
                                    <div class="category-icon">
                                        <i class="fas fa-<?php echo $registration['category'] === 'photography_paint' ? 'camera' : 'video'; ?>"></i>
                                    </div>
                                    <div class="category-info">
                                        <h4><?php echo getCategoryName($registration['category']); ?></h4>
                                        <p><?php echo $registration['category'] === 'photography_paint' ? 'Photography & Painting Category' : 'Short Video Category'; ?></p>
                                    </div>
                                </div>
                                <div class="timeline-info">
                                    <div class="timeline-item">
                                        <div class="timeline-icon">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <strong>Registration</strong>
                                            <span><?php echo formatDate($registration['registrationDate'], 'M j, Y \a\t g:i A'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="detail-card stats-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Participant Statistics</h3>
                                    <p>Engagement metrics</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo ceil((time() - strtotime($registration['registrationDate'])) / (60 * 60 * 24)); ?></span>
                                        <span class="stat-label">Days Since Registration</span>
                                    </div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-number">0</span>
                                        <span class="stat-label">Submissions</span>
                                    </div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-number">-</span>
                                        <span class="stat-label">Average Score</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Status Card -->
                    <div class="detail-card submission-status-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Submission Status</h3>
                                    <p>Current submission progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="submission-progress">
                                <div class="progress-item">
                                    <div class="progress-icon completed">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="progress-content">
                                        <h5>Registration Complete</h5>
                                        <p>Participant successfully registered</p>
                                        <span class="progress-time"><?php echo formatDate($registration['registrationDate'], 'M j, Y g:i A'); ?></span>
                                    </div>
                                </div>
                                <div class="progress-line completed"></div>
                                <div class="progress-item">
                                    <div class="progress-icon pending">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="progress-content">
                                        <h5>Artwork Submission</h5>
                                        <p>Waiting for artwork submission</p>
                                        <span class="progress-time">Deadline: July 15, 2025</span>
                                    </div>
                                </div>
                                <div class="progress-line pending"></div>
                                <div class="progress-item">
                                    <div class="progress-icon pending">
                                        <i class="fas fa-gavel"></i>
                                    </div>
                                    <div class="progress-content">
                                        <h5>Jury Review</h5>
                                        <p>Awaiting jury evaluation</p>
                                        <span class="progress-time">TBD</span>
                                    </div>
                                </div>
                                <div class="progress-line pending"></div>
                                <div class="progress-item">
                                    <div class="progress-icon pending">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="progress-content">
                                        <h5>Results Announcement</h5>
                                        <p>Winners will be announced</p>
                                        <span class="progress-time">September 1, 2025</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="detail-card actions-card">
                        <div class="card-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <div class="header-text">
                                    <h3>Quick Actions</h3>
                                    <p>Available operations</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="actions-grid">
                                <button class="action-card" onclick="sendEmail('<?php echo htmlspecialchars($registration['email']); ?>')">
                                    <div class="action-icon email">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>Send Email</h5>
                                        <p>Contact participant via email</p>
                                    </div>
                                </button>
                                
                                <button class="action-card" onclick="exportRegistration()">
                                    <div class="action-icon export">
                                        <i class="fas fa-download"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>Export Data</h5>
                                        <p>Download registration info</p>
                                    </div>
                                </button>
                                
                                <button class="action-card" onclick="viewSubmissions()">
                                    <div class="action-icon submissions">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>View Submissions</h5>
                                        <p>Check participant's artwork</p>
                                    </div>
                                </button>
                                
                                <button class="action-card" onclick="addNote()">
                                    <div class="action-icon note">
                                        <i class="fas fa-sticky-note"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>Add Note</h5>
                                        <p>Record admin comments</p>
                                    </div>
                                </button>

                                <button class="action-card" onclick="sendReminder()">
                                    <div class="action-icon reminder">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>Send Reminder</h5>
                                        <p>Remind about submission</p>
                                    </div>
                                </button>
                                
                                <button class="action-card" onclick="flagParticipant()">
                                    <div class="action-icon flag">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                    <div class="action-content">
                                        <h5>Flag Participant</h5>
                                        <p>Mark for special attention</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="additional-info-section">
                    <div class="section-header">
                        <h2><i class="fas fa-info-circle"></i> Additional Information</h2>
                        <p>Comprehensive participant overview and system data</p>
                    </div>
                    
                    <div class="additional-grid">
                        <!-- Technical Information -->
                        <div class="info-panel technical-panel">
                            <div class="panel-header">
                                <h4><i class="fas fa-server"></i> Technical Details</h4>
                            </div>
                            <div class="panel-content">
                                <div class="tech-item">
                                    <span class="tech-label">Registration IP:</span>
                                    <span class="tech-value"><?php echo htmlspecialchars($registration['ipAddress']); ?></span>
                                </div>
                                <div class="tech-item">
                                    <span class="tech-label">User Agent:</span>
                                    <span class="tech-value">Browser/Device Info</span>
                                </div>
                                <div class="tech-item">
                                    <span class="tech-label">Registration Source:</span>
                                    <span class="tech-value">Direct Website</span>
                                </div>
                                <div class="tech-item">
                                    <span class="tech-label">Account Status:</span>
                                    <span class="tech-value status-active">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Communication History -->
                        <div class="info-panel communication-panel">
                            <div class="panel-header">
                                <h4><i class="fas fa-history"></i> Communication History</h4>
                            </div>
                            <div class="panel-content">
                                <div class="comm-item">
                                    <div class="comm-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="comm-content">
                                        <strong>Registration Confirmation</strong>
                                        <span><?php echo formatDate($registration['registrationDate'], 'M j, Y g:i A'); ?></span>
                                    </div>
                                </div>
                                <div class="comm-item">
                                    <div class="comm-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="comm-content">
                                        <strong>Welcome Email Sent</strong>
                                        <span><?php echo formatDate($registration['registrationDate'], 'M j, Y g:i A'); ?></span>
                                    </div>
                                </div>
                                <div class="comm-placeholder">
                                    <p>No additional communications yet</p>
                                </div>
                            </div>
                        </div>

                        <!-- Competition Rules & Guidelines -->
                        <div class="info-panel rules-panel">
                            <div class="panel-header">
                                <h4><i class="fas fa-book"></i> Competition Guidelines</h4>
                            </div>
                            <div class="panel-content">
                                <div class="rule-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Registration completed and confirmed</span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Submission deadline: July 15, 2025</span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-file-image"></i>
                                    <span>Accepted formats: JPEG, PNG, PDF, MP4, MOV</span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-trophy"></i>
                                    <span>Winners announced: September 1, 2025</span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-award"></i>
                                    <span>Awards ceremony: September 25, 2025</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function exportRegistration() {
            // Create CSV data
            const data = [
                ['Field', 'Value'],
                ['User Code', '<?php echo addslashes($registration['userCode']); ?>'],
                ['Full Name', '<?php echo addslashes($registration['fullName']); ?>'],
                ['Birth Date', '<?php echo addslashes($registration['birthDate']); ?>'],
                ['Nationality', '<?php echo addslashes($registration['nationality']); ?>'],
                ['ID Number', '<?php echo addslashes($registration['idNumber']); ?>'],
                ['Email', '<?php echo addslashes($registration['email']); ?>'],
                ['Phone', '<?php echo addslashes($registration['phone']); ?>'],
                ['Category', '<?php echo addslashes(getCategoryName($registration['category'])); ?>'],
                ['Registration Date', '<?php echo addslashes($registration['registrationDate']); ?>'],
                ['IP Address', '<?php echo addslashes($registration['ipAddress']); ?>']
            ];

            // Convert to CSV
            const csv = data.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
            
            // Download
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `registration_<?php echo $registration['userCode']; ?>.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function sendEmail(email) {
            window.open(`mailto:${email}?subject=GREATER Art Competition - Follow up&body=Dear participant,%0D%0A%0D%0AThank you for your registration in the GREATER Art Competition.%0D%0A%0D%0ABest regards,%0D%0AGREATER Team`, '_blank');
        }

        function callParticipant(phone) {
            if (navigator.userAgent.match(/(iPhone|iPod|Android|BlackBerry|IEMobile)/)) {
                window.open(`tel:${phone}`, '_self');
            } else {
                navigator.clipboard.writeText(phone).then(() => {
                    alert(`Phone number ${phone} copied to clipboard!`);
                });
            }
        }

        function viewSubmissions() {
            window.location.href = `?page=submissions&search=<?php echo urlencode($registration['userCode']); ?>`;
        }

        function addNote() {
            const note = prompt('Add a note about this participant:');
            if (note) {
                alert('Note functionality would be implemented here.\nNote: ' + note);
            }
        }

        function sendReminder() {
            if (confirm('Send submission reminder to this participant?')) {
                alert('Reminder email would be sent here.');
            }
        }

        function flagParticipant() {
            if (confirm('Flag this participant for special attention?')) {
                alert('Participant would be flagged in the system.');
            }
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.detail-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>

    <style>
        /* Participant Header */
        .participant-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 10px 30px rgba(30, 144, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .participant-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .participant-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            color: var(--secondary);
            backdrop-filter: blur(10px);
        }

        .participant-info {
            flex: 1;
        }

        .participant-info h2 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .participant-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .user-code-large {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 25px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .category-badge-large {
            background: var(--secondary);
            color: var(--dark);
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .participant-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            opacity: 0.9;
        }

        .stat-item i {
            font-size: 16px;
        }

        .participant-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Enhanced Detail Cards */
        .detail-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .detail-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(30, 144, 255, 0.1);
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }

        .personal-card:hover {
            border-color: #4CAF50;
        }

        .contact-card:hover {
            border-color: #FF9800;
        }

        .competition-card:hover {
            border-color: #9C27B0;
        }

        .actions-card:hover {
            border-color: #F44336;
        }

        .stats-card:hover {
            border-color: #2196F3;
        }

        .submission-status-card:hover {
            border-color: #607D8B;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .personal-card .header-icon {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .contact-card .header-icon {
            background: linear-gradient(135deg, #FF9800, #f57c00);
        }

        .competition-card .header-icon {
            background: linear-gradient(135deg, #9C27B0, #7b1fa2);
        }

        .actions-card .header-icon {
            background: linear-gradient(135deg, #F44336, #d32f2f);
        }

        .stats-card .header-icon {
            background: linear-gradient(135deg, #2196F3, #1976d2);
        }

        .submission-status-card .header-icon {
            background: linear-gradient(135deg, #607D8B, #455a64);
        }

        .header-text h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .header-text p {
            font-size: 14px;
            color: var(--gray);
            margin: 0;
        }

        .card-content {
            padding: 30px;
        }

        /* Enhanced Info List */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #e3f2fd;
            border-left-color: var(--secondary);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-content label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-content span {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }

        /* Special styling for different content types */
        .email-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .email-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .nationality-badge {
            background: linear-gradient(135deg, var(--success), #45a049);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .id-number {
            font-family: 'Courier New', monospace;
            background: #e8f5e8;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: bold;
            color: var(--dark);
        }

        .phone-number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary);
        }

        .ip-address {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: var(--gray);
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* Competition Overview */
        .competition-overview {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .competition-visual {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            border: 2px dashed var(--primary);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .category-info h4 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .category-info p {
            color: var(--gray);
            font-size: 14px;
            margin: 0;
        }

        .timeline-info {
            background: white;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .timeline-content {
            display: flex;
            flex-direction: column;
        }

        .timeline-content strong {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .timeline-content span {
            font-size: 14px;
            color: var(--gray);
        }

        /* Statistics Card */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
        }

        .stat-box {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-box .stat-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Submission Progress */
        .submission-progress {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            position: relative;
        }

        .progress-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            position: relative;
            z-index: 2;
        }

        .progress-icon.completed {
            background: var(--success);
        }

        .progress-icon.pending {
            background: var(--gray);
        }

        .progress-content {
            flex: 1;
        }

        .progress-content h5 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .progress-content p {
            color: var(--gray);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .progress-time {
            font-size: 12px;
            color: var(--gray);
            font-style: italic;
        }

        .progress-line {
            width: 4px;
            height: 30px;
            margin-left: 23px;
            margin-top: -10px;
            margin-bottom: -10px;
        }

        .progress-line.completed {
            background: var(--success);
        }

        .progress-line.pending {
            background: #ddd;
        }

        /* Actions Grid */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }

        .action-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .action-card:nth-child(1):hover {
            border-color: #4CAF50;
        }

        .action-card:nth-child(2):hover {
            border-color: #2196F3;
        }

        .action-card:nth-child(3):hover {
            border-color: #FF9800;
        }

        .action-card:nth-child(4):hover {
            border-color: #9C27B0;
        }

        .action-card:nth-child(5):hover {
            border-color: #FF5722;
        }

        .action-card:nth-child(6):hover {
            border-color: #607D8B;
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .action-icon.email {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .action-icon.export {
            background: linear-gradient(135deg, #2196F3, #1976d2);
        }

        .action-icon.submissions {
            background: linear-gradient(135deg, #FF9800, #f57c00);
        }

        .action-icon.note {
            background: linear-gradient(135deg, #9C27B0, #7b1fa2);
        }

        .action-icon.reminder {
            background: linear-gradient(135deg, #FF5722, #d84315);
        }

        .action-icon.flag {
            background: linear-gradient(135deg, #607D8B, #455a64);
        }

        .action-content h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .action-content p {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
            line-height: 1.4;
        }

        /* Additional Information Section */
        .additional-info-section {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #e9ecef;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .section-header p {
            color: var(--gray);
            font-size: 16px;
        }

        .additional-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .info-panel {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .panel-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px 25px;
            border-bottom: 1px solid #dee2e6;
        }

        .panel-header h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-content {
            padding: 25px;
        }

        /* Technical Panel */
        .tech-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .tech-item:last-child {
            border-bottom: none;
        }

        .tech-label {
            font-weight: 500;
            color: var(--gray);
            font-size: 14px;
        }

        .tech-value {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .status-active {
            color: var(--success) !important;
            background: rgba(76, 175, 80, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        /* Communication Panel */
        .comm-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .comm-item:last-child {
            border-bottom: none;
        }

        .comm-icon {
            width: 35px;
            height: 35px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .comm-content {
            display: flex;
            flex-direction: column;
        }

        .comm-content strong {
            color: var(--dark);
            font-size: 14px;
            margin-bottom: 2px;
        }

        .comm-content span {
            color: var(--gray);
            font-size: 12px;
        }

        .comm-placeholder {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-style: italic;
        }

        /* Rules Panel */
        .rule-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .rule-item:last-child {
            border-bottom: none;
        }

        .rule-item i {
            color: var(--success);
            font-size: 16px;
        }

        .rule-item span {
            color: var(--dark);
            font-size: 14px;
        }

        /* Enhanced Responsive Design */
        @media (max-width: 1400px) {
            .additional-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1200px) {
            .participant-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .participant-actions {
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }

            .detail-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .participant-header {
                padding: 25px;
            }

            .participant-info h2 {
                font-size: 24px;
            }

            .participant-meta {
                flex-direction: column;
                gap: 10px;
            }

            .participant-stats {
                flex-direction: column;
                gap: 15px;
            }

            .competition-visual {
                flex-direction: column;
                text-align: center;
            }

            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .card-content {
                padding: 20px;
            }

            .additional-grid {
                grid-template-columns: 1fr;
            }

            .tech-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</body>
</html>