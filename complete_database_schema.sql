-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.37 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.7.0.6850
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for new_path_2
CREATE DATABASE IF NOT EXISTS `new_path_2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `new_path_2`;

-- Dumping structure for table new_path_2.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `achievement_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `badge_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_required` int DEFAULT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`achievement_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_achievement_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.achievements: ~0 rows (approximately)

-- Dumping structure for table new_path_2.admin
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.admin: ~0 rows (approximately)
INSERT INTO `admin` (`admin_id`, `user_id`, `full_name`, `permissions`, `is_super_admin`, `created_at`, `updated_at`) VALUES
	(1, 1, 'System Administrator', NULL, 1, '2026-01-02 18:00:38', '2026-01-02 18:00:38'),
	(2, 2, 'System Administrator', NULL, 1, '2026-01-04 17:50:30', '2026-01-04 17:50:39');

-- Dumping structure for table new_path_2.audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.audit_logs: ~0 rows (approximately)

-- Dumping structure for table new_path_2.community_posts
CREATE TABLE IF NOT EXISTS `community_posts` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_type` enum('general','success_story','question','support_request','resource') COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `is_anonymous` tinyint(1) DEFAULT '0',
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `likes_count` int DEFAULT '0',
  `comments_count` int DEFAULT '0',
  `shares_count` int DEFAULT '0',
  `views_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_created` (`created_at`),
  FULLTEXT KEY `idx_content` (`title`,`content`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.community_posts: ~0 rows (approximately)
INSERT INTO `community_posts` (`post_id`, `user_id`, `title`, `content`, `image_url`, `post_type`, `is_anonymous`, `is_pinned`, `is_active`, `likes_count`, `comments_count`, `shares_count`, `views_count`, `created_at`, `updated_at`) VALUES
	(1, 2, 'Pkyd', ';lksdj\';fljsdlf', '/NewPath_war/uploads/posts/d39b9b74-1663-4f7a-8146-5fb5bdde0541.jpg', 'general', 0, 0, 0, 0, 0, 0, 0, '2026-01-05 08:15:06', '2026-01-11 10:03:25');

-- Dumping structure for table new_path_2.counselors
CREATE TABLE IF NOT EXISTS `counselors` (
  `counselor_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty_short` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `experience_years` int DEFAULT '0',
  `education` text COLLATE utf8mb4_unicode_ci,
  `certifications` text COLLATE utf8mb4_unicode_ci,
  `languages_spoken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` json DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_clients` int DEFAULT '0',
  `total_sessions` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`counselor_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  KEY `idx_verified` (`is_verified`),
  KEY `idx_specialty` (`specialty`),
  CONSTRAINT `fk_counselor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.counselors: ~0 rows (approximately)
INSERT INTO `counselors` (`counselor_id`, `user_id`, `title`, `specialty`, `specialty_short`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `is_verified`, `rating`, `total_reviews`, `total_clients`, `total_sessions`, `created_at`, `updated_at`) VALUES
	(1, 5, 'sdfdsf', 'Addiction Counseling', NULL, 'dfgdfg', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 34543.00, '{}', 1, 0.00, 0, 0, 0, '2026-01-04 18:10:13', '2026-01-04 18:10:13');

-- Dumping structure for table new_path_2.counselor_applications
CREATE TABLE IF NOT EXISTS `counselor_applications` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `experience_years` int DEFAULT NULL,
  `education` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `certifications` text COLLATE utf8mb4_unicode_ci,
  `languages_spoken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` text COLLATE utf8mb4_unicode_ci,
  `documents_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `fk_app_admin` (`reviewed_by`),
  CONSTRAINT `fk_app_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.counselor_applications: ~2 rows (approximately)
INSERT INTO `counselor_applications` (`application_id`, `full_name`, `email`, `phone_number`, `title`, `specialty`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `documents_url`, `status`, `admin_notes`, `reviewed_by`, `review_date`, `created_at`, `updated_at`) VALUES
	(1, 'Pasidu Rajapaksha', 'Pasidurajapaksha202@gmail.com', '0773623777', 'sdfdsf', 'Addiction Counseling', 'dfgdfg', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 34543.00, 'dfgdfg', 'https://www.youtube.com/watch?v=FBbH2d5A5Tg', 'rejected', 'bullshit', 2, '2026-01-04 18:07:19', '2026-01-04 18:01:27', '2026-01-04 18:07:19'),
	(2, 'Pasidu Rajapaksha', 'Puski200322@gmail.com', '0773623777', 'sdfdsf', 'Addiction Counseling', 'dfgdfg', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 34543.00, 'dfgdfg', 'https://www.youtube.com/watch?v=FBbH2d5A5Tg', 'approved', 'Application approved and counselor account created', 2, '2026-01-04 18:10:13', '2026-01-04 18:07:36', '2026-01-04 18:10:13');

-- Dumping structure for table new_path_2.counselor_payouts
CREATE TABLE IF NOT EXISTS `counselor_payouts` (
  `payout_id` int NOT NULL AUTO_INCREMENT,
  `counselor_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `sessions_count` int DEFAULT '0',
  `status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `stripe_payout_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payout_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_payout_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.counselor_payouts: ~0 rows (approximately)

-- Dumping structure for table new_path_2.daily_checkins
CREATE TABLE IF NOT EXISTS `daily_checkins` (
  `checkin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `checkin_date` date NOT NULL,
  `mood_rating` int DEFAULT NULL,
  `mood_label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `energy_level` int DEFAULT NULL,
  `sleep_quality` int DEFAULT NULL,
  `stress_level` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`checkin_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`checkin_date`),
  CONSTRAINT `fk_checkin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.daily_checkins: ~0 rows (approximately)

-- Dumping structure for table new_path_2.help_centers
CREATE TABLE IF NOT EXISTS `help_centers` (
  `help_center_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availability` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `specialties` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`help_center_id`),
  KEY `idx_type` (`type`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `fk_hc_user` (`created_by`),
  CONSTRAINT `fk_hc_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.help_centers: ~0 rows (approximately)

-- Dumping structure for table new_path_2.job_posts
CREATE TABLE IF NOT EXISTS `job_posts` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `requirements` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_type` enum('full_time','part_time','contract','temporary','internship') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `salary_range` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_url` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  KEY `idx_type` (`job_type`),
  KEY `idx_category` (`category`),
  KEY `idx_location` (`location`),
  KEY `idx_active` (`is_active`),
  KEY `fk_job_admin` (`created_by`),
  CONSTRAINT `fk_job_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.job_posts: ~0 rows (approximately)

-- Dumping structure for table new_path_2.journal_categories
CREATE TABLE IF NOT EXISTS `journal_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_jc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.journal_categories: ~5 rows (approximately)
INSERT INTO `journal_categories` (`category_id`, `user_id`, `name`, `slug`, `is_default`, `color`, `created_at`) VALUES
	(1, NULL, 'Gratitude', 'gratitude', 1, NULL, '2026-01-02 18:00:38'),
	(2, NULL, 'Progress', 'progress', 1, NULL, '2026-01-02 18:00:38'),
	(3, NULL, 'Challenge', 'challenge', 1, NULL, '2026-01-02 18:00:38'),
	(4, NULL, 'Reflection', 'reflection', 1, NULL, '2026-01-02 18:00:38'),
	(5, NULL, 'Other', 'other', 1, NULL, '2026-01-02 18:00:38');

-- Dumping structure for table new_path_2.journal_entries
CREATE TABLE IF NOT EXISTS `journal_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_highlight` tinyint(1) DEFAULT '0',
  `mood` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_je_category` FOREIGN KEY (`category_id`) REFERENCES `journal_categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_je_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.journal_entries: ~0 rows (approximately)

-- Dumping structure for table new_path_2.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.notifications: ~0 rows (approximately)

-- Dumping structure for table new_path_2.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `payment_method_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `method_type` enum('card','paypal','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_brand` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_month` int DEFAULT NULL,
  `expiry_year` int DEFAULT NULL,
  `billing_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `stripe_payment_method_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_method_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.payment_methods: ~0 rows (approximately)

-- Dumping structure for table new_path_2.post_comments
CREATE TABLE IF NOT EXISTS `post_comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `likes_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `idx_post` (`post_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_parent` (`parent_comment_id`),
  CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `post_comments` (`comment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.post_comments: ~0 rows (approximately)

-- Dumping structure for table new_path_2.post_likes
CREATE TABLE IF NOT EXISTS `post_likes` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`),
  UNIQUE KEY `idx_post_user` (`post_id`,`user_id`),
  KEY `fk_like_user` (`user_id`),
  CONSTRAINT `fk_like_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.post_likes: ~0 rows (approximately)

-- Dumping structure for table new_path_2.post_reports
CREATE TABLE IF NOT EXISTS `post_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int DEFAULT NULL,
  `comment_id` int DEFAULT NULL,
  `reporter_id` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','resolved','dismissed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `action_taken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  KEY `idx_status` (`status`),
  KEY `fk_report_post` (`post_id`),
  KEY `fk_report_comment` (`comment_id`),
  KEY `fk_report_reporter` (`reporter_id`),
  KEY `fk_report_admin` (`reviewed_by`),
  CONSTRAINT `fk_report_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_report_comment` FOREIGN KEY (`comment_id`) REFERENCES `post_comments` (`comment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_report_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_report_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.post_reports: ~0 rows (approximately)

-- Dumping structure for table new_path_2.post_tags
CREATE TABLE IF NOT EXISTS `post_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_count` int DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.post_tags: ~6 rows (approximately)
INSERT INTO `post_tags` (`tag_id`, `name`, `slug`, `post_count`) VALUES
	(1, 'Recovery', 'recovery', 0),
	(2, 'Motivation', 'motivation', 0),
	(3, 'Support', 'support', 0),
	(4, 'Success Story', 'success-story', 0),
	(5, 'Question', 'question', 0),
	(6, 'Resources', 'resources', 0);

-- Dumping structure for table new_path_2.post_tag_mappings
CREATE TABLE IF NOT EXISTS `post_tag_mappings` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_ptm_tag` (`tag_id`),
  CONSTRAINT `fk_ptm_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptm_tag` FOREIGN KEY (`tag_id`) REFERENCES `post_tags` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.post_tag_mappings: ~0 rows (approximately)

-- Dumping structure for table new_path_2.recovery_goals
CREATE TABLE IF NOT EXISTS `recovery_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `goal_type` enum('short_term','long_term') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `target_days` int DEFAULT NULL,
  `current_progress` int DEFAULT '0',
  `status` enum('in_progress','achieved','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `achieved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`goal_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `fk_goal_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.recovery_goals: ~0 rows (approximately)

-- Dumping structure for table new_path_2.recovery_plans
CREATE TABLE IF NOT EXISTS `recovery_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `counselor_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_type` enum('counselor','self') COLLATE utf8mb4_unicode_ci DEFAULT 'self',
  `status` enum('draft','active','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `progress_percentage` int DEFAULT '0',
  `custom_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_template` tinyint(1) DEFAULT '0',
  `template_source_id` int DEFAULT NULL,
  `assigned_status` enum('pending','accepted','rejected') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_plan_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.recovery_plans: ~1 rows (approximately)
INSERT INTO `recovery_plans` (`plan_id`, `user_id`, `counselor_id`, `title`, `description`, `category`, `plan_type`, `status`, `start_date`, `target_completion_date`, `actual_completion_date`, `progress_percentage`, `custom_notes`, `created_at`, `updated_at`, `is_template`, `template_source_id`, `assigned_status`) VALUES
	(10, 2, 1, 'General Addiction Recovery Plan', 'A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.', NULL, 'counselor', 'active', '2026-01-10', '2026-04-10', NULL, 100, 'Generated plan for substance addiction recovery. Duration: 3 months. Please customize based on client\'s specific needs.', '2026-01-10 06:34:26', '2026-01-11 09:56:55', 0, NULL, 'accepted');

-- Dumping structure for table new_path_2.recovery_tasks
CREATE TABLE IF NOT EXISTS `recovery_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `task_type` enum('journal','meditation','session','exercise','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'custom',
  `status` enum('pending','in_progress','completed','skipped') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurrence_pattern` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `phase` int DEFAULT '1',
  PRIMARY KEY (`task_id`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `fk_task_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.recovery_tasks: ~9 rows (approximately)
INSERT INTO `recovery_tasks` (`task_id`, `plan_id`, `title`, `description`, `task_type`, `status`, `priority`, `due_date`, `completed_at`, `is_recurring`, `recurrence_pattern`, `sort_order`, `created_at`, `updated_at`, `phase`) VALUES
	(55, 10, 'Initial assessment', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:05:50', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:05:50', 1),
	(56, 10, 'Goal setting', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:35:57', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:35:57', 1),
	(57, 10, 'Build support system', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:35:58', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:35:58', 1),
	(58, 10, 'Regular therapy', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:44:42', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:44:42', 2),
	(59, 10, 'Develop coping skills', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:56:46', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:56:46', 2),
	(60, 10, 'Healthy routines', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:56:48', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:56:48', 2),
	(61, 10, 'Relapse prevention', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:56:51', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:56:51', 3),
	(62, 10, 'Life skills training', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:56:53', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:56:53', 3),
	(63, 10, 'Future planning', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 09:56:55', 0, '', 0, '2026-01-10 06:34:26', '2026-01-11 09:56:55', 3);

-- Dumping structure for table new_path_2.refund_disputes
CREATE TABLE IF NOT EXISTS `refund_disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `issue_type` enum('missed_session','quality_complaint','technical_issue','billing_error','unauthorized_charge','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `requested_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_by` int DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `refunded_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dispute_id`),
  KEY `idx_transaction` (`transaction_id`),
  KEY `idx_status` (`status`),
  KEY `fk_dispute_user` (`user_id`),
  KEY `fk_dispute_admin` (`resolved_by`),
  CONSTRAINT `fk_dispute_admin` FOREIGN KEY (`resolved_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dispute_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispute_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.refund_disputes: ~0 rows (approximately)

-- Dumping structure for table new_path_2.saved_jobs
CREATE TABLE IF NOT EXISTS `saved_jobs` (
  `saved_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `job_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`saved_id`),
  UNIQUE KEY `idx_user_job` (`user_id`,`job_id`),
  KEY `fk_sj_job` (`job_id`),
  CONSTRAINT `fk_sj_job` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sj_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.saved_jobs: ~0 rows (approximately)

-- Dumping structure for table new_path_2.saved_posts
CREATE TABLE IF NOT EXISTS `saved_posts` (
  `saved_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`saved_id`),
  UNIQUE KEY `idx_user_post` (`user_id`,`post_id`),
  KEY `fk_saved_post` (`post_id`),
  CONSTRAINT `fk_saved_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.saved_posts: ~0 rows (approximately)

-- Dumping structure for table new_path_2.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `session_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `session_type` enum('video','audio','chat','in_person') COLLATE utf8mb4_unicode_ci DEFAULT 'video',
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_notes` text COLLATE utf8mb4_unicode_ci,
  `counselor_private_notes` text COLLATE utf8mb4_unicode_ci,
  `rating` int DEFAULT NULL,
  `review` text COLLATE utf8mb4_unicode_ci,
  `cancelled_by` int DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_datetime` (`session_datetime`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_session_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`session_id`, `user_id`, `counselor_id`, `session_datetime`, `duration_minutes`, `session_type`, `status`, `location`, `meeting_link`, `session_notes`, `counselor_private_notes`, `rating`, `review`, `cancelled_by`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2026-01-09 22:08:55', 60, 'video', 'scheduled', NULL, 'dfsgfdgdfg', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 16:38:56', '2026-01-09 16:39:07');

-- Dumping structure for table new_path_2.support_groups
CREATE TABLE IF NOT EXISTS `support_groups` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_schedule` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_members` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `idx_active` (`is_active`),
  KEY `fk_sg_admin` (`created_by`),
  CONSTRAINT `fk_sg_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.support_groups: ~0 rows (approximately)

-- Dumping structure for table new_path_2.support_group_members
CREATE TABLE IF NOT EXISTS `support_group_members` (
  `membership_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('member','moderator','leader') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`membership_id`),
  UNIQUE KEY `idx_group_user` (`group_id`,`user_id`),
  KEY `fk_sgm_user` (`user_id`),
  CONSTRAINT `fk_sgm_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.support_group_members: ~0 rows (approximately)

-- Dumping structure for table new_path_2.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.system_settings: ~0 rows (approximately)

-- Dumping structure for table new_path_2.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `transaction_uuid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int DEFAULT NULL,
  `payment_method_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `payment_type` enum('session','subscription','tip','refund') COLLATE utf8mb4_unicode_ci DEFAULT 'session',
  `status` enum('pending','completed','failed','refunded','disputed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `idx_uuid` (`transaction_uuid`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `fk_txn_session` (`session_id`),
  KEY `fk_txn_pm` (`payment_method_id`),
  CONSTRAINT `fk_txn_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_pm` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.transactions: ~0 rows (approximately)

-- Dumping structure for table new_path_2.trigger_categories
CREATE TABLE IF NOT EXISTS `trigger_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.trigger_categories: ~5 rows (approximately)
INSERT INTO `trigger_categories` (`category_id`, `name`, `description`, `is_default`) VALUES
	(1, 'Stress', 'Work or life-related stress', 1),
	(2, 'Social', 'Social situations or peer pressure', 1),
	(3, 'Emotional', 'Emotional distress or mood changes', 1),
	(4, 'Environmental', 'Places or situations associated with past use', 1),
	(5, 'Physical', 'Physical discomfort or pain', 1);

-- Dumping structure for table new_path_2.urge_logs
CREATE TABLE IF NOT EXISTS `urge_logs` (
  `urge_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `logged_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `intensity` int DEFAULT NULL,
  `trigger_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trigger_description` text COLLATE utf8mb4_unicode_ci,
  `coping_strategy_used` text COLLATE utf8mb4_unicode_ci,
  `outcome` enum('resisted','relapsed','in_progress') COLLATE utf8mb4_unicode_ci DEFAULT 'resisted',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`urge_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_logged_at` (`logged_at`),
  CONSTRAINT `fk_urge_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.urge_logs: ~0 rows (approximately)

-- Dumping structure for table new_path_2.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','counselor','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `onboarding_completed` tinyint(1) DEFAULT '0',
  `current_onboarding_step` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.users: ~3 rows (approximately)
INSERT INTO `users` (`user_id`, `email`, `username`, `password_hash`, `salt`, `role`, `first_name`, `last_name`, `display_name`, `profile_picture`, `phone_number`, `age`, `gender`, `is_email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `is_active`, `last_login`, `onboarding_completed`, `current_onboarding_step`, `created_at`, `updated_at`) VALUES
	(1, 'admin@newpath.com', 'admin', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye', 'admin', 'System', 'Administrator', 'System Admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-01-02 18:00:38', '2026-01-02 18:00:38'),
	(2, 'pasidurajapaksha202@gmail.com', 'pasidu', '$2a$10$m3uVRJ8S7NVswiFpooQowuqLGMjeNlPGssXScEPVyqQ8LrM7oQXMe', '$2a$10$m3uVRJ8S7NVswiFpooQowu', 'user', NULL, NULL, 'pasidu', NULL, NULL, 17, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-01-04 17:49:51', '2026-01-04 18:21:51'),
	(5, 'Puski200322@gmail.com', 'pasidu_rajapaksha', '$2a$10$m3uVRJ8S7NVswiFpooQowuqLGMjeNlPGssXScEPVyqQ8LrM7oQXMe', '$2a$10$gTLAJVOOJZlDqV9gOYIXve', 'counselor', NULL, NULL, 'Pasidu Rajapaksha', NULL, '0773623777', NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-01-04 18:10:13', '2026-01-09 16:32:16');

-- Dumping structure for table new_path_2.user_profiles
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `emergency_contact_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sobriety_start_date` date DEFAULT NULL,
  `recovery_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.user_profiles: ~0 rows (approximately)

-- Dumping structure for table new_path_2.user_progress
CREATE TABLE IF NOT EXISTS `user_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `days_sober` int DEFAULT '0',
  `is_sober_today` tinyint(1) DEFAULT '1',
  `milestone_progress` int DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`date`),
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path_2.user_progress: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
