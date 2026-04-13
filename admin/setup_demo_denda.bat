@echo off
echo Installing demo denda data...
echo Run XAMPP MySQL first! DB: sewa_tripod_db
mysql -u root -p sewa_tripod_db ^< admin\sample_peminjaman.sql
echo Demo data installed. Check petugas\laporan.php or cetak_struk.php?id=XX
pause

