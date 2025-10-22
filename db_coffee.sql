-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_coffee
CREATE DATABASE IF NOT EXISTS `db_coffee` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_coffee`;

-- Dumping structure for table db_coffee.tb_admin
CREATE TABLE IF NOT EXISTS `tb_admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir','manager') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_admin: ~1 rows (approximately)
INSERT INTO `tb_admin` (`id_admin`, `username`, `password`, `nama_lengkap`, `role`, `email`, `status`, `created_at`, `last_login`, `created_by`) VALUES
	(1, 'admin', 'admin123', 'Administrator', 'admin', 'admin@starcoffee.com', 'active', NULL, '2025-10-13 01:33:30', NULL);

-- Dumping structure for table db_coffee.tb_detailpembayaran
CREATE TABLE IF NOT EXISTS `tb_detailpembayaran` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_pembayaran` int DEFAULT NULL,
  `id_pesanan` int DEFAULT NULL,
  `jumlah_pesanan` int DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  PRIMARY KEY (`id_detail`) USING BTREE,
  KEY `FK_tb_detailpembayaran_tb_pembayaran` (`id_pembayaran`),
  KEY `FK_tb_detailpembayaran_tb_pesanan` (`id_pesanan`),
  KEY `Index 4` (`id_menu`),
  CONSTRAINT `FK_tb_detailpembayaran_tb_menu` FOREIGN KEY (`id_menu`) REFERENCES `tb_menu` (`id_menu`),
  CONSTRAINT `FK_tb_detailpembayaran_tb_pembayaran` FOREIGN KEY (`id_pembayaran`) REFERENCES `tb_pembayaran` (`id_pembayaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tb_detailpembayaran_tb_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `tb_pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_detailpembayaran: ~53 rows (approximately)
INSERT INTO `tb_detailpembayaran` (`id_detail`, `id_pembayaran`, `id_pesanan`, `jumlah_pesanan`, `harga_satuan`, `subtotal`, `id_menu`) VALUES
	(1, NULL, 36, 1, 18000.00, 18000.00, NULL),
	(2, NULL, 37, 1, 17000.00, 17000.00, NULL),
	(3, NULL, 37, 1, 18000.00, 18000.00, NULL),
	(4, NULL, 38, 1, 17000.00, 17000.00, NULL),
	(5, NULL, 38, 1, 18000.00, 18000.00, NULL),
	(6, NULL, 39, 1, 15000.00, 15000.00, NULL),
	(7, NULL, 40, 1, 15000.00, 15000.00, NULL),
	(8, NULL, 41, 1, 16000.00, 16000.00, NULL),
	(9, NULL, 42, 1, 15000.00, 15000.00, NULL),
	(10, NULL, 44, 1, 17000.00, 17000.00, NULL),
	(11, NULL, 45, 4, 17000.00, 68000.00, NULL),
	(12, NULL, 48, 1, 8000.00, 8000.00, NULL),
	(13, NULL, 49, 1, 18000.00, 18000.00, 8),
	(14, NULL, 49, 1, 15000.00, 15000.00, 11),
	(15, NULL, 49, 1, 16000.00, 16000.00, 12),
	(16, NULL, 50, 2, 13000.00, 26000.00, 2),
	(17, NULL, 51, 1, 16000.00, 16000.00, 6),
	(18, NULL, 51, 1, 18000.00, 18000.00, 8),
	(19, NULL, 52, 1, 17000.00, 17000.00, 3),
	(20, NULL, 55, 1, 13000.00, 13000.00, 2),
	(21, NULL, 55, 1, 17000.00, 17000.00, 3),
	(22, NULL, 57, 2, 13000.00, 26000.00, 2),
	(23, NULL, 57, 1, 17000.00, 17000.00, 3),
	(24, NULL, 58, 1, 13000.00, 13000.00, 2),
	(25, NULL, 58, 1, 8000.00, 8000.00, 5),
	(26, NULL, 64, 2, 19000.00, 38000.00, 10),
	(27, NULL, 66, 1, 15000.00, 15000.00, 11),
	(28, NULL, 68, 1, 15000.00, 15000.00, 11),
	(34, NULL, 73, 1, 16000.00, 16000.00, 12),
	(35, NULL, 73, 1, 15000.00, 15000.00, 11),
	(36, NULL, 73, 1, 18000.00, 18000.00, 8),
	(37, NULL, 74, 2, 13000.00, 26000.00, 2),
	(38, NULL, 76, 2, 15000.00, 30000.00, 1),
	(39, NULL, 77, 1, 12000.00, 12000.00, 7),
	(40, NULL, 78, 1, 12000.00, 12000.00, 7),
	(41, NULL, 79, 1, 18000.00, 18000.00, 8),
	(42, NULL, 83, 1, 8000.00, 8000.00, 5),
	(44, NULL, 85, 1, 10800.00, 10800.00, 7),
	(45, NULL, 86, 1, 17000.00, 17000.00, 9),
	(46, NULL, 87, 1, 7200.00, 7200.00, 5),
	(47, NULL, 88, 1, 15300.00, 15300.00, 9),
	(48, NULL, 89, 1, 15000.00, 15000.00, 1),
	(49, NULL, 90, 1, 15300.00, 15300.00, 3),
	(50, NULL, 91, 1, 15000.00, 15000.00, 1),
	(51, NULL, 92, 2, 15000.00, 30000.00, 1),
	(52, NULL, 93, 1, 15300.00, 15300.00, 3),
	(53, NULL, 94, 1, 15300.00, 15300.00, 3),
	(54, NULL, 95, 1, 15300.00, 15300.00, 3),
	(55, NULL, 96, 2, 15000.00, 30000.00, 1),
	(56, NULL, 97, 1, 15000.00, 15000.00, 11),
	(57, NULL, 98, 2, 13000.00, 26000.00, 2),
	(60, NULL, 100, 1, 14400.00, 14400.00, 6),
	(61, NULL, 100, 1, 15300.00, 15300.00, 3),
	(62, NULL, 101, 3, 13000.00, 39000.00, 2),
	(63, NULL, 102, 2, 13000.00, 26000.00, 2),
	(64, NULL, 103, 2, 13000.00, 26000.00, 2),
	(65, NULL, 104, 2, 13000.00, 26000.00, 2),
	(66, NULL, 105, 2, 13000.00, 26000.00, 2),
	(67, NULL, 106, 2, 15000.00, 30000.00, 1),
	(68, NULL, 107, 2, 11700.00, 23400.00, 2),
	(69, NULL, 108, 2, 7200.00, 14400.00, 5),
	(70, NULL, 109, 1, 13000.00, 13000.00, 2),
	(71, NULL, 110, 1, 17000.00, 17000.00, 9),
	(72, NULL, 111, 2, 11700.00, 23400.00, 2);

-- Dumping structure for table db_coffee.tb_member
CREATE TABLE IF NOT EXISTS `tb_member` (
  `id_member` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `alamat` text,
  `tanggal_daftar` date DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_member`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_member: ~5 rows (approximately)
