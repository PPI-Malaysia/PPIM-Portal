-- Content Management System Database Schema
-- Created for PPIM Portal
-- Date: 2025-01-16

-- ============================================================
-- Publications Tables
-- ============================================================

CREATE TABLE IF NOT EXISTS `publications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT,
  `content` LONGTEXT,
  `featured_image_url` VARCHAR(500),
  `featured_image_alt` VARCHAR(255),
  `banner_url` VARCHAR(500),
  `banner_alt` VARCHAR(255),
  `author_id` INT(11) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `published_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reading_time` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_published_at` (`published_at`),
  KEY `idx_author_id` (`author_id`),
  CONSTRAINT `fk_publications_author` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `publication_tags` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `publication_id` INT(11) NOT NULL,
  `tag` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_publication_id` (`publication_id`),
  KEY `idx_tag` (`tag`),
  CONSTRAINT `fk_publication_tags_publication` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `publication_attachments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `publication_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `size` VARCHAR(50),
  `type` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_publication_id` (`publication_id`),
  CONSTRAINT `fk_publication_attachments_publication` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Documents Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(100) NOT NULL,
  `file_url` VARCHAR(500) NOT NULL,
  `file_size` VARCHAR(50),
  `file_type` VARCHAR(50),
  `thumbnail_url` VARCHAR(500),
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `download_count` INT(11) DEFAULT 0,
  `metadata` JSON,
  `uploaded_by` INT(11),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_documents_user` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Team Members Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `image_url` VARCHAR(500),
  `bio` TEXT,
  `department` VARCHAR(100),
  `email` VARCHAR(255),
  `phone` VARCHAR(50),
  `social_links` JSON,
  `order_position` INT(11) DEFAULT 0,
  `joined_at` DATE,
  `achievements` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_department` (`department`),
  KEY `idx_order_position` (`order_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Campuses Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `campuses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `short_name` VARCHAR(50),
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `country` VARCHAR(100) DEFAULT 'Malaysia',
  `contact_email` VARCHAR(255),
  `contact_phone` VARCHAR(50),
  `description` TEXT,
  `logo_url` VARCHAR(500),
  `cover_image_url` VARCHAR(500),
  `website` VARCHAR(500),
  `address` JSON,
  `social_links` JSON,
  `student_count` INT(11) DEFAULT 0,
  `established_year` INT(4),
  `programs` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_city` (`city`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Permissions for Content Management
-- ============================================================

-- Ensure unique permission names for safe upserts
ALTER TABLE `permissions` ADD UNIQUE KEY `uq_permissions_name` (`name`);

-- Insert permissions for content management
INSERT INTO `permissions` (`id`, `name`, `description`, `category`) VALUES
(NULL, 'content_view', 'View content management pages', 'Content Management'),
(NULL, 'content_create', 'Create new content', 'Content Management'),
(NULL, 'content_edit', 'Edit existing content', 'Content Management'),
(NULL, 'content_delete', 'Delete content', 'Content Management')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================
-- Sample Data (Optional)
-- ============================================================

-- Insert sample publication
INSERT INTO `publications` (`title`, `slug`, `excerpt`, `content`, `category`, `author_id`, `published_at`) VALUES
('Welcome to PPIM', 'welcome-to-ppim', 'This is a sample publication to get you started.', '<h2>Welcome</h2><p>This is a sample publication content.</p>', 'News', 1, NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Insert sample document
INSERT INTO `documents` (`title`, `description`, `category`, `file_url`, `file_size`, `file_type`, `uploaded_by`) VALUES
('Sample Document', 'This is a sample document', 'Organisasi', '/assets/uploads/documents/sample.pdf', '1.2 MB', 'pdf', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

