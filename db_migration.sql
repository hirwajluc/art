-- ============================================================
-- GREATER Art Competition - DB Migration
-- Feature 1: Admin Email Campaigns
-- Feature 2: Submission Versioning + Competition Deadline
-- ============================================================

-- Competition settings (single-row config table)
CREATE TABLE IF NOT EXISTS `competition_settings` (
    `id`                   INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `setting_key`          VARCHAR(100) NOT NULL UNIQUE,
    `setting_value`        TEXT         NOT NULL,
    `updated_at`           DATETIME     NOT NULL DEFAULT NOW(),
    `updated_by`           INT          NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default settings
INSERT IGNORE INTO `competition_settings` (`setting_key`, `setting_value`) VALUES
    ('registration_deadline', '2025-12-31 23:59:59'),
    ('submission_deadline',   '2025-12-31 23:59:59'),
    ('competition_title',     'The Power of Creativity: Green Energy for Tomorrow'),
    ('winner_announcement',   '2026-02-01');

-- Submission versions table (preserves every upload)
CREATE TABLE IF NOT EXISTS `submission_versions` (
    `id`               INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `submission_id`    INT           NOT NULL,
    `version_number`   INT           NOT NULL DEFAULT 1,
    `artwork_name`     VARCHAR(100)  NOT NULL,
    `description`      TEXT          NOT NULL,
    `file_name`        VARCHAR(255)  NOT NULL,
    `original_filename`VARCHAR(255)  NOT NULL,
    `file_size`        BIGINT        NOT NULL,
    `file_type`        VARCHAR(100)  NOT NULL,
    `file_path`        VARCHAR(500)  NOT NULL,
    `uploaded_at`      DATETIME      NOT NULL DEFAULT NOW(),
    `ip_address`       VARCHAR(45)   NULL,
    INDEX `idx_submission_id` (`submission_id`),
    INDEX `idx_version`       (`submission_id`, `version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email log table (track sent campaigns)
CREATE TABLE IF NOT EXISTS `email_campaigns` (
    `id`            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subject`       VARCHAR(255) NOT NULL,
    `body`          TEXT         NOT NULL,
    `recipient_type`VARCHAR(50)  NOT NULL COMMENT 'registered_no_submission | submitted_all',
    `sent_count`    INT          NOT NULL DEFAULT 0,
    `failed_count`  INT          NOT NULL DEFAULT 0,
    `sent_at`       DATETIME     NOT NULL DEFAULT NOW(),
    `sent_by`       INT          NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
