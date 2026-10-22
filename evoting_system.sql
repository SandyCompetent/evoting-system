-- MySQL dump 10.13  Distrib 26.7.0, for macos27.0 (arm64)
--
-- Host: localhost    Database: evoting_system
-- ------------------------------------------------------
-- Server version	26.7.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--


--
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `photo` varchar(100) NOT NULL,
  `symbol` varchar(100) NOT NULL,
  `category_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate`
--

LOCK TABLES `candidate` WRITE;
/*!40000 ALTER TABLE `candidate` DISABLE KEYS */;
INSERT INTO `candidate` VALUES (25,'Akshant','13.jpg','13.png',30,'2020-09-24 17:47:17'),(26,'Dhruv','01.jpg','01.png',30,'2020-09-24 17:47:52'),(27,'Mishika','11.jpg','11.png',30,'2020-09-24 17:48:38');
/*!40000 ALTER TABLE `candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_category`
--

DROP TABLE IF EXISTS `election_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `max_voter` int NOT NULL,
  `adult_voter` varchar(10) NOT NULL DEFAULT 'No',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expire_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_category`
--

LOCK TABLES `election_category` WRITE;
/*!40000 ALTER TABLE `election_category` DISABLE KEYS */;
INSERT INTO `election_category` VALUES (30,'TechComp Alumini Selection',0,'No','2020-09-24 17:27:10','2020-09-27 06:30:00','2020-09-23 06:30:10','2020-09-23 06:30:58'),(31,'College HOD Selection',0,'No','2020-09-24 17:27:49','2020-09-30 06:30:00','2020-09-24 17:27:49','2020-09-24 17:27:49'),(32,'CEO Selection for PiedPiper Project',0,'No','2020-09-24 17:28:17','2020-10-15 06:30:00','2020-09-24 17:28:17','2020-09-24 17:28:17');
/*!40000 ALTER TABLE `election_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_to_register`
--

DROP TABLE IF EXISTS `request_to_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_to_register` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `election_category` varchar(100) NOT NULL,
  `max_voter` int NOT NULL,
  `adult_voter` varchar(10) NOT NULL DEFAULT '''No''',
  `expire_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ac_status` varchar(10) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_to_register`
--

LOCK TABLES `request_to_register` WRITE;
/*!40000 ALTER TABLE `request_to_register` DISABLE KEYS */;
INSERT INTO `request_to_register` VALUES (7,'Sandeep','sandy@evoting.com','TechComp Alumini Selection',40,'Yes','2020-09-27 06:30:00','2020-09-24 17:26:04','pending');
/*!40000 ALTER TABLE `request_to_register` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_users`
--

DROP TABLE IF EXISTS `system_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_birth` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT '12346578',
  `role` enum('admin','organiser','voter') NOT NULL DEFAULT 'voter',
  `election_category_id` int NOT NULL,
  `voted` varchar(4) DEFAULT 'No',
  `created_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=386551 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_users`
--

LOCK TABLES `system_users` WRITE;
/*!40000 ALTER TABLE `system_users` DISABLE KEYS */;
INSERT INTO `system_users` VALUES (1,'Admin','admin@evoting.com',NULL,'$2y$12$h1fxS5SEgNJ2CYG5wo8h/OcRKllm7SWElY7OH37NIb9CYLkUKovse','admin',0,NULL,'2020-03-17 10:47:13'),(386543,'Sandeep','sandy@evoting.com',NULL,'$2y$12$02qnNaMhuDnj2NBywD09yOKqcggk6odsCc5sOZ9CdTMWMRs5lcX.q','organiser',30,'No','2020-09-24 17:27:10'),(386544,'Ivaan','ivaan@evoting.com',NULL,'7c222fb2927d828af22f592134e8932480637c0d','organiser',31,'No','2020-09-24 17:28:56'),(386545,'Vivaan','vivaan@evoting.com',NULL,'7c222fb2927d828af22f592134e8932480637c0d','organiser',32,'No','2020-09-24 17:29:16'),(386546,'Anaya','anaya@evoting.com',NULL,'7c222fb2927d828af22f592134e8932480637c0d','organiser',32,'No','2020-09-24 17:30:35'),(386547,'?John','john@evoting.com','20-10-1999','7c222fb2927d828af22f592134e8932480637c0d','voter',30,'Yes','2020-09-24 17:48:55'),(386548,'Thomas','thomas@evoting.com','15-02-1998','7c222fb2927d828af22f592134e8932480637c0d','voter',30,'No','2020-09-24 17:48:55'),(386549,'Robin','robin@evoting.com','12-01-1999','7c222fb2927d828af22f592134e8932480637c0d','voter',30,'No','2020-09-24 17:48:55'),(386550,'Sudhan','sudhan@evoting.com','30-05-1998','$2y$12$q6HDEAfwypMPVQwS9Av1qu6c7y01lOK8bWLlyibJ8pp5Nopwvhn9O','voter',30,'No','2020-09-24 17:48:55');
/*!40000 ALTER TABLE `system_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voter_id` int NOT NULL,
  `category_id` int NOT NULL,
  `candidate_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_one_vote_per_election` (`voter_id`,`category_id`),
  KEY `idx_tally` (`category_id`,`candidate_id`),
  KEY `fk_vote_candidate` (`candidate_id`),
  CONSTRAINT `fk_vote_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vote_category` FOREIGN KEY (`category_id`) REFERENCES `election_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vote_voter` FOREIGN KEY (`voter_id`) REFERENCES `system_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_details`
--

DROP TABLE IF EXISTS `vote_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `candidate_id` int NOT NULL,
  `vote` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_details`
--

LOCK TABLES `vote_details` WRITE;
/*!40000 ALTER TABLE `vote_details` DISABLE KEYS */;
INSERT INTO `vote_details` VALUES (9,30,25,0),(10,30,26,1),(11,30,27,0);
/*!40000 ALTER TABLE `vote_details` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 16:03:53
