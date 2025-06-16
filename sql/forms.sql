-- NOTE PRIOR TO USE --
-- Always query with WHERE deleted_at IS NULL for active records
-- Use UPDATE table SET deleted_at = NOW() instead of DELETE
-- You can restore records by setting deleted_at = NULL

-- FORMS table
CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    body JSON NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES user(id)
);

-- IMAGES table  
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    alt VARCHAR(255),
    filepath VARCHAR(500) NOT NULL,
    maxsize INT,
    form_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);

-- RESPONSES table
CREATE TABLE responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    response_body JSON NOT NULL,
    form_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
);

-- Enable event scheduler (run once, if not already set)
SET GLOBAL event_scheduler = ON;

-- DAILY CLEANUP EVENTS
-- Delete responses older than 2 weeks
CREATE EVENT cleanup_old_responses
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM responses 
    WHERE deleted_at IS NOT NULL 
    AND deleted_at < DATE_SUB(NOW(), INTERVAL 2 WEEK);

-- Delete images older than 2 weeks  
CREATE EVENT cleanup_old_images
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM images 
    WHERE deleted_at IS NOT NULL 
    AND deleted_at < DATE_SUB(NOW(), INTERVAL 2 WEEK);

-- Delete forms older than 2 weeks
CREATE EVENT cleanup_old_forms
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM forms 
    WHERE deleted_at IS NOT NULL 
    AND deleted_at < DATE_SUB(NOW(), INTERVAL 2 WEEK);

-- INDEXING for performance optimization
CREATE INDEX idx_forms_deleted_at ON forms(deleted_at);
CREATE INDEX idx_forms_created_by ON forms(created_by);
CREATE INDEX idx_images_deleted_at ON images(deleted_at);
CREATE INDEX idx_images_form_id ON images(form_id);
CREATE INDEX idx_responses_deleted_at ON responses(deleted_at);
CREATE INDEX idx_responses_form_id ON responses(form_id);
