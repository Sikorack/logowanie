-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Wersja serwera:               8.0.39 - Source distribution
-- Serwer OS:                    FreeBSD14.1
-- HeidiSQL Wersja:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Zrzut struktury bazy danych logowanie
CREATE DATABASE IF NOT EXISTS `logowanie` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `logowanie`;

-- Zrzut struktury tabela logowanie.fingerprint
CREATE TABLE IF NOT EXISTS `fingerprint` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fingerprint` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` char(32) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fingerprint_userid_unique` (`fingerprint`,`user_id`),
  KEY `fp_user_id_fk` (`user_id`),
  CONSTRAINT `fp_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Eksport danych został odznaczony.

-- Zrzut struktury tabela logowanie.tokens
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` char(32) COLLATE utf8mb4_general_ci NOT NULL,
  `fp_id` int NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_unique` (`token`),
  KEY `token_userid_fk` (`user_id`),
  KEY `token_fingerprint_fk` (`fp_id`),
  CONSTRAINT `token_fingerprint_fk` FOREIGN KEY (`fp_id`) REFERENCES `fingerprint` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `token_userid_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Eksport danych został odznaczony.

-- Zrzut struktury tabela logowanie.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(32) COLLATE utf8mb4_general_ci NOT NULL,
  `mail` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `login` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci NOT NULL,
  `hash` char(60) COLLATE utf8mb4_general_ci NOT NULL,
  `ustawienieWeryfikacji` tinyint NOT NULL,
  `tylkoKlucz` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`mail`),
  UNIQUE KEY `login_unique` (`login`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Eksport danych został odznaczony.

-- Zrzut struktury tabela logowanie.ykfido
CREATE TABLE IF NOT EXISTS `ykfido` (
  `credential_id` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` char(32) COLLATE utf8mb4_general_ci NOT NULL,
  `publicKey` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `sign_count` int NOT NULL DEFAULT '0',
  `nazwa` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`credential_id`),
  KEY `users_id_fk` (`user_id`),
  CONSTRAINT `users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Eksport danych został odznaczony.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
