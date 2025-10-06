-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 06, 2025 at 12:47 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ppimportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `class_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postcode`
--

CREATE TABLE `postcode` (
  `zip_code` varchar(5) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppim`
--

CREATE TABLE `ppim` (
  `ppim_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_year` int(11) DEFAULT NULL,
  `department` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ppim`
--
DELIMITER $$
CREATE TRIGGER `tr_ppim_status_update` BEFORE UPDATE ON `ppim` FOR EACH ROW BEGIN
    IF NEW.end_year IS NOT NULL AND NEW.end_year < YEAR(CURDATE()) THEN
        SET NEW.is_active = FALSE;
    ELSEIF NEW.end_year IS NULL OR NEW.end_year >= YEAR(CURDATE()) THEN
        SET NEW.is_active = TRUE;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validate_ppim_member` BEFORE INSERT ON `ppim` FOR EACH ROW BEGIN
    DECLARE student_count INT DEFAULT 0;
    DECLARE student_status_id INT DEFAULT 0;
    
    SELECT COUNT(*), status_id INTO student_count, student_status_id
    FROM student 
    WHERE student_id = NEW.student_id;
    
    IF student_count = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Student must be registered before joining PPIM';
    END IF;
    
    -- Only allow active students
    IF student_status_id = 2 THEN -- Graduated
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Graduated students cannot join PPIM as active members';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ppi_campus`
--

CREATE TABLE `ppi_campus` (
  `ppi_campus_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_year` int(11) DEFAULT NULL,
  `university_id` int(11) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `ppi_campus`
--
DELIMITER $$
CREATE TRIGGER `tr_validate_ppi_campus_member` BEFORE INSERT ON `ppi_campus` FOR EACH ROW BEGIN
    DECLARE student_count INT DEFAULT 0;
    DECLARE student_university_id INT DEFAULT 0;
    DECLARE student_status_id INT DEFAULT 0;
    
    SELECT COUNT(*), university_id, status_id 
    INTO student_count, student_university_id, student_status_id
    FROM student 
    WHERE student_id = NEW.student_id;
    
    IF student_count = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Student must be registered before joining PPI Campus';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `qualification_level`
--

CREATE TABLE `qualification_level` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(100) NOT NULL,
  `level_order` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `passport` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `postcode_id` varchar(5) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `expected_graduate` date DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `level_of_qualification_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `student`
--
DELIMITER $$
CREATE TRIGGER `tr_student_status_insert` BEFORE INSERT ON `student` FOR EACH ROW BEGIN
    IF NEW.expected_graduate IS NOT NULL THEN
        IF NEW.expected_graduate < CURDATE() THEN
            SET NEW.status_id = 2; -- Graduated
            SET NEW.is_active = FALSE;
        ELSE
            SET NEW.status_id = 1; -- Active
            SET NEW.is_active = TRUE;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_student_status_update` BEFORE UPDATE ON `student` FOR EACH ROW BEGIN
    IF NEW.expected_graduate IS NOT NULL THEN
        IF NEW.expected_graduate < CURDATE() THEN
            SET NEW.status_id = 2; -- Graduated
            SET NEW.is_active = FALSE;
        ELSE
            SET NEW.status_id = 1; -- Active
            SET NEW.is_active = TRUE;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validate_email_insert` BEFORE INSERT ON `student` FOR EACH ROW BEGIN
    IF NEW.email IS NOT NULL AND
       NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validate_email_update` BEFORE UPDATE ON `student` FOR EACH ROW BEGIN
    IF NEW.email IS NOT NULL AND
       NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validate_passport_insert` BEFORE INSERT ON `student` FOR EACH ROW BEGIN
    IF NEW.passport IS NOT NULL AND 
       NEW.passport NOT REGEXP '^[A-Z][0-9]{7}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid Indonesian passport format. Required: 1 uppercase letter + 7 digits (e.g., A1234567)';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_validate_passport_update` BEFORE UPDATE ON `student` FOR EACH ROW BEGIN
    IF NEW.passport IS NOT NULL AND 
       NEW.passport NOT REGEXP '^[A-Z][0-9]{7}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid Indonesian passport format. Required: 1 uppercase letter + 7 digits (e.g., A1234567)';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_status`
--

CREATE TABLE `student_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university`
--

CREATE TABLE `university` (
  `university_id` int(11) NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_num` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `postcode_id` varchar(5) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `user_linked` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_type`
--

CREATE TABLE `university_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_user`
--

CREATE TABLE `university_user` (
  `user_id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_type_permissions`
--

CREATE TABLE `user_type_permissions` (
  `user_type_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `postcode`
--
ALTER TABLE `postcode`
  ADD PRIMARY KEY (`zip_code`),
  ADD KEY `idx_city_state` (`city`,`state_name`);

--
-- Indexes for table `ppim`
--
ALTER TABLE `ppim`
  ADD PRIMARY KEY (`ppim_id`),
  ADD UNIQUE KEY `unique_student_year` (`student_id`,`start_year`),
  ADD KEY `idx_ppim_student` (`student_id`),
  ADD KEY `idx_ppim_year` (`start_year`,`end_year`),
  ADD KEY `idx_ppim_active` (`is_active`);

--
-- Indexes for table `ppi_campus`
--
ALTER TABLE `ppi_campus`
  ADD PRIMARY KEY (`ppi_campus_id`),
  ADD UNIQUE KEY `unique_student_university_year` (`student_id`,`start_year`,`university_id`),
  ADD KEY `idx_ppi_campus_student` (`student_id`),
  ADD KEY `idx_ppi_campus_university` (`university_id`),
  ADD KEY `idx_ppi_campus_year` (`start_year`,`end_year`),
  ADD KEY `idx_ppi_campus_active` (`is_active`);

--
-- Indexes for table `qualification_level`
--
ALTER TABLE `qualification_level`
  ADD PRIMARY KEY (`level_id`),
  ADD UNIQUE KEY `level_name` (`level_name`),
  ADD UNIQUE KEY `level_order` (`level_order`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `passport` (`passport`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `level_of_qualification_id` (`level_of_qualification_id`),
  ADD KEY `idx_student_email` (`email`),
  ADD KEY `idx_student_passport` (`passport`),
  ADD KEY `idx_student_university` (`university_id`),
  ADD KEY `idx_student_status` (`status_id`),
  ADD KEY `idx_student_graduation` (`expected_graduate`),
  ADD KEY `idx_student_active` (`is_active`),
  ADD KEY `student_ibfk_2` (`postcode_id`);

--
-- Indexes for table `student_status`
--
ALTER TABLE `student_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `university`
--
ALTER TABLE `university`
  ADD PRIMARY KEY (`university_id`),
  ADD UNIQUE KEY `university_name` (`university_name`),
  ADD KEY `idx_university_name` (`university_name`),
  ADD KEY `idx_university_type` (`type_id`),
  ADD KEY `idx_university_active` (`is_active`),
  ADD KEY `university_ibfk_2` (`postcode_id`);

--
-- Indexes for table `university_type`
--
ALTER TABLE `university_type`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `university_user`
--
ALTER TABLE `university_user`
  ADD PRIMARY KEY (`user_id`,`university_id`),
  ADD KEY `university_user_ibfk_2` (`university_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_type_permissions`
--
ALTER TABLE `user_type_permissions`
  ADD PRIMARY KEY (`user_type_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppim`
--
ALTER TABLE `ppim`
  MODIFY `ppim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppi_campus`
--
ALTER TABLE `ppi_campus`
  MODIFY `ppi_campus_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualification_level`
--
ALTER TABLE `qualification_level`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_status`
--
ALTER TABLE `student_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `university`
--
ALTER TABLE `university`
  MODIFY `university_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `university_type`
--
ALTER TABLE `university_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ppim`
--
ALTER TABLE `ppim`
  ADD CONSTRAINT `ppim_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `ppi_campus`
--
ALTER TABLE `ppi_campus`
  ADD CONSTRAINT `ppi_campus_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ppi_campus_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `university` (`university_id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`university_id`) REFERENCES `university` (`university_id`),
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`postcode_id`) REFERENCES `postcode` (`zip_code`),
  ADD CONSTRAINT `student_ibfk_3` FOREIGN KEY (`level_of_qualification_id`) REFERENCES `qualification_level` (`level_id`),
  ADD CONSTRAINT `student_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `student_status` (`status_id`);

--
-- Constraints for table `university`
--
ALTER TABLE `university`
  ADD CONSTRAINT `university_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `university_type` (`type_id`),
  ADD CONSTRAINT `university_ibfk_2` FOREIGN KEY (`postcode_id`) REFERENCES `postcode` (`zip_code`);

--
-- Constraints for table `university_user`
--
ALTER TABLE `university_user`
  ADD CONSTRAINT `university_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `university_user_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `university` (`university_id`);

--
-- Constraints for table `user_type_permissions`
--
ALTER TABLE `user_type_permissions`
  ADD CONSTRAINT `user_type_permissions_ibfk_1` FOREIGN KEY (`user_type_id`) REFERENCES `user_types` (`id`),
  ADD CONSTRAINT `user_type_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
