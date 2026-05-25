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
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                    <div class="user-avatar"><?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?></div>
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
                                        // Extract filename from the file path
                                        $fileName = basename($submission['filePath']);
                                        $actualFilePath = '../' . $submission['filePath'];
                                        
                                        if (file_exists($actualFilePath)): 
                                            $fileExtension = strtolower(pathinfo($submission['originalFileName'], PATHINFO_EXTENSION));
                                            $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'tiff']);
                                            $isVideo = in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm', 'ogg', 'quicktime']);
                                        ?>
                                            <?php if ($isImage || $isVideo): ?>
                                                <button onclick="viewArtworkOptimized('<?php echo $fileName; ?>', '<?php echo $isImage ? 'image' : 'video'; ?>', '<?php echo htmlspecialchars($submission['artworkName']); ?>')" 
                                                        class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                    <?php echo $isImage ? 'View Image' : 'Play Video'; ?>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="file.php?file=<?php echo urlencode($fileName); ?>&action=download" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                            
                                            <a href="file.php?file=<?php echo urlencode($fileName); ?>&action=view" 
                                               class="btn btn-warning btn-sm" 
                                               target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                                Open in New Tab
                                            </a>
                                            
                                            <?php if ($isImage): ?>
                                                <button onclick="showThumbnail('<?php echo $fileName; ?>')" 
                                                        class="btn btn-info btn-sm">
                                                    <i class="fas fa-image"></i>
                                                    Quick Preview
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-danger">File not found: <?php echo htmlspecialchars($fileName); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Preview Card (for images) -->
                    <?php if (isset($isImage) && $isImage && file_exists($actualFilePath)): ?>
                    <div class="detail-card full-width">
                        <div class="card-header">
                            <h3><i class="fas fa-image"></i> Quick Preview</h3>
                            <button onclick="viewArtworkOptimized('<?php echo $fileName; ?>', 'image', '<?php echo htmlspecialchars($submission['artworkName']); ?>')" 
                                    class="btn btn-light btn-sm">
                                <i class="fas fa-expand"></i> Full Screen
                            </button>
                        </div>
                        <div class="card-content">
                            <div class="quick-preview-container">
                                <div class="media-container">
                                    <img 
                                        id="quickPreviewImg"
                                        data-lazy-src="file.php?file=<?php echo urlencode($fileName); ?>&action=thumbnail"
                                        data-full-src="file.php?file=<?php echo urlencode($fileName); ?>&action=view"
                                        data-type="image"
                                        class="quick-preview-image lazy-image"
                                        alt="<?php echo htmlspecialchars($submission['artworkName']); ?>"
                                        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                        onclick="viewArtworkOptimized('<?php echo $fileName; ?>', 'image', '<?php echo htmlspecialchars($submission['artworkName']); ?>')"
                                    />
                                    <div class="image-overlay">
                                        <div class="overlay-buttons">
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    onclick="viewArtworkOptimized('<?php echo $fileName; ?>', 'image', '<?php echo htmlspecialchars($submission['artworkName']); ?>')">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="window.open('file.php?file=<?php echo urlencode($fileName); ?>&action=download')">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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

            <!-- ── Version History ─────────────────────────────────────── -->
            <?php
            // Load version history
            $submissionVersions = [];
            try {
                require_once __DIR__ . '/../config/database.php';
                $database2 = new Database();
                $dbConn2   = $database2->getConnection();
                $vStmt2 = $dbConn2->prepare("SELECT * FROM submission_versions WHERE submission_id = ? ORDER BY version_number ASC");
                $vStmt2->execute([$submission['id']]);
                $submissionVersions = $vStmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { /* table may not exist */ }
            ?>
            <?php if (!empty($submissionVersions)): ?>
            <div class="table-container" style="margin-top:0;">
                <div class="table-header">
                    <h3 class="table-title"><i class="fas fa-history"></i> Submission Version History</h3>
                    <span style="color:#6b7280; font-size:14px;"><?php echo count($submissionVersions); ?> version(s) — latest is considered by jury</span>
                </div>
                <table class="data-table" style="font-size:14px;">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Artwork Name</th>
                            <th>Original File</th>
                            <th>File Size</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalVers = count($submissionVersions);
                    foreach ($submissionVersions as $vi => $ver):
                        $isLatest = ($vi === $totalVers - 1);
                    ?>
                        <tr style="<?php echo $isLatest ? 'background:#f0fdf4;' : ''; ?>">
                            <td style="font-weight:700; color:<?php echo $isLatest?'#065f46':'#374151';?>">
                                v<?php echo (int)$ver['version_number']; ?>
                            </td>
                            <td><?php echo htmlspecialchars($ver['artwork_name']); ?></td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                title="<?php echo htmlspecialchars($ver['original_filename']); ?>">
                                <?php echo htmlspecialchars($ver['original_filename']); ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php
                                $sz = (int)$ver['file_size'];
                                if ($sz > 1048576) echo round($sz/1048576,1).' MB';
                                elseif ($sz > 1024) echo round($sz/1024,1).' KB';
                                else echo $sz.' B';
                                ?>
                            </td>
                            <td style="white-space:nowrap; color:#6b7280;"><?php echo date('M j, Y H:i', strtotime($ver['uploaded_at'])); ?></td>
                            <td>
                                <?php if ($isLatest): ?>
                                    <span class="status-badge" style="background:#d1fae5; color:#065f46; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:700;">✅ ACTIVE</span>
                                <?php else: ?>
                                    <span class="status-badge" style="background:#f3f4f6; color:#9ca3af; padding:4px 10px; border-radius:12px; font-size:11px;">Previous</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Optimized Artwork Viewer Modal -->
    <div class="preview-modal" id="artworkModal">
        <div class="preview-modal-backdrop" onclick="closeArtworkModal()"></div>
        <div class="preview-modal-content">
            <div class="preview-modal-header">
                <span class="preview-modal-title" id="artworkTitle">Artwork Viewer</span>
                <button class="preview-modal-close" onclick="closeArtworkModal()">&times;</button>
            </div>
            <div class="preview-modal-body" id="artworkContainer">
                <div class="preview-loading">
                    <div class="spinner"></div>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="preview-modal-footer">
                <button class="btn btn-secondary" onclick="closeArtworkModal()">Close</button>
                <a href="#" class="btn btn-primary" id="downloadBtn">
                    <i class="fas fa-download"></i> Download
                </a>
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

    <!-- Include the optimized preview JavaScript -->
    <script>
        // Optimized Preview System
        class SubmissionPreview {
            constructor() {
                this.imageCache = new Map();
                this.loadingQueue = new Set();
                this.init();
            }

            init() {
                this.setupLazyLoading();
                this.loadQuickPreview();
            }

            setupLazyLoading() {
                const options = {
                    root: null,
                    rootMargin: '50px',
                    threshold: 0.1
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadMedia(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, options);

                document.querySelectorAll('[data-lazy-src]').forEach(el => {
                    observer.observe(el);
                });
            }

            async loadMedia(element) {
                const src = element.dataset.lazySrc;
                const fullSrc = element.dataset.fullSrc;
                
                if (this.loadingQueue.has(src)) return;
                this.loadingQueue.add(src);
                
                try {
                    // Show loading
                    this.showLoadingSpinner(element);
                    
                    // Load thumbnail first
                    await this.preloadImage(src);
                    element.src = src;
                    element.classList.add('thumbnail-loaded');
                    
                    // Then load full image if available
                    if (fullSrc) {
                        await this.preloadImage(fullSrc);
                        element.dataset.fullLoaded = 'true';
                    }
                    
                    this.hideLoadingSpinner(element);
                } catch (error) {
                    console.error('Error loading media:', error);
                    this.showErrorPlaceholder(element);
                } finally {
                    this.loadingQueue.delete(src);
                }
            }

            loadQuickPreview() {
                const quickPreview = document.getElementById('quickPreviewImg');
                if (quickPreview) {
                    this.loadMedia(quickPreview);
                }
            }

            preloadImage(src) {
                return new Promise((resolve, reject) => {
                    if (this.imageCache.has(src)) {
                        resolve(this.imageCache.get(src));
                        return;
                    }

                    const img = new Image();
                    img.onload = () => {
                        this.imageCache.set(src, img);
                        resolve(img);
                    };
                    img.onerror = reject;
                    img.src = src;
                });
            }

            showLoadingSpinner(element) {
                const container = element.closest('.media-container') || element.parentNode;
                if (!container.querySelector('.loading-spinner')) {
                    const spinner = document.createElement('div');
                    spinner.className = 'loading-spinner';
                    container.style.position = 'relative';
                    container.appendChild(spinner);
                }
            }

            hideLoadingSpinner(element) {
                const container = element.closest('.media-container') || element.parentNode;
                const spinner = container.querySelector('.loading-spinner');
                if (spinner) {
                    spinner.remove();
                }
            }

            showErrorPlaceholder(element) {
                element.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJMMTMuMDkgOC4yNkwyMCA5TDEzLjA5IDE1Ljc0TDEyIDIyTDEwLjkxIDE1Ljc0TDQgOUwxMC45MSA4LjI2TDEyIDJaIiBzdHJva2U9IiNjY2MiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo=';
                this.hideLoadingSpinner(element);
            }
        }

        // Initialize preview system
        const previewSystem = new SubmissionPreview();

        // Optimized artwork viewer
        async function viewArtworkOptimized(fileName, type, title) {
            const modal = document.getElementById('artworkModal');
            const container = document.getElementById('artworkContainer');
            const titleElement = document.getElementById('artworkTitle');
            const downloadBtn = document.getElementById('downloadBtn');
            
            // Set title and download link
            titleElement.textContent = title;
            downloadBtn.href = `file.php?file=${encodeURIComponent(fileName)}&action=download`;
            
            // Show loading state
            container.innerHTML = `
                <div class="preview-loading">
                    <div class="spinner"></div>
                    <p>Loading ${type}...</p>
                </div>
            `;
            
            // Show modal
            modal.classList.add('show');
            
            try {
                if (type === 'image') {
                    // Load thumbnail first, then full image
                    const thumbnailSrc = `file.php?file=${encodeURIComponent(fileName)}&action=thumbnail`;
                    const fullSrc = `file.php?file=${encodeURIComponent(fileName)}&action=view`;
                    
                    // Create image element
                    const img = document.createElement('img');
                    img.className = 'preview-image';
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '80vh';
                    img.style.objectFit = 'contain';
                    img.style.borderRadius = '8px';
                    img.alt = title;
                    
                    // Load thumbnail first
                    await previewSystem.preloadImage(thumbnailSrc);
                    img.src = thumbnailSrc;
                    img.style.filter = 'blur(2px)';
                    
                    container.innerHTML = '';
                    container.appendChild(img);
                    
                    // Then load full image
                    await previewSystem.preloadImage(fullSrc);
                    img.src = fullSrc;
                    img.style.filter = 'none';
                    img.style.transition = 'filter 0.3s ease';
                    
                } else if (type === 'video') {
                    const video = document.createElement('video');
                    video.className = 'preview-video';
                    video.controls = true;
                    video.style.maxWidth = '100%';
                    video.style.maxHeight = '80vh';
                    video.style.borderRadius = '8px';
                    video.preload = 'metadata';
                    video.src = `file.php?file=${encodeURIComponent(fileName)}&action=view`;
                    
                    container.innerHTML = '';
                    container.appendChild(video);
                }
            } catch (error) {
                container.innerHTML = '<p class="error">Error loading preview</p>';
            }
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
            
            modal.classList.remove('show');
            
            // Clear content after animation
            setTimeout(() => {
                container.innerHTML = '';
            }, 300);
        }

        function showThumbnail(fileName) {
            const quickPreview = document.getElementById('quickPreviewImg');
            if (quickPreview) {
                const thumbnailSrc = `file.php?file=${encodeURIComponent(fileName)}&action=thumbnail`;
                quickPreview.src = thumbnailSrc;
                quickPreview.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function openReviewModal(submissionId) {
            document.getElementById('reviewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Event listeners
        window.addEventListener('click', function(e) {
            const reviewModal = document.getElementById('reviewModal');
            const artworkModal = document.getElementById('artworkModal');
            
            if (e.target === reviewModal) {
                closeModal();
            }
        });

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

        .detail-card.full-width {
            grid-column: 1 / -1;
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

        .info-grid, .review-grid {
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

        /* Quick Preview Styles */
        .quick-preview-container {
            text-align: center;
        }

        .media-container {
            position: relative;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            margin: 1rem 0;
            display: inline-block;
            max-width: 100%;
        }

        .quick-preview-image {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            cursor: pointer;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border-radius: 8px;
        }

        .quick-preview-image:hover {
            transform: scale(1.02);
        }

        .quick-preview-image.thumbnail-loaded {
            filter: blur(1px);
            transition: filter 0.3s ease;
        }

        .quick-preview-image[data-full-loaded="true"] {
            filter: none;
        }

        .image-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .media-container:hover .image-overlay {
            opacity: 1;
        }

        .overlay-buttons {
            display: flex;
            gap: 5px;
        }

        .overlay-buttons .btn {
            padding: 5px 8px;
            font-size: 12px;
            border-radius: 4px;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Modal Styles */
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

        /* Optimized Preview Modal */
        .preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .preview-modal.show {
            opacity: 1;
            visibility: visible;
        }

        .preview-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
        }

        .preview-modal-content {
            position: relative;
            width: 90%;
            max-width: 1200px;
            margin: 2% auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .preview-modal.show .preview-modal-content {
            transform: scale(1);
        }

        .preview-modal-header {
            padding: 1rem 1.5rem;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .preview-modal-close {
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
            border-radius: 4px;
        }

        .preview-modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .preview-modal-body {
            padding: 1.5rem;
            text-align: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .preview-modal-footer {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            color: #666;
        }

        .preview-loading .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .preview-image {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .preview-video {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .error {
            color: var(--danger);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error::before {
            content: "⚠️";
            font-size: 20px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .info-grid, .review-grid {
                grid-template-columns: 1fr;
            }

            .file-actions {
                flex-direction: column;
                gap: 8px;
            }

            .file-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .preview-modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .preview-modal-body {
                padding: 1rem;
            }

            .overlay-buttons {
                flex-direction: column;
            }

            .card-content {
                padding: 20px;
            }

            .card-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .quick-preview-image {
                max-height: 250px;
            }

            .preview-modal-header {
                padding: 1rem;
            }

            .preview-modal-footer {
                padding: 1rem;
                flex-direction: column;
                gap: 10px;
            }

            .preview-modal-footer .btn {
                width: 100%;
            }
        }

        /* Animation for loading states */
        .lazy-image {
            transition: opacity 0.3s ease, filter 0.3s ease;
        }

        .lazy-image[data-lazy-src] {
            opacity: 0.3;
        }

        .lazy-image.thumbnail-loaded {
            opacity: 1;
        }

        /* Accessibility improvements */
        .btn:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .preview-modal-close:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }

        /* Print styles */
        @media print {
            .preview-modal,
            .modal,
            .file-actions,
            .card-header .btn {
                display: none !important;
            }

            .detail-card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .quick-preview-image {
                max-height: 300px;
            }
        }
    </style>
</body>
</html>