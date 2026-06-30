<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Auth check
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: submit.php');
    exit;
}
if (isset($_SESSION['auth_time']) && (time() - $_SESSION['auth_time']) > 86400) {
    session_destroy();
    header('Location: submit.php?expired=1');
    exit;
}

require_once 'db.php';

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Competition deadline check ────────────────────────────────────────────────
$submissionDeadline    = null;
$registrationDeadline  = null;
$submissionOpen        = true;
$registrationOpen      = true;

try {
    $dStmt = $pdo->query("SELECT setting_key, setting_value FROM competition_settings");
    foreach ($dStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $k => $v) {
        if ($k === 'submission_deadline')   { $submissionDeadline   = $v; $submissionOpen   = strtotime($v) > time(); }
        if ($k === 'registration_deadline') { $registrationDeadline = $v; $registrationOpen = strtotime($v) > time(); }
    }
} catch (PDOException $e) {
    // table may not exist yet – defaults (open) stay
}

// ── Existing submission + version history ─────────────────────────────────────
$existingSubmission = false;
$submissionVersions = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE userCode = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_code']]);
    $existingSubmission = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingSubmission) {
        try {
            $vStmt = $pdo->prepare("
                SELECT * FROM submission_versions
                WHERE submission_id = ?
                ORDER BY version_number ASC
            ");
            $vStmt->execute([$existingSubmission['id']]);
            $submissionVersions = $vStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $submissionVersions = [];
        }
    }
} catch (PDOException $e) {
    error_log("Submission fetch error: " . $e->getMessage());
    $existingSubmission = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Artwork - Greater Art Competition 2025</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header .subtitle {
            opacity: 0.9;
            font-size: 16px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .user-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 16px;
        }

        .form-content {
            padding: 30px;
        }

        .existing-submission {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .existing-submission h3 {
            color: #155724;
            margin-bottom: 10px;
        }

        .existing-submission p {
            color: #155724;
            margin-bottom: 15px;
        }

        .submission-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: left;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 16px;
        }

        .required {
            color: #dc3545;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-family: inherit;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: block;
            padding: 40px 20px;
            border: 3px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-label:hover {
            border-color: #5a67d8;
            background: #e8f0fe;
        }

        .file-label.drag-over {
            border-color: #5a67d8;
            background: #e8f0fe;
            transform: scale(1.02);
        }

        .file-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
            display: block;
        }

        .file-text {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .file-subtext {
            color: #666;
            font-size: 14px;
        }

        .file-preview {
            margin-top: 20px;
            display: none;
        }

        .file-preview.show {
            display: block;
        }

        .preview-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e1e5e9;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            display: block;
            margin: 0 auto;
        }

        .preview-video {
            width: 100%;
            max-height: 300px;
            border-radius: 8px;
        }

        .file-details {
            margin-top: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .file-details div {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .file-name {
            color: #333;
            font-weight: 600;
        }

        .file-size {
            color: #666;
        }

        .progress-container {
            display: none;
            margin-top: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 15px;
            margin-bottom: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-danger {
            background: #dc3545;
        }

        .error {
            background: #fff5f5;
            color: #dc3545;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }

        .success {
            background: #f0f9ff;
            color: #0369a1;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #0369a1;
        }

        .warning {
            background: #fffbf0;
            color: #d97706;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #d97706;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .guidelines {
            background: #f0f9ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #0369a1;
        }

        .guidelines h4 {
            color: #0369a1;
            margin-bottom: 15px;
        }

        .guidelines ul {
            margin-left: 20px;
            color: #374151;
        }

        .guidelines li {
            margin-bottom: 8px;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .form-content {
                padding: 20px 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .logout-btn {
                position: static;
                display: block;
                margin-top: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="logout.php" class="logout-btn">Logout</a>
            <h1>Submit Your Artwork</h1>
            <p class="subtitle"><img src="Greater_full_logo.png" alt="GREATER" style="max-height:45px; vertical-align:middle; filter:brightness(0) invert(1);"> Art Competition 2025</p>
        </div>

        <div class="user-info">
            <h3>Participant Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">User Code</div>
                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_code']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Category</div>
                    <div class="info-value">
                        <?php 
                        echo $_SESSION['user_category'] === 'photography_paint' 
                            ? 'Photography & Paint' 
                            : 'Short Video'; 
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                </div>
            </div>
        </div>

        <div class="form-content">
            <div id="message-container"></div>

            <?php
            // ── Deadline banner ───────────────────────────────────────────────
            if (!$submissionOpen && $submissionDeadline): ?>
                <div class="warning" style="border-left-color:#ef4444; background:#fee2e2; color:#b91c1c;">
                    🔒 <strong>Submission deadline has passed</strong> (<?php echo date('F j, Y \a\t g:i A', strtotime($submissionDeadline)); ?>).
                    No new submissions or updates are accepted.
                </div>
            <?php elseif ($submissionOpen && $submissionDeadline): ?>
                <div class="warning">
                    ⏳ <strong>Submission deadline:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($submissionDeadline)); ?>
                </div>
            <?php endif; ?>

            <?php if ($existingSubmission): ?>
                <!-- ── Existing submission block ── -->
                <div class="existing-submission">
                    <h3>✅ Artwork Submitted</h3>
                    <p>Your current (latest) submission is shown below.</p>

                    <div class="submission-details">
                        <div><strong>Artwork Name:</strong> <?php echo htmlspecialchars($existingSubmission['artworkName']); ?></div>
                        <div><strong>Last Updated:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($existingSubmission['submissionDate'])); ?></div>
                        <div><strong>Status:</strong> <span style="color:#28a745;">✅ Received</span></div>
                        <?php if (!empty($submissionVersions)): ?>
                        <div style="margin-top:8px;"><strong>Total Versions:</strong> <?php echo count($submissionVersions); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Version history ── -->
                <?php if (!empty($submissionVersions)): ?>
                <div style="margin: 20px 0;">
                    <h4 style="color:#374151; font-size:16px; margin-bottom:12px;">
                        <span style="cursor:pointer;" onclick="toggleVersions()">
                            📋 Version History <span id="versionToggleIcon">▼</span>
                        </span>
                    </h4>
                    <div id="versionHistoryTable" style="display:none; overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:14px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="padding:10px 14px; text-align:left; border-bottom:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Version</th>
                                    <th style="padding:10px 14px; text-align:left; border-bottom:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Artwork Name</th>
                                    <th style="padding:10px 14px; text-align:left; border-bottom:1px solid #e5e7eb; color:#6b7280; font-weight:600;">File</th>
                                    <th style="padding:10px 14px; text-align:left; border-bottom:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Uploaded</th>
                                    <th style="padding:10px 14px; text-align:center; border-bottom:1px solid #e5e7eb; color:#6b7280; font-weight:600;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalVersions = count($submissionVersions);
                                foreach ($submissionVersions as $i => $ver):
                                    $isLatest = ($i === $totalVersions - 1);
                                ?>
                                <tr style="<?php echo $isLatest ? 'background:#f0fdf4;' : ''; ?>">
                                    <td style="padding:10px 14px; border-bottom:1px solid #f3f4f6; font-weight:700; color:<?php echo $isLatest ? '#065f46' : '#374151'; ?>">
                                        v<?php echo (int)$ver['version_number']; ?>
                                    </td>
                                    <td style="padding:10px 14px; border-bottom:1px solid #f3f4f6;"><?php echo htmlspecialchars($ver['artwork_name']); ?></td>
                                    <td style="padding:10px 14px; border-bottom:1px solid #f3f4f6; color:#6b7280; font-size:13px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                        title="<?php echo htmlspecialchars($ver['original_filename']); ?>">
                                        <?php echo htmlspecialchars($ver['original_filename']); ?>
                                    </td>
                                    <td style="padding:10px 14px; border-bottom:1px solid #f3f4f6; color:#6b7280; white-space:nowrap; font-size:13px;">
                                        <?php echo date('M j, Y H:i', strtotime($ver['uploaded_at'])); ?>
                                    </td>
                                    <td style="padding:10px 14px; border-bottom:1px solid #f3f4f6; text-align:center;">
                                        <?php if ($isLatest): ?>
                                            <span style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700;">ACTIVE</span>
                                        <?php else: ?>
                                            <span style="background:#f3f4f6; color:#9ca3af; padding:3px 10px; border-radius:12px; font-size:11px;">Previous</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="font-size:12px; color:#9ca3af; margin-top:8px;">
                            Only the <strong>latest version</strong> is considered by the jury.
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── Update / replace submission ── -->
                <?php if ($submissionOpen): ?>
                <div style="margin-top:24px; padding:24px; background:#fff; border:2px dashed #667eea; border-radius:12px;">
                    <h4 style="color:#374151; font-size:17px; margin-bottom:8px;">🔄 Update Your Submission</h4>
                    <p style="color:#6b7280; font-size:14px; margin-bottom:20px;">
                        You can upload a new version of your artwork. The previous version will be kept for reference,
                        but the <strong>latest version</strong> will be the one reviewed by the jury.
                    </p>

                    <form id="updateForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="userCode"   value="<?php echo htmlspecialchars($_SESSION['user_code']); ?>">

                        <div class="form-group">
                            <label for="upd_artworkName">Artwork Name <span class="required">*</span></label>
                            <input type="text" id="upd_artworkName" name="artworkName"
                                   value="<?php echo htmlspecialchars($existingSubmission['artworkName']); ?>"
                                   required maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="upd_description">Description <span class="required">*</span></label>
                            <textarea id="upd_description" name="description"
                                      required minlength="20" maxlength="1000"><?php echo htmlspecialchars($existingSubmission['description']); ?></textarea>
                            <div style="font-size:14px; color:#666; margin-top:5px;">
                                <span id="updCharCount"><?php echo strlen($existingSubmission['description']); ?></span>/1000 characters
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="upd_artworkFile">New File <span class="required">*</span></label>
                            <div class="file-upload">
                                <input type="file" id="upd_artworkFile" name="artworkFile" class="file-input"
                                       accept="<?php echo $_SESSION['user_category'] === 'photography_paint' ? 'image/*' : 'video/*'; ?>" required>
                                <label for="upd_artworkFile" class="file-label" id="updFileLabel">
                                    <span class="file-icon"><?php echo $_SESSION['user_category'] === 'photography_paint' ? '🎨' : '🎬'; ?></span>
                                    <div class="file-text">Click to select your updated file</div>
                                    <div class="file-subtext">
                                        <?php echo $_SESSION['user_category'] === 'photography_paint' ? 'JPG, PNG, TIFF up to 50MB' : 'MP4, MOV, AVI up to 500MB'; ?>
                                    </div>
                                </label>
                            </div>
                            <div class="file-preview" id="updFilePreview">
                                <div class="preview-container">
                                    <div id="updPreviewContent"></div>
                                    <div class="file-details" id="updFileDetails"></div>
                                </div>
                            </div>
                        </div>

                        <div class="progress-container" id="updProgressContainer">
                            <div class="progress-bar"><div class="progress-fill" id="updProgressFill"></div></div>
                            <div style="text-align:center; margin-top:8px;"><span id="updProgressText">0%</span></div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn" id="updateBtn">🔄 Upload New Version</button>
                            <button type="button" class="btn btn-secondary" onclick="clearUpdateForm()">Clear</button>
                        </div>
                    </form>
                    <div class="loading" id="updLoading"><div class="spinner"></div><p>Uploading new version…</p></div>
                </div>
                <?php else: ?>
                <div class="warning" style="border-left-color:#ef4444; background:#fee2e2; color:#b91c1c; margin-top:20px;">
                    🔒 The submission deadline has passed. No further changes can be made.
                    Your last submitted version will be reviewed by the jury.
                </div>
                <?php endif; ?>

                <div style="margin-top:20px;">
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>

            <?php elseif (!$submissionOpen): ?>
                <!-- ── Deadline passed, no submission ── -->
                <div class="warning" style="border-left-color:#ef4444; background:#fee2e2; color:#b91c1c;">
                    🔒 <strong>The submission deadline has passed.</strong> New submissions are no longer accepted.
                    If you believe this is an error, please contact
                    <a href="mailto:info@greaterproject.eu" style="color:#b91c1c;">info@greaterproject.eu</a>.
                </div>
                <div style="margin-top:20px;">
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>

            <?php else: ?>
                <!-- ── First-time submission form ── -->
                <div class="guidelines">
                    <h4>Submission Guidelines for <?php echo $_SESSION['user_category'] === 'photography_paint' ? 'Photography & Paint' : 'Short Video'; ?></h4>
                    <?php if ($_SESSION['user_category'] === 'photography_paint'): ?>
                        <ul>
                            <li>Submit high-resolution images (minimum 300 DPI)</li>
                            <li>Accepted formats: JPG, PNG, TIFF</li>
                            <li>Maximum file size: 50MB</li>
                            <li>Original artwork only — no AI-generated content</li>
                            <li>Ensure good lighting and clear focus</li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li>Maximum duration: 15 minutes</li>
                            <li>Preferred format: MP4, MOV, AVI</li>
                            <li>Maximum file size: 500MB</li>
                            <li>Minimum resolution: 1080p (1920×1080)</li>
                            <li>Original content only — no copyrighted material</li>
                        </ul>
                    <?php endif; ?>
                </div>

                <form id="submissionForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="userCode"   value="<?php echo $_SESSION['user_code']; ?>">
                    <input type="hidden" name="category"   value="<?php echo $_SESSION['user_category']; ?>">

                    <div class="form-group">
                        <label for="artworkName">Name of Your Artwork <span class="required">*</span></label>
                        <input type="text" id="artworkName" name="artworkName"
                               placeholder="Enter the title of your artwork" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="description">Artwork Description <span class="required">*</span></label>
                        <textarea id="description" name="description"
                                  placeholder="Describe your artwork, inspiration, techniques used, etc. (minimum 50 characters)"
                                  required minlength="50" maxlength="1000"></textarea>
                        <div style="font-size:14px; color:#666; margin-top:5px;">
                            <span id="charCount">0</span>/1000 characters (minimum 50)
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="artworkFile">Upload Your Artwork <span class="required">*</span></label>
                        <div class="file-upload">
                            <input type="file" id="artworkFile" name="artworkFile" class="file-input"
                                   accept="<?php echo $_SESSION['user_category'] === 'photography_paint' ? 'image/*' : 'video/*'; ?>" required>
                            <label for="artworkFile" class="file-label" id="fileLabel">
                                <span class="file-icon"><?php echo $_SESSION['user_category'] === 'photography_paint' ? '🎨' : '🎬'; ?></span>
                                <div class="file-text">
                                    Click to select or drag and drop your
                                    <?php echo $_SESSION['user_category'] === 'photography_paint' ? 'image' : 'video'; ?>
                                </div>
                                <div class="file-subtext">
                                    <?php echo $_SESSION['user_category'] === 'photography_paint' ? 'JPG, PNG, TIFF up to 50MB' : 'MP4, MOV, AVI up to 500MB'; ?>
                                </div>
                            </label>
                        </div>

                        <div class="file-preview" id="filePreview">
                            <div class="preview-container">
                                <div id="previewContent"></div>
                                <div class="file-details" id="fileDetails"></div>
                            </div>
                        </div>

                        <div class="progress-container" id="progressContainer">
                            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                            <div style="text-align:center; margin-top:10px;"><span id="progressText">0%</span></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn" id="submitBtn">Submit Artwork</button>
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear Form</button>
                    </div>
                </form>

                <div class="loading" id="loading"><div class="spinner"></div><p>Uploading your artwork…</p></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const category    = '<?php echo $_SESSION['user_category']; ?>';
        const maxSzImage  = 50  * 1024 * 1024;
        const maxSzVideo  = 500 * 1024 * 1024;

        // ── Version history toggle ──────────────────────────────────────────
        function toggleVersions() {
            const tbl  = document.getElementById('versionHistoryTable');
            const icon = document.getElementById('versionToggleIcon');
            if (!tbl) return;
            const shown = tbl.style.display !== 'none';
            tbl.style.display  = shown ? 'none' : 'block';
            icon.textContent   = shown ? '▼' : '▲';
        }

        // ── Shared helpers ──────────────────────────────────────────────────
        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024, s = ['B','KB','MB','GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + s[i];
        }

        function showMessage(message, type) {
            const c = document.getElementById('message-container');
            if (!c) return;
            c.innerHTML = `<div class="${type}">${message}</div>`;
            c.scrollIntoView({ behavior:'smooth', block:'center' });
            if (type === 'success') setTimeout(() => c.innerHTML = '', 10000);
        }

        function buildPreview(file, previewContentEl, fileDetailsEl, filePreviewEl) {
            previewContentEl.innerHTML = '';
            if (file.type.startsWith('image/')) {
                // Images are small enough to load as data URL for preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result; img.className = 'preview-image'; img.alt = 'Preview';
                    previewContentEl.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                // Use object URL for video — avoids loading hundreds of MB into memory as base64
                const objectUrl = URL.createObjectURL(file);
                const v = document.createElement('video');
                v.src = objectUrl; v.className = 'preview-video'; v.controls = true;
                v.onload = function() { URL.revokeObjectURL(objectUrl); };
                previewContentEl.appendChild(v);
            }
            fileDetailsEl.innerHTML = `
                <div class="file-name">📁 ${file.name}</div>
                <div class="file-size">📊 ${formatFileSize(file.size)}</div>`;
            filePreviewEl.classList.add('show');
        }

        // ── First-time submission form ──────────────────────────────────────
        <?php if (!$existingSubmission && $submissionOpen): ?>
        let selectedFile = null;

        (function() {
            const fi = document.getElementById('artworkFile');
            const fl = document.getElementById('fileLabel');
            if (!fi) return;

            fi.addEventListener('change', () => handleFileSelect(fi, fl, 'filePreview','previewContent','fileDetails', true));
            fl.addEventListener('dragover',  e => { e.preventDefault(); fl.classList.add('drag-over'); });
            fl.addEventListener('dragleave', e => { e.preventDefault(); fl.classList.remove('drag-over'); });
            fl.addEventListener('drop', e => {
                e.preventDefault(); fl.classList.remove('drag-over');
                if (e.dataTransfer.files.length) { fi.files = e.dataTransfer.files; handleFileSelect(fi, fl,'filePreview','previewContent','fileDetails', true); }
            });
        })();

        function handleFileSelect(fileInput, fileLabel, previewId, previewContentId, fileDetailsId, isMain) {
            const file = fileInput.files[0];
            if (!file) { if(isMain) selectedFile = null; return; }
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            if (category === 'photography_paint' && !isImage) { showMessage('Please select an image (JPG, PNG, TIFF)', 'error'); fileInput.value=''; return; }
            if (category === 'short_video'       && !isVideo) { showMessage('Please select a video (MP4, MOV, AVI)',  'error'); fileInput.value=''; return; }
            const maxSize = category === 'photography_paint' ? maxSzImage : maxSzVideo;
            if (file.size > maxSize) { showMessage('File too large ('+(maxSize/1024/1024)+'MB limit)', 'error'); fileInput.value=''; return; }
            if (isMain) selectedFile = file;
            buildPreview(file, document.getElementById(previewContentId), document.getElementById(fileDetailsId), document.getElementById(previewId));
        }

        document.getElementById('description')?.addEventListener('input', function() {
            const n = this.value.length;
            const c = document.getElementById('charCount');
            if(c) { c.textContent = n; c.style.color = n < 50 ? '#dc3545' : n > 900 ? '#d97706' : '#28a745'; }
        });

        document.getElementById('submissionForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('artworkName').value.trim();
            const desc = document.getElementById('description').value.trim();
            if (!name) { showMessage('Please enter artwork name', 'error'); return; }
            if (desc.length < 50) { showMessage('Description must be at least 50 characters', 'error'); return; }
            if (!selectedFile)    { showMessage('Please select a file to upload', 'error'); return; }
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            const fd = new FormData(this);
            fd.set('artworkFile', selectedFile);
            xhrUpload(fd, 'submission_handler.php', 'progressFill','progressText','progressContainer', function(resp) {
                if (resp.success) {
                    showMessage('🎉 Artwork submitted successfully! Redirecting…', 'success');
                    setTimeout(() => location.reload(), 2500);
                } else {
                    showMessage(resp.message || 'Submission failed.', 'error');
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitBtn').disabled = false;
                }
            });
        });

        function clearForm() {
            if (!confirm('Clear all form data?')) return;
            document.getElementById('submissionForm').reset();
            selectedFile = null;
            document.getElementById('filePreview').classList.remove('show');
            document.getElementById('charCount').textContent = '0';
            document.getElementById('charCount').style.color = '#dc3545';
        }
        <?php endif; ?>

        // ── Update / version form ───────────────────────────────────────────
        <?php if ($existingSubmission && $submissionOpen): ?>
        let updFile = null;

        document.getElementById('upd_description')?.addEventListener('input', function() {
            const c = document.getElementById('updCharCount');
            if (c) c.textContent = this.value.length;
        });

        document.getElementById('upd_artworkFile')?.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const maxSize = category === 'photography_paint' ? maxSzImage : maxSzVideo;
            if (file.size > maxSize) { showMessage('File too large', 'error'); this.value = ''; return; }
            updFile = file;
            buildPreview(file, document.getElementById('updPreviewContent'), document.getElementById('updFileDetails'), document.getElementById('updFilePreview'));
        });

        document.getElementById('updateForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!updFile) { showMessage('Please select a file.', 'error'); return; }
            const name = document.getElementById('upd_artworkName').value.trim();
            const desc = document.getElementById('upd_description').value.trim();
            if (!name) { showMessage('Please enter artwork name.', 'error'); return; }
            if (desc.length < 20) { showMessage('Description is too short.', 'error'); return; }

            if (!confirm('Upload a new version? Previous version will be kept for reference.')) return;

            document.getElementById('updLoading').style.display = 'block';
            document.getElementById('updateBtn').disabled = true;

            const fd = new FormData(this);
            fd.set('artworkFile', updFile);
            xhrUpload(fd, 'update_submission_handler.php', 'updProgressFill','updProgressText','updProgressContainer', function(resp) {
                if (resp.success) {
                    showMessage('✅ ' + resp.message, 'success');
                    setTimeout(() => location.reload(), 2500);
                } else {
                    showMessage('❌ ' + (resp.message || 'Update failed.'), 'error');
                    document.getElementById('updLoading').style.display = 'none';
                    document.getElementById('updateBtn').disabled = false;
                }
            });
        });

        function clearUpdateForm() {
            if (!confirm('Reset the update form?')) return;
            document.getElementById('updateForm').reset();
            updFile = null;
            document.getElementById('updFilePreview').classList.remove('show');
        }
        <?php endif; ?>

        // ── Generic XHR upload with progress ────────────────────────────────
        function xhrUpload(formData, url, fillId, textId, containerId, callback) {
            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (!e.lengthComputable) return;
                const pct = Math.round((e.loaded / e.total) * 100);
                const fill = document.getElementById(fillId);
                const text = document.getElementById(textId);
                const cont = document.getElementById(containerId);
                if (cont) cont.style.display = 'block';
                if (fill) fill.style.width = pct + '%';
                if (text) text.textContent  = pct + '%';
            });
            xhr.addEventListener('load', function() {
                try { callback(JSON.parse(xhr.responseText)); }
                catch(err) { callback({ success: false, message: 'Response parse error.' }); }
            });
            xhr.addEventListener('error', function() { callback({ success: false, message: 'Network error.' }); });
            xhr.open('POST', url);
            xhr.send(formData);
        }
    </script>
</body>
</html>