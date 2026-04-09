-- Multi-pharmacy migration for Medora
CREATE TABLE IF NOT EXISTS pharmacies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address_line1 VARCHAR(255) NOT NULL,
  address_line2 VARCHAR(255) NULL,
  city VARCHAR(120) NOT NULL,
  district VARCHAR(120) NULL,
  postal_code VARCHAR(20) NULL,
  latitude DECIMAL(10,8) NOT NULL,
  longitude DECIMAL(11,8) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  is_demo TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pharmacy_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pharmacy_id INT NOT NULL,
  pharmacist_id INT NULL,
  user_id INT NULL,
  role ENUM('pharmacist','pharmacy_admin') NOT NULL DEFAULT 'pharmacist',
  is_primary TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pharmacy (pharmacy_id),
  INDEX idx_pharmacist (pharmacist_id),
  INDEX idx_user (user_id)
);

CREATE TABLE IF NOT EXISTS patient_pharmacy_selection (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_nic VARCHAR(32) NOT NULL,
  pharmacy_id INT NOT NULL,
  selected_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_patient (patient_nic),
  INDEX idx_pharmacy (pharmacy_id)
);

ALTER TABLE medicines ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE prescriptions ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE medication_schedules ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE medication_schedule ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE schedule_master ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE medication_log ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS pharmacy_id INT NULL;

INSERT INTO pharmacies(name,address_line1,city,district,latitude,longitude,status)
SELECT 'Medora Main Pharmacy','Default Address','Colombo','Colombo',6.927079,79.861244,'active'
WHERE NOT EXISTS (SELECT 1 FROM pharmacies);

INSERT INTO pharmacies(name,address_line1,city,district,latitude,longitude,is_demo,status)
SELECT 'Medora City Care - Colombo Fort','No 14, York Street','Colombo','Colombo',6.9352,79.8428,1,'active'
WHERE NOT EXISTS (SELECT 1 FROM pharmacies WHERE name='Medora City Care - Colombo Fort');

INSERT INTO pharmacies(name,address_line1,city,district,latitude,longitude,is_demo,status)
SELECT 'Medora WellLife - Bambalapitiya','Galle Road, Bambalapitiya','Colombo','Colombo',6.8916,79.8560,1,'active'
WHERE NOT EXISTS (SELECT 1 FROM pharmacies WHERE name='Medora WellLife - Bambalapitiya');

INSERT INTO pharmacies(name,address_line1,city,district,latitude,longitude,is_demo,status)
SELECT 'Medora Community - Kandy Central','Dalada Veediya','Kandy','Kandy',7.2936,80.6413,1,'active'
WHERE NOT EXISTS (SELECT 1 FROM pharmacies WHERE name='Medora Community - Kandy Central');
