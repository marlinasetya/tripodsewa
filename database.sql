-- Database: sewa_tripod_db
-- Run: CREATE DATABASE sewa_tripod_db; then mysql -u root -p sewa_tripod_db < database.sql

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `alat` (
  `id_alat` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) NOT NULL,
  `no_alat` varchar(20) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `harga_per_hari` decimal(10,2) NOT NULL DEFAULT 0,
  `deskripsi` text,
  `stok` int(11) NOT NULL DEFAULT 0,
  `foto` varchar(255) DEFAULT NULL,
  `stok_cacat` int(11) NOT NULL DEFAULT 0,
  `galeri_utripot` JSON DEFAULT NULL,
  `galeri_booth` JSON DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alat`),
  FOREIGN KEY (`id_kategori`) REFERENCES `kategori`(`id_kategori`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing alat records to default gallery null
UPDATE alat SET galeri_utripot = JSON_ARRAY(), galeri_booth = JSON_ARRAY() WHERE galeri_utripot IS NULL OR galeri_booth IS NULL;

CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_alat` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `tanggal_sewa` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_dikembalikan` date NULL,
  `catatan` text,
  `denda` decimal(10,2) DEFAULT 0,
  `status` enum('menunggu','disetujui','terlambat','dikembalikan','rusak','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_peminjaman`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_alat`) REFERENCES `alat`(`id_alat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo Data
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Admin Utama', 'admin@sewatripod.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('Petugas 1', 'petugas@sewatripod.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas'), -- password: password
('Pelanggan 1', 'user@sewatripod.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'); -- password: password

INSERT INTO `kategori` (`nama_kategori`) VALUES
('Tripod'),
('Video Booth');


(1, 'TR001', 'Tripod Manfrotto MT055XPRO3', 150000, 'Tripod aluminium profesional dengan head ball 3D', 5, NULL),
(1, 'TR002', 'Tripod Benro Aero 4', 250000, 'Tripod karbon fiber ringan', 3, NULL),
(2, 'VB001', 'Video Booth Mirror', 500000, 'Photo booth interaktif dengan mirror effect', 2, NULL),
(2, 'VB002', 'Video Booth 360', 750000, 'Spin cam 360 derajat untuk video', 1, NULL);

-- View for riwayat peminjaman
CREATE OR REPLACE VIEW `riwayat_peminjaman` AS
SELECT 
  p.*,
  u.nama as nama_user,
  u.email,
  a.no_alat,
  a.nama_alat,
  k.nama_kategori,
  a.stok,
  a.stok_cacat
FROM peminjaman p
JOIN users u ON p.id_user = u.id_user
JOIN alat a ON p.id_alat = a.id_alat
JOIN kategori k ON a.id_kategori = k.id_kategori
ORDER BY p.created_at DESC;

-- End demo data
