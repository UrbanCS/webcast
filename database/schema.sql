CREATE TABLE IF NOT EXISTS `broadcast_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `start_at` DATETIME NOT NULL COMMENT 'UTC datetime',
  `duration_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  `timezone` VARCHAR(64) NOT NULL DEFAULT 'America/Toronto',
  `youtube_live_input` VARCHAR(255) NULL,
  `youtube_live_video_id` CHAR(11) NULL,
  `youtube_replay_input` VARCHAR(255) NULL,
  `youtube_replay_video_id` CHAR(11) NULL,
  `download_url` VARCHAR(255) NULL,
  `local_file_path` VARCHAR(255) NULL,
  `manual_status` ENUM('scheduled', 'live', 'replay', 'archived') NULL DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_published_start` (`is_published`, `start_at`),
  KEY `idx_manual_status` (`manual_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standalone admin authentication is config-based by design.
-- No admins table is required for the default MVP.
