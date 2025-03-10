-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: forumdb
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `rf_ananas`
--

DROP TABLE IF EXISTS `rf_ananas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_ananas` (
  `rom` int NOT NULL,
  KEY `rom` (`rom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_api`
--

DROP TABLE IF EXISTS `rf_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_api` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `idM` int NOT NULL,
  `url` varchar(255) NOT NULL,
  `count` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_api_check`
--

DROP TABLE IF EXISTS `rf_api_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_api_check` (
  `id` int NOT NULL AUTO_INCREMENT,
  `app` int NOT NULL,
  `idM` int NOT NULL,
  `key` varchar(32) NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=928 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_avertir`
--

DROP TABLE IF EXISTS `rf_avertir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_avertir` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPseudo` int NOT NULL,
  `prio` enum('0','1') NOT NULL,
  `module` enum('0','1') NOT NULL COMMENT '0 forums 1 news',
  `idPost` int NOT NULL,
  `page` int NOT NULL,
  `motif` varchar(255) NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6525 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_b_boards`
--

DROP TABLE IF EXISTS `rf_b_boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_b_boards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(16) NOT NULL,
  `nom` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_b_posts`
--

DROP TABLE IF EXISTS `rf_b_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_b_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idT` int NOT NULL,
  `idM` int NOT NULL,
  `date` bigint NOT NULL,
  `titre` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `img` varchar(32) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `size` int NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `anon` enum('0','1') NOT NULL,
  `admin` enum('0','1','2') NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idT` (`idT`)
) ENGINE=InnoDB AUTO_INCREMENT=18464 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_b_threads`
--

DROP TABLE IF EXISTS `rf_b_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_b_threads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `board` int NOT NULL,
  `first` int NOT NULL,
  `last` bigint NOT NULL,
  `nbP` int NOT NULL,
  `nbI` int NOT NULL,
  `sticky` enum('0','1') NOT NULL,
  `locked` enum('0','1') NOT NULL,
  `protected` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `board` (`board`),
  KEY `last` (`last`)
) ENGINE=InnoDB AUTO_INCREMENT=3116 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_bans`
--

DROP TABLE IF EXISTS `rf_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_bans` (
  `idM` int NOT NULL,
  `motif` varchar(255) NOT NULL,
  `expire` bigint NOT NULL,
  PRIMARY KEY (`idM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_bans_review`
--

DROP TABLE IF EXISTS `rf_bans_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_bans_review` (
  `idB` int NOT NULL,
  `idS` int NOT NULL,
  PRIMARY KEY (`idB`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_carotte`
--

DROP TABLE IF EXISTS `rf_carotte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_carotte` (
  `rom` int NOT NULL,
  KEY `rom` (`rom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_categories`
--

DROP TABLE IF EXISTS `rf_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_concombre`
--

DROP TABLE IF EXISTS `rf_concombre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_concombre` (
  `rom` int NOT NULL,
  PRIMARY KEY (`rom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_connectes`
--

DROP TABLE IF EXISTS `rf_connectes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_connectes` (
  `ip` bigint NOT NULL,
  `idM` int NOT NULL,
  `acces` tinyint NOT NULL,
  `time` bigint NOT NULL,
  `page` varchar(255) NOT NULL,
  KEY `ip` (`ip`),
  KEY `idM` (`idM`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_courgette`
--

DROP TABLE IF EXISTS `rf_courgette`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_courgette` (
  `rom` int NOT NULL,
  KEY `rom` (`rom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_edits`
--

DROP TABLE IF EXISTS `rf_edits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_edits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int NOT NULL COMMENT 'id du membre',
  `idP` int NOT NULL COMMENT 'id post',
  `message` text NOT NULL COMMENT 'ancien msg',
  `time` int NOT NULL,
  `old` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `old` (`old`),
  KEY `idM` (`idM`),
  KEY `idP` (`idP`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=62881 DEFAULT CHARSET=utf8mb3 COMMENT='Sert a enregistrer les edits';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_eg`
--

DROP TABLE IF EXISTS `rf_eg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_eg` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jeu` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `expire` bigint NOT NULL,
  `idM` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_fav`
--

DROP TABLE IF EXISTS `rf_fav`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_fav` (
  `idM` int NOT NULL,
  `idT` int NOT NULL,
  KEY `idM` (`idM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_forums`
--

DROP TABLE IF EXISTS `rf_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_forums` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idCat` int NOT NULL,
  `nom` varchar(60) NOT NULL,
  `lastMess` bigint NOT NULL,
  `nbMess` int NOT NULL,
  `nbTopics` int NOT NULL,
  `acces` tinyint NOT NULL,
  `entraide` enum('0','1') NOT NULL,
  `deleteAfter` int DEFAULT NULL COMMENT 'Supprime les messages/topics après X minutes',
  `anonyme` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Si 1, anonymise les réponses',
  `random` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Si 1, randomise le pseudo (liste co)',
  `heureMin` int DEFAULT NULL COMMENT 'Si !=NULL, limite la plage horaire dispo',
  `heureMax` int DEFAULT NULL COMMENT 'Si !=NULL, limite la plage horaire dispo',
  `archive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `acces` (`acces`)
) ENGINE=MyISAM AUTO_INCREMENT=123456807 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_gfy`
--

DROP TABLE IF EXISTS `rf_gfy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_gfy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gif` varchar(255) NOT NULL,
  `gfy` varchar(255) NOT NULL,
  `time` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gif` (`gif`,`gfy`,`time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COMMENT='Enregistre les associations gif/gfy';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_hide`
--

DROP TABLE IF EXISTS `rf_hide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_hide` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auteur` int NOT NULL,
  `points` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `hide` text NOT NULL,
  `nb` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1562 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_hider`
--

DROP TABLE IF EXISTS `rf_hider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_hider` (
  `idH` int NOT NULL,
  `idM` int NOT NULL,
  `time` bigint NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_ignore`
--

DROP TABLE IF EXISTS `rf_ignore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_ignore` (
  `idM` int NOT NULL,
  `idI` int NOT NULL,
  KEY `idM` (`idM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_jambom`
--

DROP TABLE IF EXISTS `rf_jambom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_jambom` (
  `idM` int NOT NULL,
  `passe` varchar(32) NOT NULL,
  PRIMARY KEY (`idM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_kickhide`
--

DROP TABLE IF EXISTS `rf_kickhide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_kickhide` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_kicknews`
--

DROP TABLE IF EXISTS `rf_kicknews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_kicknews` (
  `id` int NOT NULL,
  `pseudo` varchar(32) NOT NULL,
  `sodomite` varchar(32) NOT NULL,
  `time` int NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Kick des news';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_kicks`
--

DROP TABLE IF EXISTS `rf_kicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_kicks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPseudo` int NOT NULL,
  `idForum` int NOT NULL,
  `idModo` int NOT NULL,
  `motif` varchar(255) NOT NULL,
  `expire` bigint NOT NULL,
  `ban` enum('0','1') NOT NULL,
  `sid` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPseudo` (`idPseudo`),
  KEY `idForum` (`idForum`)
) ENGINE=MyISAM AUTO_INCREMENT=4888 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_membres`
--

DROP TABLE IF EXISTS `rf_membres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_membres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(15) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cle` varchar(10) NOT NULL,
  `time` bigint NOT NULL,
  `lasttime` bigint NOT NULL,
  `firstip` bigint NOT NULL,
  `lastip` bigint NOT NULL,
  `sexe` enum('m','f','a') NOT NULL,
  `nbmess` int NOT NULL,
  `acces` tinyint NOT NULL DEFAULT '10',
  `pref` varchar(255) NOT NULL,
  `msn` varchar(255) NOT NULL,
  `presentation` varchar(400) NOT NULL,
  `signature` varchar(400) NOT NULL,
  `pays` varchar(40) NOT NULL,
  `datenaissance` varchar(10) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `points` int NOT NULL,
  `fond` varchar(255) NOT NULL,
  `design` varchar(15) NOT NULL,
  `couleurs` varchar(64) NOT NULL,
  `activer` varchar(16) NOT NULL,
  `postheader` varchar(255) NOT NULL,
  `skey` varchar(40) NOT NULL,
  `lastMdpChange` bigint NOT NULL,
  `allPostDeleted` enum('0','1') NOT NULL,
  `isConfirm` enum('0','1') NOT NULL,
  `keyConfirm` varchar(100) NOT NULL,
  `notifsP` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pseudo` (`pseudo`),
  KEY `email` (`email`),
  KEY `datenaissance_2` (`datenaissance`)
) ENGINE=MyISAM AUTO_INCREMENT=21873 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_messages`
--

DROP TABLE IF EXISTS `rf_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idPseudo` int NOT NULL,
  `idTopic` int NOT NULL,
  `acces` tinyint NOT NULL,
  `visible` enum('0','1') NOT NULL,
  `message` text NOT NULL,
  `time` bigint NOT NULL,
  `idEdit` int NOT NULL,
  `timeEdit` bigint NOT NULL,
  `mobile` enum('0','1') NOT NULL,
  `ip` bigint NOT NULL,
  `helped` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idPseudo` (`idPseudo`),
  KEY `idTopic` (`idTopic`),
  KEY `time` (`time`),
  KEY `acces` (`acces`),
  KEY `idEdit` (`idEdit`),
  KEY `ip` (`ip`),
  FULLTEXT KEY `message` (`message`)
) ENGINE=MyISAM AUTO_INCREMENT=6028989 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_modact`
--

DROP TABLE IF EXISTS `rf_modact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_modact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int NOT NULL,
  `comod` bigint NOT NULL,
  `lastact` bigint NOT NULL,
  `online` int NOT NULL,
  `ronline` int NOT NULL,
  `pagesloaded` int NOT NULL,
  `ip` varchar(15) NOT NULL,
  `acces` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idM` (`idM`)
) ENGINE=InnoDB AUTO_INCREMENT=41242 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_modlog`
--

DROP TABLE IF EXISTS `rf_modlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_modlog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` bigint NOT NULL,
  `idM` int NOT NULL,
  `action` tinyint NOT NULL,
  `time` bigint NOT NULL,
  `log` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57251 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_modo`
--

DROP TABLE IF EXISTS `rf_modo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_modo` (
  `idM` int NOT NULL,
  `idF` int NOT NULL,
  UNIQUE KEY `idM` (`idM`,`idF`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_motifs`
--

DROP TABLE IF EXISTS `rf_motifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_motifs` (
  `id` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_mp`
--

DROP TABLE IF EXISTS `rf_mp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_mp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(60) NOT NULL,
  `auteur` int NOT NULL,
  `nbMess` int NOT NULL,
  `firstPost` int NOT NULL,
  `lastPost` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auteur` (`auteur`)
) ENGINE=MyISAM AUTO_INCREMENT=115486 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_mp_mbr`
--

DROP TABLE IF EXISTS `rf_mp_mbr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_mp_mbr` (
  `mpid` int NOT NULL,
  `idPseudo` int NOT NULL,
  `lu` enum('0','1') NOT NULL,
  `epingle` enum('0','1') NOT NULL,
  KEY `mpid` (`mpid`),
  KEY `idPseudo` (`idPseudo`),
  KEY `lu` (`lu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_mp_mess`
--

DROP TABLE IF EXISTS `rf_mp_mess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_mp_mess` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idMp` int NOT NULL,
  `idPseudo` int NOT NULL,
  `time` bigint NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idMp` (`idMp`),
  KEY `idPseudo` (`idPseudo`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=647688 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_news`
--

DROP TABLE IF EXISTS `rf_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `news` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `idPseudo` int NOT NULL,
  `icone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nbComments` int NOT NULL,
  `etat` enum('0','1','2') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `time` bigint NOT NULL,
  `vtime` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `idPseudo` (`idPseudo`),
  KEY `etat` (`etat`)
) ENGINE=MyISAM AUTO_INCREMENT=1379 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_news_coms`
--

DROP TABLE IF EXISTS `rf_news_coms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_news_coms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int NOT NULL,
  `idN` int NOT NULL,
  `comment` text NOT NULL,
  `time` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12001 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_newslog`
--

DROP TABLE IF EXISTS `rf_newslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_newslog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` bigint NOT NULL,
  `idM` int NOT NULL,
  `action` tinyint NOT NULL,
  `time` bigint NOT NULL,
  `log` varchar(20) NOT NULL,
  `idN` int NOT NULL,
  `nName` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36310 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_nosearch`
--

DROP TABLE IF EXISTS `rf_nosearch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_nosearch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int unsigned NOT NULL,
  `nbPosts` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idM` (`idM`),
  KEY `nbPosts` (`nbPosts`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb3 COMMENT='Exclus de la recherche par auteur';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_notifs`
--

DROP TABLE IF EXISTS `rf_notifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_notifs` (
  `member` int NOT NULL,
  `post` int NOT NULL,
  `time` bigint NOT NULL,
  `titre` varchar(67) NOT NULL,
  `autre` int NOT NULL,
  `autrep` int NOT NULL,
  KEY `member` (`member`),
  KEY `post` (`post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_notifsp`
--

DROP TABLE IF EXISTS `rf_notifsp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_notifsp` (
  `id` int NOT NULL,
  `pseudo` varchar(20) NOT NULL,
  `auteur` varchar(20) NOT NULL,
  `time` bigint NOT NULL,
  `lien` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_oubli`
--

DROP TABLE IF EXISTS `rf_oubli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_oubli` (
  `id` int NOT NULL,
  `key` varchar(32) NOT NULL,
  `when` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_poussver`
--

DROP TABLE IF EXISTS `rf_poussver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_poussver` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int NOT NULL,
  `donneur` int NOT NULL,
  `type` smallint NOT NULL,
  `post` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post` (`post`),
  KEY `type` (`type`),
  KEY `donneur` (`donneur`),
  KEY `idM` (`idM`)
) ENGINE=InnoDB AUTO_INCREMENT=28507 DEFAULT CHARSET=utf8mb3 COMMENT='Stocke les pouces verts/rouges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_sanctions`
--

DROP TABLE IF EXISTS `rf_sanctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_sanctions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` smallint NOT NULL,
  `user` int NOT NULL,
  `modo` int NOT NULL,
  `catg` int NOT NULL,
  `motif` text NOT NULL,
  `time` bigint NOT NULL,
  `duree` bigint NOT NULL,
  `cumul` int NOT NULL,
  `posts` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1623 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_sanctions_posts`
--

DROP TABLE IF EXISTS `rf_sanctions_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_sanctions_posts` (
  `idS` int NOT NULL,
  `idP` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_snoop`
--

DROP TABLE IF EXISTS `rf_snoop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_snoop` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin` int NOT NULL,
  `date` bigint NOT NULL,
  `user` int NOT NULL,
  `motif` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_topics`
--

DROP TABLE IF EXISTS `rf_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(67) NOT NULL,
  `idForum` int NOT NULL,
  `auteur` int NOT NULL,
  `nbMess` int NOT NULL,
  `epingle` enum('0','1') NOT NULL,
  `locked` enum('0','1') NOT NULL,
  `visible` enum('0','1') NOT NULL,
  `firstMsg` int NOT NULL,
  `lastMsg` int NOT NULL,
  `acces` tinyint NOT NULL,
  `lastMsgTime` bigint NOT NULL,
  `archive` enum('0','1') NOT NULL DEFAULT '0',
  `resolu` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idForum` (`idForum`),
  KEY `lastMsgTime` (`lastMsgTime`),
  KEY `titre` (`titre`),
  KEY `lastMsg` (`lastMsg`),
  KEY `locked` (`locked`),
  KEY `visible` (`visible`),
  KEY `auteur` (`auteur`),
  KEY `epingle` (`epingle`),
  KEY `acces` (`acces`),
  KEY `firstMsg` (`firstMsg`)
) ENGINE=MyISAM AUTO_INCREMENT=554571 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_trombi`
--

DROP TABLE IF EXISTS `rf_trombi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_trombi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idM` int NOT NULL,
  `url` varchar(255) NOT NULL,
  `valide` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=823 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rf_uploads`
--

DROP TABLE IF EXISTS `rf_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rf_uploads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `idM` int NOT NULL,
  `nomfile` varchar(255) NOT NULL,
  `size` int NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `time` bigint NOT NULL,
  `m` int NOT NULL DEFAULT '0' COMMENT '1 si via mobile',
  PRIMARY KEY (`id`),
  KEY `idM` (`idM`),
  KEY `m` (`m`)
) ENGINE=InnoDB AUTO_INCREMENT=68039 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shalenity`
--

DROP TABLE IF EXISTS `shalenity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shalenity` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(15) NOT NULL,
  `passe` varchar(32) NOT NULL,
  `email` varchar(320) NOT NULL,
  `emailVu` enum('0','1') NOT NULL,
  `banniMp` enum('0','1') NOT NULL,
  `sexe` enum('','m','f') NOT NULL,
  `age` tinyint NOT NULL,
  `pays` varchar(11) NOT NULL,
  `console1` varchar(11) NOT NULL,
  `console2` varchar(11) NOT NULL,
  `console3` varchar(11) NOT NULL,
  `console4` varchar(11) NOT NULL,
  `msn` varchar(320) NOT NULL,
  `presentation` text NOT NULL,
  `avatar` text NOT NULL,
  `acces` tinyint NOT NULL,
  `timestamp` int unsigned NOT NULL,
  `clef` varchar(255) NOT NULL,
  `valide` enum('0','1') NOT NULL,
  `verif` varchar(10) NOT NULL,
  `signature` varchar(250) NOT NULL,
  `connecterA` int unsigned NOT NULL,
  `dernierPassage` int unsigned NOT NULL,
  `activerSmileyTitre` enum('0','1') NOT NULL,
  `argent` varchar(225) NOT NULL,
  `couleur` varchar(225) NOT NULL,
  `police` varchar(225) NOT NULL,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1152 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_pers`
--

DROP TABLE IF EXISTS `stats_pers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stats_pers` (
  `id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_toptopics`
--

DROP TABLE IF EXISTS `stats_toptopics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stats_toptopics` (
  `date` varchar(7) NOT NULL,
  `forum` int NOT NULL,
  `idT` int NOT NULL,
  `nb` int NOT NULL,
  KEY `date` (`date`,`forum`),
  KEY `forum` (`forum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_toptopicsu`
--

DROP TABLE IF EXISTS `stats_toptopicsu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stats_toptopicsu` (
  `date` varchar(7) NOT NULL,
  `idM` int NOT NULL,
  `idF` int NOT NULL,
  `idT` int NOT NULL,
  `nb` int NOT NULL,
  KEY `date` (`date`,`idM`),
  KEY `idM` (`idM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-08  2:32:08
