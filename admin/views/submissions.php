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
                                <option value="pending"  <?php echo $status === 'pending'  ? 'selected' : ''; ?>>Pending</option>
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
                                            $actualFilePath = '../' . $submission['filePath'];
                                            $webFilePath    = '../' . $submission['filePath'];
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
                                            <?php if (file_exists('../' . $submission['filePath'])): ?>
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
                                <td colspan="8" class="no-data">
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

                <!-- ═══════════════════════════════════════════════════
                     PAGINATION
                ════════════════════════════════════════════════════ -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination-wrapper">

                    <div class="pagination-info">
                        Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong>
                        &nbsp;&mdash;&nbsp; <strong><?php echo $totalCount; ?></strong> total submissions
                    </div>

                    <nav class="pagination-nav" aria-label="Submissions pagination">

                        <?php /* ── Previous ── */ ?>
                        <?php if ($page > 1): ?>
                            <a href="?page=submissions&p=<?php echo ($page - 1);
                                echo !empty($search) ? '&search=' . urlencode($search) : '';
                                echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>"
                               class="page-btn page-prev" aria-label="Previous page">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                                <span>Prev</span>
                            </a>
                        <?php else: ?>
                            <span class="page-btn page-prev disabled" aria-disabled="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                                <span>Prev</span>
                            </span>
                        <?php endif; ?>

                        <?php /* ── Page numbers with smart ellipsis ── */ ?>
                        <?php
                            $window = 2;
                            $shown  = [];
                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($i === 1 || $i === $totalPages ||
                                    ($i >= $page - $window && $i <= $page + $window)) {
                                    $shown[] = $i;
                                }
                            }
                            $prev = null;
                            foreach ($shown as $i):
                                if ($prev !== null && $i - $prev > 1):
                        ?>
                                    <span class="page-ellipsis">&hellip;</span>
                        <?php
                                endif;
                                $prev = $i;
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="page-btn page-current" aria-current="page"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=submissions&p=<?php echo $i;
                                    echo !empty($search) ? '&search=' . urlencode($search) : '';
                                    echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>"
                                   class="page-btn" aria-label="Page <?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php /* ── Next ── */ ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=submissions&p=<?php echo ($page + 1);
                                echo !empty($search) ? '&search=' . urlencode($search) : '';
                                echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>"
                               class="page-btn page-next" aria-label="Next page">
                                <span>Next</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="page-btn page-next disabled" aria-disabled="true">
                                <span>Next</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </span>
                        <?php endif; ?>

                    </nav>
                </div>
                <?php endif; ?>
                <!-- ═══════════════════════════ end pagination ══════ -->

            </div><!-- /.table-container -->
        </main>
    </div>

    <!-- ─── Artwork Viewer Modal ───────────────────────────────────────── -->
    <div class="modal" id="artworkModal">
        <div class="modal-content artwork-modal">
            <div class="modal-header">
                <h3 class="modal-title" id="artworkTitle">Artwork Viewer</h3>
                <button class="close-btn" onclick="closeArtworkModal()">&times;</button>
            </div>
            <div class="modal-body" id="artworkContainer"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeArtworkModal()">Close</button>
                <button class="btn btn-primary" id="downloadBtn">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>

    <!-- ─── Review Modal ──────────────────────────────────────────────── -->
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
                    <label class="form-label">Score (0–100)</label>
                    <input type="number" class="form-input" name="score" min="0" max="100" step="0.1" placeholder="Enter score">
                </div>
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea class="form-textarea" name="feedback" placeholder="Enter your feedback for the participant..."></textarea>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /* ── Review modal ── */
        function openReviewModal(submissionId) {
            document.getElementById('submissionId').value = submissionId;
            document.getElementById('reviewModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.getElementById('reviewForm').reset();
        }

        /* ── Artwork viewer modal ── */
        function viewArtwork(filePath, type, title) {
            const modal     = document.getElementById('artworkModal');
            const container = document.getElementById('artworkContainer');
            const titleEl   = document.getElementById('artworkTitle');
            const dlBtn     = document.getElementById('downloadBtn');

            titleEl.textContent = title;
            dlBtn.onclick = () => window.open(filePath, '_blank');
            container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';

            if (type === 'image') {
                const img = document.createElement('img');
                img.src   = filePath;
                img.alt   = title;
                img.style.cssText = 'max-width:100%;max-height:70vh;object-fit:contain;border-radius:8px;';
                img.onload  = () => { container.innerHTML = ''; container.appendChild(img); };
                img.onerror = () => { container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error loading image</div>'; };
            } else {
                const video = document.createElement('video');
                video.src          = filePath;
                video.controls     = true;
                video.autoplay     = false;
                video.preload      = 'metadata';
                video.style.cssText = 'max-width:100%;max-height:70vh;border-radius:8px;';
                video.onloadedmetadata = () => { container.innerHTML = ''; container.appendChild(video); };
                video.onerror = () => { container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error loading video</div>'; };
            }
            modal.style.display = 'block';
        }
        function closeArtworkModal() {
            const container = document.getElementById('artworkContainer');
            const video = container.querySelector('video');
            if (video) { video.pause(); video.currentTime = 0; }
            document.getElementById('artworkModal').style.display = 'none';
            container.innerHTML = '';
        }

        /* ── Close on outside click / Escape ── */
        window.addEventListener('click', e => {
            if (e.target === document.getElementById('reviewModal'))  closeModal();
            if (e.target === document.getElementById('artworkModal')) closeArtworkModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeModal(); closeArtworkModal(); }
        });
    </script>

    <style>
        /* ── Table helpers ────────────────────────────────────────────── */
        .artwork-info { display:flex; flex-direction:column; max-width:200px; }
        .artwork-info strong { margin-bottom:2px; }
        .artwork-info small  { color:var(--gray); font-size:11px; line-height:1.3; }

        .file-info  { display:flex; flex-direction:column; align-items:center; }
        .file-size  { font-weight:500; color:var(--dark); }
        .file-info small { color:var(--gray); font-size:10px; text-transform:uppercase; font-weight:bold; }

        .score-badge { background:var(--success); color:#fff; padding:4px 8px; border-radius:12px; font-size:12px; font-weight:bold; }
        .no-score    { color:var(--gray); font-style:italic; }
        .btn-info    { background:#17a2b8; color:#fff; }

        /* ── Modals ───────────────────────────────────────────────────── */
        .modal {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5); z-index:1000;
        }
        .modal-content {
            position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
            background:#fff; padding:30px; border-radius:15px;
            width:90%; max-width:500px; max-height:90vh; overflow-y:auto;
        }
        .modal-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #eee;
        }
        .modal-title { font-size:20px; font-weight:600; color:var(--dark); }
        .close-btn   { background:none; border:none; font-size:24px; cursor:pointer;
                       color:var(--gray); width:30px; height:30px;
                       display:flex; align-items:center; justify-content:center; }
        .close-btn:hover { color:var(--danger); }

        /* Artwork modal overrides */
        .artwork-modal { max-width:90vw; width:auto; padding:0; }
        .artwork-modal .modal-header { padding:15px 20px; }
        .artwork-modal .modal-body   {
            padding:20px; text-align:center; background:#f8f9fa;
            min-height:200px; display:flex; align-items:center; justify-content:center;
        }
        .artwork-modal .modal-footer {
            padding:15px 20px; display:flex;
            justify-content:space-between; align-items:center;
            border-top:1px solid #eee;
        }
        .loading-message,
        .error-message { display:flex; align-items:center; gap:10px; color:var(--gray); font-size:16px; }
        .error-message { color:var(--danger); }

        /* ── Pagination ───────────────────────────────────────────────── */
        .pagination-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            padding: 28px 20px 24px;
            border-top: 1px solid rgba(0,0,0,.06);
            margin-top: 4px;
        }

        .pagination-info {
            font-size: 13px;
            color: #6b7280;
            letter-spacing: .01em;
        }
        .pagination-info strong {
            color: #111827;
            font-weight: 600;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        /* Every button */
        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            line-height: 1;
            text-decoration: none;
            color: #374151;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            cursor: pointer;
            transition:
                background .18s ease,
                border-color .18s ease,
                color .18s ease,
                box-shadow .18s ease,
                transform .12s ease;
            user-select: none;
            white-space: nowrap;
        }
        .page-btn:hover:not(.disabled):not(.page-current) {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            box-shadow: 0 2px 8px rgba(59,130,246,.18);
            transform: translateY(-1px);
        }
        .page-btn:active:not(.disabled):not(.page-current) {
            transform: translateY(0);
            box-shadow: none;
        }

        /* Active page */
        .page-current {
            background: linear-gradient(135deg, #1a73e8 0%, #0ea5e9 100%);
            border-color: transparent;
            color: #fff !important;
            font-weight: 700;
            box-shadow:
                0 4px 14px rgba(26,115,232,.40),
                0 1px 3px rgba(26,115,232,.25);
            cursor: default;
        }

        /* Prev / Next pills */
        .page-prev,
        .page-next {
            border-radius: 12px;
            padding: 0 16px;
            font-weight: 600;
            letter-spacing: .01em;
        }

        /* Disabled */
        .page-btn.disabled {
            opacity: .38;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Ellipsis */
        .page-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 38px;
            font-size: 15px;
            color: #9ca3af;
            pointer-events: none;
            user-select: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .page-prev span,
            .page-next span { display: none; }
            .page-prev, .page-next { padding: 0 10px; min-width: 38px; }
            .page-btn { min-width: 34px; height: 34px; font-size: 12.5px; }
        }
    </style>
</body>
</html>