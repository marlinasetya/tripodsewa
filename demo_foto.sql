-- Assign existing jpg files to demo alat (TR001-VB002)
UPDATE alat SET foto = '69da2301a507d.jpg' WHERE no_alat = 'TR001';
UPDATE alat SET foto = '69da2346c4ba0.jpg' WHERE no_alat = 'TR002'; 
UPDATE alat SET foto = '69da2386da534.jpg' WHERE no_alat = 'VB001';
UPDATE alat SET foto = '69da218143e7c.jpg' WHERE no_alat = 'VB002';

-- Run: mysql -u root -p sewa_tripod_db < demo_foto.sql
-- Then reload user/alat.php - foto muncul!

