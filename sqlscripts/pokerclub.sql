-- MySQL dump 10.11
--
-- Host: localhost    Database: pokerclub
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6

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
-- Table structure for table `pokermembers`
--

DROP TABLE IF EXISTS `pokermembers`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pokermembers` (
  `id` int(11) NOT NULL auto_increment,
  `FName` varchar(255) NOT NULL,
  `LName` varchar(255) NOT NULL,
  `username` varchar(255) default NULL,
  `Email` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `hphone` varchar(255) default NULL,
  `bphone` varchar(255) default NULL,
  `cphone` varchar(255) default NULL,
  `bday` date default NULL,
  `spouse` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `loggedin` tinyint(4) default '0',
  `count` int(11) default '0',
  `canplay` enum('no','yes') default 'no',
  `last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `pokermembers`
--

LOCK TABLES `pokermembers` WRITE;
/*!40000 ALTER TABLE `pokermembers` DISABLE KEYS */;
INSERT INTO `pokermembers` VALUES (1,'Dick','Vandeusen','dickv',NULL,NULL,'887-1764',NULL,NULL,NULL,'Carol','8871764',0,0,'no','2009-03-19 16:27:41'),(2,'Wayne','Balnicki','wayneb','waynebalnicki@comcast.net','88 forrest drive, granby, co 80446','887-3671','','303-570-7206','1945-10-21','Annette','8873671',1,9,'yes','2009-04-06 21:42:01'),(3,'Jim','Clair','jimc',NULL,NULL,'887-9097',NULL,NULL,NULL,'Joyce','8879097',0,0,'no','2009-03-19 16:27:41'),(4,'Ed','Tupa','edt',NULL,NULL,'887-3011',NULL,NULL,NULL,'Vernie','8873011',0,0,'no','2009-03-19 16:27:41'),(5,'Jerry','Tietsma','jerryt',NULL,NULL,'887-3910',NULL,NULL,NULL,'Libby','8873910',0,0,'no','2009-03-19 16:27:41'),(6,'Tom','Chaffin','tomc',NULL,NULL,'887-0722',NULL,NULL,NULL,'Nancy','8870722',0,0,'no','2009-03-19 16:27:41'),(7,'Jerry','Stahl','jerrys',NULL,NULL,'887-9352',NULL,NULL,NULL,'Carolyn','8879352',0,0,'no','2009-03-19 16:27:41'),(8,'Tim','Schowalter','tims',NULL,NULL,'887-3708',NULL,NULL,NULL,'Judy','8873708',0,0,'no','2009-03-19 16:27:41'),(9,'Jim','Ratcluff','jimr',NULL,NULL,'887-2513',NULL,NULL,NULL,'Jean','8872513',0,0,'no','2009-03-19 16:27:41'),(10,'Barton','Phillips','bartonp','bartonphillips@gmail.com','PO Box 4152 122 Fairway Lane ','887-3696','','970-509-9511','1944-04-11','Ingrid','8873696',1,299,'yes','2009-04-17 16:08:58'),(11,'Gary','Perkins','garyp',NULL,NULL,'887-0640',NULL,NULL,NULL,'Sue','8870640',0,0,'no','2009-03-19 16:27:41');
/*!40000 ALTER TABLE `pokermembers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-04-18 19:07:54