INSERT INTO `tb_member` (`id_member`, `nama`, `email`, `password`, `telepon`, `alamat`, `tanggal_daftar`, `status`) VALUES
	(2, 'yeti', 'yetiyuliani1978@gmail.com', '$2y$10$3qT88dM4Mj9IqK1.5HQ1xuOWce6Xogn.UmzEEatbaN5Sz3mMVoZCS', '0831122876', 'pulosari', '2025-09-23', 'active'),
	(3, 'tita', 'tita@gmail.com', '$2y$10$gbJPQGHw90JmqNyi/LRTmuPS0z3vXyx3h8U5xb/pi7A7j7P2kkXTS', '08765', 'pulosari', '2025-10-08', 'active'),
	(4, 'riskalucu', 'riskauwu@gmail.com', '$2y$10$Q2iUiygs909Cm5xoDkfmCu4fHX8jmoOY4qLDAwqzNy60.VdZwgnIy', '08776', 'campurjo', '2025-10-08', 'active'),
	(5, 'siti hartita', 'salma@gmail.com', '$2y$10$jVeQVPwsL.HS9gT4IDgzGOgdf.aEosThQvF7qDMPfYiZDpmmyf4KG', '0812345', 'raung', '2025-10-12', 'active'),
	(6, 'bismillah', 'yaya@gmail.com', '$2y$10$sbPHMHKuOw7b9iVR2XwMUe3EI71j6uEWCfEJIAFNl3bOVYBOOqmLu', '08123', 'jalan', '2025-10-13', 'active');

