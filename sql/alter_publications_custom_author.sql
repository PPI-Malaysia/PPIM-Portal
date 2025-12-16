-- Add custom author_name field to publications table
-- This allows publications to have custom author names (e.g., external contributors)
-- without requiring them to be registered users

ALTER TABLE publications 
ADD COLUMN author_name VARCHAR(255) NULL AFTER author_id,
ADD COLUMN author_affiliation VARCHAR(500) NULL AFTER author_name;

-- Update existing records to copy name from user table
UPDATE publications p
LEFT JOIN user u ON p.author_id = u.id
SET p.author_name = u.name
WHERE p.author_name IS NULL AND u.name IS NOT NULL;

-- For publications with no user match, set a default
UPDATE publications 
SET author_name = 'Unknown Author'
WHERE author_name IS NULL OR author_name = '';

-- Add comment
ALTER TABLE publications 
MODIFY COLUMN author_name VARCHAR(255) NOT NULL DEFAULT 'Unknown Author' COMMENT 'Custom author name (can be external author)',
MODIFY COLUMN author_affiliation VARCHAR(500) NULL COMMENT 'Author institution/affiliation (optional)';
