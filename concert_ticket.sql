-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: concert_ticket
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `event_dates`
--

DROP TABLE IF EXISTS `event_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_dates` (
  `date_id` int NOT NULL AUTO_INCREMENT COMMENT '場次ID(獨立流水號)',
  `event_id` int NOT NULL COMMENT '外鍵：參照 Events(活動編號)',
  `event_date` date NOT NULL COMMENT '活動舉辦日期(內含年月日特徵)',
  PRIMARY KEY (`date_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_dates_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_dates`
--

LOCK TABLES `event_dates` WRITE;
/*!40000 ALTER TABLE `event_dates` DISABLE KEYS */;
INSERT INTO `event_dates` VALUES (101,1,'2026-10-23'),(102,1,'2026-10-24'),(103,1,'2026-10-25'),(201,2,'2026-12-30'),(202,2,'2026-12-31'),(301,3,'2026-11-05'),(302,3,'2026-11-06'),(303,3,'2026-11-07'),(401,4,'2026-11-14'),(402,4,'2026-11-15'),(501,5,'2026-09-25'),(502,5,'2026-09-26');
/*!40000 ALTER TABLE `event_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `event_id` int NOT NULL AUTO_INCREMENT COMMENT '活動編號',
  `event_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活動名稱',
  `venue_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '舉辦場地',
  `ticket_release_time` datetime NOT NULL COMMENT '搶票開始時間',
  `event_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '預售中',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Taylor Swift 2026 巡迴','台北大巨蛋','2026-07-01 12:00:00','預售中'),(2,'五月天 跨年演唱會','高雄世運主場館','2026-08-01 10:00:00','預售中'),(3,'周杰倫 嘉年華世界巡迴','台北小巨蛋','2026-08-15 12:00:00','預售中'),(4,'草東沒有派對 專場','Legacy Taipei','2026-09-01 18:00:00','預售中'),(5,'百老匯 獅子王音樂劇','國家戲劇院','2026-09-10 10:00:00','預售中');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchandises`
--

DROP TABLE IF EXISTS `merchandises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchandises` (
  `merchandise_id` int NOT NULL AUTO_INCREMENT COMMENT '商品編號',
  `event_id` int NOT NULL COMMENT '外鍵：參照 Events',
  `prod_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品名稱',
  `price` decimal(10,2) NOT NULL COMMENT '單價',
  `stock` int NOT NULL DEFAULT '0' COMMENT '庫存量',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品圖片連結',
  PRIMARY KEY (`merchandise_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `merchandises_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchandises`
--

LOCK TABLES `merchandises` WRITE;
/*!40000 ALTER TABLE `merchandises` DISABLE KEYS */;
INSERT INTO `merchandises` VALUES (1,1,'泰勒絲 紀念螢光棒',500.00,1000,'ts_stick.jpg'),(2,1,'泰勒絲 限定款 T-Shirt',1200.00,500,'ts_shirt.jpg'),(3,2,'五月天 藍色螢光棒',450.00,2000,'md_stick.jpg'),(4,2,'五月天 演唱會帽T',1500.00,300,'md_hoodie.jpg');
/*!40000 ALTER TABLE `merchandises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT COMMENT '明細編號',
  `order_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外鍵：參照 orders',
  `zone_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '【同步對齊】外鍵：參照 ticket_zones (智慧型字串)',
  `merchandise_id` int DEFAULT NULL COMMENT '外鍵：參照 merchandises',
  `item_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '明細類型(Ticket/Merchandise)',
  `quantity` int NOT NULL DEFAULT '1' COMMENT '購買數量',
  `unit_price` decimal(10,2) NOT NULL COMMENT '下單當下定價',
  `attendee_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '入場人姓名',
  `attendee_identity_no` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '入場人身分證',
  PRIMARY KEY (`item_id`),
  KEY `fk_oi_orders` (`order_no`),
  KEY `fk_oi_ticket_zones` (`zone_id`),
  KEY `fk_oi_merchandises` (`merchandise_id`),
  CONSTRAINT `fk_oi_merchandises` FOREIGN KEY (`merchandise_id`) REFERENCES `merchandises` (`merchandise_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_oi_orders` FOREIGN KEY (`order_no`) REFERENCES `orders` (`order_no`) ON DELETE CASCADE,
  CONSTRAINT `fk_oi_ticket_zones` FOREIGN KEY (`zone_id`) REFERENCES `ticket_zones` (`zone_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '訂單編號',
  `identity_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外鍵：參照 Users',
  `total_amount` decimal(10,2) NOT NULL COMMENT '總金額(門票+周邊)',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '保留中' COMMENT '保留中/已付款/已逾期',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下單時間',
  PRIMARY KEY (`order_no`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`identity_id`) REFERENCES `users` (`identity_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_zones`
--

DROP TABLE IF EXISTS `ticket_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_zones` (
  `zone_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '智慧型主鍵，格式如：ACT001_20260701_TA',
  `date_id` int NOT NULL COMMENT '外鍵：改為參照 event_dates (場次ID)',
  `zone_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '區域名稱(如特區A)',
  `price` decimal(10,2) NOT NULL COMMENT '票價(獨立場次票價)',
  `total_seats` int NOT NULL COMMENT '該區總座位數',
  `available_seats` int NOT NULL COMMENT '該區剩餘座位數(獨立扣庫存關鍵)',
  PRIMARY KEY (`zone_id`),
  KEY `fk_tz_ed_new` (`date_id`),
  CONSTRAINT `fk_tz_ed_new` FOREIGN KEY (`date_id`) REFERENCES `event_dates` (`date_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_zones`
--

LOCK TABLES `ticket_zones` WRITE;
/*!40000 ALTER TABLE `ticket_zones` DISABLE KEYS */;
INSERT INTO `ticket_zones` VALUES ('1_20261023_GA',101,'看台一般區',4800.00,2000,2000),('1_20261023_VVIP',101,'搖滾VVIP區',8800.00,500,500),('1_20261024_GA',102,'看台一般區',4800.00,2000,2000),('1_20261024_VVIP',102,'搖滾VVIP區',8800.00,500,500),('1_20261025_GA',103,'看台一般區',4800.00,2000,2000),('1_20261025_VVIP',103,'搖滾VVIP區',8800.00,500,500),('2_20261230_CAT_A',201,'瘋狂搖滾A區',4500.00,1000,1000),('2_20261230_CAT_B',201,'漫遊看台B區',2800.00,3000,3000),('2_20261231_CAT_A',202,'瘋狂搖滾A區',5500.00,1000,1000),('2_20261231_CAT_B',202,'漫遊看台B區',3200.00,3000,3000),('3_20261105_BALC',301,'二樓蛋頂看台',1800.00,1500,1500),('3_20261105_ROCK',301,'地表最強搖滾區',6800.00,800,800),('3_20261106_BALC',302,'二樓蛋頂看台',1800.00,1500,1500),('3_20261106_ROCK',302,'地表最強搖滾區',6800.00,800,800),('3_20261107_BALC',303,'二樓蛋頂看台',1800.00,1500,1500),('3_20261107_ROCK',303,'地表最強搖滾區',6800.00,800,800),('4_20261114_FLOOR',401,'全場站位搖滾區',2200.00,1000,1000),('4_20261115_FLOOR',402,'全場站位搖滾區',2200.00,1000,1000),('5_20260925_BALC',501,'三樓高空包廂區',2500.00,400,400),('5_20260925_STALLS',501,'一樓正中央座位區',5200.00,600,600),('5_20260926_BALC',502,'三樓高空包廂區',2500.00,400,400),('5_20260926_STALLS',502,'一樓正中央座位區',5200.00,600,600);
/*!40000 ALTER TABLE `ticket_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `identity_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身分證字號',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密碼加密字串',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '電子郵件',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '電話號碼',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間(年月日時分秒)',
  PRIMARY KEY (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('A111111111','$2y$10$unN/bFXHb1B3YOTF9Qjw5eBrftH/L6/2hsTJ70Cheipb6EUL8WyJm','123','1@1','0123456789','2026-06-20 14:39:26'),('A222222222','$2y$10$Lr8kGSqjEXwX3hHQ1yfuHewlSIwARUdn5fUga9V.0p5opXxHxLqNq','123','1@1','0111111111','2026-06-20 14:45:14'),('A333333333','$2y$10$zGFplsa.MH5arWq40aOqA.zjziJfS0D8cYDZWlMAms54qQNctQ7N2','333','1@2','0222222222','2026-06-20 14:47:08'),('A444444444','$2y$10$RFM8Z493lTa3Lw32BBIV/eKsh.FA4fFYfZuSrZCdSx1R5xQk4X83C','444','4@4','0222222222','2026-06-20 15:15:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-23 16:12:56
