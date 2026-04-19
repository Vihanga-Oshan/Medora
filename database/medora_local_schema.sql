-- Medora local schema bootstrap
-- Import this file into MySQL 8+ to create the project schema locally.

CREATE DATABASE IF NOT EXISTS `medoradb`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `medoradb`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `medication_reminder_events`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `guardian_link_requests`;
DROP TABLE IF EXISTS `medicine_stock_movements`;
DROP TABLE IF EXISTS `reminders`;
DROP TABLE IF EXISTS `medication_log`;
DROP TABLE IF EXISTS `medication_schedule`;
DROP TABLE IF EXISTS `schedule_master`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `medicines`;
DROP TABLE IF EXISTS `patient_pharmacy_selection`;
DROP TABLE IF EXISTS `pharmacy_users`;
DROP TABLE IF EXISTS `pharmacist_requests`;
DROP TABLE IF EXISTS `admin_activity_log`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `medicine_suppliers`;
DROP TABLE IF EXISTS `medicine_manufacturers`;
DROP TABLE IF EXISTS `medicine_brands`;
DROP TABLE IF EXISTS `selling_units`;
DROP TABLE IF EXISTS `dosage_forms`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `dosage_categories`;
DROP TABLE IF EXISTS `frequencies`;
DROP TABLE IF EXISTS `meal_timing`;
DROP TABLE IF EXISTS `pharmacies`;
DROP TABLE IF EXISTS `pharmacist`;
DROP TABLE IF EXISTS `patient`;
DROP TABLE IF EXISTS `guardian`;
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `nic` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `contact` VARCHAR(15) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_nic` (`nic`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `guardian` (
  `nic` VARCHAR(20) NOT NULL,
  `g_name` VARCHAR(100) NOT NULL,
  `contact_number` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`nic`),
  UNIQUE KEY `uq_guardian_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `patient` (
  `nic` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `gender` VARCHAR(10) NOT NULL,
  `emergency_contact` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `allergies` TEXT DEFAULT NULL,
  `chronic_issues` TEXT DEFAULT NULL,
  `guardian_nic` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nic`),
  UNIQUE KEY `uq_patient_email` (`email`),
  KEY `idx_patient_guardian` (`guardian_nic`),
  CONSTRAINT `fk_patient_guardian`
    FOREIGN KEY (`guardian_nic`) REFERENCES `guardian` (`nic`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacist` (
  `id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `license_no` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pharmacist_email` (`email`),
  UNIQUE KEY `uq_pharmacist_license` (`license_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(120) NOT NULL,
  `district` VARCHAR(120) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacy_users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pharmacy_id` INT NOT NULL,
  `pharmacist_id` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `role` ENUM('pharmacist', 'pharmacy_admin') NOT NULL DEFAULT 'pharmacist',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pharmacy_users_pharmacy` (`pharmacy_id`),
  KEY `idx_pharmacy_users_pharmacist` (`pharmacist_id`),
  KEY `idx_pharmacy_users_user` (`user_id`),
  CONSTRAINT `fk_pharmacy_users_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pharmacy_users_pharmacist`
    FOREIGN KEY (`pharmacist_id`) REFERENCES `pharmacist` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `patient_pharmacy_selection` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `patient_nic` VARCHAR(20) NOT NULL,
  `pharmacy_id` INT NOT NULL,
  `selected_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_patient_pharmacy_selection_patient` (`patient_nic`),
  KEY `idx_patient_pharmacy_selection_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_patient_pharmacy_selection_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_patient_pharmacy_selection_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `admin_activity_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT DEFAULT NULL,
  `actor_name` VARCHAR(100) NOT NULL,
  `action_text` VARCHAR(255) NOT NULL,
  `tone` ENUM('green', 'blue', 'red', 'purple') NOT NULL DEFAULT 'blue',
  `entity_type` VARCHAR(50) NOT NULL DEFAULT 'system',
  `entity_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_activity_log_admin` (`admin_id`),
  KEY `idx_admin_activity_log_created` (`created_at`),
  CONSTRAINT `fk_admin_activity_log_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacist_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `license_no` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `requested_pharmacy_id` INT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pharmacist_requests_email` (`email`),
  UNIQUE KEY `uq_pharmacist_requests_license` (`license_no`),
  KEY `idx_pharmacist_requests_status` (`status`),
  KEY `idx_pharmacist_requests_pharmacy` (`requested_pharmacy_id`),
  CONSTRAINT `fk_pharmacist_requests_admin`
    FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pharmacist_requests_pharmacy`
    FOREIGN KEY (`requested_pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dosage_categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dosage_categories_label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `frequencies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  `times_of_day` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_frequencies_label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `meal_timing` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meal_timing_label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dosage_forms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dosage_forms_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `selling_units` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selling_units_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicine_brands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicine_brands_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicine_manufacturers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicine_manufacturers_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicine_suppliers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(150) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `lead_time_days` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medicine_suppliers_name` (`name`),
  KEY `idx_medicine_suppliers_active_name` (`is_active`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicines` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `med_name` VARCHAR(150) NOT NULL,
  `generic_name` VARCHAR(100) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `dosage_form` VARCHAR(50) DEFAULT NULL,
  `strength` VARCHAR(50) DEFAULT NULL,
  `quantity_in_stock` INT NOT NULL DEFAULT 0,
  `low_stock_threshold` INT NOT NULL DEFAULT 10,
  `reorder_quantity` INT NOT NULL DEFAULT 25,
  `pricing` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `manufacturer` VARCHAR(100) DEFAULT NULL,
  `supplier_id` INT DEFAULT NULL,
  `batch_number` VARCHAR(100) DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `last_restocked_at` DATETIME DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `selling_unit` VARCHAR(100) DEFAULT NULL,
  `unit_quantity` INT NOT NULL DEFAULT 1,
  `added_by` INT DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medicines_name` (`name`),
  KEY `idx_medicines_category_id` (`category_id`),
  KEY `idx_medicines_supplier_id` (`supplier_id`),
  KEY `idx_medicines_pharmacy_id` (`pharmacy_id`),
  KEY `idx_medicines_pharmacy_stock` (`pharmacy_id`, `quantity_in_stock`),
  KEY `idx_medicines_pharmacy_expiry` (`pharmacy_id`, `expiry_date`),
  CONSTRAINT `fk_medicines_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medicines_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `medicine_suppliers` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medicines_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medicine_stock_movements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `medicine_id` INT NOT NULL,
  `supplier_id` INT DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  `movement_type` ENUM('initial', 'restock', 'dispense', 'adjustment', 'set') NOT NULL,
  `quantity_change` INT NOT NULL,
  `quantity_before` INT NOT NULL DEFAULT 0,
  `quantity_after` INT NOT NULL DEFAULT 0,
  `note` VARCHAR(255) DEFAULT NULL,
  `reference_no` VARCHAR(100) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medicine_stock_movements_medicine_created` (`medicine_id`, `created_at`),
  KEY `idx_medicine_stock_movements_pharmacy_created` (`pharmacy_id`, `created_at`),
  CONSTRAINT `fk_stock_movements_medicine`
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_movements_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `medicine_suppliers` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_movements_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prescriptions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `patient_nic` VARCHAR(20) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(512) NOT NULL,
  `upload_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'SCHEDULED') NOT NULL DEFAULT 'PENDING',
  `wants_medicine_order` TINYINT(1) NOT NULL DEFAULT 0,
  `wants_schedule` TINYINT(1) NOT NULL DEFAULT 1,
  `pharmacy_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prescriptions_patient` (`patient_nic`),
  KEY `idx_prescriptions_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_prescriptions_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_prescriptions_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacy_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pharmacy_id` INT DEFAULT NULL,
  `patient_nic` VARCHAR(20) NOT NULL,
  `prescription_id` INT DEFAULT NULL,
  `source` VARCHAR(30) NOT NULL DEFAULT 'PRESCRIPTION',
  `order_title` VARCHAR(255) NOT NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'PENDING',
  `wants_schedule` TINYINT(1) NOT NULL DEFAULT 0,
  `delivery_method` VARCHAR(20) NOT NULL DEFAULT 'PICKUP',
  `billing_name` VARCHAR(150) NOT NULL DEFAULT '',
  `billing_phone` VARCHAR(50) NOT NULL DEFAULT '',
  `billing_email` VARCHAR(150) NOT NULL DEFAULT '',
  `billing_address` VARCHAR(255) NOT NULL DEFAULT '',
  `billing_city` VARCHAR(120) NOT NULL DEFAULT '',
  `billing_notes` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `fulfillment_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pharmacy_orders_patient` (`patient_nic`, `created_at`),
  KEY `idx_pharmacy_orders_pharmacy` (`pharmacy_id`, `status`, `created_at`),
  KEY `idx_pharmacy_orders_prescription` (`prescription_id`),
  CONSTRAINT `fk_pharmacy_orders_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pharmacy_orders_prescription`
    FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pharmacy_orders_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pharmacy_order_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `medicine_id` INT DEFAULT NULL,
  `medicine_name` VARCHAR(255) NOT NULL DEFAULT '',
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `line_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_medicine` (`medicine_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `pharmacy_orders` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_medicine`
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `schedule_master` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `prescription_id` INT NOT NULL,
  `patient_nic` VARCHAR(20) NOT NULL,
  `pharmacist_id` INT DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_schedule_master_prescription` (`prescription_id`),
  KEY `idx_schedule_master_patient` (`patient_nic`),
  KEY `idx_schedule_master_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_schedule_master_prescription`
    FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_master_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_master_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_master_pharmacist`
    FOREIGN KEY (`pharmacist_id`) REFERENCES `pharmacist` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medication_schedule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `duration_days` INT DEFAULT NULL,
  `instructions` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `medicine_id` INT DEFAULT NULL,
  `dosage_id` INT DEFAULT NULL,
  `frequency_id` INT DEFAULT NULL,
  `meal_timing_id` INT DEFAULT NULL,
  `schedule_master_id` INT DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_medication_schedule_start_date` (`start_date`),
  KEY `idx_medication_schedule_medicine` (`medicine_id`),
  KEY `idx_medication_schedule_dosage` (`dosage_id`),
  KEY `idx_medication_schedule_frequency` (`frequency_id`),
  KEY `idx_medication_schedule_meal_timing` (`meal_timing_id`),
  KEY `idx_medication_schedule_master` (`schedule_master_id`),
  KEY `idx_medication_schedule_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_medication_schedule_medicine`
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_schedule_dosage`
    FOREIGN KEY (`dosage_id`) REFERENCES `dosage_categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_schedule_frequency`
    FOREIGN KEY (`frequency_id`) REFERENCES `frequencies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_schedule_meal_timing`
    FOREIGN KEY (`meal_timing_id`) REFERENCES `meal_timing` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_schedule_master`
    FOREIGN KEY (`schedule_master_id`) REFERENCES `schedule_master` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_schedule_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medication_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `medication_schedule_id` INT NOT NULL,
  `patient_nic` VARCHAR(20) NOT NULL,
  `dose_date` DATE NOT NULL,
  `status` ENUM('TAKEN', 'MISSED', 'PENDING') NOT NULL DEFAULT 'PENDING',
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `time_slot` VARCHAR(45) DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_medication_log_schedule` (`medication_schedule_id`),
  KEY `idx_medication_log_patient_date` (`patient_nic`, `dose_date`),
  KEY `idx_medication_log_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_medication_log_schedule`
    FOREIGN KEY (`medication_schedule_id`) REFERENCES `medication_schedule` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_log_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_log_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reminders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `schedule_id` INT NOT NULL,
  `patient_nic` VARCHAR(20) NOT NULL,
  `reminder_type` ENUM('SMS', 'APP') NOT NULL,
  `message` TEXT NOT NULL,
  `sent_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_reminders_schedule` (`schedule_id`),
  KEY `idx_reminders_patient` (`patient_nic`),
  KEY `idx_reminders_sent_at` (`sent_at`),
  CONSTRAINT `fk_reminders_schedule`
    FOREIGN KEY (`schedule_id`) REFERENCES `medication_schedule` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reminders_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `patient_nic` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'APP',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `pharmacy_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_patient_created` (`patient_nic`, `created_at`),
  KEY `idx_notifications_is_read` (`is_read`),
  KEY `idx_notifications_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_notifications_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chat_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sender_type` VARCHAR(32) NOT NULL,
  `sender_id` VARCHAR(64) NOT NULL,
  `receiver_id` VARCHAR(64) NOT NULL,
  `message_text` TEXT NOT NULL,
  `sent_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `typing` VARCHAR(32) DEFAULT NULL,
  `type` VARCHAR(32) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `pharmacy_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat_messages_sender_receiver` (`sender_id`, `receiver_id`),
  KEY `idx_chat_messages_receiver_sent` (`receiver_id`, `sent_at`),
  KEY `idx_chat_messages_is_read` (`is_read`),
  KEY `idx_chat_messages_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_chat_messages_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `guardian_link_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `guardian_nic` VARCHAR(20) NOT NULL,
  `patient_nic` VARCHAR(20) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  `guardian_seen` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_guardian_link_requests_guardian_status` (`guardian_nic`, `status`, `responded_at`),
  KEY `idx_guardian_link_requests_patient_status` (`patient_nic`, `status`, `created_at`),
  CONSTRAINT `fk_guardian_link_requests_guardian`
    FOREIGN KEY (`guardian_nic`) REFERENCES `guardian` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_guardian_link_requests_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medication_reminder_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `patient_nic` VARCHAR(20) NOT NULL,
  `source_type` VARCHAR(20) NOT NULL,
  `source_schedule_id` INT NOT NULL,
  `dose_date` DATE NOT NULL,
  `time_slot` VARCHAR(20) NOT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('PENDING', 'TAKEN', 'MISSED') NOT NULL DEFAULT 'PENDING',
  `delivered_at` DATETIME DEFAULT NULL,
  `delivered_notification_id` INT DEFAULT NULL,
  `pharmacy_id` INT DEFAULT NULL,
  `taken_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medication_reminder_events_patient_due` (`patient_nic`, `scheduled_at`, `status`),
  KEY `idx_medication_reminder_events_source` (`source_type`, `source_schedule_id`, `dose_date`),
  KEY `idx_medication_reminder_events_notification` (`delivered_notification_id`),
  KEY `idx_medication_reminder_events_pharmacy` (`pharmacy_id`),
  CONSTRAINT `fk_medication_reminder_events_patient`
    FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_reminder_events_notification`
    FOREIGN KEY (`delivered_notification_id`) REFERENCES `notifications` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medication_reminder_events_pharmacy`
    FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT IGNORE INTO `pharmacies`
(`id`, `name`, `address_line1`, `city`, `district`, `latitude`, `longitude`, `is_demo`, `status`)
VALUES
(1, 'Medora Main Pharmacy', 'Default Address', 'Colombo', 'Colombo', 6.92707900, 79.86124400, 0, 'active'),
(2, 'Medora City Care - Colombo Fort', 'No 14, York Street', 'Colombo', 'Colombo', 6.93520000, 79.84280000, 1, 'active'),
(3, 'Medora WellLife - Bambalapitiya', 'Galle Road, Bambalapitiya', 'Colombo', 'Colombo', 6.89160000, 79.85600000, 1, 'active'),
(4, 'Medora Community - Kandy Central', 'Dalada Veediya', 'Kandy', 'Kandy', 7.29360000, 80.64130000, 1, 'active');

INSERT IGNORE INTO `dosage_categories` (`id`, `label`) VALUES
(1, '1 tablet'),
(2, '2 tablets'),
(3, '1 capsule'),
(4, '5 ml'),
(5, '10 ml'),
(6, '1 puff');

INSERT IGNORE INTO `frequencies` (`id`, `label`, `times_of_day`) VALUES
(1, 'Once daily', 'morning'),
(2, 'Twice daily', 'morning,night'),
(3, 'Three times daily', 'morning,day,night'),
(4, 'Every morning', 'morning'),
(5, 'Every afternoon', 'day'),
(6, 'Every night', 'night'),
(7, 'Every 8 hours', 'morning,day,night'),
(8, 'Every 12 hours', 'morning,night'),
(9, 'Weekly', 'morning'),
(10, 'As needed', 'morning');

INSERT IGNORE INTO `meal_timing` (`id`, `label`) VALUES
(1, 'Before breakfast'),
(2, 'After breakfast'),
(3, 'Before lunch'),
(4, 'After lunch'),
(5, 'Before dinner'),
(6, 'After dinner'),
(7, 'With food'),
(8, 'Without food'),
(9, 'Any time'),
(10, 'Before sleep');

INSERT IGNORE INTO `categories` (`id`, `name`, `is_active`) VALUES
(1, 'Pain Relief', 1),
(2, 'Antibiotics', 1),
(3, 'Diabetes Care', 1),
(4, 'Heart Health', 1),
(5, 'Respiratory', 1),
(6, 'Vitamins', 1),
(7, 'Skin Care', 1),
(8, 'Digestive Health', 1);

INSERT IGNORE INTO `dosage_forms` (`id`, `name`, `is_active`) VALUES
(1, 'Tablet', 1),
(2, 'Capsule', 1),
(3, 'Syrup', 1),
(4, 'Suspension', 1),
(5, 'Injection', 1),
(6, 'Cream', 1),
(7, 'Ointment', 1),
(8, 'Drops', 1),
(9, 'Inhaler', 1),
(10, 'Powder', 1);

INSERT IGNORE INTO `selling_units` (`id`, `name`, `is_active`) VALUES
(1, 'Item', 1),
(2, 'Strip', 1),
(3, 'Bottle', 1),
(4, 'Box', 1),
(5, 'Tube', 1),
(6, 'Vial', 1),
(7, 'Sachet', 1),
(8, 'Pack', 1);

INSERT IGNORE INTO `medicine_suppliers`
(`id`, `name`, `contact_person`, `phone`, `email`, `address`, `lead_time_days`, `is_active`)
VALUES
(1, 'Sun Pharma Distributors', 'Nadeesha Silva', '+94 11 555 1001', 'orders@sunpharma.example', 'Colombo 05', 2, 1),
(2, 'HealthLine Medical Supply', 'Kamal Perera', '+94 11 555 1002', 'supply@healthline.example', 'Kandy', 3, 1),
(3, 'CarePlus Wholesale', 'Ishara Fernando', '+94 11 555 1003', 'sales@careplus.example', 'Negombo', 4, 1);