-- Dumping structure for table db_coffee.tb_menu
CREATE TABLE IF NOT EXISTS `tb_menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_menu`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_menu: ~12 rows (approximately)
INSERT INTO `tb_menu` (`id_menu`, `nama`, `harga_satuan`, `foto`) VALUES
	(1, 'Iced coffee mocha', 15000.00, 'products-coffee-1.png'),
	(2, 'Coffee with cream', 13000.00, 'products-coffee-2.png'),
	(3, 'Cappuccino coffee', 17000.00, 'products-coffee-3.png'),
	(4, 'Coffee with milk', 11000.00, 'products-coffee-4.png'),
	(5, 'Iced coffee', 8000.00, 'products-coffee-5.png'),
	(6, 'Classic iced coffee', 16000.00, 'products-coffee-6.png'),
	(7, 'Iced coffee frappe', 12000.00, 'products-coffee-7.png'),
	(8, 'Iced matcha coffee', 18000.00, 'products-coffee-8.png'),
	(9, 'Chocolate coffe', 17000.00, 'products-coffee-9.png'),
	(10, 'Shaken creamy latte', 19000.00, 'products-coffee-10.png'),
	(11, 'Cookies cream', 15000.00, 'products-coffee-11.png'),
	(12, 'Crunchy caramel', 16000.00, 'products-coffee-12.png');

