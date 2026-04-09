-- Fix missing columns in patient table that are required by the code
-- Run these one by one if your MySQL version doesn't support the combined syntax

ALTER TABLE patient ADD COLUMN phone VARCHAR(20) AFTER name;
ALTER TABLE patient ADD COLUMN address TEXT AFTER chronic_issues;
ALTER TABLE patient ADD COLUMN link_status VARCHAR(20) DEFAULT 'UNVERIFIED' AFTER guardian_nic;

-- Update existing phone data from emergency_contact if phone is empty
UPDATE patient SET phone = emergency_contact WHERE phone IS NULL OR phone = '';
