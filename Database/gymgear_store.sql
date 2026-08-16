CREATE DATABASE  IF NOT EXISTS `gymgear_store` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `gymgear_store`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: gymgear_store
-- ------------------------------------------------------
-- Server version	9.7.0

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '5b37bf6a-6608-11f1-b122-8c1759e417d5:1-386';

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'Admin@123','admin123@gmail.com','9823456780','admin','$2y$10$b1m0MQ50Bq/dJO68IFMWdu4yy3Eoq7OmetBi8/ryEjE0XEp3R52X2','2026-08-03 10:43:45');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (5,'Cardio Equipment','Machines built to raise your heart rate and build endurance — from steady-state walking to high-intensity intervals.'),(6,'Strength Equipment','Fixed and guided machines for building strength safely across every major muscle group.'),(7,'Free Weights','Dumbbells, barbells, and loose weights for classic strength training and progressive overload.'),(8,'Benches & Racks','Supportive equipment for pressing, squatting, and safely handling heavy free-weight lifts.'),(9,'Resistance Bands','Lightweight, portable bands for strength, mobility, and rehab training anywhere.'),(10,'Functional Training','Equipment for dynamic, athletic movement — building power, agility, and coordination.'),(11,'Fitness Accessories','Everyday gear that supports recovery, comfort, and consistency in training.');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'Himani gautam','himani@gmail.com','About products','I wanna buy products in retails','2026-08-11 23:58:26','1234567890');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,12,1,40000.00),(2,1,29,1,29999.99),(3,2,56,1,3000.00),(4,2,55,1,500.00),(5,3,56,1,3000.00),(6,3,51,1,2500.00),(7,4,47,2,10000.00),(8,5,47,3,10000.00),(9,6,55,1,500.00),(10,7,56,1,3000.00),(11,8,56,1,3000.00),(12,9,56,2,3000.00),(13,10,55,1,500.00),(14,10,7,1,50000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash on Delivery') DEFAULT 'Cash on Delivery',
  `payment_status` enum('Pending','Paid') DEFAULT 'Pending',
  `order_status` enum('Pending','Confirmed','Packed','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'2026-08-11 21:08:06',69999.99,'Cash on Delivery','Paid','Delivered'),(2,1,'2026-08-11 21:14:13',3500.00,'Cash on Delivery','Pending','Shipped'),(3,1,'2026-08-11 21:21:34',5500.00,'Cash on Delivery','Pending','Cancelled'),(4,1,'2026-08-12 07:02:09',20000.00,'Cash on Delivery','Paid','Delivered'),(5,1,'2026-08-12 07:03:21',30000.00,'Cash on Delivery','Pending','Pending'),(6,1,'2026-08-12 07:08:57',500.00,'Cash on Delivery','Paid','Cancelled'),(7,1,'2026-08-12 07:35:39',3000.00,'Cash on Delivery','Pending','Pending'),(8,1,'2026-08-12 07:42:31',3000.00,'Cash on Delivery','Pending','Confirmed'),(9,3,'2026-08-12 13:33:28',6000.00,'Cash on Delivery','Paid','Delivered'),(10,3,'2026-08-13 17:14:22',50500.00,'Cash on Delivery','Pending','Packed');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('Cash on Delivery') DEFAULT 'Cash on Delivery',
  `payment_status` enum('Pending','Paid') DEFAULT 'Pending',
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'Cash on Delivery','Pending',NULL),(2,2,'Cash on Delivery','Pending',NULL),(3,3,'Cash on Delivery','Pending',NULL),(4,4,'Cash on Delivery','Pending',NULL),(5,5,'Cash on Delivery','Pending',NULL),(6,6,'Cash on Delivery','Pending',NULL),(7,7,'Cash on Delivery','Pending',NULL),(8,8,'Cash on Delivery','Pending',NULL),(9,9,'Cash on Delivery','Pending',NULL),(10,10,'Cash on Delivery','Pending',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (5,3,'prod_6a7addff41f38_0.jpg'),(6,4,'prod_6a7ade54ddb2f_0.jpg'),(7,5,'prod_6a7ade7de64c4_0.jpg'),(8,6,'prod_6a7adeb23218d_0.jpg'),(9,7,'prod_6a7adee04913b_0.jpg'),(10,8,'prod_6a7adf2ee9c70_0.jpg'),(11,9,'prod_6a7adf891a4b7_0.jpg'),(12,10,'prod_6a7adfc1477f8_0.jpg'),(13,10,'prod_6a7adfc1496a7_1.jpg'),(14,11,'prod_6a7adffd0471d_0.jpg'),(15,12,'prod_6a7ae04255fe2_0.jpg'),(16,12,'prod_6a7ae0425785a_1.jpg'),(17,13,'prod_6a7ae07028fa2_0.jpg'),(18,14,'prod_6a7ae0a93757d_0.jpg'),(19,15,'prod_6a7ae0dca823c_0.jpg'),(20,16,'prod_6a7ae17ec5453_0.jpg'),(21,17,'prod_6a7ae1f45c902_0.jpg'),(22,18,'prod_6a7ae22d7e5a0_0.jpg'),(23,19,'prod_6a7ae2527ad1a_0.jpg'),(24,20,'prod_6a7ae2894b3de_0.jpg'),(25,21,'prod_6a7ae2b45a77b_0.jpg'),(26,22,'prod_6a7ae2e84f693_0.jpg'),(27,23,'prod_6a7ae30dd6b40_0.jpg'),(28,24,'prod_6a7ae32f46589_0.jpg'),(29,25,'prod_6a7ae34b12db2_0.jpg'),(30,26,'prod_6a7ae371a1337_0.jpg'),(31,27,'prod_6a7ae38e3532d_0.jpg'),(32,28,'prod_6a7ae3c59e816_0.jpg'),(33,29,'prod_6a7ae3f769eaa_0.jpg'),(34,29,'prod_6a7ae3f76c2f6_1.jpg'),(35,30,'prod_6a7ae42401e3c_0.jpg'),(36,31,'prod_6a7ae450cefe0_0.jpg'),(37,32,'prod_6a7ae474107b3_0.jpg'),(38,33,'prod_6a7ae49d819a8_0.jpg'),(39,34,'prod_6a7ae4e846d06_0.jpg'),(40,35,'prod_6a7ae51ebdeab_0.jpg'),(41,36,'prod_6a7ae551865dd_0.jpg'),(42,37,'prod_6a7ae592cb8ca_0.jpg'),(43,38,'prod_6a7ae7326f613_0.jpg'),(44,39,'prod_6a7ae762b0696_0.jpg'),(45,40,'prod_6a7ae789ca6ec_0.jpg'),(46,41,'prod_6a7ae7c3bf5a1_0.jpg'),(47,42,'prod_6a7ae7e64d8f9_0.jpg'),(48,43,'prod_6a7ae81d02f48_0.jpg'),(49,44,'prod_6a7ae849828a0_0.jpg'),(50,45,'prod_6a7ae8768e72b_0.jpg'),(51,46,'prod_6a7ae8b4cd76d_0.jpg'),(52,47,'prod_6a7ae8ec95e92_0.jpg'),(53,48,'prod_6a7ae90b3e8f9_0.jpg'),(54,49,'prod_6a7ae935d57e1_0.jpg'),(55,50,'prod_6a7ae96bd51d1_0.jpg'),(56,51,'prod_6a7ae9bf2cc69_0.jpg'),(57,52,'prod_6a7ae9f283abe_0.jpg'),(58,53,'prod_6a7aea18cf176_0.jpg'),(59,54,'prod_6a7aea40db20a_0.jpg'),(60,55,'prod_6a7aea64b6a1f_0.jpg'),(61,56,'prod_6a7aea89b88e6_0.jpg');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `status` enum('Available','Out of Stock') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (3,5,'Treadmill','Motorized running belt with adjustable speed and incline for walking, jogging, or sprint training indoors.',40000.00,27,'Available','2026-08-11 08:31:59'),(4,5,'Walking Pad','Compact, low-profile treadmill designed for light walking during work or everyday low-impact movement.',35000.00,30,'Available','2026-08-11 08:33:24'),(5,5,'Air Bike','Fan-resistance stationary bike that scales resistance with effort — built for intense interval workouts.',40000.00,0,'Out of Stock','2026-08-11 08:34:05'),(6,5,'Spin Bike','Weighted flywheel bike modeled after studio cycling classes, with adjustable resistance and seat position.',40000.00,30,'Available','2026-08-11 08:34:58'),(7,5,'Elliptical Trainer','Low-impact, full-body cardio machine that combines upper and lower body movement in one smooth motion.',50000.00,9,'Available','2026-08-11 08:35:44'),(8,5,'Stair Climber','Vertical step machine that mimics climbing stairs to build leg strength and cardiovascular endurance.',6000.00,5,'Available','2026-08-11 08:37:02'),(9,6,'Smith Machine','Barbell fixed within a vertical track for guided, safer squats, presses, and lifts without a spotter',40000.00,0,'Out of Stock','2026-08-11 08:38:33'),(10,6,'Chest Press Machine','Seated machine that isolates the chest and triceps through a controlled pressing motion.',20000.00,7,'Available','2026-08-11 08:39:29'),(11,6,'Shoulder Press','Seated press machine built to strengthen the shoulders and upper arms with controlled resistance.',30000.00,10,'Available','2026-08-11 08:40:29'),(12,6,'Leg Press','Angled sled machine for pushing heavy loads with the legs, targeting quads, hamstrings, and glutes.',40000.00,9,'Available','2026-08-11 08:41:38'),(13,6,'Lat Pulldown','Cable station for building back width and strength through a top-down pulling motion.',30000.00,8,'Available','2026-08-11 08:42:24'),(14,6,'Cable Crossover','Dual-adjustable pulley station enabling a wide range of pressing, pulling, and isolation exercises.',30000.00,7,'Available','2026-08-11 08:43:21'),(15,6,'Seated Row','Cable or plate-loaded machine for building mid-back thickness through a horizontal pulling motion.',40000.00,10,'Available','2026-08-11 08:44:12'),(16,6,'Multi-Gym Equipments','All-in-one strength station combining several exercise stations for a full-body workout in one unit.',29999.99,9,'Available','2026-08-11 08:46:54'),(17,7,'Dumbbell 1kg','Fixed 1kg dumbbell, ideal for light toning, rehab work, or beginner training.',500.00,20,'Available','2026-08-11 08:48:52'),(18,7,'Dumbbell 2.5kg','Fixed 2.5kg dumbbell for light resistance training and warm-up sets.',800.00,0,'Out of Stock','2026-08-11 08:49:49'),(19,7,'Dumbbell 5kg','Fixed 5kg dumbbell suited for moderate strength and endurance training.',1000.00,30,'Available','2026-08-11 08:50:26'),(20,8,'Dumbbell 7.5kg','Fixed 7.5kg dumbbell for progressive strength training between standard weight jumps.',30000.00,21,'Available','2026-08-11 08:51:21'),(21,7,'Dumbbell 10kg','Fixed 10kg dumbbell for general strength training across most muscle groups.',30000.00,20,'Available','2026-08-11 08:52:04'),(22,7,'Dumbbell 12.5kg','Fixed 12.5kg dumbbell for intermediate-level strength work.',30000.00,20,'Available','2026-08-11 08:52:56'),(23,7,'Dumbbell 15kg','Fixed 15kg dumbbell for building strength in compound and isolation lifts.',30000.00,20,'Available','2026-08-11 08:53:33'),(24,7,'Dumbbell 20kg','Fixed 20kg dumbbell for advanced strength training.',30000.00,20,'Available','2026-08-11 08:54:07'),(25,7,'Dumbbell 25kg','Fixed 25kg dumbbell for heavy strength and hypertrophy training.',30000.00,20,'Available','2026-08-11 08:54:35'),(26,7,'Dumbbell 30kg','Fixed 30kg dumbbell for advanced lifters targeting heavy compound movements.',30000.00,20,'Available','2026-08-11 08:55:13'),(27,7,'Dumbbell 35kg','Fixed 35kg dumbbell for experienced lifters training at high intensity.',30000.00,20,'Available','2026-08-11 08:55:42'),(28,7,'Adjustable Dumbbells','Single dumbbell set with swappable plates, replacing an entire rack in one compact unit.',20000.00,10,'Available','2026-08-11 08:56:37'),(29,7,'Hex Dumbbells','Hexagonal-head dumbbell that won\'t roll away, with a durable rubber-coated grip.',29999.99,29,'Available','2026-08-11 08:57:27'),(30,7,'KettleBell','Cast-iron weight with a looped handle, built for swings, presses, and dynamic full-body movements.',35000.00,20,'Available','2026-08-11 08:58:12'),(31,7,'Olympic Barbell','Standard 20kg barbell with rotating sleeves, compatible with Olympic-size weight plates.',20000.00,20,'Available','2026-08-11 08:58:56'),(32,7,'EZ Curl Bar','Curved barbell designed to reduce wrist strain during curls and other arm exercises.',30000.00,30,'Available','2026-08-11 08:59:32'),(33,7,'Weight Plates','Standard iron or rubber-coated plates used to load barbells and weight machines.',50000.00,20,'Available','2026-08-11 09:00:13'),(34,9,'Resistance Bands','Elastic bands in varying resistance levels for strength training, stretching, and rehab exercises.',1500.00,20,'Available','2026-08-11 09:01:28'),(35,9,'Pull-UP Resistance Band','Heavy-duty looped band used to assist pull-ups by reducing bodyweight load.',2000.00,20,'Available','2026-08-11 09:02:22'),(36,9,'Fabric Hip Band','Non-slip fabric band worn around the thighs to activate and strengthen the glutes and hips.',3000.00,30,'Available','2026-08-11 09:03:13'),(37,8,'Adjustable Bench','Multi-angle bench that adjusts from flat to incline and decline for varied pressing exercises.',30000.00,16,'Available','2026-08-11 09:04:18'),(38,8,'Flat Bench','Sturdy flat bench for standard pressing, rows, and general strength exercises.',10000.00,10,'Available','2026-08-11 09:11:14'),(39,8,'Bench Press Rack','Rack paired with a bench for safe barbell bench pressing with spotter arms.',15000.00,25,'Available','2026-08-11 09:12:02'),(40,8,'Squat Rack','Freestanding rack with adjustable safety bars for squats and other barbell lifts.',25000.00,10,'Available','2026-08-11 09:12:41'),(41,8,'Power Rack','Fully enclosed rack offering maximum safety for heavy squats, presses, and pulls.',20000.00,12,'Available','2026-08-11 09:13:39'),(42,8,'Dumbbell Rack','Tiered storage rack for organizing and safely storing multiple pairs of dumbbells.',30000.00,7,'Available','2026-08-11 09:14:14'),(43,10,'Battle Rope','Heavy training rope used for high-intensity waves and slams that build power and conditioning.',5000.00,10,'Available','2026-08-11 09:15:09'),(44,10,'Agility Ladder','Flat ground ladder used for footwork drills that improve speed and coordination.',5500.00,20,'Available','2026-08-11 09:15:53'),(45,10,'Polymetric Box','Sturdy box used for jump training to build explosive lower-body power',5000.00,12,'Available','2026-08-11 09:16:38'),(46,10,'Speed Hurdles','Adjustable low hurdles used for agility and speed training drills.',3000.00,14,'Available','2026-08-11 09:17:40'),(47,10,'Suspension Trainer','Strap-based system that uses bodyweight for strength, balance, and core training.',10000.00,0,'Out of Stock','2026-08-11 09:18:36'),(48,10,'Medicine Ball','Weighted ball used for slams, throws, and rotational core exercises.',3000.00,30,'Available','2026-08-11 09:19:07'),(49,10,'Ab Wheel Roller','Small wheel with handles used to build core and shoulder stability through rolling motion.',1000.00,10,'Available','2026-08-11 09:19:49'),(50,10,'Jump Rope','Classic cardio and coordination tool for fast-paced conditioning workouts.',200.00,20,'Available','2026-08-11 09:20:43'),(51,11,'Foam Roller','Cylindrical foam tool used for self-myofascial release and post-workout muscle recovery.',2500.00,9,'Available','2026-08-11 09:22:07'),(52,11,'Weightlifting Belt','Supportive belt worn during heavy lifts to stabilize the core and lower back.',15000.00,30,'Available','2026-08-11 09:22:58'),(53,11,'Wrist Strap','Support strap that reinforces grip and wrist stability during heavy pulling exercises.',1000.00,30,'Available','2026-08-11 09:23:36'),(54,7,'Shaker Bottle','Leak-proof bottle with a mixing ball for blending protein shakes and supplements on the go.',2000.00,20,'Available','2026-08-11 09:24:16'),(55,7,'Water Bottles','Durable bottles for staying hydrated through every training session.',500.00,7,'Available','2026-08-11 09:24:52'),(56,11,'Microfibre Gym Towel','Quick-drying, lightweight towel designed for wiping down during and after workouts.',3000.00,14,'Available','2026-08-11 09:25:29');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `product_id` (`product_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,56,3,5,'Perfect towel for my workout','2026-08-14 10:32:22'),(2,55,3,3,'Over-Priced','2026-08-14 10:33:05'),(3,56,1,5,'Nice one','2026-08-14 10:35:11');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Alina Gautam','alinagautam22222@gmail.com','9826574874','Kalanki','alina23','$2y$10$Cd6/uLH4avdElPWhA/KIP.kGNiGkF6SROmyewC5z62VT0YlpfwKW.','2026-08-03 11:29:31'),(2,'Prabisha neupane','prabisha123@gmail.com','1234567890','kupondole','Prabi','$2y$10$T3H3Y/WE74ATVX267uTrcOfoucXagk.ZVrMtTylqn6vkgopP1pL.O','2026-08-06 11:42:47'),(3,'Prabi','Prabishaneupane2@gmail.com','1234567890','kupondole','prabisha123','$2y$10$JQalv.YmsktnIDVMNUJ4FOVOGG4WOy5SSaLWJwP.NXge55y4X79om','2026-08-12 07:47:39');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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

-- Dump completed on 2026-08-14 16:23:34
