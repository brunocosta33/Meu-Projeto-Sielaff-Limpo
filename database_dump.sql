-- MySQL dump 10.13  Distrib 8.0.41, for macos15.2 (arm64)
--
-- Host: localhost    Database: sielaffapp
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `tipo_servico` varchar(255) DEFAULT NULL,
  `status` enum('Agendado','Concluído','Cancelado') DEFAULT 'Agendado',
  `observacoes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `logistica_team_id` bigint unsigned DEFAULT NULL,
  `tecnica_team_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `fk_appointments_supplier_id` (`supplier_id`),
  CONSTRAINT `fk_appointments_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=315 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (86,2,1,'2025-04-01','08:00:00','Entrega','Concluído','Módulo 4 amassado lado direito','2025-07-30 10:30:37','2025-07-30 13:52:55',NULL,NULL,NULL),(87,3,1,'2025-04-01','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(88,4,1,'2025-08-08','16:00:00','Levantamento','Agendado',NULL,'2025-07-30 10:30:37','2025-07-30 13:21:46',NULL,NULL,NULL),(89,5,1,'2025-04-08','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(90,6,1,'2025-04-10','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(91,7,1,'2025-04-15','08:00:00','Entrega','Concluído','Porta Superior direita do Módulo 1 Amassada, Porta inferior direita do módulo 2 amassada','2025-07-30 10:30:37','2025-07-30 13:52:15',NULL,NULL,NULL),(92,8,1,'2025-04-17','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(93,9,1,'2025-04-22','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(94,10,1,'2025-04-23','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(95,11,1,'2025-04-24','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(96,12,1,'2025-04-29','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(97,13,1,'2025-04-30','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(98,14,1,'2025-05-02','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(99,15,1,'2025-05-06','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(100,16,1,'2025-05-07','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(101,17,1,'2025-05-08','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(102,18,1,'2025-05-13','08:00:00','Entrega','Concluído','Porta Módulo 2 inferior danificada','2025-07-30 10:30:37','2025-07-30 13:57:32',NULL,NULL,NULL),(103,19,1,'2025-05-14','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(104,20,1,'2025-05-15','08:00:00','Entrega','Concluído','Porta inferior do módulo 3 amolgada','2025-07-30 10:30:37','2025-07-30 13:58:09',NULL,NULL,NULL),(105,21,1,'2025-05-20','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(106,22,1,'2025-05-21','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(107,23,1,'2025-05-22','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(108,24,1,'2025-05-27','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(109,25,1,'2025-05-28','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(110,26,1,'2025-05-29','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:30:37','2025-07-30 10:30:37',NULL,NULL,NULL),(187,27,1,'2025-06-03','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(188,28,1,'2025-06-04','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(189,29,1,'2025-06-05','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(190,30,1,'2025-06-11','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(191,31,1,'2025-06-12','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(192,32,1,'2025-06-13','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(193,33,1,'2025-06-17','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(194,34,1,'2025-06-18','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(195,35,1,'2025-06-20','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(196,36,1,'2025-06-25','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(197,37,1,'2025-06-26','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:35:13','2025-07-30 10:35:13',NULL,NULL,NULL),(198,38,1,'2025-06-27','08:00:00','Entrega','Concluído','Módulo 3 está amassado','2025-07-30 10:35:13','2025-07-30 13:53:40',NULL,NULL,NULL),(252,58,1,'2025-08-05','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(253,59,1,'2025-08-06','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(254,60,1,'2025-08-07','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(255,61,1,'2025-08-12','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(256,62,1,'2025-08-13','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(257,63,1,'2025-08-14','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(258,64,1,'2025-08-19','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(259,65,1,'2025-08-20','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(260,66,1,'2025-08-21','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(261,67,1,'2025-08-26','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(262,68,1,'2025-09-02','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(263,41,1,'2025-09-03','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 10:36:26',NULL,NULL,NULL),(264,69,1,'2025-09-04','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:26','2025-07-30 14:06:24',NULL,NULL,NULL),(279,53,1,'2025-07-24','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:36:59','2025-07-30 09:54:35',NULL,NULL,NULL),(280,54,1,'2025-07-25','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:36:59','2025-07-30 09:53:55',NULL,NULL,NULL),(281,55,1,'2025-07-29','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:36:59','2025-07-30 09:53:49',NULL,NULL,NULL),(282,56,1,'2025-07-30','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:36:59','2025-07-30 13:50:21',NULL,NULL,NULL),(283,57,1,'2025-07-31','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:59','2025-07-30 10:36:59',NULL,NULL,NULL),(284,70,1,'2025-09-04','08:00:00','Entrega','Agendado',NULL,'2025-07-30 10:36:59','2025-07-30 10:36:59',NULL,NULL,NULL),(294,39,1,'2025-07-01','08:00:00','Entrega','Concluído','Placa de Proteção traseira danificada','2025-07-30 10:37:23','2025-07-30 13:54:34',NULL,NULL,NULL),(295,40,1,'2025-07-02','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:23','2025-07-30 09:54:13',NULL,NULL,NULL),(296,41,1,'2025-07-03','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:23','2025-07-30 09:56:15',NULL,NULL,NULL),(297,42,1,'2025-07-03','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:23','2025-07-30 09:54:21',NULL,NULL,NULL),(298,43,1,'2025-07-08','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:23','2025-07-30 09:56:07',NULL,NULL,NULL),(304,44,1,'2025-07-09','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:46','2025-07-30 09:56:01',NULL,NULL,NULL),(305,45,1,'2025-07-10','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:46','2025-07-30 09:55:54',NULL,NULL,NULL),(306,46,1,'2025-07-11','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:46','2025-07-30 09:55:46',NULL,NULL,NULL),(307,47,1,'2025-07-15','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:37:46','2025-07-30 09:55:36',NULL,NULL,NULL),(308,48,1,'2025-07-16','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:38:10','2025-07-30 09:55:29',NULL,NULL,NULL),(309,49,1,'2025-07-17','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:38:10','2025-07-30 09:55:20',NULL,NULL,NULL),(310,50,1,'2025-07-18','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:38:10','2025-07-30 09:54:58',NULL,NULL,NULL),(311,52,1,'2025-07-23','08:00:00','Entrega','Concluído',NULL,'2025-07-30 10:38:38','2025-07-30 09:54:50',NULL,NULL,NULL),(312,1,1,'2025-07-22','08:00:00','Entrega','Concluído','Porta Módulo 2 Amolgada, friso riscado do frame','2025-07-30 09:40:56','2025-07-30 13:56:00',NULL,NULL,NULL),(313,1,1,'2025-10-31','08:00:00','Entrega','Agendado',NULL,'2025-07-30 12:37:35','2025-07-30 12:37:49','2025-07-30 12:37:49',NULL,NULL),(314,1,1,'2025-10-03','08:00:00','Entrega','Agendado',NULL,'2025-07-30 12:40:31','2025-07-30 12:45:40','2025-07-30 12:45:40',NULL,NULL);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title_pt` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `image_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `slug` longtext,
  `sort` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (15,'Sobre nos','About Us',153,'2024-03-03 11:22:23','2024-03-03 11:42:40','2024-03-03 11:43:38',1,'sobre-nos',NULL),(16,'Lara','Lara',155,'2024-03-03 11:43:12','2024-03-03 11:43:22','2024-03-03 11:43:33',1,'lara',NULL),(17,'Sobre','About',278,'2024-03-03 11:48:42','2024-03-18 16:37:33',NULL,1,'sobre',1),(18,'Supermecado','Market',257,'2024-03-06 00:39:43','2024-03-18 16:36:47',NULL,0,'supermecado',2),(19,'bgtb','gbtb',195,'2024-03-06 09:37:34','2024-03-06 09:37:34','2024-03-06 09:37:41',1,'bgtb',NULL),(20,'rgerggerg4tg4','gee',197,'2024-03-06 09:37:51','2024-03-06 09:38:01','2024-03-06 09:38:08',1,'rgerg',NULL),(21,'tgtdgd','gfdg',207,'2024-03-06 15:43:02','2024-03-06 15:43:21','2024-03-06 15:44:47',1,'tgt',NULL),(22,'vdfvxvvvv','vcxvcvvc',209,'2024-03-06 15:44:25','2024-03-06 15:44:35','2024-03-06 15:44:43',1,'vdf',NULL),(23,'Natal','Christmas',256,'2024-03-06 16:17:14','2024-03-08 11:42:31','2024-03-11 10:54:56',1,'christmas',3),(24,'xsaxasxax','xsaxa',229,'2024-03-07 10:03:04','2024-03-07 10:03:14','2024-03-07 10:13:02',1,'xsax',NULL),(25,'Teste','dwxwc',259,'2024-03-08 11:40:35','2024-03-08 11:42:18','2024-03-11 10:54:59',1,'teste',4),(26,'teste2','sas',260,'2024-03-08 11:42:45','2024-03-08 11:42:45','2024-03-08 11:50:20',1,'teste2',4),(27,'teste 2','dccwc',261,'2024-03-08 11:50:35','2024-03-08 11:50:35','2024-03-08 11:50:46',1,'teste-2',5),(28,'gfg','bvb',301,'2024-03-18 11:57:11','2024-03-18 11:57:17','2024-03-18 11:57:57',1,'gfg',3),(29,'Teste123','teste123',304,'2024-03-18 12:13:44','2024-03-18 12:16:24','2024-03-18 12:17:27',1,'teste123',3),(30,'fgfrgdf','fggdf',NULL,'2024-03-18 12:16:35','2024-03-18 12:16:35','2024-03-18 12:17:24',1,'fgfrgdf',4),(31,'fgfrgdf','fggdf',306,'2024-03-18 12:17:13','2024-03-18 12:17:13','2024-03-18 12:17:20',1,'fgfrgdf-1',5),(32,'fff','fff',308,'2024-03-18 12:17:38','2024-03-18 12:17:49','2024-03-18 12:17:57',0,'fff',3);
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title_pt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_id` bigint DEFAULT NULL,
  `slug` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `category_id` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (2,'Coca Cola 1','Coca Cola 1','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',246,'coca-cola-1','2024-03-07 11:29:15','2024-03-07 13:15:12',NULL,1,10),(3,'Coca Cola 2','Coca Cola  2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',247,'coca-cola-2','2024-03-07 11:29:51','2024-03-08 11:04:07',NULL,1,10),(5,'Coca Cola 4','Coca Cola 4','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',248,'coca-cola-4','2024-03-07 11:30:33','2024-03-07 13:34:55',NULL,1,10),(6,'Coca Cola 5','Coca Cola 5','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',249,'coca-cola-5','2024-03-07 11:30:51','2024-03-07 13:35:09',NULL,1,10),(7,'NeoBlanc','NeoBlanc','Neoblanc tradicional garante proteção para a sua casa e para a sua roupa. Graças à tecnologia exclusiva protege fibra, envolve as fibras e protege-as durante a lavagem, ajudando a manter as peças como novas! Além disso, higieniza e remove mais de 90% dos alergénios de pólen, poeira, ácaros e animais domésticos.','Neoblanc tradicional garante proteção para a sua casa e para a sua roupa. Graças à tecnologia exclusiva protege fibra, envolve as fibras e protege-as durante a lavagem, ajudando a manter as peças como novas! Além disso, higieniza e remove mais de 90% dos alergénios de pólen, poeira, ácaros e animais domésticos.',268,'neoblanc','2024-03-11 09:11:42','2024-03-18 22:17:52',NULL,1,17),(8,'Oral-B','Oral-B','O dentífrico Pro-Expert de Proteção Profissional com sabor a menta fresca da Oral-B, desenvolvido pelos especialistas da Oral-B, protege a boca dos 8 problemas que se tratam com mais frequência nas consultas com os dentistas.','O dentífrico Pro-Expert de Proteção Profissional com sabor a menta fresca da Oral-B, desenvolvido pelos especialistas da Oral-B, protege a boca dos 8 problemas que se tratam com mais frequência nas consultas com os dentistas.',270,'oral-b','2024-03-11 09:15:41','2024-03-11 09:15:41',NULL,1,19),(9,'UniCroissants','UniCroissant','sdrecrf','dfdfffd',272,'unicroissants','2024-03-11 09:24:20','2024-03-11 09:24:20',NULL,1,15),(11,'Logitech','xxewde','xsw','sxas',283,'logitech','2024-03-11 11:58:16','2024-03-11 11:59:22','2024-03-11 11:59:26',1,15),(12,'ddsds','dsdsdf','fdsdfsdfs','dfs',NULL,'ddsds','2024-03-15 14:38:07','2024-03-15 14:38:07','2024-03-15 14:38:14',1,15),(13,'Teste','Teste','ddd','ddd',NULL,'teste','2024-03-18 11:06:44','2024-03-18 11:06:44','2024-03-18 11:09:02',1,10),(14,'Teste','Teste','ddd','ddd',NULL,'teste-1','2024-03-18 11:08:08','2024-03-18 11:08:08','2024-03-18 11:09:05',1,10),(15,'Teste','Teste','ddd','ddd',NULL,'teste-2','2024-03-18 11:08:16','2024-03-18 11:08:16','2024-03-18 11:09:11',1,10),(16,'Teste','Teste','ddd','ddd',NULL,'teste-3','2024-03-18 11:08:20','2024-03-18 11:08:20','2024-03-18 11:09:14',1,10),(17,'Teste','Teste','ddd','ddd',296,'teste-4','2024-03-18 11:08:48','2024-03-18 11:10:34','2024-03-18 11:56:40',1,10),(18,'testeeee','dddd',NULL,NULL,299,'testeeee','2024-03-18 11:55:10','2024-03-18 11:56:25','2024-03-18 11:56:37',1,17),(19,'teste','teste',NULL,NULL,310,'teste-5','2024-03-18 12:21:46','2024-03-18 12:24:08','2024-03-18 12:24:17',1,10),(20,'teste222333','fff',NULL,NULL,312,'teste222','2024-03-18 12:24:32','2024-03-18 12:24:43','2024-03-18 12:24:49',1,19),(21,'teste marca','teste marca','sd',NULL,332,'teste-marca','2024-03-18 14:19:40','2024-03-18 14:19:40','2024-03-18 22:14:14',1,24),(22,'teste222222','rrrrr',NULL,NULL,337,'teste222222','2024-03-18 22:06:24','2024-03-18 22:06:24','2024-03-18 22:14:00',1,15);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title_pt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` int NOT NULL DEFAULT '1',
  `sort` int DEFAULT NULL,
  `slug` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Bebidas','Drinks',NULL,NULL,1,NULL,'bebidas',161,'2024-03-03 16:18:04','2024-03-03 16:18:04','2024-03-03 16:21:21'),(2,'Bebidas','Dri','Baka','cce',1,NULL,'bebidas-1',162,'2024-03-03 16:21:57','2024-03-03 16:21:57','2024-03-07 12:04:52'),(3,'hhghhh','daf','fa','fasf',1,NULL,'dc',NULL,'2024-03-03 16:35:50','2024-03-03 16:59:05','2024-03-03 16:59:30'),(4,'safsaf','asfsaf','fasf','sfsaf',1,NULL,'safsaf',163,'2024-03-03 16:36:03','2024-03-03 16:36:03','2024-03-03 16:48:53'),(5,'aaaaaaaaaaaaaaabbbbbbbbbbbb','vsdvrfvrtvtv','fvfvevtrvt','fvfvvt',1,NULL,'safdverfvrfvt',164,'2024-03-03 16:44:34','2024-03-03 16:48:46','2024-03-03 16:48:50'),(6,'wrtwtaaa','trwt','twrt','twtwt',1,NULL,'wrtwt',NULL,'2024-03-03 16:59:15','2024-03-03 16:59:23','2024-03-03 16:59:27'),(7,'sfrfwrw','rfrf','rferf','frwf',1,NULL,'sfrfwr',167,'2024-03-03 16:59:53','2024-03-03 17:00:07','2024-03-03 17:00:10'),(8,'dewddsfd','d','sdfd','dsfs',1,NULL,'dewd',219,'2024-03-06 16:15:06','2024-03-06 16:15:16','2024-03-07 12:04:55'),(9,'aaaaabbb','aaaa','aaaa','aaa',1,NULL,'aaaaa',227,'2024-03-06 16:22:16','2024-03-06 16:22:28','2024-03-06 16:22:36'),(10,'Bebidas','Drinks','Venha saborear as nossas bebidas de varias marcas','Come and taste our drinks from various brands',1,NULL,'bebidas-2',280,'2024-03-07 12:05:51','2024-03-11 10:56:36',NULL),(11,'Coca Cola 2','Coca Cola 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'coca-cola-2',235,'2024-03-07 12:06:33','2024-03-07 12:20:35','2024-03-07 13:23:45'),(12,'Coca Cola 3','Coca Cola 3','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'coca-cola-3',236,'2024-03-07 12:06:51','2024-03-07 12:20:45','2024-03-07 13:23:53'),(13,'Coca Cola 4','Coca Cola 4','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'coca-cola-4',237,'2024-03-07 12:07:20','2024-03-07 12:20:54','2024-03-07 13:23:49'),(14,'Coca Cola 5','Coca Cola 5','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'coca-cola-5',238,'2024-03-07 12:07:43','2024-03-07 12:21:08','2024-03-07 13:23:37'),(15,'Lanche','Snack','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'lanche',279,'2024-03-07 12:08:19','2024-03-11 10:56:27',NULL),(16,'Snack 2','Snack 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'snack-2',240,'2024-03-07 12:08:37','2024-03-07 12:21:34','2024-03-07 13:23:40'),(17,'Limpeza','Cleaning','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'limpeza',241,'2024-03-07 12:09:42','2024-03-07 15:12:23',NULL),(18,'Cleaning 2','Cleaning 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'cleaning-2',242,'2024-03-07 12:10:08','2024-03-07 12:21:57','2024-03-07 13:23:43'),(19,'Higiene','Hygiene','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'higiene',281,'2024-03-07 12:10:44','2024-03-11 10:56:48',NULL),(20,'Hygiene 2','Hygiene 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',1,NULL,'hygiene-2',244,'2024-03-07 12:11:07','2024-03-07 12:22:40','2024-03-07 13:23:28'),(21,'fer','e','dc','df',1,NULL,'fer',NULL,'2024-03-11 11:54:49','2024-03-11 11:54:49','2024-03-11 11:55:01'),(22,'teste','teste123','sds','sad',1,NULL,'teste',314,'2024-03-18 12:29:01','2024-03-18 12:33:10','2024-03-18 12:33:45'),(23,'teste33233','sed','s','sff',1,NULL,'teste33233',316,'2024-03-18 12:33:27','2024-03-18 12:33:34','2024-03-18 12:33:42'),(24,'Teste Categoria','Teste Categoria','ss','dwdwd',1,NULL,'teste-categoria',331,'2024-03-18 14:19:20','2024-03-18 14:19:20','2024-03-18 22:14:34');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Teste','2024-02-27 19:00:05','2024-03-13 10:19:53','2024-03-13 10:19:53'),(2,'Bruno','2024-03-12 14:35:45','2024-03-12 14:35:45',NULL),(3,'Brunoss','2024-03-12 15:23:08','2024-03-12 15:23:08',NULL),(4,'jose','2024-03-13 10:27:56','2024-03-13 10:27:56',NULL),(5,'COSTA','2024-03-13 11:18:25','2024-03-13 11:18:25',NULL),(6,'Carolina','2024-03-13 11:32:43','2024-03-13 11:32:43',NULL),(7,'joel','2024-03-13 11:35:27','2024-03-13 11:35:27',NULL),(8,'Monica','2024-03-13 12:38:59','2024-03-13 12:38:59',NULL),(9,'Amelia','2024-03-13 14:43:21','2024-03-13 14:43:21',NULL),(10,'beatriz','2024-03-13 14:57:39','2024-03-13 14:57:39',NULL),(11,'ivone','2024-03-13 16:56:11','2024-03-13 16:58:16','2024-03-13 16:58:16'),(12,'Laia','2024-03-14 10:25:13','2024-03-14 10:25:13',NULL),(13,'Joaquim','2024-03-14 10:31:55','2024-03-18 15:01:52','2024-03-18 15:01:52'),(14,'wwdqddwdq','2024-03-14 11:09:19','2024-03-18 14:57:25','2024-03-18 14:57:25'),(15,'cdssddewewee','2024-03-15 15:14:26','2024-03-18 14:57:16','2024-03-18 14:57:16'),(16,'sssss','2024-03-18 15:02:00','2024-03-18 15:02:02','2024-03-18 15:02:02'),(17,'saassaas','2024-03-18 15:02:06','2024-03-18 15:02:06',NULL),(18,'qqqq','2024-03-18 15:02:09','2024-03-18 16:43:59','2024-03-18 16:43:59'),(19,'Bruno Contacto','2024-03-18 16:42:20','2024-03-18 16:42:20',NULL),(20,'Bruno Flyer','2024-03-18 16:45:48','2024-03-18 16:45:48',NULL),(21,'Bruno Produto','2024-03-18 16:47:10','2024-03-18 22:27:09','2024-03-18 22:27:09');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configurations`
