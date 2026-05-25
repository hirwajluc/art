<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Submission Details</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Submission Details</h1>
                <div class="user-info">
                    <a href="?page=submissions" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Submissions
                    </a>
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="detail-container">
                <div class="detail-grid">
                    <!-- Submission Info Card -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Submission Information</h3>
                            <span class="status-badge status-<?php echo $submission['status']; ?>">
                                <?php echo ucfirst($submission['status']); ?>
                            </span>
                        </div>
                        <div class="card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>User Code</label>
                                    <span class="user-code"><?php echo htmlspecialchars($submission['userCode']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Participant</label>
                                    <span><?php echo htmlspecialchars($submission['userName']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Email</label>
                                    <span><?php echo htmlspecialchars($submission['userEmail']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Category</label>
                                    <span class="category-badge category-<?php echo $submission['category']; ?>">
                                        <?php echo getCategoryName($submission['category']); ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Submission Date</label>
                                    <span><?php echo formatDate($submission['submissionDate']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>IP Address</label>
                                    <span><?php echo htmlspecialchars($submission['ipAddress']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Artwork Info Card -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-palette"></i> Artwork Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="artwork-title">
                                <h4><?php echo htmlspecialchars($submission['artworkName']); ?></h4>
                            </div>
                            <div class="artwork-description">
                                <label>Description</label>
                                <p><?php echo nl2br(htmlspecialchars($submission['description'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- File Information Card -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-file"></i> File Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="file-grid">
                                <div class="info-item">
                                    <label>Original Filename</label>
                                    <span><?php echo htmlspecialchars($submission['originalFileName']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>File Size</label>
                                    <span><?php echo formatFileSize($submission['fileSize']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>File Type</label>
                                    <span><?php echo htmlspecialchars($submission['fileType']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>File Actions</label>
                                    <div class="file-actions">
                                        <?php 
                                        // The file path in database is like: uploads/filename.jpg
                                        // We need ../uploads/ to access from admin folder
                                        // But for file_exists, we need the actual path from admin folder perspective
                                        $actualFilePath = '../' . $submission['filePath']; // This is for file_exists check from admin folder
                                        $webFilePath = '../' . $submission['filePath'];    // This is for web access
                                        
                                        if (file_exists($actualFilePath)): 
                                            $fileExtension = strtolower(pathinfo($submission['originalFileName'], PATHINFO_EXTENSION));
                                            $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isVideo = in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm']);
                                        ?>
                                            <?php if ($isImage || $isVideo): ?>
                                                <button onclick="viewArtwork('<?php echo $webFilePath; ?>', '<?php echo $isImage ? 'image' : 'video'; ?>', '<?php echo htmlspecialchars($submission['artworkName']); ?>')" 
                                                        class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo $isImage ? 'View Image' : 'Play Video'; ?>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="<?php echo $webFilePath; ?>" 
                                               class="btn btn-success btn-sm" 
                                               target="_blank">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                            <a href="<?php echo $webFilePath; ?>" 
                                               class="btn btn-warning btn-sm" 
                                               target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                                Open in New Tab
                                            </a>
                                        <?php else: ?>
                                            <span class="text-danger">File not found at: <?php echo htmlspecialchars($actualFilePath); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Information Card -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-gavel"></i> Review Information</h3>
                            <?php if ($submission['status'] === 'pending'): ?>
                                <button onclick="openReviewModal(<?php echo $submission['id']; ?>)" 
                                        class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                    Review Now
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <div class="review-grid">
                                <div class="info-item">
                                    <label>Score</label>
                                    <?php if ($submission['score']): ?>
                                        <span class="score-display"><?php echo number_format($submission['score'], 1); ?>/100</span>
                                    <?php else: ?>
                                        <span class="no-score">Not scored yet</span>
                                    <?php endif; ?>
                                </div>
                                <div class="info-item">
                                    <label>Reviewed At</label>
                                    <span><?php echo $submission['reviewed_at'] ? formatDate($submission['reviewed_at']) : 'Not reviewed yet'; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Reviewed By</label>
                                    <span><?php echo $submission['reviewed_by'] ? 'Admin User' : 'Not reviewed yet'; ?></span>
                                </div>
                                <?php if ($submission['jury_feedback']): ?>
                                    <div class="info-item full-width">
                                        <label>Jury Feedback</label>
                                        <div class="feedback-content">
                                            <?php echo nl2br(htmlspecialchars($submission['jury_feedback'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Artwork Viewer Modal -->
    <div class="modal" id="artworkModal">
        <div class="modal-content artwork-modal">
            <div class="modal-header">
                <h3 class="modal-title" id="artworkTitle">Artwork Viewer</h3>
                <button class="close-btn" onclick="closeArtworkModal()">&times;</button>
            </div>
            <div class="modal-body" id="artworkContainer">
                <!-- Artwork content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeArtworkModal()">Close</button>
                <button class="btn btn-primary" id="downloadBtn">
                    <i class="fas fa-download"></i>
                    Download
                </button>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal" id="reviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Review Submission: <?php echo htmlspecialchars($submission['artworkName']); ?></h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" action="?page=update_submission">
                <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="">Select Status</option>
                        <option value="approved" <?php echo $submission['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $submission['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="pending" <?php echo $submission['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Score (0-100)</label>
                    <input type="number" 
                           class="form-input" 
                           name="score" 
                           min="0" 
                           max="100" 
                           step="0.1" 
                           value="<?php echo $submission['score'] ? $submission['score'] : ''; ?>"
                           placeholder="Enter score">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea class="form-textarea" 
                              name="feedback" 
                              placeholder="Enter your feedback for the participant..."><?php echo htmlspecialchars($submission['jury_feedback']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReviewModal(submissionId) {
            document.getElementById('reviewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        function viewArtwork(filePath, type, title) {
            const modal = document.getElementById('artworkModal');
            const container = document.getElementById('artworkContainer');
            const titleElement = document.getElementById('artworkTitle');
            const downloadBtn = document.getElementById('downloadBtn');
            
            // Set title
            titleElement.textContent = title;
            
            // Set download link
            downloadBtn.onclick = () => window.open(filePath, '_blank');
            
            // Clear container
            container.innerHTML = '';
            
            if (type === 'image') {
                // Create image element
                const img = document.createElement('img');
                img.src = filePath;
                img.alt = title;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '70vh';
                img.style.objectFit = 'contain';
                img.style.borderRadius = '8px';
                
                // Add loading handler
                img.onload = () => {
                    container.innerHTML = '';
                    container.appendChild(img);
                };
                
                img.onerror = () => {
                    container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error loading image</div>';
                };
                
                // Show loading
                container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading image...</div>';
                
            } else if (type === 'video') {
                // Create video element
                const video = document.createElement('video');
                video.src = filePath;
                video.controls = true;
                video.style.maxWidth = '100%';
                video.style.maxHeight = '70vh';
                video.style.borderRadius = '8px';
                video.autoplay = false;
                video.preload = 'metadata';
                
                // Add loading handler
                video.onloadedmetadata = () => {
                    container.innerHTML = '';
                    container.appendChild(video);
                };
                
                video.onerror = () => {
                    container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error loading video</div>';
                };
                
                // Show loading
                container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading video...</div>';
            }
            
            // Show modal
            modal.style.display = 'block';
        }

        function closeArtworkModal() {
            const modal = document.getElementById('artworkModal');
            const container = document.getElementById('artworkContainer');
            
            // Stop any playing video
            const video = container.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
            
            modal.style.display = 'none';
            container.innerHTML = '';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const reviewModal = document.getElementById('reviewModal');
            const artworkModal = document.getElementById('artworkModal');
            
            if (e.target === reviewModal) {
                closeModal();
            }
            
            if (e.target === artworkModal) {
                closeArtworkModal();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeArtworkModal();
            }
        });
    </script>

    <style>
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .review-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .file-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item label {
            font-weight: 600;
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item span {
            color: var(--dark);
            font-weight: 500;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .artwork-title h4 {
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 15px;
        }

        .artwork-description label {
            font-weight: 600;
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .artwork-description p {
            color: var(--dark);
            line-height: 1.6;
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .file-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .score-display {
            background: var(--success);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
        }

        .no-score {
            color: var(--gray);
            font-style: italic;
        }

        .feedback-content {
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--warning);
            color: var(--dark);
            line-height: 1.6;
        }

        .user-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary);
            background: #f0f8ff;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .category-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            display: inline-block;
        }

        .category-photography_paint {
            background: #e3f2fd;
            color: #1565c0;
        }

        .category-short_video {
            background: #fff3e0;
            color: #e65100;
        }

        .text-danger {
            color: var(--danger);
            font-weight: 500;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
        }

        .modal-header {
            background: var(--primary);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .modal form {
            padding: 25px;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            opacity: 0.8;
        }

        /* Artwork Modal Styles */
        .artwork-modal {
            max-width: 90vw;
            max-height: 90vh;
            width: auto;
            padding: 0;
        }

        .artwork-modal .modal-header {
            padding: 15px 20px;
        }

        .artwork-modal .modal-body {
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .artwork-modal .modal-footer {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }

        .loading-message, .error-message {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            font-size: 16px;
        }

        .error-message {
            color: var(--danger);
        }

        .loading-message i {
            font-size: 20px;
        }

        /* Video controls styling */
        video::-webkit-media-controls-panel {
            background-color: rgba(0, 0, 0, 0.8);
        }

        /* Responsive modal */
        @media (max-width: 768px) {
            .artwork-modal {
                max-width: 95vw;
                max-height: 95vh;
            }
            
            .artwork-modal .modal-body {
                padding: 15px;
            }
            
            .file-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .file-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .review-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>