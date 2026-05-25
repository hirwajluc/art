<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition - Submissions</title>
    <?php include 'views/includes/styles.php'; ?>
</head>
<body>
    <div class="admin-container">
        <?php include 'views/includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'views/includes/topbar.php'; ?>
            
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
            
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Submissions (<?php echo $totalCount; ?>)</h3>
                    <div class="search-box">
                        <form method="GET" style="display: flex; gap: 10px;">
                            <input type="hidden" name="page" value="submissions">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <input type="text" 
                                   class="search-input" 
                                   name="search" 
                                   placeholder="Search submissions..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search) || !empty($status)): ?>
                                <a href="?page=submissions" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User Code</th>
                            <!--<th>Participant</th>-->
                            <th>Artwork Title</th>
                            <th>Category</th>
                            <th>File Info</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($submissions)): ?>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td>
                                        <span class="user-code"><?php echo htmlspecialchars($submission['userCode']); ?></span>
                                    </td>
                                    <!--<td>
                                        <div class="participant-info">
                                            <strong><?php echo htmlspecialchars($submission['userName']); ?></strong>
                                            <small><?php echo htmlspecialchars($submission['userEmail']); ?></small>
                                        </div>
                                    </td>-->
                                    <td>
                                        <div class="artwork-info">
                                            <strong><?php echo htmlspecialchars($submission['artworkName']); ?></strong>
                                            <small><?php echo htmlspecialchars(substr($submission['description'], 0, 100)) . '...'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge category-<?php echo $submission['category']; ?>">
                                            <?php echo $submission['category'] === 'photography_paint' ? 'Photography' : 'Video'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="file-info">
                                            <div class="file-size"><?php echo formatFileSize($submission['fileSize']); ?></div>
                                            <small><?php echo strtoupper(pathinfo($submission['originalFileName'], PATHINFO_EXTENSION)); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y H:i', strtotime($submission['submissionDate'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $submission['status']; ?>">
                                            <?php echo ucfirst($submission['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($submission['score']): ?>
                                            <span class="score-badge"><?php echo number_format($submission['score'], 1); ?>/100</span>
                                        <?php else: ?>
                                            <span class="no-score">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?page=submission_detail&id=<?php echo $submission['id']; ?>" 
                                               class="btn btn-primary btn-sm" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php 
                                            // Quick view button for images and videos
                                            $actualFilePath = '../' . $submission['filePath']; // From admin to uploads
                                            $webFilePath = '../' . $submission['filePath'];    // For web access
                                            if (file_exists($actualFilePath)): 
                                                $fileExtension = strtolower(pathinfo($submission['originalFileName'], PATHINFO_EXTENSION));
                                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                $isVideo = in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm']);
                                                if ($isImage || $isVideo):
                                            ?>
                                                <button onclick="viewArtwork('<?php echo $webFilePath; ?>', '<?php echo $isImage ? 'image' : 'video'; ?>', '<?php echo htmlspecialchars($submission['artworkName']); ?>')" 
                                                        class="btn btn-info btn-sm" 
                                                        title="<?php echo $isImage ? 'View Image' : 'Play Video'; ?>">
                                                    <i class="fas fa-<?php echo $isImage ? 'image' : 'play'; ?>"></i>
                                                </button>
                                            <?php 
                                                endif;
                                            endif; 
                                            ?>
                                            <?php if ($submission['status'] === 'pending'): ?>
                                                <button onclick="openReviewModal(<?php echo $submission['id']; ?>)" 
                                                        class="btn btn-warning btn-sm" 
                                                        title="Review">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php 
                                            // Download button
                                            if (file_exists('../' . $submission['filePath'])): 
                                            ?>
                                                <a href="<?php echo '../' . $submission['filePath']; ?>" 
                                                   class="btn btn-success btn-sm" 
                                                   title="Download File" 
                                                   target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="no-data">
                                    <div class="no-data-content">
                                        <i class="fas fa-images"></i>
                                        <p>No submissions found</p>
                                        <?php if (!empty($search) || !empty($status)): ?>
                                            <small>Try adjusting your search or filter criteria</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=submissions&p=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=submissions&p=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=submissions&p=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>" 
                               class="btn btn-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
                <h3 class="modal-title">Review Submission</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form id="reviewForm" method="POST" action="?page=update_submission">
                <input type="hidden" id="submissionId" name="id">
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="">Select Status</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Score (0-100)</label>
                    <input type="number" class="form-input" name="score" min="0" max="100" step="0.1" placeholder="Enter score">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea class="form-textarea" name="feedback" placeholder="Enter your feedback for the participant..."></textarea>
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
            document.getElementById('submissionId').value = submissionId;
            document.getElementById('reviewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.getElementById('reviewForm').reset();
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
        .participant-info {
            display: flex;
            flex-direction: column;
        }

        .participant-info small {
            color: var(--gray);
            font-size: 12px;
        }

        .artwork-info {
            display: flex;
            flex-direction: column;
            max-width: 200px;
        }

        .artwork-info strong {
            margin-bottom: 2px;
        }

        .artwork-info small {
            color: var(--gray);
            font-size: 11px;
            line-height: 1.3;
        }

        .file-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .file-size {
            font-weight: 500;
            color: var(--dark);
        }

        .file-info small {
            color: var(--gray);
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .score-badge {
            background: var(--success);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .no-score {
            color: var(--gray);
            font-style: italic;
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
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: var(--danger);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
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
        }
    </style>
</body>
</html>