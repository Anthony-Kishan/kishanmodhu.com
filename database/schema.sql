-- ============================================================================
-- kishanmodhu.com — CMS schema
--
-- Run once against an empty database:
--   mysql -u USER -p DBNAME < database/schema.sql
-- Then seed the current site content:
--   php database/seed.php
--
-- Conventions
--   * `sort_order`   — drag-and-drop display order (0 = first)
--   * `is_published` — 1 shows the row on the public site, 0 hides it
-- Any table carrying both automatically gains reordering and a publish toggle
-- in the admin UI (see config/content_types.php).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Admin accounts ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120)  NOT NULL,
    `email`         VARCHAR(160)  NOT NULL,
    `password`      VARCHAR(255)  NOT NULL,
    `role`          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `last_login_at` DATETIME      NULL DEFAULT NULL,
    `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Singleton settings (headings, bios, meta tags) ──────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(120) NOT NULL,
    `setting_value` TEXT         NOT NULL,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Portfolio grid ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `works` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(120) NOT NULL,
    `category`     VARCHAR(120) NOT NULL,
    `tag`          VARCHAR(60)  NOT NULL,
    `image_path`   VARCHAR(255) NOT NULL,
    `image_alt`    VARCHAR(160) NOT NULL,
    `url`          VARCHAR(255) NULL DEFAULT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `works_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Services accordion ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`         VARCHAR(120) NOT NULL,
    `description`   TEXT         NOT NULL,
    `starting_cost` INT UNSIGNED NOT NULL DEFAULT 0,
    `features`      JSON         NOT NULL,
    `image_path`    VARCHAR(255) NOT NULL,
    `sort_order`    INT          NOT NULL DEFAULT 0,
    `is_published`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `services_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Client testimonials ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(120) NOT NULL,
    `body`         TEXT         NOT NULL,
    `country`      VARCHAR(80)  NOT NULL,
    `date_label`   VARCHAR(60)  NOT NULL,
    `avatar_path`  VARCHAR(255) NOT NULL,
    `source_icon`  VARCHAR(255) NOT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `testimonials_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Experience timeline ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `experiences` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company`          VARCHAR(160) NOT NULL,
    `position`         VARCHAR(160) NOT NULL,
    `description`      TEXT         NOT NULL,
    `date_label`       VARCHAR(60)  NOT NULL,
    `date_label_short` VARCHAR(40)  NOT NULL,
    `logo_path`        VARCHAR(255) NOT NULL,
    `sort_order`       INT          NOT NULL DEFAULT 0,
    `is_published`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `experiences_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Favourite stack ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `stacks` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(80)  NOT NULL,
    `category`     VARCHAR(80)  NOT NULL,
    `proficiency`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `description`  TEXT         NOT NULL,
    `logo_path`    VARCHAR(255) NOT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `stacks_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Scrolling logo strip ────────────────────────────────────────────────────
-- Separate from `stacks`: the marquee carries logos (Laravel, Arduino) that
-- have no matching entry in the detailed stack list.
CREATE TABLE IF NOT EXISTS `marquee_logos` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(80)  NOT NULL,
    `logo_path`    VARCHAR(255) NOT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `marquee_logos_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Certificates ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `certificates` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(160) NOT NULL,
    `year`         VARCHAR(20)  NOT NULL,
    `url`          VARCHAR(500) NULL DEFAULT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `certificates_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Social links ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `social_links` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `label`         VARCHAR(60)  NOT NULL,
    `url`           VARCHAR(500) NOT NULL,
    `icon_path`     VARCHAR(255) NOT NULL,
    `show_in_about` TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`    INT          NOT NULL DEFAULT 0,
    `is_published`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `social_links_display_index` (`is_published`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact-form submissions ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name`   VARCHAR(80)  NOT NULL,
    `last_name`    VARCHAR(80)  NOT NULL,
    `email`        VARCHAR(160) NOT NULL,
    `company_type` VARCHAR(60)  NOT NULL,
    `budget`       VARCHAR(80)  NOT NULL,
    `body`         TEXT         NOT NULL,
    `ip_address`   VARCHAR(45)  NOT NULL DEFAULT '',
    `user_agent`   VARCHAR(255) NOT NULL DEFAULT '',
    `is_read`      TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `messages_unread_index` (`is_read`, `created_at`),
    KEY `messages_throttle_index` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Media library ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `media` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename`    VARCHAR(255) NOT NULL,
    `path`        VARCHAR(255) NOT NULL,
    `mime_type`   VARCHAR(100) NOT NULL,
    `size_bytes`  INT UNSIGNED NOT NULL DEFAULT 0,
    `width`       INT UNSIGNED NULL DEFAULT NULL,
    `height`      INT UNSIGNED NULL DEFAULT NULL,
    `uploaded_by` INT UNSIGNED NULL DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `media_path_unique` (`path`),
    CONSTRAINT `media_uploaded_by_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
