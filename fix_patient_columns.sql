-- Add missing columns to patient table
ALTER TABLE patient ADD COLUMN address TEXT AFTER chronic_issues;
ALTER TABLE patient ADD COLUMN phone VARCHAR(20) AFTER name;

-- Update existing phone data from emergency_contact if phone is empty
UPDATE patient SET phone = emergency_contact WHERE phone IS NULL OR phone = '';
