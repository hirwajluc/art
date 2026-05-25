<?php
/**
 * One-time migration script
 * - Creates competition_settings, submission_versions, email_campaigns tables
 * - Backfills every existing submission as version 1 in submission_versions
 *
 * Run ONCE via browser, then delete (or it will refuse to run again).
 * Access: https://yourdomain.com/art/run_migration.php
 */

// ── Simple lock: refuse to run if already done ────────────────────────────────
$lockFile = __DIR__ . '/cache/migration_done.lock';
if (file_exists($lockFile)) {
    die('<h2 style="color:green;font-family:sans-serif;">✅ Migration already applied. Delete <code>cache/migration_done.lock</code> to re-run (not recommended).</h2>');
}

require_once __DIR__ . '/db.php'; // gives us $pdo

$log     = [];
$errors  = [];

function step(string $label, callable $fn, array &$log, array &$errors): void {
    try {
        $result = $fn();
        $log[] = "✅ $label" . ($result ? " — $result" : '');
    } catch (PDOException $e) {
        $errors[] = "❌ $label — " . $e->getMessage();
    }
}

// ── 1. Create competition_settings ────────────────────────────────────────────
step('Create table: competition_settings', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `competition_settings` (
            `id`            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT         NOT NULL,
            `updated_at`    DATETIME     NOT NULL DEFAULT NOW(),
            `updated_by`    INT          NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
}, $log, $errors);

// ── 2. Seed default settings (INSERT IGNORE = safe to re-run) ────────────────
step('Seed default competition settings', function() use ($pdo) {
    $pdo->exec("
        INSERT IGNORE INTO `competition_settings` (`setting_key`, `setting_value`) VALUES
            ('registration_deadline', '2025-12-31 23:59:59'),
            ('submission_deadline',   '2025-12-31 23:59:59'),
            ('competition_title',     'The Power of Creativity: Green Energy for Tomorrow'),
            ('winner_announcement',   '2026-02-01')
    ");
    return 'OK';
}, $log, $errors);

// ── 3. Create submission_versions ────────────────────────────────────────────
step('Create table: submission_versions', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `submission_versions` (
            `id`                INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `submission_id`     INT           NOT NULL,
            `version_number`    INT           NOT NULL DEFAULT 1,
            `artwork_name`      VARCHAR(100)  NOT NULL,
            `description`       TEXT          NOT NULL,
            `file_name`         VARCHAR(255)  NOT NULL,
            `original_filename` VARCHAR(255)  NOT NULL,
            `file_size`         BIGINT        NOT NULL,
            `file_type`         VARCHAR(100)  NOT NULL,
            `file_path`         VARCHAR(500)  NOT NULL,
            `uploaded_at`       DATETIME      NOT NULL DEFAULT NOW(),
            `ip_address`        VARCHAR(45)   NULL,
            INDEX `idx_submission_id` (`submission_id`),
            INDEX `idx_version`       (`submission_id`, `version_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
}, $log, $errors);

// ── 4. Create email_campaigns ────────────────────────────────────────────────
step('Create table: email_campaigns', function() use ($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `email_campaigns` (
            `id`              INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `subject`         VARCHAR(255) NOT NULL,
            `body`            TEXT         NOT NULL,
            `recipient_type`  VARCHAR(50)  NOT NULL,
            `sent_count`      INT          NOT NULL DEFAULT 0,
            `failed_count`    INT          NOT NULL DEFAULT 0,
            `sent_at`         DATETIME     NOT NULL DEFAULT NOW(),
            `sent_by`         INT          NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    return 'OK';
}, $log, $errors);

// ── 5. Backfill existing submissions as version 1 ────────────────────────────
step('Backfill existing submissions → version 1', function() use ($pdo) {
    // Find all submissions that don't have ANY entry in submission_versions yet
    $stmt = $pdo->query("
        SELECT s.*
        FROM submissions s
        LEFT JOIN submission_versions sv ON sv.submission_id = s.id
        WHERE sv.id IS NULL
    ");
    $toBackfill = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($toBackfill)) {
        return 'Nothing to backfill (all submissions already have versions)';
    }

    $insert = $pdo->prepare("
        INSERT INTO submission_versions
            (submission_id, version_number, artwork_name, description,
             file_name, original_filename, file_size, file_type, file_path,
             uploaded_at, ip_address)
        VALUES
            (:sid, 1, :artwork_name, :description,
             :file_name, :original_filename, :file_size, :file_type, :file_path,
             :uploaded_at, :ip_address)
    ");

    $count = 0;
    foreach ($toBackfill as $sub) {
        $insert->execute([
            ':sid'               => $sub['id'],
            ':artwork_name'      => $sub['artworkName']      ?? 'Untitled',
            ':description'       => $sub['description']      ?? '',
            ':file_name'         => $sub['fileName']         ?? '',
            ':original_filename' => $sub['originalFileName'] ?? $sub['fileName'] ?? '',
            ':file_size'         => $sub['fileSize']         ?? 0,
            ':file_type'         => $sub['fileType']         ?? '',
            ':file_path'         => $sub['filePath']         ?? '',
            ':uploaded_at'       => $sub['submissionDate']   ?? date('Y-m-d H:i:s'),
            ':ip_address'        => $sub['ipAddress']        ?? null,
        ]);
        $count++;
    }

    return "Backfilled $count submission(s) as version 1";
}, $log, $errors);

// ── 6. Write lock file ───────────────────────────────────────────────────────
if (empty($errors)) {
    @file_put_contents($lockFile, date('Y-m-d H:i:s') . "\n");
}

// ── Render results ───────────────────────────────────────────────────────────
$allOk = empty($errors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DB Migration</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 720px; margin: 60px auto; padding: 0 20px; background:#f9fafb; }
    h1   { font-size: 24px; color: #1f2937; margin-bottom: 24px; }
    .result { background:#fff; border-radius:12px; padding:24px; box-shadow:0 4px 16px rgba(0,0,0,.08); }
    .step  { padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-size: 15px; }
    .ok    { background: #d1fae5; color: #065f46; }
    .err   { background: #fee2e2; color: #b91c1c; }
    .summary { margin-top: 24px; padding: 18px 22px; border-radius: 10px; font-size: 16px; font-weight: 600; }
    .summary.ok  { background: #d1fae5; color: #065f46; }
    .summary.err { background: #fee2e2; color: #b91c1c; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
  </style>
</head>
<body>
  <h1>🛠 GREATER — Database Migration</h1>
  <div class="result">
    <?php foreach ($log as $line): ?>
      <div class="step ok"><?php echo htmlspecialchars($line); ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $line): ?>
      <div class="step err"><?php echo htmlspecialchars($line); ?></div>
    <?php endforeach; ?>

    <div class="summary <?php echo $allOk ? 'ok' : 'err'; ?>">
      <?php if ($allOk): ?>
        ✅ Migration completed successfully. You can safely delete <code>run_migration.php</code>.
      <?php else: ?>
        ⚠️ Migration completed with errors — check the red steps above.
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
