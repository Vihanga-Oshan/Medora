-- ==========================================
-- MEDORA DATABASE FINAL UPDATE SCRIPT
-- Purpose: Add missing constraints, fix data types, and optimize performance.
-- ==========================================

-- 1. FIX DATA INCONSISTENCIES
-- Align notification patient_nic length with the patient table
-- (Note: We must drop and re-add the FK to change the column type)
ALTER TABLE notifications DROP FOREIGN KEY notifications_ibfk_1;
ALTER TABLE notifications MODIFY patient_nic VARCHAR(20);
ALTER TABLE notifications 
ADD CONSTRAINT notifications_ibfk_1 
FOREIGN KEY (patient_nic) REFERENCES patient(nic) ON DELETE CASCADE;

-- 2. ADD MISSING DATA INTEGRITY CONSTRAINTS (FOREIGN KEYS)
-- Link Medicines to the Pharmacist who added them
-- First, clean up any invalid added_by IDs that don't exist in the pharmacist table
UPDATE medicines SET added_by = NULL WHERE added_by NOT IN (SELECT id FROM pharmacist);

ALTER TABLE medicines 
ADD CONSTRAINT fk_medicine_pharmacist 
FOREIGN KEY (added_by) REFERENCES pharmacist(id) ON DELETE SET NULL;

-- Link Patients to their Guardians
-- First, ensure empty strings in guardian_nic are NULL to prevent FK errors
UPDATE patient SET guardian_nic = NULL WHERE guardian_nic = '';
ALTER TABLE patient 
ADD CONSTRAINT fk_patient_guardian 
FOREIGN KEY (guardian_nic) REFERENCES guardian(nic) ON DELETE SET NULL;

-- 3. PERFORMANCE OPTIMIZATIONS (INDEXES)
-- Speeds up searching for medicines by brand or generic name
CREATE INDEX idx_medicine_name ON medicines(name);
CREATE INDEX idx_medicine_generic_name ON medicines(generic_name);

-- Speeds up inventory management and shop filtering
CREATE INDEX idx_medicine_expiry ON medicines(expiry_date);
CREATE INDEX idx_medicine_category ON medicines(category_id);

-- Speeds up order tracking for patients and pharmacists
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_order_created_at ON orders(created_at);

-- 4. ENSURE DYNAMIC METADATA TABLES ARE PRESENT (Safety Check)
CREATE TABLE IF NOT EXISTS dosage_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS selling_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Note: Current Seed Data is already in your dump, but these ensure safety for future resets.
-- INSERT IGNORE INTO dosage_forms (name) VALUES ('Tablet'), ('Capsule'), ('Syrup'), ('Suspension'), ('Injection'), ('Cream'), ('Ointment'), ('Gel'), ('Inhaler'), ('Drops');
-- INSERT IGNORE INTO selling_units (name) VALUES ('Strip'), ('Bottle'), ('Box'), ('Tube'), ('Vial'), ('Ampoule'), ('Sachet'), ('Inhaler');

-- ==========================================
-- SCRIPT COMPLETE
-- ==========================================
