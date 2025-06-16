CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    category VARCHAR(50), -- e.g., 'form', 'ticketing', 'calendar'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store user types
CREATE TABLE user_types (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT, -- references users.id
    is_active BOOLEAN DEFAULT TRUE
);

-- Junction table for user type permissions
CREATE TABLE user_type_permissions (
    user_type_id INT,
    permission_id INT,
    PRIMARY KEY (user_type_id, permission_id),
    FOREIGN KEY (user_type_id) REFERENCES user_types(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);


-- Insert all available permissions
INSERT INTO permissions (name, description, category) VALUES
('form_create', 'Create forms', 'form'),
('form_view_all', 'View all forms', 'form'),
('form_edit_all', 'Edit/delete all forms', 'form'),
('ticketing_create', 'Create tickets', 'ticketing'),
('ticketing_view_all', 'View all tickets', 'ticketing'),
('ticketing_edit_all', 'Edit/delete all tickets', 'ticketing'),
('calendar_access', 'Access calendar', 'calendar'),
('calendar_edit_others', 'Edit other accounts calendar', 'calendar'),
('calendar_delete_others', 'Delete other accounts calendar', 'calendar'),
('student_db_view', 'View student database', 'student_database'),
('student_db_add', 'Add students', 'student_database'),
('student_db_edit', 'Edit students', 'student_database'),
('student_db_delete', 'Delete students', 'student_database'),
('menfess_view', 'View menfess', 'menfess'),
('menfess_edit', 'Edit menfess', 'menfess'),
('website_settings', 'Edit website settings', 'website'),
('website_posts', 'Add and edit website posts', 'website');

-- Insert existing user types
INSERT INTO user_types (id, name, description) VALUES
(1, 'Basic User', 'Can access Form, Ticketing, Calendar'),
(2, 'Extended User', 'Basic User access + Menfess'),
(3, 'Viewer Admin', 'Extended User access + View databases'),
(4, 'Editor Admin', 'Viewer Admin access + Edit permissions'),
(5, 'Full Admin', 'Editor Admin access + Student database management'),
(999, 'Super Admin', 'Full system access');