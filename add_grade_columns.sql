ALTER TABLE pendaftar 
ADD COLUMN nilai_k4_s1 FLOAT DEFAULT 0,
ADD COLUMN nilai_k4_s2 FLOAT DEFAULT 0,
ADD COLUMN nilai_k5_s1 FLOAT DEFAULT 0,
ADD COLUMN nilai_k5_s2 FLOAT DEFAULT 0,
ADD COLUMN nilai_k6_s1 FLOAT DEFAULT 0,
ADD COLUMN nilai_jumlah FLOAT DEFAULT 0;
-- nilai_rapor_rata2 already exists from previous migrations or schema_comprehensive.sql
-- If not, we can add it safely if we check. But let's just add the individual ones first.
