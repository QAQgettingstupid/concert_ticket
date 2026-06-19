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
  `date_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL COMMENT '對應的活動編號',
  `event_date` date NOT NULL COMMENT '活動舉辦日期',
  PRIMARY KEY (`date_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_dates_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_dates`
--

LOCK TABLES `event_dates` WRITE;
/*!40000 ALTER TABLE `event_dates` DISABLE KEYS */;
INSERT INTO `event_dates` VALUES (1,1,'2026-09-15'),(2,1,'2026-09-16'),(3,2,'2026-12-31'),(4,2,'2027-01-01'),(5,3,'2026-10-20'),(6,3,'2026-10-21'),(7,4,'2026-11-05'),(8,4,'2026-11-06'),(9,5,'2026-12-10'),(10,5,'2026-12-11');
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
  `order_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外鍵：參照 Orders',
  `zone_id` int DEFAULT NULL COMMENT '外鍵：參照 Ticket_Zones (可為 Null)',
  `merchandise_id` int DEFAULT NULL COMMENT '外鍵：參照 Merchandises (可為 Null)',
  `item_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '明細類型(Ticket/Merchandise)',
  `quantity` int NOT NULL DEFAULT '1' COMMENT '購買數量',
  `unit_price` decimal(10,2) NOT NULL COMMENT '購買當下的單價',
  `attendee_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '實名制：入場人姓名',
  `attendee_identity_no` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '實名制：入場人身分證',
  PRIMARY KEY (`item_id`),
  KEY `order_no` (`order_no`),
  KEY `zone_id` (`zone_id`),
  KEY `merchandise_id` (`merchandise_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_no`) REFERENCES `orders` (`order_no`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `ticket_zones` (`zone_id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`merchandise_id`) REFERENCES `merchandises` (`merchandise_id`) ON DELETE SET NULL
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
  `zone_id` int NOT NULL AUTO_INCREMENT COMMENT '區域編號',
  `event_id` int NOT NULL COMMENT '外鍵：參照 Events',
  `zone_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '區域名稱(如特區A)',
  `price` decimal(10,2) NOT NULL COMMENT '票價',
  `total_seats` int NOT NULL COMMENT '該區總座位數',
  `available_seats` int NOT NULL COMMENT '該區剩餘座位數(扣庫存關鍵)',
  PRIMARY KEY (`zone_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `ticket_zones_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_zones`
--

LOCK TABLES `ticket_zones` WRITE;
/*!40000 ALTER TABLE `ticket_zones` DISABLE KEYS */;
INSERT INTO `ticket_zones` VALUES (1,1,'搖滾 A 區',6800.00,500,500),(2,1,'看台區',3800.00,1000,1000),(3,2,'特區',5500.00,800,800),(4,2,'一般區',2800.00,2000,2000),(5,3,'VIP 區',7200.00,300,300),(6,3,'熱門區',4200.00,1200,1200),(7,4,'自由入場',1500.00,600,600),(8,5,'樓下包廂',8800.00,100,100),(9,5,'樓上座位',3600.00,500,500);
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

-- Dump completed on 2026-06-19 20:08:11