--

DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configurations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image_id` bigint DEFAULT NULL,
  `image_background_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `text_termos_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_termos_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_rgpd_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_rgpd_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_about_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_about_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configurations`
--

LOCK TABLES `configurations` WRITE;
/*!40000 ALTER TABLE `configurations` DISABLE KEYS */;
INSERT INTO `configurations` VALUES (1,339,342,'2024-01-11 17:10:50',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_client_id_foreign` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,2,'Bruno','bruno.joel.costa@hotmail.com','910041440','2024-03-12 14:35:45','2024-03-12 14:35:45',NULL),(2,3,'Brunoss','support@develop2you.com','910041447','2024-03-12 15:23:08','2024-03-12 15:23:08',NULL),(3,3,'Lara','support@develop2you.com','910041448','2024-03-13 10:12:13','2024-03-13 10:12:13',NULL),(4,4,'jose','bruno@hotmail.com','911111111','2024-03-13 10:27:56','2024-03-13 10:27:56',NULL),(5,5,'COSTA','costa@hotmail.com','911111114','2024-03-13 11:18:25','2024-03-13 11:18:25',NULL),(6,6,'Carolina','carolina@hotmail.com','910041433','2024-03-13 11:32:43','2024-03-13 11:32:43',NULL),(7,7,'joel','joel@hotmail.com','912345678','2024-03-13 11:35:28','2024-03-13 11:35:28',NULL),(8,8,'Monica','Monica@hotmail.com','910041442','2024-03-13 12:38:59','2024-03-13 12:38:59',NULL),(9,9,'Amelia','amelia@hotmail.com','910041321','2024-03-13 14:43:22','2024-03-13 14:43:22',NULL),(10,10,'beatriz','beatriz@hotmail.com','910041465','2024-03-13 14:57:39','2024-03-13 14:57:39',NULL),(11,11,'ivone','ivos@hotmail.com','910041875','2024-03-13 16:56:11','2024-03-13 16:58:16','2024-03-13 16:58:16'),(12,12,'Laia','aas@hotmail.com','9133122111','2024-03-14 10:25:13','2024-03-14 10:25:13',NULL),(13,13,'Joaquim','joaquimsantos@hotmail.com','910041461','2024-03-14 10:31:55','2024-03-18 15:01:52','2024-03-18 15:01:52'),(14,14,'wwdqddwdq','santweweeos@hotmail.com','9100414331','2024-03-14 11:09:19','2024-03-18 14:57:25','2024-03-18 14:57:25'),(15,15,'cdssddewewee','santddddeos@hotmail.com','91004144811','2024-03-15 15:14:26','2024-03-18 14:57:16','2024-03-18 14:57:16'),(16,19,'Bruno Contacto','contacto@hotmail.com','9100284632','2024-03-18 16:42:20','2024-03-18 16:42:20',NULL),(17,20,'Bruno Flyer','flyer@hotmail.com','91204745242','2024-03-18 16:45:48','2024-03-18 16:45:48',NULL),(18,21,'Bruno Produto','Produto@hotmail.com','91745628173','2024-03-18 16:47:10','2024-03-18 22:27:09','2024-03-18 22:27:09');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flyer_banners`
--

DROP TABLE IF EXISTS `flyer_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flyer_banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title_pt` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `image_id` bigint DEFAULT NULL,
  `slug` longtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flyer_banners`
--

LOCK TABLES `flyer_banners` WRITE;
/*!40000 ALTER TABLE `flyer_banners` DISABLE KEYS */;
INSERT INTO `flyer_banners` VALUES (1,NULL,'hyth',182,'','2024-03-04 19:55:00','2024-03-04 19:55:00','2024-03-04 19:55:04',1),(2,NULL,'6y6y',183,'-1','2024-03-04 19:55:50','2024-03-04 19:55:50','2024-03-04 19:57:25',1),(3,'y6y6','y6yy',184,'-2','2024-03-04 19:57:32','2024-03-04 19:57:32','2024-03-04 20:14:00',1),(4,'Delta','sdd',185,'-3','2024-03-04 20:14:13','2024-03-04 20:14:13','2024-03-04 20:15:39',1),(5,'Delta','sdsdec',288,'delta','2024-03-04 20:15:50','2024-03-11 23:49:19',NULL,1),(6,'Apple','dqwdw',286,'apple','2024-03-04 20:16:05','2024-03-11 15:54:59',NULL,1),(7,'gbr','bgf',199,'gbr','2024-03-06 09:38:58','2024-03-06 09:39:05','2024-03-06 09:39:12',1),(8,'fvebvdvvdcdfvfcfv','dvdfv',211,'fvebv','2024-03-06 15:50:40','2024-03-06 15:50:51','2024-03-06 15:51:00',1),(9,'fsfsdfsdfaaaaaa','sfdsf',221,'fsfsdfsdf','2024-03-06 16:15:35','2024-03-06 16:15:53','2024-03-06 16:15:57',1),(10,'Sobreeee','dwd',262,'sobreeee','2024-03-08 12:02:42','2024-03-08 12:02:42','2024-03-08 12:02:47',1),(11,'Bebidasaaa','cvcv',NULL,'bebidas','2024-03-15 14:39:21','2024-03-15 14:40:19',NULL,1),(12,'fvfv','dsvscc',322,'fvfv','2024-03-18 12:40:52','2024-03-18 12:42:15','2024-03-18 12:42:34',1),(13,'dfd','dffd',324,'dfd','2024-03-18 12:42:21','2024-03-18 12:42:27','2024-03-18 12:42:32',1),(14,'teste flyerbanner','teste flyer banner',334,'teste-flyerbanner','2024-03-18 14:20:52','2024-03-18 14:20:52','2024-03-18 22:14:43',1);
/*!40000 ALTER TABLE `flyer_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flyers`
--

DROP TABLE IF EXISTS `flyers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flyers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_pt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `image_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flyers`
--

LOCK TABLES `flyers` WRITE;
/*!40000 ALTER TABLE `flyers` DISABLE KEYS */;
INSERT INTO `flyers` VALUES (1,'Seven Up','Seven Up','seven-up',NULL,NULL,157,'2024-03-03 15:12:01','2024-03-03 15:12:01','2024-03-03 15:16:11',1),(2,'Natal','Seven Up','seven-up-1','2024-03-11','2024-03-28',289,'2024-03-03 15:16:23','2024-03-12 10:22:25',NULL,0),(3,'dfrfffaaaaa','rfrfrf','dfrfff',NULL,NULL,165,'2024-03-03 16:47:28','2024-03-03 16:47:37','2024-03-03 16:47:42',1),(4,'aaaaaaaaaaaabbbbbbbbbbb','aaaaaaaaaaaa','aaaaaaaaaaaa',NULL,NULL,166,'2024-03-03 16:48:17','2024-03-03 16:48:24','2024-03-03 16:48:30',1),(5,'csdfrfaaaaaaaa','fdfsdf','csdfrf',NULL,NULL,168,'2024-03-03 17:00:29','2024-03-03 17:00:35','2024-03-03 17:00:39',1),(6,'Refrigerantesq','sdfwf','refrigerantesq','2024-03-04','2024-03-14',NULL,'2024-03-04 10:15:31','2024-03-04 10:15:49','2024-03-04 10:17:47',1),(7,'assa','asas','assa','2024-03-04','2024-03-14',NULL,'2024-03-04 10:17:58','2024-03-04 10:17:58','2024-03-04 10:43:54',1),(8,'dwvwcrfvwe','sdcsw','dwvwcrfvwe','2024-03-04','2024-03-06',169,'2024-03-04 10:37:48','2024-03-04 10:37:48','2024-03-04 10:42:53',1),(9,'sacasca','scscaca','sacasca','2024-03-04',NULL,NULL,'2024-03-04 10:41:49','2024-03-04 10:41:49','2024-03-04 10:42:50',1),(10,'qqqqq','qqqqq','qqqqq','2024-03-04','2024-03-14',170,'2024-03-04 10:43:06','2024-03-04 10:43:06','2024-03-04 10:43:51',1),(11,'ccc','ccc','ccc',NULL,NULL,171,'2024-03-04 10:44:05','2024-03-04 10:45:36','2024-03-04 10:45:49',1),(12,'wdswq','sxaca','wdswq','2024-03-15','2024-03-15',NULL,'2024-03-04 10:46:01','2024-03-04 10:46:01','2024-03-04 10:56:09',1),(13,'SDAQWD','DSQ','sdaqwd','2024-03-13','2024-03-22',172,'2024-03-04 10:56:27','2024-03-04 10:56:27','2024-03-04 10:57:26',1),(14,'sxaxa','xsasx','sxaxa','2024-03-07','2024-03-20',173,'2024-03-04 10:58:32','2024-03-04 10:58:32','2024-03-04 11:25:39',1),(15,'Páscoa','Páscoa','pascoa','2024-03-07','2024-03-15',174,'2024-03-04 11:26:27','2024-03-12 15:54:58',NULL,0),(16,'dfdf','dasdad','dfdf','2024-03-04','2024-03-15',NULL,'2024-03-04 11:41:49','2024-03-04 11:41:49','2024-03-04 12:34:57',1),(17,'fewf','sdffdsfs','fewf','2024-03-18',NULL,NULL,'2024-03-04 12:03:15','2024-03-04 12:08:11','2024-03-04 12:34:54',1),(18,'Delta','cacsda','delta','2024-03-06','2024-03-22',175,'2024-03-04 12:12:53','2024-03-04 12:13:36','2024-03-04 12:34:51',1),(19,'Apple','Aplle','apple','2024-03-04','2024-03-13',NULL,'2024-03-04 12:20:16','2024-03-04 12:20:16','2024-03-04 12:34:49',1),(20,'Bebidas','Drinks','bebidas','2024-03-05','2024-03-15',NULL,'2024-03-05 23:36:15','2024-03-05 23:36:15','2024-03-05 23:52:37',1),(21,'Bebidas','Drinks','bebidas-1','2024-03-05','2024-03-15',NULL,'2024-03-05 23:43:29','2024-03-05 23:43:29','2024-03-05 23:52:34',1),(22,'Bebidas','Drinks','bebidas-2','2024-03-05','2024-03-15',NULL,'2024-03-05 23:48:14','2024-03-05 23:48:14','2024-03-05 23:52:31',1),(23,'Bebidas','Drinks','bebidas-3','2024-03-05','2024-03-15',NULL,'2024-03-05 23:51:59','2024-03-05 23:51:59','2024-03-05 23:52:28',1),(24,'Aniversário','Birthday','aniversario','2024-03-05','2024-03-21',191,'2024-03-05 23:53:11','2024-03-18 14:42:44',NULL,1),(25,'dfrfff','q','dfrfff-1','2024-03-06','2024-03-14',NULL,'2024-03-06 00:22:46','2024-03-06 00:22:46','2024-03-08 11:30:27',1),(26,'Black Friday','Black Friday','black-friday','2024-03-06','2024-03-23',NULL,'2024-03-06 00:29:37','2024-03-18 16:48:15',NULL,1),(27,'xaxxa','axax','xaxxa','2024-03-06','2024-03-22',NULL,'2024-03-06 00:43:36','2024-03-06 00:43:36','2024-03-08 11:30:31',1),(28,'aaaaaaaaaaaa','Ref','aaaaaaaaaaaa-1','2024-03-06','2024-03-30',NULL,'2024-03-06 00:46:37','2024-03-06 00:46:37','2024-03-08 11:29:38',1),(29,'ggerger','fref','ggerger','2024-03-06','2024-03-23',NULL,'2024-03-06 08:51:41','2024-03-06 08:51:41','2024-03-08 11:30:16',1),(30,'fgtrbr','brtb','fgtrbr','2024-03-06','2024-03-21',NULL,'2024-03-06 08:53:07','2024-03-06 08:53:07','2024-03-08 11:30:52',1),(31,'fgtrbr','brtb','fgtrbr-1','2024-03-06','2024-03-21',NULL,'2024-03-06 08:54:48','2024-03-06 08:54:48','2024-03-06 08:54:52',1),(32,'th5th','tgrtr','th5th',NULL,NULL,NULL,'2024-03-06 08:54:58','2024-03-06 08:54:58','2024-03-06 08:55:02',1),(33,'thgrth','htrh','thgrth',NULL,NULL,NULL,'2024-03-06 08:55:10','2024-03-06 08:55:10','2024-03-06 08:55:14',1),(34,'tr45','trg','tr45','2024-03-07','2024-03-26',NULL,'2024-03-06 08:55:33','2024-03-06 08:55:33','2024-03-08 11:30:36',1),(35,'Seven Up','vdfsv','seven-up-2','2024-03-06','2024-03-14',NULL,'2024-03-06 09:12:48','2024-03-06 09:12:48','2024-03-12 10:24:26',1),(36,'mjmkm','mjhm','mjmkm',NULL,NULL,NULL,'2024-03-06 09:23:25','2024-03-06 09:23:25','2024-03-08 11:30:48',1),(37,'fvrfv','rfr','fvrfv',NULL,NULL,NULL,'2024-03-06 09:25:59','2024-03-06 09:25:59','2024-03-08 11:30:45',1),(38,'ewdre','dw','ewdre',NULL,NULL,NULL,'2024-03-06 09:30:56','2024-03-06 09:30:56','2024-03-08 11:30:42',1),(39,'ewdre','dw','ewdre-1',NULL,NULL,194,'2024-03-06 09:31:48','2024-03-06 09:31:48','2024-03-06 09:48:55',1),(40,'refvrv','ferfe','refvrv','2024-03-06','2024-03-06',202,'2024-03-06 09:48:30','2024-03-06 09:48:30','2024-03-06 09:48:44',1),(41,'fdwf','sdf','fdwf','2024-03-06','2024-03-06',NULL,'2024-03-06 12:03:13','2024-03-06 12:03:13','2024-03-08 11:30:12',1),(42,'fdwf','sdf','fdwf-1','2024-03-06','2024-03-06',NULL,'2024-03-06 12:06:05','2024-03-06 12:06:05','2024-03-08 11:30:07',1),(43,'fdwfxcxc','sdf','fdwf-2','2024-03-06','2024-03-06',204,'2024-03-06 12:07:35','2024-03-06 12:46:06','2024-03-08 11:29:43',1),(44,'faaaaaaa','faaaa','faaaaaaa','2024-03-06','2024-03-06',NULL,'2024-03-06 13:49:58','2024-03-06 13:49:58','2024-03-08 11:29:33',1),(45,'gergeg','fdsfg','gergeg','2024-03-06','2024-03-06',NULL,'2024-03-06 15:05:17','2024-03-06 15:05:17','2024-03-06 15:30:28',1),(46,'gergeg','fdsfg','gergeg-1','2024-03-06','2024-03-06',NULL,'2024-03-06 15:11:46','2024-03-06 15:11:46','2024-03-06 15:30:22',1),(47,'gergeg','fdsfg','gergeg-2','2024-03-06','2024-03-06',NULL,'2024-03-06 15:17:24','2024-03-06 15:17:24','2024-03-06 15:55:02',1),(48,'REdbull','adsd','redbull','2024-03-06','2024-03-06',205,'2024-03-06 15:29:50','2024-03-06 15:39:30','2024-03-07 10:35:18',1),(49,'tttttt fdvdffvvf','gfrh','tttttt','2024-03-06','2024-03-06',215,'2024-03-06 15:55:30','2024-03-06 15:55:45','2024-03-06 16:17:51',1),(50,'aaaaabbb','aaaaa','aaaaa','2024-03-06','2024-03-06',225,'2024-03-06 16:18:11','2024-03-06 16:18:25','2024-03-06 16:18:36',1),(51,'axcvedds','sdadew','axcvedds','2024-03-08','2024-03-08',258,'2024-03-08 11:29:13','2024-03-08 11:29:13','2024-03-08 11:29:28',1),(52,'teste222222ddd','bvb','teste','2024-03-18','2024-03-18',319,'2024-03-18 12:35:42','2024-03-18 12:39:01','2024-03-18 12:39:04',1),(53,'teste flyer','teste flyer','teste-flyer','2024-03-18','2024-03-18',335,'2024-03-18 14:21:33','2024-03-18 14:21:33','2024-03-18 22:14:38',1);
/*!40000 ALTER TABLE `flyers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flyers_flyerbanner`
--

DROP TABLE IF EXISTS `flyers_flyerbanner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flyers_flyerbanner` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flyer_id` bigint NOT NULL,
  `flyerbanner_id` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flyers_flyerbanner`
--

LOCK TABLES `flyers_flyerbanner` WRITE;
/*!40000 ALTER TABLE `flyers_flyerbanner` DISABLE KEYS */;
INSERT INTO `flyers_flyerbanner` VALUES (1,2,5),(2,43,5),(4,44,5),(5,44,6),(6,48,5),(7,49,5),(8,49,6),(9,50,5),(10,50,6),(11,51,5),(12,24,5),(13,26,6),(14,15,5),(15,52,6),(16,53,14);
/*!40000 ALTER TABLE `flyers_flyerbanner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flyers_superprice_products`
--

DROP TABLE IF EXISTS `flyers_superprice_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flyers_superprice_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint NOT NULL,
  `flyer_id` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flyers_superprice_products`
--

LOCK TABLES `flyers_superprice_products` WRITE;
/*!40000 ALTER TABLE `flyers_superprice_products` DISABLE KEYS */;
INSERT INTO `flyers_superprice_products` VALUES (1,10,47),(2,10,48),(3,10,49),(4,11,49),(5,13,49),(6,10,50),(7,11,50),(8,10,51),(9,11,51),(10,11,2),(11,10,26),(13,24,15),(14,25,15),(15,24,52),(16,29,53);
/*!40000 ALTER TABLE `flyers_superprice_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flyersproduct_campaign`
--

DROP TABLE IF EXISTS `flyersproduct_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flyersproduct_campaign` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flyer_id` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flyersproduct_campaign`
--

LOCK TABLES `flyersproduct_campaign` WRITE;
/*!40000 ALTER TABLE `flyersproduct_campaign` DISABLE KEYS */;
INSERT INTO `flyersproduct_campaign` VALUES (1,23,9),(2,23,10),(3,23,11),(4,24,9),(5,24,10),(6,25,9),(7,25,11),(8,26,9),(9,26,11),(10,27,9),(11,27,10),(12,28,10),(13,28,11),(14,29,9),(15,29,10),(16,29,11),(17,30,9),(18,31,9),(19,32,9),(20,32,10),(21,32,11),(22,33,9),(23,33,10),(24,33,11),(25,34,9),(26,34,10),(27,35,10),(28,35,11),(29,36,9),(30,36,10),(31,36,11),(32,37,9),(33,37,10),(34,37,11),(35,38,9),(36,38,10),(37,38,11),(38,39,9),(39,39,10),(40,39,11),(41,40,9),(42,40,10),(43,40,11),(44,41,9),(45,41,10),(46,41,11),(47,42,9),(48,42,10),(49,42,11),(51,43,10),(52,43,11),(53,44,10),(54,44,11),(55,47,10),(56,48,9),(57,50,10),(58,50,11),(59,51,10),(60,2,20),(61,15,22),(62,2,23),(63,2,24),(64,2,25),(65,24,20),(66,24,22),(67,52,22),(68,53,29);
/*!40000 ALTER TABLE `flyersproduct_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` bigint DEFAULT NULL,
  `main` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images`
--

LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
INSERT INTO `images` VALUES (123,'/Users/vitorsilva/rsalimentar/public/app_images/bg/2024/2/27','/app_images/bg/2024/2/27/','1-bg-2702202403051345194.jpg','bg',1,0,'2024-02-27 15:05:13',NULL,'2025-07-30 06:37:26'),(150,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','15-ba-03032024112223.jpg','banner',15,0,'2024-03-03 11:22:23',NULL,'2024-03-03 11:42:40'),(151,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','15-ba-03032024112606.png','banner',15,0,'2024-03-03 11:26:06',NULL,NULL),(152,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','15-ba-03032024112621.jpg','banner',15,0,'2024-03-03 11:26:21',NULL,NULL),(153,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','15-ba-03032024114240.png','banner',15,1,'2024-03-03 11:42:40',NULL,NULL),(154,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','16-ba-03032024114312.jpg','banner',16,0,'2024-03-03 11:43:12',NULL,'2024-03-03 11:43:21'),(155,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','16-ba-03032024114322.png','banner',16,1,'2024-03-03 11:43:22',NULL,NULL),(156,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','17-ba-03032024114842.png','banner',17,0,'2024-03-03 11:48:42',NULL,'2024-03-03 13:58:15'),(157,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/3','/app_images/ba/2024/3/3/','17-ba-03032024015817.jpg','banner',17,0,'2024-03-03 13:58:17',NULL,'2024-03-11 10:54:12'),(158,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/3','/app_images/logo/2024/3/3/','1-logo-03032024015835.png','logo',1,0,'2024-03-03 13:58:35',NULL,'2024-03-14 14:13:31'),(159,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/3','/app_images/fy/2024/3/3/','1-fy-03032024031201.png','flyer',1,0,'2024-03-03 15:12:01',NULL,NULL),(160,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/3','/app_images/fy/2024/3/3/','2-fy-03032024031623.png','flyer',2,0,'2024-03-03 15:16:23',NULL,'2024-03-04 15:37:23'),(161,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/3','/app_images/ca/2024/3/3/','1-ca-03032024041804.png','category',1,1,'2024-03-03 16:18:04',NULL,NULL),(162,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/3','/app_images/ca/2024/3/3/','2-ca-03032024042157.png','category',2,1,'2024-03-03 16:21:57',NULL,NULL),(163,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/3','/app_images/ca/2024/3/3/','4-ca-03032024043603.jpg','category',4,1,'2024-03-03 16:36:03',NULL,NULL),(164,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/3','/app_images/ca/2024/3/3/','5-ca-03032024044434.png','category',5,1,'2024-03-03 16:44:34',NULL,NULL),(165,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/3','/app_images/fy/2024/3/3/','3-fy-03032024044728.jpg','flyer',3,1,'2024-03-03 16:47:28',NULL,NULL),(166,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/3','/app_images/fy/2024/3/3/','4-fy-03032024044817.png','flyer',4,1,'2024-03-03 16:48:17',NULL,NULL),(167,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/3','/app_images/ca/2024/3/3/','7-ca-03032024045953.png','category',7,1,'2024-03-03 16:59:53',NULL,NULL),(168,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/3','/app_images/fy/2024/3/3/','5-fy-03032024050029.jpg','flyer',5,1,'2024-03-03 17:00:29',NULL,NULL),(169,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','8-fy-04032024103748.png','flyer',8,1,'2024-03-04 10:37:48',NULL,NULL),(170,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','10-fy-04032024104306.jpg','flyer',10,1,'2024-03-04 10:43:06',NULL,NULL),(171,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','11-fy-04032024104405.jpg','flyer',11,1,'2024-03-04 10:44:05',NULL,NULL),(172,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','13-fy-04032024105627.jpg','flyer',13,1,'2024-03-04 10:56:27',NULL,NULL),(173,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','14-fy-04032024105832.jpg','flyer',14,1,'2024-03-04 10:58:32',NULL,NULL),(174,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','15-fy-04032024112627.jpg','flyer',15,1,'2024-03-04 11:26:27',NULL,NULL),(175,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','18-fy-04032024121253.png','flyer',18,1,'2024-03-04 12:12:53',NULL,NULL),(176,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/4','/app_images/pd/2024/3/4/','6-pd-04032024032821.png','product',6,1,'2024-03-04 15:28:21',NULL,NULL),(177,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/4','/app_images/pd/2024/3/4/','7-pd-04032024033434.png','product',7,0,'2024-03-04 15:34:34',NULL,'2024-03-04 15:35:16'),(178,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/4','/app_images/pd/2024/3/4/','8-pd-04032024033641.jpg','product',8,1,'2024-03-04 15:36:41',NULL,NULL),(179,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/4','/app_images/fy/2024/3/4/','2-fy-04032024033725.png','flyer',2,0,'2024-03-04 15:37:25',NULL,'2024-03-11 15:06:36'),(180,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/4','/app_images/pd/2024/3/4/','9-pd-04032024034036.png','product',9,0,'2024-03-04 15:40:36',NULL,'2024-03-04 15:58:26'),(181,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/4','/app_images/pd/2024/3/4/','9-pd-04032024035828.png','product',9,0,'2024-03-04 15:58:28',NULL,'2024-03-07 13:35:50'),(182,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','1-fb-04032024075501.jpg','flyerbanner',1,1,'2024-03-04 19:55:01',NULL,NULL),(183,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','2-fb-04032024075550.jpg','flyerbanner',2,1,'2024-03-04 19:55:50',NULL,NULL),(184,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','3-fb-04032024075732.jpg','flyerbanner',3,1,'2024-03-04 19:57:32',NULL,NULL),(185,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','4-fb-04032024081413.jpg','flyerbanner',4,1,'2024-03-04 20:14:13',NULL,NULL),(186,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','5-fb-04032024081550.png','flyerbanner',5,0,'2024-03-04 20:15:50',NULL,'2024-03-11 15:54:50'),(187,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/4','/app_images/fb/2024/3/4/','6-fb-04032024081605.png','flyerbanner',6,0,'2024-03-04 20:16:05',NULL,'2024-03-05 09:11:11'),(188,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/5','/app_images/fb/2024/3/5/','6-fb-05032024091112.jpg','flyerbanner',6,0,'2024-03-05 09:11:12',NULL,'2024-03-11 15:54:57'),(189,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/5','/app_images/pd/2024/3/5/','10-pd-05032024025908.png','product',10,0,'2024-03-05 14:59:08',NULL,'2024-03-11 09:27:28'),(190,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/5','/app_images/pd/2024/3/5/','11-pd-05032024113440.png','product',11,0,'2024-03-05 23:34:40',NULL,'2024-03-07 13:37:14'),(191,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','24-fy-06032024122214.png','flyer',24,1,'2024-03-06 00:22:14',NULL,NULL),(192,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','18-ba-06032024123943.png','banner',18,0,'2024-03-06 00:39:43',NULL,'2024-03-08 10:51:31'),(193,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','28-fy-06032024124637.png','flyer',28,0,'2024-03-06 00:46:37',NULL,NULL),(194,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','39-fy-06032024093148.png','flyer',39,1,'2024-03-06 09:31:48',NULL,NULL),(195,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','19-ba-06032024093734.png','banner',19,1,'2024-03-06 09:37:34',NULL,NULL),(196,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','20-ba-06032024093751.png','banner',20,0,'2024-03-06 09:37:51',NULL,'2024-03-06 09:38:00'),(197,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','20-ba-06032024093801.png','banner',20,1,'2024-03-06 09:38:01',NULL,NULL),(198,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','7-fb-06032024093858.png','flyerbanner',7,0,'2024-03-06 09:38:58',NULL,'2024-03-06 09:39:04'),(199,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','7-fb-06032024093905.png','flyerbanner',7,1,'2024-03-06 09:39:05',NULL,NULL),(200,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','12-pd-06032024093955.jpg','product',12,0,'2024-03-06 09:39:55',NULL,'2024-03-06 09:40:03'),(201,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','12-pd-06032024094003.png','product',12,1,'2024-03-06 09:40:03',NULL,NULL),(202,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','40-fy-06032024094830.png','flyer',40,1,'2024-03-06 09:48:30',NULL,NULL),(203,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','43-fy-06032024120735.png','flyer',43,0,'2024-03-06 12:07:35',NULL,'2024-03-06 12:45:54'),(204,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','43-fy-06032024124606.png','flyer',43,1,'2024-03-06 12:46:06',NULL,NULL),(205,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','48-fy-06032024032950.png','flyer',48,1,'2024-03-06 15:29:50',NULL,NULL),(206,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','21-ba-06032024034302.png','banner',21,0,'2024-03-06 15:43:02',NULL,'2024-03-06 15:43:20'),(207,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','21-ba-06032024034321.png','banner',21,1,'2024-03-06 15:43:21',NULL,NULL),(208,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','22-ba-06032024034425.png','banner',22,0,'2024-03-06 15:44:25',NULL,'2024-03-06 15:44:34'),(209,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','22-ba-06032024034435.png','banner',22,1,'2024-03-06 15:44:35',NULL,NULL),(210,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','8-fb-06032024035040.jpg','flyerbanner',8,0,'2024-03-06 15:50:40',NULL,'2024-03-06 15:50:51'),(211,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','8-fb-06032024035051.png','flyerbanner',8,1,'2024-03-06 15:50:51',NULL,NULL),(212,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','13-pd-06032024035415.jpg','product',13,0,'2024-03-06 15:54:15',NULL,'2024-03-06 15:54:26'),(213,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','13-pd-06032024035427.png','product',13,1,'2024-03-06 15:54:27',NULL,NULL),(214,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','49-fy-06032024035530.jpg','flyer',49,0,'2024-03-06 15:55:30',NULL,'2024-03-06 15:55:41'),(215,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','49-fy-06032024035545.png','flyer',49,1,'2024-03-06 15:55:45',NULL,NULL),(216,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','14-pd-06032024041420.png','product',14,0,'2024-03-06 16:14:20',NULL,'2024-03-06 16:14:35'),(217,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/6','/app_images/pd/2024/3/6/','14-pd-06032024041437.png','product',14,1,'2024-03-06 16:14:37',NULL,NULL),(218,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/6','/app_images/ca/2024/3/6/','8-ca-06032024041506.png','category',8,0,'2024-03-06 16:15:06',NULL,'2024-03-06 16:15:15'),(219,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/6','/app_images/ca/2024/3/6/','8-ca-06032024041516.jpg','category',8,1,'2024-03-06 16:15:16',NULL,NULL),(220,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','9-fb-06032024041535.png','flyerbanner',9,0,'2024-03-06 16:15:35',NULL,'2024-03-06 16:15:45'),(221,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/6','/app_images/fb/2024/3/6/','9-fb-06032024041546.png','flyerbanner',9,1,'2024-03-06 16:15:46',NULL,NULL),(222,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','23-ba-06032024041714.png','banner',23,0,'2024-03-06 16:17:14',NULL,'2024-03-06 16:17:21'),(223,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/6','/app_images/ba/2024/3/6/','23-ba-06032024041722.png','banner',23,0,'2024-03-06 16:17:22',NULL,'2024-03-08 10:52:52'),(224,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','50-fy-06032024041812.png','flyer',50,0,'2024-03-06 16:18:12',NULL,'2024-03-06 16:18:21'),(225,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/6','/app_images/fy/2024/3/6/','50-fy-06032024041825.png','flyer',50,1,'2024-03-06 16:18:25',NULL,NULL),(226,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/6','/app_images/ca/2024/3/6/','9-ca-06032024042216.png','category',9,0,'2024-03-06 16:22:16',NULL,'2024-03-06 16:22:25'),(227,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/6','/app_images/ca/2024/3/6/','9-ca-06032024042228.png','category',9,1,'2024-03-06 16:22:28',NULL,NULL),(228,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/7','/app_images/ba/2024/3/7/','24-ba-07032024100304.png','banner',24,0,'2024-03-07 10:03:04',NULL,'2024-03-07 10:03:11'),(229,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/7','/app_images/ba/2024/3/7/','24-ba-07032024100314.png','banner',24,1,'2024-03-07 10:03:14',NULL,NULL),(230,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','1-br-07032024102232.png','brand',1,0,'2024-03-07 10:22:32',NULL,'2024-03-07 10:22:41'),(231,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','1-br-07032024102242.png','brand',1,1,'2024-03-07 10:22:42',NULL,NULL),(232,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','16-pd-07032024114223.png','product',16,0,'2024-03-07 11:42:23',NULL,'2024-03-07 11:43:11'),(233,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','16-pd-07032024114312.png','product',16,0,'2024-03-07 11:43:12',NULL,'2024-03-07 13:38:39'),(234,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','10-ca-07032024122022.jpg','category',10,0,'2024-03-07 12:20:22',NULL,'2024-03-11 10:56:35'),(235,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','11-ca-07032024122035.jpg','category',11,1,'2024-03-07 12:20:35',NULL,NULL),(236,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','12-ca-07032024122045.jpg','category',12,1,'2024-03-07 12:20:45',NULL,NULL),(237,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','13-ca-07032024122054.jpg','category',13,1,'2024-03-07 12:20:54',NULL,NULL),(238,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','14-ca-07032024122108.jpg','category',14,1,'2024-03-07 12:21:08',NULL,NULL),(239,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','15-ca-07032024122125.png','category',15,0,'2024-03-07 12:21:25',NULL,'2024-03-08 14:07:50'),(240,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','16-ca-07032024122134.png','category',16,1,'2024-03-07 12:21:34',NULL,NULL),(241,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','17-ca-07032024122147.png','category',17,1,'2024-03-07 12:21:47',NULL,NULL),(242,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','18-ca-07032024122157.png','category',18,1,'2024-03-07 12:21:57',NULL,NULL),(243,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','19-ca-07032024122224.png','category',19,0,'2024-03-07 12:22:24',NULL,'2024-03-08 14:15:46'),(244,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/7','/app_images/ca/2024/3/7/','20-ca-07032024122240.png','category',20,1,'2024-03-07 12:22:40',NULL,NULL),(245,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','2-br-07032024122340.jpg','brand',2,0,'2024-03-07 12:23:40',NULL,'2024-03-07 13:15:10'),(246,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','2-br-07032024011512.jpg','brand',2,1,'2024-03-07 13:15:12',NULL,NULL),(247,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','3-br-07032024013446.jpg','brand',3,1,'2024-03-07 13:34:46',NULL,NULL),(248,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','5-br-07032024013455.jpg','brand',5,1,'2024-03-07 13:34:55',NULL,NULL),(249,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/7','/app_images/br/2024/3/7/','6-br-07032024013509.jpg','brand',6,1,'2024-03-07 13:35:09',NULL,NULL),(250,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','9-pd-07032024013553.jpg','product',9,1,'2024-03-07 13:35:53',NULL,NULL),(251,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','11-pd-07032024013727.jpg','product',11,1,'2024-03-07 13:37:27',NULL,NULL),(252,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','15-pd-07032024013818.jpg','product',15,1,'2024-03-07 13:38:18',NULL,NULL),(253,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','16-pd-07032024013858.jpg','product',16,1,'2024-03-07 13:38:58',NULL,NULL),(254,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/7','/app_images/pd/2024/3/7/','17-pd-07032024015338.jpg','product',17,1,'2024-03-07 13:53:38',NULL,NULL),(255,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','18-ba-08032024105157.png','banner',18,0,'2024-03-08 10:51:57',NULL,'2024-03-08 10:53:59'),(256,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','23-ba-08032024105253.png','banner',23,1,'2024-03-08 10:52:53',NULL,NULL),(257,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','18-ba-08032024105400.jpg','banner',18,0,'2024-03-08 10:54:00',NULL,'2024-03-11 10:55:19'),(258,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/8','/app_images/fy/2024/3/8/','51-fy-08032024112913.jpg','flyer',51,1,'2024-03-08 11:29:13',NULL,NULL),(259,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','25-ba-08032024114035.png','banner',25,1,'2024-03-08 11:40:35',NULL,NULL),(260,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','26-ba-08032024114245.jpg','banner',26,1,'2024-03-08 11:42:45',NULL,NULL),(261,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/8','/app_images/ba/2024/3/8/','27-ba-08032024115035.jpg','banner',27,1,'2024-03-08 11:50:35',NULL,NULL),(262,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/8','/app_images/fb/2024/3/8/','10-fb-08032024120242.jpg','flyerbanner',10,1,'2024-03-08 12:02:42',NULL,NULL),(263,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/8','/app_images/ca/2024/3/8/','15-ca-08032024020752.jpg','category',15,0,'2024-03-08 14:07:52',NULL,'2024-03-08 14:08:16'),(264,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/8','/app_images/ca/2024/3/8/','15-ca-08032024020818.ico','category',15,0,'2024-03-08 14:08:18',NULL,'2024-03-11 10:56:26'),(265,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/8','/app_images/ca/2024/3/8/','19-ca-08032024021550.png','category',19,0,'2024-03-08 14:15:50',NULL,'2024-03-11 10:56:47'),(266,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/8','/app_images/pd/2024/3/8/','20-pd-08032024040751.jpg','product',20,0,'2024-03-08 16:07:51',NULL,'2024-03-11 09:28:11'),(267,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/8','/app_images/pd/2024/3/8/','21-pd-08032024042151.png','product',21,1,'2024-03-08 16:21:51',NULL,NULL),(268,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/11','/app_images/br/2024/3/11/','7-br-11032024091142.png','brand',7,1,'2024-03-11 09:11:42',NULL,NULL),(269,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','22-pd-11032024091252.png','product',22,1,'2024-03-11 09:12:52',NULL,NULL),(270,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/11','/app_images/br/2024/3/11/','8-br-11032024091541.webp','brand',8,1,'2024-03-11 09:15:41',NULL,NULL),(271,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','23-pd-11032024091656.webp','product',23,1,'2024-03-11 09:16:56',NULL,NULL),(272,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/11','/app_images/br/2024/3/11/','9-br-11032024092420.png','brand',9,1,'2024-03-11 09:24:20',NULL,NULL),(273,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','24-pd-11032024092515.jpeg','product',24,1,'2024-03-11 09:25:15',NULL,NULL),(274,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','10-pd-11032024092730.jpg','product',10,1,'2024-03-11 09:27:30',NULL,NULL),(275,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','20-pd-11032024092813.jpg','product',20,1,'2024-03-11 09:28:13',NULL,NULL),(276,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/pd/2024/3/11','/app_images/pd/2024/3/11/','25-pd-11032024093037.jpg','product',25,1,'2024-03-11 09:30:37',NULL,NULL),(277,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/11','/app_images/ba/2024/3/11/','17-ba-11032024105415.jpg','banner',17,0,'2024-03-11 10:54:15',NULL,'2024-03-11 10:55:32'),(278,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ba/2024/3/11','/app_images/ba/2024/3/11/','17-ba-11032024105533.jpg','banner',17,1,'2024-03-11 10:55:33',NULL,NULL),(279,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/11','/app_images/ca/2024/3/11/','15-ca-11032024105627.png','category',15,1,'2024-03-11 10:56:27',NULL,NULL),(280,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/11','/app_images/ca/2024/3/11/','10-ca-11032024105636.png','category',10,1,'2024-03-11 10:56:36',NULL,NULL),(281,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/ca/2024/3/11','/app_images/ca/2024/3/11/','19-ca-11032024105648.png','category',19,1,'2024-03-11 10:56:48',NULL,NULL),(282,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/11','/app_images/br/2024/3/11/','11-br-11032024115816.png','brand',11,0,'2024-03-11 11:58:16',NULL,'2024-03-11 11:59:22'),(283,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/br/2024/3/11','/app_images/br/2024/3/11/','11-br-11032024115922.png','brand',11,1,'2024-03-11 11:59:22',NULL,NULL),(284,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/11','/app_images/fy/2024/3/11/','2-fy-11032024030637.jpg','flyer',2,0,'2024-03-11 15:06:37',NULL,'2024-03-12 10:22:04'),(285,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/11','/app_images/fb/2024/3/11/','5-fb-11032024035451.jpg','flyerbanner',5,0,'2024-03-11 15:54:51',NULL,'2024-03-11 23:45:55'),(286,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/11','/app_images/fb/2024/3/11/','6-fb-11032024035459.jpg','flyerbanner',6,1,'2024-03-11 15:54:59',NULL,NULL),(287,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/11','/app_images/fb/2024/3/11/','5-fb-11032024114556.jpg','flyerbanner',5,0,'2024-03-11 23:45:56',NULL,'2024-03-11 23:49:18'),(288,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fb/2024/3/11','/app_images/fb/2024/3/11/','5-fb-11032024114919.jpg','flyerbanner',5,1,'2024-03-11 23:49:19',NULL,NULL),(289,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/fy/2024/3/12','/app_images/fy/2024/3/12/','2-fy-12032024102206.jpg','flyer',2,1,'2024-03-12 10:22:06',NULL,NULL),(290,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','3-logo-14032024020523.png','logo',3,1,'2024-03-14 14:05:23',NULL,NULL),(291,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','4-logo-14032024020607.png','logo',4,1,'2024-03-14 14:06:07',NULL,NULL),(292,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','1-logo-14032024021335.jpg','logo',1,0,'2024-03-14 14:13:35',NULL,'2024-03-14 14:13:42'),(293,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','1-logo-14032024021344.png','logo',1,0,'2024-03-14 14:13:44',NULL,'2024-03-14 14:15:24'),(294,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','1-logo-14032024021527.png','logo',1,0,'2024-03-14 14:15:27',NULL,'2024-03-14 14:15:39'),(295,'C:\\Users\\Bruno Costa\\rsalimentar\\rsalimentar-site/public/app_images/logo/2024/3/14','/app_images/logo/2024/3/14/','1-logo-14032024021543.png','logo',1,0,'2024-03-14 14:15:43',NULL,'2025-07-28 14:00:45'),(296,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','17-brand-18032024110848.jpeg','brand',17,0,'2024-03-18 11:08:48',NULL,'2024-03-18 11:09:49'),(297,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','18-brand-18032024115510.png','brand',18,0,'2024-03-18 11:55:10',NULL,'2024-03-18 11:55:37'),(298,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','18-brand-18032024115613.jpg','brand',18,0,'2024-03-18 11:56:13',NULL,NULL),(299,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','18-brand-18032024115625.jpg','brand',18,1,'2024-03-18 11:56:25',NULL,NULL),(300,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','28-banner-18032024115711.png','banner',28,0,'2024-03-18 11:57:11',NULL,'2024-03-18 11:57:16'),(301,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','28-banner-18032024115717.png','banner',28,1,'2024-03-18 11:57:17',NULL,NULL),(302,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','29-banner-18032024121509.png','banner',29,1,'2024-03-18 12:15:09',NULL,NULL),(303,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','29-banner-18032024121517.jpg','banner',29,1,'2024-03-18 12:15:17',NULL,NULL),(304,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','29-banner-18032024121558.jpg','banner',29,1,'2024-03-18 12:15:58',NULL,NULL),(305,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','30-banner-18032024121635.png','banner',30,0,'2024-03-18 12:16:35',NULL,NULL),(306,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','31-banner-18032024121713.png','banner',31,1,'2024-03-18 12:17:13',NULL,NULL),(307,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','32-banner-18032024121738.jpg','banner',32,0,'2024-03-18 12:17:38',NULL,'2024-03-18 12:17:44'),(308,'/Users/BrunoCosta/rsalimentar-site/public/app_images/banner/2024/3/18','/app_images/banner/2024/3/18/','32-banner-18032024121745.jpg','banner',32,1,'2024-03-18 12:17:45',NULL,NULL),(309,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','19-brand-18032024122146.png','brand',19,0,'2024-03-18 12:21:46',NULL,'2024-03-18 12:24:08'),(310,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','19-brand-18032024122408.jpg','brand',19,1,'2024-03-18 12:24:08',NULL,NULL),(311,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','20-brand-18032024122432.png','brand',20,0,'2024-03-18 12:24:32',NULL,'2024-03-18 12:24:42'),(312,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','20-brand-18032024122443.png','brand',20,1,'2024-03-18 12:24:43',NULL,NULL),(313,'/Users/BrunoCosta/rsalimentar-site/public/app_images/category/2024/3/18','/app_images/category/2024/3/18/','22-category-18032024122901.png','category',22,0,'2024-03-18 12:29:01',NULL,'2024-03-18 12:33:09'),(314,'/Users/BrunoCosta/rsalimentar-site/public/app_images/category/2024/3/18','/app_images/category/2024/3/18/','22-category-18032024123310.jpg','category',22,1,'2024-03-18 12:33:10',NULL,NULL),(315,'/Users/BrunoCosta/rsalimentar-site/public/app_images/category/2024/3/18','/app_images/category/2024/3/18/','23-category-18032024123327.jpg','category',23,0,'2024-03-18 12:33:27',NULL,'2024-03-18 12:33:31'),(316,'/Users/BrunoCosta/rsalimentar-site/public/app_images/category/2024/3/18','/app_images/category/2024/3/18/','23-category-18032024123334.png','category',23,1,'2024-03-18 12:33:34',NULL,NULL),(317,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyer/2024/3/18','/app_images/flyer/2024/3/18/','52-flyer-18032024123542.png','flyer',52,0,'2024-03-18 12:35:42',NULL,'2024-03-18 12:37:42'),(318,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyer/2024/3/18','/app_images/flyer/2024/3/18/','52-flyer-18032024123844.jpg','flyer',52,0,'2024-03-18 12:38:44',NULL,'2024-03-18 12:38:56'),(319,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyer/2024/3/18','/app_images/flyer/2024/3/18/','52-flyer-18032024123857.jpg','flyer',52,1,'2024-03-18 12:38:57',NULL,NULL),(320,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','12-flyerbanner-18032024124052.jpg','flyerbanner',12,0,'2024-03-18 12:40:52',NULL,'2024-03-18 12:42:08'),(321,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','12-flyerbanner-18032024124209.jpg','flyerbanner',12,0,'2024-03-18 12:42:09',NULL,'2024-03-18 12:42:13'),(322,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','12-flyerbanner-18032024124215.png','flyerbanner',12,1,'2024-03-18 12:42:15',NULL,NULL),(323,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','13-flyerbanner-18032024124221.png','flyerbanner',13,0,'2024-03-18 12:42:21',NULL,'2024-03-18 12:42:26'),(324,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','13-flyerbanner-18032024124227.png','flyerbanner',13,1,'2024-03-18 12:42:27',NULL,NULL),(325,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','27-product-18032024124950.png','product',27,0,'2024-03-18 12:49:50',NULL,'2024-03-18 12:52:33'),(326,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','27-product-18032024125234.jpg','product',27,0,'2024-03-18 12:52:34',NULL,NULL),(327,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','27-product-18032024125607.jpg','product',27,0,'2024-03-18 12:56:07',NULL,NULL),(328,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','28-product-18032024125637.png','product',28,0,'2024-03-18 12:56:37',NULL,'2024-03-18 12:56:46'),(329,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','28-product-18032024125647.jpg','product',28,0,'2024-03-18 12:56:47',NULL,NULL),(330,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','28-product-18032024125737.jpg','product',28,1,'2024-03-18 12:57:37',NULL,NULL),(331,'/Users/BrunoCosta/rsalimentar-site/public/app_images/category/2024/3/18','/app_images/category/2024/3/18/','24-category-18032024021920.png','category',24,1,'2024-03-18 14:19:20',NULL,NULL),(332,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','21-brand-18032024021940.jpg','brand',21,1,'2024-03-18 14:19:40',NULL,NULL),(333,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','29-product-18032024022004.jpg','product',29,1,'2024-03-18 14:20:04',NULL,NULL),(334,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyerbanner/2024/3/18','/app_images/flyerbanner/2024/3/18/','14-flyerbanner-18032024022053.png','flyerbanner',14,1,'2024-03-18 14:20:53',NULL,NULL),(335,'/Users/BrunoCosta/rsalimentar-site/public/app_images/flyer/2024/3/18','/app_images/flyer/2024/3/18/','53-flyer-18032024022133.png','flyer',53,1,'2024-03-18 14:21:33',NULL,NULL),(336,'/Users/BrunoCosta/rsalimentar-site/public/app_images/product/2024/3/18','/app_images/product/2024/3/18/','30-product-18032024035751.svg','product',30,1,'2024-03-18 15:57:51',NULL,NULL),(337,'/Users/BrunoCosta/rsalimentar-site/public/app_images/brand/2024/3/18','/app_images/brand/2024/3/18/','22-brand-18032024100624.png','brand',22,1,'2024-03-18 22:06:24',NULL,NULL),(338,'/Users/brunocosta/Desktop/sielaffapp/sielaffapp/public/app_images/logo/2025/7/28','/app_images/logo/2025/7/28/','1-logo-28072025030051.png','logo',1,0,'2025-07-28 14:00:51',NULL,'2025-07-28 20:25:26'),(339,'/Users/brunocosta/Desktop/sielaffproject/public/app_images/logo/2025/7/28','/app_images/logo/2025/7/28/','1-logo-28072025092532.png','logo',1,1,'2025-07-28 20:25:32',NULL,NULL),(340,'/Users/brunocosta/Desktop/sielaffproject/public/app_images/bg/2025/7/30','/app_images/bg/2025/7/30/','1-bg-30072025073729.webp','bg',1,0,'2025-07-30 06:37:29',NULL,'2025-07-30 06:37:53'),(341,'/Users/brunocosta/Desktop/sielaffproject/public/app_images/bg/2025/7/30','/app_images/bg/2025/7/30/','1-bg-30072025073755.jpeg','bg',1,0,'2025-07-30 06:37:55',NULL,'2025-07-30 06:38:29'),(342,'/Users/brunocosta/Desktop/sielaffproject/public/app_images/bg/2025/7/30','/app_images/bg/2025/7/30/','1-bg-30072025073832.jpg','bg',1,1,'2025-07-30 06:38:32',NULL,NULL);
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installations`
--

DROP TABLE IF EXISTS `installations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `team_id` bigint unsigned DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `tipo_servico` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'Pendente',
  `observacoes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `installations_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `installations_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installations`
--

LOCK TABLES `installations` WRITE;
/*!40000 ALTER TABLE `installations` DISABLE KEYS */;
/*!40000 ALTER TABLE `installations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `relation_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_state_id_foreign` (`state_id`),
  KEY `leads_contact_id_foreign` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (1,'Bruno','2024-03-12 14:39:21','2024-03-12 14:44:09',NULL,'product',1,'crcrcr',2,1,9),(2,'Brunoss','2024-03-12 15:23:08','2024-03-12 15:23:08',NULL,NULL,1,'uhig',1,2,NULL),(3,'Lara','2024-03-13 10:13:01','2024-03-13 10:16:09','2024-03-13 10:16:09','contact',1,'aaaa',1,3,NULL),(4,'Lara','2024-03-13 10:16:32','2024-03-13 10:19:04','2024-03-13 10:19:04','contact',1,'ujhjuu',1,3,NULL),(5,'Lara','2024-03-13 10:19:19','2024-03-13 10:19:19',NULL,NULL,1,'qqqq',1,3,NULL),(6,'jose','2024-03-13 10:27:56','2024-03-13 10:27:56',NULL,NULL,1,'aaaaaaaa',1,4,NULL),(7,'COSTA','2024-03-13 11:18:25','2024-03-13 11:18:25',NULL,'contact',1,'EERTYT',1,5,NULL),(8,'Carolina','2024-03-13 11:32:43','2024-03-13 11:32:43',NULL,'contact',1,'querty',1,6,NULL),(9,'joel','2024-03-13 11:35:28','2024-03-13 11:35:28',NULL,'flyer',1,'fffff',1,7,NULL),(10,'Monica','2024-03-13 12:38:59','2024-03-13 12:38:59',NULL,NULL,1,'oooo',1,8,NULL),(11,'Amelia','2024-03-13 14:43:22','2024-03-13 14:43:22',NULL,'flyer',1,'ddddffff',1,9,NULL),(12,'beatriz','2024-03-13 14:57:39','2024-03-13 14:57:39',NULL,'flyer',1,'gthf',1,10,24),(13,'ivone','2024-03-13 16:56:11','2024-03-13 16:58:16','2024-03-13 16:58:16','contact',1,'jjjgghf',1,11,NULL),(14,'Laia','2024-03-14 10:25:13','2024-03-18 16:43:49','2024-03-18 16:43:49','flyer',1,'ssqs',1,12,24),(15,'Joaquim','2024-03-14 10:31:55','2024-03-18 15:01:52','2024-03-18 15:01:52','contact',1,'ssazxwdd',1,13,NULL),(16,'wwdqddwdq','2024-03-14 11:09:19','2024-03-15 15:13:59','2024-03-15 15:13:59','product',1,'dewew',1,14,22),(17,'cdssddewewee','2024-03-15 15:14:26','2024-03-18 14:57:03','2024-03-18 14:57:03','flyer',1,'sdsddfedcrrc',1,15,24),(18,'Bruno Contacto','2024-03-18 16:42:20','2024-03-18 16:42:20',NULL,'flyer',1,'ssddd',1,16,24),(19,'Bruno Flyer','2024-03-18 16:45:48','2024-03-18 16:45:48',NULL,'flyer',1,'addds',1,17,24),(20,'Bruno Produto','2024-03-18 16:47:10','2024-03-18 22:27:09','2024-03-18 22:27:09','product',1,'wdddds',1,18,20);
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_history`
--

DROP TABLE IF EXISTS `leads_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `state_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `leads_history_lead_id_foreign` (`lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_history`
--

LOCK TABLES `leads_history` WRITE;
/*!40000 ALTER TABLE `leads_history` DISABLE KEYS */;
INSERT INTO `leads_history` VALUES (1,1,'<p>Respondido</p>','2024-03-12 14:44:09','2024-03-12 14:44:09',NULL,'1','Admin');
/*!40000 ALTER TABLE `leads_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_events`
--

DROP TABLE IF EXISTS `log_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ipv4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_events`
--

LOCK TABLES `log_events` WRITE;
/*!40000 ALTER TABLE `log_events` DISABLE KEYS */;
INSERT INTO `log_events` VALUES (1,1,'LOGOUT','127.0.0.1','2024-01-08 21:01:23','2024-01-08 21:01:23'),(2,1,'LOGIN','127.0.0.1','2024-01-08 21:01:26','2024-01-08 21:01:26'),(3,1,'LOGOUT','127.0.0.1','2024-01-08 21:03:53','2024-01-08 21:03:53'),(4,1,'LOGIN','127.0.0.1','2024-01-08 21:03:56','2024-01-08 21:03:56'),(5,1,'LOGOUT','127.0.0.1','2024-01-08 21:15:27','2024-01-08 21:15:27'),(6,2,'LOGIN','127.0.0.1','2024-01-08 21:15:39','2024-01-08 21:15:39'),(7,1,'LOGIN','127.0.0.1','2024-01-09 09:41:19','2024-01-09 09:41:19'),(8,1,'LOGOUT','127.0.0.1','2024-01-09 10:02:23','2024-01-09 10:02:23'),(9,1,'LOGIN','127.0.0.1','2024-01-09 10:02:33','2024-01-09 10:02:33'),(10,1,'LOGOUT','127.0.0.1','2024-01-09 10:02:36','2024-01-09 10:02:36'),(11,1,'LOGIN','127.0.0.1','2024-01-09 10:14:31','2024-01-09 10:14:31'),(12,1,'LOGOUT','127.0.0.1','2024-01-09 10:15:02','2024-01-09 10:15:02'),(13,1,'LOGIN','127.0.0.1','2024-01-09 10:16:35','2024-01-09 10:16:35'),(14,1,'LOGOUT','127.0.0.1','2024-01-09 10:23:41','2024-01-09 10:23:41'),(15,1,'LOGIN','127.0.0.1','2024-01-09 10:23:46','2024-01-09 10:23:46'),(16,1,'LOGOUT','127.0.0.1','2024-01-09 10:28:11','2024-01-09 10:28:11'),(17,1,'LOGIN','127.0.0.1','2024-01-09 10:28:18','2024-01-09 10:28:18'),(18,1,'LOGIN','127.0.0.1','2024-01-11 10:49:06','2024-01-11 10:49:06'),(19,1,'LOGIN','127.0.0.1','2024-01-11 15:54:12','2024-01-11 15:54:12'),(20,1,'LOGIN','127.0.0.1','2024-01-12 11:15:27','2024-01-12 11:15:27'),(21,1,'LOGIN','127.0.0.1','2024-01-12 14:10:41','2024-01-12 14:10:41'),(22,1,'LOGIN','127.0.0.1','2024-01-15 09:42:22','2024-01-15 09:42:22'),(23,1,'LOGIN','127.0.0.1','2024-01-15 14:23:29','2024-01-15 14:23:29'),(24,1,'LOGIN','127.0.0.1','2024-01-16 10:00:03','2024-01-16 10:00:03'),(25,1,'LOGIN','127.0.0.1','2024-01-16 15:26:36','2024-01-16 15:26:36'),(26,1,'LOGIN','127.0.0.1','2024-01-16 18:03:39','2024-01-16 18:03:39'),(27,1,'LOGIN','127.0.0.1','2024-01-16 23:24:51','2024-01-16 23:24:51'),(28,1,'LOGIN','127.0.0.1','2024-01-17 00:43:31','2024-01-17 00:43:31'),(29,1,'LOGIN','127.0.0.1','2024-01-17 00:44:04','2024-01-17 00:44:04'),(30,1,'LOGIN','127.0.0.1','2024-01-17 00:45:06','2024-01-17 00:45:06'),(31,1,'LOGIN','127.0.0.1','2024-01-17 09:39:16','2024-01-17 09:39:16'),(32,1,'LOGOUT','127.0.0.1','2024-01-17 09:39:25','2024-01-17 09:39:25'),(33,1,'LOGIN','127.0.0.1','2024-01-17 09:45:01','2024-01-17 09:45:01'),(34,1,'LOGOUT','127.0.0.1','2024-01-17 10:02:14','2024-01-17 10:02:14'),(35,1,'LOGIN','127.0.0.1','2024-01-17 10:05:20','2024-01-17 10:05:20'),(36,1,'LOGIN','127.0.0.1','2024-01-17 14:30:00','2024-01-17 14:30:00'),(37,1,'LOGIN','127.0.0.1','2024-01-17 14:50:16','2024-01-17 14:50:16'),(38,1,'LOGIN','127.0.0.1','2024-01-18 09:51:00','2024-01-18 09:51:00'),(39,1,'LOGOUT','127.0.0.1','2024-01-18 15:29:10','2024-01-18 15:29:10'),(40,1,'LOGIN','127.0.0.1','2024-01-18 15:39:34','2024-01-18 15:39:34'),(41,1,'LOGIN','127.0.0.1','2024-01-19 09:25:47','2024-01-19 09:25:47'),(42,1,'LOGIN','127.0.0.1','2024-01-19 12:15:04','2024-01-19 12:15:04'),(43,1,'LOGOUT','127.0.0.1','2024-01-19 15:33:58','2024-01-19 15:33:58'),(44,1,'LOGIN','127.0.0.1','2024-01-19 15:39:22','2024-01-19 15:39:22'),(45,1,'LOGOUT','127.0.0.1','2024-01-19 16:07:11','2024-01-19 16:07:11'),(46,1,'LOGIN','127.0.0.1','2024-01-19 16:10:50','2024-01-19 16:10:50'),(47,1,'LOGOUT','127.0.0.1','2024-01-19 16:11:02','2024-01-19 16:11:02'),(48,1,'LOGIN','127.0.0.1','2024-01-19 16:11:57','2024-01-19 16:11:57'),(49,1,'LOGIN','127.0.0.1','2024-01-22 09:18:51','2024-01-22 09:18:51'),(50,1,'LOGIN','127.0.0.1','2024-01-22 15:32:51','2024-01-22 15:32:51'),(51,1,'LOGIN','127.0.0.1','2024-01-22 16:04:53','2024-01-22 16:04:53'),(52,1,'LOGIN','127.0.0.1','2024-01-22 20:49:28','2024-01-22 20:49:28'),(53,1,'LOGIN','127.0.0.1','2024-01-23 09:33:10','2024-01-23 09:33:10'),(54,1,'LOGIN','127.0.0.1','2024-01-23 15:44:12','2024-01-23 15:44:12'),(55,1,'LOGIN','127.0.0.1','2024-01-24 14:12:24','2024-01-24 14:12:24'),(56,1,'LOGIN','127.0.0.1','2024-01-25 14:46:25','2024-01-25 14:46:25'),(57,1,'LOGIN','127.0.0.1','2024-01-25 15:36:58','2024-01-25 15:36:58'),(58,1,'LOGOUT','127.0.0.1','2024-01-25 17:09:02','2024-01-25 17:09:02'),(59,1,'LOGIN','127.0.0.1','2024-01-25 17:09:05','2024-01-25 17:09:05'),(60,1,'LOGIN','127.0.0.1','2024-01-26 09:22:31','2024-01-26 09:22:31'),(61,1,'LOGIN','127.0.0.1','2024-01-26 11:03:48','2024-01-26 11:03:48'),(62,1,'LOGIN','127.0.0.1','2024-01-26 11:42:28','2024-01-26 11:42:28'),(63,1,'LOGIN','127.0.0.1','2024-01-26 16:31:12','2024-01-26 16:31:12'),(64,1,'LOGIN','127.0.0.1','2024-01-29 09:30:02','2024-01-29 09:30:02'),(65,1,'LOGOUT','127.0.0.1','2024-01-29 13:36:01','2024-01-29 13:36:01'),(66,1,'LOGIN','127.0.0.1','2024-01-29 13:36:36','2024-01-29 13:36:36'),(67,1,'LOGIN','127.0.0.1','2024-01-29 16:25:31','2024-01-29 16:25:31'),(68,1,'LOGIN','127.0.0.1','2024-01-30 09:36:45','2024-01-30 09:36:45'),(69,1,'LOGIN','127.0.0.1','2024-02-01 15:27:17','2024-02-01 15:27:17'),(70,1,'LOGIN','127.0.0.1','2024-02-14 09:20:22','2024-02-14 09:20:22'),(71,1,'LOGIN','127.0.0.1','2024-02-18 07:44:18','2024-02-18 07:44:18'),(72,1,'LOGOUT','127.0.0.1','2024-02-18 07:47:16','2024-02-18 07:47:16'),(73,1,'LOGIN','127.0.0.1','2024-02-18 07:47:22','2024-02-18 07:47:22'),(74,1,'LOGOUT','127.0.0.1','2024-02-18 08:00:05','2024-02-18 08:00:05'),(75,1,'LOGIN','127.0.0.1','2024-02-20 09:26:54','2024-02-20 09:26:54'),(76,1,'LOGIN','127.0.0.1','2024-02-20 18:01:31','2024-02-20 18:01:31'),(77,1,'LOGIN','127.0.0.1','2024-02-21 11:35:44','2024-02-21 11:35:44'),(78,1,'LOGIN','127.0.0.1','2024-02-27 15:00:52','2024-02-27 15:00:52'),(79,1,'LOGIN','127.0.0.1','2024-02-29 10:05:25','2024-02-29 10:05:25'),(80,1,'LOGIN','127.0.0.1','2024-02-29 23:22:51','2024-02-29 23:22:51'),(81,1,'LOGIN','127.0.0.1','2024-03-01 09:06:11','2024-03-01 09:06:11'),(82,1,'LOGIN','127.0.0.1','2024-03-01 14:55:05','2024-03-01 14:55:05'),(83,1,'LOGIN','127.0.0.1','2024-03-03 10:49:13','2024-03-03 10:49:13'),(84,1,'LOGIN','127.0.0.1','2024-03-03 13:57:52','2024-03-03 13:57:52'),(85,1,'LOGIN','127.0.0.1','2024-03-04 09:01:23','2024-03-04 09:01:23'),(86,1,'LOGIN','127.0.0.1','2024-03-04 19:41:44','2024-03-04 19:41:44'),(87,1,'LOGIN','127.0.0.1','2024-03-05 08:52:50','2024-03-05 08:52:50'),(88,1,'LOGIN','127.0.0.1','2024-03-05 21:28:48','2024-03-05 21:28:48'),(89,1,'LOGIN','127.0.0.1','2024-03-06 08:51:14','2024-03-06 08:51:14'),(90,1,'LOGOUT','127.0.0.1','2024-03-06 14:40:52','2024-03-06 14:40:52'),(91,1,'LOGIN','127.0.0.1','2024-03-06 14:40:55','2024-03-06 14:40:55'),(92,1,'LOGOUT','127.0.0.1','2024-03-06 14:46:03','2024-03-06 14:46:03'),(93,1,'LOGIN','127.0.0.1','2024-03-06 14:55:39','2024-03-06 14:55:39'),(94,1,'LOGIN','127.0.0.1','2024-03-07 09:33:34','2024-03-07 09:33:34'),(95,1,'LOGIN','127.0.0.1','2024-03-08 08:55:23','2024-03-08 08:55:23'),(96,1,'LOGIN','127.0.0.1','2024-03-08 22:30:49','2024-03-08 22:30:49'),(97,1,'LOGIN','127.0.0.1','2024-03-11 08:49:38','2024-03-11 08:49:38'),(98,1,'LOGIN','127.0.0.1','2024-03-11 14:59:44','2024-03-11 14:59:44'),(99,1,'LOGIN','127.0.0.1','2024-03-11 21:15:41','2024-03-11 21:15:41'),(100,1,'LOGIN','127.0.0.1','2024-03-12 10:09:21','2024-03-12 10:09:21'),(101,1,'LOGIN','127.0.0.1','2024-03-13 08:50:08','2024-03-13 08:50:08'),(102,1,'LOGIN','127.0.0.1','2024-03-14 09:09:55','2024-03-14 09:09:55'),(103,1,'LOGIN','127.0.0.1','2024-03-14 09:10:02','2024-03-14 09:10:02'),(104,1,'LOGIN','127.0.0.1','2024-03-15 09:33:06','2024-03-15 09:33:06'),(105,1,'LOGIN','127.0.0.1','2024-03-17 23:35:54','2024-03-17 23:35:54'),(106,1,'LOGIN','127.0.0.1','2024-03-17 23:38:04','2024-03-17 23:38:04'),(107,1,'LOGIN','127.0.0.1','2024-03-18 11:04:59','2024-03-18 11:04:59'),(108,1,'LOGIN','127.0.0.1','2024-03-18 21:56:47','2024-03-18 21:56:47'),(109,2,'LOGIN','127.0.0.1','2025-07-12 22:07:30','2025-07-12 22:07:30'),(110,2,'LOGOUT','127.0.0.1','2025-07-12 22:08:03','2025-07-12 22:08:03'),(111,1,'LOGIN','127.0.0.1','2025-07-12 22:08:15','2025-07-12 22:08:15'),(112,1,'LOGIN','127.0.0.1','2025-07-13 09:42:08','2025-07-13 09:42:08'),(113,1,'LOGIN','127.0.0.1','2025-07-14 20:40:03','2025-07-14 20:40:03'),(114,1,'LOGIN','127.0.0.1','2025-07-28 13:59:33','2025-07-28 13:59:33'),(115,3,'LOGIN','127.0.0.1','2025-07-28 14:19:11','2025-07-28 14:19:11'),(116,3,'LOGIN','127.0.0.1','2025-07-28 19:41:26','2025-07-28 19:41:26'),(117,3,'LOGIN','127.0.0.1','2025-07-28 20:25:11','2025-07-28 20:25:11'),(118,3,'LOGIN','127.0.0.1','2025-07-29 06:08:22','2025-07-29 06:08:22'),(119,3,'LOGIN','127.0.0.1','2025-07-29 14:00:07','2025-07-29 14:00:07'),(120,3,'LOGIN','127.0.0.1','2025-07-29 17:11:05','2025-07-29 17:11:05'),(121,3,'LOGIN','127.0.0.1','2025-07-29 21:42:56','2025-07-29 21:42:56'),(122,3,'LOGOUT','127.0.0.1','2025-07-30 00:47:13','2025-07-30 00:47:13'),(123,3,'LOGIN','127.0.0.1','2025-07-30 00:49:36','2025-07-30 00:49:36'),(124,3,'LOGIN','127.0.0.1','2025-07-30 06:35:41','2025-07-30 06:35:41'),(125,3,'LOGOUT','127.0.0.1','2025-07-30 06:37:35','2025-07-30 06:37:35'),(126,3,'LOGIN','127.0.0.1','2025-07-30 06:37:45','2025-07-30 06:37:45'),(127,3,'LOGOUT','127.0.0.1','2025-07-30 06:37:57','2025-07-30 06:37:57'),(128,3,'LOGIN','127.0.0.1','2025-07-30 06:38:05','2025-07-30 06:38:05'),(129,3,'LOGOUT','127.0.0.1','2025-07-30 06:38:33','2025-07-30 06:38:33'),(130,3,'LOGIN','127.0.0.1','2025-07-30 06:38:43','2025-07-30 06:38:43'),(131,3,'LOGOUT','127.0.0.1','2025-07-30 07:23:22','2025-07-30 07:23:22'),(132,3,'LOGIN','127.0.0.1','2025-07-30 07:23:36','2025-07-30 07:23:36'),(133,3,'LOGIN','127.0.0.1','2025-07-30 12:34:39','2025-07-30 12:34:39');
/*!40000 ALTER TABLE `log_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_activities`
--

DROP TABLE IF EXISTS `login_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_activities`
--

LOCK TABLES `login_activities` WRITE;
/*!40000 ALTER TABLE `login_activities` DISABLE KEYS */;
INSERT INTO `login_activities` VALUES (55,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-18 07:44:18',NULL),(56,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-18 07:47:22',NULL),(57,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-20 09:26:54',NULL),(58,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-20 18:01:31',NULL),(59,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-21 11:35:44',NULL),(60,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15','127.0.0.1','2024-02-27 15:00:52',NULL),(61,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-02-29 10:05:25',NULL),(62,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-02-29 23:22:50',NULL),(63,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-01 09:06:11',NULL),(64,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-01 14:55:05',NULL),(65,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-03 10:49:13',NULL),(66,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-03 13:57:52',NULL),(67,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-04 09:01:22',NULL),(68,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-04 19:41:43',NULL),(69,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-05 08:52:50',NULL),(70,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-05 21:28:48',NULL),(71,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-06 08:51:14',NULL),(72,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-06 14:40:55',NULL),(73,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-06 14:55:39',NULL),(74,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-07 09:33:34',NULL),(75,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-08 08:55:22',NULL),(76,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-08 22:30:49',NULL),(77,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-11 08:49:38',NULL),(78,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-11 14:59:44',NULL),(79,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-11 21:15:41',NULL),(80,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-12 10:09:21',NULL),(81,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-13 08:50:08',NULL),(82,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-14 09:09:55',NULL),(83,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-14 09:10:02',NULL),(84,1,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36','127.0.0.1','2024-03-15 09:33:06',NULL),(85,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Safari/605.1.15','127.0.0.1','2024-03-17 23:35:54',NULL),(86,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Safari/605.1.15','127.0.0.1','2024-03-17 23:38:04',NULL),(87,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Safari/605.1.15','127.0.0.1','2024-03-18 11:04:59',NULL),(88,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Safari/605.1.15','127.0.0.1','2024-03-18 21:56:47',NULL),(89,2,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-12 22:07:30',NULL),(90,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-12 22:08:15',NULL),(91,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-13 09:42:08',NULL),(92,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-14 20:40:02',NULL),(93,1,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-28 13:59:33',NULL),(94,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-28 14:19:11',NULL),(95,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-28 19:41:26',NULL),(96,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-28 20:25:11',NULL),(97,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-29 06:08:22',NULL),(98,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-29 14:00:07',NULL),(99,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-29 17:11:05',NULL),(100,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-29 21:42:56',NULL),(101,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 00:49:36',NULL),(102,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 06:35:41',NULL),(103,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 06:37:45',NULL),(104,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 06:38:05',NULL),(105,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 06:38:43',NULL),(106,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 07:23:36',NULL),(107,3,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','127.0.0.1','2025-07-30 12:34:39',NULL);
/*!40000 ALTER TABLE `login_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `machines`
--

DROP TABLE IF EXISTS `machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `machines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelo` varchar(255) NOT NULL,
  `numero_serie` varchar(255) NOT NULL,
  `data_recebimento` date NOT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_serie` (`numero_serie`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `machines`
--

LOCK TABLES `machines` WRITE;
/*!40000 ALTER TABLE `machines` DISABLE KEYS */;
INSERT INTO `machines` VALUES (1,'DualSort','90560943','2025-08-08',NULL,'2025-07-30 02:55:08','2025-07-30 02:55:08',NULL);
/*!40000 ALTER TABLE `machines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2023_02_06_165059_laratrust_setup_tables',1),(3,'2023_02_17_132813_create_configurations_table',1),(4,'2023_02_17_134321_create_failed_jobs_table',1),(5,'2023_02_17_134623_create_images_table',1),(6,'2023_02_17_140545_create_log_events_table',1),(7,'2023_02_17_143404_create_aboutus_table',1),(8,'2023_02_17_143404_create_banners_table',1),(9,'2023_02_17_143404_create_slideshows_table',1),(10,'2023_02_17_143404_create_teams_table',1),(11,'2023_02_17_143404_create_users_table',1),(12,'2023_02_17_143404_create_wines_table',1),(13,'2023_02_17_144843_create_login_activities_table',1),(14,'2023_02_17_145658_create_password_resets_table',1),(15,'2024_01_05_164904_create_datasheets_table',1),(16,'2024_01_05_164915_create_datasheetgroups_table',1),(17,'2024_01_05_165000_create_datasheetsubgroups_table',1),(18,'2024_01_05_164904_create__datasheets_table',2),(19,'2024_01_05_164915_create__datasheet_groups_table',2),(20,'2024_01_05_165000_create__datasheet_sub_groups_table',2),(21,'2024_01_11_152144_add_image_id_to_datasheets_table',3),(22,'2024_01_18_100713_create_contacts_table',4),(23,'2024_01_18_110547_create_clients_table',5),(24,'2024_01_18_110821_create_contacts_table',6),(25,'2024_01_18_111330_create_leads_table',7),(26,'2024_01_18_111620_create_leads_history_table',8),(27,'2024_01_18_114808_add_deleted_at_to_contacts_table',9),(28,'2024_01_18_114809_add_deleted_at_to_contacts_table',10),(29,'2024_01_18_125342_add_deleted_at_to_clients_table',11),(30,'2024_01_18_163610_add_descrption1_to_datasheets_table',11),(31,'2024_01_19_142332_add_reply_to_leads_history',12),(32,'2024_01_19_152620_change_state_column_in_leads_history_table',13),(33,'2024_01_24_104400_create_return_text_table',14),(34,'2024_01_25_151500_add_camps_to_wines_table',15),(35,'2024_01_25_152126_add_sugar_to_wines_table',16),(36,'2024_01_25_172001_add_text_to_abouts_table',17),(37,'2024_01_26_095556_add_response_text_en_to_return_text_table',18),(38,'2024_01_26_122229_add_image_id_to_abouts_table',19),(39,'2024_01_26_142033_create_intro_table',20),(40,'2024_01_26_144916_add_image_id_to_teams_table',21),(41,'2024_01_26_161848_add_text_teams_to_intro_table',22),(42,'2024_01_26_170023_add_image_id_to_slideshows_table',23),(43,'2024_01_29_105356_add_type_to_slideshows_table',24),(44,'2024_02_18_075600_create_customer_table',25),(45,'2024_02_27_191315_create_products_table',26),(46,'2024_02_27_191343_create_brands_table',26),(47,'2024_02_27_191348_create_categories_table',26),(48,'2024_02_27_191406_create_flyers_table',26);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `new_links`
--

DROP TABLE IF EXISTS `new_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `new_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title_pt` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `link` longtext,
  `sort` int DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `new_links`
--

LOCK TABLES `new_links` WRITE;
/*!40000 ALTER TABLE `new_links` DISABLE KEYS */;
INSERT INTO `new_links` VALUES (1,'Final do Ano!!','Last Year!','http://127.0.0.1:8000/flyer/aniversario',1,1,'2024-03-15 11:06:14','2024-03-18 11:39:50',NULL),(2,'Ultima Semana','Last Week','http://127.0.0.1:8000/flyer/black-friday',2,1,'2024-03-15 11:41:27','2024-03-18 11:40:39',NULL),(3,'ddsdsaaaa','sdsd','http://127.0.0.1:8000/newslinks/newslinks/create',3,1,'2024-03-18 12:44:48','2024-03-18 12:45:45','2024-03-18 12:45:49'),(4,'sdaasddsa','sddsas','sadsaa',3,1,'2024-03-18 12:45:54','2024-03-18 12:45:54','2024-03-18 12:45:59');
/*!40000 ALTER TABLE `new_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `descricao` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `quantidade` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts`
--

LOCK TABLES `parts` WRITE;
/*!40000 ALTER TABLE `parts` DISABLE KEYS */;
INSERT INTO `parts` VALUES (1,'Sensor','AZDA','refletor para DualSort','2025-07-30 03:05:18','2025-07-30 03:08:43',NULL,13);
/*!40000 ALTER TABLE `parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `permission_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(1,3),(2,3),(3,3),(4,3),(5,3),(6,3),(7,3),(8,3),(9,3),(10,3);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_user`
--

DROP TABLE IF EXISTS `permission_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_user` (
  `permission_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `user_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`,`user_type`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_user`
--

LOCK TABLES `permission_user` WRITE;
/*!40000 ALTER TABLE `permission_user` DISABLE KEYS */;
INSERT INTO `permission_user` VALUES (1,1,'App\\User'),(2,1,'App\\User'),(3,1,'App\\User'),(4,1,'App\\User'),(5,1,'App\\User'),(6,1,'App\\User'),(7,1,'App\\User'),(8,1,'App\\User'),(9,1,'App\\User'),(10,1,'App\\User');
/*!40000 ALTER TABLE `permission_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_leftmenu','View Left Menu','Ver menu de acesso à esquerda.',1,NULL,NULL,NULL),(2,'view_users','View Users','Ver utilizadores.',1,NULL,NULL,NULL),(3,'view_roles','View Roles','Ver cargos.',1,NULL,NULL,NULL),(4,'view_loginactivity','View Login Activity','Ver atividades de login.',1,NULL,NULL,NULL),(5,'view_permissions','View Permissions','Ver permissões.',1,NULL,NULL,NULL),(6,'view_configurations','View Configurations','Ver configurações.',1,NULL,NULL,NULL),(7,'manage_users','Manage Users','Gerenciar utilizadores.',1,NULL,NULL,NULL),(8,'manage_roles','Manage Roles','Gerenciar cargos.',1,NULL,NULL,NULL),(9,'manage_permissions','Manage Permissions','Gerenciar permissões.',1,NULL,NULL,NULL),(10,'manage_configurations','Manage Configurations','Gerenciar configurações.',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_pt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_pt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_id` bigint DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category_id` bigint NOT NULL,
  `brand_id` bigint NOT NULL,
  `slug` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'cdssd','dqd','sdfwef','scqew',NULL,NULL,0,0,'','2024-03-04 15:06:55','2024-03-04 15:06:55','2024-03-04 15:13:19',1,NULL),(2,'sdcsdc','xcas','csac','scac',NULL,NULL,0,0,'-1','2024-03-04 15:13:30','2024-03-04 15:13:30','2024-03-04 15:16:26',1,NULL),(3,'wqcwwacq','sxqsx','saxc','asxsx',NULL,NULL,0,0,'wqcwwacq','2024-03-04 15:16:44','2024-03-04 15:16:44','2024-03-04 15:21:55',1,NULL),(4,'dwedewd','eedqdf','ewde','ewd',NULL,NULL,0,0,'dwedewd','2024-03-04 15:22:06','2024-03-04 15:22:06','2024-03-04 15:24:09',1,NULL),(5,'de','ds','cds','c',NULL,NULL,0,0,'de','2024-03-04 15:24:18','2024-03-04 15:24:18','2024-03-04 15:28:11',1,NULL),(6,'qwdeefrf','fdsf','dwf','safa',176,NULL,0,0,'qwdeefrf','2024-03-04 15:28:21','2024-03-04 15:28:21','2024-03-04 15:34:14',1,NULL),(7,'sdcwecew','fasdf','<p><b>safsffasf  </b>  dvfwas</p>','<p>cdscdcqwdqdd</p>',177,NULL,0,0,'sdcwecew','2024-03-04 15:34:34','2024-03-04 15:35:17','2024-03-04 15:36:28',1,NULL),(8,'erer','dewd','edd','ded',178,NULL,0,0,'erer','2024-03-04 15:36:41','2024-03-04 15:36:41','2024-03-04 15:40:25',1,NULL),(9,'Lata Coca Cola 1','Lata Coca Cola 1','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',250,32.60,10,2,'lata-coca-cola-1','2024-03-04 15:40:36','2024-03-15 13:56:28',NULL,1,NULL),(10,'Lata Coca Cola 2','Lata Coca Cola 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',274,20.00,10,3,'lata-coca-cola-2','2024-03-05 14:59:08','2024-03-11 09:27:30',NULL,1,NULL),(11,'Lata Coca Cola 3','Lata Coca Cola 3','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',251,3.00,10,4,'lata-coca-cola-3','2024-03-05 23:34:40','2024-03-07 13:39:19',NULL,1,NULL),(12,'tg4t','rg','trg','rg4',201,11.00,0,0,'tg4t','2024-03-06 09:39:55','2024-03-06 09:40:03','2024-03-06 09:40:11',1,NULL),(13,'scsdc','czcsd','csdc','dsc',213,23.00,0,0,'scsdc','2024-03-06 15:54:15','2024-03-06 15:54:27','2024-03-07 11:31:01',1,NULL),(14,'aaaabbb','aaaa','aaaxa','aaas',217,11.00,0,0,'aaaa','2024-03-06 16:14:20','2024-03-06 16:14:37','2024-03-06 16:14:45',1,NULL),(15,'Lata Coca Cola 4','Lata Coca Cola 4','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',252,3.00,10,5,'lata-coca-cola-4','2024-03-07 11:37:05','2024-03-07 13:38:18',NULL,1,NULL),(16,'Lata Coca Cola 5','Lata Coca Cola 5','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco',253,2.00,10,6,'lata-coca-cola-5','2024-03-07 11:42:23','2024-03-07 13:38:58',NULL,1,NULL),(17,'asas','sasa','s','as',254,2.00,10,2,'asas','2024-03-07 13:53:38','2024-03-07 13:53:38','2024-03-07 13:54:24',1,NULL),(18,'we','ee',NULL,NULL,NULL,2.00,10,2,'we','2024-03-07 14:06:30','2024-03-07 14:06:30','2024-03-07 14:06:34',1,NULL),(19,'ddf','dfss',NULL,NULL,NULL,1.00,15,2,'ddf','2024-03-07 14:06:53','2024-03-07 14:06:53','2024-03-07 14:13:27',1,NULL),(20,'Seven Up','seven up',NULL,NULL,275,2.00,10,2,'seven-up','2024-03-08 16:07:51','2024-03-11 10:36:04',NULL,0,NULL),(21,'mjcdbjisdcd','ecfe',NULL,NULL,267,1.00,15,2,'mjcdbjisdcd','2024-03-08 16:21:51','2024-03-08 16:21:51','2024-03-11 09:11:58',1,NULL),(22,'Lixivia Tradicional','Lixivia tradicional','Neoblanc tradicional garante proteção para a sua casa e para a sua roupa. Graças à tecnologia exclusiva protege fibra, envolve as fibras e protege-as durante a lavagem, ajudando a manter as peças como novas! Além disso, higieniza e remove mais de 90% dos alergénios de pólen, poeira, ácaros e animais domésticos.','Neoblanc tradicional garante proteção para a sua casa e para a sua roupa. Graças à tecnologia exclusiva protege fibra, envolve as fibras e protege-as durante a lavagem, ajudando a manter as peças como novas! Além disso, higieniza e remove mais de 90% dos alergénios de pólen, poeira, ácaros e animais domésticos.',269,3.00,17,7,'lixivia-tradicional','2024-03-11 09:12:52','2024-03-18 22:16:58',NULL,0,NULL),(23,'Pasta de dentes Oral-B Pro-Expert Protecção Profissional','Pasta de dentes Oral-B Pro-Expert Protecção Profissional','O dentífrico Pro-Expert de Proteção Profissional com sabor a menta fresca da Oral-B, desenvolvido pelos especialistas da Oral-B, protege a boca dos 8 problemas que se tratam com mais frequência nas consultas com os dentistas.','O dentífrico Pro-Expert de Proteção Profissional com sabor a menta fresca da Oral-B, desenvolvido pelos especialistas da Oral-B, protege a boca dos 8 problemas que se tratam com mais frequência nas consultas com os dentistas.',271,2.00,19,8,'pasta-de-dentes-oral-b-pro-expert-proteccao-profissional','2024-03-11 09:16:56','2024-03-11 09:16:56',NULL,1,NULL),(24,'Croissant','Croissant',NULL,NULL,273,1.00,15,9,'croissant','2024-03-11 09:25:15','2024-03-11 09:25:15',NULL,1,NULL),(25,'Limpa Vidros Spray Tripla Ação','Limpa Vidros Spray Tripla Ação',NULL,NULL,276,2.00,17,10,'limpa-vidros-spray-tripla-acao','2024-03-11 09:30:37','2024-03-11 09:30:37',NULL,1,NULL),(26,'Refrigerantesss','sdfwf','as','as',NULL,3.14,10,3,'refrigerantesss','2024-03-15 13:50:04','2024-03-15 13:50:04','2024-03-15 13:50:15',1,NULL),(27,'ffdfdsaaaa','dsfsfdsf',NULL,NULL,27,12.00,17,7,'ffdfds','2024-03-18 12:49:50','2024-03-18 22:16:58','2024-03-18 12:56:41',0,NULL),(28,'aaaaa','aaa','aa',NULL,330,12.00,17,8,'aaaaa','2024-03-18 12:56:37','2024-03-18 12:57:37','2024-03-18 12:57:47',1,NULL),(29,'teste produto','sd','s',NULL,333,10.00,24,21,'teste-produto','2024-03-18 14:20:04','2024-03-18 14:20:04','2024-03-18 22:14:23',1,NULL),(30,'testessss','rtrtrt',NULL,NULL,336,NULL,24,9,'teste','2024-03-18 15:57:51','2024-03-18 16:28:29','2024-03-18 22:14:20',1,'sssss'),(31,'4544545','r34','dffvvv','v',NULL,40.00,15,5,'4544545','2025-07-13 09:42:34','2025-07-13 09:42:34',NULL,1,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_text`
--

DROP TABLE IF EXISTS `return_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_text` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `response_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `response_text_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_text`
--

LOCK TABLES `return_text` WRITE;
/*!40000 ALTER TABLE `return_text` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_text` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `role_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `user_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AppModelsUser',
  PRIMARY KEY (`user_id`,`role_id`,`user_type`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1,'App\\Models\\User'),(1,3,'App\\Models\\User'),(2,2,'App\\Models\\User');
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Admin','Permissões para ver e gerenciar o sistema.',1,NULL,NULL,NULL),(2,'user','User','Permissões para visualização dos registos no sistema.',1,NULL,NULL,NULL),(3,'profissionalSaude','Profissional Saúde',NULL,1,'2024-02-20 09:30:14','2024-02-20 09:30:14',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `desc` varchar(155) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `states`
--

LOCK TABLES `states` WRITE;
/*!40000 ALTER TABLE `states` DISABLE KEYS */;
INSERT INTO `states` VALUES (1,'Não lido','2024-03-12 14:40:27','2024-03-12 14:40:27',NULL,'Por ler'),(2,'LIdo','2024-03-12 14:42:49','2024-03-12 14:42:49',NULL,NULL);
/*!40000 ALTER TABLE `states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `regiao` varchar(100) NOT NULL,
  `codigo_loja` varchar(50) NOT NULL,
  `nome_loja` varchar(255) NOT NULL,
  `morada` text,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stores`
--

LOCK TABLES `stores` WRITE;
/*!40000 ALTER TABLE `stores` DISABLE KEYS */;
INSERT INTO `stores` VALUES (1,'LOU','472','Sacavém','Qt.ªS.João das Areias','2685-012','2025-07-28 15:47:01','2025-07-29 18:13:32',NULL),(2,'LOU','483','Sintra - Casal de Cambra','Av. De Lisboa','2605-004','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(3,'LOU','562','Sintra - Mem Martins','Estrada Nacional 249, Km 14,700','2725-397','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(4,'LOU','119','Amadora - Venteira','Rua de Angola 13','2700-058','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(5,'PAL','369','Grândola','Rua D. Álvares Pereira','7570-239','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(6,'PAL','490','Barreiro - Quinta das Canas','Av. Escola dos Fuzileiros Navais, Quinta das Canas','2830-239','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(7,'TON','588','Vieira de Leiria','Rua da Marinha Grande 4','2430-666','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(8,'TON','608','Leiria - Paulo VI','Rua Paulo VI','2415-614','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(9,'SAN','619','Matosinhos Perafita','Rua Silva Aroso','4455-559','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(10,'SAN','281','Sever do Vouga','Rua da APCDI','3740-115','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(11,'SAN','263','Valença','Av. Dr. Tito Fontes - Ponte','4930-676','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(12,'LOU','552','Amadora - Venda Nova','Rua João de Deus nº 5','2700-486','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(13,'LOU','448','Cascais - Torre','Rua da Torre, nº 831','2750-269','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(14,'LOU','569','Oeiras - Área de Serviço','A5, Km 10.1','2780-000','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(15,'PAL','360','Almada-Sobreda','Rua Quinta do Salgado, Nº14','2815-756','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(16,'PAL','352','Alcochete','Lagoa do Laparo','2890-112','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(17,'PAL','370','Setúbal-Estr. de Algeruz','Estrada de Algeruz','2910-279','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(18,'TON','412','Penacova','Rua das Escolas','3360-191','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(19,'TON','414','Entroncamento','Rua Dr. Francisco Sá Carneiro','2330-148','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(20,'TON','430','Soure','Lugar de Fujaco','3130-200','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(21,'SAN','584','Arcos de Veldevez','Lugar do Souto - EN 101 - Paçô','4970-243','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(22,'SAN','565','Viseu Sátão','Av Prof. Reinaldo Cardoso','3500-188','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(23,'SAN','230','Paredes','Rua D. Maria Sotto Mayor Meneses','4580-146','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(24,'LOU','486','Casal Ribeiro','Avenida Casal Ribeiro, N.º 18','1000-092','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(25,'LOU','502','Loures Frielas','EN250, Frielas','2660-001','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(26,'LOU','618','Sintra Cacém','Rua da Bela Vista - Alto da Belavista - Casal do Cotão, 31','2735-184','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(27,'PAL','541','Armação de Pêra','Panasqueira','8364-138','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(28,'PAL','371','Santiago do Cacém','Rua do Mercado, ZIL 1','7500-220','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(29,'PAL','612','Faro','Rua Eng. Nuno Abecassis','8000-548','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(30,'TON','137','Alcobaça','Estrada de Leiria','2460-059','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(31,'TON','467','Figueira da Foz','Rua Celulose de Billerud','3080-030','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(32,'TON','247','Mangualde','Prova - Av. dos Combatentes Grande Guerra','3530-261','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(33,'SAN','285','Mirandela','Lugar Quinta Branca','5370-346','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(34,'SAN','549','Porto AEP','Estrada Interior da Circunvalação 13941 a 13975','4100-179','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(35,'SAN','599','Vale de Cambra','Av. Dr. António Fonseca 1990','3730-250','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(36,'LOU','435','Loures Moscavide','M. Oriente, Lj.B2.01, Av. Moscavide','1885-075','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(37,'LOU','444','Alenquer','Variante Álvaro Pedro','2580-351','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(38,'LOU','190','Av. Lusíada','R. Xavier Araujo/Av.Lusiada BL4,12D','1600-226','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(39,'PAL','497','Seixal Fernão Ferro','Av. 10 de Junho','2865-600','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(40,'LOU','362','Seixal Miratejo','Rua Ferreira de Castro','2855-238','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(41,'PAL','463','Silves','Rua Cruz da Palmeira','8300-010','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(42,'TON','168','Santarém-Av. Bernardo Santareno','Av. Bernardo Santareno','2005-177','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(43,'TON','571','Oliveira do Hospital','Rua Dr. João Afonso Ferreira Dinis','3400-173','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(44,'TON','545','Cartaxo','Rua do Progresso nº 2','2070-085','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(45,'TON','114','Abrantes','Av. D. João I Vale Rãs','2200-233','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(46,'SAN','282','Póvoa do Lanhoso','Rua Pôça de Chidelas','4830-598','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(47,'SAN','291','Macedo de Cavaleiros','Rua Viriato Martins','5340-281','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(48,'SAN','494','Gondomar Rio Tinto','Av. Dr. Domingos Gonçalves de Sá, 381, 4435-123','4435-123','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(49,'SAN','418','São joão da Madeira','Av. Dr. Renato Araújo, nº865','3700-008','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(50,'SAN','636','Aveiro Verdemilho','','3800-384','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(51,'LOU','472','Sacavém','Qt.ª S. João das Areias','2685-012','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(52,'LOU','185','Agualva Cacém','R. Ant. Antunes Martins de Oliveira','2735-535','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(53,'TON','534','Arganil','Rua Mariano Lopes Morgado, n.º 360','3300-060','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(54,'SAN','422','Peso da Régua','Av. Sacadura Cabral','5050-071','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(55,'TON','620','Alcains','Estrada de São Domingos','6005-010','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(56,'TON','241','Guarda','Rua Vila de Manteigas','6300-617','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(57,'TON','411','Ourém','Av. D. Nuno Álvares Pereira, nº 351','2490-483','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(58,'PAL','346','Odemira','E.N. 393 - Portas do Transval','7630-208','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(59,'SAN','277','Matosinhos - Leça da Palmeira','Rua da Bataria, 45','4450-800','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(60,'SAN','482','Porto - Diogo Botelho','Rua Diogo Botelho, nº 440/ Rua da Pasteleira nº 161','4150-586','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(61,'SAN','451','Gondomar - S. Cosme','Rua Eng.º Álvaro de Sousa','4420-230','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(62,'SAN','214','Santo Tirso','Rua do Jornal de Stº Tirso - Lugar de Vilalva','4780-563','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(63,'SAN','423','Porto - Camilo','Av. Camilo, Nº 26/52','4300-095','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(64,'TON','409','Alcobaça - Benedita','Estrada Nacional 8/6','2475-022','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(65,'TON','403','Miranda do Corvo','Rua 25 de Abril','3220-185','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(66,'TON','157','Salvaterra de Magos','Estrada Nacional 118','2120-066','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(67,'TON','406','Santa Comba Dão','Av. de Santo Estevão','3440-323','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(68,'SAN','500','Fernão Magalhães','Avenida Fernão de Magalhães, 897-1009','4350-167','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(69,'SAN','576','São João Madeira - Oliveira Junior Arrifana','Rua Oliveira Junior 680 - São João da Madeira','3700-206','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL),(70,'LOU','539','Massamá Norte','Rua D. Matilde, Lote 177-179','2605-246','2025-07-28 17:03:46','2025-07-28 17:03:46',NULL);
/*!40000 ALTER TABLE `stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nif` varchar(50) DEFAULT NULL,
  `morada` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Lusocargo','2299909007','geral@lusocargo.pt','5015082790','Rua Joaquim Dias Salguerios','2025-07-28 14:02:58','2025-07-29 17:14:41',NULL),(2,'DHL','2299909007','geral@dhl.pt','5015082790','Alfena','2025-07-29 10:00:14','2025-07-29 10:00:14','2025-07-30 08:09:34'),(3,'Chronopost',NULL,NULL,NULL,NULL,'2025-07-30 01:24:54','2025-07-30 01:24:54','2025-07-30 01:25:02');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_schedule_user`
--

DROP TABLE IF EXISTS `task_schedule_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_schedule_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `task_schedule_id` int NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_task_schedule_user_schedule` (`task_schedule_id`),
  KEY `fk_task_schedule_user_user` (`user_id`),
  CONSTRAINT `fk_task_schedule_user_schedule` FOREIGN KEY (`task_schedule_id`) REFERENCES `task_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_schedule_user_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_schedule_user`
--

LOCK TABLES `task_schedule_user` WRITE;
/*!40000 ALTER TABLE `task_schedule_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_schedule_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_schedules`
--

DROP TABLE IF EXISTS `task_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `data_limite` date DEFAULT NULL,
  `hora_limite` time DEFAULT NULL,
  `prioridade` enum('Baixa','Média','Alta') DEFAULT 'Baixa',
  `activa` tinyint(1) DEFAULT '1',
  `grupo` tinyint(1) DEFAULT '0',
  `repetir` tinyint(1) DEFAULT '0',
  `estado` enum('Pendente','Visualizada','Concluída') DEFAULT 'Pendente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `fk_user_task_schedule` (`user_id`),
  CONSTRAINT `fk_user_task_schedule` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_schedules_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_schedules`
--

LOCK TABLES `task_schedules` WRITE;
/*!40000 ALTER TABLE `task_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_user`
--

DROP TABLE IF EXISTS `team_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `team_user_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_user`
--

LOCK TABLES `team_user` WRITE;
/*!40000 ALTER TABLE `team_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `contacto` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `observacoes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'G:Hofle',NULL,NULL,NULL,'2025-07-29 08:42:56','2025-07-29 08:47:38','2025-07-29 08:47:38'),(2,'G:Hofle','2299909007','geral@g:hofle.pt','Sanderson e João','2025-07-29 08:50:01','2025-07-29 08:50:01',NULL),(3,'Tecnico Bruno Sielaff','910041440','Bruno.costa@sielaff.pt',NULL,'2025-07-29 09:59:39','2025-07-30 01:28:27','2025-07-30 01:28:27');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `technical_requests`
--

DROP TABLE IF EXISTS `technical_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `technical_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned DEFAULT NULL,
  `origem` varchar(255) NOT NULL,
  `descricao_problema` text NOT NULL,
  `prioridade` enum('baixa','media','alta') DEFAULT 'media',
  `estado` enum('pendente','agendado','concluido','cancelado') DEFAULT 'pendente',
  `observacoes` text,
  `data_pedido` date NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_technical_requests_store` (`store_id`),
  CONSTRAINT `fk_technical_requests_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `technical_requests`
--

LOCK TABLES `technical_requests` WRITE;
/*!40000 ALTER TABLE `technical_requests` DISABLE KEYS */;
INSERT INTO `technical_requests` VALUES (1,38,'HotLine','Máquina não funciona','media','agendado','Passar o masi rápidamente possivel','2025-07-30','2025-07-30 03:30:17','2025-07-30 04:07:44',NULL),(2,4,'HotLine','aaa','media','agendado','aaa','2025-07-30','2025-07-30 03:53:36','2025-07-30 03:53:36',NULL),(3,56,'Ricardo Gomes Lidl','assa','media','concluido',NULL,'2025-07-30','2025-07-30 04:00:50','2025-07-30 08:21:07',NULL),(4,33,'HotLine','Faz barulho','alta','agendado',NULL,'2025-07-30','2025-07-30 04:01:23','2025-07-30 04:01:30',NULL);
/*!40000 ALTER TABLE `technical_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `technical_schedules`
--

DROP TABLE IF EXISTS `technical_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `technical_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `technical_request_id` int NOT NULL,
  `equipa_id` int DEFAULT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `local` varchar(255) DEFAULT NULL,
  `estado` enum('planeado','concluido','reagendado','falhado') DEFAULT 'planeado',
  `notas` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `technical_request_id` (`technical_request_id`),
  CONSTRAINT `technical_schedules_ibfk_1` FOREIGN KEY (`technical_request_id`) REFERENCES `technical_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `technical_schedules`
--

LOCK TABLES `technical_schedules` WRITE;
/*!40000 ALTER TABLE `technical_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `technical_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pt',
  `role_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','support@develop2you.com',NULL,'$2y$10$GZK0WUpF/1cp6sgIxJbUyewNgyaaixVTj9mrZ5fW3iijbLEw8xpSW','2025-07-28 13:59:33',1,NULL,'2025-07-28 15:07:48','en',1,'2024-02-20 18:14:00','2025-07-28 14:07:48',NULL),(2,'User','user@teste.com',NULL,'$2y$10$NEmruEVwmrCC1UXanNPL1.YBmf1oA6N0WPpM4bn9TyBwX37vKhMb2','2025-07-12 22:07:30',1,NULL,'2025-07-12 23:08:03','pt',2,'2024-01-08 21:15:54','2025-07-12 22:08:03',NULL),(3,'Bruno Costa','Bruno.costa@sielaff.pt',NULL,'$2y$10$OuxKfrxTrbxMhu3fEeTCuutYyyzMfwpKGe7v74zcPbZ8WVb7fFSfW','2025-07-30 12:34:39',1,NULL,'2025-07-30 15:57:08','pt',1,'2025-07-28 13:59:58','2025-07-30 14:57:08',NULL);
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

-- Dump completed on 2025-07-31 10:19:05
