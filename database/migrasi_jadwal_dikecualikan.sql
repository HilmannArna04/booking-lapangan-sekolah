-- Jalankan satu kali pada database db_booking_lapangan yang sudah ada.
-- Tabel ini mencatat slot yang sengaja dihapus admin agar generator otomatis
-- tidak membuat slot yang sama kembali. Tidak menghapus/mengubah data lama.

CREATE TABLE IF NOT EXISTS `jadwal_dikecualikan` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lapangan_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `dihapus_oleh` bigint(20) UNSIGNED DEFAULT NULL,
  `dihapus_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_jadwal_dikecualikan` (`lapangan_id`,`tanggal`,`jam_mulai`,`jam_selesai`),
  KEY `idx_jadwal_dikecualikan_tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
