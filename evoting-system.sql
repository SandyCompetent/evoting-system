-- MySQL dump for e-Voting System
-- Schema & Default Seed Dataset (October 2026)
-- Database: evoting_system
-- ------------------------------------------------------

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
-- Table structure for table `election_category`
--

DROP TABLE IF EXISTS `election_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_voter` int NOT NULL DEFAULT '0',
  `adult_voter` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expire_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_category`
--

LOCK TABLES `election_category` WRITE;
/*!40000 ALTER TABLE `election_category` DISABLE KEYS */;
INSERT INTO `election_category` VALUES
(30,'Student Council General Election',50,'Yes','2026-10-20 08:00:00','2026-10-28 00:00:00','2026-10-20 09:00:00','2026-10-27 18:00:00'),
(31,'Faculty Board Representative',30,'Yes','2026-10-21 09:00:00','2026-11-02 00:00:00','2026-10-25 09:00:00','2026-11-01 18:00:00'),
(32,'Alumni Advisory Board',25,'No','2026-10-14 08:00:00','2026-10-21 18:00:00','2026-10-14 09:00:00','2026-10-21 18:00:00');
/*!40000 ALTER TABLE `election_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_candidate_category` (`category_id`),
  CONSTRAINT `fk_candidate_category` FOREIGN KEY (`category_id`) REFERENCES `election_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate`
--

LOCK TABLES `candidate` WRITE;
/*!40000 ALTER TABLE `candidate` DISABLE KEYS */;
INSERT INTO `candidate` VALUES
-- Candidates for Category 30 (Student Council - Live)
(25,'Alex Rivera','alex_rivera.jpg','star.png',30,'2026-10-20 08:30:00'),
(26,'Jane Doe','jane_doe.jpg','eagle.png',30,'2026-10-20 08:31:00'),
(27,'Michael Chang','michael_chang.jpg','sun.png',30,'2026-10-20 08:32:00'),
(28,'Chloe Bennett','chloe_bennett.jpg','tree.png',30,'2026-10-20 08:33:00'),
-- Candidates for Category 31 (Faculty Board - Upcoming)
(29,'Prof. David Miller','david_miller.jpg','book.png',31,'2026-10-21 09:30:00'),
(30,'Dr. Elena Rostova','elena_rostova.jpg','globe.png',31,'2026-10-21 09:31:00'),
(31,'Sophia Patel','sophia_patel.jpg','feather.png',31,'2026-10-21 09:32:00'),
-- Candidates for Category 32 (Alumni Advisory - Concluded)
(32,'James Wilson','james_wilson.jpg','torch.png',32,'2026-10-14 08:30:00'),
(33,'Emily Watson','emily_watson.jpg','compass.png',32,'2026-10-14 08:31:00'),
(34,'Daniel Craig','daniel_craig.jpg','shield.png',32,'2026-10-14 08:32:00');
/*!40000 ALTER TABLE `candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_users`
--

DROP TABLE IF EXISTS `system_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','organiser','voter') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'voter',
  `election_category_id` int DEFAULT NULL,
  `voted` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT 'No',
  `created_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role_category` (`role`,`election_category_id`),
  CONSTRAINT `fk_system_users_category` FOREIGN KEY (`election_category_id`) REFERENCES `election_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=386560 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_users`
-- Note: bcrypt hash for 'password123' is $2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.
-- Admin password also accepts '12345678' ($2y$12$h1fxS5SEgNJ2CYG5wo8h/OcRKllm7SWElY7OH37NIb9CYLkUKovse)
--

