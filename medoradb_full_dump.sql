-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: medoradb
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nic` (`nic`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','200112500353','admin@gmail.com','0704435388','8bb0cf6eb9b17d0f7d22b456f121257dc1254e1f01665370476383ea776df414','2025-10-21 13:52:24'),(2,'nemitha','20030711287','nemitha@gmail.com','0704435288','1234567','2025-10-22 11:46:05'),(4,'admin2','200112500355','admin2@gmail.com','0704435388','65f2abac987d02eaaf120019508e3a9d3c6aeee7947b8352a891a544db2d96d0','2025-10-23 05:34:22');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dosage_categories`
--

DROP TABLE IF EXISTS `dosage_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosage_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dosage_categories`
--

LOCK TABLES `dosage_categories` WRITE;
/*!40000 ALTER TABLE `dosage_categories` DISABLE KEYS */;
INSERT INTO `dosage_categories` VALUES (1,'1 tablet'),(2,'2 tablets'),(3,'5 ml'),(4,'10 ml'),(5,'500 mg'),(6,'1 capsule');
/*!40000 ALTER TABLE `dosage_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `frequencies`
--

DROP TABLE IF EXISTS `frequencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frequencies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `times_of_day` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frequencies`
--

LOCK TABLES `frequencies` WRITE;
/*!40000 ALTER TABLE `frequencies` DISABLE KEYS */;
INSERT INTO `frequencies` VALUES (1,'Morning','morning'),(2,'Daytime','day'),(3,'Night','night'),(4,'Morning & Night','morning,night'),(5,'Day & Night','day,night'),(6,'Morning & Day','morning,day'),(7,'Morning & Day & Night','morning,day,night');
/*!40000 ALTER TABLE `frequencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guardian`
--

DROP TABLE IF EXISTS `guardian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guardian` (
  `nic` varchar(45) NOT NULL,
  `g_name` varchar(100) NOT NULL,
  `contact_number` varchar(10) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(45) NOT NULL,
  PRIMARY KEY (`nic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guardian`
--

LOCK TABLES `guardian` WRITE;
/*!40000 ALTER TABLE `guardian` DISABLE KEYS */;
INSERT INTO `guardian` VALUES ('200155503745','sandali','0704435355','sandali@gmail.com','1234567'),('555555555V','Test Guardian','0123456789','guardian@test.com','password123');
/*!40000 ALTER TABLE `guardian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meal_timing`
--

DROP TABLE IF EXISTS `meal_timing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meal_timing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_timing`
--

LOCK TABLES `meal_timing` WRITE;
/*!40000 ALTER TABLE `meal_timing` DISABLE KEYS */;
INSERT INTO `meal_timing` VALUES (1,'Before Meal'),(2,'After Meal'),(3,'With Meal'),(4,'Empty Stomach');
/*!40000 ALTER TABLE `meal_timing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medication_log`
--

DROP TABLE IF EXISTS `medication_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medication_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medication_schedule_id` int NOT NULL,
  `patient_nic` varchar(20) NOT NULL,
  `dose_date` date NOT NULL,
  `status` enum('TAKEN','MISSED','PENDING') DEFAULT 'PENDING',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `time_slot` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medication_log_ibfk_1` (`medication_schedule_id`),
  CONSTRAINT `medication_log_ibfk_1` FOREIGN KEY (`medication_schedule_id`) REFERENCES `medication_schedule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medication_log`
--

LOCK TABLES `medication_log` WRITE;
/*!40000 ALTER TABLE `medication_log` DISABLE KEYS */;
INSERT INTO `medication_log` VALUES (34,23,'200367610600','2025-10-23','TAKEN','2025-10-23 07:05:57','Morning');
/*!40000 ALTER TABLE `medication_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medication_schedule`
--

DROP TABLE IF EXISTS `medication_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medication_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `duration_days` int DEFAULT NULL,
  `instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `medicine_id` int DEFAULT NULL,
  `dosage_id` int DEFAULT NULL,
  `frequency_id` int DEFAULT NULL,
  `meal_timing_id` int DEFAULT NULL,
  `schedule_master_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_start_date` (`start_date`),
  KEY `fk_ms_meal` (`meal_timing_id`),
  KEY `fk_schedule_master` (`schedule_master_id`),
  CONSTRAINT `fk_ms_meal` FOREIGN KEY (`meal_timing_id`) REFERENCES `meal_timing` (`id`),
  CONSTRAINT `fk_schedule_master` FOREIGN KEY (`schedule_master_id`) REFERENCES `schedule_master` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medication_schedule`
--

LOCK TABLES `medication_schedule` WRITE;
/*!40000 ALTER TABLE `medication_schedule` DISABLE KEYS */;
INSERT INTO `medication_schedule` VALUES (22,'2025-10-22',NULL,7,'Drink water','2025-10-23 06:59:24',6,2,2,1,22),(23,'2025-10-21',NULL,6,'Sleep','2025-10-23 06:59:24',13,2,7,1,22);
/*!40000 ALTER TABLE `medication_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicines`
--

DROP TABLE IF EXISTS `medicines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `quantity_in_stock` int DEFAULT '0',
  `manufacturer` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `added_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicines`
--

LOCK TABLES `medicines` WRITE;
/*!40000 ALTER TABLE `medicines` DISABLE KEYS */;
INSERT INTO `medicines` VALUES (9,'Aspirin','Acetylsalicylic Acid','Antiplatelet','Used to prevent blood clots.','Tablet','75mg',300,'Bayer','2027-06-20',1,'2025-10-22 14:27:12'),(10,'Metformin','Metformin Hydrochloride','Antidiabetic','Helps control blood sugar levels.','Tablet','500mg',180,'Sun Pharma','2026-09-12',3,'2025-10-22 14:27:12'),(11,'Lipitor','Atorvastatin','Statin','Lowers cholesterol.','Tablet','20mg',160,'Pfizer','2026-11-30',2,'2025-10-22 14:27:12'),(12,'Ventolin','Salbutamol','Bronchodilator','Relieves asthma symptoms.','Inhaler','100mcg',75,'GSK','2025-12-01',1,'2025-10-22 14:27:12'),(13,'Ibuprofen','Ibuprofen','NSAID','Reduces pain and inflammation.','Tablet','400mg',210,'Advil','2026-05-18',1,'2025-10-22 14:27:12'),(14,'Omeprazole','Omeprazole','PPI','Reduces stomach acid.','Capsule','20mg',145,'Dr. Reddy’s','2027-01-22',3,'2025-10-22 14:27:12'),(15,'Ciprofloxacin','Ciprofloxacin','Antibiotic','Treats bacterial infections.','Tablet','500mg',130,'Sandoz','2026-02-15',2,'2025-10-22 14:27:12'),(16,'Augmentin','Amoxicillin + Clavulanic Acid','Antibiotic','Broad spectrum antibiotic.','Tablet','625mg',90,'GSK','2026-06-05',1,'2025-10-22 14:27:12'),(17,'Prednisolone','Prednisolone','Steroid','Reduces inflammation.','Tablet','5mg',85,'Teva','2025-11-30',3,'2025-10-22 14:27:12'),(18,'Amlodipine','Amlodipine','Antihypertensive','Treats high blood pressure.','Tablet','5mg',170,'Zydus','2026-08-25',2,'2025-10-22 14:27:12'),(19,'Losartan','Losartan Potassium','Antihypertensive','Lowers blood pressure.','Tablet','50mg',200,'Torrent Pharma','2027-03-15',3,'2025-10-22 14:27:12'),(20,'Insulin Glargine','Insulin Glargine','Insulin','Long-acting insulin.','Injection','100IU/mL',50,'Sanofi','2025-12-31',1,'2025-10-22 14:27:12'),(21,'Hydrochlorothiazide','Hydrochlorothiazide','Diuretic','Used to treat fluid retention.','Tablet','25mg',110,'Merck','2026-04-30',2,'2025-10-22 14:27:12'),(22,'Furosemide','Furosemide','Diuretic','Treats edema and hypertension.','Tablet','40mg',140,'Sanofi','2026-12-12',3,'2025-10-22 14:27:12'),(23,'Tramadol','Tramadol Hydrochloride','Analgesic','Moderate to severe pain relief.','Tablet','50mg',65,'Mylan','2025-10-10',2,'2025-10-22 14:27:12'),(24,'Ranitidine','Ranitidine','H2 Blocker','Reduces stomach acid.','Tablet','150mg',95,'Zantac','2025-11-01',1,'2025-10-22 14:27:12'),(25,'Clopidogrel','Clopidogrel Bisulfate','Antiplatelet','Prevents heart attacks.','Tablet','75mg',105,'Sanofi','2026-07-07',3,'2025-10-22 14:27:12');
/*!40000 ALTER TABLE `medicines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient` (
  `nic` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `allergies` text,
  `chronic_issues` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `guardian_nic` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`nic`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient`
--

LOCK TABLES `patient` WRITE;
/*!40000 ALTER TABLE `patient` DISABLE KEYS */;
INSERT INTO `patient` VALUES ('123456000V','Vihanga Test','Male','0771234567','vihanga@example.com','Password123!','None','None','2025-12-26 14:47:47',''),('123456789V','Test Patient','Male','0123456789','test@test.com','password123','','','2025-12-25 09:44:27',''),('200012345679','Test Patient','Male','0771234567','test2@medora.com','password123','','','2025-12-27 04:30:41',''),('200112500356','vihanga ','Male','0704435300','pharmacist5@medora.com','1234567','noooooono','nooooooooo','2025-10-22 12:48:14',NULL),('200312500353','vihanga','Male','0704435388','vihangaoshan132@gmail.com','Password1324','no','no','2025-12-25 09:33:31',''),('200312500444','mobit','Male','0704435322','rgrgfA@gmail.com','1234567','nono','no','2025-10-23 06:51:00','200312500777'),('200367610500','John','Male','0702665757','john@gmail.com','1234567','no','no','2025-10-23 07:56:00','200155503745'),('200367610600','Peter','Male','0704444444','peter@gmail.com','1234567','no','Diabetes','2025-10-23 06:52:11','200155503745'),('211112500353','Chenal','Male','0703333333','chenal@gmail.com','1234567','no','no','2025-10-23 07:03:40','200312500333');
/*!40000 ALTER TABLE `patient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacist`
--

DROP TABLE IF EXISTS `pharmacist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacist` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacist`
--

LOCK TABLES `pharmacist` WRITE;
/*!40000 ALTER TABLE `pharmacist` DISABLE KEYS */;
INSERT INTO `pharmacist` VALUES (1234,'production','zybbera@gmail.com','8bb0cf6eb9b17d0f7d22b456f121257dc1254e1f01665370476383ea776df414','2025-12-28 04:37:21'),(2003,'Vihanga','vihanga@gmail.com','8bb0cf6eb9b17d0f7d22b456f121257dc1254e1f01665370476383ea776df414','2025-10-23 06:57:55'),(7777,'Oshan Pharmacist','oshan_new@example.com','a109e36947ad56de1dca1cc49f0ef8ac9ad9a7b1aa0df41fb3c4cb73c1ff01ea','2025-12-26 15:15:00'),(9999,'Pharmacist Test','pharmacist@example.com','a109e36947ad56de1dca1cc49f0ef8ac9ad9a7b1aa0df41fb3c4cb73c1ff01ea','2025-12-26 14:59:11'),(12345,'Test Pharmacist','ph@test.com','ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f','2025-12-27 08:34:01'),(2002125,'nemtihaaaa','pharmacist5@medora.com','8bb0cf6eb9b17d0f7d22b456f121257dc1254e1f01665370476383ea776df414','2025-10-22 14:08:55'),(987654321,'Test Pharmacist','pharm@test.com','ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f','2025-12-25 09:49:03');
/*!40000 ALTER TABLE `pharmacist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_nic` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('PENDING','APPROVED','REJECTED','SCHEDULED') DEFAULT 'PENDING',
  PRIMARY KEY (`id`),
  KEY `patient_nic` (`patient_nic`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (45,'200367610600','IMG-20251023-WA0004.jpg','2d51cb06-4ad0-4090-bf91-886baec2e429_IMG-20251023-WA0004.jpg','2025-10-23 06:56:46','SCHEDULED'),(46,'200367610500','download.jpeg','ccdb5afb-98fa-416e-9ade-6931ee3a720a_download.jpeg','2025-10-23 07:56:54','PENDING'),(47,'200367610600','images.jpeg','31497650-cde8-4ad1-8a57-8ec356282f79_images.jpeg','2025-10-23 08:00:10','SCHEDULED'),(48,'123456789V','IMG_8046-16.jpg','1bb23c32-d35b-4d85-855e-18d0a76f217c_IMG_8046-16.jpg','2025-12-28 04:29:58','PENDING');
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `schedule_id` int NOT NULL,
  `patient_nic` varchar(50) NOT NULL,
  `reminder_type` enum('SMS','APP') NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_schedule_id` (`schedule_id`),
  KEY `idx_patient_nic` (`patient_nic`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `medication_schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reminders`
--

LOCK TABLES `reminders` WRITE;
/*!40000 ALTER TABLE `reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_master`
--

DROP TABLE IF EXISTS `schedule_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_master` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `patient_nic` varchar(20) NOT NULL,
  `pharmacist_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sm_prescription` (`prescription_id`),
  KEY `fk_sm_patient` (`patient_nic`),
  CONSTRAINT `fk_sm_patient` FOREIGN KEY (`patient_nic`) REFERENCES `patient` (`nic`) ON DELETE CASCADE,
  CONSTRAINT `fk_sm_prescription` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_master_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_master`
--

LOCK TABLES `schedule_master` WRITE;
/*!40000 ALTER TABLE `schedule_master` DISABLE KEYS */;
INSERT INTO `schedule_master` VALUES (22,45,'200367610600',NULL,'2025-10-23 06:59:24','2025-10-23 06:59:24'),(23,47,'200367610600',NULL,'2025-10-23 08:01:21','2025-10-23 08:01:21');
/*!40000 ALTER TABLE `schedule_master` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-28 11:09:25
