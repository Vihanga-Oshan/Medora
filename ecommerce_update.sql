
-- ---------------------------------------------------------
-- POPULATE CATEGORIES (Run this to fill sidebar with data)
-- ---------------------------------------------------------
-- ---------------------------------------------------------
-- CATEGORY REFACTOR
-- ---------------------------------------------------------
```sql

-- =========================================================
-- MEDORA DATABASE RESET & SEED SCRIPT
-- WARNING: This will DELETE all existing data in e-commerce tables!
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. CLEAR EXISTING DATA
-- We use DELETE to avoid errors if tables don't exist yet (though DELETE only works if table exists)
-- Better approach for a RESET script: Drop tables if they exist and re-create.

DROP TABLE IF EXISTS order_items;
-- Can't drop medicines easily if other things depend on it, but for a full reset:
-- DROP TABLE IF EXISTS medicines; 
-- But user wants to keep structure maybe? No, user said "delete all data".
-- Let's stick to cleaning data.

-- 1. CLEAR EXISTING DATA via DROP (Easiest way to reset)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
-- We need to recreate them later if we drop them. 
-- Since the user wants a full reset, dropping and recreating is cleaner than DELETE.

-- But wait, if we DROP them, we need CREATE statements for them. 
-- The script below has CREATE IF NOT EXISTS. So DROP is perfect for a hard reset.

-- For medicines, we also want to drop it to clear data and schema old columns
DROP TABLE IF EXISTS medicines;

-- For categories, drop it too
DROP TABLE IF EXISTS categories;

SET FOREIGN_KEY_CHECKS = 1;

-- 2. SCHEMA UPDATES (Re-creation)

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Re-create Medicines Table (New Schema directly)
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    category_id INT,
    description TEXT,
    dosage_form VARCHAR(50),
    strength VARCHAR(50),
    quantity_in_stock INT DEFAULT 0,
    manufacturer VARCHAR(100),
    expiry_date DATE,
    price DECIMAL(10,2) DEFAULT 0.00,
    image_path VARCHAR(255),
    added_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Re-create Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_nic VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'PENDING',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_nic) REFERENCES patient(nic)
);

-- Re-create Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

-- SKIP ALTER STATEMENTS since we just created fresh tables with correct columns.
-- We comment them out to avoid errors.
-- ALTER TABLE medicines DROP COLUMN category;
-- ALTER TABLE medicines ADD COLUMN category_id INT;
-- ALTER TABLE medicines ADD CONSTRAINT fk_medicine_category FOREIGN KEY (category_id) REFERENCES categories(id);

-- 3. SEED NEW DATA

-- Insert Categories
INSERT INTO categories (id, name) VALUES 
(1, 'Pain Relief'),
(2, 'Antibiotics'),
(3, 'Cardiovascular'),
(4, 'Supplements'),
(5, 'First Aid'),
(6, 'Skincare'),
(7, 'Respiratory'),
(8, 'Gastrointestinal');

-- Insert Medicines (Linked to Categories)
INSERT INTO medicines (name, generic_name, category_id, description, dosage_form, strength, quantity_in_stock, manufacturer, expiry_date, price, image_path, added_by) VALUES 
('Panadol', 'Paracetamol', 1, 'Effective for mild to moderate pain and fever.', 'Tablet', '500mg', 100, 'GSK', '2027-12-31', 3.50, 'https://www.panadol.com.au/content/dam/cf-consumer-healthcare/panadol/en_au/products/core-range/tablets/Panadol-Tablets-500mg-20pk_3D_Type1.png', 1),
('Amoxil', 'Amoxicillin', 2, 'Broad-spectrum antibiotic for bacterial infections.', 'Capsule', '500mg', 50, 'GSK', '2026-06-30', 12.00, 'https://www.mims.com/resources/drugs/Malaysia/packshot/Amoxil%20cap%20250%20mg60024PPS0.JPG', 1),
('Lipitor', 'Atorvastatin', 3, 'Used to lower bad cholesterol and fats.', 'Tablet', '20mg', 40, 'Pfizer', '2026-05-20', 25.00, 'https://img.medscapestatic.com/pi/features/drugdirectory/oct2020/Pfizer/Lipitor/lipitor-20mg.jpg', 1),
('Vitamin C', 'Ascorbic Acid', 4, 'Immune system support supplement.', 'Chewable', '1000mg', 150, 'NatureMade', '2028-01-01', 15.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRz-XvR-_q_J_q_q_q_q_q_q_q_q_q_q_q_q&s', 1),
('Ventolin', 'Salbutamol', 7, 'Relief for asthma and COPD.', 'Inhaler', '100mcg', 30, 'GSK', '2026-11-15', 18.50, 'https://www.mypharmacy.co.uk/wp-content/uploads/2019/08/ventolin-evohaler-100mcg-200-doses.jpg', 1),
('Gaviscon', 'Sodium Alginate', 8, 'Fast relief for heartburn and indigestion.', 'Liquid', '150ml', 60, 'Reckitt', '2027-04-10', 9.99, 'https://www.chemistwarehouse.co.nz/images/productimages/56983/original_common.jpg', 1),
('Band-Aid', 'Adhesive Bandage', 5, 'Sterile strips for minor cuts.', 'Strips', 'Assorted', 200, 'Johnson & Johnson', '2030-01-01', 5.50, 'https://m.media-amazon.com/images/I/71u+q+q+q+L._AC_SL1500_.jpg', 1),
('Nivea Creme', 'Moisturizer', 6, 'All-purpose moisturizing cream.', 'Cream', '150ml', 80, 'Beiersdorf', '2028-08-01', 6.50, 'https://images-na.ssl-images-amazon.com/images/I/61S3y7%2Bp%2BLL._SX425_.jpg', 1);



