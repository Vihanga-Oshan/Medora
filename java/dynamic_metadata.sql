-- Create tables for dynamic metadata
CREATE TABLE IF NOT EXISTS dosage_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS selling_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Seed Dosage Forms
INSERT IGNORE INTO dosage_forms (name) VALUES 
('Tablet'), 
('Capsule'), 
('Syrup'), 
('Suspension'), 
('Injection'), 
('Cream'), 
('Ointment'), 
('Gel'), 
('Inhaler'), 
('Drops');

-- Seed Selling Units
INSERT IGNORE INTO selling_units (name) VALUES 
('Strip'), 
('Bottle'), 
('Box'), 
('Tube'), 
('Vial'), 
('Ampoule'), 
('Sachet'), 
('Inhaler');
