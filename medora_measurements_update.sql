-- Migration: Add selling_unit and unit_quantity to medicines table
ALTER TABLE medicines ADD COLUMN selling_unit VARCHAR(50) DEFAULT 'Item';
ALTER TABLE medicines ADD COLUMN unit_quantity INT DEFAULT 1;

-- Update Panadol as an example if it exists
UPDATE medicines SET selling_unit = 'Strip', unit_quantity = 10 WHERE name = 'Panadol';
