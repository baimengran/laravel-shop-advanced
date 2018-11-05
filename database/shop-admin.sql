-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: laravel-shop
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'首页','fa-bar-chart','/',NULL,'2018-09-16 08:41:50'),(2,0,9,'系统管理','fa-tasks',NULL,NULL,'2018-10-17 00:38:16'),(3,2,10,'管理员','fa-users','auth/users',NULL,'2018-10-17 00:38:16'),(4,2,11,'角色','fa-user','auth/roles',NULL,'2018-10-17 00:38:16'),(5,2,12,'权限','fa-ban','auth/permissions',NULL,'2018-10-17 00:38:16'),(6,2,13,'菜单','fa-bars','auth/menu',NULL,'2018-10-17 00:38:16'),(7,2,14,'操作日志','fa-history','auth/logs',NULL,'2018-10-17 00:38:16'),(8,0,2,'用户管理','fa-user','/users','2018-09-17 07:32:28','2018-09-17 07:33:09'),(9,0,4,'商品管理','fa-cubes','/products','2018-09-17 10:08:23','2018-10-15 23:45:45'),(10,0,7,'订单管理','fa-rmb','/orders','2018-10-04 16:56:00','2018-10-17 00:38:15'),(11,0,8,'优惠卷管理','fa-tags','/coupon_codes','2018-10-09 19:36:44','2018-10-17 00:38:16'),(12,0,3,'类目管理','fa-bars','/categories','2018-10-15 23:45:27','2018-10-15 23:45:45'),(13,9,6,'众筹商品','fa-flag-checkered','/crowdfunding_products','2018-10-17 00:36:00','2018-10-17 00:38:15'),(14,9,5,'普通商品','fa-cubes','/products','2018-10-17 00:37:56','2018-10-17 00:38:15');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,'All permission','*','','*',NULL,NULL),(2,'Dashboard','dashboard','GET','/',NULL,NULL),(3,'Login','auth.login','','/auth/login\r\n/auth/logout',NULL,NULL),(4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),(5,'Auth management','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,NULL),(6,'用户管理','users','','/users*','2018-09-17 07:40:58','2018-09-17 07:40:58'),(7,'商品管理','products','','/products*','2018-10-11 19:16:57','2018-10-11 19:16:57'),(8,'订单管理','orders','','/orders*','2018-10-11 19:17:30','2018-10-11 19:17:30'),(9,'优惠卷管理','coupon_codes','','/coupon_codes*','2018-10-11 19:18:20','2018-10-11 19:18:20');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_menu`
--

LOCK TABLES `admin_role_menu` WRITE;
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` VALUES (1,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_permissions`
--

LOCK TABLES `admin_role_permissions` WRITE;
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_users`
--

LOCK TABLES `admin_role_users` WRITE;
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES (1,'Administrator','administrator','2018-09-16 08:14:44','2018-09-16 08:14:44'),(2,'运营','operator','2018-09-17 07:44:35','2018-09-17 07:44:35');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_permissions`
--

LOCK TABLES `admin_user_permissions` WRITE;
/*!40000 ALTER TABLE `admin_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$lMCvDU/cIxyKFU5WgeWStO6Jq1FJ9u9fmF36lm2Y4p2/N48ACd/dG','Administrator',NULL,'FRteWw3HYmvobvMNfcV7u7l8N6jnz1553drIGGECb63sKFuDqJPq9yRWJZU2','2018-09-16 08:14:44','2018-11-05 15:37:09'),(2,'operator','$2y$10$C2ozP5fvH3FhlaDAz/6g0.QVC/zv0JsQWAKSfXGQKdqQcoqIiqpzO','运营','images/54353453453453453.jpg','FMD0p7yQhE49CmzeWVDhTQjOYh7CohxbnDMhIcvuGsdqcDS50NKCVrrKhWWe','2018-09-17 07:47:07','2018-09-17 14:12:26'),(3,'guest','$2y$10$MSsNsXIlZl10Ig8drnI6xeFjDsWikhKTVn4eb6ibQmFM6bqqczngy','Guest','images/25235235235235345345.jpg',NULL,'2018-11-05 15:41:39','2018-11-05 15:41:39');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-05 10:42:24
