PERBAIKAN HAPUS JADWAL OTOMATIS

Perubahan:
1. admin/jadwal/hapus.php:
   - Menolak penghapusan jika jadwal memiliki booking/riwayat booking.
   - Menyimpan slot yang dihapus ke jadwal_dikecualikan.
   - Menghapus jadwal dari daftar admin dalam transaksi database.
2. user/booking/index.php:
   - Generator jadwal otomatis mengecek jadwal_dikecualikan.
   - Slot yang sengaja dihapus admin tidak dibuat kembali untuk tanggal/lapangan/jam yang sama.
3. database/migrasi_jadwal_dikecualikan.sql:
   - SQL migrasi yang harus dijalankan sekali pada database aplikasi yang sedang digunakan.

PENTING:
- Sebelum mengganti file, backup folder proyek dan database.
- Buka phpMyAdmin, pilih database aplikasi yang benar (db_booking_lapangan), buka tab SQL, lalu jalankan isi file:
  database/migrasi_jadwal_dikecualikan.sql
- Jika database aplikasi Anda memiliki nama berbeda, ubah baris USE db_booking_lapangan; pada file migrasi menjadi nama database yang benar sebelum menjalankan.
- Setelah migrasi berhasil, ganti file proyek dengan versi dari ZIP ini.
- Jadwal yang memiliki booking tetap tidak dapat dihapus, termasuk booking yang statusnya dibatalkan, karena riwayatnya dipertahankan.
- Pengecualian berlaku untuk kombinasi lapangan, tanggal, jam mulai, dan jam selesai yang dihapus. Untuk membuat slot itu tersedia lagi, hapus baris yang sesuai dari tabel jadwal_dikecualikan melalui phpMyAdmin.


CATATAN DATABASE db_booking_lapangan
- Struktur aplikasi menggunakan tabel jadwal(lapangan_id, tanggal, jam_mulai, jam_selesai, status)
  dan bookings(jadwal_id), sesuai dump database yang diberikan.
- Jika database db_booking_lapangan sudah ada dan berisi data, JANGAN import ulang dump lengkap.
  Jalankan hanya database/migrasi_jadwal_dikecualikan.sql lewat phpMyAdmin > pilih db_booking_lapangan > SQL.
- Jika membuat database baru dari dump lengkap yang sudah diperbaiki, gunakan file db_booking_lapangan_fixed.sql terpisah.
