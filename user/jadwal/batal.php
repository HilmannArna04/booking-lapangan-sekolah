<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("/booking-lapangan-sekolah/user/jadwal/");
}

$user_id = (int) $_SESSION['user_id'];
$booking_id = (int) ($_POST['booking_id'] ?? 0);

if ($booking_id <= 0) {
    flash("jadwal", "Booking tidak valid.", "error");
    redirect("/booking-lapangan-sekolah/user/jadwal/");
}

mysqli_begin_transaction($conn);

try {
    // Kunci booking milik user ini dan pastikan status masih menunggu.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, jadwal_id, status
         FROM bookings
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$booking) {
        throw new RuntimeException("Booking tidak ditemukan atau bukan milik akun Anda.");
    }

    if ($booking['status'] !== 'menunggu') {
        throw new RuntimeException("Booking hanya dapat dibatalkan saat status masih Menunggu. Booking yang sudah dikonfirmasi tidak bisa dibatalkan.");
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE bookings
         SET status = 'dibatalkan'
         WHERE id = ? AND user_id = ? AND status = 'menunggu'"
    );
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);

    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
        throw new RuntimeException("Pembatalan gagal. Silakan muat ulang halaman dan coba lagi.");
    }
    mysqli_stmt_close($stmt);

    // Jadwal bisa dipakai kembali setelah booking Menunggu dibatalkan.
    $jadwal_id = (int) $booking['jadwal_id'];
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE jadwal
         SET status = 'tersedia'
         WHERE id = ? AND status = 'dibooking'
           AND NOT EXISTS (
               SELECT 1 FROM bookings
               WHERE jadwal_id = ?
                 AND status IN ('menunggu', 'dikonfirmasi')
           )"
    );
    mysqli_stmt_bind_param($stmt, "ii", $jadwal_id, $jadwal_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Status jadwal gagal diperbarui.");
    }
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    log_activity($conn, $user_id, "Batalkan Booking", "Membatalkan booking ID {$booking_id} yang masih berstatus menunggu.");
    flash("jadwal", "Booking berhasil dibatalkan.", "success");
} catch (Throwable $e) {
    mysqli_rollback($conn);
    flash("jadwal", $e->getMessage(), "error");
}

redirect("/booking-lapangan-sekolah/user/jadwal/");
?>
