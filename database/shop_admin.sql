/*
SQLyog Ultimate v11.5 (64 bit)
MySQL - 5.7.22-0ubuntu18.04.1 : Database - laravel-shop
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`laravel-shop` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `laravel-shop`;

/*Data for the table `admin_menu` */

insert  into `admin_menu`(`id`,`parent_id`,`order`,`title`,`icon`,`uri`,`created_at`,`updated_at`) values (1,0,1,'首页','fa-bar-chart','/',NULL,'2018-09-16 08:41:50'),(2,0,6,'系统管理','fa-tasks',NULL,NULL,'2018-10-09 19:36:53'),(3,2,7,'管理员','fa-users','auth/users',NULL,'2018-10-09 19:36:53'),(4,2,8,'角色','fa-user','auth/roles',NULL,'2018-10-09 19:36:53'),(5,2,9,'权限','fa-ban','auth/permissions',NULL,'2018-10-09 19:36:53'),(6,2,10,'菜单','fa-bars','auth/menu',NULL,'2018-10-09 19:36:53'),(7,2,11,'操作日志','fa-history','auth/logs',NULL,'2018-10-09 19:36:53'),(8,0,2,'用户管理','fa-user','/users','2018-09-17 07:32:28','2018-09-17 07:33:09'),(9,0,3,'商品管理','fa-cubes','/products','2018-09-17 10:08:23','2018-09-17 10:09:04'),(10,0,4,'订单管理','fa-rmb','/orders','2018-10-04 16:56:00','2018-10-04 16:56:12'),(11,0,5,'优惠卷管理','fa-tags','/coupon_codes','2018-10-09 19:36:44','2018-10-09 19:36:53');

/*Data for the table `admin_permissions` */

insert  into `admin_permissions`(`id`,`name`,`slug`,`http_method`,`http_path`,`created_at`,`updated_at`) values (1,'All permission','*','','*',NULL,NULL),(2,'Dashboard','dashboard','GET','/',NULL,NULL),(3,'Login','auth.login','','/auth/login\r\n/auth/logout',NULL,NULL),(4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),(5,'Auth management','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,NULL),(6,'用户管理','users','','/users*','2018-09-17 07:40:58','2018-09-17 07:40:58'),(7,'商品管理','products','','/products*','2018-10-11 19:16:57','2018-10-11 19:16:57'),(8,'订单管理','orders','','/orders*','2018-10-11 19:17:30','2018-10-11 19:17:30'),(9,'优惠卷管理','coupon_codes','','/coupon_codes*','2018-10-11 19:18:20','2018-10-11 19:18:20');

/*Data for the table `admin_role_menu` */

insert  into `admin_role_menu`(`role_id`,`menu_id`,`created_at`,`updated_at`) values (1,2,NULL,NULL);

/*Data for the table `admin_role_permissions` */

insert  into `admin_role_permissions`(`role_id`,`permission_id`,`created_at`,`updated_at`) values (1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL);

/*Data for the table `admin_role_users` */

insert  into `admin_role_users`(`role_id`,`user_id`,`created_at`,`updated_at`) values (1,1,NULL,NULL),(2,2,NULL,NULL);

/*Data for the table `admin_roles` */

insert  into `admin_roles`(`id`,`name`,`slug`,`created_at`,`updated_at`) values (1,'Administrator','administrator','2018-09-16 08:14:44','2018-09-16 08:14:44'),(2,'运营','operator','2018-09-17 07:44:35','2018-09-17 07:44:35');

/*Data for the table `admin_user_permissions` */

/*Data for the table `admin_users` */

insert  into `admin_users`(`id`,`username`,`password`,`name`,`avatar`,`remember_token`,`created_at`,`updated_at`) values (1,'admin','$2y$10$HiyAEE/Bdvvz/Ct7/Ii5C.coW3mxpSqDdymY5AlN2sLyD2Z4AyLHe','Administrator',NULL,'gu0J5LKJEzUP8b9ZyDw2rWrQDZIdnR7YL6TeZBwcGJ8mHWsWcAkY1TSnpxJG','2018-09-16 08:14:44','2018-09-16 08:14:44'),(2,'operator','$2y$10$C2ozP5fvH3FhlaDAz/6g0.QVC/zv0JsQWAKSfXGQKdqQcoqIiqpzO','运营','images/54353453453453453.jpg','FMD0p7yQhE49CmzeWVDhTQjOYh7CohxbnDMhIcvuGsdqcDS50NKCVrrKhWWe','2018-09-17 07:47:07','2018-09-17 14:12:26');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
