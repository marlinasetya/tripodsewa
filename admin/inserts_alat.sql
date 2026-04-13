-- Buat 10 Tripod + 10 Video Booth (total 20 alat)
-- Kategori: 1 = Tripod, 2 = Video Booth
-- Run in phpMyAdmin SQL tab or mysql -u root -p sewa_tripod_db < inserts_alat.sql
-- Foto/galeri kosong, isi via admin/alat.php Edit

DELETE FROM alat WHERE no_alat LIKE 'TR%' OR no_alat LIKE 'VB%'; -- Clear existing

INSERT INTO alat (id_kategori, no_alat, nama_alat, harga_per_hari, deskripsi, stok, stok_cacat, galeri_utripot, galeri_booth) VALUES

-- 10 TRIPOD (Kategori 1)
(1, 'TR001', 'Tripod Manfrotto MT055XPRO3', 150000, 'Tripod aluminium heavy duty untuk kamera DSLR', 8, 2, '[]', '[]'),
(1, 'TR002', 'Tripod Benro Aero 4', 250000, 'Tripod karbon fiber ringan dan stabil', 5, 0, '[]', '[]'),
(1, 'TR003', 'Tripod Manfrotto 190X', 120000, 'Tripod portable untuk travel', 12, 1, '[]', '[]'),
(1, 'TR004', 'Tripod Benro TAD28C', 180000, 'Tripod compact travel dengan quick lock', 6, 0, '[]', '[]'),
(1, 'TR005', 'Tripod Velbon Neo V-10', 80000, 'Tripod budget untuk beginner', 15, 3, '[]', '[]'),
(1, 'TR006', 'Tripod Sirui ET-2204', 220000, 'Tripod carbon fiber lightweight 4 section', 9, 0, '[]', '[]'),
(1, 'TR007', 'Tripod Gitzo GT1545T', 350000, 'Tripod premium series 1 traveler', 4, 1, '[]', '[]'),
(1, 'TR008', 'Tripod Vanguard Alta Pro 263AB', 200000, 'Tripod multi angle position', 7, 2, '[]', '[]'),
(1, 'TR009', 'Tripod MeFOTO RoadTrip Pro', 160000, 'Tripod travel convertible monopod', 10, 0, '[]', '[]'),
(1, 'TR010', 'Tripod K&F Concept TM2534T', 95000, 'Tripod titanium alloy tall', 11, 1, '[]', '[]'),

-- 10 VIDEO BOOTH (Kategori 2)
(2, 'VB001', 'Video Booth Mirror Magic', 500000, 'Interactive mirror photo booth', 4, 1, '[]', '[]'),
(2, 'VB002', 'Video Booth 360 Spin Cam', 750000, '360 degree rotating video booth', 3, 0, '[]', '[]'),
(2, 'VB003', 'Video Booth LED Ring Light', 600000, 'Booth with professional ring light', 5, 2, '[]', '[]'),
(2, 'VB004', 'Video Booth Selfie Pod', 400000, 'Auto selfie video booth', 7, 0, '[]', '[]'),
(2, 'VB005', 'Video Booth Magic Mirror Pro', 550000, 'Magic mirror with touch screen effects', 4, 1, '[]', '[]'),
(2, 'VB006', 'Video Booth Open Air 360', 800000, 'Open air 360 spinner booth', 2, 0, '[]', '[]'),
(2, 'VB007', 'Video Booth LED Wall', 650000, 'LED wall backdrop booth', 6, 1, '[]', '[]'),
(2, 'VB008', 'Video Booth Flower Wall', 450000, 'Floral backdrop photo booth', 8, 2, '[]', '[]'),
(2, 'VB009', 'Video Booth Boomerang GIF', 350000, 'GIF boomerang short video booth', 9, 0, '[]', '[]'),
(2, 'VB010', 'Video Booth Slow Motion', 700000, 'High speed slow motion camera booth', 3, 1, '[]', '[]');

-- Ensure galleries are JSON array
UPDATE alat SET galeri_utripot = JSON_ARRAY(), galeri_booth = JSON_ARRAY() WHERE galeri_utripot IS NULL OR galeri_booth IS NULL;
UPDATE alat SET stok_cacat = 0 WHERE stok_cacat IS NULL;

-- Reload admin/alat.php → Daftar Alat (20)
