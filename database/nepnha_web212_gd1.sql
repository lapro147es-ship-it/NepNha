-- Nếp Nhà complete application sample export 2026-11-13
CREATE DATABASE IF NOT EXISTS nepnha_web212_gd1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nepnha_web212_gd1;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `apartments` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `customer_id` bigint NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_m2` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `apartments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `apartments_chk_1` CHECK ((`area_m2` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `apartments` WRITE;
/*!40000 ALTER TABLE `apartments` DISABLE KEYS */;
INSERT INTO `apartments` VALUES (1,1,'Căn hộ Lumière Riverside','Tầng 12, tòa West, TP. Thủ Đức, TP.HCM',82.00,'2 phòng ngủ, ưu tiên phong cách Japandi.','2026-11-13 05:56:29'),(2,1,'Studio The Sun Avenue','Tầng 8, tòa SAV3, TP. Thủ Đức, TP.HCM',48.00,'Tối ưu lưu trữ và ánh sáng tự nhiên.','2026-11-13 05:56:29'),(3,7,'Căn hộ Masteri','TP. Thủ Đức, TP.HCM',70.00,'Dữ liệu kiểm tra phân quyền.','2026-11-13 05:56:29');
/*!40000 ALTER TABLE `apartments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_migrations` (
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `app_migrations` WRITE;
/*!40000 ALTER TABLE `app_migrations` DISABLE KEYS */;
INSERT INTO `app_migrations` VALUES ('commerce_v1','2026-11-13 05:56:28');
/*!40000 ALTER TABLE `app_migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `design_version_id` bigint DEFAULT NULL,
  `work_update_id` bigint DEFAULT NULL,
  `uploaded_by` bigint NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_key` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint NOT NULL,
  `uploaded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_key` (`storage_key`),
  KEY `request_id` (`request_id`),
  KEY `design_version_id` (`design_version_id`),
  KEY `work_update_id` (`work_update_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `attachments_ibfk_2` FOREIGN KEY (`design_version_id`) REFERENCES `design_versions` (`id`),
  CONSTRAINT `attachments_ibfk_3` FOREIGN KEY (`work_update_id`) REFERENCES `work_updates` (`id`),
  CONSTRAINT `attachments_ibfk_4` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `attachments_chk_1` CHECK (((`size_bytes` > 0) and (`size_bytes` <= 10485760))),
  CONSTRAINT `attachments_chk_2` CHECK (((`design_version_id` is null) or (`work_update_id` is null)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `actor_id` bigint NOT NULL,
  `request_id` bigint DEFAULT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `actor_id` (`actor_id`),
  KEY `request_id` (`request_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,1,'request',1,'DEMO_CREATE',NULL,'IN_PROGRESS',NULL,'2026-11-13 05:56:29'),(2,1,2,'request',2,'DEMO_CREATE',NULL,'NEW',NULL,'2026-11-13 05:56:29'),(3,1,3,'request',3,'DEMO_CREATE',NULL,'QUOTED',NULL,'2026-11-13 05:56:29'),(4,1,4,'request',4,'DEMO_CREATE',NULL,'COMPLETED',NULL,'2026-11-13 05:56:29'),(5,7,5,'request',5,'DEMO_CREATE',NULL,'NEW',NULL,'2026-11-13 05:56:29');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` int DEFAULT NULL,
  `discount_kind` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` bigint NOT NULL,
  `max_discount` bigint NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `variant_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `is_selected` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`variant_id`),
  KEY `variant_id` (`variant_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `service_variants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checkout_batches` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `customer_id` bigint NOT NULL,
  `apartment_id` bigint NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_total` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  UNIQUE KEY `code` (`code`),
  KEY `customer_id` (`customer_id`),
  KEY `apartment_id` (`apartment_id`),
  CONSTRAINT `checkout_batches_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `checkout_batches_ibfk_2` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `checkout_batches` WRITE;
/*!40000 ALTER TABLE `checkout_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `checkout_batches` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_leads` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `reference_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_date` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEW',
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `handled_by` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  KEY `service_id` (`service_id`),
  KEY `handled_by` (`handled_by`),
  CONSTRAINT `consultation_leads_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `consultation_leads_ibfk_2` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `consultation_leads` WRITE;
/*!40000 ALTER TABLE `consultation_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultation_leads` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `design_versions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint NOT NULL,
  `version` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SUBMITTED',
  `submitted_by` bigint NOT NULL,
  `submitted_at` datetime NOT NULL,
  `reviewed_by` bigint DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `approved_work_key` bigint GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'APPROVED') then `work_order_id` else NULL end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_order_id` (`work_order_id`,`version`),
  UNIQUE KEY `approved_work_key` (`approved_work_key`),
  KEY `submitted_by` (`submitted_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `design_versions_ibfk_1` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`),
  CONSTRAINT `design_versions_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `design_versions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `design_versions_chk_1` CHECK ((`version` > 0)),
  CONSTRAINT `design_versions_chk_2` CHECK ((`status` in (_utf8mb4'SUBMITTED',_utf8mb4'APPROVED',_utf8mb4'REVISION_REQUESTED')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `design_versions` WRITE;
/*!40000 ALTER TABLE `design_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `design_versions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `attempt_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `failures` int NOT NULL,
  `last_attempt` bigint NOT NULL,
  PRIMARY KEY (`attempt_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_suppliers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `material_suppliers` WRITE;
/*!40000 ALTER TABLE `material_suppliers` DISABLE KEYS */;
INSERT INTO `material_suppliers` VALUES (1,'Xưởng nội thất mẫu','Nhà cung cấp minh họa của đồ án; không phải xác nhận đối tác thực tế.',1);
/*!40000 ALTER TABLE `material_suppliers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_tiers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_spend` bigint NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `min_spend` (`min_spend`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `membership_tiers` WRITE;
/*!40000 ALTER TABLE `membership_tiers` DISABLE KEYS */;
INSERT INTO `membership_tiers` VALUES (1,'Đồng hành',0,'Xét theo tổng khoản thu POSTED của tài khoản; khoản VOID không được tính.',1),(2,'Gắn kết',50000000,'Xét theo tổng khoản thu POSTED của tài khoản; khoản VOID không được tính.',1),(3,'Thân thiết',150000000,'Xét theo tổng khoản thu POSTED của tài khoản; khoản VOID không được tính.',1);
/*!40000 ALTER TABLE `membership_tiers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint DEFAULT NULL,
  `request_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_intents` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `customer_id` bigint NOT NULL,
  `reference_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint NOT NULL,
  `provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `provider_txn` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` bigint DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  UNIQUE KEY `payment_id` (`payment_id`),
  KEY `request_id` (`request_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `payment_intents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `payment_intents_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `payment_intents_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payment_intents` WRITE;
/*!40000 ALTER TABLE `payment_intents` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_intents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `quotation_id` bigint NOT NULL,
  `receipt_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,0) NOT NULL,
  `method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POSTED',
  `paid_at` datetime NOT NULL,
  `recorded_by` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  `voided_by` bigint DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,
  `void_reason` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_code` (`receipt_code`),
  KEY `quotation_id` (`quotation_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `voided_by` (`voided_by`),
  KEY `idx_payments_request_id` (`request_id`,`status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_chk_1` CHECK ((`amount` > 0)),
  CONSTRAINT `payments_chk_2` CHECK ((`method` in (_utf8mb4'CASH',_utf8mb4'BANK_TRANSFER'))),
  CONSTRAINT `payments_chk_3` CHECK ((`status` in (_utf8mb4'POSTED',_utf8mb4'VOID')))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,1,'PT-DEMO-1',40000000,'BANK_TRANSFER','POSTED','2026-11-13 05:56:29',2,'2026-11-13 05:56:29',NULL,NULL,NULL),(2,4,3,'PT-DEMO-4',12000000,'BANK_TRANSFER','POSTED','2026-11-13 05:56:29',2,'2026-11-13 05:56:29',NULL,NULL,NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_adjustments` (
  `quotation_id` bigint NOT NULL,
  `campaign_id` bigint DEFAULT NULL,
  `voucher_id` bigint DEFAULT NULL,
  `campaign_discount` bigint NOT NULL DEFAULT '0',
  `voucher_discount` bigint NOT NULL DEFAULT '0',
  `label` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`quotation_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `voucher_id` (`voucher_id`),
  CONSTRAINT `quotation_adjustments_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  CONSTRAINT `quotation_adjustments_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`),
  CONSTRAINT `quotation_adjustments_ibfk_3` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotation_adjustments` WRITE;
/*!40000 ALTER TABLE `quotation_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotation_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_attachments` (
  `attachment_id` bigint NOT NULL,
  `quotation_id` bigint NOT NULL,
  `purpose` varchar(20) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `quotation_id` (`quotation_id`),
  CONSTRAINT `quotation_attachments_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  CONSTRAINT `quotation_attachments_ibfk_2` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotation_attachments` WRITE;
/*!40000 ALTER TABLE `quotation_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotation_attachments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_items` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint NOT NULL,
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_price` decimal(18,0) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  CONSTRAINT `quotation_items_chk_1` CHECK ((`quantity` > 0)),
  CONSTRAINT `quotation_items_chk_2` CHECK ((`unit_price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotation_items` WRITE;
/*!40000 ALTER TABLE `quotation_items` DISABLE KEYS */;
INSERT INTO `quotation_items` VALUES (2,1,'Khảo sát và lập phạm vi','gói',1.00,1000000),(3,1,'Hồ sơ thiết kế nội thất','gói',1.00,18000000),(4,1,'Hạng mục tủ bếp','gói',1.00,55000000),(5,1,'Hạng mục tủ quần áo','gói',1.00,25000000),(6,1,'Kệ và nội thất bổ sung','gói',1.00,18000000),(7,1,'Lắp đặt, căn chỉnh và bàn giao','gói',1.00,8000000),(9,2,'Khảo sát và tổng hợp nhu cầu','gói',1.00,1000000),(10,2,'Phương án mặt bằng bố trí','phương án',2.00,2000000),(11,2,'Phối cảnh không gian','góc nhìn',4.00,1500000),(12,2,'Hồ sơ bản vẽ kỹ thuật','bộ',1.00,4000000),(13,2,'Bảng vật liệu và dự toán tham khảo','bộ',1.00,2000000),(14,2,'Rà soát và bàn giao hồ sơ','buổi',1.00,1000000),(16,3,'Bộ tủ giày theo phương án','bộ',1.00,7000000),(17,3,'Kệ trang trí phòng khách','bộ',1.00,3000000),(18,3,'Vận chuyển và lắp đặt','gói',1.00,1500000),(19,3,'Căn chỉnh và vệ sinh bàn giao','gói',1.00,500000);
/*!40000 ALTER TABLE `quotation_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_specs` (
  `quotation_id` bigint NOT NULL,
  `data_json` longtext NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`quotation_id`),
  CONSTRAINT `quotation_specs_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotation_specs` WRITE;
/*!40000 ALTER TABLE `quotation_specs` DISABLE KEYS */;
INSERT INTO `quotation_specs` VALUES (1,'{\"demo\":true,\"scope\":\"Dữ liệu minh họa cho đồ án, dùng để hình dung nội dung một báo giá chi tiết.\\nPhạm vi chính thức phải được xác nhận bằng khảo sát, kích thước và tài liệu của từng căn hộ.\",\"materials\":\"Tông gỗ sáng, bề mặt dễ vệ sinh, sắc độ be và trắng ấm.\\nThương hiệu, mã vật liệu, độ dày và phụ kiện chưa được xác nhận trong dữ liệu mẫu; cần chốt bằng bảng vật liệu có mã và mẫu đối chiếu.\",\"timeline\":\"Mốc 1 · Xác nhận mặt bằng, nhu cầu và danh sách hạng mục.\\nMốc 2 · Gửi phương án, trao đổi và thống nhất nội dung.\\nMốc 3 · Bàn giao hồ sơ\\/kết quả, kiểm tra và xử lý các điểm còn lại.\\nNgày bắt đầu và kết thúc cần được tư vấn xác nhận sau khi thống nhất đầu vào.\",\"payment_terms\":\"Phương án minh họa: 50% sau khi chấp thuận, 50% sau khi hoàn tất bàn giao.\\nTỷ lệ và lịch thu tiền thực tế phải được hai bên thống nhất; các khoản đã thu được đối chiếu riêng trong trang Thanh toán.\",\"revision_terms\":\"Minh họa 2 vòng điều chỉnh trong phạm vi đã thống nhất. Mỗi vòng cần tổng hợp phản hồi cụ thể.\\nĐổi công năng, tăng diện tích hoặc thêm hạng mục được lập thành nội dung phát sinh và báo giá mới trước khi thực hiện.\",\"support_terms\":\"Bàn giao danh sách tài liệu, giải thích nội dung và ghi nhận các điểm cần làm rõ.\\nCác điều kiện bảo hành vật tư, phụ kiện hoặc thi công phải được nêu riêng theo sản phẩm và đơn vị cung cấp; dữ liệu mẫu không tự cam kết thời hạn bảo hành.\",\"exclusions\":\"Không bao gồm cải tạo kết cấu, điện nước ngoài phạm vi, thiết bị gia dụng, đồ rời chưa liệt kê, phí quản lý tòa nhà hoặc phát sinh không có trong hạng mục.\\nẢnh tham khảo không phải sản phẩm hoặc vật liệu đã được chấp thuận.\",\"tax_terms\":\"Giá trị ở đây là tổng các dòng minh họa. Thông tin đã\\/chưa gồm thuế chưa được xác nhận; cần tư vấn nêu rõ trước khi khách hàng chấp thuận.\",\"valid_until\":\"\",\"deliverables\":\"Bảng tổng hợp hạng mục và phạm vi.\\nTài liệu phương án\\/bản vẽ hoặc báo cáo thực hiện theo dịch vụ.\\nBảng vật liệu và phụ kiện cần xác nhận.\\nBiên nhận bàn giao và danh sách các điểm cần xử lý.\",\"item_details\":[{\"zone\":\"Toàn căn hộ\",\"description\":\"Ghi hiện trạng và danh sách đầu việc cần xác nhận.\"},{\"zone\":\"Các không gian chính\",\"description\":\"Bố trí mặt bằng, phối cảnh, chi tiết kỹ thuật và bảng vật liệu theo phương án duyệt.\"},{\"zone\":\"Khu bếp\",\"description\":\"Khối lượng, mã bề mặt và phụ kiện cần được đối chiếu bằng bản vẽ và bảng vật liệu có xác nhận.\"},{\"zone\":\"Phòng ngủ\",\"description\":\"Kiểm tra kích thước, chia khoang, ray và bản lề theo cấu hình đã thống nhất.\"},{\"zone\":\"Phòng khách\",\"description\":\"Chốt danh mục từng sản phẩm trong phạm vi; không mặc định bao gồm thiết bị hoặc đồ rời ngoài danh sách.\"},{\"zone\":\"Căn hộ\",\"description\":\"Phân công, theo dõi tiến độ, kiểm tra và khách hàng xác nhận nghiệm thu.\"}]}','2026-11-13 05:56:29'),(2,'{\"demo\":true,\"scope\":\"Dữ liệu minh họa cho đồ án, dùng để hình dung nội dung một báo giá chi tiết.\\nPhạm vi chính thức phải được xác nhận bằng khảo sát, kích thước và tài liệu của từng căn hộ.\\nVí dụ studio 48 m²: khu tiếp khách, khu ngủ, bếp và các giải pháp lưu trữ. Không thay đổi kết cấu căn hộ.\",\"materials\":\"Tông gỗ sáng, bề mặt dễ vệ sinh, sắc độ be và trắng ấm.\\nThương hiệu, mã vật liệu, độ dày và phụ kiện chưa được xác nhận trong dữ liệu mẫu; cần chốt bằng bảng vật liệu có mã và mẫu đối chiếu.\",\"timeline\":\"Mốc 1 · Xác nhận mặt bằng, nhu cầu và danh sách hạng mục.\\nMốc 2 · Gửi phương án, trao đổi và thống nhất nội dung.\\nMốc 3 · Bàn giao hồ sơ\\/kết quả, kiểm tra và xử lý các điểm còn lại.\\nNgày bắt đầu và kết thúc cần được tư vấn xác nhận sau khi thống nhất đầu vào.\",\"payment_terms\":\"Phương án minh họa: 50% sau khi chấp thuận, 50% sau khi hoàn tất bàn giao.\\nTỷ lệ và lịch thu tiền thực tế phải được hai bên thống nhất; các khoản đã thu được đối chiếu riêng trong trang Thanh toán.\",\"revision_terms\":\"Minh họa 2 vòng điều chỉnh trong phạm vi đã thống nhất. Mỗi vòng cần tổng hợp phản hồi cụ thể.\\nĐổi công năng, tăng diện tích hoặc thêm hạng mục được lập thành nội dung phát sinh và báo giá mới trước khi thực hiện.\",\"support_terms\":\"Bàn giao danh sách tài liệu, giải thích nội dung và ghi nhận các điểm cần làm rõ.\\nCác điều kiện bảo hành vật tư, phụ kiện hoặc thi công phải được nêu riêng theo sản phẩm và đơn vị cung cấp; dữ liệu mẫu không tự cam kết thời hạn bảo hành.\",\"exclusions\":\"Không bao gồm cải tạo kết cấu, điện nước ngoài phạm vi, thiết bị gia dụng, đồ rời chưa liệt kê, phí quản lý tòa nhà hoặc phát sinh không có trong hạng mục.\\nẢnh tham khảo không phải sản phẩm hoặc vật liệu đã được chấp thuận.\",\"tax_terms\":\"Giá trị ở đây là tổng các dòng minh họa. Thông tin đã\\/chưa gồm thuế chưa được xác nhận; cần tư vấn nêu rõ trước khi khách hàng chấp thuận.\",\"valid_until\":\"\",\"deliverables\":\"02 phương án bố trí mặt bằng để lựa chọn.\\n04 góc phối cảnh các khu vực chính.\\n01 bộ bản vẽ PDF trong phạm vi đã chốt.\\n01 bảng vật liệu\\/phụ kiện và khái toán tham khảo.\\n01 buổi giải thích và bàn giao hồ sơ.\",\"item_details\":[{\"zone\":\"Toàn căn hộ\",\"description\":\"Đo hiện trạng khi có lịch khảo sát; ghi nhu cầu sử dụng và các ràng buộc. Đầu ra: biên bản hiện trạng kèm kích thước\\/ảnh do nhân viên bổ sung.\"},{\"zone\":\"Studio 48 m²\",\"description\":\"Bố trí khu ngủ, tiếp khách, bếp và lưu trữ; đối chiếu lối đi. Đầu ra: 2 phương án để lựa chọn trước khi triển khai chi tiết.\"},{\"zone\":\"Các khu vực chính\",\"description\":\"Thể hiện bảng màu, vật liệu và đồ nội thất theo phương án đã thống nhất. Đầu ra: 4 góc nhìn; ảnh thư viện trên website không thay thế phối cảnh này.\"},{\"zone\":\"Nội thất trong phạm vi\",\"description\":\"Mặt bằng, mặt đứng và chi tiết đồ đóng theo hạng mục. Đầu ra: bộ PDF có số hiệu bản vẽ và kích thước để đối chiếu.\"},{\"zone\":\"Vật liệu và phụ kiện\",\"description\":\"Liệt kê bề mặt, mã vật liệu và nhóm phụ kiện để chốt mẫu; tách rõ phần chưa xác nhận. Đầu ra: bảng vật liệu và khái toán theo phương án.\"},{\"zone\":\"Hồ sơ thiết kế\",\"description\":\"Giải thích bộ hồ sơ, đối chiếu danh sách tài liệu và tổng hợp các điểm còn lại. Đầu ra: danh sách bàn giao và nội dung cần làm rõ.\"}]}','2026-11-13 05:56:29'),(3,'{\"demo\":true,\"scope\":\"Dữ liệu minh họa cho đồ án, dùng để hình dung nội dung một báo giá chi tiết.\\nPhạm vi chính thức phải được xác nhận bằng khảo sát, kích thước và tài liệu của từng căn hộ.\",\"materials\":\"Tông gỗ sáng, bề mặt dễ vệ sinh, sắc độ be và trắng ấm.\\nThương hiệu, mã vật liệu, độ dày và phụ kiện chưa được xác nhận trong dữ liệu mẫu; cần chốt bằng bảng vật liệu có mã và mẫu đối chiếu.\",\"timeline\":\"Mốc 1 · Xác nhận mặt bằng, nhu cầu và danh sách hạng mục.\\nMốc 2 · Gửi phương án, trao đổi và thống nhất nội dung.\\nMốc 3 · Bàn giao hồ sơ\\/kết quả, kiểm tra và xử lý các điểm còn lại.\\nNgày bắt đầu và kết thúc cần được tư vấn xác nhận sau khi thống nhất đầu vào.\",\"payment_terms\":\"Phương án minh họa: 50% sau khi chấp thuận, 50% sau khi hoàn tất bàn giao.\\nTỷ lệ và lịch thu tiền thực tế phải được hai bên thống nhất; các khoản đã thu được đối chiếu riêng trong trang Thanh toán.\",\"revision_terms\":\"Minh họa 2 vòng điều chỉnh trong phạm vi đã thống nhất. Mỗi vòng cần tổng hợp phản hồi cụ thể.\\nĐổi công năng, tăng diện tích hoặc thêm hạng mục được lập thành nội dung phát sinh và báo giá mới trước khi thực hiện.\",\"support_terms\":\"Bàn giao danh sách tài liệu, giải thích nội dung và ghi nhận các điểm cần làm rõ.\\nCác điều kiện bảo hành vật tư, phụ kiện hoặc thi công phải được nêu riêng theo sản phẩm và đơn vị cung cấp; dữ liệu mẫu không tự cam kết thời hạn bảo hành.\",\"exclusions\":\"Không bao gồm cải tạo kết cấu, điện nước ngoài phạm vi, thiết bị gia dụng, đồ rời chưa liệt kê, phí quản lý tòa nhà hoặc phát sinh không có trong hạng mục.\\nẢnh tham khảo không phải sản phẩm hoặc vật liệu đã được chấp thuận.\",\"tax_terms\":\"Giá trị ở đây là tổng các dòng minh họa. Thông tin đã\\/chưa gồm thuế chưa được xác nhận; cần tư vấn nêu rõ trước khi khách hàng chấp thuận.\",\"valid_until\":\"\",\"deliverables\":\"Bảng tổng hợp hạng mục và phạm vi.\\nTài liệu phương án\\/bản vẽ hoặc báo cáo thực hiện theo dịch vụ.\\nBảng vật liệu và phụ kiện cần xác nhận.\\nBiên nhận bàn giao và danh sách các điểm cần xử lý.\",\"item_details\":[{\"zone\":\"Khu cửa vào\",\"description\":\"Đối chiếu kích thước ô lắp đặt, số khoang và quy cách đã được chốt trước khi gia công.\"},{\"zone\":\"Phòng khách\",\"description\":\"Xác nhận cao độ, liên kết và tải sử dụng theo phương án; kiểm tra bề mặt khi bàn giao.\"},{\"zone\":\"Căn hộ\",\"description\":\"Thống nhất tuyến vận chuyển, giờ thi công và lắp đặt các hạng mục đã liệt kê.\"},{\"zone\":\"Các hạng mục lắp đặt\",\"description\":\"Kiểm tra cánh, bản lề, liên kết; ghi nhận tồn tại trước khi khách hàng nghiệm thu.\"}]}','2026-11-13 05:56:29');
/*!40000 ALTER TABLE `quotation_specs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotations` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `version` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `customer_feedback` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `responded_by` bigint DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `sent_request_key` bigint GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'SENT') then `request_id` else NULL end)) STORED,
  `accepted_request_key` bigint GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'ACCEPTED') then `request_id` else NULL end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`,`version`),
  UNIQUE KEY `sent_request_key` (`sent_request_key`),
  UNIQUE KEY `accepted_request_key` (`accepted_request_key`),
  KEY `created_by` (`created_by`),
  KEY `responded_by` (`responded_by`),
  CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `quotations_ibfk_3` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `quotations_chk_1` CHECK ((`version` > 0)),
  CONSTRAINT `quotations_chk_2` CHECK ((`status` in (_utf8mb4'DRAFT',_utf8mb4'SENT',_utf8mb4'ACCEPTED',_utf8mb4'REJECTED')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
INSERT INTO `quotations` (`id`, `request_id`, `version`, `status`, `notes`, `customer_feedback`, `created_by`, `created_at`, `sent_at`, `responded_by`, `responded_at`) VALUES (1,1,1,'ACCEPTED','Phạm vi và vật liệu được xác nhận theo hồ sơ.',NULL,2,'2026-11-13 05:56:29','2026-11-13 05:56:29',1,'2026-11-13 05:56:29'),(2,3,1,'SENT','Phạm vi và vật liệu được xác nhận theo hồ sơ.',NULL,2,'2026-11-13 05:56:29','2026-11-13 05:56:29',NULL,NULL),(3,4,1,'ACCEPTED','Phạm vi và vật liệu được xác nhận theo hồ sơ.',NULL,2,'2026-11-13 05:56:29','2026-11-13 05:56:29',1,'2026-11-13 05:56:29');
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_orders` (
  `request_id` bigint NOT NULL,
  `batch_id` bigint NOT NULL,
  `variant_id` bigint NOT NULL,
  `quantity` int NOT NULL,
  `estimated_subtotal` bigint NOT NULL,
  `campaign_id` bigint DEFAULT NULL,
  `voucher_code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `batch_id` (`batch_id`),
  KEY `variant_id` (`variant_id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `request_orders_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `request_orders_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `checkout_batches` (`id`),
  CONSTRAINT `request_orders_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `service_variants` (`id`),
  CONSTRAINT `request_orders_ibfk_4` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `request_orders` WRITE;
/*!40000 ALTER TABLE `request_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review_images` (
  `review_id` bigint NOT NULL,
  `storage_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint NOT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `storage_key` (`storage_key`),
  CONSTRAINT `review_images_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `service_reviews` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `review_images` WRITE;
/*!40000 ALTER TABLE `review_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `review_images` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'CUSTOMER','Khách hàng'),(2,'CONSULTANT','Tư vấn viên'),(3,'DESIGNER','Nhà thiết kế'),(4,'INSTALLER','Nhân viên lắp đặt'),(5,'MAINTAINER','Kỹ thuật bảo trì'),(6,'ADMIN','Quản trị viên');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saved_vouchers` (
  `user_id` bigint NOT NULL,
  `voucher_id` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`voucher_id`),
  KEY `voucher_id` (`voucher_id`),
  CONSTRAINT `saved_vouchers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `saved_vouchers_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `saved_vouchers` WRITE;
/*!40000 ALTER TABLE `saved_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_vouchers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,'Thiết kế và hoàn thiện','Giải pháp tổ chức không gian và hoàn thiện căn hộ.',1),(2,'Lắp đặt và chăm sóc','Thi công, lắp đặt và bảo trì theo hồ sơ được duyệt.',1);
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_requests` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint NOT NULL,
  `apartment_id` bigint NOT NULL,
  `service_id` int NOT NULL,
  `consultant_id` bigint DEFAULT NULL,
  `parent_request_id` bigint DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `budget` decimal(18,0) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEW',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `apartment_id` (`apartment_id`),
  KEY `service_id` (`service_id`),
  KEY `parent_request_id` (`parent_request_id`),
  KEY `idx_service_requests_customer_id` (`customer_id`,`created_at`),
  KEY `idx_service_requests_consultant_id` (`consultant_id`,`status`),
  CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`),
  CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`consultant_id`) REFERENCES `users` (`id`),
  CONSTRAINT `service_requests_ibfk_5` FOREIGN KEY (`parent_request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `service_requests_chk_1` CHECK ((`budget` >= 0)),
  CONSTRAINT `service_requests_chk_2` CHECK ((`status` in (_utf8mb4'NEW',_utf8mb4'RECEIVED',_utf8mb4'QUOTED',_utf8mb4'APPROVED',_utf8mb4'IN_PROGRESS',_utf8mb4'COMPLETED',_utf8mb4'CLOSED',_utf8mb4'REJECTED',_utf8mb4'CANCELLED')))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
INSERT INTO `service_requests` VALUES (1,'NT-DEMO-001',1,1,4,2,NULL,'Thiết kế và lắp đặt phòng khách, phòng ngủ theo phong cách Japandi.',145000000,'2026-11-20','IN_PROGRESS',NULL,'2026-11-12 05:56:29','2026-11-13 05:56:29'),(2,'NT-DEMO-002',1,1,3,NULL,NULL,'Bản lề tủ bếp bị lệch, cần kiểm tra và cân chỉnh.',1500000,'2026-11-20','NEW',NULL,'2026-11-11 05:56:29','2026-11-13 05:56:29'),(3,'NT-DEMO-003',1,2,1,2,NULL,'Thiết kế studio tối giản, thêm giải pháp lưu trữ thông minh.',18000000,'2026-11-20','QUOTED',NULL,'2026-11-10 05:56:29','2026-11-13 05:56:29'),(4,'NT-DEMO-004',1,2,2,2,NULL,'Lắp đặt bộ tủ giày và kệ trang trí phòng khách.',12000000,'2026-11-20','COMPLETED',NULL,'2026-11-09 05:56:29','2026-11-13 05:56:29'),(5,'NT-DEMO-005',7,3,1,NULL,NULL,'Thiết kế căn hộ 2 phòng ngủ.',25000000,'2026-11-20','NEW',NULL,'2026-11-08 05:56:29','2026-11-13 05:56:29');
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_reviews` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `customer_id` bigint NOT NULL,
  `service_id` int NOT NULL,
  `rating` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PUBLISHED',
  `reply` text COLLATE utf8mb4_unicode_ci,
  `replied_by` bigint DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`),
  KEY `customer_id` (`customer_id`),
  KEY `service_id` (`service_id`),
  KEY `replied_by` (`replied_by`),
  CONSTRAINT `service_reviews_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `service_reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `service_reviews_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `service_reviews_ibfk_4` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_reviews` WRITE;
/*!40000 ALTER TABLE `service_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_reviews` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_variants` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `category_id` bigint NOT NULL,
  `supplier_id` bigint DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` bigint NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `service_variants_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `service_variants_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `service_variants_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `material_suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_variants` WRITE;
/*!40000 ALTER TABLE `service_variants` DISABLE KEYS */;
INSERT INTO `service_variants` VALUES (1,1,1,1,'Cơ bản · Thiết kế không gian','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',18000000,1),(2,1,1,1,'Mở rộng · Thiết kế không gian','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',22500000,1),(3,2,1,1,'Cơ bản · Lắp đặt nội thất','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',35000000,1),(4,2,1,1,'Mở rộng · Lắp đặt nội thất','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',43750000,1),(5,3,1,1,'Cơ bản · Chăm sóc và bảo trì','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',850000,1),(6,3,1,1,'Mở rộng · Chăm sóc và bảo trì','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',1062500,1),(7,4,1,1,'Cơ bản · Nội thất trọn gói','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',125000000,1),(8,4,1,1,'Mở rộng · Nội thất trọn gói','Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.','gói',156250000,1);
/*!40000 ALTER TABLE `service_variants` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_price` decimal(18,0) DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  CONSTRAINT `services_chk_1` CHECK ((`service_type` in (_utf8mb4'DESIGN',_utf8mb4'INSTALLATION',_utf8mb4'MAINTENANCE',_utf8mb4'PACKAGE'))),
  CONSTRAINT `services_chk_2` CHECK ((`reference_price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'TK','Thiết kế không gian','DESIGN','Bố trí mặt bằng, lựa chọn vật liệu và phối cảnh theo phong cách sống của bạn.',18000000,1),(2,'LD','Lắp đặt nội thất','INSTALLATION','Lắp đặt tủ bếp, phòng ngủ và nội thất rời theo phương án đã thống nhất.',35000000,1),(3,'BT','Chăm sóc và bảo trì','MAINTENANCE','Kiểm tra phụ kiện, điều chỉnh bản lề, xử lý hư hỏng và bảo dưỡng nội thất.',850000,1),(4,'TG','Nội thất trọn gói','PACKAGE','Đồng hành từ khảo sát, thiết kế đến lắp đặt và nghiệm thu căn hộ.',125000000,1);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `surveyor_id` bigint NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `result` text COLLATE utf8mb4_unicode_ci,
  `customer_confirmed_at` datetime DEFAULT NULL,
  `created_by` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_surveys_surveyor_id` (`surveyor_id`,`starts_at`),
  CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `surveys_ibfk_2` FOREIGN KEY (`surveyor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `surveys_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `surveys_chk_1` CHECK ((`ends_at` > `starts_at`)),
  CONSTRAINT `surveys_chk_2` CHECK ((`status` in (_utf8mb4'PENDING',_utf8mb4'CONFIRMED',_utf8mb4'DONE',_utf8mb4'CANCELLED')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `event_key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_page` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`event_key`),
  CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_notifications` WRITE;
/*!40000 ALTER TABLE `user_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_notifications` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` bigint NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1),(7,1),(2,2),(3,3),(4,4),(5,5),(6,6);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'khach@nepnha.test','$2y$12$wlZmK4r8OMn/yDcBr9Q6Uu7Nf88VhL.YEjZO401PKQM2toDN6c1C.','Nguyễn Minh Anh','0901234567',1,'2026-11-13 05:56:28'),(2,'tuvan@nepnha.test','$2y$12$.8P1ndR4O7xqsXZhjz.m0.Pbv9eFBFQBdYpPtHLHRytfpILXhfe/y','Trần Ngọc Linh','0902345678',1,'2026-11-13 05:56:28'),(3,'thietke@nepnha.test','$2y$12$Y80tNK9mAaut.vitTpv1b.Hq9avXZkLneRsqUSytl6xRFkTatQw56','Lê Hoàng Nam','0903456789',1,'2026-11-13 05:56:28'),(4,'lapdat@nepnha.test','$2y$12$rqSWH8/ocZQIgUFQ0o1iNuV6EGy2qTC0HyCLkqSAOk.MIUrTacKQK','Phạm Đức Huy','0904567890',1,'2026-11-13 05:56:28'),(5,'baotri@nepnha.test','$2y$12$uVidxlM9/qe3HRPO5v7NgOOuIsqob9WjYl7PQOaC/z/86IT1FaISe','Võ Thanh Tùng','0905678901',1,'2026-11-13 05:56:29'),(6,'admin@nepnha.test','$2y$12$lP677gvqQIAkF2bxYEMVbOGuVV1agYGGJskzMgkoRuR69ZEtb7AQy','Quản trị Nếp Nhà','0906789012',1,'2026-11-13 05:56:29'),(7,'khach2@nepnha.test','$2y$12$Wsw2ZmsPnCTefFy1HT1nC.9ThicX.ydNKl45zibU1bB.ShkHmYmO6','Đỗ Thu Hà','0907890123',1,'2026-11-13 05:56:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_redemptions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint NOT NULL,
  `customer_id` bigint NOT NULL,
  `quotation_id` bigint NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_id` (`quotation_id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `voucher_redemptions_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`),
  CONSTRAINT `voucher_redemptions_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `voucher_redemptions_ibfk_3` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `voucher_redemptions` WRITE;
/*!40000 ALTER TABLE `voucher_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `voucher_redemptions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vouchers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_kind` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` bigint NOT NULL,
  `min_total` bigint NOT NULL DEFAULT '0',
  `max_discount` bigint NOT NULL,
  `max_uses` int NOT NULL,
  `per_user_limit` int NOT NULL,
  `required_tier_id` bigint DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `required_tier_id` (`required_tier_id`),
  CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`required_tier_id`) REFERENCES `membership_tiers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` VALUES (1,'NEPNHA5','Ưu đãi mẫu 5%, tối đa 500.000đ, áp dụng khi báo giá đủ điều kiện.','PERCENT',5,1000000,500000,100,1,NULL,'2026-11-12 05:56:28','2027-11-13 05:56:28',1);
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_orders` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `request_id` bigint NOT NULL,
  `assignee_id` bigint NOT NULL,
  `assigned_by` bigint NOT NULL,
  `work_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ASSIGNED',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_work_orders_assignee_id` (`assignee_id`,`starts_at`),
  CONSTRAINT `work_orders_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`),
  CONSTRAINT `work_orders_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`),
  CONSTRAINT `work_orders_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `work_orders_chk_1` CHECK ((`ends_at` > `starts_at`)),
  CONSTRAINT `work_orders_chk_2` CHECK ((`work_type` in (_utf8mb4'DESIGN',_utf8mb4'INSTALLATION',_utf8mb4'MAINTENANCE'))),
  CONSTRAINT `work_orders_chk_3` CHECK ((`status` in (_utf8mb4'ASSIGNED',_utf8mb4'IN_PROGRESS',_utf8mb4'REPORTED',_utf8mb4'DONE',_utf8mb4'CANCELLED')))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `work_orders` WRITE;
/*!40000 ALTER TABLE `work_orders` DISABLE KEYS */;
INSERT INTO `work_orders` VALUES (1,1,3,2,'DESIGN','Thiết kế căn hộ Japandi 82 m²','Mặt bằng phòng khách, bếp và hai phòng ngủ.','2026-11-15 05:56:29','2026-11-15 08:56:29','IN_PROGRESS','2026-11-13 05:56:29'),(2,4,4,2,'INSTALLATION','Lắp tủ giày và kệ phòng khách',NULL,'2026-11-10 05:56:29','2026-11-10 08:56:29','DONE','2026-11-13 05:56:29');
/*!40000 ALTER TABLE `work_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_updates` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint NOT NULL,
  `author_id` bigint NOT NULL,
  `event_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress_percent` int DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `work_order_id` (`work_order_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `work_updates_ibfk_1` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`),
  CONSTRAINT `work_updates_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `work_updates_chk_1` CHECK ((`progress_percent` between 0 and 100))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `work_updates` WRITE;
/*!40000 ALTER TABLE `work_updates` DISABLE KEYS */;
INSERT INTO `work_updates` VALUES (1,1,3,'PROGRESS',65,'Đã hoàn thành mặt bằng bố trí, đang phát triển phối cảnh và bảng vật liệu.','2026-11-13 05:56:29'),(2,2,1,'ACCEPTED',NULL,'Nghiệm thu đạt yêu cầu, bàn giao đầy đủ.','2026-11-13 05:56:29');
/*!40000 ALTER TABLE `work_updates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