-- Dumping structure for table db_coffee.tb_pembayaran
CREATE TABLE IF NOT EXISTS `tb_pembayaran` (
  `id_pembayaran` int NOT NULL AUTO_INCREMENT,
  `id_pesanan` int DEFAULT NULL,
  `id_pembeli` int DEFAULT NULL,
  `metode_pembayaran` enum('Y','N') DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT NULL,
  `total_harga` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `FK_tb_pembayaran_tb_pembeli` (`id_pembeli`),
  KEY `FK_tb_pembayaran_tb_pesanan` (`id_pesanan`),
  CONSTRAINT `FK_tb_pembayaran_tb_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `tb_pembeli` (`id_pembeli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tb_pembayaran_tb_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `tb_pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_pembayaran: ~0 rows (approximately)

-- Dumping structure for table db_coffee.tb_pembeli
CREATE TABLE IF NOT EXISTS `tb_pembeli` (
  `id_pembeli` int NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(100) DEFAULT NULL,
  `nomor_pembeli` varchar(15) DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`id_pembeli`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_pembeli: ~90 rows (approximately)
INSERT INTO `tb_pembeli` (`id_pembeli`, `nama_pembeli`, `nomor_pembeli`, `alamat`) VALUES
	(1, 'tita', '08765', 'jAA'),
	(2, 'tita', '08765', 'jAA'),
	(3, 'tita', '08765', 'jAA'),
	(4, 'tita', '08765', 'jAA'),
	(5, 'tita', '08765', 'jAA'),
	(6, 'tita', '08765', 'wilis'),
	(7, 'tita', '08765', 'wilis'),
	(8, 'tita', '08765', 'wilis'),
	(9, 'tita', '08765', 'wilis'),
	(10, 'tita', '08765', 'wilis'),
	(11, 'tita', '08765', 'wilis'),
	(12, 'tita', '08765', 'wilis'),
	(13, 'tita', '08765', 'wilis'),
	(14, 'tita', '08765', 'jjgugu'),
	(15, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(16, 'ajiuag', '09i', 'a,njkja'),
	(17, 'ajiuag', '09i', 'a,njkja'),
	(18, 'ajiuag', '09i', 'a,njkja'),
	(19, 'mkk', '08790', 'aihnajol'),
	(20, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(21, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(22, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(23, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(24, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(25, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(26, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(27, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(28, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(29, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(30, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(31, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(32, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(33, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(34, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(35, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(36, 'uihqiu', 'jkbdqj', 'jkanjkdnj'),
	(37, 'jbwqjkdkjhqdq', 'iqqdbjk', 'iojqdikjq'),
	(38, 'jbwqjkdkjhqdq', 'iqqdbjk', 'iojqdikjq'),
	(39, 'brilian', '0865437', 'xnqx nq'),
	(40, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(41, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(42, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(43, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(44, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(45, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(46, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(47, 'tita', '0865437', 'Jl.Veteran 36 Ke.Mojoroto'),
	(48, 'tita', '0865437', '3uihbrif'),
	(49, 'tita', '08765', 'Jl. Kh.agus Salim Bandarkidul'),
	(50, 'tita', '08765', 'jalan jaln'),
	(51, 'tita', '08765', 'jalan jaln'),
	(52, 'tita', '08765', 'jalan jaln'),
	(53, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(54, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(55, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(56, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(57, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(58, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(59, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(60, 'brilian', '41022230', 'melbourne, Amerika'),
	(61, 'brilian', '41022230', 'amerika'),
	(62, 'brilian', '41022230', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(63, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(64, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(65, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(66, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(67, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(68, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(69, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(70, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(71, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(72, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(73, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(74, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(75, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(76, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(77, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(78, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(79, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(80, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(81, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(82, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(83, 'tita', '08765', 'Jl.Raung no 136 Pulosari Mojoroto Kota Kediri'),
	(84, 'mukti', '081216318022', 'rumah tika'),
	(85, 'brilian', '0865437', 'JZJIKNHZJKZ'),
	(86, 'tita', '09876', 'jalan'),
	(87, 'tito', '0987766', 'jalannn'),
	(88, 'santoso', '09877', 'JALAN'),
	(89, 'brilian', '0865437', 'jaln'),
	(90, 'tita', '08765', 'jalan'),
	(91, 'yeti', '0831122876', 'jalan'),
	(92, 'tiat', '098y6', 'kjaxnkj'),
	(93, 'tita', '08765', 'jalan'),
	(94, 'tita', '08765', 'jalan'),
	(95, 'tita', '08765', 'jaalan'),
	(96, 'tita', '098765', 'jalan'),
	(97, 'tita', '08766', 'jalan'),
	(98, 'riska', '0876', 'jalan'),
	(99, 'riskalucu', '08776', 'raung'),
	(100, 'riska', '0876', 'jalan'),
	(101, 'riska', '0876', 'jalan'),
	(102, 'tita', '0812345', 'raung'),
	(103, 'tita', '0812345', 'raung'),
	(104, 'siti hartita', '0812345', 'raung'),
	(105, 'siti hartita', '0812345', 'raung'),
	(106, 'siti hartita', '0812345', 'raung'),
	(107, 'siti hartita', '0812345', 'raung'),
	(108, 'bismillah', '08123', 'jalan');

-- Dumping structure for table db_coffee.tb_pesanan
CREATE TABLE IF NOT EXISTS `tb_pesanan` (
  `id_pesanan` int NOT NULL AUTO_INCREMENT,
  `id_pembeli` int DEFAULT NULL,
  `is_member` tinyint DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `status_pesanan` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT NULL,
  `status_pembayaran` enum('paid','unpaid') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `metode_pembayaran` enum('gopay','dana','ovo','shopeepay','qris','transfer_bank') DEFAULT NULL,
  PRIMARY KEY (`id_pesanan`),
  KEY `FK_tb_pesanan_tb_pembeli` (`id_pembeli`),
  CONSTRAINT `FK_tb_pesanan_tb_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `tb_pembeli` (`id_pembeli`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_coffee.tb_pesanan: ~75 rows (approximately)
INSERT INTO `tb_pesanan` (`id_pesanan`, `id_pembeli`, `is_member`, `tanggal`, `catatan`, `status_pesanan`, `status_pembayaran`, `metode_pembayaran`) VALUES
	(25, 1, NULL, '2025-09-15 00:00:00', 'hqdwhujq', NULL, NULL, NULL),
	(26, 31, NULL, '2025-09-15 14:13:22', 'hqdwhujq', NULL, NULL, NULL),
	(27, 36, NULL, '2025-09-15 14:19:53', 'hqdwhujq', NULL, NULL, NULL),
	(28, 37, NULL, '2025-09-15 14:23:31', 'kjnqjkq', 'delivered', 'paid', 'transfer_bank'),
	(29, 38, NULL, '2025-09-15 14:23:55', 'kjnqjkq', NULL, NULL, NULL),
	(30, 39, NULL, '2025-09-16 04:32:43', 'oln', NULL, NULL, NULL),
	(31, 40, NULL, '2025-09-16 04:34:48', 'oln', NULL, NULL, NULL),
	(32, 41, NULL, '2025-09-16 04:35:11', 'oln', NULL, NULL, NULL),
	(33, 42, NULL, '2025-09-16 04:36:13', 'oln', NULL, NULL, NULL),
	(34, 43, NULL, '2025-09-16 04:36:17', 'oln', NULL, NULL, NULL),
	(35, 44, NULL, '2025-09-16 04:40:03', 'oln', NULL, NULL, NULL),
	(36, 45, NULL, '2025-09-16 04:42:14', 'oln', NULL, NULL, NULL),
	(37, 46, NULL, '2025-09-16 05:43:06', 'oln', NULL, NULL, NULL),
	(38, 47, NULL, '2025-09-16 05:54:20', 'oln', NULL, NULL, NULL),
	(39, 48, NULL, '2025-09-16 07:12:57', 'jree', NULL, NULL, NULL),
	(40, 49, NULL, '2025-09-16 07:32:19', 'hqdwhujq', NULL, NULL, NULL),
	(41, 50, NULL, '2025-09-16 16:27:46', 'kjnqjkq', NULL, NULL, NULL),
	(42, 51, NULL, '2025-09-16 17:11:08', 'kjnqjkq', NULL, NULL, NULL),
	(43, 52, NULL, '2025-09-16 17:20:35', 'kjnqjkq', NULL, NULL, NULL),
	(44, 53, NULL, '2025-09-17 00:34:44', 'kjnqjkq', NULL, NULL, NULL),
	(45, 54, NULL, '2025-09-17 00:52:11', 'kjnqjkq', NULL, NULL, NULL),
	(46, 55, NULL, '2025-09-17 01:02:15', 'kjnqjkq', NULL, NULL, NULL),
	(47, 56, NULL, '2025-09-17 01:03:54', 'kjnqjkq', NULL, NULL, NULL),
	(48, 59, NULL, '2025-09-17 01:41:54', 'kjnqjkq', NULL, NULL, NULL),
	(49, 60, NULL, '2025-09-17 01:59:07', 'caramel sedikit, macha yangbanyak, adah yg bagus, gula sedikit, es batunya secukupnya, terimkasih', NULL, NULL, NULL),
	(50, 61, NULL, '2025-09-17 09:07:52', 'panas', NULL, NULL, NULL),
	(51, 62, NULL, '2025-09-17 09:18:04', 'panas', NULL, NULL, NULL),
	(52, 63, NULL, '2025-09-17 09:20:26', 'kjnqjkq', NULL, NULL, NULL),
	(53, 64, NULL, '2025-09-17 09:56:01', 'kjnqjkq', NULL, NULL, NULL),
	(54, 65, NULL, '2025-09-17 09:59:29', 'kjnqjkq', NULL, NULL, NULL),
	(55, 66, NULL, '2025-09-17 17:10:34', 'kjnqjkq', NULL, NULL, NULL),
	(56, 67, NULL, '2025-09-17 17:15:52', 'kjnqjkq', NULL, NULL, NULL),
	(57, 68, NULL, '2025-09-17 17:17:31', 'panas', NULL, NULL, NULL),
	(58, 69, NULL, '2025-09-19 19:42:17', 'kjnqjkq', NULL, NULL, NULL),
	(59, 70, NULL, '2025-09-19 19:53:09', 'kjnqjkq', NULL, NULL, NULL),
	(60, 71, NULL, '2025-09-19 19:56:01', 'kjnqjkq', NULL, NULL, NULL),
	(61, 72, NULL, '2025-09-19 19:58:15', 'kjnqjkq', NULL, NULL, NULL),
	(62, 73, NULL, '2025-09-19 19:59:32', 'kjnqjkq', NULL, NULL, NULL),
	(63, 74, NULL, '2025-09-19 20:05:45', 'kjnqjkq', NULL, NULL, NULL),
	(64, 75, NULL, '2025-09-21 14:53:14', 'kjnqjkq', NULL, NULL, NULL),
	(65, 76, NULL, '2025-09-21 14:56:11', 'kjnqjkq', NULL, NULL, NULL),
	(66, 77, NULL, '2025-09-21 14:56:56', 'kjnqjkq', NULL, NULL, NULL),
	(67, 78, NULL, '2025-09-21 14:57:11', 'kjnqjkq', NULL, NULL, NULL),
	(68, 79, NULL, '2025-09-21 15:01:45', 'kjnqjkq', NULL, NULL, NULL),
	(73, 84, NULL, '2025-10-07 11:42:52', 'gula banyak sampai diabetes', 'confirmed', 'paid', 'gopay'),
	(74, 85, NULL, '2025-10-07 17:47:49', 'JNCASNJK', NULL, NULL, NULL),
	(75, 39, NULL, '2025-10-07 18:25:53', '', 'confirmed', 'unpaid', NULL),
	(76, 39, NULL, '2025-10-07 18:26:31', '', 'confirmed', 'unpaid', NULL),
	(77, 86, NULL, '2025-10-07 18:27:13', 'panas', 'preparing', 'unpaid', NULL),
	(78, 87, NULL, '2025-10-07 18:31:30', '', 'confirmed', 'unpaid', NULL),
	(79, 88, NULL, '2025-10-07 18:33:09', '', 'confirmed', 'unpaid', NULL),
	(80, 88, NULL, '2025-10-07 20:16:32', 'PANAS', 'confirmed', 'unpaid', NULL),
	(81, 88, NULL, '2025-10-07 20:17:08', 'PANAS', 'confirmed', 'unpaid', NULL),
	(82, 88, NULL, '2025-10-07 20:17:44', 'PANAS', 'confirmed', 'unpaid', NULL),
	(83, 89, NULL, '2025-10-07 20:39:56', 'panas', NULL, NULL, NULL),
	(85, 91, NULL, '2025-10-07 21:24:31', 'paans', 'confirmed', 'paid', 'ovo'),
	(86, 91, NULL, '2025-10-08 04:59:27', 'panas', 'confirmed', 'paid', 'ovo'),
	(87, 91, NULL, '2025-10-08 05:03:03', '', 'confirmed', 'unpaid', NULL),
	(88, 91, NULL, '2025-10-08 05:19:39', '', 'confirmed', 'paid', 'ovo'),
	(89, 92, NULL, '2025-10-08 05:20:32', '', 'confirmed', 'paid', 'ovo'),
	(90, 91, NULL, '2025-10-08 05:23:39', '', 'confirmed', 'paid', 'dana'),
	(91, 93, NULL, '2025-10-08 07:04:48', 'panas', NULL, NULL, NULL),
	(92, 94, NULL, '2025-10-08 07:05:37', 'panas', NULL, NULL, NULL),
	(93, 91, NULL, '2025-10-08 07:12:54', 'panas', 'confirmed', 'paid', 'dana'),
	(94, 91, NULL, '2025-10-08 07:17:02', '', 'confirmed', 'paid', 'dana'),
	(95, 91, NULL, '2025-10-08 07:24:28', '', 'confirmed', 'paid', 'dana'),
	(96, 95, NULL, '2025-10-08 07:39:18', 'panas', NULL, NULL, NULL),
	(97, 96, NULL, '2025-10-08 08:13:25', 'dinginnyaa', NULL, NULL, NULL),
	(98, 97, NULL, '2025-10-08 08:17:26', 'panas', NULL, NULL, NULL),
	(100, 99, NULL, '2025-10-08 08:24:05', 'panas/dingin', 'ready', 'paid', 'qris'),
	(101, 100, NULL, '2025-10-08 09:40:53', 'panas', NULL, NULL, NULL),
	(102, 101, NULL, '2025-10-08 09:48:56', 'panas', NULL, NULL, NULL),
	(103, 102, NULL, '2025-10-12 20:35:52', 'panas', NULL, NULL, NULL),
	(104, 103, NULL, '2025-10-12 20:54:03', 'panas', NULL, NULL, NULL),
	(105, 104, NULL, '2025-10-12 21:00:10', 'panas', NULL, NULL, NULL),
	(106, 105, NULL, '2025-10-12 21:18:14', 'panas', NULL, NULL, NULL),
	(107, 106, 1, '2025-10-12 21:49:25', 'panas', NULL, NULL, NULL),
	(108, 107, 1, '2025-10-12 21:50:10', 'dingin', NULL, NULL, NULL),
	(109, 99, 0, '2025-10-13 08:21:01', '', 'pending', 'paid', 'dana'),
	(110, 99, 0, '2025-10-13 08:24:07', '', 'pending', 'paid', 'gopay'),
	(111, 108, 1, '2025-10-13 08:34:28', 'panas', NULL, NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
