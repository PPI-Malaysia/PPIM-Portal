-- ============================================================
-- Team Management System Database Schema
-- Created for PPIM Portal
-- Date: 2024-12-13
-- ============================================================

-- ============================================================
-- Team Periods Table
-- Academic periods/terms (e.g., 2024/2025)
-- ============================================================

CREATE TABLE IF NOT EXISTS `team_periods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'e.g., 2024/2025',
  `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., 24-25',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `theme` VARCHAR(255) DEFAULT NULL COMMENT 'Period theme/motto',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 0 COMMENT 'Only one period can be active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Departments Table
-- Biro and Departemen definitions
-- ============================================================

CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Full department name',
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `short_name` VARCHAR(50) DEFAULT NULL COMMENT 'e.g., Kominfo',
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL COMMENT 'Bootstrap icon class e.g., bi-megaphone-fill',
  `color` VARCHAR(20) DEFAULT NULL COMMENT 'Accent color hex e.g., #dc3545',
  `is_biro` TINYINT(1) DEFAULT 1 COMMENT '1=Biro, 0=Departemen',
  `display_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_is_biro` (`is_biro`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Team Members Table (Updated)
-- All team members including core team and department staff
-- ============================================================

-- Note: If you have an existing team_members table, back it up first:
-- CREATE TABLE team_members_backup AS SELECT * FROM team_members;

-- Create the new team_members table with enhanced structure
CREATE TABLE IF NOT EXISTS `team_members_v2` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `period_id` INT(11) NOT NULL COMMENT 'Which period this member belongs to',
  `department_id` INT(11) DEFAULT NULL COMMENT 'NULL for core team members',
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) DEFAULT NULL,
  `position` VARCHAR(255) NOT NULL COMMENT 'Full position title',
  `position_level` ENUM('ketua_umum', 'wakil_ketua', 'sekretaris', 'bendahara', 'kepala_biro', 'wakil_kepala_biro', 'kepala_dept', 'wakil_kepala_dept', 'staff') NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `instagram` VARCHAR(100) DEFAULT NULL,
  `university` VARCHAR(255) DEFAULT NULL,
  `major` VARCHAR(255) DEFAULT NULL,
  `display_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_period_id` (`period_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_position_level` (`position_level`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_team_members_v2_period` FOREIGN KEY (`period_id`) REFERENCES `team_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_team_members_v2_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Department Period Info Table
-- Period-specific content for departments (vision, mission, programs)
-- ============================================================

CREATE TABLE IF NOT EXISTS `department_period_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `department_id` INT(11) NOT NULL,
  `period_id` INT(11) NOT NULL,
  `vision` TEXT DEFAULT NULL,
  `mission` TEXT DEFAULT NULL,
  `programs` JSON DEFAULT NULL COMMENT 'Array of program names',
  `achievements` JSON DEFAULT NULL COMMENT 'Array of achievements',
  `gallery` JSON DEFAULT NULL COMMENT 'Array of image URLs',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_period` (`department_id`, `period_id`),
  KEY `idx_period_id` (`period_id`),
  CONSTRAINT `fk_dept_period_info_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dept_period_info_period` FOREIGN KEY (`period_id`) REFERENCES `team_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Migration: Copy existing data to new structure (if applicable)
-- ============================================================

-- If you have existing data in team_members table, run this migration:
-- 1. First create a default period if needed
-- INSERT INTO team_periods (name, slug, start_date, end_date, is_active) 
-- VALUES ('2024/2025', '24-25', '2024-10-01', '2025-09-30', 1);

-- 2. Then migrate the data:
-- INSERT INTO team_members_v2 (period_id, name, position, position_level, image_url, bio, email, phone, display_order)
-- SELECT 1, name, position, 'staff', image_url, bio, email, phone, order_position
-- FROM team_members;

-- After verification, the old table can remain as backup or be dropped if not needed

-- ============================================================
-- Sample Data
-- ============================================================

-- Insert sample period
INSERT INTO `team_periods` (`name`, `slug`, `start_date`, `end_date`, `theme`, `is_active`) VALUES
('2024/2025', '24-25', '2024-10-01', '2025-09-30', 'Bersatu Membangun Negeri', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert sample departments
INSERT INTO `departments` (`name`, `slug`, `short_name`, `icon`, `color`, `is_biro`, `display_order`) VALUES
('Biro Komunikasi dan Informasi', 'biro-kominfo', 'Kominfo', 'bi-megaphone-fill', '#dc3545', 1, 1),
('Biro Kesekretariatan', 'biro-kestari', 'Kestari', 'bi-file-earmark-text-fill', '#0d6efd', 1, 2),
('Biro Hubungan Luar', 'biro-hublu', 'Hublu', 'bi-globe', '#198754', 1, 3),
('Departemen Akademik', 'dept-akademik', 'Akademik', 'bi-mortarboard-fill', '#6f42c1', 0, 4),
('Departemen Olahraga', 'dept-olahraga', 'Olahraga', 'bi-trophy-fill', '#fd7e14', 0, 5),
('Departemen Seni dan Budaya', 'dept-senbud', 'Senbud', 'bi-palette-fill', '#d63384', 0, 6)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
