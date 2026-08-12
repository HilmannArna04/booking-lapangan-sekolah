# Booking Lapangan Sekolah - W3

Stack:
- PHP Native
- MySQL/MariaDB
- HTML/CSS
- JavaScript vanilla
- XAMPP

## Instalasi

1. Letakkan folder `booking-lapangan-sekolah` di:
   `C:\xampp\htdocs\`

2. Jalankan Apache dan MySQL di XAMPP.

3. Buka phpMyAdmin dan import:
   `database/db_booking_lapangan.sql`

4. Pastikan `config/koneksi.php` sesuai dengan konfigurasi MySQL lokal.

5. Buka:
   `http://localhost/booking-lapangan-sekolah/setup_admin.php`

6. Login menggunakan:
   Email: `admin@booking.com`
   Password: `admin123`

7. Setelah admin berhasil dibuat, HAPUS `setup_admin.php`.

## Fitur W3

- Login
- Register
- Logout
- Session & role admin/user
- Manajemen profil
- Dashboard admin sederhana
- CRUD data lapangan
- CRUD data pengguna
- CRUD jadwal
- Filter/search
- Activity log admin

## Catatan

Fitur transaksi booking belum dibuat di W3. Booking dapat dikerjakan pada tahap berikutnya setelah data lapangan, jadwal, pengguna, dan autentikasi stabil.