LOCK TABLES `system_users` WRITE;
/*!40000 ALTER TABLE `system_users` DISABLE KEYS */;
INSERT INTO `system_users` VALUES
-- Admin
(1,'System Admin','admin@evoting.com',NULL,'$2y$12$h1fxS5SEgNJ2CYG5wo8h/OcRKllm7SWElY7OH37NIb9CYLkUKovse','admin',NULL,NULL,'2026-10-01 10:00:00'),
-- Organisers
(2,'Sarah Jenkins','council.organiser@evoting.com',NULL,'$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','organiser',30,'No','2026-10-20 08:00:00'),
(3,'Marcus Vance','faculty.organiser@evoting.com',NULL,'$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','organiser',31,'No','2026-10-21 09:00:00'),
(4,'Rachel Green','alumni.organiser@evoting.com',NULL,'$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','organiser',32,'No','2026-10-14 08:00:00'),
-- Category 30 Voters (Student Council - 20 voters: 12 voted, 8 ready to vote)
(101,'Emma Smith','voter1@evoting.com','2001-03-15','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:05:00'),
(102,'Liam Johnson','voter2@evoting.com','2000-07-22','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:10:00'),
(103,'Olivia Williams','voter3@evoting.com','2002-01-10','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:12:00'),
(104,'Noah Brown','voter4@evoting.com','2001-11-04','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:15:00'),
(105,'Sophia Jones','voter5@evoting.com','1999-09-30','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:20:00'),
(106,'Lucas Garcia','voter6@evoting.com','2000-04-18','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:25:00'),
(107,'Ava Miller','voter7@evoting.com','2001-06-12','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:30:00'),
(108,'Oliver Davis','voter8@evoting.com','2002-08-25','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:35:00'),
(109,'Mia Rodriguez','voter9@evoting.com','2000-12-05','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:40:00'),
(110,'Ethan Martinez','voter10@evoting.com','2001-05-19','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:45:00'),
(111,'Harper Hernandez','voter11@evoting.com','1999-02-14','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:50:00'),
(112,'Mason Lopez','voter12@evoting.com','2002-10-08','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'Yes','2026-10-20 09:55:00'),
(113,'Evelyn Gonzalez','voter13@evoting.com','2001-04-03','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(114,'Logan Wilson','voter14@evoting.com','2000-08-16','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(115,'Abigail Anderson','voter15@evoting.com','2001-12-21','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(116,'James Thomas','voter16@evoting.com','2002-03-30','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(117,'Emily Taylor','voter17@evoting.com','2000-11-11','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(118,'Alexander Moore','voter18@evoting.com','2001-09-09','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(119,'Ella Jackson','voter19@evoting.com','2002-06-18','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
(120,'Henry Martin','voter20@evoting.com','1999-07-07','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',30,'No','2026-10-20 10:00:00'),
-- Category 31 Voters (Faculty Board - 5 sample voters, all unvoted)
(201,'Dr. Sarah Clark','fvoter1@evoting.com','1985-04-12','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',31,'No','2026-10-21 09:10:00'),
(202,'Prof. Alan Turing','fvoter2@evoting.com','1982-06-23','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',31,'No','2026-10-21 09:12:00'),
(203,'Dr. Grace Hopper','fvoter3@evoting.com','1988-12-09','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',31,'No','2026-10-21 09:14:00'),
(204,'Prof. Richard Feynman','fvoter4@evoting.com','1980-05-11','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',31,'No','2026-10-21 09:15:00'),
(205,'Dr. Marie Curie','fvoter5@evoting.com','1987-11-07','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',31,'No','2026-10-21 09:16:00'),
-- Category 32 Voters (Alumni Advisory - 15 voters: 14 voted)
(301,'Alumni John','alumni1@evoting.com','1992-03-20','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:00:00'),
(302,'Alumni Thomas','alumni2@evoting.com','1991-08-14','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:05:00'),
(303,'Alumni Robin','alumni3@evoting.com','1993-01-25','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:10:00'),
(304,'Alumni Sudhan','alumni4@evoting.com','1990-11-18','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:15:00'),
(305,'Alumni Clara','alumni5@evoting.com','1994-05-02','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:20:00'),
(306,'Alumni Brian','alumni6@evoting.com','1992-09-17','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:25:00'),
(307,'Alumni Fiona','alumni7@evoting.com','1993-04-30','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:30:00'),
(308,'Alumni George','alumni8@evoting.com','1991-12-12','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:35:00'),
(309,'Alumni Hannah','alumni9@evoting.com','1990-06-28','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:40:00'),
(310,'Alumni Ian','alumni10@evoting.com','1994-02-14','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:45:00'),
(311,'Alumni Julia','alumni11@evoting.com','1992-10-09','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:50:00'),
(312,'Alumni Kevin','alumni12@evoting.com','1993-07-04','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 10:55:00'),
(313,'Alumni Laura','alumni13@evoting.com','1991-01-19','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 11:00:00'),
(314,'Alumni Nathan','alumni14@evoting.com','1990-08-22','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'Yes','2026-10-15 11:05:00'),
(315,'Alumni Oscar','alumni15@evoting.com','1994-11-05','$2y$10$52Q.R/geqQ54jNpF3arcVebdOvYmRfZDGVaDrrMSjrWThF2z7kFe.','voter',32,'No','2026-10-15 11:10:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
-- Category 30: Alex Rivera(25) = 6, Jane Doe(26) = 4, Michael Chang(27) = 1, Chloe Bennett(28) = 1
-- Category 32: James Wilson(32) = 9 (Winner), Emily Watson(33) = 4, Daniel Craig(34) = 1
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
INSERT INTO `vote` VALUES
-- Category 30 (Student Council)
(1,101,30,25,'2026-10-20 10:00:00'),
(2,102,30,25,'2026-10-20 10:05:00'),
(3,103,30,25,'2026-10-20 10:10:00'),
(4,104,30,25,'2026-10-20 10:15:00'),
(5,105,30,25,'2026-10-20 10:20:00'),
(6,106,30,25,'2026-10-20 10:25:00'),
(7,107,30,26,'2026-10-20 10:30:00'),
(8,108,30,26,'2026-10-20 10:35:00'),
(9,109,30,26,'2026-10-20 10:40:00'),
(10,110,30,26,'2026-10-20 10:45:00'),
(11,111,30,27,'2026-10-20 10:50:00'),
(12,112,30,28,'2026-10-20 10:55:00'),
-- Category 32 (Alumni Advisory - Concluded)
(13,301,32,32,'2026-10-16 11:00:00'),
(14,302,32,32,'2026-10-16 11:05:00'),
(15,303,32,32,'2026-10-16 11:10:00'),
(16,304,32,32,'2026-10-16 11:15:00'),
(17,305,32,32,'2026-10-16 11:20:00'),
(18,306,32,32,'2026-10-16 11:25:00'),
(19,307,32,32,'2026-10-16 11:30:00'),
(20,308,32,32,'2026-10-16 11:35:00'),
(21,309,32,32,'2026-10-16 11:40:00'),
(22,310,32,33,'2026-10-16 11:45:00'),
(23,311,32,33,'2026-10-16 11:50:00'),
(24,312,32,33,'2026-10-16 11:55:00'),
(25,313,32,33,'2026-10-16 12:00:00'),
(26,314,32,34,'2026-10-16 12:05:00');
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_to_register`
--

DROP TABLE IF EXISTS `request_to_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_to_register` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `election_category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_voter` int NOT NULL,
  `adult_voter` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `expire_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ac_status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_to_register`
--

LOCK TABLES `request_to_register` WRITE;
/*!40000 ALTER TABLE `request_to_register` DISABLE KEYS */;
INSERT INTO `request_to_register` VALUES
(8,'Dr. Robert Chen','robert.chen@engineering.edu','Engineering Society Executive',40,'Yes','2026-11-05 18:00:00','2026-10-22 14:00:00','pending'),
(9,'Samantha Reed','samantha@athletics.org','Varsity Sports Club Captaincy',30,'No','2026-11-12 18:00:00','2026-10-22 15:30:00','pending');
/*!40000 ALTER TABLE `request_to_register` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 18:00:00
