-- Pendataan Mahasiswa Database Schema
-- =============================================

-- 1. Reference Tables First (no dependencies)
-- a. University Type Reference Table
CREATE TABLE university_type (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- b. Qualification Level Reference Table
CREATE TABLE qualification_level (
    level_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name VARCHAR(100) NOT NULL UNIQUE,
    level_order INT NOT NULL UNIQUE, -- For sorting (1=Certificate, 2=Diploma, 3=Bachelor's, 4=Master's, 5=PhD)
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- c. Student Status Reference Table
CREATE TABLE student_status (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Postcode table
CREATE TABLE postcode (
    zip_code INT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_city_state (city, state_name)
);

-- 3. University table
CREATE TABLE university (
    university_id INT PRIMARY KEY AUTO_INCREMENT,
    university_name VARCHAR(255) NOT NULL UNIQUE,
    address TEXT,
    type_id INT NOT NULL,
    postcode_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (type_id) REFERENCES university_type(type_id),
    FOREIGN KEY (postcode_id) REFERENCES postcode(zip_code),
    
    -- Indexes
    INDEX idx_university_name (university_name),
    INDEX idx_university_type (type_id),
    INDEX idx_university_active (is_active)
);

-- 4. Student table
CREATE TABLE student (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(255) NOT NULL,
    university_id INT,
    dob DATE,
    email VARCHAR(255) UNIQUE,
    passport VARCHAR(50) UNIQUE,
    phone_number VARCHAR(20) UNIQUE,
    postcode_id INT,
    address TEXT,
    expected_graduate DATE,
    degree VARCHAR(255),
    level_of_qualification_id INT,
    status_id INT DEFAULT 1, -- References student_status table (1=active)
    is_active BOOLEAN DEFAULT TRUE, -- Keep existing for compatibility
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (university_id) REFERENCES university(university_id),
    FOREIGN KEY (postcode_id) REFERENCES postcode(zip_code),
    FOREIGN KEY (level_of_qualification_id) REFERENCES qualification_level(level_id),
    FOREIGN KEY (status_id) REFERENCES student_status(status_id),
    
    -- Indexes
    INDEX idx_student_email (email),
    INDEX idx_student_passport (passport),
    INDEX idx_student_university (university_id),
    INDEX idx_student_status (status_id),
    INDEX idx_student_graduation (expected_graduate),
    INDEX idx_student_active (is_active)
);

-- 5. PPIM table
CREATE TABLE ppim (
    ppim_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    start_year INT NOT NULL,
    end_year INT NULL, -- Track when membership ends
    department VARCHAR(255) NOT NULL,
    position VARCHAR(100), -- NEW: Track position/role
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_year (student_id, start_year), -- Maintain uniqueness
    
    -- Indexes
    INDEX idx_ppim_student (student_id),
    INDEX idx_ppim_year (start_year, end_year),
    INDEX idx_ppim_active (is_active)
);

-- 6. PPI Campus table
CREATE TABLE ppi_campus (
    ppi_campus_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    start_year INT NOT NULL,
    end_year INT NULL, -- Track when membership ends
    university_id INT NOT NULL,
    department VARCHAR(255),
    position VARCHAR(100), -- Track position/role
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
    FOREIGN KEY (university_id) REFERENCES university(university_id),
    UNIQUE KEY unique_student_university_year (student_id, start_year, university_id),
    
    -- Indexes
    INDEX idx_ppi_campus_student (student_id),
    INDEX idx_ppi_campus_university (university_id),
    INDEX idx_ppi_campus_year (start_year, end_year),
    INDEX idx_ppi_campus_active (is_active)
);

-- =============================================
-- INSERT REFERENCE DATA
-- =============================================

-- Insert University Types
INSERT INTO university_type (type_name, description) VALUES
('Public', 'Public universities'),
('Private', 'Private universities'),
('International', 'International branch campuses'),
('Technical', 'Technical and vocational institutions');

-- Insert Qualification Levels
INSERT INTO qualification_level (level_name, level_order, description) VALUES
('Certificate', 1, 'Certificate programs'),
('Diploma', 2, 'Diploma programs'),
('Bachelor', 3, 'Bachelor degree programs'),
('Master', 4, 'Master degree programs'),
('PhD', 5, 'Doctoral programs'),
('Postdoc', 6, 'Post-doctoral research');

-- Insert Student Status Options
INSERT INTO student_status (status_name, description) VALUES
('Active', 'Currently studying'),
('Graduated', 'Completed studies'),
('Transferred', 'Transferred to another institution'),
('Suspended', 'Temporarily suspended'),
('Inactive', 'No longer active');

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- 1. Auto-update student status based on graduation date
CREATE TRIGGER tr_student_status_insert
    BEFORE INSERT ON student
    FOR EACH ROW
BEGIN
    IF NEW.expected_graduate IS NOT NULL THEN
        IF NEW.expected_graduate < CURDATE() THEN
            SET NEW.status_id = 2; -- Graduated
            SET NEW.is_active = FALSE;
        ELSE
            SET NEW.status_id = 1; -- Active
            SET NEW.is_active = TRUE;
        END IF;
    END IF;
END //

CREATE TRIGGER tr_student_status_update
    BEFORE UPDATE ON student
    FOR EACH ROW
BEGIN
    IF NEW.expected_graduate IS NOT NULL THEN
        IF NEW.expected_graduate < CURDATE() THEN
            SET NEW.status_id = 2; -- Graduated
            SET NEW.is_active = FALSE;
        ELSE
            SET NEW.status_id = 1; -- Active
            SET NEW.is_active = TRUE;
        END IF;
    END IF;
END //

-- 2. Validate Indonesian passport format
CREATE TRIGGER tr_validate_passport_insert
    BEFORE INSERT ON student
    FOR EACH ROW
BEGIN
    IF NEW.passport IS NOT NULL AND 
       NEW.passport NOT REGEXP '^[A-Z][0-9]{7}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid Indonesian passport format. Required: 1 uppercase letter + 7 digits (e.g., A1234567)';
    END IF;
END //

CREATE TRIGGER tr_validate_passport_update
    BEFORE UPDATE ON student
    FOR EACH ROW
BEGIN
    IF NEW.passport IS NOT NULL AND 
       NEW.passport NOT REGEXP '^[A-Z][0-9]{7}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid Indonesian passport format. Required: 1 uppercase letter + 7 digits (e.g., A1234567)';
    END IF;
END //

-- 3. Validate email format
CREATE TRIGGER tr_validate_email_insert
    BEFORE INSERT ON student
    FOR EACH ROW
BEGIN
    IF NEW.email IS NOT NULL AND
       NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
END //

CREATE TRIGGER tr_validate_email_update
    BEFORE UPDATE ON student
    FOR EACH ROW
BEGIN
    IF NEW.email IS NOT NULL AND
       NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
END //

-- 4. Ensure PPIM members exist and are eligible
CREATE TRIGGER tr_validate_ppim_member
    BEFORE INSERT ON ppim
    FOR EACH ROW
BEGIN
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
END //

-- 5. Ensure PPI Campus members join at their university
CREATE TRIGGER tr_validate_ppi_campus_member
    BEFORE INSERT ON ppi_campus
    FOR EACH ROW
BEGIN
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
    
    IF student_university_id != NEW.university_id THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Student can only join PPI at their registered university';
    END IF;
    
    IF student_status_id = 2 THEN -- Graduated
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Graduated students cannot join PPI Campus as active members';
    END IF;
END //

-- 6. Auto-update membership status based on end_year
CREATE TRIGGER tr_ppim_status_update
    BEFORE UPDATE ON ppim
    FOR EACH ROW
BEGIN
    IF NEW.end_year IS NOT NULL AND NEW.end_year < YEAR(CURDATE()) THEN
        SET NEW.is_active = FALSE;
    ELSEIF NEW.end_year IS NULL OR NEW.end_year >= YEAR(CURDATE()) THEN
        SET NEW.is_active = TRUE;
    END IF;
END //

CREATE TRIGGER tr_ppi_campus_status_update
    BEFORE UPDATE ON ppi_campus
    FOR EACH ROW
BEGIN
    IF NEW.end_year IS NOT NULL AND NEW.end_year < YEAR(CURDATE()) THEN
        SET NEW.is_active = FALSE;
    ELSEIF NEW.end_year IS NULL OR NEW.end_year >= YEAR(CURDATE()) THEN
        SET NEW.is_active = TRUE;
    END IF;
END //

DELIMITER ;