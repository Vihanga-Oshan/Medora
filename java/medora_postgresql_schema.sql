-- Medora Database Schema (PostgreSQL/Neon Compatible)

-- Create a helper function for ON UPDATE CURRENT_TIMESTAMP behavior
CREATE OR REPLACE FUNCTION update_timestamp_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = CURRENT_TIMESTAMP;
   RETURN NEW;
END;
$$ language 'plpgsql';

-- Table structure for table admins
DROP TABLE IF EXISTS admins CASCADE;
CREATE TABLE admins (
  id SERIAL PRIMARY KEY,
  name varchar(100) NOT NULL,
  nic varchar(20) NOT NULL UNIQUE,
  email varchar(100) NOT NULL UNIQUE,
  contact varchar(15) DEFAULT NULL,
  password varchar(255) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table structure for table dosage_categories
DROP TABLE IF EXISTS dosage_categories CASCADE;
CREATE TABLE dosage_categories (
  id SERIAL PRIMARY KEY,
  label varchar(50) NOT NULL
);

-- Table structure for table frequencies
DROP TABLE IF EXISTS frequencies CASCADE;
CREATE TABLE frequencies (
  id SERIAL PRIMARY KEY,
  label varchar(50) NOT NULL,
  times_of_day varchar(50) NOT NULL
);

-- Table structure for table guardian
DROP TABLE IF EXISTS guardian CASCADE;
CREATE TABLE guardian (
  nic varchar(45) PRIMARY KEY,
  g_name varchar(100) NOT NULL,
  contact_number varchar(10) NOT NULL,
  email varchar(100) DEFAULT NULL,
  password varchar(255) NOT NULL
);

-- Table structure for table meal_timing
DROP TABLE IF EXISTS meal_timing CASCADE;
CREATE TABLE meal_timing (
  id SERIAL PRIMARY KEY,
  label varchar(50) NOT NULL
);

-- Table structure for table medicines
DROP TABLE IF EXISTS medicines CASCADE;
CREATE TABLE medicines (
  id SERIAL PRIMARY KEY,
  name varchar(100) NOT NULL,
  generic_name varchar(100) DEFAULT NULL,
  category varchar(50) DEFAULT NULL,
  description text,
  dosage_form varchar(50) DEFAULT NULL,
  strength varchar(50) DEFAULT NULL,
  quantity_in_stock int DEFAULT 0,
  manufacturer varchar(100) DEFAULT NULL,
  expiry_date date DEFAULT NULL,
  added_by int DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table structure for table patient
DROP TABLE IF EXISTS patient CASCADE;
CREATE TABLE patient (
  nic varchar(20) PRIMARY KEY,
  name varchar(100) NOT NULL,
  gender varchar(10) NOT NULL,
  emergency_contact varchar(20) DEFAULT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  allergies text,
  chronic_issues text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  guardian_nic varchar(20) DEFAULT NULL
);

-- Table structure for table pharmacist
DROP TABLE IF EXISTS pharmacist CASCADE;
CREATE TABLE pharmacist (
  id int PRIMARY KEY,
  name varchar(100) NOT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Table structure for table prescriptions
DROP TABLE IF EXISTS prescriptions CASCADE;
CREATE TABLE prescriptions (
  id SERIAL PRIMARY KEY,
  patient_nic varchar(50) NOT NULL REFERENCES patient(nic),
  file_name varchar(255) NOT NULL,
  file_path varchar(512) NOT NULL,
  upload_date timestamp DEFAULT CURRENT_TIMESTAMP,
  status varchar(20) DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'APPROVED', 'REJECTED', 'SCHEDULED'))
);

-- Table structure for table schedule_master
DROP TABLE IF EXISTS schedule_master CASCADE;
CREATE TABLE schedule_master (
  id SERIAL PRIMARY KEY,
  prescription_id int NOT NULL REFERENCES prescriptions(id) ON DELETE CASCADE,
  patient_nic varchar(20) NOT NULL REFERENCES patient(nic) ON DELETE CASCADE,
  pharmacist_id int DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Trigger for update_at in schedule_master
CREATE TRIGGER update_schedule_master_modtime
BEFORE UPDATE ON schedule_master
FOR EACH ROW
EXECUTE PROCEDURE update_timestamp_column();

-- Table structure for table medication_schedule
DROP TABLE IF EXISTS medication_schedule CASCADE;
CREATE TABLE medication_schedule (
  id SERIAL PRIMARY KEY,
  start_date date NOT NULL,
  end_date date DEFAULT NULL,
  duration_days int DEFAULT NULL,
  instructions text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  medicine_id int DEFAULT NULL,
  dosage_id int DEFAULT NULL,
  frequency_id int DEFAULT NULL,
  meal_timing_id int DEFAULT NULL REFERENCES meal_timing(id),
  schedule_master_id int DEFAULT NULL REFERENCES schedule_master(id)
);

-- Table structure for table medication_log
DROP TABLE IF EXISTS medication_log CASCADE;
CREATE TABLE medication_log (
  id SERIAL PRIMARY KEY,
  medication_schedule_id int NOT NULL REFERENCES medication_schedule(id) ON DELETE CASCADE,
  patient_nic varchar(20) NOT NULL,
  dose_date date NOT NULL,
  status varchar(20) DEFAULT 'PENDING' CHECK (status IN ('TAKEN', 'MISSED', 'PENDING')),
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP,
  time_slot varchar(45) DEFAULT NULL
);

-- Table structure for table reminders
DROP TABLE IF EXISTS reminders CASCADE;
CREATE TABLE reminders (
  id SERIAL PRIMARY KEY,
  schedule_id int NOT NULL REFERENCES medication_schedule(id) ON DELETE CASCADE,
  patient_nic varchar(50) NOT NULL REFERENCES patient(nic) ON DELETE CASCADE,
  reminder_type varchar(10) NOT NULL CHECK (reminder_type IN ('SMS', 'APP')),
  message text NOT NULL,
  sent_at timestamp DEFAULT CURRENT_TIMESTAMP,
  delivered boolean DEFAULT false
);

-- Seed Data (Optional - common ones)
INSERT INTO dosage_categories (label) VALUES ('1 tablet'), ('2 tablets'), ('5 ml'), ('10 ml'), ('500 mg'), ('6 capsule');
INSERT INTO frequencies (label, times_of_day) VALUES ('Morning', 'morning'), ('Daytime', 'day'), ('Night', 'night'), ('Morning & Night', 'morning,night'), ('Day & Night', 'day,night'), ('Morning & Day', 'morning,day'), ('Morning & Day & Night', 'morning,day,night');
INSERT INTO meal_timing (label) VALUES ('Before Meal'), ('After Meal'), ('With Meal'), ('Empty Stomach');
