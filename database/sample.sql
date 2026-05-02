-- Sample database for proyekmbd
-- Import this file in phpMyAdmin after selecting the target database.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `barang`;
DROP TABLE IF EXISTS `kategori`;

CREATE TABLE `kategori` (
  `id_kategori` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_kategori`),
  UNIQUE KEY `uk_kategori_nama` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barang` (
  `id_barang` VARCHAR(20) NOT NULL,
  `nama_barang` VARCHAR(150) NOT NULL,
  `harga_jual` INT UNSIGNED NOT NULL,
  `harga_beli` INT UNSIGNED NOT NULL,
  `id_kategori` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_barang`),
  KEY `idx_barang_kategori` (`id_kategori`),
  CONSTRAINT `fk_barang_kategori`
    FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
  (1, 'Alat Tulis'),
  (2, 'Peralatan Kantor'),
  (3, 'Elektronik Kecil'),
  (4, 'Kebersihan'),
  (5, 'Rumah Tangga'),
  (6, 'Makanan & Minuman');

INSERT INTO `barang` (`id_barang`, `nama_barang`, `harga_jual`, `harga_beli`, `id_kategori`) VALUES
  ('BRG-001', 'Buku Tulis A5 58 Lembar', 8500, 6000, 1),
  ('BRG-002', 'Pensil 2B Premium', 3500, 2000, 1),
  ('BRG-003', 'Pulpen Gel Hitam 0.5 mm', 7000, 4500, 1),
  ('BRG-004', 'Spidol Whiteboard Biru', 9500, 6500, 1),
  ('BRG-005', 'Penghapus Karet Putih', 2500, 1500, 1),
  ('BRG-006', 'Stapler Mini No.10', 15000, 11000, 2),
  ('BRG-007', 'Isi Staples No.10', 5000, 3200, 2),
  ('BRG-008', 'Map Folder Plastik', 6000, 3800, 2),
  ('BRG-009', 'Lakban Cokelat 2 Inch', 12000, 8000, 2),
  ('BRG-010', 'Kertas HVS A4 80gsm (1 Rim)', 55000, 47000, 2),
  ('BRG-011', 'Mouse Wireless Optical', 65000, 54000, 3),
  ('BRG-012', 'Keyboard USB Standard', 85000, 72000, 3),
  ('BRG-013', 'Lampu LED 12 Watt', 18000, 12000, 3),
  ('BRG-014', 'Kipas Mini USB', 45000, 36000, 3),
  ('BRG-015', 'Power Bank 10000 mAh', 175000, 145000, 3),
  ('BRG-016', 'Sabun Cuci Piring 800 ml', 14000, 9500, 4),
  ('BRG-017', 'Pembersih Lantai 1 Liter', 16000, 10500, 4),
  ('BRG-018', 'Tisu Gulung Ekstra', 9000, 6000, 4),
  ('BRG-019', 'Spons Cuci Piring 3 Pack', 7000, 4200, 4),
  ('BRG-020', 'Cairan Pewangi Ruangan', 22000, 15000, 4),
  ('BRG-021', 'Gelas Kaca 250 ml', 12000, 8000, 5),
  ('BRG-022', 'Piring Makan Keramik', 18000, 12000, 5),
  ('BRG-023', 'Mangkok Plastik Set 6', 25000, 17000, 5),
  ('BRG-024', 'Rice Cooker 1.8 Liter', 275000, 235000, 5),
  ('BRG-025', 'Wajan Anti Lengket 30 cm', 65000, 52000, 5),
  ('BRG-026', 'Teh Celup 25 Sachet', 11000, 7500, 6),
  ('BRG-027', 'Kopi Bubuk 100 Gram', 15000, 9800, 6),
  ('BRG-028', 'Gula Pasir 1 Kg', 16500, 13200, 6),
  ('BRG-029', 'Biskuit Cokelat 8 Pack', 24000, 18000, 6),
  ('BRG-030', 'Susu UHT 1 Liter', 19000, 15500, 6);

SET FOREIGN_KEY_CHECKS = 1;
