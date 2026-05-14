-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: db_toko_1
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `id_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `harga_beli` decimal(10,2) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  PRIMARY KEY (`id_barang`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES ('B001','Cup Plastik 12 oz',5000.00,3000.00,2),('B002','Tutup Cup',2000.00,1000.00,2),('B003','Box Makanan',12000.00,8000.00,3),('B004','Sedotan Bening',16000.00,13000.00,4),('B005','Sedotan Berwarna',18000.00,14000.00,4),('B006','Sendok Makan Putih',14000.00,11500.00,5),('B007','Sendok Makan Hitam',13500.00,11000.00,5),('B008','Sendok Teh',11000.00,9000.00,5),('B009','Garpu Bening',13000.00,11000.00,6),('B010','Garpu Hitam',12500.00,10000.00,6),('B011','Piring Kue',6000.00,2500.00,7),('B012','Toples Kecil',9500.00,7500.00,8),('B013','Toples Sedang',11500.00,8500.00,8),('B014','Toples Besar',13500.00,10000.00,8),('B015','Cup Saus',8000.00,6000.00,2);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_pembelian`
--

DROP TABLE IF EXISTS `detail_pembelian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pembelian` (
  `iddetail_pembelian` int NOT NULL AUTO_INCREMENT,
  `id_barang` varchar(20) DEFAULT NULL,
  `id_pembelian` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga_beli` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`iddetail_pembelian`),
  KEY `id_barang` (`id_barang`),
  KEY `id_pembelian` (`id_pembelian`),
  CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pembelian`
--

LOCK TABLES `detail_pembelian` WRITE;
/*!40000 ALTER TABLE `detail_pembelian` DISABLE KEYS */;
INSERT INTO `detail_pembelian` VALUES (1,'B001',1,10,3000.00,30000.00),(2,'B002',1,20,1000.00,20000.00),(3,'B003',2,10,8000.00,80000.00),(4,'B011',2,20,2500.00,50000.00),(5,'B006',3,10,11500.00,115000.00),(6,'B007',3,10,11000.00,110000.00),(7,'B002',3,20,1000.00,20000.00),(8,'B015',4,10,6000.00,60000.00),(9,'B013',5,40,8500.00,340000.00),(10,'B014',5,10,10000.00,100000.00),(11,'B011',5,2,2500.00,5000.00),(12,'B004',6,20,13000.00,260000.00),(13,'B005',6,10,14000.00,140000.00),(14,'B008',6,10,9000.00,90000.00),(15,'B012',7,40,7500.00,300000.00),(16,'B015',7,10,6000.00,60000.00);
/*!40000 ALTER TABLE `detail_pembelian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penjualan` (
  `iddetail_penjualan` int NOT NULL AUTO_INCREMENT,
  `id_barang` varchar(20) DEFAULT NULL,
  `id_penjualan` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`iddetail_penjualan`),
  KEY `id_barang` (`id_barang`),
  KEY `id_penjualan` (`id_penjualan`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=889 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (1,'B001',1,4,5000.00,20000.00),(2,'B002',1,7,2000.00,14000.00),(3,'B001',2,1,5000.00,5000.00),(4,'B006',3,1,14000.00,14000.00),(5,'B004',4,1,16000.00,16000.00),(6,'B015',5,2,8000.00,16000.00),(7,'B005',6,2,18000.00,36000.00),(8,'B014',7,3,13500.00,40500.00),(9,'B012',8,5,9500.00,47500.00),(10,'B003',9,5,12000.00,60000.00),(11,'B011',10,10,6000.00,60000.00),(12,'B003',11,2,12000.00,24000.00),(13,'B006',11,2,14000.00,28000.00),(14,'B009',11,2,13000.00,26000.00),(15,'B015',12,10,8000.00,80000.00),(16,'B005',13,5,18000.00,90000.00),(17,'B013',14,10,11500.00,115000.00),(18,'B011',15,20,6000.00,120000.00),(19,'B008',16,12,11000.00,132000.00),(20,'B014',17,10,13500.00,135000.00),(21,'B006',18,10,14000.00,140000.00),(22,'B009',19,20,13000.00,260000.00),(23,'B007',20,20,13500.00,270000.00),(24,'B001',21,100,5000.00,500000.00),(500,'B001',100,1,5000.00,5000.00),(501,'B004',100,1,16000.00,16000.00);
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (1,'Plastik'),(2,'Cup'),(3,'Box'),(4,'Sedotan'),(5,'Sendok'),(6,'Garpu'),(7,'Piring'),(8,'Toples');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembelian`
--

DROP TABLE IF EXISTS `pembelian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembelian` (
  `id_pembelian` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `id_supplier` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_pembelian`),
  KEY `id_user` (`id_user`),
  KEY `id_supplier` (`id_supplier`),
  CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  CONSTRAINT `pembelian_ibfk_2` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian`
--

LOCK TABLES `pembelian` WRITE;
/*!40000 ALTER TABLE `pembelian` DISABLE KEYS */;
INSERT INTO `pembelian` VALUES (1,1,1,'2026-03-20',50000.00),(2,1,3,'2026-03-28',130000.00),(3,1,3,'2026-04-11',245000.00),(4,1,2,'2026-04-15',60000.00),(5,1,3,'2026-04-21',445000.00),(6,1,2,'2026-05-01',490000.00),(7,1,1,'2026-05-14',360000.00);
/*!40000 ALTER TABLE `pembelian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `id_penjualan` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_penjualan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (1,2,'2026-03-21',34000.00),(2,2,'2026-03-21',5000.00),(3,2,'2026-03-21',14000.00),(4,2,'2026-03-22',16000.00),(5,2,'2026-03-22',16000.00),(6,2,'2026-03-22',36000.00),(7,2,'2026-03-22',40500.00),(8,2,'2026-03-23',47500.00),(9,2,'2026-03-24',60000.00),(10,2,'2026-03-27',60000.00),(11,2,'2026-03-27',78000.00),(12,2,'2026-03-27',80000.00),(13,2,'2026-03-27',90000.00),(14,2,'2026-03-27',115000.00),(15,2,'2026-03-27',120000.00),(16,2,'2026-03-28',132000.00),(17,2,'2026-03-28',135000.00),(18,2,'2026-03-29',140000.00),(19,2,'2026-03-29',260000.00),(20,2,'2026-03-29',270000.00),(21,2,'2026-03-30',500000.00),(100,2,'2026-03-23',21000.00);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier` (
  `id_supplier` int NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(100) DEFAULT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'PT Sumber Plastik','08123456789'),(2,'CV Kemasan Jaya','08234567890'),(3,'PT Anugrah Kemasan','08514667219');
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Admin','admin','12345','admin'),(2,'Kasir','kasir','12345','kasir');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'db_toko_1'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-14 12:06:47
