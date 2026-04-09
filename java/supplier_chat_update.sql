USE medoradb;

CREATE TABLE IF NOT EXISTS supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(15),
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update generic to be compatible with potential string IDs if needed, but here Supplier ID is INT.
-- ChatDAO will need to handle the conversion or we just treat receiver_id as string.

ALTER TABLE chat_messages MODIFY COLUMN sender_type ENUM('patient', 'pharmacist', 'supplier') NOT NULL;

-- Seed Data
INSERT INTO supplier (name, contact_number, email) 
SELECT 'MediPharma Distributors', '0112345678', 'orders@medipharma.com' 
WHERE NOT EXISTS (SELECT * FROM supplier WHERE email = 'orders@medipharma.com');

INSERT INTO supplier (name, contact_number, email) 
SELECT 'City Health Supplies', '0777123456', 'sales@cityhealth.lk' 
WHERE NOT EXISTS (SELECT * FROM supplier WHERE email = 'sales@cityhealth.lk');
