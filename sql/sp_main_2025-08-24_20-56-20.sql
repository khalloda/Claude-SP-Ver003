/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.4-MariaDB, for Win64 (AMD64)
--
-- Host: p3nlmysql13plsk.secureserver.net    Database: sp_main
-- ------------------------------------------------------
-- Server version	8.0.37-29

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `sp_clients`
--

DROP TABLE IF EXISTS `sp_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('company','individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_clients`
--

LOCK TABLES `sp_clients` WRITE;
/*!40000 ALTER TABLE `sp_clients` DISABLE KEYS */;
INSERT INTO `sp_clients` VALUES
(1,'company','ABC Auto Parts Ltd','+1-555-0001','contact@abcauto.com','123 Main Street, City, State 12345','2025-08-20 07:19:08'),
(2,'company','XYZ Motors Inc','+1-555-0002','info@xyzmotors.com','456 Oak Avenue, City, State 67890','2025-08-20 07:19:08'),
(3,'individual','John Smith','+1-555-0003','john.smith@email.com','789 Pine Road, City, State 11111','2025-08-20 07:19:08'),
(4,'individual','Khaled Mohamed Helmy','01007847333','khaled.h87@gmail.com','KM 28 Cairo Alex Desert Road B 19 - Smart Village\r\nSarieldin & Partners','2025-08-21 15:32:34');
/*!40000 ALTER TABLE `sp_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_currencies`
--

DROP TABLE IF EXISTS `sp_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_currencies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `decimal_places` tinyint NOT NULL DEFAULT '2',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_primary` (`is_primary`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_currencies`
--

LOCK TABLES `sp_currencies` WRITE;
/*!40000 ALTER TABLE `sp_currencies` DISABLE KEYS */;
INSERT INTO `sp_currencies` VALUES
(1,'EGP','Egyptian Pound','ج.م',1,1,1.000000,2,'2025-08-23 12:29:36','2025-08-23 12:29:36'),
(2,'USD','US Dollar','$',0,1,0.032258,2,'2025-08-23 12:29:36','2025-08-23 12:29:36'),
(3,'EUR','Euro','€',0,1,0.029032,2,'2025-08-23 13:42:45','2025-08-23 13:42:45'),
(4,'GBP','British Pound','£',0,1,0.025806,2,'2025-08-23 13:42:45','2025-08-23 13:42:45'),
(5,'SAR','Saudi Riyal','ر.س',0,1,0.121029,2,'2025-08-23 13:42:45','2025-08-23 13:42:45'),
(6,'AED','UAE Dirham','د.إ',0,1,0.118548,2,'2025-08-23 13:42:45','2025-08-23 13:42:45');
/*!40000 ALTER TABLE `sp_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_currency_history`
--

DROP TABLE IF EXISTS `sp_currency_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_currency_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_rate` decimal(15,6) DEFAULT NULL,
  `new_rate` decimal(15,6) NOT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_currency_history_currency` (`currency_code`),
  KEY `fk_currency_history_user` (`updated_by`),
  KEY `idx_currency_date` (`currency_code`,`created_at`),
  CONSTRAINT `fk_currency_history_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_currency_history_user` FOREIGN KEY (`updated_by`) REFERENCES `sp_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_currency_history`
--

LOCK TABLES `sp_currency_history` WRITE;
/*!40000 ALTER TABLE `sp_currency_history` DISABLE KEYS */;
INSERT INTO `sp_currency_history` VALUES
(1,'EGP',NULL,1.000000,1,'2025-08-23 13:42:45'),
(2,'USD',NULL,0.032258,1,'2025-08-23 13:42:45'),
(3,'EUR',NULL,0.029032,1,'2025-08-23 13:42:45'),
(4,'GBP',NULL,0.025806,1,'2025-08-23 13:42:45'),
(5,'SAR',NULL,0.121029,1,'2025-08-23 13:42:45'),
(6,'AED',NULL,0.118548,1,'2025-08-23 13:42:45');
/*!40000 ALTER TABLE `sp_currency_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_dropdowns`
--

DROP TABLE IF EXISTS `sp_dropdowns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_dropdowns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` enum('color','brand','car_make','car_model','classification') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_category_parent` (`category`,`parent_id`),
  CONSTRAINT `fk_dropdowns_parent` FOREIGN KEY (`parent_id`) REFERENCES `sp_dropdowns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_dropdowns`
--

LOCK TABLES `sp_dropdowns` WRITE;
/*!40000 ALTER TABLE `sp_dropdowns` DISABLE KEYS */;
INSERT INTO `sp_dropdowns` VALUES
(1,'classification','Engine Parts',NULL,'2025-08-20 07:19:08'),
(2,'classification','Body Parts',NULL,'2025-08-20 07:19:08'),
(3,'classification','Electrical',NULL,'2025-08-20 07:19:08'),
(4,'classification','Filters',NULL,'2025-08-20 07:19:08'),
(10,'color','Black',NULL,'2025-08-20 07:19:08'),
(11,'color','White',NULL,'2025-08-20 07:19:08'),
(12,'color','Red',NULL,'2025-08-20 07:19:08'),
(13,'color','Blue',NULL,'2025-08-20 07:19:08'),
(14,'color','Silver',NULL,'2025-08-20 07:19:08'),
(20,'brand','OEM',NULL,'2025-08-20 07:19:08'),
(21,'brand','Aftermarket',NULL,'2025-08-20 07:19:08'),
(22,'brand','Bosch',NULL,'2025-08-20 07:19:08'),
(23,'brand','NGK',NULL,'2025-08-20 07:19:08'),
(24,'brand','Denso',NULL,'2025-08-20 07:19:08'),
(30,'car_make','Toyota',NULL,'2025-08-20 07:19:08'),
(31,'car_make','Honda',NULL,'2025-08-20 07:19:08'),
(32,'car_make','Ford',NULL,'2025-08-20 07:19:08'),
(33,'car_make','BMW',NULL,'2025-08-20 07:19:08'),
(34,'car_make','Mercedes',NULL,'2025-08-20 07:19:08'),
(40,'car_model','Camry',30,'2025-08-20 07:19:08'),
(41,'car_model','Corolla',30,'2025-08-20 07:19:08'),
(42,'car_model','Prius',30,'2025-08-20 07:19:08'),
(43,'car_model','RAV4',30,'2025-08-20 07:19:08'),
(50,'car_model','Civic',31,'2025-08-20 07:19:08'),
(51,'car_model','Accord',31,'2025-08-20 07:19:08'),
(52,'car_model','CR-V',31,'2025-08-20 07:19:08'),
(60,'car_model','Focus',32,'2025-08-20 07:19:08'),
(61,'car_model','Mustang',32,'2025-08-20 07:19:08'),
(62,'car_model','F-150',32,'2025-08-20 07:19:08'),
(70,'car_model','3 Series',33,'2025-08-20 07:19:08'),
(71,'car_model','5 Series',33,'2025-08-20 07:19:08'),
(72,'car_model','X3',33,'2025-08-20 07:19:08'),
(80,'car_model','C-Class',34,'2025-08-20 07:19:08'),
(81,'car_model','E-Class',34,'2025-08-20 07:19:08'),
(82,'car_model','GLC',34,'2025-08-20 07:19:08'),
(83,'car_make','Fiat',NULL,'2025-08-21 17:13:33'),
(84,'car_model','Punto 2018',83,'2025-08-21 18:48:22');
/*!40000 ALTER TABLE `sp_dropdowns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_exchange_rate_history`
--

DROP TABLE IF EXISTS `sp_exchange_rate_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_exchange_rate_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(10,6) NOT NULL,
  `previous_rate` decimal(10,6) DEFAULT NULL,
  `changed_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `currency_code` (`currency_code`),
  KEY `created_at` (`created_at`),
  KEY `fk_rate_history_user` (`changed_by`),
  CONSTRAINT `fk_rate_history_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON DELETE CASCADE,
  CONSTRAINT `fk_rate_history_user` FOREIGN KEY (`changed_by`) REFERENCES `sp_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_exchange_rate_history`
--

LOCK TABLES `sp_exchange_rate_history` WRITE;
/*!40000 ALTER TABLE `sp_exchange_rate_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `sp_exchange_rate_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_invoice_items`
--

DROP TABLE IF EXISTS `sp_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `product_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_invoice_items_invoice` (`invoice_id`),
  KEY `fk_invoice_items_product` (`product_id`),
  KEY `fk_invoice_items_currency` (`currency_code`),
  CONSTRAINT `fk_invoice_items_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `sp_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_invoice_items`
--

LOCK TABLES `sp_invoice_items` WRITE;
/*!40000 ALTER TABLE `sp_invoice_items` DISABLE KEYS */;
INSERT INTO `sp_invoice_items` VALUES
(1,1,'EGP',7,2.00,100.00,NULL,1.000000,10.00,'percent',0.00,'percent'),
(2,1,'EGP',6,1.00,75.00,NULL,1.000000,10.00,'percent',0.00,'percent');
/*!40000 ALTER TABLE `sp_invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_invoices`
--

DROP TABLE IF EXISTS `sp_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `sales_order_id` int DEFAULT NULL,
  `status` enum('open','partial','paid','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `global_tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT '0.00',
  `global_discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT '0.00',
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_invoices_client` (`client_id`),
  KEY `fk_invoices_sales_order` (`sales_order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_currency_code` (`currency_code`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_invoices_sales_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sp_sales_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_invoices`
--

LOCK TABLES `sp_invoices` WRITE;
/*!40000 ALTER TABLE `sp_invoices` DISABLE KEYS */;
INSERT INTO `sp_invoices` VALUES
(1,4,'EGP',1.000000,1,'paid',275.00,27.50,0.00,'percent',0.00,'percent',0.00,27.50,0.00,302.50,302.50,'','2025-08-22 16:44:46');
/*!40000 ALTER TABLE `sp_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_migrations`
--

DROP TABLE IF EXISTS `sp_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_migrations`
--

LOCK TABLES `sp_migrations` WRITE;
/*!40000 ALTER TABLE `sp_migrations` DISABLE KEYS */;
INSERT INTO `sp_migrations` VALUES
(1,'phase_1','2025-08-20 07:20:34'),
(2,'multicurrency_support','2025-08-23 12:29:37');
/*!40000 ALTER TABLE `sp_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_payments`
--

DROP TABLE IF EXISTS `sp_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `client_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `amount` decimal(12,2) NOT NULL,
  `amount_in_base` decimal(12,2) DEFAULT NULL,
  `method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payments_invoice` (`invoice_id`),
  KEY `fk_payments_client` (`client_id`),
  KEY `idx_method` (`method`),
  KEY `idx_currency_code` (`currency_code`),
  CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `sp_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_payments`
--

LOCK TABLES `sp_payments` WRITE;
/*!40000 ALTER TABLE `sp_payments` DISABLE KEYS */;
INSERT INTO `sp_payments` VALUES
(1,1,4,'EGP',1.000000,150.00,150.00,'cash','Downpayment','2025-08-22 09:45:39'),
(2,1,4,'EGP',1.000000,152.50,152.50,'bank_transfer','Final Payment','2025-08-22 09:47:25');
/*!40000 ALTER TABLE `sp_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_permissions`
--

DROP TABLE IF EXISTS `sp_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_permissions`
--

LOCK TABLES `sp_permissions` WRITE;
/*!40000 ALTER TABLE `sp_permissions` DISABLE KEYS */;
INSERT INTO `sp_permissions` VALUES
(1,'manage_users','Manage users and permissions','2025-08-20 07:19:08'),
(2,'manage_clients','Manage clients','2025-08-20 07:19:08'),
(3,'manage_suppliers','Manage suppliers','2025-08-20 07:19:08'),
(4,'manage_products','Manage products','2025-08-20 07:19:08'),
(5,'manage_quotes','Manage quotes','2025-08-20 07:19:08'),
(6,'manage_orders','Manage sales orders','2025-08-20 07:19:08'),
(7,'manage_invoices','Manage invoices','2025-08-20 07:19:08'),
(8,'manage_payments','Manage payments','2025-08-20 07:19:08'),
(9,'view_reports','View reports','2025-08-20 07:19:08'),
(10,'manage_warehouses','Manage warehouses','2025-08-20 07:19:08');
/*!40000 ALTER TABLE `sp_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_product_locations`
--

DROP TABLE IF EXISTS `sp_product_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_product_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `location_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_warehouse` (`product_id`,`warehouse_id`),
  KEY `fk_product_locations_warehouse` (`warehouse_id`),
  CONSTRAINT `fk_product_locations_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_locations_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `sp_warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_product_locations`
--

LOCK TABLES `sp_product_locations` WRITE;
/*!40000 ALTER TABLE `sp_product_locations` DISABLE KEYS */;
INSERT INTO `sp_product_locations` VALUES
(1,1,1,'A1-01',30.00),
(2,1,2,'B2-05',20.00),
(3,2,1,'A1-02',15.00),
(4,2,3,'C3-01',10.00),
(5,3,1,'A2-10',3.00),
(6,3,2,'B1-15',2.00),
(7,4,1,'A1-05',10.00),
(8,4,3,'C2-08',5.00),
(9,5,1,'A1-03',20.00),
(10,5,2,'B2-03',10.00),
(11,6,2,'B2-05',15.00),
(12,6,4,'B2-05',5.00),
(13,7,4,'B2-10',20.00);
/*!40000 ALTER TABLE `sp_product_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_products`
--

DROP TABLE IF EXISTS `sp_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `classification` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_price` decimal(12,2) DEFAULT '0.00',
  `sale_price` decimal(12,2) DEFAULT '0.00',
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `color` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `car_make` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `car_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reserved_quotes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reserved_orders` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_classification` (`classification`),
  KEY `idx_name` (`name`),
  KEY `idx_brand` (`brand`),
  KEY `idx_car_make_model` (`car_make`,`car_model`),
  KEY `fk_products_currency` (`currency_code`),
  CONSTRAINT `fk_products_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_products`
--

LOCK TABLES `sp_products` WRITE;
/*!40000 ALTER TABLE `sp_products` DISABLE KEYS */;
INSERT INTO `sp_products` VALUES
(1,'Engine Parts','ENG0001','Oil Filter Standard',8.50,12.99,'EGP','Black','Bosch','Toyota','Camry',50.00,0.00,0.00,'2025-08-20 07:19:08'),
(2,'Engine Parts','ENG0002','Air Filter Element',15.00,22.50,'EGP','White','OEM','Honda','Civic',25.00,0.00,0.00,'2025-08-20 07:19:08'),
(3,'Body Parts','BDY0001','Front Bumper Cover',125.00,189.99,'EGP','Black','Aftermarket','Ford','Focus',5.00,0.00,0.00,'2025-08-20 07:19:08'),
(4,'Electrical','ELE0001','Spark Plug Set',28.00,42.99,'EGP','Silver','NGK','BMW','3 Series',15.00,0.00,0.00,'2025-08-20 07:19:08'),
(5,'Filters','FIL0001','Fuel Filter',12.00,18.75,'EGP','Black','Denso','Mercedes','C-Class',30.00,0.00,0.00,'2025-08-20 07:19:08'),
(6,'Body Parts','BOD0001','Spoiler',50.00,75.00,'EGP','Blue','Bosch','Fiat','Punto 2018',20.00,0.00,1.00,'2025-08-21 15:39:49'),
(7,'Filters','FIL0002','Air Filter',10.00,100.00,'EGP','White','Bosch','Fiat','Punto 2018',20.00,0.00,2.00,'2025-08-22 14:26:18');
/*!40000 ALTER TABLE `sp_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_quote_items`
--

DROP TABLE IF EXISTS `sp_quote_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_quote_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quote_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `product_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_quote_items_quote` (`quote_id`),
  KEY `fk_quote_items_product` (`product_id`),
  KEY `fk_quote_items_currency` (`currency_code`),
  CONSTRAINT `fk_quote_items_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_quote_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quote_items_quote` FOREIGN KEY (`quote_id`) REFERENCES `sp_quotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_quote_items`
--

LOCK TABLES `sp_quote_items` WRITE;
/*!40000 ALTER TABLE `sp_quote_items` DISABLE KEYS */;
INSERT INTO `sp_quote_items` VALUES
(1,1,'EGP',7,2.00,100.00,NULL,1.000000,10.00,'percent',0.00,'percent'),
(2,1,'EGP',6,1.00,75.00,NULL,1.000000,10.00,'percent',0.00,'percent');
/*!40000 ALTER TABLE `sp_quote_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_quotes`
--

DROP TABLE IF EXISTS `sp_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `status` enum('sent','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `global_tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT '0.00',
  `global_discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT '0.00',
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_quotes_client` (`client_id`),
  KEY `idx_status` (`status`),
  KEY `idx_currency_code` (`currency_code`),
  CONSTRAINT `fk_quotes_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotes_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_quotes`
--

LOCK TABLES `sp_quotes` WRITE;
/*!40000 ALTER TABLE `sp_quotes` DISABLE KEYS */;
INSERT INTO `sp_quotes` VALUES
(1,4,'EGP',1.000000,'approved',275.00,27.50,0.00,'percent',0.00,'percent',0.00,27.50,0.00,302.50,'','2025-08-22 16:42:48');
/*!40000 ALTER TABLE `sp_quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_role_permissions`
--

DROP TABLE IF EXISTS `sp_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `sp_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `sp_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_role_permissions`
--

LOCK TABLES `sp_role_permissions` WRITE;
/*!40000 ALTER TABLE `sp_role_permissions` DISABLE KEYS */;
INSERT INTO `sp_role_permissions` VALUES
(1,1,'2025-08-20 07:19:08'),
(1,2,'2025-08-20 07:19:08'),
(1,3,'2025-08-20 07:19:08'),
(1,4,'2025-08-20 07:19:08'),
(1,5,'2025-08-20 07:19:08'),
(1,6,'2025-08-20 07:19:08'),
(1,7,'2025-08-20 07:19:08'),
(1,8,'2025-08-20 07:19:08'),
(1,9,'2025-08-20 07:19:08'),
(1,10,'2025-08-20 07:19:08');
/*!40000 ALTER TABLE `sp_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_roles`
--

DROP TABLE IF EXISTS `sp_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_roles`
--

LOCK TABLES `sp_roles` WRITE;
/*!40000 ALTER TABLE `sp_roles` DISABLE KEYS */;
INSERT INTO `sp_roles` VALUES
(1,'Admin','Full system access','2025-08-20 07:19:08'),
(2,'Manager','Management access','2025-08-20 07:19:08'),
(3,'User','Basic user access','2025-08-20 07:19:08');
/*!40000 ALTER TABLE `sp_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_sales_order_items`
--

DROP TABLE IF EXISTS `sp_sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_sales_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sales_order_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `product_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  PRIMARY KEY (`id`),
  KEY `fk_sales_order_items_so` (`sales_order_id`),
  KEY `fk_sales_order_items_product` (`product_id`),
  KEY `fk_sales_order_items_currency` (`currency_code`),
  CONSTRAINT `fk_sales_order_items_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_order_items_so` FOREIGN KEY (`sales_order_id`) REFERENCES `sp_sales_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_sales_order_items`
--

LOCK TABLES `sp_sales_order_items` WRITE;
/*!40000 ALTER TABLE `sp_sales_order_items` DISABLE KEYS */;
INSERT INTO `sp_sales_order_items` VALUES
(1,1,'EGP',7,2.00,100.00,NULL,1.000000,10.00,'percent',0.00,'percent'),
(2,1,'EGP',6,1.00,75.00,NULL,1.000000,10.00,'percent',0.00,'percent');
/*!40000 ALTER TABLE `sp_sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_sales_orders`
--

DROP TABLE IF EXISTS `sp_sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_sales_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EGP',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000',
  `quote_id` int DEFAULT NULL,
  `status` enum('open','delivered','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `items_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `items_discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `global_tax_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_tax_value` decimal(10,2) DEFAULT '0.00',
  `global_discount_type` enum('percent','amount') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `global_discount_value` decimal(10,2) DEFAULT '0.00',
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sales_orders_client` (`client_id`),
  KEY `fk_sales_orders_quote` (`quote_id`),
  KEY `idx_status` (`status`),
  KEY `idx_currency_code` (`currency_code`),
  CONSTRAINT `fk_sales_orders_client` FOREIGN KEY (`client_id`) REFERENCES `sp_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sales_orders_currency` FOREIGN KEY (`currency_code`) REFERENCES `sp_currencies` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_orders_quote` FOREIGN KEY (`quote_id`) REFERENCES `sp_quotes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_sales_orders`
--

LOCK TABLES `sp_sales_orders` WRITE;
/*!40000 ALTER TABLE `sp_sales_orders` DISABLE KEYS */;
INSERT INTO `sp_sales_orders` VALUES
(1,4,'EGP',1.000000,1,'delivered',275.00,27.50,0.00,'percent',0.00,'percent',0.00,27.50,0.00,302.50,'','2025-08-22 16:44:23');
/*!40000 ALTER TABLE `sp_sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_stock_movements`
--

DROP TABLE IF EXISTS `sp_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_stock_movements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `direction` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_table` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_movements_product` (`product_id`),
  KEY `idx_direction` (`direction`),
  KEY `idx_ref` (`ref_table`,`ref_id`),
  CONSTRAINT `fk_stock_movements_product` FOREIGN KEY (`product_id`) REFERENCES `sp_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_stock_movements`
--

LOCK TABLES `sp_stock_movements` WRITE;
/*!40000 ALTER TABLE `sp_stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `sp_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_suppliers`
--

DROP TABLE IF EXISTS `sp_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('company','individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_suppliers`
--

LOCK TABLES `sp_suppliers` WRITE;
/*!40000 ALTER TABLE `sp_suppliers` DISABLE KEYS */;
INSERT INTO `sp_suppliers` VALUES
(1,'company','Global Parts Supplier','+1-555-1001','orders@globalparts.com','100 Industrial Blvd, City, State 22222','2025-08-20 07:19:08'),
(2,'company','Auto Components Co','+1-555-1002','sales@autocomponents.com','200 Commerce Street, City, State 33333','2025-08-20 07:19:08'),
(3,'individual','Mike Johnson','+1-555-1003','mike.j@email.com','300 Business Ave, City, State 44444','2025-08-20 07:19:08'),
(5,'individual','Ahmed Abdel Salam','01120121343','ahmedfuture445@gmail.com','Nozha','2025-08-21 19:33:15');
/*!40000 ALTER TABLE `sp_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_user_currency_preferences`
--

DROP TABLE IF EXISTS `sp_user_currency_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_user_currency_preferences` (
  `user_id` int NOT NULL,
  `preferred_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `fk_user_currency_pref_currency` (`preferred_currency`),
  CONSTRAINT `fk_user_currency_pref_currency` FOREIGN KEY (`preferred_currency`) REFERENCES `sp_currencies` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_currency_pref_user` FOREIGN KEY (`user_id`) REFERENCES `sp_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_user_currency_preferences`
--

LOCK TABLES `sp_user_currency_preferences` WRITE;
/*!40000 ALTER TABLE `sp_user_currency_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `sp_user_currency_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_user_roles`
--

DROP TABLE IF EXISTS `sp_user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_roles_role` (`role_id`),
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `sp_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `sp_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_user_roles`
--

LOCK TABLES `sp_user_roles` WRITE;
/*!40000 ALTER TABLE `sp_user_roles` DISABLE KEYS */;
INSERT INTO `sp_user_roles` VALUES
(1,1,'2025-08-20 07:19:08');
/*!40000 ALTER TABLE `sp_user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_users`
--

DROP TABLE IF EXISTS `sp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` enum('en','ar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_users`
--

LOCK TABLES `sp_users` WRITE;
/*!40000 ALTER TABLE `sp_users` DISABLE KEYS */;
INSERT INTO `sp_users` VALUES
(1,'System Administrator','admin@example.com','$2y$10$FTGNszqnW3x6zZPnMx/yD.l7x1pHXpHdA4mtQ/5c4gQKi9GD9bUI2','en','2025-08-20 07:19:07');
/*!40000 ALTER TABLE `sp_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sp_warehouses`
--

DROP TABLE IF EXISTS `sp_warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sp_warehouses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `capacity` decimal(10,2) DEFAULT NULL,
  `responsible_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sp_warehouses`
--

LOCK TABLES `sp_warehouses` WRITE;
/*!40000 ALTER TABLE `sp_warehouses` DISABLE KEYS */;
INSERT INTO `sp_warehouses` VALUES
(1,'Main Warehouse','500 Storage Drive, City, State 55555',10000.00,'Alice Johnson','alice@spareparts.com','+1-555-2001','2025-08-20 07:19:08'),
(2,'North Branch','600 North Street, City, State 66666',5000.00,'Bob Wilson','bob@spareparts.com','+1-555-2002','2025-08-20 07:19:08'),
(3,'South Branch','700 South Avenue, City, State 77777',3000.00,'Carol Davis','carol@spareparts.com','+1-555-2003','2025-08-20 07:19:08'),
(4,'Ramsis Warehouse','Rehab City - Group 27 - Building 9 - Apt. 34\r\nNew Cairo',1000.00,'Ali Rabee','arabee@gmail.com','01007847333','2025-08-21 15:41:13');
/*!40000 ALTER TABLE `sp_warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sp_main'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-08-24 10:56:36
