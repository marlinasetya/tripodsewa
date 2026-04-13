-- Demo peminjaman for testing TERLAMBAT / RUSAK / DENDA
-- Run in phpMyAdmin sewa_tripod_db → SQL after inserts_alat.sql
-- Assumes users: id=3 'Pelanggan 1', alat id=1 'TR001', id=2 'TR002'
-- Clear old demo first

DELETE FROM peminjaman WHERE catatan LIKE '%DEMO%';

-- 1. Normal (denda=0, on-time)
INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, total_harga, tanggal_sewa, tanggal_kembali, tanggal_dikembalikan, denda, status, catatan) VALUES
(3, 1, 1, 150000, 150000, '2024-10-01', '2024-10-02', '2024-10-02', 0, 'dikembalikan', 'DEMO Normal - no late');

-- 2. Late (auto calc 3 days * 100k *1 = 300k)
INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, total_harga, tanggal_sewa, tanggal_kembali, tanggal_dikembalikan, denda, status, catatan) VALUES
(3, 2, 1, 250000, 750000, '2024-10-10', '2024-10-12', '2024-10-15', 300000, 'dikembalikan', 'DEMO Late 3 days');

-- 3. Rusak (manual denda 50000)
INSERT INTO peminjaman (id_user, id_alat, jumlah, harga_per_hari, total_harga, tanggal_sewa, tanggal_kembali, tanggal_dikembalikan, denda, status, catatan) VALUES
(3, 1, 2, 150000, 600000, '2024-10-20', '2024-10-22', '2024-10-22', 50000, 'rusak', 'DEMO Rusak - manual denda');

-- Verify: SELECT * FROM peminjaman WHERE catatan LIKE '%DEMO%';
-- Test print: http://localhost/tripod/petugas/cetak_struk.php?id=LAST_INSERT_ID (adjust id)

