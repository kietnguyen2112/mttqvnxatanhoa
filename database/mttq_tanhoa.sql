-- MySQL dump 10.13  Distrib 9.6.0, for macos26.2 (arm64)
--
-- Host: 127.0.0.1    Database: mttq_tanhoa
-- ------------------------------------------------------
-- Server version	9.6.0

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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$12$rqoQUgfD5hckgaDsRQjMX.IsvxUVxwhmPL3t6PzexA2j5SKBel3lK','2026-05-23 09:18:30','2026-05-23 09:16:10');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_files`
--

DROP TABLE IF EXISTS `document_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_files_path_unique` (`file_path`),
  KEY `document_files_document_index` (`document_id`),
  CONSTRAINT `document_files_document_id_fk` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_files`
--

LOCK TABLES `document_files` WRITE;
/*!40000 ALTER TABLE `document_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `document_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `issued_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `documents_issued_date_index` (`issued_date`),
  KEY `documents_type_index` (`document_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hamlet_members`
--

DROP TABLE IF EXISTS `hamlet_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hamlet_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `hamlet_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `role` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hamlet_members_unique` (`organization_id`,`hamlet_name`,`full_name`,`role`),
  KEY `hamlet_members_hamlet_index` (`hamlet_name`),
  CONSTRAINT `hamlet_members_organization_id_fk` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hamlet_members`
--

LOCK TABLES `hamlet_members` WRITE;
/*!40000 ALTER TABLE `hamlet_members` DISABLE KEYS */;
INSERT INTO `hamlet_members` VALUES (25,1,'Ấp Một Ngàn','Nguyễn Văn Hà',NULL,'Trưởng Ban Công tác Mặt trận ấp','0901281720','',1),(26,1,'Ấp Tân Thuận','Phạm Văn Hòa',NULL,'Trưởng Ban Công tác Mặt trận ấp','0794971400','',2),(27,1,'Ấp Nhơn Thuận','Võ Thị Hết',NULL,'Trưởng Ban Công tác Mặt trận ấp','0765987187','',3),(28,1,'Ấp Nhơn Xuân','Phạm Văn Cưỡng',NULL,'Trưởng Ban Công tác Mặt trận ấp','0766872944','',4),(29,1,'Ấp Tân Lợi','Nguyễn Ngọc Dã',NULL,'Trưởng Ban Công tác Mặt trận ấp','0767927733','',5),(30,1,'Ấp Thị Tứ','Bùi Văn Đa',NULL,'Trưởng Ban Công tác Mặt trận ấp','0915611693','',6),(31,1,'Ấp 2','Đặng Văn Đây',NULL,'Trưởng Ban Công tác Mặt trận ấp','0939414569','',7),(32,1,'Ấp 5','Trần Văn Phước',NULL,'Trưởng Ban Công tác Mặt trận ấp','0704743783','',8),(33,1,'Ấp 3','Nguyễn Ngọc Thanh',NULL,'Trưởng Ban Công tác Mặt trận ấp','0945281475','',9),(34,1,'Ấp 6','Nguyễn Văn Lượm',NULL,'Trưởng Ban Công tác Mặt trận ấp','0706509960','',10),(35,1,'Ấp 4','Phạm Văn Mãnh',NULL,'Trưởng Ban Công tác Mặt trận ấp','0947906460','',11),(36,1,'Ấp 7','Nguyễn Ngọc Thành',NULL,'Trưởng Ban Công tác Mặt trận ấp','0375677570','',12),(37,1,'Ấp Bảy Ngàn','Nguyễn Tấn Hưng',NULL,'Trưởng Ban Công tác Mặt trận ấp','0769377123','',13),(38,1,'Ấp 2A','Đoàn Hùng Hà',NULL,'Trưởng Ban Công tác Mặt trận ấp','0702954311','',14),(39,1,'Ấp 3A','Huỳnh Văn Trưng',NULL,'Trưởng Ban Công tác Mặt trận ấp','0778101672','',15),(40,1,'Ấp 3B','Nguyễn Văn Phượng',NULL,'Trưởng Ban Công tác Mặt trận ấp','0706559007','',16),(41,1,'Ấp 1A','Trần Thị Lụa',NULL,'Trưởng Ban Công tác Mặt trận ấp','0939193499','',17),(42,1,'Ấp 1B','Lâm Quốc Kỳ',NULL,'Trưởng Ban Công tác Mặt trận ấp','0888987847','',18),(43,1,'Ấp 4A','Tạ Quan Tiến',NULL,'Trưởng Ban Công tác Mặt trận ấp','0383911996','',19),(44,1,'Ấp 4B','Lê Văn Chiều',NULL,'Trưởng Ban Công tác Mặt trận ấp','0934529017','',20),(45,1,'Ấp 6B','Trần Văn Dệt',NULL,'Trưởng Ban Công tác Mặt trận ấp','0379467031','',21),(46,1,'Ấp Nhơn Hòa','Nguyễn Văn Bước',NULL,'Trưởng Ban Công tác Mặt trận ấp','0932628210','',22),(47,1,'Ấp Nhơn Ninh','Ngô Hữu Thọ',NULL,'Trưởng Ban Công tác Mặt trận ấp','0915668462','',23),(48,1,'Ấp Nhơn Phú 1','Phạm Văn Chiến',NULL,'Trưởng Ban Công tác Mặt trận ấp','0327933040','',24),(49,1,'Ấp Nhơn Thọ','Trương Văn Út',NULL,'Trưởng Ban Công tác Mặt trận ấp','0788765331','',25),(50,1,'Ấp Nhơn Thuận 1','Phan Thanh Thế',NULL,'Trưởng Ban Công tác Mặt trận ấp','0778137145','',26),(51,1,'Ấp Nhơn Thuận 1A','Nguyễn Văn Nhỏ',NULL,'Trưởng Ban Công tác Mặt trận ấp','0795897125','',27),(52,1,'Ấp Nhơn Thuận 1B','Tiết Văn Tú',NULL,'Trưởng Ban Công tác Mặt trận ấp','0984070580','',28),(53,1,'Ấp Nhơn Phú 2','Nguyễn Văn Diễn',NULL,'Trưởng Ban Công tác Mặt trận ấp','0977789276','',29),(54,1,'Ấp 2B','Nguyễn Thành Tâm',NULL,'Trưởng Ban Công tác Mặt trận ấp','0788951704','',30),(55,1,'Ấp 5B','Huỳnh Long Trường',NULL,'Trưởng Ban Công tác Mặt trận ấp','0794994273','',31),(56,1,'Ấp Nhơn Phú','Diệp Em',NULL,'Trưởng Ban Công tác Mặt trận ấp','0338610450','',32),(59,4,'Ấp Tân Lợi','Trần Văn Coi',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1969.0',4),(60,4,'Ấp 2B','Nguyễn Thu Hà',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1961.0',5),(61,4,'Ấp 6B','Trần Văn Dệt',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1968.0',6),(62,4,'Ấp 1B','Huỳnh Văn Giỏi',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1995.0',7),(63,4,'Ấp Tân Thuận','Nguyễn Văn Để',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1957.0',8),(64,4,'Ấp 2A','Đoàn Hùng Hà',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1968.0',9),(65,4,'Ấp Nhơn Hòa','Nguyễn Văn Hòa',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1960.0',10),(66,4,'Ấp 1A','Đinh Văn Nhớ',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1960.0',11),(67,4,'Ấp Một Ngàn','Nguyễn Văn Sáng',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1957.0',12),(68,4,'Ấp 2','Lê Thành Tâm',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1975.0',13),(69,4,'Ấp Bảy Ngàn','Lê Văn Thương',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1960.0',14),(70,4,'Ấp Thị Tứ','Bùi Văn Vui',NULL,'Chi hội trưởng','','Năm sinh/ngày sinh: 1964.0',15),(71,3,'Hội LHPN xã','Bùi Thị Hồng Thơm',NULL,'Chủ tịch','','Năm sinh/ngày sinh: 31067.0',1),(72,3,'Hội LHPN xã','Vũ Phạm Lanh',NULL,'P. Chủ tịch','','Năm sinh/ngày sinh: 28687.0',2),(73,3,'Hội LHPN xã','Trương Thị Kim Thúy',NULL,'Chuyên viên UBMTTQ VN','','Năm sinh/ngày sinh: 32096.0',3),(74,3,'Hội LHPN xã','Huỳnh Thị Hằng',NULL,'Cán bộ không chuyên trách Hội LHPN xã','','Năm sinh/ngày sinh: 32060.0',4),(75,3,'Văn phòng HĐND - UBND','Nguyễn Thị Thúy Hằng',NULL,'Phó chánh văn phòng','','Năm sinh/ngày sinh: 31312.0',5),(76,3,'Trường MG Tuổi Hồng','Nguyễn Thị Ngọc Nữ',NULL,'P. Hiệu trưởng','','Năm sinh/ngày sinh: 32728.0',6),(77,3,'Trường TH Kim Đồng','Trần Thị Tuyết Phương',NULL,'Giáo viên','','Năm sinh/ngày sinh: 1979.0',7),(78,3,'Công An xã','Nguyễn Thị Thanh Phương',NULL,'Chi hội PN','','Năm sinh/ngày sinh: 29855.0',8),(79,3,'Trưởng trạm Y tế xã','Trần Thị Minh Châu',NULL,'Phó Giám đốc','','Năm sinh/ngày sinh: 30824.0',9),(80,3,'Hội Nông Dân','Lê Thị Thùy Dung',NULL,'Chuyên viên Uỷ ban MTTQ','','Năm sinh/ngày sinh: 1980.0',10),(81,3,'Đoàn Thanh Niên','Nguyễn Thị Hồng Thoa',NULL,'Chuyên viên UBMTTQ VN','','Năm sinh/ngày sinh: 31778.0',11),(82,3,'UBKT Đảng ủy xã','Lê Thị Mỹ Khánh',NULL,'Phó Chủ nhiệm','','Năm sinh/ngày sinh: 29601.0',12),(83,3,'Trường THPT Châu Thành A','Nguyễn Thị Tính',NULL,'Phó hiệu trưởng','','Năm sinh/ngày sinh: 29384.0',13),(84,3,'Trường THCS Tân Hòa','Phạm Thị Huỳnh Trinh',NULL,'Giáo viên','','Năm sinh/ngày sinh: 29549.0',14),(85,3,'Trường TH Nhơn Nghĩa A 2','Nguyễn Thị Hồng Mai',NULL,'Hiệu trưởng','','Năm sinh/ngày sinh: 1974.0',15),(86,3,'Trường Mầm non Hướng Dương','Bùi Thị Ngọc Sương',NULL,'Phó hiệu trưởng','','Năm sinh/ngày sinh: 28663.0',16),(87,3,'Trường Mẫu giáo Tuổi Ngọc','Lê Thị Sơn Ca',NULL,'Phó hiệu trưởng trường','','Năm sinh/ngày sinh: 31316.0',17),(88,3,'Ấp 6B Xã Tân Hoà','Nguyễn Thị Ngoan',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 28408.0',18),(89,3,'Ấp 2B Xã Tân Hoà','Nguyễn Thu Hà',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 22399.0',19),(90,3,'Ấp Nhơn Hoà, xã Tân Hoà','Dương Thị Hiệp',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 1957.0',20),(91,3,'Ấp Nhơn Thọ, xã Tân Hoà','Nguyễn Thị Mộng Tuyền',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 1970.0',22),(92,3,'Ấp Thị Tứ xã Tân Hoà','Lê Hồng Ngự',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 1964.0',23),(93,3,'Ấp Nhơn Thuận xã Tân Hoà','Diệp Thị Hiếu',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 1988.0',24),(94,3,'Ấp 2 xã Tân Hoà','Trần Thị Ngọc Giàu',NULL,'Chi hội Trưởng','','Năm sinh/ngày sinh: 1966.0',25),(95,2,'UB. MTTQ VN XÃ','Nguyễn Ngọc Công Hữu',NULL,'Phó Chủ tịch MTTQ VN - Bí thư Đoàn xã Tân Hòa','','Năm sinh/ngày sinh: 33904.0',1),(96,2,'UB. MTTQ VN XÃ','Nguyễn Thanh Tuấn',NULL,'Phó Bí thư Đoàn xã','','Năm sinh/ngày sinh: 35040.0',2),(97,2,'Văn phòng HĐND - UBND','Trần Lê Như Ngọc',NULL,'Chuyên viên\nUB MTTQ VN xã','','Năm sinh/ngày sinh: 35362.0',3),(98,2,'UB. MTTQ VN XÃ','Nguyễn Chí Trung',NULL,'Chuyên viên\nVăn phòng\nHĐND - UBND','','Năm sinh/ngày sinh: 22/02/1995',4),(99,2,'Công an xã','Nguyễn Hoàng Nhi',NULL,'BTCĐ Công an','','Năm sinh/ngày sinh: 22/9/1994',5),(100,2,'Trường THPT Châu Thành A','Trần Thị Bạch Tuyết',NULL,'BTCĐ Trường\nTHPT CTA','','Năm sinh/ngày sinh: 34693.0',6),(101,2,'Trường THCS Tân Hòa','Trần Thị Thu Thảo',NULL,'BTCĐ Trường THCS Tân Hòa','','Năm sinh/ngày sinh: 34932.0',7),(102,2,'MG Tuổi Hồng','Dương Kim Đon',NULL,'BTCĐ Trường\nMG Tuổi Hồng','','Năm sinh/ngày sinh: 35600.0',8),(103,2,'MG Tuôổi Ngọc','Nguyễn Thị Ngọc Lành',NULL,'BTCĐ Trường\nMG Tuổi Ngọc','','Năm sinh/ngày sinh: 35425.0',9),(104,2,'Trường THPT Châu Thành A','Danh Thanh Sang',NULL,'P.BTCĐ Trường\nTHPT CTA','','Năm sinh/ngày sinh: 35918.0',10),(105,2,'TTYT','Trần Thị Hải An',NULL,'BTCĐ TTYT','','Năm sinh/ngày sinh: 33562.0',11),(106,2,'ấp Tân Lợi','Tiết Thị Cẩm Trúc',NULL,'BTCĐ Tân Lợi','','Năm sinh/ngày sinh: 28/06/1999',12),(107,2,'ấp Nhơn Thuận','Đặng Hoàng Dũng',NULL,'Đoàn viên','','Năm sinh/ngày sinh: 37067.0',13),(108,2,'âp NTIB','Nguyễn Hữu Kiệt',NULL,'CB TT phục vụ\nhành chính công','','Năm sinh/ngày sinh: 21/12/2002',14),(109,2,'ấp Một Ngàn','Phan Thanh Diễm',NULL,'BTCĐ ấp Một Ngàn','','Năm sinh/ngày sinh: 1995.0',15),(110,2,'ấp Thị Tứ','Trà Ngọc Thắm',NULL,'BTCĐ ấp Thị Tứ\n(Một Ngàn cũ)','','Năm sinh/ngày sinh: 1995.0',16),(111,2,'ấp Tân Thuận','Phan Hoàng Kha',NULL,'BTCĐ','','Năm sinh/ngày sinh: 2001.0',17),(112,2,'ấp Bảy Ngàn','Nguyễn Thị Phượng Trân',NULL,'BTCĐ','','Năm sinh/ngày sinh: 36077.0',18),(113,2,'ấp 2A','Nguyễn Quang Nhựt',NULL,'BTCĐ ấp 2A\n(Tân Hòa cũ)','','Năm sinh/ngày sinh: 1998.0',19),(114,2,'ấp IB','Huỳnh Văn Giỏi',NULL,'BTCĐ ấp IB\n(Tân Hòa cũ)','','Năm sinh/ngày sinh: 1995.0',20);
/*!40000 ALTER TABLE `hamlet_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_group_members`
--

DROP TABLE IF EXISTS `loan_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_group_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loan_group_id` bigint unsigned NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Thành viên',
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `loan_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `outstanding_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `overdue_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `loan_group_members_group_id_fk` (`loan_group_id`),
  CONSTRAINT `loan_group_members_group_id_fk` FOREIGN KEY (`loan_group_id`) REFERENCES `loan_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_group_members`
--

LOCK TABLES `loan_group_members` WRITE;
/*!40000 ALTER TABLE `loan_group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_groups`
--

DROP TABLE IF EXISTS `loan_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `hamlet_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_count` int unsigned NOT NULL DEFAULT '0',
  `fund_source` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `outstanding_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `savings_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `overdue_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `rating` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `loan_groups_organization_id_fk` (`organization_id`),
  KEY `loan_groups_hamlet_index` (`hamlet_name`),
  CONSTRAINT `loan_groups_organization_id_fk` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_groups`
--

LOCK TABLES `loan_groups` WRITE;
/*!40000 ALTER TABLE `loan_groups` DISABLE KEYS */;
INSERT INTO `loan_groups` VALUES (33,5,'Ấp 6','Tổ vay vốn Hội Nông dân - Trần Văn Trung','Trần Văn Trung','',48,'Ngân hàng Chính sách xã hội',2606937930.00,102839230.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(34,5,'Ấp 6','Tổ vay vốn Hội Nông dân - Trần Văn Sĩ','Trần Văn Sĩ','',42,'Ngân hàng Chính sách xã hội',1765500000.00,102932400.00,30000000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(35,5,'Ấp 4','Tổ vay vốn Hội Nông dân - Thạch Rắc Sa Mây','Thạch Rắc Sa Mây','',40,'Ngân hàng Chính sách xã hội',1359428000.00,82073971.00,30028000.00,'Trung bình','Xếp loại: Trung bình','2026-06-04 14:01:09'),(36,5,'Ấp 5','Tổ vay vốn Hội Nông dân - Nguyễn Tấn Phát','Nguyễn Tấn Phát','',57,'Ngân hàng Chính sách xã hội',2608997986.00,70297956.00,29997986.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(37,5,'Ấp Bảy Ngàn','Tổ vay vốn Hội Nông dân - Nguyễn Tấn Hưng','Nguyễn Tấn Hưng','',40,'Ngân hàng Chính sách xã hội',1989500000.00,163889522.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(38,5,'Ấp 7','Tổ vay vốn Hội Nông dân - Nguyễn Bảo Lộc','Nguyễn Bảo Lộc','',41,'Ngân hàng Chính sách xã hội',1779465163.00,72225477.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(39,5,'Ấp 5','Tổ vay vốn Hội Nông dân - Lê Thanh Tân','Lê Thanh Tân','',42,'Ngân hàng Chính sách xã hội',1437000000.00,41117737.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(40,5,'Ấp 4','Tổ vay vốn Hội Nông dân - Huỳnh Thành Chót','Huỳnh Thành Chót','',37,'Ngân hàng Chính sách xã hội',1683000000.00,122820489.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(41,5,'Ấp 6','Tổ vay vốn Hội Nông dân - Chung Văn Tuôi','Chung Văn Tuôi','',44,'Ngân hàng Chính sách xã hội',1901987988.00,96606567.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(42,5,'Ấp 6','Tổ vay vốn Hội Nông dân - Bùi Văn Mười','Bùi Văn Mười','',59,'Ngân hàng Chính sách xã hội',2880999581.00,132805960.00,49999581.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(43,5,'Ấp 3','Tổ vay vốn Hội Nông dân - Bạch Thị Nương','Bạch Thị Nương','',48,'Ngân hàng Chính sách xã hội',1770616800.00,81922349.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(44,5,'Ấp Nhơn Thuận','Tổ vay vốn Hội Nông dân - Võ Thị Hết','Võ Thị Hết','',51,'Ngân hàng Chính sách xã hội',2578100000.00,148103182.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(45,5,'Ấp Nhơn Xuân','Tổ vay vốn Hội Nông dân - Phạm Văn Cưỡng','Phạm Văn Cưỡng','',56,'Ngân hàng Chính sách xã hội',3838717618.00,98485973.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(46,5,'Ấp Một Ngàn','Tổ vay vốn Hội Nông dân - Nguyễn Văn Sáng','Nguyễn Văn Sáng','',33,'Ngân hàng Chính sách xã hội',1652000000.00,92010310.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(47,5,'Ấp Tân Thuận','Tổ vay vốn Hội Nông dân - Nguyễn Văn Núi','Nguyễn Văn Núi','',44,'Ngân hàng Chính sách xã hội',1826030772.00,109328539.00,8000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(48,5,'Ấp Một Ngàn','Tổ vay vốn Hội Nông dân - Nguyễn Văn Mót','Nguyễn Văn Mót','',43,'Ngân hàng Chính sách xã hội',1432932899.00,86964106.00,8932899.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(49,5,'Ấp Tân Thuận','Tổ vay vốn Hội Nông dân - Nguyễn Văn Lợi','Nguyễn Văn Lợi','',57,'Ngân hàng Chính sách xã hội',3337360000.00,223879724.00,5000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(50,5,'Ấp Tân Lợi','Tổ vay vốn Hội Nông dân - Nguyễn Thành Tâm','Nguyễn Thành Tâm','',60,'Ngân hàng Chính sách xã hội',3557000000.00,188619738.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(51,5,'Ấp Tân Thuận','Tổ vay vốn Hội Nông dân - Nguyễn Đông Phương','Nguyễn Đông Phương','',37,'Ngân hàng Chính sách xã hội',2967000000.00,166189372.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(52,5,'Ấp Nhơn Xuân','Tổ vay vốn Hội Nông dân - Dương Văn Hải','Dương Văn Hải','',38,'Ngân hàng Chính sách xã hội',1974000000.00,47051968.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(53,5,'Ấp Thị Tứ','Tổ vay vốn Hội Nông dân - Bùi Văn Đa','Bùi Văn Đa','',49,'Ngân hàng Chính sách xã hội',2130600000.00,59985163.00,9000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(54,5,'Ấp Nhơn Thọ','Tổ vay vốn Hội Nông dân - Trần Văn Nam','Trần Văn Nam','',42,'Ngân hàng Chính sách xã hội',3270000000.00,96068982.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(55,5,'Ấp Nhơn Thuận 1','Tổ vay vốn Hội Nông dân - Trần Văn Hiền','Trần Văn Hiền','',53,'Ngân hàng Chính sách xã hội',2879364033.00,74652075.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(56,5,'Ấp Nhơn Phú 2','Tổ vay vốn Hội Nông dân - Trần Văn Điều','Trần Văn Điều','',35,'Ngân hàng Chính sách xã hội',1777000000.00,113153107.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(57,5,'Ấp Nhơn Thọ','Tổ vay vốn Hội Nông dân - Trần Trung Tri','Trần Trung Tri','',48,'Ngân hàng Chính sách xã hội',3664000000.00,150995292.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(58,5,'Ấp Nhơn Phú 2','Tổ vay vốn Hội Nông dân - Trần Quốc Quang','Trần Quốc Quang','',49,'Ngân hàng Chính sách xã hội',2298619906.00,97268909.00,3877391.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(59,5,'Ấp Nhơn Hòa','Tổ vay vốn Hội Nông dân - Thái Thanh Hùng','Thái Thanh Hùng','',59,'Ngân hàng Chính sách xã hội',2861130686.00,163175696.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(60,5,'Ấp Nhơn Thuân 1B','Tổ vay vốn Hội Nông dân - Thái Hoàng Khai','Thái Hoàng Khai','',53,'Ngân hàng Chính sách xã hội',1870500000.00,113354941.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(61,5,'Ấp Nhơn Thuận 1A','Tổ vay vốn Hội Nông dân - Phan Thanh Thế','Phan Thanh Thế','',55,'Ngân hàng Chính sách xã hội',3612500000.00,191266872.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(62,5,'Ấp Nhơn Phú 1','Tổ vay vốn Hội Nông dân - Phạm Văn Chiến','Phạm Văn Chiến','',43,'Ngân hàng Chính sách xã hội',3035000000.00,250136739.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(63,5,'Ấp Nhơn Phú 2','Tổ vay vốn Hội Nông dân - Nguyễn Văn Út Lớn','Nguyễn Văn Út Lớn','',33,'Ngân hàng Chính sách xã hội',1650500000.00,56871224.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(64,5,'Ấp Nhơn Thuận 1A','Tổ vay vốn Hội Nông dân - Nguyễn Văn Tol','Nguyễn Văn Tol','',54,'Ngân hàng Chính sách xã hội',1953000000.00,135047253.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(65,5,'Ấp Nhơn Phú','Tổ vay vốn Hội Nông dân - Nguyễn Văn Thảnh','Nguyễn Văn Thảnh','',36,'Ngân hàng Chính sách xã hội',2012500000.00,83768671.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(66,5,'Ấp Nhơn Ninh','Tổ vay vốn Hội Nông dân - Nguyễn Hữu Hùng','Nguyễn Hữu Hùng','',48,'Ngân hàng Chính sách xã hội',1813000000.00,63845103.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(67,5,'Ấp Nhơn Phú','Tổ vay vốn Hội Nông dân - Mạch Chí Hồng','Mạch Chí Hồng','',36,'Ngân hàng Chính sách xã hội',1229500000.00,71955744.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(68,5,'Ấp Nhơn Phú 1','Tổ vay vốn Hội Nông dân - Huỳnh Văn Út','Huỳnh Văn Út','',55,'Ngân hàng Chính sách xã hội',2612715096.00,188071112.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(69,5,'Ấp 2B','Tổ vay vốn Hội Nông dân - Trần Văn Thương','Trần Văn Thương','',43,'Ngân hàng Chính sách xã hội',2085805903.00,59107894.00,17975485.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(70,5,'Ấp 2A','Tổ vay vốn Hội Nông dân - Trần Văn Sang','Trần Văn Sang','',53,'Ngân hàng Chính sách xã hội',2078500000.00,79847752.00,8000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(71,5,'Ấp 1A','Tổ vay vốn Hội Nông dân - Trần Văn Quang','Trần Văn Quang','',37,'Ngân hàng Chính sách xã hội',1689363520.00,47123953.00,13978520.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(72,5,'Ấp 6B','Tổ vay vốn Hội Nông dân - Trần Văn Dệt','Trần Văn Dệt','',45,'Ngân hàng Chính sách xã hội',1964800000.00,58545389.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(73,5,'Ấp 1B','Tổ vay vốn Hội Nông dân - Trần Thanh Dưỡng','Trần Thanh Dưỡng','',51,'Ngân hàng Chính sách xã hội',1574400000.00,66501329.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(74,5,'Ấp 3B','Tổ vay vốn Hội Nông dân - Tạ Văn Hớn','Tạ Văn Hớn','',45,'Ngân hàng Chính sách xã hội',1421000000.00,66406489.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(75,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Tạ Thị Tuyết Em','Tạ Thị Tuyết Em','',49,'Ngân hàng Chính sách xã hội',2349980935.00,72014772.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(76,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Tạ Quan Tiến','Tạ Quan Tiến','',57,'Ngân hàng Chính sách xã hội',2822800000.00,56271739.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(77,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Phan Văn Trí','Phan Văn Trí','',51,'Ngân hàng Chính sách xã hội',3003000000.00,68237892.00,26000000.00,'Trung bình','Xếp loại: Trung bình','2026-06-04 14:01:09'),(78,5,'Ấp 5B','Tổ vay vốn Hội Nông dân - Phạm Văn Hoàng','Phạm Văn Hoàng','',40,'Ngân hàng Chính sách xã hội',2701084285.00,139827355.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(79,5,'Ấp 4B','Tổ vay vốn Hội Nông dân - Phạm Thị Gọn','Phạm Thị Gọn','',41,'Ngân hàng Chính sách xã hội',3114000000.00,96375158.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(80,5,'Ấp 3A','Tổ vay vốn Hội Nông dân - Nguyễn Văn Nhuận','Nguyễn Văn Nhuận','',45,'Ngân hàng Chính sách xã hội',1396000000.00,113138248.00,4000000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(81,5,'Ấp 1B','Tổ vay vốn Hội Nông dân - Nguyễn Văn Mực','Nguyễn Văn Mực','',49,'Ngân hàng Chính sách xã hội',1675998000.00,34245196.00,0.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(82,5,'Ấp 5B','Tổ vay vốn Hội Nông dân - Nguyễn Văn Luận','Nguyễn Văn Luận','',45,'Ngân hàng Chính sách xã hội',1804475976.00,121976689.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(83,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Nguyễn Thị Vui','Nguyễn Thị Vui','',53,'Ngân hàng Chính sách xã hội',3254000000.00,102720295.00,48000000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(84,5,'Ấp 1A','Tổ vay vốn Hội Nông dân - Nguyễn Thị Thu Thảo','Nguyễn Thị Thu Thảo','',49,'Ngân hàng Chính sách xã hội',1995799978.00,79881018.00,6999978.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(85,5,'Ấp 3A','Tổ vay vốn Hội Nông dân - Nguyễn Thanh Tùng','Nguyễn Thanh Tùng','',46,'Ngân hàng Chính sách xã hội',1785500000.00,44892942.00,15000000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(86,5,'Ấp 3A','Tổ vay vốn Hội Nông dân - Nguyễn Kim Thanh','Nguyễn Kim Thanh','',56,'Ngân hàng Chính sách xã hội',2111600000.00,66760535.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(87,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Nguyễn Hoàng Sĩ','Nguyễn Hoàng Sĩ','',52,'Ngân hàng Chính sách xã hội',2533000000.00,167272652.00,16000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(88,5,'Ấp 3A','Tổ vay vốn Hội Nông dân - Huỳnh Văn Trưng','Huỳnh Văn Trưng','',54,'Ngân hàng Chính sách xã hội',2143000000.00,82529002.00,18000000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(89,5,'Ấp 6B','Tổ vay vốn Hội Nông dân - Huỳnh Hải Lý','Huỳnh Hải Lý','',49,'Ngân hàng Chính sách xã hội',2266996000.00,139885646.00,4996000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(90,5,'Ấp 2B','Tổ vay vốn Hội Nông dân - Đoàn Phước Khoe','Đoàn Phước Khoe','',47,'Ngân hàng Chính sách xã hội',2200800000.00,146423570.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(91,5,'Ấp 2B','Tổ vay vốn Hội Nông dân - Đặng Văn Hải','Đặng Văn Hải','',27,'Ngân hàng Chính sách xã hội',1362482000.00,48694449.00,11982000.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(92,5,'Ấp 4A','Tổ vay vốn Hội Nông dân - Đặng Thị Thanh Hoa','Đặng Thị Thanh Hoa','',42,'Ngân hàng Chính sách xã hội',2105525470.00,50414722.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(93,5,'Ấp 2A','Tổ vay vốn Hội Nông dân - Bùi Văn Rô','Bùi Văn Rô','',58,'Ngân hàng Chính sách xã hội',2290000000.00,73546397.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(94,5,'Ấp 2A','Tổ vay vốn Hội Nông dân - Bùi Thị Thu Sương','Bùi Thị Thu Sương','',55,'Ngân hàng Chính sách xã hội',2641000000.00,60636302.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(95,4,'Nhơn Ninh','Tổ vay vốn Hội CCB - Lương Văn Mưa','Lương Văn Mưa','',33,'Ngân hàng Chính sách xã hội',1219000000.00,2830000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(96,4,'Nhơn Hòa','Tổ vay vốn Hội CCB - Nguyễn Văn Hoà','Nguyễn Văn Hoà','',53,'Ngân hàng Chính sách xã hội',2478500000.00,8246000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(97,4,'Nhơn Phú 1','Tổ vay vốn Hội CCB - Nguyễn Văn Tươi','Nguyễn Văn Tươi','',53,'Ngân hàng Chính sách xã hội',2644141258.00,27900000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(98,4,'Nhơn Phú','Tổ vay vốn Hội CCB - Tống Văn Ly','Tống Văn Ly','',32,'Ngân hàng Chính sách xã hội',1483400000.00,11950000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(99,4,'Nhơn Thuận 1','Tổ vay vốn Hội CCB - Lương Văn Chính','Lương Văn Chính','',59,'Ngân hàng Chính sách xã hội',3859671218.00,11785000.00,55671218.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(100,4,'Tân Thuận','Tổ vay vốn Hội CCB - Nguyễn Hồng Việt','Nguyễn Hồng Việt','',56,'Ngân hàng Chính sách xã hội',3938175759.00,20830000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(101,4,'Ấp 1A','Tổ vay vốn Hội CCB - Trần Hoàng Nghĩa','Trần Hoàng Nghĩa','',57,'Ngân hàng Chính sách xã hội',2781094000.00,18537000.00,29994000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(102,4,'Ấp 4B','Tổ vay vốn Hội CCB - Trần Phước Thiện','Trần Phước Thiện','',36,'Ngân hàng Chính sách xã hội',1667000000.00,10430000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(103,4,'ấp 6B','Tổ vay vốn Hội CCB - Lê Văn Mười Ba','Lê Văn Mười Ba','',51,'Ngân hàng Chính sách xã hội',2039569125.00,25820814.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(104,4,'ấp 3B','Tổ vay vốn Hội CCB - Lê Thành Công','Lê Thành Công','',45,'Ngân hàng Chính sách xã hội',1936000000.00,22437247.00,0.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(105,4,'ấp 5B','Tổ vay vốn Hội CCB - Phạm Thị Mai','Phạm Thị Mai','',31,'Ngân hàng Chính sách xã hội',1744000000.00,3415018.00,14000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(106,4,'Ấp 6B','Tổ vay vốn Hội CCB - Huỳnh Thị Hằng','Huỳnh Thị Hằng','',46,'Ngân hàng Chính sách xã hội',2279000000.00,21617073.00,47000000.00,'Trung bình','Xếp loại: Trung bình','2026-06-04 14:01:09'),(107,4,'ấp 4','Tổ vay vốn Hội CCB - Võ Văn Việt','Võ Văn Việt','',39,'Ngân hàng Chính sách xã hội',2084300000.00,10518000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(108,4,'ấp 5','Tổ vay vốn Hội CCB - Trần Văn Nhỏ','Trần Văn Nhỏ','',40,'Ngân hàng Chính sách xã hội',1410900000.00,11840000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(109,4,'Ấp Bảy Ngàn','Tổ vay vốn Hội CCB - Trần Thị Phượng','Trần Thị Phượng','',59,'Ngân hàng Chính sách xã hội',3519036437.00,20239000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(110,4,'Ấp Bảy Ngàn','Tổ vay vốn Hội CCB - Lê Văn Thương','Lê Văn Thương','',53,'Ngân hàng Chính sách xã hội',3107963986.00,6489000.00,54963986.00,'Trung bình','Xếp loại: Trung bình','2026-06-04 14:01:09'),(111,4,'Ấp 3','Tổ vay vốn Hội CCB - Nguyễn Ngọc Thanh','Nguyễn Ngọc Thanh','',48,'Ngân hàng Chính sách xã hội',1811489138.00,7266000.00,38989138.00,'Trung bình','Xếp loại: Trung bình','2026-06-04 14:01:09'),(112,4,'Ấp 7','Tổ vay vốn Hội CCB - Nguyễn Văn Mỹ','Nguyễn Văn Mỹ','',44,'Ngân hàng Chính sách xã hội',2411000000.00,14489000.00,8000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(113,4,'Ấp 2','Tổ vay vốn Hội CCB - Lê Thành Tâm','Lê Thành Tâm','',41,'Ngân hàng Chính sách xã hội',2668000000.00,6262000.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(114,3,'Ấp Nhơn Thuận','Tổ vay vốn Phụ nữ - Tạ Thị Ngọc Mỹ','Tạ Thị Ngọc Mỹ','',44,'Ngân hàng Chính sách xã hội',3153000000.00,149413271.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(115,3,'Ấp Nhơn Xuân','Tổ vay vốn Phụ nữ - Phạm Thị Xuân','Phạm Thị Xuân','',46,'Ngân hàng Chính sách xã hội',2331000000.00,162353442.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(116,3,'Ấp Thị Tứ','Tổ vay vốn Phụ nữ - Nguyễn Thị Ngọc Nga','Nguyễn Thị Ngọc Nga','',49,'Ngân hàng Chính sách xã hội',2454300000.00,158523650.00,7000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(117,3,'Ấp Tân Thuận','Tổ vay vốn Phụ nữ - Huỳnh Thị Lệ','Huỳnh Thị Lệ','',49,'Ngân hàng Chính sách xã hội',2986000000.00,150519040.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(118,3,'Ấp Nhơn Xuân','Tổ vay vốn Phụ nữ - Bùi Văn Lợi','Bùi Văn Lợi','',52,'Ngân hàng Chính sách xã hội',2235100000.00,122852373.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(119,3,'Ấp Nhơn Thọ','Tổ vay vốn Phụ nữ - Trương Văn Út','Trương Văn Út','',48,'Ngân hàng Chính sách xã hội',2393997000.00,122053360.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(120,3,'Ấp Nhơn Thuận 1','Tổ vay vốn Phụ nữ - Trương Thị Liên Hoa','Trương Thị Liên Hoa','',58,'Ngân hàng Chính sách xã hội',2908000000.00,109541216.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(121,3,'Ấp Nhơn Phú 1','Tổ vay vốn Phụ nữ - Trần Thị Sáu','Trần Thị Sáu','',47,'Ngân hàng Chính sách xã hội',2238500000.00,146919026.00,6000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(122,3,'Ấp Nhơn Thuận 1B','Tổ vay vốn Phụ nữ - Trần Thị Hiếu','Trần Thị Hiếu','',50,'Ngân hàng Chính sách xã hội',2629500000.00,106579519.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(123,3,'Ấp Nhơn Phú','Tổ vay vốn Phụ nữ - Tràn Ngọc Sương','Tràn Ngọc Sương','',34,'Ngân hàng Chính sách xã hội',2123000000.00,104107986.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(124,3,'Ấp Nhơn Thuận 1A','Tổ vay vốn Phụ nữ - Thái Kim Thêu','Thái Kim Thêu','',57,'Ngân hàng Chính sách xã hội',2658000000.00,143856032.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(125,3,'Ấp Nhơn Phú 2','Tổ vay vốn Phụ nữ - Nguyễn Văn Diễn','Nguyễn Văn Diễn','',49,'Ngân hàng Chính sách xã hội',2726400000.00,123634059.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(126,3,'Ấp Nhơn Ninh','Tổ vay vốn Phụ nữ - Nguyễn Thị Lan','Nguyễn Thị Lan','',47,'Ngân hàng Chính sách xã hội',2753500000.00,133392402.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(127,3,'Ấp Nhơn Phú 2','Tổ vay vốn Phụ nữ - Nguyễn Hoàng Kiếm','Nguyễn Hoàng Kiếm','',32,'Ngân hàng Chính sách xã hội',1526500000.00,58012597.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(128,3,'Ấp Nhơn Thuận 1A','Tổ vay vốn Phụ nữ - Đỗ Thanh Khoản','Đỗ Thanh Khoản','',60,'Ngân hàng Chính sách xã hội',2892000000.00,187912892.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(129,3,'4A','Tổ vay vốn Phụ nữ - Trần Văn Tâm','Trần Văn Tâm','',35,'Ngân hàng Chính sách xã hội',2047000000.00,51219403.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(130,3,'Ấp 3B','Tổ vay vốn Phụ nữ - Phạm Thị Thuý An','Phạm Thị Thuý An','',36,'Ngân hàng Chính sách xã hội',2369000000.00,130940619.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(131,3,'Ấp 1A','Tổ vay vốn Phụ nữ - Nguyễn Thị Tuyết Nhung','Nguyễn Thị Tuyết Nhung','',58,'Ngân hàng Chính sách xã hội',3055999998.00,112764011.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(132,3,'Ấp 6B','Tổ vay vốn Phụ nữ - Nguyễn Thị Ngoan','Nguyễn Thị Ngoan','',59,'Ngân hàng Chính sách xã hội',3994000000.00,81154851.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(133,3,'Ấp 2A','Tổ vay vốn Phụ nữ - Lê Thị Mịnh','Lê Thị Mịnh','',47,'Ngân hàng Chính sách xã hội',2268000000.00,83758387.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(134,3,'Ấp 2','Tổ vay vốn Phụ nữ - Võ Thị Hồng Hên','Võ Thị Hồng Hên','',43,'Ngân hàng Chính sách xã hội',2641000000.00,114028239.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(135,3,'Ấp Bảy Ngàn','Tổ vay vốn Phụ nữ - Trần Thị Út','Trần Thị Út','',62,'Ngân hàng Chính sách xã hội',3074000000.00,148010300.00,0.00,'Khá','Xếp loại: Khá','2026-06-04 14:01:09'),(136,3,'Ấp 4','Tổ vay vốn Phụ nữ - Trần Thị Thu Hồng','Trần Thị Thu Hồng','',54,'Ngân hàng Chính sách xã hội',3179000000.00,202022878.00,11000000.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(137,3,'Ấp 2','Tổ vay vốn Phụ nữ - Trần Hoàng Quân','Trần Hoàng Quân','',53,'Ngân hàng Chính sách xã hội',2680000000.00,152648369.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(138,3,'Ấp 6','Tổ vay vốn Phụ nữ - Nguyễn Văn Bình','Nguyễn Văn Bình','',49,'Ngân hàng Chính sách xã hội',3204000000.00,143044579.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(139,3,'Ấp 3','Tổ vay vốn Phụ nữ - Lê Thanh Hà','Lê Thanh Hà','',60,'Ngân hàng Chính sách xã hội',3497000000.00,207761620.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(140,3,'Ấp Bảy Ngàn','Tổ vay vốn Phụ nữ - Lê Chiến Hữu','Lê Chiến Hữu','',60,'Ngân hàng Chính sách xã hội',3237000000.00,168959164.00,0.00,'Tốt','Xếp loại: Tốt','2026-06-04 14:01:09'),(141,2,'ấp 4','Tổ vay vốn Thanh niên - Thạch Út','Thạch Út','',56,'Ngân hàng Chính sách xã hội',3204500000.00,179174080.00,0.00,'','','2026-06-04 14:01:09'),(142,2,'ấp Bảy Ngàn','Tổ vay vốn Thanh niên - Nguyễn Công Tâm','Nguyễn Công Tâm','',36,'Ngân hàng Chính sách xã hội',1723859671.00,133784340.00,0.00,'','','2026-06-04 14:01:09'),(143,2,'ấp 2','Tổ vay vốn Thanh niên - Đặng Văn Đây','Đặng Văn Đây','',52,'Ngân hàng Chính sách xã hội',2594350000.00,154862709.00,0.00,'','','2026-06-04 14:01:09'),(144,2,'ấp Tân Lợi','Tổ vay vốn Thanh niên - Tiết Thị Cẩm Chúc','Tiết Thị Cẩm Chúc','',41,'Ngân hàng Chính sách xã hội',1569000000.00,99888859.00,0.00,'','','2026-06-04 14:01:09'),(145,2,'ấp Nhơn Thuận 1A','Tổ vay vốn Thanh niên - Nguyễn Văn Nhỏ','Nguyễn Văn Nhỏ','',54,'Ngân hàng Chính sách xã hội',2085000000.00,123330816.00,0.00,'','','2026-06-04 14:01:09'),(146,2,'ấp Nhơn Hòa','Tổ vay vốn Thanh niên - Nguyễn Văn Bước','Nguyễn Văn Bước','',52,'Ngân hàng Chính sách xã hội',2879000000.00,195554876.00,0.00,'','','2026-06-04 14:01:09'),(147,2,'ấp Nhơn Ninh','Tổ vay vốn Thanh niên - Nguyễn Thị Nhạn','Nguyễn Thị Nhạn','',48,'Ngân hàng Chính sách xã hội',2976000000.00,174885595.00,0.00,'','','2026-06-04 14:01:09'),(148,2,'ấp 3B','Tổ vay vốn Thanh niên - Trần Văn Được','Trần Văn Được','',57,'Ngân hàng Chính sách xã hội',2357561000.00,113744986.00,0.00,'','','2026-06-04 14:01:09'),(149,2,'ấp 1B','Tổ vay vốn Thanh niên - Tăng Văn Mười','Tăng Văn Mười','',57,'Ngân hàng Chính sách xã hội',1914100000.00,93445064.00,0.00,'','','2026-06-04 14:01:09'),(150,2,'ấp 4A','Tổ vay vốn Thanh niên - Nguyễn Hữu Tâm','Nguyễn Hữu Tâm','',53,'Ngân hàng Chính sách xã hội',2657700000.00,61387619.00,0.00,'','','2026-06-04 14:01:09');
/*!40000 ALTER TABLE `loan_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_chapters`
--

DROP TABLE IF EXISTS `organization_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_chapters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `household_count` int unsigned NOT NULL DEFAULT '0',
  `member_count` int unsigned NOT NULL DEFAULT '0',
  `male_count` int unsigned NOT NULL DEFAULT '0',
  `female_count` int unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_chapters_unique` (`organization_id`,`name`),
  CONSTRAINT `organization_chapters_organization_id_fk` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_chapters`
--

LOCK TABLES `organization_chapters` WRITE;
/*!40000 ALTER TABLE `organization_chapters` DISABLE KEYS */;
INSERT INTO `organization_chapters` VALUES (36,5,'ấp Nhơn Phú 1',295,126,126,0,'',1),(37,5,'ấp Nhơn Thuận 1A',394,149,144,5,'',2),(38,5,'ấp Nhơn Ninh',291,217,184,33,'',3),(39,5,'ấp Nhơn Thuận 1B',294,154,115,39,'',4),(40,5,'ấp Nhơn Phú',250,140,140,0,'',5),(41,5,'ấp Nhơn Phú 2',229,115,108,7,'',6),(42,5,'ấp Nhơn Hòa',254,138,130,8,'',7),(43,5,'ấp Nhơn Thọ',199,136,127,9,'',8),(44,5,'ấp Nhơn Thuận 1',314,257,217,40,'',9),(45,5,'ấp Nhơn Thuận',156,117,100,17,'',10),(46,5,'ấp Một Ngàn',239,115,106,9,'',11),(47,5,'ấp Thị Tứ',190,158,117,41,'',12),(48,5,'ấp Tân Lợi',189,177,113,64,'',13),(49,5,'ấp Nhơn Xuân',261,185,168,17,'',14),(50,5,'ấp Tân Thuận',246,147,132,15,'',15),(51,5,'ấp 1A',346,169,159,10,'',16),(52,5,'ấp 2A',334,178,117,61,'',17),(53,5,'ấp 3A',317,180,106,74,'',18),(54,5,'ấp 4A',426,235,143,92,'',19),(55,5,'ấp 1B',265,129,113,16,'',20),(56,5,'ấp 2B',225,136,105,31,'',21),(57,5,'ấp 3B',241,125,103,22,'',22),(58,5,'ấp 4B',168,119,109,10,'',23),(59,5,'ấp 5B',184,144,109,35,'',24),(60,5,'ấp 6B',285,174,154,20,'',25),(61,5,'ấp 2',264,206,158,48,'',26),(62,5,'ấp 3',264,159,97,62,'',27),(63,5,'ấp Bảy Ngàn',216,107,89,18,'',28),(64,5,'ấp 4',254,129,121,8,'',29),(65,5,'ấp 7',225,84,61,23,'',30),(66,5,'ấp 6',246,110,103,7,'',31),(67,5,'ấp 5',132,120,88,32,'',32),(68,4,'Chi hội ấp Nhơn Thuận 1',0,17,17,0,'',1),(69,4,'Chi hội ấp Nhơn Thuận 1A',0,17,17,0,'',2),(70,4,'Chi hội ấp Nhơn Thuận 1B',0,14,14,0,'',3),(71,4,'Chi hội ấp Nhơn Phú',0,12,12,0,'',4),(72,4,'Chi hội ấp Nhơn Phú 1',0,25,22,3,'',5),(73,4,'Chi hội ấp Nhơn Phú 2',0,10,10,0,'',6),(74,4,'Chi hội ấp Nhơn Hòa',0,14,12,2,'',7),(75,4,'Chi hội ấp Nhơn Ninh',0,15,11,4,'',8),(76,4,'Chi hội ấp Nhơn Thọ',0,15,13,2,'',9),(77,4,'Chi hội ấp Nhơn Thuận',0,11,10,1,'',10),(78,4,'Chi hội ấp Tân Lợi',0,12,10,2,'',11),(79,4,'Chi hội ấp Một Ngàn',0,14,13,1,'',12),(80,4,'Chi hội ấp Thị Tứ',0,9,8,1,'',13),(81,4,'Chi hội ấp Nhơn Xuân',0,25,24,1,'',14),(82,4,'Chi hội ấp Tân Thuận',0,29,29,0,'',15),(83,4,'Chi hội ấp 1A',0,13,13,0,'',16),(84,4,'Chi hội ấp 2A',0,16,16,0,'',17),(85,4,'Chi hội ấp 3A',0,7,7,0,'',18),(86,4,'Chi hội ấp 4A',0,14,13,1,'',19),(87,4,'Chi hội ấp 1B',0,5,5,0,'',20),(88,4,'Chi hội ấp 2B',0,9,6,3,'',21),(89,4,'Chi hội ấp 3B',0,19,16,3,'',22),(90,4,'Chi hội ấp 4B',0,6,6,0,'',23),(91,4,'Chi hội ấp 5B',0,13,13,0,'',24),(92,4,'Chi hội ấp 6B',0,12,12,0,'',25),(93,4,'Chi hội ấp 2',0,17,15,2,'',26),(94,4,'Chi hội ấp 3',0,11,11,0,'',27),(95,4,'Chi hội ấp 4',0,20,19,1,'',28),(96,4,'Chi hội ấp 5',0,12,12,0,'',29),(97,4,'Chi hội ấp 6',0,23,23,0,'',30),(98,4,'Chi hội ấp 7',0,20,16,4,'',31),(99,4,'Chi hội ấp Bảy Ngàn',0,25,23,2,'',32),(100,3,'Chi hội phụ nữ Công An',0,5,0,0,'',1),(101,3,'Chi hội Phụ nữ các cơ quan Đảng',0,12,0,0,'',2),(102,3,'Chi hội phụ nữ cơ quan uỷ ban nhân dân',0,19,0,0,'',3),(103,3,'Ấp Thị Tứ',0,220,0,0,'',4),(104,3,'Ấp Nhơn Thuận',0,113,0,0,'',5),(105,3,'Ấp Một Ngàn',0,187,0,0,'',6),(106,3,'Ấp Tân Thuận',0,317,0,0,'',7),(107,3,'Ấp Nhơn Xuân',0,116,0,0,'',8),(108,3,'Ấp Tân Lợi',0,178,0,0,'',9),(109,3,'Ấp 2',0,191,0,0,'',10),(110,3,'Ấp 3',0,143,0,0,'',11),(111,3,'Ấp 4',0,288,0,0,'',12),(112,3,'Ấp 5',0,180,0,0,'',13),(113,3,'Ấp 6',0,211,0,0,'',14),(114,3,'Ấp 7',0,153,0,0,'',15),(115,3,'Ấp Bảy Ngàn',0,256,0,0,'',16),(116,3,'Ấp Nhơn Phú',0,172,0,0,'',17),(117,3,'Ấp Nhơn Phú 1',0,158,0,0,'',18),(118,3,'Ấp Nhơn Phú 2',0,139,0,0,'',19),(119,3,'Ấp Nhơn Thuận 1',0,236,0,0,'',20),(120,3,'Ấp Nhơn Thuận 1A',0,290,0,0,'',21),(121,3,'Ấp Nhơn Thuận 1B',0,211,0,0,'',22),(122,3,'Ấp Nhơn Hòa',0,242,0,0,'',23),(123,3,'Ấp Nhơn Ninh',0,119,0,0,'',24),(124,3,'Ấp nhơn Thọ',0,105,0,0,'',25),(125,3,'Ấp 1A',0,170,0,0,'',26),(126,3,'Ấp 2A',0,135,0,0,'',27),(127,3,'Ấp 3A',0,156,0,0,'',28),(128,3,'Ấp 4A',0,200,0,0,'',29),(129,3,'Ấp 1B',0,102,0,0,'',30),(130,3,'Ấp 2B',0,154,0,0,'',31),(131,3,'Ấp 3B',0,161,0,0,'',32),(132,3,'Ấp 4B',0,205,0,0,'',33),(133,3,'Ấp 5B',0,96,0,0,'',34),(134,3,'Ấp 6B',0,287,0,0,'',35),(135,2,'ấp Nhơn Phú 1',0,0,0,0,'',1),(136,2,'ấp Nhơn Thuận 1A',0,0,0,0,'',2),(137,2,'ấp Nhơn Ninh',0,0,0,0,'',3),(138,2,'ấp Nhơn Thuận 1B',0,0,0,0,'',4),(139,2,'ấp Nhơn Phú',0,0,0,0,'',5),(140,2,'ấp Nhơn Phú 2',0,0,0,0,'',6),(141,2,'ấp Nhơn Hòa',0,0,0,0,'',7),(142,2,'ấp Nhơn Thọ',0,0,0,0,'',8),(143,2,'ấp Nhơn Thuận 1',0,0,0,0,'',9),(144,2,'ấp Nhơn Thuận',0,0,0,0,'',10),(145,2,'ấp Một Ngàn',0,0,0,0,'',11),(146,2,'ấp Thị Tứ',0,0,0,0,'',12),(147,2,'ấp Tân Lợi',0,0,0,0,'',13),(148,2,'ấp Nhơn Xuân',0,0,0,0,'',14),(149,2,'ấp Tân Thuận',0,0,0,0,'',15),(150,2,'ấp 1A',0,0,0,0,'',16),(151,2,'ấp 2A',0,0,0,0,'',17),(152,2,'ấp 3A',0,0,0,0,'',18),(153,2,'ấp 4A',0,0,0,0,'',19),(154,2,'ấp 1B',0,0,0,0,'',20),(155,2,'ấp 2B',0,0,0,0,'',21),(156,2,'ấp 3B',0,0,0,0,'',22),(157,2,'ấp 4B',0,0,0,0,'',23),(158,2,'ấp 5B',0,0,0,0,'',24),(159,2,'ấp 6B',0,0,0,0,'',25),(160,2,'ấp 2',0,0,0,0,'',26),(161,2,'ấp 3',0,0,0,0,'',27),(162,2,'ấp Bảy Ngàn',0,0,0,0,'',28),(163,2,'ấp 4',0,0,0,0,'',29),(164,2,'ấp 7',0,0,0,0,'',30),(165,2,'ấp 6',0,0,0,0,'',31),(166,2,'ấp 5',0,0,0,0,'',32),(167,2,'Công an',12,12,11,1,'',33),(168,2,'Trung tâm Y tế',23,23,7,16,'',34),(169,2,'MG Tuổi Hoa',4,4,0,4,'',35),(170,2,'MG Tuổi Hồng',6,6,0,6,'',36),(171,2,'MG Tuổi Ngọc',4,4,0,4,'',37),(172,2,'MNHD',3,3,0,3,'',38),(173,2,'THPT Châu Thành A',0,0,0,0,'',39),(174,2,'Trung tâm GDTX-GDNN',0,0,0,0,'',40),(175,2,'THCS Tân Hòa',16,16,2,14,'',41),(176,2,'TH Nguyễn Du',4,4,1,3,'',42);
/*!40000 ALTER TABLE `organization_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_leaders`
--

DROP TABLE IF EXISTS `organization_leaders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_leaders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_leaders_unique` (`organization_id`,`full_name`,`position`),
  CONSTRAINT `organization_leaders_organization_id_fk` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_leaders`
--

LOCK TABLES `organization_leaders` WRITE;
/*!40000 ALTER TABLE `organization_leaders` DISABLE KEYS */;
INSERT INTO `organization_leaders` VALUES (1,1,'Phạm Hoàng Hiệp','Uỷ viên BTV Đảng uỷ - Chủ tịch MTTQVN xã','0918944834','mttq.tanhoa@cantho.gov.vn','uploads/avatars/avatar-64d243e9f92b-1779554064.jpg',1),(2,1,'Nguyễn Ngọc Công Hữu','Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Bí thư Đoàn TNCS Hồ Chí Minh xã','0902 222 001','','uploads/avatars/avatar-85a1b9eb41f7-1779554394.jpg',3),(3,1,'Bùi Thị Hồng Thơm','Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Chủ tịch Hội Liên Hiệp Phụ nữ xã','0903 333 001','','img/hoi-phu-nu.png',4),(4,1,'Đặng Minh Thắng','Phó Chủ tịch MTTQVN xã - Chủ tịch Hội Cựu chiến binh','0904 444 001','','img/cuu-chien-binh.jpg',5),(5,1,'Nguyễn Vĩnh Thọ','Uỷ viên BCH Đảng bộ - Phó Chủ tịch TT UBMTTQVN - Chủ tịch Hội Nông dân xã','0905 555 001','','img/hoi-nong-dan.jpg',2),(22,1,'Nguyễn Văn Bằng','Ủy viên - Phó Bí thư Thường trực Đảng ủy, CT. HĐND xã','','','',1),(23,1,'Nguyễn Văn Cần','Ủy viên - Ủy viên BCH Đảng bộ, Phó CHT Ban CHQS xã','','','',2),(24,1,'Nguyễn Thanh Tuấn','Ủy viên - Chủ tịch Hội LHTN Việt Nam xã','','','',3),(25,1,'Võ Văn Chúc','Ủy viên - Chi hội trưởng Hội Cựu CAND xã','','','',4),(26,1,'Hội Chữ thập đỏ','Ủy viên - Đại diện tổ chức thành viên','','','',52),(27,1,'Hội Người cao tuổi','Ủy viên - Đại diện tổ chức thành viên','','','',6),(28,1,'Hội quần chúng khác','Ủy viên - Đại diện tổ chức thành viên','','','',7),(29,1,'Nguyễn Ngọc Thanh','Ủy viên - Trưởng Ban CTMT ấp 3A','','','',8),(30,1,'Phạm Văn Mãnh','Ủy viên - Trưởng Ban CTMT ấp 4A','','','',9),(31,1,'Nguyễn Văn Lượm','Ủy viên - Trưởng Ban CTMT ấp 3B','','','',10),(32,1,'Nguyễn Ngọc Thành','Ủy viên - Trưởng Ban CTMT ấp 4B','','','',11),(33,1,'Phan Thanh Thế','Ủy viên - Trưởng Ban CTMT ấp Nhơn Thuận 1','','','',12),(34,1,'Diệp Em','Ủy viên - Trưởng Ban CTMT ấp Nhơn Phú','','','',13),(35,1,'Nguyễn Văn Diễn','Ủy viên - Trưởng Ban CTMT ấp Nhơn Phú 2','','','',14),(36,1,'Phạm Văn Chiến','Ủy viên - Trưởng Ban CTMT ấp Nhơn Phú 1','','','',15),(37,1,'Nguyễn Văn Bước','Ủy viên - Trưởng Ban CTMT ấp Nhơn Hòa','','','',16),(38,1,'Trương Văn Út','Ủy viên - Trưởng Ban CTMT ấp Nhơn Thọ','','','',17),(39,1,'Tiết Văn Tú','Ủy viên - Trưởng Ban CTMT ấp Nhơn Thuận 1B','','','',18),(40,1,'Trần Thị Lụa','Ủy viên - Trưởng Ban CTMT ấp 1A','','','',19),(41,1,'Đoàn Hùng Hà','Ủy viên - Trưởng Ban CTMT ấp 2A','','','',20),(42,1,'Huỳnh Văn Trưng','Ủy viên - Trưởng Ban CTMT ấp 3A','','','',21),(43,1,'Tạ Quang Tiến','Ủy viên - Trưởng Ban CTMT ấp 4A','','','',22),(44,1,'Lâm Quốc Kỳ','Ủy viên - Trưởng Ban CTMT ấp 1B','','','',23),(45,1,'Nguyễn Thành Tâm','Ủy viên - Trưởng Ban CTMT ấp 2B','','','',24),(46,1,'Bùi Văn Đa','Ủy viên - Trưởng Ban CTMT ấp Thị Tứ','','','',25),(47,1,'Nguyễn Ngọc Dã','Ủy viên - Trưởng Ban CTMT ấp Tân Lợi','','','',26),(48,1,'Võ Thị Hết','Ủy viên - Trưởng Ban CTMT ấp Nhơn Thuận 1A','','','',27),(49,1,'Phạm Văn Hòa','Ủy viên - Trưởng Ban CTMT ấp 1B','','','',28),(50,1,'Trần Văn Mến','Ủy viên - Chủ Doanh nghiệp VLXD Hai Mến ấp 3A','','','',29),(51,1,'Chung Văn Tìm','Ủy viên - Chủ doanh nghiệp VLXD Tìm Ánh ấp 3B','','','',30),(52,1,'Trần Trung Tích','Ủy viên - Chủ Doanh Nghiệp VTNN Trung Tích ấp Nhơn Thuận 1B','','','',31),(53,1,'Trần Kim Đỉnh','Ủy viên - Chủ nhà máy xay lúa ấp 6B','','','',32),(54,1,'Diệp Thị Hiếu','Ủy viên - Đại diện hộ có người thân ở nước ngoài','','','',33),(55,1,'Võ Văn Bảnh','Ủy viên - Đại diện hộ có người thân ở nước ngoài','','','',34),(56,1,'Nguyễn Văn Bé Chính','Ủy viên - Giám đốc HTX nông nghiệp Thành Đạt ấp 1B','','','',35),(57,1,'Trần Việt Mỹ','Ủy viên - Nông dân SXKD giỏi ấp 1B','','','',36),(58,1,'Nguyễn Văn Lại','Ủy viên - Cá nhân tiêu biểu ấp Nhơn Xuân','','','',37),(59,1,'Đoàn Phước Quang','Ủy viên - Cá nhân tiêu biểu ấp 2B','','','',38),(60,1,'Thạch Út','Ủy viên - Đại diện người có uy tín trong đồng bào dân tộc','','','',39),(61,1,'Sơn Tài','Ủy viên - Đại diện người có uy tín trong đồng bào dân tộc','','','',40),(62,1,'Trang Thanh Tâm','Ủy viên - Đại diện người dân tộc Hoa','','','',41),(63,1,'Đoàn Huỳnh Lương','Ủy viên - Trưởng Ban Trị sự Giáo hội Phật giáo Hòa Hảo, xã Tân Hòa','','','',42),(64,1,'Huỳnh Văn Chánh','Ủy viên - Hội quán Hưng Nghĩa Tự ấp Thị Tứ','','','',43),(65,1,'Huỳnh Văn Cho','Ủy viên - Hội quán Hưng Thiện Tự ấp 1A','','','',44),(66,1,'Huỳnh Phượng Uyên','Ủy viên - Trưởng ban trị sự Phật giáo hòa hảo TT Bảy Ngàn','','','',45),(67,1,'Ni sư Thích Nữ Nguyên Hoa (Bà Văn Thị Lệ)','Ủy viên - Trụ trì Chùa Bửu Tường, Thị trấn Bảy Ngàn','','','',46),(68,1,'Nguyễn Văn Đức','Ủy viên - Chủ tịch Hội đồng Mục vụ Nhà thờ Bảy Ngàn','','','',47),(69,1,'Nguyễn Phước Trung','Ủy viên - Ủy viên BCH Đảng bộ, Hiệu trưởng - Trường THCS Tân Hòa','','','',48),(70,1,'Huỳnh Văn Phùng','Ủy viên - Phó Hiệu trưởng Trường TH Nguyễn Du','','','',49),(71,1,'Trần Thành Hưởng','Ủy viên - Chuyên viên TT phục vụ hành chính công','','','',50),(72,1,'Nguyễn Văn Vũ','Ủy viên - Giám đốc Phòng giao dịch NHCSXH Châu Thành A','','','',51),(73,1,'Nguyễn Chí Trung','Ủy viên - Chuyên viên văn phòng Ủy ban MTTQ Việt Nam xã','','','',5),(76,5,'Nguyễn Vĩnh Thọ','Uỷ viên BCH Đảng bộ - Phó Chủ tịch TT UBMTTQVN - Chủ tịch Hội Nông dân xã','0905 555 001','','img/hoi-nong-dan.jpg',1),(77,5,'Trương Sĩ Nguyên','Phó Chủ tịch Hội Nông dân','','','',2),(78,5,'Lê Thị Thùy Dung','Chuyên viên UB MTTQ VN xã','','','',5),(79,5,'Nguyễn Văn Vũ','PGĐNHCSXH Châu Thành A','','','',4),(80,5,'Tăng Văn Đủ','Phòng kinh tế','','','',5),(81,5,'Đỗ Văn Hải','Trạm KN Châu Thành A,\nthuộc TTKN-DV TP Cần Thơ','','','',6),(82,5,'Bùi Hữu Như','Phòng văn hóa','','','',7),(83,5,'Nguyễn Văn Bé Chín','Giám đốc HTX NN Thành Đạt\nấp 1B','','','',8),(84,5,'Lê Thị Ngọc Dình','CHT nông dân ấp 1B','','','',9),(85,5,'Đặng Thị Ngọc Đào','Chủ cơ sở OCOP sữa dê\nNgọc Đào','','','',10),(86,5,'Tạ Thị Tuyết Em','CHT nông dân ấp 4A','','','',11),(87,5,'Dương Văn Hải','CHT nông dân ấp Nhơn Xuân','','','',12),(88,5,'Nguyễn Thanh Hùng','CHT nông dân ấp\nNhơn Thuận 1A','','','',13),(89,5,'Trần Hồng Nhiên','Chủ cơ sở OCOP Cafe dừa,\ntắc mật ong','','','',14),(90,5,'Bạch Thị Nương','CHT nông dân ấp 3','','','',15),(91,5,'Nguyễn Thành Tâm','CHT nông dân ấp Tân Lợi','','','',16),(92,5,'Lê Thanh Tân','CHT nông dân ấp 5','','','',17),(93,5,'Nguyễn Văn Út','Nông dân sản xuất kinh doanh\nsản xuất kinh doanh giỏi ấp Nhơn Phú','','','',18),(94,5,'Nguyễn Duy Khoa','CV.UBMTTQ xã','','','',19),(95,4,'Đặng Minh Thắng','Phó Chủ tịch MTTQVN xã - Chủ tịch Hội Cựu chiến binh','0904 444 001','','img/cuu-chien-binh.jpg',1),(96,4,'Hồ Hùng Hiệp','Phó Chủ tịch Hội Cựu chiến binh','','','',2),(97,4,'Đoàn Thanh Điền','Chuyên viên UB MTTQ VN xã','','','',3),(98,4,'Trần Văn Coi','Chi hội trưởng','','','',4),(99,4,'Nguyễn Thu Hà','Chi hội trưởng','','','',5),(100,4,'Trần Văn Dệt','Chi hội trưởng','','','',6),(101,4,'Huỳnh Văn Giỏi','Chi hội trưởng','','','',7),(102,4,'Nguyễn Văn Để','Chi hội trưởng','','','',8),(103,4,'Đoàn Hùng Hà','Chi hội trưởng','','','',9),(104,4,'Nguyễn Văn Hòa','Chi hội trưởng','','','',10),(105,4,'Đinh Văn Nhớ','Chi hội trưởng','','','',11),(106,4,'Nguyễn Văn Sáng','Chi hội trưởng','','','',12),(107,4,'Lê Thành Tâm','Chi hội trưởng','','','',13),(108,4,'Lê Văn Thương','Chi hội trưởng','','','',14),(109,4,'Bùi Văn Vui','Chi hội trưởng','','','',15),(110,3,'Bùi Thị Hồng Thơm','Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Chủ tịch Hội Liên Hiệp Phụ nữ xã','0903 333 001','','img/hoi-phu-nu.png',1),(111,3,'Vũ Phạm Lanh','Phó Chủ tịch Hội Liên Hiệp Phụ nữ xã','','','',2),(112,3,'Trương Thị Kim Thúy','Chuyên viên UBMTTQ VN','','','',3),(113,3,'Huỳnh Thị Hằng','Cán bộ không chuyên trách Hội LHPN xã','','','',4),(114,3,'Nguyễn Thị Thúy Hằng','Phó chánh văn phòng','','','',5),(115,3,'Nguyễn Thị Ngọc Nữ','P. Hiệu trưởng','','','',6),(116,3,'Trần Thị Tuyết Phương','Giáo viên','','','',7),(117,3,'Nguyễn Thị Thanh Phương','Chi hội PN','','','',8),(118,3,'Trần Thị Minh Châu','Phó Giám đốc','','','',9),(119,3,'Lê Thị Thùy Dung','Chuyên viên Uỷ ban MTTQ','','','',10),(120,3,'Nguyễn Thị Hồng Thoa','Chuyên viên UB MTTQ VN xã','','','',11),(121,3,'Lê Thị Mỹ Khánh','Phó Chủ nhiệm','','','',12),(122,3,'Nguyễn Thị Tính','Phó hiệu trưởng','','','',13),(123,3,'Phạm Thị Huỳnh Trinh','Giáo viên','','','',14),(124,3,'Nguyễn Thị Hồng Mai','Hiệu trưởng','','','',15),(125,3,'Bùi Thị Ngọc Sương','Phó hiệu trưởng','','','',16),(126,3,'Lê Thị Sơn Ca','Phó hiệu trưởng trường','','','',17),(127,3,'Nguyễn Thị Ngoan','Chi hội Trưởng','','','',18),(128,3,'Nguyễn Thu Hà','Chi hội Trưởng','','','',19),(129,3,'Dương Thị Hiệp','Chi hội Trưởng','','','',20),(130,3,'Trương Thị Liên Hoa','Chi hội Trưởng','','','',21),(131,3,'Nguyễn Thị Mộng Tuyền','Chi hội Trưởng','','','',22),(132,3,'Lê Hồng Ngự','Chi hội Trưởng','','','',23),(133,3,'Diệp Thị Hiếu','Chi hội Trưởng','','','',24),(134,3,'Trần Thị Ngọc Giàu','Chi hội Trưởng','','','',25),(136,2,'Nguyễn Thanh Tuấn','Phó Bí thư Đoàn TNCS Hồ Chí Minh xã','','','',2),(137,2,'Trần Lê Như Ngọc','Văn phòng HĐND - UBND','','','',4),(138,2,'Nguyễn Chí Trung','Chuyên viên UB MTTQ VN xã','','','',3),(139,2,'Nguyễn Hoàng Nhi','BTCĐ Công an','','','',6),(140,2,'Trần Thị Bạch Tuyết','BTCĐ Trường\nTHPT CTA','','','',7),(141,2,'Trần Thị Thu Thảo','BTCĐ Trường THCS Tân Hòa','','','',8),(142,2,'Dương Kim Đon','BTCĐ Trường\nMG Tuổi Hồng','','','',9),(143,2,'Nguyễn Thị Ngọc Lành','BTCĐ Trường\nMG Tuổi Ngọc','','','',10),(144,2,'Danh Thanh Sang','P.BTCĐ Trường\nTHPT CTA','','','',11),(145,2,'Trần Thị Hải An','BTCĐ TTYT','','','',12),(146,2,'Tiết Thị Cẩm Trúc','BTCĐ Tân Lợi','','','',13),(147,2,'Đặng Hoàng Dũng','Đoàn viên','','','',14),(148,2,'Nguyễn Hữu Kiệt','CB TT phục vụ\nhành chính công','','','',15),(149,2,'Phan Thanh Diễm','BTCĐ ấp Một Ngàn','','','',16),(150,2,'Trà Ngọc Thắm','BTCĐ ấp Thị Tứ\n(Một Ngàn cũ)','','','',17),(151,2,'Phan Hoàng Kha','BTCĐ','','','',18),(152,2,'Nguyễn Thị Phượng Trân','BTCĐ','','','',19),(153,2,'Nguyễn Quang Nhựt','BTCĐ ấp 2A\n(Tân Hòa cũ)','','','',20),(154,2,'Huỳnh Văn Giỏi','BTCĐ ấp IB\n(Tân Hòa cũ)','','','',21),(156,2,'Nguyễn Ngọc Công Hữu','Uỷ viên BCH Đảng bộ - Phó Chủ tịch UBMTTQVN - Bí thư Đoàn TNCS Hồ Chí Minh xã','0902 222 001','','uploads/avatars/avatar-85a1b9eb41f7-1779554394.jpg',1);
/*!40000 ALTER TABLE `organization_leaders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,'Ủy ban Mặt trận Tổ quốc Việt Nam xã Tân Hòa','MTTQVN','mttq-viet-nam-xa-tan-hoa','Cơ quan liên minh chính trị, liên hiệp tự nguyện của các tổ chức chính trị - xã hội và các tầng lớp nhân dân tại xã Tân Hòa.',1,'2026-05-23 09:04:51'),(2,'Đoàn Thanh niên Cộng sản Hồ Chí Minh xã Tân Hòa','Đoàn TN','doan-thanh-nien','Tổ chức phụ trách công tác thanh niên, phong trào xung kích, tình nguyện, chuyển đổi số cộng đồng và giáo dục lý tưởng cách mạng.',3,'2026-05-23 09:04:51'),(3,'Hội Liên hiệp Phụ nữ xã Tân Hòa','Hội Phụ nữ','hoi-lien-hiep-phu-nu','Tổ chức đại diện chăm lo quyền, lợi ích hợp pháp của phụ nữ, xây dựng gia đình no ấm, tiến bộ, hạnh phúc, văn minh.',4,'2026-05-23 09:04:51'),(4,'Hội Cựu chiến binh xã Tân Hòa','Hội CCB','hoi-cuu-chien-binh','Tổ chức tập hợp cựu chiến binh, phát huy phẩm chất Bộ đội Cụ Hồ, tham gia xây dựng Đảng, chính quyền và giáo dục truyền thống.',5,'2026-05-23 09:04:51'),(5,'Hội Nông dân xã Tân Hòa','Hội ND','hoi-nong-dan','Tổ chức đại diện giai cấp nông dân, hỗ trợ hội viên phát triển sản xuất, xây dựng nông thôn mới và liên kết tiêu thụ nông sản.',2,'2026-05-23 09:04:51');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_images`
--

DROP TABLE IF EXISTS `post_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_images_path_unique` (`image_path`),
  KEY `post_images_post_index` (`post_id`,`sort_order`),
  CONSTRAINT `post_images_post_id_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_images`
--

LOCK TABLES `post_images` WRITE;
/*!40000 ALTER TABLE `post_images` DISABLE KEYS */;
INSERT INTO `post_images` VALUES (1,1,'img/about.jpg','Hình ảnh minh họa: MTTQ xã Tân Hòa tổ chức Ngày hội Đại đoàn kết toàn dân tộc',1,'2026-06-02 12:30:38'),(2,2,'img/background.png','Hình ảnh minh họa: MTTQ xã Tân Hòa triển khai hoạt động chào mừng tháng cao điểm vì người nghèo',1,'2026-06-02 12:30:38'),(3,3,'img/background-trangconlai.png','Hình ảnh minh họa: Các hội đoàn thể phối hợp tuyên truyền chuyển đổi số cộng đồng',1,'2026-06-02 12:30:38'),(4,4,'img/nen-pho-ct.png','Hình ảnh minh họa: Hội Phụ nữ xã ra quân chăm lo tuyến đường xanh sạch đẹp',1,'2026-06-02 12:30:38'),(5,5,'img/background-chantrang.png','Hình ảnh minh họa: Hội Nông dân hỗ trợ hội viên tiếp cận nguồn vốn sản xuất',1,'2026-06-02 12:30:38'),(6,6,'img/logo-mttq.png','Hình ảnh minh họa: Cựu chiến binh xã tham gia giữ gìn an ninh trật tự cơ sở',1,'2026-06-02 12:30:38'),(7,1,'uploads/posts/post-9a48ef1f6d8b-1780403563.jpg','',2,'2026-06-02 12:32:44');
/*!40000 ALTER TABLE `post_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` enum('draft','published','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `views` bigint unsigned NOT NULL DEFAULT '0',
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_status_published_index` (`status`,`published_at`),
  KEY `posts_featured_index` (`is_featured`,`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'MTTQ xã Tân Hòa tổ chức Ngày hội Đại đoàn kết toàn dân tộc','mttq-xa-tan-hoa-to-chuc-ngay-hoi-dai-doan-ket-toan-dan-toc','Ủy ban MTTQ Việt Nam xã Tân Hòa tổ chức Ngày hội Đại đoàn kết toàn dân tộc nhằm phát huy tinh thần đoàn kết, gắn bó trong cộng đồng dân cư, góp phần xây dựng quê hương ngày càng văn minh, giàu đẹp.','<p>Sáng ngày …/…/2026, Ủy ban MTTQ Việt Nam xã Tân Hòa tổ chức Ngày hội Đại đoàn kết toàn dân tộc tại địa phương với sự tham dự của lãnh đạo xã, đại diện các ban ngành, đoàn thể cùng đông đảo bà con nhân dân.</p><p>Ngày hội là dịp để ôn lại truyền thống vẻ vang của Mặt trận Tổ quốc Việt Nam, đồng thời đánh giá kết quả thực hiện các phong trào thi đua yêu nước, các cuộc vận động tại khu dân cư trong thời gian qua.</p><p>Tại chương trình, các đại biểu và nhân dân đã cùng nhau nhìn lại những kết quả nổi bật trong xây dựng đời sống văn hóa, giữ gìn an ninh trật tự, bảo vệ môi trường, chăm lo đời sống nhân dân và phát huy tinh thần tương thân tương ái trong cộng đồng.</p><p>Phát biểu tại ngày hội, đại diện lãnh đạo địa phương ghi nhận và biểu dương những nỗ lực của cán bộ, đảng viên và nhân dân trong việc xây dựng khối đại đoàn kết toàn dân. Đồng thời mong muốn bà con tiếp tục phát huy tinh thần đoàn kết, tích cực tham gia các phong trào thi đua, góp phần xây dựng xã Tân Hòa ngày càng phát triển.</p><p>Nhân dịp này, địa phương cũng đã trao tặng các phần quà ý nghĩa cho những hộ gia đình có hoàn cảnh khó khăn, gia đình chính sách và các cá nhân tiêu biểu trong phong trào thi đua tại khu dân cư.</p><p>Ngày hội diễn ra trong không khí vui tươi, phấn khởi, tạo sự gắn kết giữa chính quyền, Mặt trận, các đoàn thể và nhân dân, góp phần củng cố niềm tin, phát huy sức mạnh đại đoàn kết toàn dân tộc.</p>\n\n<figure><img src=\"/img/about.jpg\" alt=\"Ảnh minh họa trong nội dung bài đăng\"><figcaption>Ảnh được chèn trực tiếp trong nội dung bài đăng.</figcaption></figure>','uploads/posts/post-d54feeb8f432-1780402847.png','uploads/posts/post-d54feeb8f432-1780402847.png','published',0,11,'2026-06-02 12:18:00','','',NULL,NULL,'2026-06-02 12:20:47','2026-06-03 13:00:59'),(2,'MTTQ xã Tân Hòa triển khai hoạt động chào mừng tháng cao điểm vì người nghèo','mttq-xa-tan-hoa-trien-khai-hoat-dong-chao-mung-thang-cao-diem-vi-nguoi-ngheo','Ủy ban MTTQ Việt Nam xã Tân Hòa phối hợp các tổ chức thành viên rà soát, hỗ trợ các hộ còn khó khăn trên địa bàn.','<p>Sáng nay, Ủy ban MTTQ Việt Nam xã Tân Hòa tổ chức họp triển khai hoạt động chào mừng tháng cao điểm vì người nghèo. Nội dung tập trung vào công tác rà soát hộ khó khăn, vận động nguồn lực xã hội hóa và phối hợp các đoàn thể trong từng ấp.<br>\r\n<br>\r\nCác tổ chức thành viên thống nhất tăng cường tuyên truyền, nắm chắc nhu cầu thực tế của người dân và ưu tiên những trường hợp cần hỗ trợ kịp thời.</p>','img/background-trangchu.png','img/background-trangchu.png','published',0,1,'2026-06-02 08:00:00','','',NULL,NULL,'2026-06-02 12:27:01','2026-06-03 07:36:39'),(3,'Các hội đoàn thể phối hợp tuyên truyền chuyển đổi số cộng đồng','cac-hoi-doan-the-phoi-hop-tuyen-truyen-chuyen-doi-so-cong-dong','Đoàn Thanh niên, Hội Phụ nữ, Hội Nông dân và Hội Cựu chiến binh cùng tham gia hướng dẫn người dân sử dụng dịch vụ công trực tuyến.','Hoạt động tuyên truyền chuyển đổi số cộng đồng được triển khai tại các ấp với sự tham gia của nhiều lực lượng. Nội dung hướng dẫn gồm tạo tài khoản dịch vụ công, tra cứu thủ tục hành chính và tiếp cận thông tin chính thống trên môi trường số.','img/nen-ct.png','img/nen-ct.png','published',0,0,'2026-06-01 15:30:00','','',NULL,NULL,'2026-06-02 12:27:01','2026-06-03 07:27:42'),(4,'Hội Phụ nữ xã ra quân chăm lo tuyến đường xanh sạch đẹp','hoi-phu-nu-xa-ra-quan-cham-lo-tuyen-duong-xanh-sach-dep','Các chi hội phụ nữ vận động hội viên dọn vệ sinh, trồng hoa và giữ gìn cảnh quan khu dân cư.','Hội Liên hiệp Phụ nữ xã Tân Hòa tổ chức ra quân vệ sinh môi trường, trồng hoa ven đường và vận động hộ dân cùng chăm sóc cảnh quan. Hoạt động góp phần xây dựng nếp sống văn minh, xanh sạch đẹp tại địa phương.','img/hoi-phu-nu.png','img/hoi-phu-nu.png','published',0,4,'2026-05-31 09:15:00','','',NULL,NULL,'2026-06-02 12:27:01','2026-06-03 08:42:39'),(5,'Hội Nông dân hỗ trợ hội viên tiếp cận nguồn vốn sản xuất','hoi-nong-dan-ho-tro-hoi-vien-tiep-can-nguon-von-san-xuat','Các tổ vay vốn tiếp tục rà soát nhu cầu vay, hướng dẫn hồ sơ và theo dõi mục đích sử dụng vốn.','Hội Nông dân xã phối hợp các tổ vay vốn nắm tình hình sản xuất của hội viên, hướng dẫn hoàn thiện hồ sơ vay và nhắc nhở sử dụng vốn đúng mục đích. Công tác quản lý được thực hiện công khai, chặt chẽ theo từng tổ.','img/hoi-nong-dan.jpg','img/hoi-nong-dan.jpg','published',0,0,'2026-05-30 14:00:00','','',NULL,NULL,'2026-06-02 12:27:01','2026-06-03 07:27:42'),(6,'Cựu chiến binh xã tham gia giữ gìn an ninh trật tự cơ sở','cuu-chien-binh-xa-tham-gia-giu-gin-an-ninh-trat-tu-co-so','Hội Cựu chiến binh phát huy vai trò gương mẫu trong tuyên truyền, vận động nhân dân chấp hành quy định tại địa phương.','Hội Cựu chiến binh xã Tân Hòa tiếp tục phối hợp các lực lượng ở ấp tuyên truyền giữ gìn an ninh trật tự, phòng chống tệ nạn xã hội và phát huy tinh thần trách nhiệm trong cộng đồng dân cư.','img/cuu-chien-binh.jpg','img/cuu-chien-binh.jpg','published',0,2,'2026-05-29 10:00:00','','',NULL,NULL,'2026-06-02 12:27:01','2026-06-03 07:39:20'),(7,'Demo copy định dạng từ Word','demo-copy-dinh-dang-tu-word','Bài demo kiểm tra copy định dạng từ Word và ảnh giữ kích thước ban đầu.','<p style=\"font-family: Times New Roman; font-size: 14pt; color: #1f497d; text-align: center\"><strong>Tiêu đề copy từ Word</strong></p><p style=\"font-family: Times New Roman; font-size: 12pt; color: #333333\"><em>Nội dung in nghiêng</em>, <u>gạch chân</u> và giữ màu chữ.</p><table style=\"border: 1px solid #999999\"><tbody><tr><th style=\"background-color: #eeeeee; border: 1px solid #999999\">Nội dung</th><th style=\"background-color: #eeeeee; border: 1px solid #999999\">Ghi chú</th></tr><tr><td style=\"border: 1px solid #999999\">Bảng từ Word</td><td style=\"border: 1px solid #999999\">Được giữ định dạng</td></tr></tbody></table><figure><img src=\"/img/logo-mttq.png\" alt=\"Logo trong nội dung\" width=\"120\" height=\"120\"><figcaption>Ảnh giữ kích thước gốc nếu vừa khung.</figcaption></figure>','img/logo-mttq.png','img/logo-mttq.png','published',0,6,'2026-06-02 12:41:21','','',NULL,NULL,'2026-06-02 12:41:21','2026-06-03 08:33:53'),(8,'Chúc tết các cụ cao tuổi','chuc-tet-cac-cu-cao-tuoi','','<p style=\"text-align: center\"><span>Chúc tết các cụ cao tuổi</span></p><p><span>Nhân dịp Tết Nguyên đán Bính Ngọ 2026, sáng ngày 11/2, ông\r\nLê Hoàng Xuyên - Bí thư Đảng ủy xã Tân Hòa đã đến thăm, tặng quà Tết và chúc\r\nthọ cụ tròn 100 tuổi và trên 100 tuổi hiện đang thường trú tại xã. Cùng đi có\r\nông Bùi Công Mến-phó chủ tịch thường trực UBND xã.</span></p><p><span>Tại buổi thăm, ông Lê Hoàng Xuyên đã trân trọng trao quà\r\nmừng thọ của Chủ tịch nước cùng nhiều phần quà ý nghĩa của các cấp, các ngành\r\ngửi tặng cụ Phạm Văn Khâu tròn 100 tuổi. Trao giấy mừng thọ cho cụ Trần Văn\r\nPhường, 103 tuổi, </span></p><p><span>Tại mỗi nơi đến đại diện đoàn, ông Lê Hoàng Xuyên đã chúc\r\ncác cụ luôn dồi dào sức khỏe, tinh thần minh mẫn, sống lâu, sống vui, sống\r\ntrường thọ, tiếp tục là chỗ dựa tinh thần vững chắc cho con cháu trong gia\r\nđình, góp phần giáo dục truyền thống, nêu gương sáng cho thế hệ trẻ noi theo. </span></p><p><span></span></p><figure><figcaption></figcaption></figure>Việc tổ chức thăm hỏi, chúc thọ người cao tuổi nhân dịp\r\nTết Nguyên đán là hoạt động thường niên mang ý nghĩa nhân văn sâu sắc, thể hiện\r\nsự quan tâm của Đảng, Nhà nước và chính quyền địa phương đối với các bậc cao\r\nniên – những người đã có nhiều đóng góp cho gia đình và xã hội qua các thời kỳ.\r\nĐây cũng là dịp để tiếp tục phát huy truyền thống kính lão, trọng thọ của dân\r\ntộc, góp phần giáo dục thế hệ trẻ về đạo lý “uống nước nhớ nguồn”, “kính già,\r\nyêu trẻ”.<p></p><p style=\"text-align: justify\"><span>Dịp này, đoàn đến chúc tết Trung tâm y tế khu\r\nvực Châu Thành A,</span><span> biểu\r\ndương những nỗ lực, cố gắng của tập thể Trung tâm Y tế trong công tác khám, chữa\r\nbệnh và phòng, chống dịch bệnh thời gian qua. Nhân dịp năm mới, bí thư Đảng đã\r\ngửi lời thăm hỏi, động viên và chúc Tết đến toàn thể viên chức, người lao động\r\ncủa Trung tâm, chúc tập thể Trung tâm tiếp tục đoàn kết, hoàn thành tốt nhiệm vụ\r\nđược giao.</span></p><figure><img src=\"/uploads/posts/post-inline-6b6ac466069c-1780404639.png\" alt=\"\" width=\"936\" height=\"528\"><figcaption></figcaption></figure><div><br><p style=\"text-align: right\">HỮU TOÀN</p><figure><figcaption></figcaption></figure><figure><figcaption></figcaption></figure><p style=\"text-align: right\"><span>&nbsp;</span></p><p style=\"text-align: right\"><span></span><span></span></p><p style=\"text-align: right\"><span>Trao giấy mừng thọ cho cụ Trần Văn Phường,\r\n103 tuổi,</span></p><p style=\"text-align: right\"><span></span><span></span></p><p style=\"text-align: right\"><span>Đại diện gia đình cụ Phạm Văn Khâu tròn 100\r\ntuổi, nhận giấy mừng thọ chủ tịch nước<br></span></p><p style=\"text-align: right\"><span></span></p><p style=\"text-align: right\"><span></span><span></span></p></div>','','','published',0,2,'2026-06-02 12:40:00','','',NULL,NULL,'2026-06-02 12:41:36','2026-06-03 07:35:48');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mttq_tanhoa'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-06 21:05:35
