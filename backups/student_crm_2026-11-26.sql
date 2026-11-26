-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: student_crm
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_years` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `campus_id` bigint(20) unsigned NOT NULL,
  `course_category_id` bigint(20) unsigned DEFAULT NULL,
  `academic_year` varchar(50) NOT NULL,
  `current_year` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_years_campus_id_foreign` (`campus_id`),
  CONSTRAINT `academic_years_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2026-01-01','2026-12-31',1,3,'2026-2027',1,'2026-11-01 19:32:17','2026-11-02 19:53:31',NULL),(6,'2026-01-01','2026-12-31',1,5,'2026-2027',0,'2026-11-01 19:42:57','2026-11-02 19:53:31',NULL),(7,'2024-01-01','2024-12-31',1,3,'ZZZ-NOMATCH',0,'2026-11-01 19:42:57','2026-11-01 19:48:51','2026-11-01 19:48:51');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumni_events`
--

DROP TABLE IF EXISTS `alumni_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `max_participants` int(10) unsigned DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `target_group` varchar(255) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','ongoing','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alumni_events_created_by_foreign` (`created_by`),
  CONSTRAINT `alumni_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni_events`
--

LOCK TABLES `alumni_events` WRITE;
/*!40000 ALTER TABLE `alumni_events` DISABLE KEYS */;
INSERT INTO `alumni_events` VALUES (1,'Water Festiwal','water Festiwal','workshop','2026-11-23 00:00:00','2026-11-25 12:00:00',NULL,'2026-11-22',NULL,'I-Tech Media Center',NULL,NULL,'events/mavXvpLZIWTynbzFlG7dSxJ59ckspsDkte65Vhyq.png','published',3,'2026-10-21 23:52:36','2026-11-17 04:06:13',NULL);
/*!40000 ALTER TABLE `alumni_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned DEFAULT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `check_in_at` datetime NOT NULL,
  `checked_in_by` bigint(20) unsigned DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `checked_out_by` bigint(20) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_student_id_attendance_date_unique` (`student_id`,`attendance_date`),
  UNIQUE KEY `attendance_records_employee_id_attendance_date_unique` (`employee_id`,`attendance_date`),
  KEY `attendance_records_campus_id_foreign` (`campus_id`),
  KEY `attendance_records_checked_in_by_foreign` (`checked_in_by`),
  KEY `attendance_records_checked_out_by_foreign` (`checked_out_by`),
  KEY `attendance_records_attendance_date_campus_id_index` (`attendance_date`,`campus_id`),
  CONSTRAINT `attendance_records_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_checked_in_by_foreign` FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_checked_out_by_foreign` FOREIGN KEY (`checked_out_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
INSERT INTO `attendance_records` VALUES (1,NULL,4,1,'2026-11-10','2026-11-10 16:11:50',3,NULL,NULL,'Late','2026-11-10 09:11:50','2026-11-10 09:11:50'),(18,NULL,4,1,'2026-11-11','2026-11-11 16:22:30',3,NULL,NULL,'Late','2026-11-11 09:22:30','2026-11-11 09:22:30'),(19,NULL,4,1,'2026-11-13','2026-11-13 08:26:21',3,NULL,NULL,'Late','2026-11-13 01:26:21','2026-11-13 01:26:21'),(20,NULL,4,1,'2026-11-17','2026-11-17 09:11:34',3,NULL,NULL,'Late','2026-11-17 02:11:34','2026-11-17 02:11:34'),(21,NULL,4,1,'2026-11-18','2026-11-18 08:21:30',3,'2026-11-18 08:21:44',3,'Late','2026-11-18 01:21:30','2026-11-18 01:21:44'),(22,NULL,4,1,'2026-11-26','2026-11-26 09:06:31',3,NULL,NULL,'Late','2026-11-26 02:06:31','2026-11-26 02:06:31');
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=522 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-20 21:46:12','2026-10-20 21:46:12'),(2,3,'created','school_club',1,'Created school club \'Football Club\'',NULL,'127.0.0.1','2026-10-20 22:03:12','2026-10-20 22:03:12'),(3,3,'updated','campus',1,'Updated campus \'Toul Tompong Main Campus\'',NULL,'127.0.0.1','2026-10-20 22:03:53','2026-10-20 22:03:53'),(4,3,'created','student',1,'Created student Ratanak Khoeurn (STU-2026-0001)',NULL,'127.0.0.1','2026-10-20 23:11:43','2026-10-20 23:11:43'),(5,3,'created','communication_log',1,'Logged telegram interaction with Ratanak Khoeurn',NULL,'127.0.0.1','2026-10-21 00:17:11','2026-10-21 00:17:11'),(6,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-21 03:03:50','2026-10-21 03:03:50'),(7,3,'updated','user',3,'Updated staff account \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-10-21 03:04:38','2026-10-21 03:04:38'),(8,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-10-21 03:04:51','2026-10-21 03:04:51'),(9,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-21 03:04:59','2026-10-21 03:04:59'),(10,3,'updated','employee',2,'Updated employee \'Sreyroth Sok\'',NULL,'127.0.0.1','2026-10-21 03:05:33','2026-10-21 03:05:33'),(11,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-21 20:47:42','2026-10-21 20:47:42'),(12,1,'updated','student',1,'Enrolled Ratanak Khoeurn in course Microsoft Office',NULL,'127.0.0.1','2026-10-21 21:37:53','2026-10-21 21:37:53'),(13,1,'updated','student',1,'Updated course enrollment Microsoft Office for Ratanak Khoeurn',NULL,'127.0.0.1','2026-10-21 21:37:53','2026-10-21 21:37:53'),(14,3,'updated','student',1,'Enrolled Ratanak Khoeurn in course Microsoft Office',NULL,'127.0.0.1','2026-10-21 21:44:35','2026-10-21 21:44:35'),(15,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"date_of_birth\",\"registered_date\",\"graduation_status\",\"last_contacted_date\"]','127.0.0.1','2026-10-21 21:46:30','2026-10-21 21:46:30'),(16,1,'updated','student',1,'Enrolled Ratanak Khoeurn in course Photography',NULL,'127.0.0.1','2026-10-21 23:11:51','2026-10-21 23:11:51'),(17,1,'updated','student',1,'Enrolled Ratanak Khoeurn in course Microsoft Office',NULL,'127.0.0.1','2026-10-21 23:11:51','2026-10-21 23:11:51'),(18,3,'updated','student',1,'Removed course enrollment Graphic Design for Ratanak Khoeurn',NULL,'127.0.0.1','2026-10-21 23:19:28','2026-10-21 23:19:28'),(19,3,'updated','student',1,'Enrolled Ratanak Khoeurn in course Graphic Design',NULL,'127.0.0.1','2026-10-21 23:41:25','2026-10-21 23:41:25'),(20,3,'status_changed','invoice',1,'Invoice INV-2026-0001 → cancelled',NULL,'127.0.0.1','2026-10-21 23:47:03','2026-10-21 23:47:03'),(21,3,'status_changed','invoice',2,'Invoice INV-2026-0002 → sent',NULL,'127.0.0.1','2026-10-21 23:47:26','2026-10-21 23:47:26'),(22,3,'created','payment',2,'Payment of 175 on INV-2026-0002',NULL,'127.0.0.1','2026-10-21 23:47:51','2026-10-21 23:47:51'),(23,3,'created','payment',3,'Payment of 150 on INV-2026-0003',NULL,'127.0.0.1','2026-10-21 23:48:18','2026-10-21 23:48:18'),(24,3,'updated','invoice',3,'Updated invoice INV-2026-0003',NULL,'127.0.0.1','2026-10-21 23:48:38','2026-10-21 23:48:38'),(25,3,'status_changed','invoice',3,'Invoice INV-2026-0003 → sent',NULL,'127.0.0.1','2026-10-21 23:48:48','2026-10-21 23:48:48'),(26,3,'created','payment',3,'Payment of 150 on INV-2026-0003',NULL,'127.0.0.1','2026-10-21 23:49:28','2026-10-21 23:49:28'),(27,3,'created','payment',3,'Payment of 50 on INV-2026-0003',NULL,'127.0.0.1','2026-10-21 23:49:38','2026-10-21 23:49:38'),(28,3,'created','payment',3,'Payment of 50 on INV-2026-0003',NULL,'127.0.0.1','2026-10-21 23:49:43','2026-10-21 23:49:43'),(29,3,'created','alumni_event',1,'Created event \'Water Festiwal\'',NULL,'127.0.0.1','2026-10-21 23:52:36','2026-10-21 23:52:36'),(30,3,'updated','alumni_event',1,'Updated event \'Water Festiwal\'',NULL,'127.0.0.1','2026-10-21 23:52:52','2026-10-21 23:52:52'),(31,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-10-21 23:53:04','2026-10-21 23:53:04'),(32,3,'created','program',1,'Created program \'Local Curriculums\'',NULL,'127.0.0.1','2026-10-22 00:18:07','2026-10-22 00:18:07'),(33,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-25 18:12:18','2026-10-25 18:12:18'),(34,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-28 01:53:21','2026-10-28 01:53:21'),(35,3,'updated','event_registration',1,'Registration status → registered',NULL,'127.0.0.1','2026-10-28 02:04:30','2026-10-28 02:04:30'),(36,3,'updated','event_registration',1,'Registration status → attended',NULL,'127.0.0.1','2026-10-28 02:04:39','2026-10-28 02:04:39'),(37,3,'updated','event_registration',1,'Registration status → attended',NULL,'127.0.0.1','2026-10-28 02:04:43','2026-10-28 02:04:43'),(38,3,'updated','alumni_event',1,'Updated event \'Water Festiwal\'',NULL,'127.0.0.1','2026-10-28 02:05:48','2026-10-28 02:05:48'),(39,3,'updated','alumni_event',1,'Updated event \'Water Festiwal\'',NULL,'127.0.0.1','2026-10-28 02:06:13','2026-10-28 02:06:13'),(40,3,'created','opportunity',1,'Created opportunity \'Graphic Design\'',NULL,'127.0.0.1','2026-10-28 02:13:12','2026-10-28 02:13:12'),(41,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-10-28 18:07:17','2026-10-28 18:07:17'),(42,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"date_of_birth\",\"emergency_contact_phone\",\"registered_date\",\"last_contacted_date\"]','127.0.0.1','2026-10-28 18:10:23','2026-10-28 18:10:23'),(43,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-01 18:21:53','2026-11-01 18:21:53'),(44,NULL,'registered','user',7,'New account \'qa.register.6ae7e7a3735d4@example.test\' registered (pending approval)',NULL,'127.0.0.1','2026-11-01 18:30:11','2026-11-01 18:30:11'),(45,NULL,'registered','user',8,'New account \'qa.register.6ae7e7c5748ce@example.test\' registered (pending approval)',NULL,'127.0.0.1','2026-11-01 18:30:45','2026-11-01 18:30:45'),(46,NULL,'registered','user',9,'New account \'qa.register.6ae7e7f0db790@example.test\' registered (pending approval)',NULL,'127.0.0.1','2026-11-01 18:31:28','2026-11-01 18:31:28'),(47,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:07:50','2026-11-01 19:07:50'),(48,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:08:04','2026-11-01 19:08:04'),(49,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:08:48','2026-11-01 19:08:48'),(50,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:11:08','2026-11-01 19:11:08'),(51,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:11:31','2026-11-01 19:11:31'),(52,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:12:26','2026-11-01 19:12:26'),(53,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:13:50','2026-11-01 19:13:50'),(54,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:14:00','2026-11-01 19:14:00'),(55,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:17:01','2026-11-01 19:17:01'),(56,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:19:45','2026-11-01 19:19:45'),(57,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-01 19:20:34','2026-11-01 19:20:34'),(60,3,'updated','event_registration',1,'Registration status → attended',NULL,'127.0.0.1','2026-11-01 21:00:50','2026-11-01 21:00:50'),(65,3,'updated','alumni_event',1,'Updated event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:16:09','2026-11-01 21:16:09'),(66,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:16:16','2026-11-01 21:16:16'),(67,3,'updated','event_registration',3,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:30:29','2026-11-01 21:30:29'),(68,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:31:50','2026-11-01 21:31:50'),(69,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:34:18','2026-11-01 21:34:18'),(70,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:34:47','2026-11-01 21:34:47'),(71,3,'updated','event_registration',6,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:36:52','2026-11-01 21:36:52'),(72,3,'updated','event_registration',6,'Registration status → attended',NULL,'127.0.0.1','2026-11-01 21:37:07','2026-11-01 21:37:07'),(73,3,'updated','event_registration',6,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:37:18','2026-11-01 21:37:18'),(74,3,'updated','event_registration',6,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:40:59','2026-11-01 21:40:59'),(75,3,'updated','event_registration',6,'Registration status → attended',NULL,'127.0.0.1','2026-11-01 21:41:05','2026-11-01 21:41:05'),(76,3,'updated','event_registration',6,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:41:12','2026-11-01 21:41:12'),(77,3,'updated','event_registration',6,'Registration status → no_show',NULL,'127.0.0.1','2026-11-01 21:41:18','2026-11-01 21:41:18'),(78,3,'updated','event_registration',6,'Registration status → cancelled',NULL,'127.0.0.1','2026-11-01 21:41:22','2026-11-01 21:41:22'),(79,3,'updated','event_registration',6,'Registration status → cancelled',NULL,'127.0.0.1','2026-11-01 21:46:57','2026-11-01 21:46:57'),(80,3,'updated','event_registration',6,'Registration status → attended',NULL,'127.0.0.1','2026-11-01 21:47:13','2026-11-01 21:47:13'),(81,3,'updated','event_registration',6,'Registration status → no_show',NULL,'127.0.0.1','2026-11-01 21:47:16','2026-11-01 21:47:16'),(82,3,'updated','event_registration',6,'Registration status → attended',NULL,'127.0.0.1','2026-11-01 21:47:18','2026-11-01 21:47:18'),(83,3,'registered','event_registration',1,'Registered student #1 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-01 21:47:27','2026-11-01 21:47:27'),(84,3,'updated','event_registration',7,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:56:42','2026-11-01 21:56:42'),(85,3,'updated','event_registration',7,'Registration status → registered',NULL,'127.0.0.1','2026-11-01 21:58:52','2026-11-01 21:58:52'),(86,3,'referred','opportunity_application',1,'Referred student #1 to \'Graphic Design\'',NULL,'127.0.0.1','2026-11-01 23:15:53','2026-11-01 23:15:53'),(87,3,'updated','opportunity',1,'Updated opportunity \'Graphic Design\'',NULL,'127.0.0.1','2026-11-01 23:17:17','2026-11-01 23:17:17'),(88,3,'updated','employee',1,'Updated employee \'Vandet Noun\'',NULL,'127.0.0.1','2026-11-01 23:18:51','2026-11-01 23:18:51'),(89,3,'updated','employee',2,'Updated employee \'Sreyroth Sok\'',NULL,'127.0.0.1','2026-11-01 23:19:11','2026-11-01 23:19:11'),(90,3,'deleted','payment',2,'Deleted payment on INV-2026-0002',NULL,'127.0.0.1','2026-11-01 23:22:47','2026-11-01 23:22:47'),(91,3,'deleted','payment',3,'Deleted payment on INV-2026-0003',NULL,'127.0.0.1','2026-11-01 23:22:50','2026-11-01 23:22:50'),(92,3,'deleted','payment',3,'Deleted payment on INV-2026-0003',NULL,'127.0.0.1','2026-11-01 23:22:52','2026-11-01 23:22:52'),(93,3,'deleted','payment',3,'Deleted payment on INV-2026-0003',NULL,'127.0.0.1','2026-11-01 23:22:55','2026-11-01 23:22:55'),(94,3,'deleted','invoice',1,'Deleted invoice INV-2026-0001',NULL,'127.0.0.1','2026-11-01 23:29:13','2026-11-01 23:29:13'),(95,3,'deleted','payment',3,'Deleted payment on INV-2026-0003',NULL,'127.0.0.1','2026-11-01 23:43:54','2026-11-01 23:43:54'),(96,3,'created','payment',2,'Payment of 175 on INV-2026-0002',NULL,'127.0.0.1','2026-11-02 01:14:55','2026-11-02 01:14:55'),(97,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-11-02 01:46:13','2026-11-02 01:46:13'),(98,2,'login','user',2,'Kakada Horn logged in',NULL,'127.0.0.1','2026-11-02 01:46:32','2026-11-02 01:46:32'),(99,2,'logout','user',2,'Kakada Horn logged out',NULL,'127.0.0.1','2026-11-02 01:47:07','2026-11-02 01:47:07'),(100,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-02 01:47:10','2026-11-02 01:47:10'),(101,3,'updated','course',2,'Updated course \'Graphic Design\'',NULL,'127.0.0.1','2026-11-02 02:05:19','2026-11-02 02:05:19'),(102,3,'updated','course',2,'Updated course \'Graphic Design\'',NULL,'127.0.0.1','2026-11-02 02:09:12','2026-11-02 02:09:12'),(103,3,'updated','course',1,'Updated course \'Microsoft Office\'',NULL,'127.0.0.1','2026-11-02 02:09:25','2026-11-02 02:09:25'),(104,3,'updated','batch',2,'Updated batch \'Graphic Design — Batch 2026-B\'',NULL,'127.0.0.1','2026-11-02 02:10:59','2026-11-02 02:10:59'),(105,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-02 18:12:28','2026-11-02 18:12:28'),(106,3,'updated','semester',1,'Updated semester \'Semester 1 2026\'',NULL,'127.0.0.1','2026-11-02 18:25:22','2026-11-02 18:25:22'),(107,3,'updated','school_club',1,'Updated school club \'Football Club\'',NULL,'127.0.0.1','2026-11-02 18:39:10','2026-11-02 18:39:10'),(108,3,'updated','school_club',1,'Updated school club \'Football Club\'',NULL,'127.0.0.1','2026-11-02 18:45:07','2026-11-02 18:45:07'),(109,3,'updated','school_club',1,'Updated school club \'Football Club\'',NULL,'127.0.0.1','2026-11-02 18:47:11','2026-11-02 18:47:11'),(110,3,'created','student',2,'Created student Ratana Khoeurn (STU-2026-0002)',NULL,'127.0.0.1','2026-11-02 19:00:56','2026-11-02 19:00:56'),(111,3,'created','study_group',1,'Created study group \'Graphic Design — Batch 2026-01\'',NULL,'127.0.0.1','2026-11-02 19:58:23','2026-11-02 19:58:23'),(112,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"date_of_birth\",\"registered_date\",\"last_contacted_date\"]','127.0.0.1','2026-11-02 20:04:02','2026-11-02 20:04:02'),(113,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"date_of_birth\",\"registered_date\",\"graduation_status\",\"last_contacted_date\"]','127.0.0.1','2026-11-02 20:05:06','2026-11-02 20:05:06'),(114,3,'graduated','student',1,'Marked Ratanak Khoeurn as graduated from study group \'Graphic Design — Batch 2026-01\'',NULL,'127.0.0.1','2026-11-02 20:05:23','2026-11-02 20:05:23'),(115,3,'graduated','study_group',1,'Graduated 1 students from study group \'Graphic Design — Batch 2026-01\'',NULL,'127.0.0.1','2026-11-02 20:05:23','2026-11-02 20:05:23'),(116,3,'removed','study_group_student',1,'Removed student #1 from study group \'Graphic Design — Batch 2026-01\'',NULL,'127.0.0.1','2026-11-02 20:06:01','2026-11-02 20:06:01'),(117,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"date_of_birth\",\"registered_date\",\"graduation_status\",\"last_contacted_date\"]','127.0.0.1','2026-11-02 20:06:16','2026-11-02 20:06:16'),(118,3,'created','study_group',2,'Created study group \'Computer Office-2026-G1\'',NULL,'127.0.0.1','2026-11-02 20:07:34','2026-11-02 20:07:34'),(119,3,'updated','study_group',1,'Updated study group \'Graphic Design-2026-G1\'',NULL,'127.0.0.1','2026-11-02 20:08:10','2026-11-02 20:08:10'),(120,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-02 20:09:10','2026-11-02 20:09:10'),(121,3,'updated','campus',1,'Updated campus \'Toul Tompong Main Campus\'',NULL,'127.0.0.1','2026-11-02 20:28:46','2026-11-02 20:28:46'),(122,3,'created','campus',2,'Created campus \'Chbar Ampov\'',NULL,'127.0.0.1','2026-11-02 20:30:52','2026-11-02 20:30:52'),(123,3,'updated','campus',1,'Updated campus \'Toul Tompong Main Campus\'',NULL,'127.0.0.1','2026-11-02 20:31:14','2026-11-02 20:31:14'),(124,3,'updated','campus',1,'Updated campus \'Toul Tompong Main Campus\'',NULL,'127.0.0.1','2026-11-02 20:32:39','2026-11-02 20:32:39'),(125,3,'uploaded','student_document',1,'Uploaded document \'Graduation\' for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-02 20:42:04','2026-11-02 20:42:04'),(126,3,'backup','database',NULL,'Downloaded a database backup',NULL,'127.0.0.1','2026-11-02 20:45:03','2026-11-02 20:45:03'),(127,3,'created','user',10,'Created staff account \'Reaksmey Ky\'',NULL,'127.0.0.1','2026-11-02 20:49:45','2026-11-02 20:49:45'),(128,3,'updated','intake',3,'Updated intake \'Intake I\'',NULL,'127.0.0.1','2026-11-02 20:59:03','2026-11-02 20:59:03'),(129,3,'updated','intake',1,'Updated intake \'Intake II\'',NULL,'127.0.0.1','2026-11-02 20:59:22','2026-11-02 20:59:22'),(130,3,'updated','intake',2,'Updated intake \'Intake III\'',NULL,'127.0.0.1','2026-11-02 20:59:46','2026-11-02 20:59:46'),(131,3,'created','employee',3,'Created employee \'Reaksmey Ky\'',NULL,'127.0.0.1','2026-11-02 21:11:47','2026-11-02 21:11:47'),(132,3,'updated','study_group',2,'Updated study group \'Computer Office-2026-G1\'',NULL,'127.0.0.1','2026-11-02 21:12:23','2026-11-02 21:12:23'),(133,3,'deleted','user',10,'Deleted staff account \'Reaksmey Ky\'',NULL,'127.0.0.1','2026-11-02 21:12:55','2026-11-02 21:12:55'),(134,3,'referred','opportunity_application',1,'Referred student #2 to \'Graphic Design\'',NULL,'127.0.0.1','2026-11-02 21:18:30','2026-11-02 21:18:30'),(135,3,'created','follow_up_task',1,'Created follow-up task \'Create CRM System\'',NULL,'127.0.0.1','2026-11-02 21:23:17','2026-11-02 21:23:17'),(136,3,'status_changed','follow_up_task',1,'Task \'Create CRM System\' → in_progress',NULL,'127.0.0.1','2026-11-02 21:23:25','2026-11-02 21:23:25'),(137,3,'created','communication_log',2,'Logged email interaction with Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-02 21:27:20','2026-11-02 21:27:20'),(138,3,'export','student',1,'Exported profile of Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-02 21:27:40','2026-11-02 21:27:40'),(139,3,'updated','communication_log',2,'Updated communication record',NULL,'127.0.0.1','2026-11-02 21:28:16','2026-11-02 21:28:16'),(140,3,'updated','communication_log',2,'Updated communication record',NULL,'127.0.0.1','2026-11-02 21:29:35','2026-11-02 21:29:35'),(141,3,'status_changed','follow_up_task',2,'Task \'Follow-up: Review task\' → in_progress',NULL,'127.0.0.1','2026-11-02 21:33:08','2026-11-02 21:33:08'),(142,3,'updated','follow_up_task',2,'Updated task \'Follow-up: Review task\'',NULL,'127.0.0.1','2026-11-02 21:33:30','2026-11-02 21:33:30'),(143,3,'created','payment',4,'Payment of 150 on INV-2026-0004',NULL,'127.0.0.1','2026-11-02 22:07:30','2026-11-02 22:07:30'),(144,3,'status_changed','invoice',4,'Invoice INV-2026-0004 → sent',NULL,'127.0.0.1','2026-11-02 22:07:34','2026-11-02 22:07:34'),(145,3,'created','payment',4,'Payment of 25 on INV-2026-0004',NULL,'127.0.0.1','2026-11-02 22:08:56','2026-11-02 22:08:56'),(146,3,'created','payment',3,'Payment of 400 on INV-2026-0003',NULL,'127.0.0.1','2026-11-03 00:18:18','2026-11-03 00:18:18'),(147,3,'checked_in','attendance',1,'Ratanak Khoeurn checked in',NULL,'127.0.0.1','2026-11-03 01:13:37','2026-11-03 01:13:37'),(148,3,'checked_out','attendance',1,'Ratanak Khoeurn checked out',NULL,'127.0.0.1','2026-11-03 01:13:58','2026-11-03 01:13:58'),(149,3,'checked_in','attendance',2,'Ratana Khoeurn checked in',NULL,'127.0.0.1','2026-11-03 01:14:22','2026-11-03 01:14:22'),(150,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-03 18:06:08','2026-11-03 18:06:08'),(151,3,'downloaded','student_document',1,'Downloaded document \'Graduation\'',NULL,'127.0.0.1','2026-11-03 18:08:56','2026-11-03 18:08:56'),(152,3,'checked_in','attendance',3,'Ratanak Khoeurn checked in',NULL,'127.0.0.1','2026-11-03 18:44:07','2026-11-03 18:44:07'),(153,3,'checked_out','attendance',3,'Ratanak Khoeurn checked out',NULL,'127.0.0.1','2026-11-03 18:44:21','2026-11-03 18:44:21'),(154,3,'checked_in','attendance',4,'Ratana Khoeurn checked in',NULL,'127.0.0.1','2026-11-03 18:45:01','2026-11-03 18:45:01'),(155,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-04 01:36:13','2026-11-04 01:36:13'),(156,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-04 18:12:16','2026-11-04 18:12:16'),(157,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 18:26:52','2026-11-04 18:26:52'),(158,3,'export','student',1,'Exported profile of Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 18:28:36','2026-11-04 18:28:36'),(159,3,'updated','student',2,'Updated student Ratana Khoeurn','[\"date_of_birth\",\"registered_date\"]','127.0.0.1','2026-11-04 18:39:25','2026-11-04 18:39:25'),(160,3,'updated','student',2,'Updated student Ratana Khoeurn','[\"date_of_birth\",\"registered_date\"]','127.0.0.1','2026-11-04 18:39:42','2026-11-04 18:39:42'),(161,3,'updated','student',2,'Enrolled Ratana Khoeurn in course Photography',NULL,'127.0.0.1','2026-11-04 18:56:26','2026-11-04 18:56:26'),(162,3,'created','invoice',6,'Created invoice INV-2026-0006',NULL,'127.0.0.1','2026-11-04 19:20:40','2026-11-04 19:20:40'),(163,3,'created','payment',6,'Payment of 80 on INV-2026-0006',NULL,'127.0.0.1','2026-11-04 19:24:50','2026-11-04 19:24:50'),(164,3,'status_changed','invoice',6,'Invoice INV-2026-0006 → sent',NULL,'127.0.0.1','2026-11-04 19:25:04','2026-11-04 19:25:04'),(165,3,'status_changed','invoice',5,'Invoice INV-2026-0005 → sent',NULL,'127.0.0.1','2026-11-04 19:26:01','2026-11-04 19:26:01'),(166,3,'created','payment',5,'Payment of 125 on INV-2026-0005',NULL,'127.0.0.1','2026-11-04 19:27:57','2026-11-04 19:27:57'),(167,3,'updated','student',1,'Updated student Ratanak Khoeurn','[\"name_khmer\",\"date_of_birth\",\"registered_date\",\"last_contacted_date\",\"next_followup_date\"]','127.0.0.1','2026-11-04 19:37:33','2026-11-04 19:37:33'),(168,3,'export','student',1,'Generated student card for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 19:38:06','2026-11-04 19:38:06'),(169,3,'submitted','student_change_request',1,'Submitted profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 19:41:36','2026-11-04 19:41:36'),(170,3,'approved','student_change_request',1,'Approved profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 19:42:40','2026-11-04 19:42:40'),(171,3,'submitted','student_change_request',2,'Submitted profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 19:58:46','2026-11-04 19:58:46'),(172,3,'approved','student_change_request',2,'Approved profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 19:59:15','2026-11-04 19:59:15'),(173,3,'submitted','student_change_request',3,'Submitted profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 20:37:47','2026-11-04 20:37:47'),(174,3,'approved','student_change_request',3,'Approved profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-04 20:37:56','2026-11-04 20:37:56'),(175,3,'created','payment',5,'Payment of 100 on INV-2026-0005',NULL,'127.0.0.1','2026-11-04 20:50:46','2026-11-04 20:50:46'),(176,3,'created','payment',5,'Payment of 25 on INV-2026-0005',NULL,'127.0.0.1','2026-11-04 20:58:22','2026-11-04 20:58:22'),(177,3,'created','payment',6,'Payment of 80 on INV-2026-0006',NULL,'127.0.0.1','2026-11-04 21:00:31','2026-11-04 21:00:31'),(178,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-05 01:02:55','2026-11-05 01:02:55'),(179,3,'checked_in','attendance',5,'Ratanak Khoeurn checked in',NULL,'127.0.0.1','2026-11-05 01:03:39','2026-11-05 01:03:39'),(180,3,'checked_in','attendance',6,'Ratana Khoeurn checked in',NULL,'127.0.0.1','2026-11-05 01:04:54','2026-11-05 01:04:54'),(181,3,'created','school_club',2,'Created school club \'Coding Club\'',NULL,'127.0.0.1','2026-11-05 01:11:36','2026-11-05 01:11:36'),(182,3,'created','employee',4,'Created employee \'RATANAK KHOEURN\'',NULL,'127.0.0.1','2026-11-05 01:58:36','2026-11-05 01:58:36'),(183,3,'checked_out','attendance',5,'Ratanak Khoeurn checked out',NULL,'127.0.0.1','2026-11-05 02:24:45','2026-11-05 02:24:45'),(184,3,'checked_out','attendance',6,'Ratana Khoeurn checked out',NULL,'127.0.0.1','2026-11-05 02:24:59','2026-11-05 02:24:59'),(185,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-05 18:01:56','2026-11-05 18:01:56'),(186,3,'checked_in','attendance',7,'Ratana Khoeurn checked in',NULL,'127.0.0.1','2026-11-05 18:02:18','2026-11-05 18:02:18'),(187,3,'checked_in','attendance',8,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-05 18:15:32','2026-11-05 18:15:32'),(188,3,'checked_out','attendance',8,'RATANAK KHOEURN checked out',NULL,'127.0.0.1','2026-11-05 18:15:32','2026-11-05 18:15:32'),(189,3,'updated','employee',4,'Updated employee \'RATANAK KHOEURN\'',NULL,'127.0.0.1','2026-11-05 18:18:56','2026-11-05 18:18:56'),(190,3,'updated','employee',2,'Updated employee \'Sreyroth Sok\'',NULL,'127.0.0.1','2026-11-05 18:19:22','2026-11-05 18:19:22'),(191,3,'updated','employee',1,'Updated employee \'Vandet Noun\'',NULL,'127.0.0.1','2026-11-05 18:19:36','2026-11-05 18:19:36'),(192,3,'created','position',7,'Created position \'Humance Resource\'',NULL,'127.0.0.1','2026-11-05 18:42:26','2026-11-05 18:42:26'),(193,3,'updated','employee',1,'Updated employee \'Vandet Noun\'',NULL,'127.0.0.1','2026-11-05 18:42:42','2026-11-05 18:42:42'),(194,3,'updated','employee',2,'Updated employee \'Sreyroth Sok\'',NULL,'127.0.0.1','2026-11-05 18:42:57','2026-11-05 18:42:57'),(195,3,'checked_in','attendance',9,'Sreyroth Sok checked in',NULL,'127.0.0.1','2026-11-05 18:46:07','2026-11-05 18:46:07'),(196,3,'checked_out','attendance',7,'Ratana Khoeurn checked out',NULL,'127.0.0.1','2026-11-05 18:47:29','2026-11-05 18:47:29'),(197,3,'checked_in','attendance',1,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-05 19:01:46','2026-11-05 19:01:46'),(204,3,'checked_out','attendance',1,'RATANAK KHOEURN checked out',NULL,'127.0.0.1','2026-11-05 19:22:07','2026-11-05 19:22:07'),(206,3,'checked_in','attendance',1,'Ratana Khoeurn checked in',NULL,'127.0.0.1','2026-11-06 02:29:17','2026-11-06 02:29:17'),(210,3,'export','attendance',NULL,'Exported attendance to CSV (2026-11-06)',NULL,'127.0.0.1','2026-11-06 02:34:25','2026-11-06 02:34:25'),(217,3,'export','attendance',NULL,'Exported attendance to CSV (2026-11-06_to_2026-11-06)',NULL,'127.0.0.1','2026-11-06 02:42:58','2026-11-06 02:42:58'),(218,3,'checked_out','attendance',1,'Ratana Khoeurn checked out',NULL,'127.0.0.1','2026-11-06 02:43:36','2026-11-06 02:43:36'),(219,3,'export','attendance',NULL,'Exported attendance to CSV (2026-11-06_to_2026-11-06)',NULL,'127.0.0.1','2026-11-06 02:43:42','2026-11-06 02:43:42'),(220,3,'updated','school_club',2,'Updated school club \'Coding Club\'',NULL,'127.0.0.1','2026-11-06 02:57:58','2026-11-06 02:57:58'),(221,3,'updated','school_club',2,'Updated school club \'Coding Club\'',NULL,'127.0.0.1','2026-11-06 02:58:24','2026-11-06 02:58:24'),(222,3,'export','student',1,'Exported profile of Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-06 03:56:15','2026-11-06 03:56:15'),(223,3,'updated','study_group',1,'Updated study group \'Computer Skill-2026-G1\'',NULL,'127.0.0.1','2026-11-06 03:59:22','2026-11-06 03:59:22'),(224,3,'submitted','student_change_request',4,'Submitted profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 04:00:33','2026-11-06 04:00:33'),(225,3,'approved','student_change_request',4,'Approved profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 04:00:41','2026-11-06 04:00:41'),(226,3,'submitted','student_change_request',5,'Submitted profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 04:02:48','2026-11-06 04:02:48'),(227,3,'approved','student_change_request',5,'Approved profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 04:02:53','2026-11-06 04:02:53'),(228,3,'updated','study_group',2,'Updated study group \'Computer Skill-2026-G1\'',NULL,'127.0.0.1','2026-11-06 04:37:55','2026-11-06 04:37:55'),(229,3,'import','student',NULL,'Imported 2 students from CSV (0 skipped)',NULL,'127.0.0.1','2026-11-06 04:38:39','2026-11-06 04:38:39'),(230,3,'submitted','student_change_request',6,'Submitted profile changes for Chan Bopha',NULL,'127.0.0.1','2026-11-06 04:40:46','2026-11-06 04:40:46'),(231,3,'approved','student_change_request',6,'Approved profile changes for Chan Bopha',NULL,'127.0.0.1','2026-11-06 04:40:52','2026-11-06 04:40:52'),(233,1,'submitted','student_change_request',7,'Submitted profile changes for Sok Dara',NULL,'127.0.0.1','2026-11-06 04:54:34','2026-11-06 04:54:34'),(234,1,'approved','student_change_request',7,'Approved profile changes for Sok Dara',NULL,'127.0.0.1','2026-11-06 04:54:34','2026-11-06 04:54:34'),(235,3,'created','study_group',3,'Created study group \'Graphic Design-2026-G1\'',NULL,'127.0.0.1','2026-11-06 05:00:51','2026-11-06 05:00:51'),(236,3,'updated','batch',2,'Updated batch \'Graphic Design — Batch 2026-B\'',NULL,'127.0.0.1','2026-11-06 05:02:21','2026-11-06 05:02:21'),(238,3,'submitted','student_change_request',8,'Submitted profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 06:24:26','2026-11-06 06:24:26'),(239,3,'approved','student_change_request',8,'Approved profile changes for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 06:24:32','2026-11-06 06:24:32'),(241,3,'import','student',NULL,'Imported 0 students from CSV (2 skipped)',NULL,'127.0.0.1','2026-11-06 06:34:50','2026-11-06 06:34:50'),(242,1,'export','student',2,'Exported profile of Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 06:42:28','2026-11-06 06:42:28'),(243,3,'updated','student',2,'Removed course enrollment Microsoft Office for Ratana Khoeurn',NULL,'127.0.0.1','2026-11-06 06:44:00','2026-11-06 06:44:00'),(244,3,'checked_in','attendance',3,'Ratanak Khoeurn checked in',NULL,'127.0.0.1','2026-11-06 06:49:17','2026-11-06 06:49:17'),(245,3,'checked_in','attendance',4,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-06 06:51:58','2026-11-06 06:51:58'),(246,3,'checked_in','attendance',1,'Sreyroth Sok checked in',NULL,'127.0.0.1','2026-11-06 06:57:51','2026-11-06 06:57:51'),(247,3,'checked_out','attendance',1,'Sreyroth Sok checked out',NULL,'127.0.0.1','2026-11-06 07:03:19','2026-11-06 07:03:19'),(248,3,'submitted','student_change_request',9,'Submitted profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-06 07:43:26','2026-11-06 07:43:26'),(249,3,'approved','student_change_request',9,'Approved profile changes for Ratanak Khoeurn',NULL,'127.0.0.1','2026-11-06 07:43:40','2026-11-06 07:43:40'),(250,3,'added','study_group_student',2,'Added student #4 to study group \'Computer Skill-2026-G1\'',NULL,'127.0.0.1','2026-11-06 07:46:01','2026-11-06 07:46:01'),(251,3,'updated','batch',1,'Updated batch \'Microsoft Office — Batch 2026-A\'',NULL,'127.0.0.1','2026-11-06 07:46:42','2026-11-06 07:46:42'),(254,3,'created','invoice',8,'Created invoice INV-2026-0007',NULL,'127.0.0.1','2026-11-06 08:23:05','2026-11-06 08:23:05'),(255,3,'created','payment',8,'Payment of 100 on INV-2026-0007',NULL,'127.0.0.1','2026-11-06 08:25:32','2026-11-06 08:25:32'),(256,3,'created','payment',8,'Payment of 75 on INV-2026-0007',NULL,'127.0.0.1','2026-11-06 08:27:33','2026-11-06 08:27:33'),(257,3,'status_changed','invoice',8,'Invoice INV-2026-0007 → sent',NULL,'127.0.0.1','2026-11-06 08:27:38','2026-11-06 08:27:38'),(258,3,'created','invoice',9,'Created invoice INV-2026-0008',NULL,'127.0.0.1','2026-11-06 08:33:23','2026-11-06 08:33:23'),(259,3,'created','payment',9,'Payment of 125 on INV-2026-0008',NULL,'127.0.0.1','2026-11-06 08:33:44','2026-11-06 08:33:44'),(260,3,'submitted','student_change_request',10,'Submitted profile changes for Sok Dara',NULL,'127.0.0.1','2026-11-06 08:43:52','2026-11-06 08:43:52'),(261,3,'approved','student_change_request',10,'Approved profile changes for Pov Saory',NULL,'127.0.0.1','2026-11-06 08:44:02','2026-11-06 08:44:02'),(262,3,'bulk_assigned','student',NULL,'Bulk assigned 1 students to staff #5',NULL,'127.0.0.1','2026-11-06 08:45:55','2026-11-06 08:45:55'),(263,3,'bulk_assigned','student',NULL,'Bulk assigned 1 students to staff #5',NULL,'127.0.0.1','2026-11-06 08:45:55','2026-11-06 08:45:55'),(264,3,'archived','student',4,'Archived student Chan Bopha',NULL,'127.0.0.1','2026-11-06 08:46:38','2026-11-06 08:46:38'),(265,3,'created','invoice',10,'Created invoice INV-2026-0009',NULL,'127.0.0.1','2026-11-06 08:51:29','2026-11-06 08:51:29'),(266,3,'created','payment',10,'Payment of 125 on INV-2026-0009',NULL,'127.0.0.1','2026-11-06 08:51:45','2026-11-06 08:51:45'),(267,3,'status_changed','follow_up_task',2,'Task \'Follow-up: Review task\' → completed',NULL,'127.0.0.1','2026-11-06 09:54:28','2026-11-06 09:54:28'),(272,3,'checked_in','attendance',1,'Sreyroth Sok checked in',NULL,'127.0.0.1','2026-11-06 10:01:44','2026-11-06 10:01:44'),(273,3,'checked_out','attendance',1,'Sreyroth Sok checked out',NULL,'127.0.0.1','2026-11-06 10:01:56','2026-11-06 10:01:56'),(274,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 01:22:51','2026-11-10 01:22:51'),(275,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-11-10 01:23:04','2026-11-10 01:23:04'),(276,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 01:30:19','2026-11-10 01:30:19'),(277,3,'checked_in','attendance',2,'Ratanak Khoeurn checked in',NULL,'127.0.0.1','2026-11-10 01:43:20','2026-11-10 01:43:20'),(308,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-11-10 02:03:20','2026-11-10 02:03:20'),(309,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 02:04:20','2026-11-10 02:04:20'),(310,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-11-10 02:04:22','2026-11-10 02:04:22'),(311,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 02:04:29','2026-11-10 02:04:29'),(312,3,'updated','user',5,'Updated staff account \'Sereykosal Ken\'',NULL,'127.0.0.1','2026-11-10 02:05:19','2026-11-10 02:05:19'),(313,3,'logout','user',3,'Ratanak Khoeurn logged out',NULL,'127.0.0.1','2026-11-10 02:05:30','2026-11-10 02:05:30'),(314,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 02:06:37','2026-11-10 02:06:37'),(315,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-10 06:22:00','2026-11-10 06:22:00'),(316,3,'checked_out','attendance',2,'Ratanak Khoeurn checked out',NULL,'127.0.0.1','2026-11-10 06:58:12','2026-11-10 06:58:12'),(317,3,'created','position',8,'Created position \'General Management\'',NULL,'127.0.0.1','2026-11-10 08:51:25','2026-11-10 08:51:25'),(318,3,'updated','position',8,'Updated position \'General Management\'',NULL,'127.0.0.1','2026-11-10 08:51:39','2026-11-10 08:51:39'),(319,3,'updated','position',1,'Updated position \'Director\'',NULL,'127.0.0.1','2026-11-10 08:51:48','2026-11-10 08:51:48'),(320,3,'deleted','employee',2,'Deleted employee #2',NULL,'127.0.0.1','2026-11-10 08:52:02','2026-11-10 08:52:02'),(321,3,'created','employee',5,'Created employee \'Kakada Horn\'',NULL,'127.0.0.1','2026-11-10 08:55:46','2026-11-10 08:55:46'),(322,3,'updated','employee',5,'Updated employee \'Kakada Horn\'',NULL,'127.0.0.1','2026-11-10 08:55:59','2026-11-10 08:55:59'),(323,3,'updated','employee',1,'Updated employee \'Vandet Noun\'',NULL,'127.0.0.1','2026-11-10 08:56:32','2026-11-10 08:56:32'),(324,3,'updated','employee',5,'Updated employee \'Kakada Horn\'',NULL,'127.0.0.1','2026-11-10 08:56:43','2026-11-10 08:56:43'),(325,3,'updated','employee',4,'Updated employee \'RATANAK KHOEURN\'',NULL,'127.0.0.1','2026-11-10 08:56:54','2026-11-10 08:56:54'),(326,3,'created','employee',6,'Created employee \'Doungchai Pha\'',NULL,'127.0.0.1','2026-11-10 08:58:44','2026-11-10 08:58:44'),(327,3,'created','position',9,'Created position \'Marketing Officer\'',NULL,'127.0.0.1','2026-11-10 08:59:52','2026-11-10 08:59:52'),(328,3,'created','employee',7,'Created employee \'Sereykosal Ken\'',NULL,'127.0.0.1','2026-11-10 09:01:12','2026-11-10 09:01:12'),(329,3,'created','employee',8,'Created employee \'Si Ey Sor\'',NULL,'127.0.0.1','2026-11-10 09:02:48','2026-11-10 09:02:48'),(330,3,'created','position',10,'Created position \'Graphic Designer\'',NULL,'127.0.0.1','2026-11-10 09:04:05','2026-11-10 09:04:05'),(331,3,'updated','employee',6,'Updated employee \'Doungchai Pha\'',NULL,'127.0.0.1','2026-11-10 09:04:35','2026-11-10 09:04:35'),(332,3,'updated','employee',8,'Updated employee \'Si Ey Sor\'',NULL,'127.0.0.1','2026-11-10 09:04:49','2026-11-10 09:04:49'),(333,3,'created','employee',9,'Created employee \'Sunnara Ruet\'',NULL,'127.0.0.1','2026-11-10 09:06:47','2026-11-10 09:06:47'),(334,3,'created','employee',10,'Created employee \'Sophara Khan\'',NULL,'127.0.0.1','2026-11-10 09:08:38','2026-11-10 09:08:38'),(335,3,'created','employee',11,'Created employee \'Sakmai Dorn\'',NULL,'127.0.0.1','2026-11-10 09:10:00','2026-11-10 09:10:00'),(336,3,'checked_in','attendance',1,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-10 09:11:50','2026-11-10 09:11:50'),(340,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-11 01:12:46','2026-11-11 01:12:46'),(355,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as excel',NULL,'127.0.0.1','2026-11-11 01:20:49','2026-11-11 01:20:49'),(356,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:00','2026-11-11 01:22:00'),(357,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:02','2026-11-11 01:22:02'),(358,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:02','2026-11-11 01:22:02'),(359,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:03','2026-11-11 01:22:03'),(360,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:03','2026-11-11 01:22:03'),(361,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:04','2026-11-11 01:22:04'),(362,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as pdf',NULL,'127.0.0.1','2026-11-11 01:22:04','2026-11-11 01:22:04'),(363,3,'export','report',NULL,'Exported report \'Attendance Detail (per day)\' as excel',NULL,'127.0.0.1','2026-11-11 01:23:15','2026-11-11 01:23:15'),(364,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-11 09:18:41','2026-11-11 09:18:41'),(365,3,'checked_in','attendance',18,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-11 09:22:30','2026-11-11 09:22:30'),(366,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-13 01:25:49','2026-11-13 01:25:49'),(367,3,'checked_in','attendance',19,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-13 01:26:21','2026-11-13 01:26:21'),(368,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-13 06:00:21','2026-11-13 06:00:21'),(369,3,'updated','event_registration',7,'Registration status → attended',NULL,'127.0.0.1','2026-11-13 06:56:21','2026-11-13 06:56:21'),(370,3,'updated','student',1,'Enrolled Ratanak Khoeurn in course Graphic Design',NULL,'127.0.0.1','2026-11-13 07:06:49','2026-11-13 07:06:49'),(371,3,'created','payment',11,'Payment of 200 on INV-2026-0010',NULL,'127.0.0.1','2026-11-13 07:07:54','2026-11-13 07:07:54'),(372,3,'status_changed','invoice',11,'Invoice INV-2026-0010 → sent',NULL,'127.0.0.1','2026-11-13 07:07:57','2026-11-13 07:07:57'),(373,3,'updated','invoice',11,'Updated invoice INV-2026-0010',NULL,'127.0.0.1','2026-11-13 07:08:53','2026-11-13 07:08:53'),(374,3,'updated','invoice',8,'Updated invoice INV-2026-0007',NULL,'127.0.0.1','2026-11-13 07:09:33','2026-11-13 07:09:33'),(375,3,'deleted','payment',8,'Deleted payment on INV-2026-0007',NULL,'127.0.0.1','2026-11-13 07:10:38','2026-11-13 07:10:38'),(376,3,'deleted','payment',8,'Deleted payment on INV-2026-0007',NULL,'127.0.0.1','2026-11-13 07:10:41','2026-11-13 07:10:41'),(377,3,'created','payment',8,'Payment of 140 on INV-2026-0007',NULL,'127.0.0.1','2026-11-13 07:10:51','2026-11-13 07:10:51'),(378,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-17 01:42:25','2026-11-17 01:42:25'),(379,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-17 02:10:51','2026-11-17 02:10:51'),(380,3,'checked_in','attendance',20,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-17 02:11:34','2026-11-17 02:11:34'),(383,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 02:30:19','2026-11-17 02:30:19'),(384,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Contacted -> Not Contacted',NULL,'127.0.0.1','2026-11-17 02:30:21','2026-11-17 02:30:21'),(385,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 02:30:26','2026-11-17 02:30:26'),(386,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Contacted -> Not Contacted',NULL,'127.0.0.1','2026-11-17 02:30:27','2026-11-17 02:30:27'),(387,3,'update','student',2,'Moved Ratana Khoeurn (STU-2026-0002) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 02:30:29','2026-11-17 02:30:29'),(388,3,'update','student',2,'Moved Ratana Khoeurn (STU-2026-0002) on pipeline: Contacted -> Not Contacted',NULL,'127.0.0.1','2026-11-17 02:30:30','2026-11-17 02:30:30'),(389,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: In Progress -> Enrolled',NULL,'127.0.0.1','2026-11-17 02:30:35','2026-11-17 02:30:35'),(390,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: Enrolled -> In Progress',NULL,'127.0.0.1','2026-11-17 02:30:36','2026-11-17 02:30:36'),(401,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-17 02:39:12','2026-11-17 02:39:12'),(402,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 02:39:48','2026-11-17 02:39:48'),(403,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Contacted -> Not Contacted',NULL,'127.0.0.1','2026-11-17 02:53:23','2026-11-17 02:53:23'),(404,3,'update','student',2,'Moved Ratana Khoeurn (STU-2026-0002) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 02:53:24','2026-11-17 02:53:24'),(405,3,'update','student',2,'Moved Ratana Khoeurn (STU-2026-0002) on pipeline: Contacted -> Not Contacted',NULL,'127.0.0.1','2026-11-17 02:53:25','2026-11-17 02:53:25'),(406,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: In Progress -> Enrolled',NULL,'127.0.0.1','2026-11-17 02:53:37','2026-11-17 02:53:37'),(407,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: Enrolled -> Unreachable',NULL,'127.0.0.1','2026-11-17 02:53:38','2026-11-17 02:53:38'),(408,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: Unreachable -> Enrolled',NULL,'127.0.0.1','2026-11-17 02:53:39','2026-11-17 02:53:39'),(409,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: Enrolled -> In Progress',NULL,'127.0.0.1','2026-11-17 02:53:40','2026-11-17 02:53:40'),(410,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: In Progress -> Contacted',NULL,'127.0.0.1','2026-11-17 02:53:41','2026-11-17 02:53:41'),(411,3,'update','student',1,'Moved Ratanak Khoeurn (STU-2026-0001) on pipeline: Contacted -> In Progress',NULL,'127.0.0.1','2026-11-17 02:53:42','2026-11-17 02:53:42'),(453,3,'update','student',3,'Moved Pov Saory (STU-2026-0003) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 04:00:26','2026-11-17 04:00:26'),(454,3,'update','student',2,'Moved Ratana Khoeurn (STU-2026-0002) on pipeline: Not Contacted -> Contacted',NULL,'127.0.0.1','2026-11-17 04:00:37','2026-11-17 04:00:37'),(455,3,'created','outreach_campaign',24,'Created outreach campaign \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-11-17 04:03:31','2026-11-17 04:03:31'),(456,3,'updated','outreach_campaign',24,'Built audience for \'Ratanak Khoeurn\': 0 added, 0 already present',NULL,'127.0.0.1','2026-11-17 04:03:46','2026-11-17 04:03:46'),(457,3,'updated','alumni_event',1,'Updated event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-17 04:06:13','2026-11-17 04:06:13'),(458,3,'registered','event_registration',1,'Registered student #3 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-17 04:06:26','2026-11-17 04:06:26'),(459,3,'updated','outreach_campaign',24,'Built audience for \'Ratanak Khoeurn\': 0 added, 0 already present',NULL,'127.0.0.1','2026-11-17 04:06:56','2026-11-17 04:06:56'),(460,3,'updated','event_registration',9,'Registration status → attended',NULL,'127.0.0.1','2026-11-17 04:09:04','2026-11-17 04:09:04'),(461,3,'updated','outreach_campaign',24,'Built audience for \'Ratanak Khoeurn\': 0 added, 0 already present',NULL,'127.0.0.1','2026-11-17 04:09:21','2026-11-17 04:09:21'),(462,3,'created','follow_up_task',3,'Created follow-up task \'test\'',NULL,'127.0.0.1','2026-11-17 04:09:48','2026-11-17 04:09:48'),(463,3,'deleted','follow_up_task',3,'Deleted task \'test\'',NULL,'127.0.0.1','2026-11-17 04:10:10','2026-11-17 04:10:10'),(467,3,'created','communication_log',11,'Logged facebook interaction with Pov Saory',NULL,'127.0.0.1','2026-11-17 04:13:28','2026-11-17 04:13:28'),(470,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-17 04:37:34','2026-11-17 04:37:34'),(471,3,'created','outreach_lead',35,'Added Ratanak Khoeurn to campaign \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-11-17 04:47:30','2026-11-17 04:47:30'),(472,3,'deleted','outreach_lead',35,'Removed Ratanak Khoeurn from campaign \"Ratanak Khoeurn\"',NULL,'127.0.0.1','2026-11-17 04:47:48','2026-11-17 04:47:48'),(473,3,'updated','event_registration',9,'Registration status → no_show',NULL,'127.0.0.1','2026-11-17 04:49:44','2026-11-17 04:49:44'),(474,3,'updated','event_registration',9,'Registration status → no_show',NULL,'127.0.0.1','2026-11-17 04:49:44','2026-11-17 04:49:44'),(475,3,'updated','event_registration',9,'Registration status → attended',NULL,'127.0.0.1','2026-11-17 04:49:49','2026-11-17 04:49:49'),(476,3,'created','outreach_lead',39,'Added Pov Saory to campaign \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-11-17 04:50:16','2026-11-17 04:50:16'),(477,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": To Contact -> Contacted',NULL,'127.0.0.1','2026-11-17 04:58:07','2026-11-17 04:58:07'),(478,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Contacted -> To Contact',NULL,'127.0.0.1','2026-11-17 04:58:09','2026-11-17 04:58:09'),(479,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": To Contact -> Contacted',NULL,'127.0.0.1','2026-11-17 04:58:10','2026-11-17 04:58:10'),(480,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Contacted -> No Answer',NULL,'127.0.0.1','2026-11-17 04:58:10','2026-11-17 04:58:10'),(481,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": No Answer -> Interested',NULL,'127.0.0.1','2026-11-17 04:58:11','2026-11-17 04:58:11'),(482,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Interested -> Declined',NULL,'127.0.0.1','2026-11-17 04:58:39','2026-11-17 04:58:39'),(483,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Declined -> Interested',NULL,'127.0.0.1','2026-11-17 04:58:43','2026-11-17 04:58:43'),(484,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Interested -> No Answer',NULL,'127.0.0.1','2026-11-17 04:58:46','2026-11-17 04:58:46'),(485,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": No Answer -> Contacted',NULL,'127.0.0.1','2026-11-17 04:58:46','2026-11-17 04:58:46'),(486,3,'created','communication_log',12,'Recorded phone_call contact with Pov Saory on campaign \"Ratanak Khoeurn\"',NULL,'127.0.0.1','2026-11-17 04:58:54','2026-11-17 04:58:54'),(488,3,'created','communication_log',13,'Recorded phone_call contact with Pov Saory on campaign \"Ratanak Khoeurn\"',NULL,'127.0.0.1','2026-11-17 05:04:35','2026-11-17 05:04:35'),(489,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Contacted -> No Answer',NULL,'127.0.0.1','2026-11-17 05:04:45','2026-11-17 05:04:45'),(490,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": No Answer -> Interested',NULL,'127.0.0.1','2026-11-17 05:04:46','2026-11-17 05:04:46'),(492,3,'updated','outreach_campaign',24,'Built audience for \'Ratanak Khoeurn\': 0 added, 0 already present',NULL,'127.0.0.1','2026-11-17 05:05:01','2026-11-17 05:05:01'),(493,3,'registered','event_registration',1,'Registered student #3 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-17 05:05:33','2026-11-17 05:05:33'),(494,3,'updated','event_registration',10,'Registration status → attended',NULL,'127.0.0.1','2026-11-17 05:05:41','2026-11-17 05:05:41'),(495,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Registered -> Interested',NULL,'127.0.0.1','2026-11-17 05:05:54','2026-11-17 05:05:54'),(496,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Registered -> Declined',NULL,'127.0.0.1','2026-11-17 05:06:06','2026-11-17 05:06:06'),(497,3,'update','outreach_lead',39,'Moved Pov Saory on campaign \"Ratanak Khoeurn\": Declined -> Interested',NULL,'127.0.0.1','2026-11-17 05:06:17','2026-11-17 05:06:17'),(500,3,'created','outreach_lead',24,'Added 1 student(s) to outreach campaign \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-11-17 05:49:51','2026-11-17 05:49:51'),(501,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": To Contact -> Contacted',NULL,'127.0.0.1','2026-11-17 05:52:34','2026-11-17 05:52:34'),(502,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": Contacted -> No Answer',NULL,'127.0.0.1','2026-11-17 05:52:34','2026-11-17 05:52:34'),(503,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": No Answer -> Interested',NULL,'127.0.0.1','2026-11-17 05:52:36','2026-11-17 05:52:36'),(504,3,'created','outreach_lead',24,'Added 1 student(s) to outreach campaign \'Ratanak Khoeurn\'',NULL,'127.0.0.1','2026-11-17 06:04:22','2026-11-17 06:04:22'),(505,3,'created','communication_log',14,'Logged phone_call interaction with Ratana Khoeurn',NULL,'127.0.0.1','2026-11-17 06:05:45','2026-11-17 06:05:45'),(506,3,'registered','event_registration',1,'Registered student #2 for event \'Water Festiwal\'',NULL,'127.0.0.1','2026-11-17 06:05:56','2026-11-17 06:05:56'),(507,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": Registered -> To Contact',NULL,'127.0.0.1','2026-11-17 06:06:22','2026-11-17 06:06:22'),(508,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": To Contact -> Contacted',NULL,'127.0.0.1','2026-11-17 06:06:27','2026-11-17 06:06:27'),(509,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": Contacted -> No Answer',NULL,'127.0.0.1','2026-11-17 06:06:27','2026-11-17 06:06:27'),(510,3,'update','outreach_lead',49,'Moved Ratana Khoeurn on campaign \"Ratanak Khoeurn\": No Answer -> Interested',NULL,'127.0.0.1','2026-11-17 06:06:28','2026-11-17 06:06:28'),(511,3,'updated','event_registration',11,'Registration status → attended',NULL,'127.0.0.1','2026-11-17 06:06:53','2026-11-17 06:06:53'),(512,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-17 08:46:06','2026-11-17 08:46:06'),(513,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-18 01:21:10','2026-11-18 01:21:10'),(514,3,'checked_in','attendance',21,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-18 01:21:30','2026-11-18 01:21:30'),(515,3,'checked_out','attendance',21,'RATANAK KHOEURN checked out',NULL,'127.0.0.1','2026-11-18 01:21:44','2026-11-18 01:21:44'),(516,3,'login','user',3,'Ratanak Khoeurn logged in',NULL,'127.0.0.1','2026-11-26 02:03:42','2026-11-26 02:03:42'),(517,3,'checked_in','attendance',22,'RATANAK KHOEURN checked in',NULL,'127.0.0.1','2026-11-26 02:06:31','2026-11-26 02:06:31'),(518,1,'registered','event_registration',2,'Registered student #1 for event \'ZZ mail test event\'',NULL,'127.0.0.1','2026-11-26 02:19:30','2026-11-26 02:19:30'),(519,1,'registered','event_registration',3,'Registered student #1 for event \'ZZ mail test event\'',NULL,'127.0.0.1','2026-11-26 02:20:19','2026-11-26 02:20:19'),(520,1,'registered','event_registration',4,'Registered student #1 for event \'ZZ mail test event\'',NULL,'127.0.0.1','2026-11-26 02:20:53','2026-11-26 02:20:53'),(521,1,'registered','event_registration',5,'Registered student #1 for event \'ZZ mail test event\'',NULL,'127.0.0.1','2026-11-26 02:21:08','2026-11-26 02:21:08');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(60) NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `intake_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `study_time` varchar(80) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'upcoming',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batches_code_unique` (`code`),
  KEY `batches_course_id_foreign` (`course_id`),
  KEY `batches_campus_id_foreign` (`campus_id`),
  KEY `batches_intake_id_foreign` (`intake_id`),
  KEY `batches_semester_id_foreign` (`semester_id`),
  CONSTRAINT `batches_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `batches_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `batches_intake_id_foreign` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `batches_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batches`
--

LOCK TABLES `batches` WRITE;
/*!40000 ALTER TABLE `batches` DISABLE KEYS */;
INSERT INTO `batches` VALUES (1,'Microsoft Office — Batch 2026-A','OFF-2026-A',1,1,1,1,'6:00PM - 7:30PM','2026-10-21','2027-01-21',25,'ongoing',NULL,'2026-10-20 21:45:46','2026-11-06 07:46:42'),(2,'Graphic Design — Batch 2026-B','GD-2026-B',2,1,3,1,'6:00PM - 7:30PM','2026-09-30','2027-02-21',30,'ongoing','Test','2026-10-20 21:45:46','2026-11-06 05:02:21'),(3,'Photography — Batch 2026-C','PHO-2026-C',3,1,3,1,NULL,'2026-08-21','2027-03-21',35,'completed',NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(4,'Video Editing — Batch 2026-D','VID-2026-D',4,1,1,2,NULL,'2026-07-21','2027-04-21',25,'ongoing',NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(5,'Robotics — Batch 2026-E','ROB-2026-E',5,1,2,1,NULL,'2026-06-21','2027-01-21',30,'upcoming',NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(6,'Artificial Intelligence — Batch 2026-F','AI-2026-F',6,1,3,2,NULL,'2026-05-21','2027-02-21',35,'completed',NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46');
/*!40000 ALTER TABLE `batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('student-crm-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1793952694),('student-crm-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1793952694;',1793952694);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campus_user`
--

DROP TABLE IF EXISTS `campus_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campus_user` (
  `campus_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`campus_id`,`user_id`),
  KEY `campus_user_user_id_foreign` (`user_id`),
  CONSTRAINT `campus_user_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campus_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campus_user`
--

LOCK TABLES `campus_user` WRITE;
/*!40000 ALTER TABLE `campus_user` DISABLE KEYS */;
INSERT INTO `campus_user` VALUES (1,5);
/*!40000 ALTER TABLE `campus_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campuses`
--

DROP TABLE IF EXISTS `campuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campuses_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campuses`
--

LOCK TABLES `campuses` WRITE;
/*!40000 ALTER TABLE `campuses` DISABLE KEYS */;
INSERT INTO `campuses` VALUES (1,'Toul Tompong Main Campus','C-TTP-01','St. 470, Sangkat Chomkar Mon1, Toul Tompong1, Phnom Penh, Cambodia','023456789','campuses/PVV9H9W4HQRa9nalXZF7gBW4iZDobHRtJs2EmOR5.jpg',1,'2026-10-20 21:45:46','2026-11-02 20:32:39',NULL),(2,'Chbar Ampov','C-CAP-01','Building #251, 1st Floor, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh','0962728383','campuses/VKNZPtdpMF4sQbmOMvqbHdGnhOLXyfehD4siWUx8.jpg',1,'2026-11-02 20:30:52','2026-11-02 20:30:52',NULL);
/*!40000 ALTER TABLE `campuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `communication_logs`
--

DROP TABLE IF EXISTS `communication_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communication_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `contact_datetime` datetime NOT NULL,
  `channel` varchar(50) NOT NULL,
  `direction` enum('incoming','outgoing') NOT NULL DEFAULT 'outgoing',
  `subject` varchar(255) NOT NULL,
  `message_summary` text DEFAULT NULL,
  `outcome` varchar(255) DEFAULT NULL,
  `followup_required` tinyint(1) NOT NULL DEFAULT 0,
  `next_followup_date` date DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communication_logs_student_id_foreign` (`student_id`),
  KEY `communication_logs_user_id_foreign` (`user_id`),
  KEY `communication_logs_contact_datetime_index` (`contact_datetime`),
  CONSTRAINT `communication_logs_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communication_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `communication_logs`
--

LOCK TABLES `communication_logs` WRITE;
/*!40000 ALTER TABLE `communication_logs` DISABLE KEYS */;
INSERT INTO `communication_logs` VALUES (1,1,3,'2026-10-21 07:16:00','telegram','incoming','Graphic Design',NULL,NULL,0,NULL,NULL,NULL,'2026-10-21 00:17:11','2026-10-21 00:17:11'),(2,1,3,'2026-11-03 11:32:00','email','incoming','Review task','Please review your tasks again before go back home',NULL,1,'2026-11-04','communications/r6babBI6OKufE0sh1G3yxHmWSTvswpI9hSxHyXNY.png','testing','2026-11-02 21:27:20','2026-11-02 21:29:35'),(11,3,3,'2026-11-17 11:11:00','facebook','incoming','Computer Office','Testing','Pickup',1,'2026-11-18','communications/WTLiScewIdRzmtGIJV4maDJooydAxuikph60aktT.jpg','testing','2026-11-17 04:13:28','2026-11-17 04:13:28'),(12,3,3,'2026-11-17 11:58:54','phone_call','outgoing','Outreach: Ratanak Khoeurn',NULL,'Contacted',0,NULL,NULL,NULL,'2026-11-17 04:58:54','2026-11-17 04:58:54'),(13,3,3,'2026-11-17 12:04:35','phone_call','outgoing','Outreach: Ratanak Khoeurn',NULL,'Contacted',0,NULL,NULL,NULL,'2026-11-17 05:04:35','2026-11-17 05:04:35'),(14,2,3,'2026-11-17 13:05:00','phone_call','outgoing','Web Developer','test','Pickup',1,'2026-11-18','communications/dFkIESyXRSFi5MtjEsFakjNVVHdlTTSbPsUrOKw9.jpg','testing','2026-11-17 06:05:45','2026-11-17 06:05:45');
/*!40000 ALTER TABLE `communication_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_categories`
--

DROP TABLE IF EXISTS `course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_kh` varchar(255) DEFAULT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `academic_year_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `name_import` varchar(255) DEFAULT NULL,
  `extra_course` tinyint(1) NOT NULL DEFAULT 0,
  `penalty` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_categories_code_unique` (`code`),
  KEY `course_categories_program_id_foreign` (`program_id`),
  KEY `course_categories_semester_id_foreign` (`semester_id`),
  CONSTRAINT `course_categories_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `course_categories_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_categories`
--

LOCK TABLES `course_categories` WRITE;
/*!40000 ALTER TABLE `course_categories` DISABLE KEYS */;
INSERT INTO `course_categories` VALUES (3,'TTP-LC-CS-01','Computer Skills','កុំព្យូទ័ររដ្ឋបាល',1,1,1,0,'Computer Skills',1,1,'2026-11-01 19:31:55','2026-11-02 02:07:59'),(5,'TTP-LC-DM-01','Digital Media','Digital Media',1,1,1,1,'Digital Media',1,1,'2026-11-02 02:06:51','2026-11-02 02:07:49');
/*!40000 ALTER TABLE `course_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_study_group`
--

DROP TABLE IF EXISTS `course_study_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_study_group` (
  `study_group_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`study_group_id`,`course_id`),
  KEY `course_study_group_course_id_foreign` (`course_id`),
  CONSTRAINT `course_study_group_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_study_group_study_group_id_foreign` FOREIGN KEY (`study_group_id`) REFERENCES `study_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_study_group`
--

LOCK TABLES `course_study_group` WRITE;
/*!40000 ALTER TABLE `course_study_group` DISABLE KEYS */;
INSERT INTO `course_study_group` VALUES (1,1,'2026-11-06 03:59:22','2026-11-06 03:59:22'),(2,1,'2026-11-02 20:07:34','2026-11-02 20:07:34'),(3,2,'2026-11-06 05:00:51','2026-11-06 05:00:51');
/*!40000 ALTER TABLE `course_study_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `course_category` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `certificate_text` text DEFAULT NULL,
  `duration_months` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_code_unique` (`code`),
  KEY `courses_program_id_course_category_index` (`program_id`,`course_category`),
  KEY `courses_category_id_foreign` (`category_id`),
  CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courses_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'Microsoft Office','OFF',1,3,'Compuster Skills','Introduction to Microsoft Office suite',NULL,2,1,'2026-10-20 21:45:46','2026-11-02 02:09:25',NULL),(2,'Graphic Design','GD',1,5,'Graphic Design Course','Visual and graphic design program',NULL,2,1,'2026-10-20 21:45:46','2026-11-02 02:09:12',NULL),(3,'Photography','PHO',NULL,NULL,NULL,'Introduction to photography techniques',NULL,2,1,'2026-10-20 21:45:46','2026-10-21 23:11:51',NULL),(4,'Video Editing','VID',NULL,NULL,NULL,'Introduction to video editing software',NULL,2,1,'2026-10-20 21:45:46','2026-10-20 21:45:46',NULL),(5,'Robotics','ROB',NULL,NULL,NULL,'Introduction to robotics and automation',NULL,3,1,'2026-10-20 21:45:46','2026-10-20 21:45:46',NULL),(6,'Artificial Intelligence','AI',NULL,NULL,NULL,'Introduction to artificial intelligence concepts',NULL,3,1,'2026-10-20 21:45:46','2026-10-20 21:45:46',NULL);
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `education_histories`
--

DROP TABLE IF EXISTS `education_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `education_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `institution` varchar(255) DEFAULT NULL,
  `degree_program` varchar(255) DEFAULT NULL,
  `major` varchar(255) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expected_graduation_date` date DEFAULT NULL,
  `scholarship_info` varchar(255) DEFAULT NULL,
  `further_education_plans` text DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `education_histories_student_id_foreign` (`student_id`),
  CONSTRAINT `education_histories_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `education_histories`
--

LOCK TABLES `education_histories` WRITE;
/*!40000 ALTER TABLE `education_histories` DISABLE KEYS */;
INSERT INTO `education_histories` VALUES (1,1,1,'University Of Puthisastra','Information Technology','Information Technology','Year 4','2025-12-01','2027-12-31','70%',NULL,'graduated',NULL,'2026-10-20 23:11:43','2026-11-04 19:59:15'),(2,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'graduated',NULL,'2026-11-02 19:00:55','2026-11-02 19:00:55'),(3,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:40:52','2026-11-06 04:40:52'),(5,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:54:34','2026-11-06 04:54:34');
/*!40000 ALTER TABLE `education_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_face_profiles`
--

DROP TABLE IF EXISTS `employee_face_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_face_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `embedding` longtext NOT NULL,
  `model_version` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `consent_obtained_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_face_profiles_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_face_profiles_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_face_profiles`
--

LOCK TABLES `employee_face_profiles` WRITE;
/*!40000 ALTER TABLE `employee_face_profiles` DISABLE KEYS */;
INSERT INTO `employee_face_profiles` VALUES (1,4,'eyJpdiI6Ik5ZWWdOOTdlTUxTdWd3ZkduWXBLQ3c9PSIsInZhbHVlIjoiK2d3UG93bEVJZjcrbnB0TGozeFRJZ1RuR3lnRVM4bG05WWIzRE9jeHJiTW9kNW5pMmlyRGVMOHdyS3VMaktwa0I3emFyc2NYS1hIWHA3cVpHS1pVSDRhWlRvNURKT1hoVW5yMnhEbWxDQVJmU3lwcFFUa3REYm5ENTdtM2VrSFlUMFRIcjlXekxLM0dsUEh5RWsxVWs0c2ozR0NyWE5CNlM1cWZIWFFqVVg2aC9RWlo4MDZuZ1pSWmcwRnNocnRGZG1TRDNEMGxORUhtS3g5emg4a1FkZmlXV3JUVnk2UFVNOUZ4MzNzc1g0NStkcmJvWnVZZitLa2NyT2lIdys5MnBKbDNjamsrSE1sZ3dWQk1ocFFPclZhQURmNWlwWXF0YVFReTdHYXZ5OVRlSEF6L0N0V3pPT3NrQUdrTlhFUXJLYjY1NFhnYWQzN2VRbVlUT0Q4MzBBTWUwUTZ2c2Z3ZUsxTmlPQmFCWENjRDBzQ2hKcllrZUZGaG5BQ1I2TkVzVDIxaUd2ZXByRkhvY1BPTVFTdDFUajJjTTRIMGhraWU2MUpvTnJLS3ZBY1Qzb1NoYWc5TUtCWENoQ3YxeUFqc0MyTkpyT3FrblhlVCtaM1FBWkQzK1crR0lXMVAzQ2pTTTlQRmk0UDNKQUFRSnYvL0ltRXU1TUw5WXk4TzFuNWZiRHFjMnppSmg1Zm1iakdoL0dYVVRzbjUxZWlFRlhwa1pCaTJVa2YybTdKcjZZdlhBU3RzL0xWSEp6N0ptY3RYSCsvcXA1dEt5Y2swRHUvVEw2UllHTjVRQ1dxVkQ5UGZtSHlRdjZmWHhHNlZXRVZSVlN1VWI4TDJhcERlK0tWVVdRMkc4OXlGN2YvZWs0UFFYTnovb1lscytHQVE1WDlOVW10cFVkZE5DRE9XajZjVFl2R3R3NGoyQ3ptM3hjWWk0cnpvQzBJbi9LbGdQeFV6enNCT2grZTUwblZCaWYyaVRYSTJNMmdLUHpYSERWbFdSaFJueVNPSms3SjNqelZvQ0dyUkRHMlJsY1YydFVJOFRMbjBpYXpSWWVlUGFsaEhUVmF4dldVczh2a0N4Vjg4UDFXUW5OV2EyNko5UmtJWm1Ca1dXZWovSThaWmxYWUlCWmJuSEZuWnFSb2hIWENnV1BHTEx0QkpINzJ4dWRFdVNiQlJjVVFkaUVVUHlHayttdzdVTjVsVkJxdURLZXA4OGxMVHhoNW8xSEdUeHJSM3BjT2FNV2FrNmlMeXpuckZEalUvMWxLMVdvNTg1Z011eExqMm8vRjNEQWZrZ2J3VEFDWUFOQkl2SGRzalRSMHRHblRnNytCN05PUWdGSlMzS040Y2VDbVE5d09hS25KNHBHWXdlQ0hRcWV1VW1yMjg2ZjdKLzhqcndlTGlUK3dzdUVzMERuZmtMZmdOZU5XUDJqQjZDK0h0ZG1VNVpacTdHQkh1a1BCanYzbmp6b1c3a3V1RmNVc1dzbEo0QnBqZzV0azB2UlFHSFNsRkNtaWZpUVNHZjBYVVBMNVBWajVVWnZiL3NNQzd1Qi80K3krWmVFNXd0TmF4d3ZYNXprd2dWKzMxOGxNK1RGUSthdVM4cHhobGQ1NW5CdUxHMGNXa3JRUllXWUZXNkoxYUFhaHpMT3N0L2tmSWFRT2JWQ093UStZM2dxMTFqdGF6R0FsOVREMGQyd29JS0IrMVpKckIwbnQ1YlhXbkFVOXdLNXFWZG9TVnVnWXN0RTViL2JPY1ZqWGEvV0FLSGkrVjE1K1FGdGFOS0h5bWtsd2hsV2FpK2pGWGVqRUVCZjZRWnFPYzhmcHVkU0Z4M3pRWW1KbTJDQmh6WGs3Y3dPY3Zhcnl5WW83WGpBS2ljNStmclIxZFJKQStKR0lya3JxTXFDUUMzRVdGS0J0T1Y4ZlFzTytyNXYvbkhSOHRmYTcrbHI2eFFPVGNXSHdrYnQwTEErRmRFaXYxN3JqSE9PUzNaNW5WQ1RKUHI5Qm9Sd0o3REJ4TDBKZTJJWDZIMFoweVNZZEVpdCtWc0hhT3JmUFBUOU1OMitJbmJkb1NKSk1iUjNpOENkdVJnbGE4eUdiNVhiYnlkMDhrSlIyWUdaT3haQlpwVTNWUjZ1ZXp3ZXNCY1ZaN2pjdk9HWUlEcEVXaEIrYTY5WDZHU0JoUXdqVHNtQ2xtU3RUN3VnVWhNVEdINm9OZkpxWmM4L0s2eENVaTVTK3lCL0d3ZXp0cWFyenE4STIyVy8wdmM4MFdKU2pUYUh4ZnI0eUFUOFYwMnZnQkpnS1NqM3cvdGVlc1ZrS2JBSVVyTzEvSUt4SzhldnppZ0llSXdwL1gzbTRReXZtbTArUGxiZWZhazZVWFdTQnVuS1ptcHlBcTNXQW1vZlJUV3ZzbUdMbFViOGx4SThwQllmdzhwdi9sczZ2aWlPbmhueVBQSEYvREVXL1pCTlRPM2lmaWNPdFNMS29zT3NQNkpIMk8yVmtHc0t3QU9jZDJIR2NNdVVtbUdIbmc5eUU2Qnc3eTJaOVBlcC9WaE5sZU5pVjk4bVU4TXI3VWZveGZtR1FuM2tIWmIvY0JFNllHVG4yZUJZc0tKR0pWanVzMGdKbTVwYWQyY1hqcmtKTmlzaGpyNU1wWnNTT3dlTzFZaXF1R1dqeEtjSWJKazFZcUpVMDRiY1FTMTJqZmFQdXcvTStWMFVYbWIrbHJpb3NGQUJKSjY2ZzVWMlpxNmpRWnFNRHJqRDEwNHNuV09VOS9EN2ZYeTYzSFRWZ0dOcGlSRlNKajFjUGNUd3pwZnB1M0JjN0ZmdGE1N1VuWnVXVDBqL3Z5YjJ4dy9lNElSSFFlaTdvUnhvVnRGQ2pSRlZxM2tvZ3pRRG03eTJDSG8xUHpGSE9YQVdRMFcyc1MxZ1FMbGZBdWpFMG1lSHZXdkkxdUpyMzcrV3NHc2dieVhGajMzUTNzTE9UUjdpTkpud0FvdVFSSzVMTnpmUDl0R2U4eVRXYlhJZ2N1MTQvd25PdnpKMXdEWHVhUjVReGJyVUZzNnpyRi82bzVyelFlK1R4NVR4V1VMUUNpVWtqMW95ZDZUSms1YzJvcFR3MUVZY2lIWGZxRUdRb0NydFY1K0o3Y2dvblRsWDhQa2lVUVYyaElRRzA3YWNvNTIvM2JCVzRKN2Z6SHMwbTl1MDVNbXU5U3RpZFh4RFRJcFc1d1NSUHk3VVJETU9mM2JwMThDTjNLNFBRbDNCTmoveTNFeFl5TVorNGUxc1U3Mytsa2hDZS9TTFNMVzAzZTJqcG9YcksvVGIzMEp2VjUvZVRiKzlSZ2lMZGxmWW9QWFQ4L1pJY0tPd21YbS9nTk9BVEJWWUxmNWxFdzNGeU5EVzRBMzV4U1JYRDZtYTBCVy9Bb0piNGd2bXFIQnNacFRzaW9kZk9LQmkrc3RsdFRhaWhZVXhwN2lscHZKQUNFWDhVanp2OHFXTWtjL1lQbXVPUVFaRDFYVVYrUDRuVkx1M3RZTnJaYVE0ckU3V256WWNLdjZxM0w5UmtmSHA0Q1pJcVVUSUx1ZWdrRmlHUnFuSjZxYk9LcjdxeUZWUHBab0R0RndpcFJRRG9kK0dCOW40MUpXSktLMjVwNzFzbDFXRkpiaXcyU2d3L2NabHZVSUhGYlo3SGpybzZjTUVySklpYWQwMldHYVBiSVVyT1FUOVNZa2pQU09US01kbmZVQ1pjMDBPVEFWK3k4VzNtY1doc2NvRitJMEF1TEhuU1BxQ1ZLWXRTdmM1cG1Eb0hXclVtSnR5S3hxZ2lDVFJvV3pzcnl4THZTcmplZ0dERXlHK0xYYUlGcVJqSFYzNFp3dHJDR1RjM3huRjBJeXFzdGFDRGp6ZDJrTnZsaXVzVW1rUGIxcDNPaHpjTXBrQnYvY3NoeFBUZ3ZDYnl5WVVOQUt1SVUvV1NQUi9EUlBkSWs1a1h1Zy9EZ0NJUEJqMElPbXZhenNoMUw2Rk5ha2RPemphZHl5Q0VhSko5bHNGNnNrbFhYemN6bzFDMTREVEY4TGdBSWx5RWdNUDNxREsyb1VBMGEyVGdISlVjeURRNXNmeVN0Qm5OZEZHN0wvbEJxSnMzU1ZXcklodlM1bm0wKzlpdHFoMFVDT3JuTFd1VVFXcUk2MGRJOWllYUxTMVZrTFRLSHAxTFFnUmpHMFRZWjRxY21YMWwwUGMvZzNxY0VQaWttaCtVdWhWOGZOSnE1cTdKS0drWUpNQmE4YmRWKy9OdVlmWHBvUUZDb1hCTXp4WmwyRVVreU5vdFNZUXJkSEVTbllQWS9hWnAyMGZBclZCbTgxWU4rTUVZTnF5SDRXNzlXekg4a2RRQ2cxZ21qOXoxSGpuWnpZb2k5a1BEUFJFWGJNZ3loME41cUpOTUp2L0twajhhSmV4UVdMTVhLaGVVYXh2U3hrNjNTeC9RUGd6blhGUUFRWXdlWWtPSHh4UGVPVklOYnNNSG8vZzBqdUd2RGNuMTIyYnVTZDlzcjlLVHZnUlk9IiwibWFjIjoiMWRmZTgwZGFmYmY0MzA4NDY4OTgzZTliNzU4NmQ4OWI3YmU2YzlkZGI5NzQyZjU1ZDIwNzVlZjBmY2I1ZTA3MCIsInRhZyI6IiJ9','opencv-sface-2021dec',1,'2026-11-10 09:11:15','2026-11-10 09:11:15','2026-11-10 09:11:15');
/*!40000 ALTER TABLE `employee_face_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(40) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employment_type` varchar(30) NOT NULL DEFAULT 'full_time',
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `salary` decimal(12,2) DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_position_id_foreign` (`position_id`),
  KEY `employees_campus_id_foreign` (`campus_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_status_index` (`status`),
  CONSTRAINT `employees_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'IM0002',1,7,1,'Vandet','Noun','employees/boW79JtQ9C1LVr6MxtZgLZanVvILEFA7HJ3zw9bC.png',NULL,NULL,'hr@itechmediakh.com','0965828662 / 092426205',NULL,'2026-04-21','full_time','active',400.00,NULL,NULL,1,'2026-10-20 21:45:46','2026-11-10 08:56:32'),(3,'T-001',3,4,1,'Reaksmey','Ky','employees/swgbn3tZSRnYuztwJ0USgPbn8saqw4z6ns9dmODu.jpg','male','1986-02-12','k.reaksmey@iteachmediakh.com','0973747342','Street 472','2026-01-01','part_time','active',200.00,'2026-12-31','Computer Office teacher',3,'2026-11-02 21:11:47','2026-11-02 21:11:47'),(4,'IM0003',3,6,1,'RATANAK','KHOEURN','employees/yrenOwzNMV6dsN2vBNMcIBjjUmfKXlh4MSO2duJo.jpg','male','2004-07-25','ratanak.khoeurn@student.passerellesnumeriques.org','0962728396','Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh','2025-10-20','full_time','active',300.00,'2027-12-31','IT Assistant',3,'2026-11-05 01:58:36','2026-11-10 08:56:54'),(5,'IM0001',2,8,1,'Kakada','Horn','employees/04XfhrqjCm1JEsdJyfgA0vXFCiM6k8BiblvHuUwQ.jpg','male','1988-07-02','h.kakada@itechmediakh.com','086897759','Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh',NULL,'full_time','active',NULL,NULL,'Mr.Kakada Horn is our general manager at I-Tech Media',3,'2026-11-10 08:55:46','2026-11-10 08:56:43'),(6,'IM0004',1,9,1,'Doungchai','Pha','employees/mGte9RNWaEybg4i2Hd08MEaKOlaG1BaJNZVRT6KF.png','male','2003-02-05','p.doungchai@itechmediakh.com','0973747342','BP 511, Phum Tropeang Chhuk (Borey Sorla) Sangtak, Street 371, Phnom Penh',NULL,'full_time','resigned',NULL,NULL,NULL,3,'2026-11-10 08:58:44','2026-11-10 09:04:35'),(7,'IM0005',5,2,1,'Sereykosal','Ken','employees/2lfVHSu6xO0nxCknKLH6FYFv4ofahvWfqPUgUjbn.png','female','2003-02-16','k.sereykosal@itechmediakh.com','0962728383','BP 511, Phum Tropeang Chhuk (Borey Sorla) Sangtak, Street 371, Phnom Penh',NULL,'full_time','active',NULL,NULL,NULL,3,'2026-11-10 09:01:12','2026-11-10 09:01:12'),(8,'IM0006',4,10,1,'Si Ey','Sor','employees/ruhbpgCdfuuZ6VdL8AORrLBaE6TowEact6VlwGJz.png','male','2003-04-25','s.siey@itechmediakh.com','0973747342','BP 511, Phum Tropeang Chhuk (Borey Sorla) Sangtak, Street 371, Phnom Penh',NULL,'full_time','resigned',NULL,NULL,NULL,3,'2026-11-10 09:02:48','2026-11-10 09:04:49'),(9,'IM0007',1,3,1,'Sunnara','Ruet','employees/v3C6N0eBlwt9nR8m1PR0ZsJAsUfJcErZ5RNygdGt.png','male','1996-05-03','r.sunnara@itechmediakh.com','0962728383','BP 511, Phum Tropeang Chhuk (Borey Sorla) Sangtak, Street 371, Phnom Penh','2026-11-10','full_time','active',NULL,'2027-12-31',NULL,3,'2026-11-10 09:06:47','2026-11-10 09:06:47'),(10,'IM0008',1,10,1,'Sophara','Khan','employees/eny76C0HxP5anlUjULOV8XvSE3IDNO2EDRuKgbNu.jpg','male','1999-12-22','k.sophara@itechmediakh.com','0962728323','អគារលេខ 251 ផ្លូវ 472 សង្កាត់ទួលទំពូងទី១','2026-11-02','full_time','active',NULL,'2027-12-31',NULL,3,'2026-11-10 09:08:38','2026-11-10 09:08:38'),(11,'IM0009',1,9,1,'Sakmai','Dorn','employees/DWHh6SHPAz0wcJvXLK7PPXwiVKqH3VjQ8XaO9Kgn.jpg','female','2003-05-22','d.sakmai@itechmediakh.com','09627283943','អគារលេខ 251 ផ្លូវ 472 សង្កាត់ទួលទំពូងទី១','2026-10-01','full_time','active',NULL,'2027-12-31',NULL,3,'2026-11-10 09:10:00','2026-11-10 09:10:00');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employment_histories`
--

DROP TABLE IF EXISTS `employment_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employment_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `company` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `work_phone` varchar(50) DEFAULT NULL,
  `work_email` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `salary_range` varchar(255) DEFAULT NULL,
  `employment_type` varchar(30) DEFAULT NULL,
  `employment_status` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employment_histories_student_id_foreign` (`student_id`),
  CONSTRAINT `employment_histories_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employment_histories`
--

LOCK TABLES `employment_histories` WRITE;
/*!40000 ALTER TABLE `employment_histories` DISABLE KEYS */;
INSERT INTO `employment_histories` VALUES (1,1,1,'I-Tech Media','IT Assistant','Information Technology','IT','អគារលេខ 251 ផ្លូវ 472 សង្កាត់ទួលទំពូងទី១','0962728396','k.ratanak@itechmediakh.com','2025-10-20',NULL,'300','full_time','employed',NULL,'2026-10-20 23:11:43','2026-10-20 23:11:43'),(2,2,1,'TL Express','Sale Manager','Delivery','Sale','k.ratana@tlexpress.com','0819997111',NULL,NULL,NULL,NULL,'full_time','employed',NULL,'2026-11-02 19:00:55','2026-11-02 19:00:55'),(3,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:40:52','2026-11-06 04:40:52'),(5,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:54:34','2026-11-06 04:54:34');
/*!40000 ALTER TABLE `employment_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_registrations`
--

DROP TABLE IF EXISTS `event_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alumni_event_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `status` enum('registered','attended','no_show','cancelled') NOT NULL DEFAULT 'registered',
  `attended_at` timestamp NULL DEFAULT NULL,
  `confirmation_sent_at` timestamp NULL DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `registered_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_registrations_alumni_event_id_student_id_unique` (`alumni_event_id`,`student_id`),
  KEY `event_registrations_student_id_foreign` (`student_id`),
  KEY `event_registrations_registered_by_foreign` (`registered_by`),
  CONSTRAINT `event_registrations_alumni_event_id_foreign` FOREIGN KEY (`alumni_event_id`) REFERENCES `alumni_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_registrations_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `event_registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_registrations`
--

LOCK TABLES `event_registrations` WRITE;
/*!40000 ALTER TABLE `event_registrations` DISABLE KEYS */;
INSERT INTO `event_registrations` VALUES (7,1,1,'attended','2026-11-13 06:56:21',NULL,NULL,3,'2026-11-01 21:47:26','2026-11-13 06:56:21'),(10,1,3,'attended','2026-11-17 05:05:41',NULL,NULL,3,'2026-11-17 05:05:33','2026-11-17 05:05:41'),(11,1,2,'attended','2026-11-17 06:06:53',NULL,NULL,3,'2026-11-17 06:05:56','2026-11-17 06:06:53');
/*!40000 ALTER TABLE `event_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structures`
--

DROP TABLE IF EXISTS `fee_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee_structures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `fee_type` varchar(30) NOT NULL DEFAULT 'tuition',
  `amount` decimal(10,2) NOT NULL,
  `frequency` varchar(30) NOT NULL DEFAULT 'one_time',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fee_structures_course_id_foreign` (`course_id`),
  KEY `fee_structures_campus_id_foreign` (`campus_id`),
  CONSTRAINT `fee_structures_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fee_structures_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structures`
--

LOCK TABLES `fee_structures` WRITE;
/*!40000 ALTER TABLE `fee_structures` DISABLE KEYS */;
INSERT INTO `fee_structures` VALUES (1,1,NULL,'Tuition — Microsoft Office','tuition',150.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(2,2,NULL,'Tuition — Graphic Design','tuition',200.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(3,3,NULL,'Tuition — Photography','tuition',250.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(4,4,NULL,'Tuition — Video Editing','tuition',300.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(5,5,NULL,'Tuition — Robotics','tuition',150.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(6,6,NULL,'Tuition — Artificial Intelligence','tuition',200.00,'per_semester',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(7,1,NULL,'Registration — Microsoft Office','registration',25.00,'one_time',1,'2026-10-20 21:45:46','2026-10-20 21:45:46');
/*!40000 ALTER TABLE `fee_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follow_up_tasks`
--

DROP TABLE IF EXISTS `follow_up_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follow_up_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `communication_log_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `due_date` date NOT NULL,
  `reminder_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_at` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `result` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follow_up_tasks_student_id_foreign` (`student_id`),
  KEY `follow_up_tasks_assigned_to_foreign` (`assigned_to`),
  KEY `follow_up_tasks_created_by_foreign` (`created_by`),
  KEY `follow_up_tasks_communication_log_id_foreign` (`communication_log_id`),
  KEY `follow_up_tasks_status_due_date_index` (`status`,`due_date`),
  CONSTRAINT `follow_up_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_up_tasks_communication_log_id_foreign` FOREIGN KEY (`communication_log_id`) REFERENCES `communication_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_up_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_up_tasks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follow_up_tasks`
--

LOCK TABLES `follow_up_tasks` WRITE;
/*!40000 ALTER TABLE `follow_up_tasks` DISABLE KEYS */;
INSERT INTO `follow_up_tasks` VALUES (1,1,3,3,NULL,'Create CRM System','Create a CRM System for I-Tech Media','high','2026-11-30',1,'2026-11-20 11:23:00','in_progress',NULL,NULL,'2026-11-02 21:23:17','2026-11-02 21:23:25'),(2,1,3,3,2,'Follow-up: Review task','Auto-created from communication on Nov 03, 2026.','medium','2026-11-04',1,'2026-11-03 11:34:00','completed',NULL,'2026-11-06 16:54:28','2026-11-02 21:27:20','2026-11-06 09:54:28'),(4,3,5,3,11,'Follow-up: Computer Office','Auto-created from communication on Nov 17, 2026.','medium','2026-11-18',0,NULL,'pending',NULL,NULL,'2026-11-17 04:13:28','2026-11-17 04:13:28'),(5,2,5,3,14,'Follow-up: Web Developer','Auto-created from communication on Nov 17, 2026.','medium','2026-11-18',0,NULL,'pending',NULL,NULL,'2026-11-17 06:05:45','2026-11-17 06:05:45');
/*!40000 ALTER TABLE `follow_up_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intakes`
--

DROP TABLE IF EXISTS `intakes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intakes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `academic_year` smallint(5) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intakes_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intakes`
--

LOCK TABLES `intakes` WRITE;
/*!40000 ALTER TABLE `intakes` DISABLE KEYS */;
INSERT INTO `intakes` VALUES (1,'Intake II','Int-02',2026,'2026-01-01','2026-04-01',60,1,NULL,'2026-10-20 21:45:46','2026-11-02 20:59:22'),(2,'Intake III','Int-03',2026,'2026-05-01','2026-08-01',80,1,NULL,'2026-10-20 21:45:46','2026-11-02 20:59:46'),(3,'Intake I','Int-01',2026,'2026-09-01','2026-12-01',100,1,NULL,'2026-10-20 21:45:46','2026-11-02 20:59:03');
/*!40000 ALTER TABLE `intakes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (2,2,'Tuition — Microsoft Office',1,150.00,150.00,'2026-10-21 23:11:51','2026-10-21 23:11:51'),(3,2,'Registration — Microsoft Office',1,25.00,25.00,'2026-10-21 23:11:51','2026-10-21 23:11:51'),(5,3,'Tuition — Graphic Design',1,200.00,200.00,'2026-10-21 23:48:38','2026-10-21 23:48:38'),(6,3,'Tuition — Graphic Design (Graphic Design)',1,200.00,200.00,'2026-10-21 23:48:38','2026-10-21 23:48:38'),(7,4,'Tuition — Microsoft Office',1,150.00,150.00,'2026-11-02 19:00:55','2026-11-02 19:00:55'),(8,4,'Registration — Microsoft Office',1,25.00,25.00,'2026-11-02 19:00:55','2026-11-02 19:00:55'),(9,5,'Tuition — Photography',1,250.00,250.00,'2026-11-04 18:56:26','2026-11-04 18:56:26'),(10,6,'Tuition — Microsoft Office (Microsoft Office)',1,150.00,150.00,'2026-11-04 19:20:40','2026-11-04 19:20:40'),(11,6,'Enrollment Fee',1,10.00,10.00,'2026-11-04 19:20:40','2026-11-04 19:20:40'),(15,9,'Tuition — Microsoft Office (Microsoft Office)',1,150.00,150.00,'2026-11-06 08:33:23','2026-11-06 08:33:23'),(16,9,'Registration — Microsoft Office (Microsoft Office)',1,25.00,25.00,'2026-11-06 08:33:23','2026-11-06 08:33:23'),(17,10,'Tuition — Microsoft Office (Microsoft Office)',1,150.00,150.00,'2026-11-06 08:51:29','2026-11-06 08:51:29'),(18,10,'Registration — Microsoft Office (Microsoft Office)',1,25.00,25.00,'2026-11-06 08:51:29','2026-11-06 08:51:29'),(20,11,'Tuition — Graphic Design',1,200.00,200.00,'2026-11-13 07:08:53','2026-11-13 07:08:53'),(21,8,'Tuition — Microsoft Office (Microsoft Office)',1,150.00,150.00,'2026-11-13 07:09:33','2026-11-13 07:09:33'),(22,8,'Registration — Microsoft Office (Microsoft Office)',1,25.00,25.00,'2026-11-13 07:09:33','2026-11-13 07:09:33');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(40) NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `student_course_id` bigint(20) unsigned DEFAULT NULL,
  `fee_structure_id` bigint(20) unsigned DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_student_id_foreign` (`student_id`),
  KEY `invoices_fee_structure_id_foreign` (`fee_structure_id`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_student_course_id_foreign` (`student_course_id`),
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_student_course_id_foreign` FOREIGN KEY (`student_course_id`) REFERENCES `student_courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (2,'INV-2026-0002',1,NULL,NULL,'2026-10-22',NULL,'paid',0.00,'Auto-generated for course enrollment',1,'2026-10-21 23:11:51','2026-11-02 01:14:55'),(3,'INV-2026-0003',1,6,NULL,'2026-10-22',NULL,'paid',0.00,'Auto-generated for course enrollment',3,'2026-10-21 23:41:25','2026-11-03 00:18:18'),(4,'INV-2026-0004',2,7,NULL,'2026-11-03',NULL,'paid',0.00,'Auto-generated for course enrollment',3,'2026-11-02 19:00:55','2026-11-02 22:08:56'),(5,'INV-2026-0005',2,NULL,NULL,'2026-11-05',NULL,'paid',0.00,'Auto-generated for course enrollment',3,'2026-11-04 18:56:26','2026-11-04 20:58:22'),(6,'INV-2026-0006',1,3,1,'2026-11-05',NULL,'paid',0.00,NULL,3,'2026-11-04 19:20:40','2026-11-04 21:00:31'),(8,'INV-2026-0007',3,9,1,'2026-11-06','2026-11-13','paid',35.00,NULL,3,'2026-11-06 08:23:05','2026-11-13 07:10:51'),(9,'INV-2026-0008',3,9,1,'2026-11-06','2026-11-10','paid',50.00,'Microsft Office',3,'2026-11-06 08:33:23','2026-11-06 08:33:44'),(10,'INV-2026-0009',3,9,1,'2026-11-06','2026-11-10','paid',50.00,'Microsft Office',3,'2026-11-06 08:51:29','2026-11-06 08:51:45'),(11,'INV-2026-0010',1,24,NULL,'2026-11-13',NULL,'paid',0.00,'Auto-generated for course enrollment',3,'2026-11-13 07:06:49','2026-11-13 07:08:53');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000001_create_roles_table',1),(5,'2024_01_01_000002_add_role_fields_to_users_table',1),(6,'2024_01_01_000003_create_campuses_courses_tables',1),(7,'2024_01_01_000004_create_students_table',1),(8,'2024_01_01_000005_create_student_education_employment_tables',1),(9,'2024_01_01_000006_create_communication_followup_tables',1),(10,'2024_01_01_000007_create_events_tables',1),(11,'2024_01_01_000008_create_opportunities_tables',1),(12,'2024_01_01_000009_create_documents_notes_notifications_settings_audit_tables',1),(13,'2026_09_29_035800_add_image_to_campuses_table',1),(14,'2026_09_29_041310_create_fee_structures_table',1),(15,'2026_09_29_041310_create_invoices_table',1),(16,'2026_09_29_041311_create_invoice_items_table',1),(17,'2026_09_29_041311_create_payments_table',1),(18,'2026_09_30_000001_create_academic_tables',1),(19,'2026_09_30_000002_create_hr_tables',1),(20,'2026_10_02_000001_add_student_stage_and_school_clubs',1),(21,'2026_10_06_000001_separate_graduation_status_from_student_stage',1),(22,'2026_10_06_000002_add_course_program_category_and_batch_study_time',1),(23,'2026_10_06_000003_create_programs_and_link_courses',1),(24,'2026_10_07_091438_create_course_categories_table',1),(25,'2026_10_07_091439_create_academic_years_table',1),(26,'2026_10_22_000001_add_enrollment_status_to_student_courses_table',2),(27,'2026_10_22_000002_add_category_id_to_courses_table',3),(28,'2026_10_22_000003_add_student_course_id_to_invoices_table',3),(29,'2026_11_02_000001_add_attendance_and_email_tracking_to_event_registrations',4),(30,'2026_11_02_000002_link_semesters_to_academic_years',5),(31,'2026_11_03_000001_create_study_groups_tables',6),(32,'2026_11_03_000002_add_academic_filters_to_study_groups',7),(33,'2026_11_03_000003_add_teacher_to_study_groups',8),(34,'2026_11_03_000004_create_attendance_records_table',9),(35,'2026_11_05_000001_create_student_change_requests_table',10),(36,'2026_11_05_000002_add_request_remarks_to_student_change_requests_table',11),(37,'2026_11_05_081716_create_student_face_profiles_table',12),(38,'2026_11_06_015457_create_employee_face_profiles_table',13),(39,'2026_11_06_021000_add_certificate_text_to_courses_table',14),(40,'2026_11_06_030000_add_study_time_to_study_groups',15),(41,'2026_11_06_030100_add_study_group_id_to_student_courses',15),(42,'2026_11_06_050000_add_remark_to_attendance_records_table',16),(43,'2026_11_10_000000_create_campus_user_table',17),(44,'2026_11_17_000001_add_pipeline_stage_to_students_table',18),(45,'2026_11_17_000002_create_outreach_tables',19);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',0,'2026-10-21 23:52:36','2026-10-21 23:52:36'),(2,2,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',0,'2026-10-21 23:52:36','2026-10-21 23:52:36'),(3,3,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',1,'2026-10-21 23:52:36','2026-11-10 06:57:25'),(4,4,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',0,'2026-10-21 23:52:36','2026-10-21 23:52:36'),(5,5,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',0,'2026-10-21 23:52:36','2026-10-21 23:52:36'),(6,6,'upcoming_event','New alumni event published','Water Festiwal — Nov 23, 2026 00:00','http://127.0.0.1:8000/events/1',0,'2026-10-21 23:52:36','2026-10-21 23:52:36'),(7,1,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',0,'2026-10-28 02:13:12','2026-10-28 02:13:12'),(8,2,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',0,'2026-10-28 02:13:12','2026-10-28 02:13:12'),(9,3,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',1,'2026-10-28 02:13:12','2026-11-10 06:57:25'),(10,4,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',0,'2026-10-28 02:13:12','2026-10-28 02:13:12'),(11,5,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',0,'2026-10-28 02:13:12','2026-10-28 02:13:12'),(12,6,'new_opportunity','New Scholarship','Graphic Design @ I-Tech Media','http://127.0.0.1:8000/opportunities/1',0,'2026-10-28 02:13:12','2026-10-28 02:13:12'),(13,3,'new_opportunity','New opportunity: Graphic Design','I-Tech Media — deadline Nov 14, 2026','http://127.0.0.1:8000/opportunities/1',1,'2026-11-01 18:53:55','2026-11-10 06:57:25'),(14,3,'followup_reminder','New follow-up task assigned','Create CRM System — due Nov 30, 2026','http://127.0.0.1:8000/students/1?tab=tasks',1,'2026-11-02 21:23:17','2026-11-03 18:08:07'),(15,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 19:41:36','2026-11-04 19:41:36'),(16,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 19:41:36','2026-11-04 19:41:36'),(17,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-04 19:41:36','2026-11-10 06:57:25'),(18,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 19:58:46','2026-11-04 19:58:46'),(19,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 19:58:46','2026-11-04 19:58:46'),(20,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-04 19:58:46','2026-11-10 06:57:25'),(21,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 20:37:47','2026-11-04 20:37:47'),(22,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-04 20:37:47','2026-11-04 20:37:47'),(23,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-04 20:37:47','2026-11-10 06:57:25'),(24,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:00:33','2026-11-06 04:00:33'),(25,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:00:33','2026-11-06 04:00:33'),(26,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 04:00:33','2026-11-10 06:57:25'),(27,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:02:48','2026-11-06 04:02:48'),(28,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:02:48','2026-11-06 04:02:48'),(29,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 04:02:48','2026-11-10 06:57:25'),(30,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Chan Bopha.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:40:46','2026-11-06 04:40:46'),(31,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Chan Bopha.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 04:40:46','2026-11-06 04:40:46'),(32,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Chan Bopha.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 04:40:46','2026-11-10 06:57:25'),(33,1,'student_update','Student changes need approval','Super Admin submitted profile changes for Sok Dara.','http://localhost/student-change-requests',0,'2026-11-06 04:54:34','2026-11-06 04:54:34'),(34,2,'student_update','Student changes need approval','Super Admin submitted profile changes for Sok Dara.','http://localhost/student-change-requests',0,'2026-11-06 04:54:34','2026-11-06 04:54:34'),(35,3,'student_update','Student changes need approval','Super Admin submitted profile changes for Sok Dara.','http://localhost/student-change-requests',1,'2026-11-06 04:54:34','2026-11-10 06:57:25'),(36,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 06:24:26','2026-11-06 06:24:26'),(37,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 06:24:26','2026-11-06 06:24:26'),(38,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratana Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 06:24:26','2026-11-10 06:57:25'),(39,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 07:43:26','2026-11-06 07:43:26'),(40,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 07:43:26','2026-11-06 07:43:26'),(41,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Ratanak Khoeurn.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 07:43:26','2026-11-10 06:57:25'),(42,1,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Sok Dara.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 08:43:52','2026-11-06 08:43:52'),(43,2,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Sok Dara.','http://127.0.0.1:8000/student-change-requests',0,'2026-11-06 08:43:52','2026-11-06 08:43:52'),(44,3,'student_update','Student changes need approval','Ratanak Khoeurn submitted profile changes for Sok Dara.','http://127.0.0.1:8000/student-change-requests',1,'2026-11-06 08:43:52','2026-11-10 06:57:25'),(45,3,'followup_reminder','Upcoming follow-up: Create CRM System','Ratanak Khoeurn — due Nov 30, 2026','http://127.0.0.1:8000/students/1?tab=tasks',1,'2026-11-10 06:57:31','2026-11-17 02:29:39'),(46,3,'followup_reminder','New follow-up task assigned','test — due Nov 24, 2026','http://127.0.0.1:8000/students/3?tab=tasks',0,'2026-11-17 04:09:48','2026-11-17 04:09:48');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities`
--

DROP TABLE IF EXISTS `opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `opportunity_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `application_url` varchar(255) DEFAULT NULL,
  `target_course` varchar(255) DEFAULT NULL,
  `target_graduation_year` varchar(255) DEFAULT NULL,
  `publication_status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opportunities_created_by_foreign` (`created_by`),
  CONSTRAINT `opportunities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities`
--

LOCK TABLES `opportunities` WRITE;
/*!40000 ALTER TABLE `opportunities` DISABLE KEYS */;
INSERT INTO `opportunities` VALUES (1,'Graphic Design','I-Tech Media','scholarship','Our graphic design course will open for a new term on 14/11/2026','basic with computer','2026-11-14',NULL,NULL,NULL,NULL,'published',3,'2026-10-28 02:13:12','2026-10-28 02:13:12',NULL);
/*!40000 ALTER TABLE `opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunity_applications`
--

DROP TABLE IF EXISTS `opportunity_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunity_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `opportunity_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `source` enum('matched','referred','self_applied') NOT NULL DEFAULT 'referred',
  `status` enum('interested','applied','interviewing','accepted','rejected','withdrawn') NOT NULL DEFAULT 'interested',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opportunity_applications_opportunity_id_student_id_unique` (`opportunity_id`,`student_id`),
  KEY `opportunity_applications_student_id_foreign` (`student_id`),
  KEY `opportunity_applications_created_by_foreign` (`created_by`),
  CONSTRAINT `opportunity_applications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `opportunity_applications_opportunity_id_foreign` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opportunity_applications_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunity_applications`
--

LOCK TABLES `opportunity_applications` WRITE;
/*!40000 ALTER TABLE `opportunity_applications` DISABLE KEYS */;
INSERT INTO `opportunity_applications` VALUES (1,1,1,'referred','accepted','Testing',3,'2026-11-01 23:15:53','2026-11-01 23:16:15'),(2,1,2,'self_applied','applied','Test',3,'2026-11-02 21:18:30','2026-11-02 21:18:30');
/*!40000 ALTER TABLE `opportunity_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outreach_campaigns`
--

DROP TABLE IF EXISTS `outreach_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outreach_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `purpose` varchar(30) NOT NULL DEFAULT 'event',
  `alumni_event_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `audience_filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`audience_filters`)),
  `status` enum('active','completed','archived') NOT NULL DEFAULT 'active',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `outreach_campaigns_alumni_event_id_foreign` (`alumni_event_id`),
  KEY `outreach_campaigns_created_by_foreign` (`created_by`),
  KEY `outreach_campaigns_status_index` (`status`),
  CONSTRAINT `outreach_campaigns_alumni_event_id_foreign` FOREIGN KEY (`alumni_event_id`) REFERENCES `alumni_events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `outreach_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outreach_campaigns`
--

LOCK TABLES `outreach_campaigns` WRITE;
/*!40000 ALTER TABLE `outreach_campaigns` DISABLE KEYS */;
INSERT INTO `outreach_campaigns` VALUES (24,'Ratanak Khoeurn','event',1,'Hi Ratanak, you\'re invited to join our event Water Festival.','{\"course_id\":2,\"campus_id\":1,\"graduation_year\":2027,\"student_stage\":\"alumni\",\"graduation_status\":\"graduated\",\"engagement_status\":\"engaged\"}','active',3,'2026-11-17 04:03:31','2026-11-17 04:03:31',NULL);
/*!40000 ALTER TABLE `outreach_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outreach_leads`
--

DROP TABLE IF EXISTS `outreach_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outreach_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `outreach_campaign_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `stage` varchar(30) NOT NULL DEFAULT 'to_contact',
  `channel` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `contacted_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `outreach_leads_outreach_campaign_id_student_id_unique` (`outreach_campaign_id`,`student_id`),
  KEY `outreach_leads_student_id_foreign` (`student_id`),
  KEY `outreach_leads_created_by_foreign` (`created_by`),
  KEY `outreach_leads_outreach_campaign_id_stage_index` (`outreach_campaign_id`,`stage`),
  CONSTRAINT `outreach_leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `outreach_leads_outreach_campaign_id_foreign` FOREIGN KEY (`outreach_campaign_id`) REFERENCES `outreach_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `outreach_leads_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outreach_leads`
--

LOCK TABLES `outreach_leads` WRITE;
/*!40000 ALTER TABLE `outreach_leads` DISABLE KEYS */;
INSERT INTO `outreach_leads` VALUES (39,24,3,'registered','phone_call',NULL,'2026-11-17 12:04:35',3,'2026-11-17 04:50:16','2026-11-17 05:06:21'),(49,24,2,'registered',NULL,NULL,NULL,3,'2026-11-17 05:49:51','2026-11-17 06:06:59'),(50,24,1,'registered',NULL,NULL,NULL,3,'2026-11-17 06:04:22','2026-11-17 06:04:22');
/*!40000 ALTER TABLE `outreach_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(30) NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_received_by_foreign` (`received_by`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (6,2,'2026-11-02',175.00,'mobile_banking','Testing',3,'Testing','2026-11-02 01:14:55','2026-11-02 01:14:55'),(7,4,'2026-11-03',150.00,'bank_transfer','Receipt',3,'Microsft Office','2026-11-02 22:07:30','2026-11-02 22:07:30'),(8,4,'2026-11-03',25.00,'bank_transfer','Receipt',3,'Microsft Office','2026-11-02 22:08:56','2026-11-02 22:08:56'),(9,3,'2026-11-03',400.00,'bank_transfer','Receipt',3,'Graphic Design Course payment','2026-11-03 00:18:18','2026-11-03 00:18:18'),(10,6,'2026-11-05',80.00,'bank_transfer','Receipt',3,'Part 1','2026-11-04 19:24:50','2026-11-04 19:24:50'),(11,5,'2026-11-05',125.00,'bank_transfer','Receipt',3,'Photography','2026-11-04 19:27:57','2026-11-04 19:27:57'),(12,5,'2026-11-05',100.00,'bank_transfer','Receipt',3,'part 2','2026-11-04 20:50:46','2026-11-04 20:50:46'),(13,5,'2026-11-05',25.00,'bank_transfer','Receipt',3,'Part 3','2026-11-04 20:58:22','2026-11-04 20:58:22'),(14,6,'2026-11-05',80.00,'bank_transfer','Receipt',3,'Part 2','2026-11-04 21:00:31','2026-11-04 21:00:31'),(17,9,'2026-11-06',125.00,'bank_transfer','Receipt',3,'Microsft Office','2026-11-06 08:33:44','2026-11-06 08:33:44'),(18,10,'2026-11-06',125.00,'bank_transfer','Receipt',3,'Microsft Office','2026-11-06 08:51:45','2026-11-06 08:51:45'),(19,11,'2026-11-13',200.00,'bank_transfer','Receipt',3,'Graphic Design Course payment','2026-11-13 07:07:54','2026-11-13 07:07:54'),(20,8,'2026-11-13',140.00,'bank_transfer','Receipt',3,'Graphic Design Course payment','2026-11-13 07:10:51','2026-11-13 07:10:51');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `level` smallint(5) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'Director','Management',10,'Director within the Management department.',1,'2026-10-20 21:45:46','2026-11-10 08:51:48'),(2,'Admissions Officer','Admissions',2,'Admissions Officer within the Admissions department.',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(3,'Academic Coordinator','Academics',2,'Academic Coordinator within the Academics department.',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(4,'Instructor','Academics',3,'Instructor within the Academics department.',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(5,'Accountant','Finance',2,'Accountant within the Finance department.',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(6,'IT Support','IT',3,'IT Support within the IT department.',1,'2026-10-20 21:45:46','2026-10-20 21:45:46'),(7,'Humance Resource','HR',10,'HR',1,'2026-11-05 18:42:26','2026-11-05 18:42:26'),(8,'General Management','GM',10,'General Management position handles all employees in company',1,'2026-11-10 08:51:25','2026-11-10 08:51:39'),(9,'Marketing Officer','MO',5,'Marketing Office manages all actions related to social.',1,'2026-11-10 08:59:52','2026-11-10 08:59:52'),(10,'Graphic Designer','GD',5,'Graphic Designer handles all poster, video highlight etc...',1,'2026-11-10 09:04:05','2026-11-10 09:04:05');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `programs_name_unique` (`name`),
  UNIQUE KEY `programs_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES (1,'Local Curriculums','LC-TTP-01','Local Curriculums we created this program for to learner about coures that can help them in real life nowadays.',1,'2026-10-22 00:18:07','2026-10-22 00:18:07');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','Super Admin','Full access to all system features and settings.','2026-10-20 21:45:44','2026-10-20 21:45:44'),(2,'admin','Admin','Manage students, staff, communication, and reports.','2026-10-20 21:45:44','2026-10-20 21:45:44'),(3,'staff','Staff','View and update assigned student records and communication history.','2026-10-20 21:45:44','2026-10-20 21:45:44'),(4,'student','Student','Read-only access to permitted student information.','2026-10-20 21:45:44','2026-10-20 21:45:44');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_club_student`
--

DROP TABLE IF EXISTS `school_club_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_club_student` (
  `student_id` bigint(20) unsigned NOT NULL,
  `school_club_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`student_id`,`school_club_id`),
  KEY `school_club_student_school_club_id_foreign` (`school_club_id`),
  CONSTRAINT `school_club_student_school_club_id_foreign` FOREIGN KEY (`school_club_id`) REFERENCES `school_clubs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `school_club_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_club_student`
--

LOCK TABLES `school_club_student` WRITE;
/*!40000 ALTER TABLE `school_club_student` DISABLE KEYS */;
INSERT INTO `school_club_student` VALUES (1,1,NULL,NULL),(2,1,NULL,NULL);
/*!40000 ALTER TABLE `school_club_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_clubs`
--

DROP TABLE IF EXISTS `school_clubs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_clubs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `billing_period` varchar(50) DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `schedule` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `registration_start` date DEFAULT NULL,
  `registration_end` date DEFAULT NULL,
  `coach_name` varchar(255) DEFAULT NULL,
  `coach_email` varchar(255) DEFAULT NULL,
  `coach_phone` varchar(30) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_clubs_name_unique` (`name`),
  UNIQUE KEY `school_clubs_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_clubs`
--

LOCK TABLES `school_clubs` WRITE;
/*!40000 ALTER TABLE `school_clubs` DISABLE KEYS */;
INSERT INTO `school_clubs` VALUES (1,'Football Club','CL-01','Football Club','Sports',30.00,'USD','monthly',20,'Zion International School','Monday-Friday','2026-12-01','2027-01-31','2026-11-05','2026-11-30','Ratanak Khoeurn','k.ratanak@itechmediakh.com','0962728396','school-clubs/Axu0if3D8zdpDP7a3QIWhlstMr7JFf1t7CCTr8nW.jpg',1,'2026-10-20 22:03:12','2026-11-02 18:47:11'),(2,'Coding Club','CL-02','Coding Club provides a training course related to create robot, drone or something else.','Information technology',150.00,'USD','monthly',30,'Zion International School','Monday-Friday','2026-10-31',NULL,'2026-10-31','2026-11-29','Ratanak Khoeurn','ratanak.khoeurn@student.passerellesnumeriques.org','0962728396','school-clubs/2AMBDP0dLy6J6q1IrS7D24Edm3b92vIabHHUJDPP.jpg',1,'2026-11-05 01:11:36','2026-11-06 02:58:24');
/*!40000 ALTER TABLE `school_clubs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semesters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `academic_year_id` bigint(20) unsigned DEFAULT NULL,
  `term` tinyint(3) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `semesters_code_unique` (`code`),
  KEY `semesters_academic_year_id_foreign` (`academic_year_id`),
  CONSTRAINT `semesters_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (1,'Semester 1 2026','2026-S1',1,1,'2026-01-01','2026-06-01',1,'Semester I','2026-10-20 21:45:46','2026-11-02 18:25:22'),(2,'Semester 2 2026','2026-S2',1,2,'2026-07-01','2026-12-01',0,NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46');
/*!40000 ALTER TABLE `semesters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('18Lq0rP4ccJzKcRrsWqF0GB5VskflJgRKHnw4sg7',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoia2w1VmhoS0VXWGpiSGJkNXB2MWNpS0c1WmNWdjdRWmRpSTZHT2s3YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdHVkZW50LWNoYW5nZS1yZXF1ZXN0cyI7czo1OiJyb3V0ZSI7czoyOToic3R1ZGVudC1jaGFuZ2UtcmVxdWVzdHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9',1795660215),('6LqfQvwLiPw7AoT0PtJCHjIpzFcnQpJi6zZqQN1g',1,'127.0.0.1','Symfony','YTo0OntzOjY6Il90b2tlbiI7czo4OiJ0b2stbWFpbCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjc6InN1Y2Nlc3MiO3M6MTA4OiJTdHVkZW50IHJlZ2lzdGVyZWQg4oCUIHRoZSBjb25maXJtYXRpb24gZW1haWwgdG8gay5yYXRhbmFrQGl0ZWNobWVkaWFraC5jb20gaXMgYmVpbmcgc2VudCBpbiB0aGUgYmFja2dyb3VuZC4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fX0=',1795659619),('aQqSmzAouZdYRpWn2rmBwPDUuhQfGHxPFd2JZ1L4',1,'127.0.0.1','Symfony','YTo0OntzOjY6Il90b2tlbiI7czo4OiJ0b2stbWFpbCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjc6InN1Y2Nlc3MiO3M6MTA4OiJTdHVkZW50IHJlZ2lzdGVyZWQg4oCUIHRoZSBjb25maXJtYXRpb24gZW1haWwgdG8gay5yYXRhbmFrQGl0ZWNobWVkaWFraC5jb20gaXMgYmVpbmcgc2VudCBpbiB0aGUgYmFja2dyb3VuZC4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fX0=',1795659668),('C8roZvFROaFGwyUBNn9v8Qw8dHnK4iOaZI0C0ZQz',1,'127.0.0.1','Symfony','YTo0OntzOjY6Il90b2tlbiI7czo4OiJ0b2stbWFpbCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjc6InN1Y2Nlc3MiO3M6MTA4OiJTdHVkZW50IHJlZ2lzdGVyZWQg4oCUIHRoZSBjb25maXJtYXRpb24gZW1haWwgdG8gay5yYXRhbmFrQGl0ZWNobWVkaWFraC5jb20gaXMgYmVpbmcgc2VudCBpbiB0aGUgYmFja2dyb3VuZC4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fX0=',1795659570),('RJBpBuU2ElAWVHRfh8d8chCmSlHMXyDaHiT2YgPQ',1,'127.0.0.1','Symfony','YTo0OntzOjY6Il90b2tlbiI7czo4OiJ0b2stbWFpbCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjc6InN1Y2Nlc3MiO3M6MTA4OiJTdHVkZW50IHJlZ2lzdGVyZWQg4oCUIHRoZSBjb25maXJtYXRpb24gZW1haWwgdG8gay5yYXRhbmFrQGl0ZWNobWVkaWFraC5jb20gaXMgYmVpbmcgc2VudCBpbiB0aGUgYmFja2dyb3VuZC4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fX0=',1795659653);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'center_name','I-Tech Media Center','general','2026-10-20 21:45:46','2026-10-20 21:45:46'),(2,'center_email','info@itechmediakh.com','general','2026-10-20 21:45:46','2026-10-20 21:45:46'),(3,'center_phone','092 426 205','general','2026-10-20 21:45:46','2026-10-20 21:45:46'),(4,'center_address','Building #10A, 1st Floor, St. 470, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh','general','2026-10-20 21:45:46','2026-10-20 21:45:46'),(5,'notify_followup','1','notifications','2026-10-20 21:45:46','2026-10-20 21:45:46'),(6,'notify_overdue','1','notifications','2026-10-20 21:45:46','2026-10-20 21:45:46'),(7,'notify_events','1','notifications','2026-10-20 21:45:46','2026-10-20 21:45:46'),(8,'notify_opportunities','1','notifications','2026-10-20 21:45:46','2026-10-20 21:45:46');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_change_requests`
--

DROP TABLE IF EXISTS `student_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `request_remarks` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `review_note` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_change_requests_requested_by_foreign` (`requested_by`),
  KEY `student_change_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `student_change_requests_status_created_at_index` (`status`,`created_at`),
  KEY `student_change_requests_student_id_status_index` (`student_id`,`status`),
  CONSTRAINT `student_change_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_change_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_change_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_change_requests`
--

LOCK TABLES `student_change_requests` WRITE;
/*!40000 ALTER TABLE `student_change_requests` DISABLE KEYS */;
INSERT INTO `student_change_requests` VALUES (1,1,3,3,'{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03\",\"next_followup_date\":\"2026-11-04\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"4\",\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01\",\"sc_course_start_date\":\"2026-10-31\",\"sc_course_completion_date\":\"2026-10-31\",\"sc_graduation_date\":\"2026-11-15\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"IT Sector\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01\",\"edu_expected_graduation_date\":\"2027-12-31\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25T00:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21T00:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03T00:00:00.000000Z\",\"next_followup_date\":\"2026-11-04T00:00:00.000000Z\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":4,\"internal_notes\":null,\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01T00:00:00.000000Z\",\"sc_course_start_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_course_completion_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_graduation_date\":\"2026-11-15T00:00:00.000000Z\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"IT Sector\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01T00:00:00.000000Z\",\"edu_expected_graduation_date\":\"2027-12-31T00:00:00.000000Z\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20T00:00:00.000000Z\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false}}',NULL,'approved','this student change address','2026-11-04 19:42:40','2026-11-04 19:41:36','2026-11-04 19:42:40'),(2,1,3,3,'{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03\",\"next_followup_date\":\"2026-11-04\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"4\",\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01\",\"sc_course_start_date\":\"2026-10-31\",\"sc_course_completion_date\":\"2026-10-31\",\"sc_graduation_date\":\"2026-11-15\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"Information Technology\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01\",\"edu_expected_graduation_date\":\"2027-12-31\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":true,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25T00:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21T00:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03T00:00:00.000000Z\",\"next_followup_date\":\"2026-11-04T00:00:00.000000Z\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":4,\"internal_notes\":null,\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01T00:00:00.000000Z\",\"sc_course_start_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_course_completion_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_graduation_date\":\"2026-11-15T00:00:00.000000Z\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"IT Sector\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01T00:00:00.000000Z\",\"edu_expected_graduation_date\":\"2027-12-31T00:00:00.000000Z\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20T00:00:00.000000Z\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false}}',NULL,'approved',NULL,'2026-11-04 19:59:15','2026-11-04 19:58:46','2026-11-04 19:59:15'),(3,1,3,3,'{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03\",\"next_followup_date\":\"2026-11-04\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"4\",\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01\",\"sc_course_start_date\":\"2026-10-31\",\"sc_course_completion_date\":\"2026-10-31\",\"sc_graduation_date\":\"2026-11-15\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"Information Technology\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01\",\"edu_expected_graduation_date\":\"2027-12-31\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":true,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25T00:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21T00:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03T00:00:00.000000Z\",\"next_followup_date\":\"2026-11-04T00:00:00.000000Z\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":4,\"internal_notes\":null,\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-09-01T00:00:00.000000Z\",\"sc_course_start_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_course_completion_date\":\"2026-10-31T00:00:00.000000Z\",\"sc_graduation_date\":\"2026-11-15T00:00:00.000000Z\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"Information Technology\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01T00:00:00.000000Z\",\"edu_expected_graduation_date\":\"2027-12-31T00:00:00.000000Z\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20T00:00:00.000000Z\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":true,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false},\"_staged_files\":{\"photo\":{\"path\":\"student-change-requests\\/3\\/miQIs2khaXMgbe2jtAu7nXUxHTIkQcfmcEV9LYnR.jpg\",\"original_name\":\"IMG_20251102_161305.jpg\",\"size\":1003503}}}',NULL,'approved',NULL,'2026-11-04 20:37:56','2026-11-04 20:37:47','2026-11-04 20:37:56'),(4,2,3,3,'{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-05\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-03\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"5\",\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":\"01\",\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-11-05\",\"sc_course_start_date\":\"2026-11-21\",\"sc_course_completion_date\":\"2026-12-27\",\"sc_graduation_date\":\"2027-01-03\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":\"A\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":\"Add New course\",\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-04T17:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-02T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":5,\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":1,\"sc_course_id\":3,\"sc_academic_year\":null,\"sc_intake_id\":3,\"sc_semester_id\":1,\"sc_batch_id\":3,\"sc_term\":null,\"sc_registration_number\":\"01\",\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-11-04T17:00:00.000000Z\",\"sc_course_start_date\":\"2026-11-20T17:00:00.000000Z\",\"sc_course_completion_date\":\"2026-12-26T17:00:00.000000Z\",\"sc_graduation_date\":\"2027-01-02T17:00:00.000000Z\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":\"A\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":\"Add New course\",\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false},\"_changes\":{\"sc_course_id\":{\"old\":3,\"new\":\"1\"},\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_intake_id\":{\"old\":3,\"new\":\"1\"},\"sc_batch_id\":{\"old\":3,\"new\":\"1\"},\"sc_term\":{\"old\":null,\"new\":\"1\"}}}',NULL,'approved',NULL,'2026-11-06 04:00:41','2026-11-06 04:00:33','2026-11-06 04:00:41'),(5,2,3,3,'{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-05\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-03\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"5\",\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":\"Sale Manager\",\"sc_class_group\":\"TTP-T3-COC-G1\",\"sc_instructor_name\":\"Rady Y\",\"sc_enrollment_date\":\"2026-11-03\",\"sc_course_start_date\":\"2026-11-16\",\"sc_course_completion_date\":\"2027-01-14\",\"sc_graduation_date\":\"2027-01-25\",\"sc_graduation_year\":\"2027\",\"sc_attendance_percentage\":\"100.00\",\"sc_grade\":\"C\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":\"2026-11-05\",\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":true,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-04T17:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-02T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":5,\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":\"Sale Manager\",\"sc_class_group\":\"TTP-T3-COC-G1\",\"sc_instructor_name\":\"Rady Y\",\"sc_enrollment_date\":\"2026-11-02T17:00:00.000000Z\",\"sc_course_start_date\":\"2026-11-15T17:00:00.000000Z\",\"sc_course_completion_date\":\"2027-01-13T17:00:00.000000Z\",\"sc_graduation_date\":\"2027-01-24T17:00:00.000000Z\",\"sc_graduation_year\":2027,\"sc_attendance_percentage\":\"100.00\",\"sc_grade\":\"C\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":\"2026-11-04T17:00:00.000000Z\",\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":true,\"sc_internship_participation\":false},\"_changes\":{\"student_stage\":{\"old\":\"current_student\",\"new\":\"alumni\"},\"graduation_status\":{\"old\":\"not_graduated\",\"new\":\"graduated\"},\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_term\":{\"old\":null,\"new\":\"1\"}}}',NULL,'approved',NULL,'2026-11-06 04:02:53','2026-11-06 04:02:48','2026-11-06 04:02:53'),(6,4,3,3,'{\"name_english\":\"Chan Bopha\",\"name_khmer\":\"\\u1785\\u17b6\\u1793\\u17cb \\u1794\\u17bb\\u1794\\u17d2\\u1795\\u17b6\",\"name_chinese\":null,\"gender\":\"female\",\"date_of_birth\":null,\"nationality\":null,\"national_id\":null,\"marital_status\":null,\"phone\":\"098765432\",\"telegram\":\"@bopha\",\"email\":\"bopha@example.com\",\"facebook_url\":null,\"linkedin_url\":null,\"address\":null,\"province\":\"Siem Reap\",\"country\":null,\"emergency_contact_name\":null,\"emergency_contact_relationship\":null,\"emergency_contact_phone\":null,\"preferred_channel\":null,\"preferred_language\":null,\"registered_date\":\"2026-11-06\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":null,\"employment_status\":null,\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":null,\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":null,\"sc_course_start_date\":null,\"sc_course_completion_date\":null,\"sc_graduation_date\":null,\"sc_graduation_year\":\"2022\",\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":null,\"emp_job_title\":null,\"emp_industry\":null,\"emp_department\":null,\"emp_company_address\":null,\"emp_work_phone\":null,\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":null,\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Chan Bopha\",\"name_khmer\":\"\\u1785\\u17b6\\u1793\\u17cb \\u1794\\u17bb\\u1794\\u17d2\\u1795\\u17b6\",\"name_chinese\":null,\"gender\":\"female\",\"date_of_birth\":null,\"nationality\":null,\"national_id\":null,\"marital_status\":null,\"phone\":\"098765432\",\"telegram\":\"@bopha\",\"email\":\"bopha@example.com\",\"facebook_url\":null,\"linkedin_url\":null,\"address\":null,\"province\":\"Siem Reap\",\"country\":null,\"emergency_contact_name\":null,\"emergency_contact_relationship\":null,\"emergency_contact_phone\":null,\"preferred_channel\":null,\"preferred_language\":null,\"registered_date\":\"2026-11-05T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":null,\"employment_status\":null,\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":3,\"internal_notes\":null,\"sc_campus_id\":null,\"sc_course_id\":2,\"sc_academic_year\":null,\"sc_intake_id\":null,\"sc_semester_id\":null,\"sc_batch_id\":null,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":null,\"sc_course_start_date\":null,\"sc_course_completion_date\":null,\"sc_graduation_date\":null,\"sc_graduation_year\":2022,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":null,\"emp_job_title\":null,\"emp_industry\":null,\"emp_department\":null,\"emp_company_address\":null,\"emp_work_phone\":null,\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":null,\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false},\"_changes\":{\"assigned_staff_id\":{\"old\":3,\"new\":null},\"sc_campus_id\":{\"old\":null,\"new\":\"1\"},\"sc_course_id\":{\"old\":2,\"new\":\"1\"},\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_intake_id\":{\"old\":null,\"new\":\"1\"},\"sc_semester_id\":{\"old\":null,\"new\":\"1\"},\"sc_batch_id\":{\"old\":null,\"new\":\"1\"},\"sc_term\":{\"old\":null,\"new\":\"1\"}}}',NULL,'approved',NULL,'2026-11-06 04:40:52','2026-11-06 04:40:46','2026-11-06 04:40:52'),(8,2,3,3,'{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-05\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-03\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"5\",\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_study_group_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":\"Sale Manager\",\"sc_class_group\":\"Computer Skill-2026-G1\",\"sc_instructor_name\":\"Vandet Noun\",\"sc_enrollment_date\":\"2026-11-03\",\"sc_course_start_date\":\"2026-11-16\",\"sc_course_completion_date\":\"2027-01-14\",\"sc_graduation_date\":\"2027-01-25\",\"sc_graduation_year\":\"2027\",\"sc_attendance_percentage\":\"100.00\",\"sc_grade\":\"C\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":\"2026-11-05\",\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":true,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratana Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17b6\",\"name_chinese\":\"Ratana khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2026-02-04T17:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"1234568788\",\"marital_status\":\"single\",\"phone\":\"012622524\",\"telegram\":\"012622524\",\"email\":\"ratanakhoeurn1@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratana.khoeun.4\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratana-khoeurn-4\\/\",\"address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1 \\u1781\\u178e\\u17d2\\u178c\\u1785\\u17c6\\u1780\\u17b6\\u179a\\u1798\\u1793 \\u179a\\u17b6\\u1787\\u1792\\u17b6\\u1793\\u17b8\\u1797\\u17d2\\u1793\\u17c6\\u1796\\u17c1\\u1789\\u17d4\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-02T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":5,\"internal_notes\":\"Computer Office student enrol in football club\",\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_study_group_id\":null,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":\"Sale Manager\",\"sc_class_group\":\"TTP-T3-COC-G1\",\"sc_instructor_name\":\"Rady Y\",\"sc_enrollment_date\":\"2026-11-02T17:00:00.000000Z\",\"sc_course_start_date\":\"2026-11-15T17:00:00.000000Z\",\"sc_course_completion_date\":\"2027-01-13T17:00:00.000000Z\",\"sc_graduation_date\":\"2027-01-24T17:00:00.000000Z\",\"sc_graduation_year\":2027,\"sc_attendance_percentage\":\"100.00\",\"sc_grade\":\"C\",\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":\"2026-11-04T17:00:00.000000Z\",\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":\"TL Express\",\"emp_job_title\":\"Sale Manager\",\"emp_industry\":\"Delivery\",\"emp_department\":\"Sale\",\"emp_company_address\":\"k.ratana@tlexpress.com\",\"emp_work_phone\":\"0819997111\",\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":true,\"sc_internship_participation\":false},\"_changes\":{\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_study_group_id\":{\"old\":null,\"new\":\"1\"},\"sc_term\":{\"old\":null,\"new\":\"1\"},\"sc_class_group\":{\"old\":\"TTP-T3-COC-G1\",\"new\":\"Computer Skill-2026-G1\"},\"sc_instructor_name\":{\"old\":\"Rady Y\",\"new\":\"Vandet Noun\"}}}',NULL,'approved',NULL,'2026-11-06 06:24:32','2026-11-06 06:24:26','2026-11-06 06:24:32'),(9,1,3,3,'{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-25\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-21\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-03\",\"next_followup_date\":\"2026-11-04\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":\"4\",\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_study_group_id\":\"2\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":\"Computer Skill-2026-G1\",\"sc_instructor_name\":\"Reaksmey Ky\",\"sc_enrollment_date\":\"2026-09-01\",\"sc_course_start_date\":\"2026-10-31\",\"sc_course_completion_date\":\"2026-10-31\",\"sc_graduation_date\":\"2026-11-15\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"Information Technology\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-12-01\",\"edu_expected_graduation_date\":\"2027-12-31\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-20\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[\"1\"],\"is_currently_studying\":true,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Ratanak Khoeurn\",\"name_khmer\":\"\\u1781\\u17bf\\u1793 \\u179a\\u178f\\u1793\\u17c8\",\"name_chinese\":\"Ratanak Khoeurn\",\"gender\":\"male\",\"date_of_birth\":\"2004-07-24T17:00:00.000000Z\",\"nationality\":\"Khmer\",\"national_id\":\"171223074\",\"marital_status\":\"single\",\"phone\":\"0962728396\",\"telegram\":\"0962728396\",\"email\":\"k.ratanak@itechmediakh.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/ratanak.khoeun.3\",\"linkedin_url\":\"https:\\/\\/www.linkedin.com\\/in\\/ratanak-khoeurn-98b0a7287\\/\",\"address\":\"Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh\",\"province\":\"Phnom Penh\",\"country\":\"Cambodia\",\"emergency_contact_name\":\"Ratana Khoeurn\",\"emergency_contact_relationship\":\"Sibling\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-10-20T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"alumni\",\"graduation_status\":\"not_graduated\",\"education_status\":\"graduated\",\"employment_status\":\"employed\",\"professional_skills\":\"IT Assistant, Web Development, Backend Development\",\"technical_skills\":\"IT Skills\",\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":\"2026-11-02T17:00:00.000000Z\",\"next_followup_date\":\"2026-11-03T17:00:00.000000Z\",\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":4,\"internal_notes\":null,\"sc_campus_id\":1,\"sc_course_id\":1,\"sc_academic_year\":null,\"sc_intake_id\":1,\"sc_semester_id\":1,\"sc_batch_id\":1,\"sc_study_group_id\":null,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":\"2026-08-31T17:00:00.000000Z\",\"sc_course_start_date\":\"2026-10-30T17:00:00.000000Z\",\"sc_course_completion_date\":\"2026-10-30T17:00:00.000000Z\",\"sc_graduation_date\":\"2026-11-14T17:00:00.000000Z\",\"sc_graduation_year\":null,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":\"member\",\"sc_alumni_membership_date\":null,\"sc_notes\":\"Test\",\"edu_institution\":\"University Of Puthisastra\",\"edu_degree_program\":\"Information Technology\",\"edu_major\":\"Information Technology\",\"edu_year_level\":\"Year 4\",\"edu_start_date\":\"2025-11-30T17:00:00.000000Z\",\"edu_expected_graduation_date\":\"2027-12-30T17:00:00.000000Z\",\"edu_scholarship_info\":\"70%\",\"edu_further_plans\":null,\"emp_company\":\"I-Tech Media\",\"emp_job_title\":\"IT Assistant\",\"emp_industry\":\"Information Technology\",\"emp_department\":\"IT\",\"emp_company_address\":\"\\u17a2\\u1782\\u17b6\\u179a\\u179b\\u17c1\\u1781 251 \\u1795\\u17d2\\u179b\\u17bc\\u179c 472 \\u179f\\u1784\\u17d2\\u1780\\u17b6\\u178f\\u17cb\\u1791\\u17bd\\u179b\\u1791\\u17c6\\u1796\\u17bc\\u1784\\u1791\\u17b8\\u17e1\",\"emp_work_phone\":\"0962728396\",\"emp_work_email\":\"k.ratanak@itechmediakh.com\",\"emp_start_date\":\"2025-10-19T17:00:00.000000Z\",\"emp_salary_range\":\"300\",\"emp_employment_type\":\"full_time\",\"school_club_ids\":[1],\"is_currently_studying\":true,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false},\"_changes\":{\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_study_group_id\":{\"old\":null,\"new\":\"2\"},\"sc_term\":{\"old\":null,\"new\":\"1\"},\"sc_class_group\":{\"old\":null,\"new\":\"Computer Skill-2026-G1\"},\"sc_instructor_name\":{\"old\":null,\"new\":\"Reaksmey Ky\"}}}',NULL,'approved',NULL,'2026-11-06 07:43:40','2026-11-06 07:43:26','2026-11-06 07:43:40'),(10,3,3,3,'{\"name_english\":\"Pov Saory\",\"name_khmer\":\"\\u1796\\u17c5 \\u179f\\u17c5\\u179a\\u17b8\",\"name_chinese\":null,\"gender\":\"female\",\"date_of_birth\":\"2005-09-15\",\"nationality\":\"Khmer\",\"national_id\":\"171234568\",\"marital_status\":\"divorced\",\"phone\":\"012345678\",\"telegram\":\"@Saorypov\",\"email\":\"saory7@gmail.com\",\"facebook_url\":\"https:\\/\\/www.facebook.com\\/saory.pov7\",\"linkedin_url\":null,\"address\":null,\"province\":\"Phnom Penh\",\"country\":null,\"emergency_contact_name\":\"RATANAK KHOEURN\",\"emergency_contact_relationship\":\"Spouse\",\"emergency_contact_phone\":\"0962728396\",\"preferred_channel\":\"telegram\",\"preferred_language\":\"Khmer\",\"registered_date\":\"2026-11-06\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":null,\"employment_status\":null,\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":null,\"internal_notes\":null,\"sc_campus_id\":\"1\",\"sc_course_id\":\"1\",\"sc_academic_year\":\"1\",\"sc_intake_id\":\"1\",\"sc_semester_id\":\"1\",\"sc_batch_id\":\"1\",\"sc_study_group_id\":\"1\",\"sc_term\":\"1\",\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":\"Computer Skill-2026-G1\",\"sc_instructor_name\":\"Vandet Noun\",\"sc_enrollment_date\":\"2026-11-06\",\"sc_course_start_date\":null,\"sc_course_completion_date\":null,\"sc_graduation_date\":null,\"sc_graduation_year\":\"2023\",\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":null,\"emp_job_title\":null,\"emp_industry\":null,\"emp_department\":null,\"emp_company_address\":null,\"emp_work_phone\":null,\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":null,\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false,\"_original\":{\"name_english\":\"Sok Dara\",\"name_khmer\":\"\\u179f\\u17bb\\u1781 \\u178a\\u17b6\\u179a\\u17c9\\u17b6\",\"name_chinese\":null,\"gender\":\"male\",\"date_of_birth\":null,\"nationality\":null,\"national_id\":null,\"marital_status\":null,\"phone\":\"012345678\",\"telegram\":\"@dara\",\"email\":\"dara@example.com\",\"facebook_url\":null,\"linkedin_url\":null,\"address\":null,\"province\":\"Phnom Penh\",\"country\":null,\"emergency_contact_name\":null,\"emergency_contact_relationship\":null,\"emergency_contact_phone\":null,\"preferred_channel\":null,\"preferred_language\":null,\"registered_date\":\"2026-11-05T17:00:00.000000Z\",\"status\":\"active_alumni\",\"student_stage\":\"current_student\",\"graduation_status\":\"not_graduated\",\"education_status\":null,\"employment_status\":null,\"professional_skills\":null,\"technical_skills\":null,\"certifications\":null,\"portfolio_url\":null,\"career_goals\":null,\"career_notes\":null,\"alumni_membership_number\":null,\"alumni_group\":null,\"engagement_status\":null,\"last_contacted_date\":null,\"next_followup_date\":null,\"referral_info\":null,\"alumni_feedback\":null,\"engagement_notes\":null,\"assigned_staff_id\":3,\"internal_notes\":null,\"sc_campus_id\":null,\"sc_course_id\":null,\"sc_academic_year\":null,\"sc_intake_id\":null,\"sc_semester_id\":null,\"sc_batch_id\":null,\"sc_study_group_id\":null,\"sc_term\":null,\"sc_registration_number\":null,\"sc_major\":null,\"sc_class_group\":null,\"sc_instructor_name\":null,\"sc_enrollment_date\":null,\"sc_course_start_date\":null,\"sc_course_completion_date\":null,\"sc_graduation_date\":null,\"sc_graduation_year\":2023,\"sc_attendance_percentage\":null,\"sc_grade\":null,\"sc_scholarship_status\":null,\"sc_internship_company\":null,\"sc_graduation_project\":null,\"sc_withdrawal_reason\":null,\"sc_alumni_membership_status\":null,\"sc_alumni_membership_date\":null,\"sc_notes\":null,\"edu_institution\":null,\"edu_degree_program\":null,\"edu_major\":null,\"edu_year_level\":null,\"edu_start_date\":null,\"edu_expected_graduation_date\":null,\"edu_scholarship_info\":null,\"edu_further_plans\":null,\"emp_company\":null,\"emp_job_title\":null,\"emp_industry\":null,\"emp_department\":null,\"emp_company_address\":null,\"emp_work_phone\":null,\"emp_work_email\":null,\"emp_start_date\":null,\"emp_salary_range\":null,\"emp_employment_type\":null,\"is_currently_studying\":false,\"is_currently_employed\":false,\"is_seeking_employment\":false,\"interested_jobs\":false,\"interested_internships\":false,\"interested_mentoring\":false,\"interested_trainer\":false,\"interested_volunteering\":false,\"interested_activities\":false,\"interested_newsletter\":false,\"interested_job_announcements\":false,\"interested_training\":false,\"sc_certificate_received\":false,\"sc_internship_participation\":false},\"_changes\":{\"name_english\":{\"old\":\"Sok Dara\",\"new\":\"Pov Saory\"},\"name_khmer\":{\"old\":\"\\u179f\\u17bb\\u1781 \\u178a\\u17b6\\u179a\\u17c9\\u17b6\",\"new\":\"\\u1796\\u17c5 \\u179f\\u17c5\\u179a\\u17b8\"},\"gender\":{\"old\":\"male\",\"new\":\"female\"},\"date_of_birth\":{\"old\":null,\"new\":\"2005-09-15\"},\"nationality\":{\"old\":null,\"new\":\"Khmer\"},\"national_id\":{\"old\":null,\"new\":\"171234568\"},\"marital_status\":{\"old\":null,\"new\":\"divorced\"},\"telegram\":{\"old\":\"@dara\",\"new\":\"@Saorypov\"},\"email\":{\"old\":\"dara@example.com\",\"new\":\"saory7@gmail.com\"},\"facebook_url\":{\"old\":null,\"new\":\"https:\\/\\/www.facebook.com\\/saory.pov7\"},\"emergency_contact_name\":{\"old\":null,\"new\":\"RATANAK KHOEURN\"},\"emergency_contact_relationship\":{\"old\":null,\"new\":\"Spouse\"},\"emergency_contact_phone\":{\"old\":null,\"new\":\"0962728396\"},\"preferred_channel\":{\"old\":null,\"new\":\"telegram\"},\"preferred_language\":{\"old\":null,\"new\":\"Khmer\"},\"assigned_staff_id\":{\"old\":3,\"new\":null},\"sc_campus_id\":{\"old\":null,\"new\":\"1\"},\"sc_course_id\":{\"old\":null,\"new\":\"1\"},\"sc_academic_year\":{\"old\":null,\"new\":\"1\"},\"sc_intake_id\":{\"old\":null,\"new\":\"1\"},\"sc_semester_id\":{\"old\":null,\"new\":\"1\"},\"sc_batch_id\":{\"old\":null,\"new\":\"1\"},\"sc_study_group_id\":{\"old\":null,\"new\":\"1\"},\"sc_term\":{\"old\":null,\"new\":\"1\"},\"sc_class_group\":{\"old\":null,\"new\":\"Computer Skill-2026-G1\"},\"sc_instructor_name\":{\"old\":null,\"new\":\"Vandet Noun\"},\"sc_enrollment_date\":{\"old\":null,\"new\":\"2026-11-06\"}},\"_staged_files\":{\"photo\":{\"path\":\"student-change-requests\\/10\\/5CosCHjh41uVYSs47AAsa1uFi3BRANsouoRFQQq4.png\",\"original_name\":\"My love.png\",\"size\":2419320}}}',NULL,'approved',NULL,'2026-11-06 08:44:02','2026-11-06 08:43:52','2026-11-06 08:44:02');
/*!40000 ALTER TABLE `student_change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_courses`
--

DROP TABLE IF EXISTS `student_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `study_group_id` bigint(20) unsigned DEFAULT NULL,
  `enrollment_status` varchar(30) NOT NULL DEFAULT 'in_progress',
  `batch_id` bigint(20) unsigned DEFAULT NULL,
  `intake_id` bigint(20) unsigned DEFAULT NULL,
  `semester_id` bigint(20) unsigned DEFAULT NULL,
  `center_name` varchar(255) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `major` varchar(255) DEFAULT NULL,
  `class_group` varchar(255) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `course_start_date` date DEFAULT NULL,
  `course_completion_date` date DEFAULT NULL,
  `graduation_date` date DEFAULT NULL,
  `graduation_year` smallint(5) unsigned DEFAULT NULL,
  `attendance_percentage` decimal(5,2) DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `certificate_received` tinyint(1) NOT NULL DEFAULT 0,
  `certificate_number` varchar(255) DEFAULT NULL,
  `scholarship_status` varchar(100) DEFAULT NULL,
  `internship_participation` tinyint(1) NOT NULL DEFAULT 0,
  `internship_company` varchar(255) DEFAULT NULL,
  `graduation_project` varchar(255) DEFAULT NULL,
  `withdrawal_reason` text DEFAULT NULL,
  `alumni_membership_status` varchar(50) DEFAULT NULL,
  `alumni_membership_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_courses_student_id_foreign` (`student_id`),
  KEY `student_courses_campus_id_foreign` (`campus_id`),
  KEY `student_courses_course_id_foreign` (`course_id`),
  KEY `student_courses_graduation_year_index` (`graduation_year`),
  KEY `student_courses_batch_id_foreign` (`batch_id`),
  KEY `student_courses_intake_id_foreign` (`intake_id`),
  KEY `student_courses_semester_id_foreign` (`semester_id`),
  KEY `student_courses_enrollment_status_index` (`enrollment_status`),
  KEY `student_courses_study_group_id_foreign` (`study_group_id`),
  CONSTRAINT `student_courses_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_courses_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_courses_intake_id_foreign` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_courses_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_courses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_courses_study_group_id_foreign` FOREIGN KEY (`study_group_id`) REFERENCES `study_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_courses`
--

LOCK TABLES `student_courses` WRITE;
/*!40000 ALTER TABLE `student_courses` DISABLE KEYS */;
INSERT INTO `student_courses` VALUES (3,1,1,1,2,'completed',1,1,1,'I-Tech Media Center',NULL,NULL,'Computer Skill-2026-G1','Reaksmey Ky','2026-09-01','2026-10-31','2026-10-31','2026-11-15',NULL,NULL,NULL,0,NULL,NULL,0,NULL,NULL,NULL,'member',NULL,'Test','2026-10-21 21:44:35','2026-11-06 07:43:40'),(6,1,1,2,NULL,'completed',2,2,1,'I-Tech Media Center','02',NULL,NULL,NULL,NULL,NULL,'2026-11-03','2026-11-03',2027,NULL,'A',0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-21 23:41:25','2026-11-02 20:05:23'),(7,2,1,1,1,'completed',1,1,1,'I-Tech Media Center',NULL,'Sale Manager','Computer Skill-2026-G1','Vandet Noun','2026-11-03','2026-11-16','2027-01-14','2027-01-25',2027,100.00,'C',1,NULL,NULL,0,NULL,NULL,NULL,'member','2026-11-05',NULL,'2026-11-02 19:00:55','2026-11-06 06:24:32'),(9,3,1,1,1,'in_progress',1,1,1,'I-Tech Media Center',NULL,NULL,'Computer Skill-2026-G1','Vandet Noun','2026-11-06',NULL,NULL,NULL,2023,NULL,NULL,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:38:39','2026-11-06 08:44:02'),(10,4,1,1,NULL,'in_progress',1,1,1,'I-Tech Media Center',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2022,NULL,NULL,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06 04:38:39','2026-11-06 04:40:52'),(24,1,1,2,NULL,'in_progress',2,1,1,'I-Tech Media Center','03',NULL,NULL,NULL,'2026-11-13','2026-11-16','2027-01-15',NULL,NULL,NULL,'GD',0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'Add New Course','2026-11-13 07:06:49','2026-11-13 07:06:49');
/*!40000 ALTER TABLE `student_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `doc_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_sensitive` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_documents_student_id_foreign` (`student_id`),
  KEY `student_documents_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `student_documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
INSERT INTO `student_documents` VALUES (1,1,'Graduation','photo','students/documents/GuW06nrTiNkNIoSMdpX2MjHRMm6PUeHfzY33JXKQ.jpg','1320567','Zion students Graduation day',1,3,'2026-11-02 20:42:04','2026-11-02 20:42:04');
/*!40000 ALTER TABLE `student_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_face_profiles`
--

DROP TABLE IF EXISTS `student_face_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_face_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `embedding` longtext NOT NULL,
  `model_version` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `consent_obtained_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_face_profiles_student_id_unique` (`student_id`),
  CONSTRAINT `student_face_profiles_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_face_profiles`
--

LOCK TABLES `student_face_profiles` WRITE;
/*!40000 ALTER TABLE `student_face_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_face_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_movements`
--

DROP TABLE IF EXISTS `student_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `movement_type` varchar(40) NOT NULL,
  `from_batch_id` bigint(20) unsigned DEFAULT NULL,
  `to_batch_id` bigint(20) unsigned DEFAULT NULL,
  `from_campus_id` bigint(20) unsigned DEFAULT NULL,
  `to_campus_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(40) DEFAULT NULL,
  `to_status` varchar(40) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_movements_from_batch_id_foreign` (`from_batch_id`),
  KEY `student_movements_to_batch_id_foreign` (`to_batch_id`),
  KEY `student_movements_from_campus_id_foreign` (`from_campus_id`),
  KEY `student_movements_to_campus_id_foreign` (`to_campus_id`),
  KEY `student_movements_created_by_foreign` (`created_by`),
  KEY `student_movements_student_id_movement_type_index` (`student_id`,`movement_type`),
  CONSTRAINT `student_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_movements_from_batch_id_foreign` FOREIGN KEY (`from_batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_movements_from_campus_id_foreign` FOREIGN KEY (`from_campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_movements_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_movements_to_batch_id_foreign` FOREIGN KEY (`to_batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_movements_to_campus_id_foreign` FOREIGN KEY (`to_campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_movements`
--

LOCK TABLES `student_movements` WRITE;
/*!40000 ALTER TABLE `student_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_notes`
--

DROP TABLE IF EXISTS `student_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_notes_student_id_foreign` (`student_id`),
  KEY `student_notes_created_by_foreign` (`created_by`),
  CONSTRAINT `student_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_notes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_notes`
--

LOCK TABLES `student_notes` WRITE;
/*!40000 ALTER TABLE `student_notes` DISABLE KEYS */;
INSERT INTO `student_notes` VALUES (1,1,'This student is allergic to Beer',0,3,'2026-11-06 03:55:56','2026-11-06 03:55:56');
/*!40000 ALTER TABLE `student_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_code` varchar(30) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `name_khmer` varchar(255) DEFAULT NULL,
  `name_english` varchar(255) NOT NULL,
  `name_chinese` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `marital_status` varchar(30) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `telegram` varchar(150) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_relationship` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `preferred_channel` varchar(50) DEFAULT NULL,
  `preferred_language` varchar(50) DEFAULT NULL,
  `registered_date` date DEFAULT NULL,
  `status` enum('active_alumni','inactive','unreachable','do_not_contact') NOT NULL DEFAULT 'active_alumni',
  `student_stage` varchar(30) NOT NULL DEFAULT 'current_student',
  `graduation_status` varchar(30) NOT NULL DEFAULT 'not_graduated',
  `education_status` varchar(30) DEFAULT NULL,
  `employment_status` varchar(30) DEFAULT NULL,
  `is_currently_studying` tinyint(1) NOT NULL DEFAULT 0,
  `is_currently_employed` tinyint(1) NOT NULL DEFAULT 0,
  `is_seeking_employment` tinyint(1) NOT NULL DEFAULT 0,
  `professional_skills` text DEFAULT NULL,
  `technical_skills` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `career_goals` text DEFAULT NULL,
  `interested_jobs` tinyint(1) NOT NULL DEFAULT 0,
  `interested_internships` tinyint(1) NOT NULL DEFAULT 0,
  `interested_mentoring` tinyint(1) NOT NULL DEFAULT 0,
  `interested_trainer` tinyint(1) NOT NULL DEFAULT 0,
  `interested_volunteering` tinyint(1) NOT NULL DEFAULT 0,
  `career_notes` text DEFAULT NULL,
  `alumni_membership_number` varchar(50) DEFAULT NULL,
  `alumni_group` varchar(255) DEFAULT NULL,
  `interested_activities` tinyint(1) NOT NULL DEFAULT 0,
  `interested_newsletter` tinyint(1) NOT NULL DEFAULT 0,
  `interested_job_announcements` tinyint(1) NOT NULL DEFAULT 0,
  `interested_training` tinyint(1) NOT NULL DEFAULT 0,
  `last_contacted_date` date DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `engagement_status` varchar(50) DEFAULT NULL,
  `pipeline_stage` varchar(30) NOT NULL DEFAULT 'not_contacted',
  `referral_info` text DEFAULT NULL,
  `alumni_feedback` text DEFAULT NULL,
  `engagement_notes` text DEFAULT NULL,
  `assigned_staff_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `record_status` enum('active','archived') NOT NULL DEFAULT 'active',
  `internal_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_student_code_unique` (`student_code`),
  KEY `students_assigned_staff_id_foreign` (`assigned_staff_id`),
  KEY `students_created_by_foreign` (`created_by`),
  KEY `students_updated_by_foreign` (`updated_by`),
  KEY `students_status_record_status_index` (`status`,`record_status`),
  KEY `students_email_index` (`email`),
  KEY `students_phone_index` (`phone`),
  KEY `students_stage_record_status_index` (`student_stage`,`record_status`),
  KEY `students_lifecycle_graduation_index` (`student_stage`,`graduation_status`,`record_status`),
  KEY `students_pipeline_stage_index` (`pipeline_stage`),
  CONSTRAINT `students_assigned_staff_id_foreign` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'STU-2026-0001','students/photos/miQIs2khaXMgbe2jtAu7nXUxHTIkQcfmcEV9LYnR.jpg','ខឿន រតនៈ','Ratanak Khoeurn','Ratanak Khoeurn','male','2004-07-25','Khmer','171223074','single','0962728396','0962728396','k.ratanak@itechmediakh.com','https://www.facebook.com/ratanak.khoeun.3','https://www.linkedin.com/in/ratanak-khoeurn-98b0a7287/','Building #108, St. 472, Sangkat Toul Tom Poung I, Khan Chamkarmorn, Phnom Penh','Phnom Penh','Cambodia','Ratana Khoeurn','Sibling','0962728396','telegram','Khmer','2026-10-21','active_alumni','alumni','not_graduated','graduated','employed',1,0,0,'IT Assistant, Web Development, Backend Development','IT Skills',NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,'2026-11-03','2026-11-04',NULL,'unreachable',NULL,NULL,NULL,NULL,3,3,'active',NULL,'2026-10-20 23:11:43','2026-11-17 04:11:04',NULL),(2,'STU-2026-0002','students/photos/wRW2I90eieItOZ8FrPuPFhYSP4CIEcgHrHgJIDjM.jpg','ខឿន រតនា','Ratana Khoeurn','Ratana khoeurn','male','2026-02-05','Khmer','1234568788','single','012622524','012622524','ratanakhoeurn1@gmail.com','https://www.facebook.com/ratana.khoeun.4','https://www.linkedin.com/in/ratana-khoeurn-4/','អគារលេខ 251 ផ្លូវ 472 សង្កាត់ទួលទំពូងទី១ ខណ្ឌចំការមន រាជធានីភ្នំពេញ។','Phnom Penh','Cambodia','RATANAK KHOEURN','Sibling','0962728396','telegram','Khmer','2026-11-03','active_alumni','alumni','graduated','graduated','employed',0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,'2026-11-17','2026-11-18',NULL,'unreachable',NULL,NULL,NULL,5,3,3,'active','Computer Office student enrol in football club','2026-11-02 19:00:55','2026-11-17 06:05:45',NULL),(3,'STU-2026-0003','students/photos/5CosCHjh41uVYSs47AAsa1uFi3BRANsouoRFQQq4.png','ពៅ សៅរី','Pov Saory',NULL,'female','2005-09-15','Khmer','171234568','divorced','012345678','@Saorypov','saory7@gmail.com','https://www.facebook.com/saory.pov7',NULL,NULL,'Phnom Penh',NULL,'RATANAK KHOEURN','Spouse','0962728396','telegram','Khmer','2026-11-06','active_alumni','current_student','not_graduated',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,'2026-11-17','2026-11-18',NULL,'unreachable',NULL,NULL,NULL,5,3,3,'active',NULL,'2026-11-06 04:38:39','2026-11-17 05:04:35',NULL),(4,'STU-2026-0004',NULL,'ចាន់ បុប្ផា','Chan Bopha',NULL,'female',NULL,NULL,NULL,NULL,'098765432','@bopha','bopha@example.com',NULL,NULL,NULL,'Siem Reap',NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-06','active_alumni','current_student','not_graduated',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,0,NULL,NULL,NULL,0,0,0,0,NULL,NULL,NULL,'not_contacted',NULL,NULL,NULL,NULL,3,3,'archived',NULL,'2026-11-06 04:38:39','2026-11-06 08:46:38',NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_group_student`
--

DROP TABLE IF EXISTS `study_group_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_group_student` (
  `study_group_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`study_group_id`,`student_id`),
  KEY `study_group_student_student_id_foreign` (`student_id`),
  CONSTRAINT `study_group_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `study_group_student_study_group_id_foreign` FOREIGN KEY (`study_group_id`) REFERENCES `study_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_group_student`
--

LOCK TABLES `study_group_student` WRITE;
/*!40000 ALTER TABLE `study_group_student` DISABLE KEYS */;
INSERT INTO `study_group_student` VALUES (1,2,'2026-11-06 06:37:04','2026-11-06 06:37:04'),(1,3,'2026-11-06 08:44:02','2026-11-06 08:44:02'),(2,1,'2026-11-06 07:43:40','2026-11-06 07:43:40'),(2,4,'2026-11-06 07:46:01','2026-11-06 07:46:01');
/*!40000 ALTER TABLE `study_group_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `study_groups`
--

DROP TABLE IF EXISTS `study_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(60) DEFAULT NULL,
  `campus_id` bigint(20) unsigned DEFAULT NULL,
  `course_category_id` bigint(20) unsigned DEFAULT NULL,
  `academic_year_id` bigint(20) unsigned DEFAULT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `intake_id` bigint(20) unsigned DEFAULT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `study_time` varchar(80) DEFAULT NULL,
  `all_courses` tinyint(1) NOT NULL DEFAULT 1,
  `graduation_year` smallint(5) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `study_groups_code_unique` (`code`),
  KEY `study_groups_campus_id_foreign` (`campus_id`),
  KEY `study_groups_course_category_id_foreign` (`course_category_id`),
  KEY `study_groups_academic_year_id_foreign` (`academic_year_id`),
  KEY `study_groups_program_id_foreign` (`program_id`),
  KEY `study_groups_intake_id_foreign` (`intake_id`),
  KEY `study_groups_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `study_groups_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_groups_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_groups_course_category_id_foreign` FOREIGN KEY (`course_category_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_groups_intake_id_foreign` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_groups_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `study_groups_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_groups`
--

LOCK TABLES `study_groups` WRITE;
/*!40000 ALTER TABLE `study_groups` DISABLE KEYS */;
INSERT INTO `study_groups` VALUES (1,'Computer Skill-2026-G1','COC-2026-01',1,3,1,1,3,1,'Morning 7:00-9:00',0,2027,NULL,'2026-11-02 19:58:23','2026-11-06 06:19:25'),(2,'Computer Skill-2026-G1','COC-2026-G2',1,3,1,1,1,3,NULL,0,2027,NULL,'2026-11-02 20:07:34','2026-11-06 04:37:55'),(3,'Graphic Design-2026-G1','GD-2026-01',1,5,6,1,3,3,NULL,0,2027,NULL,'2026-11-06 05:00:51','2026-11-06 05:00:51');
/*!40000 ALTER TABLE `study_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Super Admin','superadmin@itechmediakh.com','012345678',NULL,1,NULL,'$2y$12$8DYVKcqgxS/xwDp143LJIOhe0yqY0cOWz5WEKjkuqA7vg7GYQIGli',NULL,'2026-10-20 21:45:45','2026-10-20 21:45:45'),(2,1,'Kakada Horn','h.kakada@itechmediakh.com','012345672',NULL,1,NULL,'$2y$12$FE9GfNzMfqZU7qIqxt/Ka.PBrSo1Ds4yXZM0wCyTdYZSSJkasDmOa',NULL,'2026-10-20 21:45:45','2026-10-20 21:45:45'),(3,1,'Ratanak Khoeurn','k.ratanak@itechmediakh.com','012345679','users/avatars/nPFQnxX4gUduvjvxzrH6lBCrPYfhOQ6CffsfpjdT.jpg',1,NULL,'$2y$12$bFAh/X6qN5MEG8zKyie9CeOYdu2QLRV3iT/KVK30ZuURcO1sJMlma',NULL,'2026-10-20 21:45:45','2026-10-21 03:04:38'),(4,3,'Si Ey Sor','s.siey@itechmediakh.com','012345680',NULL,1,NULL,'$2y$12$t8H2Ynyyl9YZpgSLPOen3OimN7KfPeFkHPDqt9vq.NFy5YwSngB5q',NULL,'2026-10-20 21:45:45','2026-10-20 21:45:45'),(5,3,'Sereykosal Ken','k.sereykosal@itechmediakh.com','012345681',NULL,1,NULL,'$2y$12$V3uCamo2CQyoGgH2cvzraOoziLlGXy8Gu0Wa.udYyh8C6nYF8sGYW',NULL,'2026-10-20 21:45:45','2026-11-10 02:05:19'),(6,4,'Student','student@itechmediakh.com','012345682',NULL,1,NULL,'$2y$12$St4dSeNqXT5VhNrjpoETp.IKBSLFunl.K2H4iDTMcPriLuEhJjc9i',NULL,'2026-10-20 21:45:46','2026-10-20 21:45:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'student_crm'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-26  9:31:50
