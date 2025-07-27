CREATE DATABASE  IF NOT EXISTS `projectDB` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `projectDB`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: itp4523mproject
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actualmat`
--

DROP TABLE IF EXISTS `actualmat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actualmat` (
  `oid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `rqty` int(11) DEFAULT NULL,
  PRIMARY KEY (`oid`,`pid`,`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actualmat`
--

LOCK TABLES `actualmat` WRITE;
/*!40000 ALTER TABLE `actualmat` DISABLE KEYS */;
INSERT INTO `actualmat` VALUES (4,5,2,100),
(4,5,5,600),
(5,5,2,200),
(5,5,5,1200),
(6,1,4,3),
(6,1,5,18),
(7,2,3,5),
(7,2,5,10),
(3,2,3,5),
(3,2,5,10),
(2,1,4,3),
(2,1,5,18),
(1,1,5,18),
(1,1,4,3);
/*!40000 ALTER TABLE `actualmat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cpasswordreset`
--

DROP TABLE IF EXISTS `cpasswordreset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpasswordreset` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `token` varchar(6) NOT NULL,
  `attempt` int(11) DEFAULT 0,
  `expiry` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`rid`),
  KEY `cid` (`cid`),
  CONSTRAINT `cpasswordreset_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cpasswordreset`
--

LOCK TABLES `cpasswordreset` WRITE;
/*!40000 ALTER TABLE `cpasswordreset` DISABLE KEYS */;
INSERT INTO `cpasswordreset` VALUES (19,1,'696670',0,'2025-07-08 04:51:53',0,'2025-07-08 10:36:53','2025-07-08 10:37:09'),(20,1,'380812',0,'2025-07-08 05:15:15',0,'2025-07-08 11:00:15',NULL),(21,1,'116922',0,'2025-07-08 05:15:51',0,'2025-07-08 11:00:51','2025-07-08 11:01:07');
/*!40000 ALTER TABLE `cpasswordreset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cemail` varchar(255) DEFAULT NULL,
  `cpassword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ctel` int(11) DEFAULT NULL,
  `caddr` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `lastSession` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lastSession`)),
  `cimage` varchar(255) DEFAULT NULL,
  `cavail` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'Alex Wong','hkiit240064782@gmail.com','$2y$10$/eplKtMAU7LbVnKrdeWksezZdsOUWDXo6wumGnASv9K9W/VAqHUoW',21232123,'G/F, ABC Building, King Yip Street, KwunTong, Kowloon, Hong Kong','Fat Cat Company Limited','[]',NULL,1),(2,'Tina Chan','tinachan@xdd.com','$2y$10$aFUXp8pRYoYkAMC3C14FTe7T46vq2xkfqjG3ohX6MxRFyLO1HqOsO',31233123,'303, Mei Hing Center, Yuen Long, NT, Hong Kong','XDD LOL Company','[]',NULL,1),(3,'Bowie','bowie@gpa4.com','$2y$10$aFUXp8pRYoYkAMC3C14FTe7T46vq2xkfqjG3ohX6MxRFyLO1HqOsO',61236123,'401, Sing Kei Building, Kowloon, Hong Kong','GPA4 Company',NULL,NULL,1);
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issue`
--

DROP TABLE IF EXISTS `issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `issue` (
  `issueId` int(11) NOT NULL AUTO_INCREMENT,
  `issueType` varchar(100) DEFAULT NULL,
  `issueDetails` varchar(1000) DEFAULT NULL,
  `screenshot` varchar(255) DEFAULT NULL,
  `rname` varchar(50) DEFAULT NULL,
  `remail` varchar(255) DEFAULT NULL,
  `isCustomer` tinyint(1) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`issueId`),
  KEY `issue_fk_1` (`cid`),
  CONSTRAINT `issue_fk_1` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issue`
--

LOCK TABLES `issue` WRITE;
/*!40000 ALTER TABLE `issue` DISABLE KEYS */;
INSERT INTO `issue` VALUES (1,'Security Concern','User reported unauthorized account activity.','1.png','Chris Lee','chris@example.com',1,1,0),(2,'Payment Declined','Transaction failed after credit card submission.','2.png','Alice Wong','alice@example.com',1,2,1),(3,'Website Crash','Website crashes when uploading photo.','3.png','Bob Chan','bob@example.com',1,3,1),(4,'Inappropriate Content','Found offensive language on homepage banner.','4.png','Janice Ho','janice@example.com',0,NULL,0);
/*!40000 ALTER TABLE `issue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `mname` varchar(255) NOT NULL,
  `mqty` int(11) NOT NULL,
  `mrqty` int(11) NOT NULL,
  `munit` varchar(20) NOT NULL,
  `mreorderqty` int(11) NOT NULL,
  `mimage` varchar(255) DEFAULT NULL,
  `mavail` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material`
--

LOCK TABLES `material` WRITE;
/*!40000 ALTER TABLE `material` DISABLE KEYS */;
INSERT INTO `material` VALUES (1,'Rubber 3233',1000,0,'KG',200,'1.png',1),(2,'Cotten CDC24',300,300,'KG',400,'2.png',1),(3,'Wood RAW77',4998,3,'KG',1000,'3.png',1),(4,'ABS LL Chem 5026',2000,3,'KG',400,'4.png',1),(5,'4 x 1 Flat Head Stainless Steel Screws',49992,1820,'PC',20000,'5.png',1);
/*!40000 ALTER TABLE `material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `oid` int(11) NOT NULL AUTO_INCREMENT,
  `odate` datetime NOT NULL,
  `pid` int(11) NOT NULL,
  `oqty` int(11) NOT NULL,
  `ocost` decimal(20,2) NOT NULL,
  `cid` int(11) NOT NULL,
  `odeliverdate` datetime DEFAULT NULL,
  `ostatus` int(11) NOT NULL,
  `ocancel` int(11) DEFAULT 0,
  `proid` int(11) DEFAULT NULL,
  PRIMARY KEY (`oid`),
  KEY `pid_PK_idx` (`pid`),
  KEY `cid_pk_idx` (`cid`),
  CONSTRAINT `cid_pk` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`),
  CONSTRAINT `pid_pk` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'2025-04-12 17:50:00',1,200,3980.00,1,NULL,1,0,1),(2,'2025-07-06 17:50:00',1,90,1791.00,2,NULL,1,0,2),(3,'2025-07-06 10:33:55',2,1,9.90,2,NULL,1,0,3),(4,'2025-07-08 09:23:03',5,100,49900.00,1,NULL,1,0,4),(5,'2025-07-08 09:23:58',5,200,99800.00,1,NULL,1,0,5),(6,'2025-07-08 09:23:58',1,3,59.70,1,NULL,3,0,6),(7,'2025-07-08 09:40:29',2,2,19.80,1,NULL,4,1,7);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prodmat`
--

DROP TABLE IF EXISTS `prodmat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prodmat` (
  `pid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `pmqty` int(11) DEFAULT NULL,
  PRIMARY KEY (`pid`,`mid`),
  KEY `mid_fk_idx` (`mid`),
  CONSTRAINT `mid_fk` FOREIGN KEY (`mid`) REFERENCES `material` (`mid`),
  CONSTRAINT `pid_fk` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prodmat`
--

LOCK TABLES `prodmat` WRITE;
/*!40000 ALTER TABLE `prodmat` DISABLE KEYS */;
INSERT INTO `prodmat` VALUES (1,4,1),(1,5,6),(2,3,1),(2,5,4),(3,4,1),(3,5,12),(4,4,1),(4,5,8),(5,2,1),(5,5,6);
/*!40000 ALTER TABLE `prodmat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `pname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `pdesc` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pimage` varchar(255) DEFAULT NULL,
  `pcost` decimal(12,2) NOT NULL,
  `pqty` int(11) DEFAULT NULL,
  `pavail` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,'Cyberpunk Truck C204','Explore the world of imaginative play with our vibrant and durable toy truck. Perfect for little hands, this truck will inspire endless storytelling adventures both indoors and outdoors. Made from high-quality materials, it is built to withstand hours of creative playtime.','1.png',19.90,0,1),(2,'XDD Wooden Plane','Take to the skies with our charming wooden plane toy. Crafted from eco-friendly and child-safe materials, this beautifully designed plane sparks the imagination and encourages interactive play. With smooth edges and a sturdy construction, it\'s a delightful addition to any young aviator\'s toy collection.','2.png',9.90,0,1),(3,'iRobot 3233GG','Introduce your child to the wonders of technology and robotics with our smart robot companion. Packed with interactive features and educational benefits, this futuristic toy engages curious minds and promotes STEM learning in a fun and engaging way. Watch as your child explores coding, problem-solving, and innovation with this cutting-edge robot friend.','3.png',249.90,0,1),(4,'Apex Ball Ball Helicopter M1297','Experience the thrill of flight with our ball helicopter toy. Easy to launch and navigate, this exciting toy provides hours of entertainment for children of all ages. With colorful LED lights and a durable design, it\'s a fantastic outdoor toy that brings joy and excitement to playtime.','4.png',30.00,0,1),(5,'RoboKat AI Cat Robot','Meet our AI Cat Robot – the purr-fect blend of technology and cuddly companionship. This interactive robotic feline offers lifelike movements, sounds, and responses, providing a realistic pet experience without the hassle. With customizable features and playful interactions, this charming cat robot is a delightful addition to your child\'s playroom.','5.png',499.00,0,1);
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production`
--

DROP TABLE IF EXISTS `production`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production` (
  `proid` int(11) NOT NULL AUTO_INCREMENT,
  `pstatus` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`proid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production`
--

LOCK TABLES `production` WRITE;
/*!40000 ALTER TABLE `production` DISABLE KEYS */;
INSERT INTO `production` VALUES (1,'open'),(2,'open'),(3,'open'),(4,'open'),(5,'open'),(6,'started'),(7,'finished');
/*!40000 ALTER TABLE `production` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spasswordreset`
--

DROP TABLE IF EXISTS `spasswordreset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spasswordreset` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `token` varchar(6) NOT NULL,
  `attempt` int(11) DEFAULT 0,
  `expiry` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`rid`),
  KEY `sid` (`sid`),
  CONSTRAINT `spasswordreset_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `staff` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spasswordreset`
--

LOCK TABLES `spasswordreset` WRITE;
/*!40000 ALTER TABLE `spasswordreset` DISABLE KEYS */;
INSERT INTO `spasswordreset` VALUES (1,1,'311489',0,'2025-07-08 04:53:39',0,'2025-07-08 10:38:39','2025-07-08 10:38:55');
/*!40000 ALTER TABLE `spasswordreset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `sname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `semail` varchar(255) DEFAULT NULL,
  `spassword` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `srole` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `stel` int(11) DEFAULT NULL,
  `simage` varchar(255) DEFAULT NULL,
  `savail` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Hachi Leung','hachileung@smilesunshine.com','$2y$10$/eplKtMAU7LbVnKrdeWksezZdsOUWDXo6wumGnASv9K9W/VAqHUoW','admin',25669197,NULL,1);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-08 17:25:53
