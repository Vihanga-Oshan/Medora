-- Increase password length for guardian table to accommodate hashed passwords
-- SHA-256 produces 64 characters, so 255 is safe for future changes.

ALTER TABLE guardian MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Also increasing contact_number for consistency with other tables (optional but recommended)
ALTER TABLE guardian MODIFY COLUMN contact_number VARCHAR(15) NOT NULL;
