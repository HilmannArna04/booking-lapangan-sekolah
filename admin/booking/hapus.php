<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_admin();

/*
|--------------------------------------------------------------------------
| Hanya izinkan penghapusan melalui POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("/booking-lapangan-sekolah/admin/booking/index.php");
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash("booking", "ID booking tidak valid.", "error");
    redirect("/booking-lapangan-sekolah/admin/booking/index.php");
}

/*
|--------------------------------------------------------------------------
| Ambil status booking dari database, bukan dari input browser
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, status
     FROM bookings
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$booking) {
    flash("booking", "Data booking tidak ditemukan.", "error");
    redirect("/booking-lapangan-sekolah/admin/booking/index.php");
}

/*
|--------------------------------------------------------------------------
| Hanya booking berstatus selesai yang boleh dihapus
|--------------------------------------------------------------------------
*/
if ($booking['status'] !== 'selesai') {
    flash(
        "booking",
        "Hanya booking berstatus selesai yang dapat dihapus.",
        "error"
    );
    redirect("/booking-lapangan-sekolah/admin/booking/index.php");
}

/*
|--------------------------------------------------------------------------
| Hapus hanya data booking. Jadwal dan data pengguna tidak ikut dihapus.
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM bookings
     WHERE id = ?
       AND status = 'selesai'"
);

mysqli_stmt_bind_param($stmt, "i", $id);
$success = mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($success && $affected === 1) {
    log_activity(
        $conn,
        $_SESSION['user_id'],
        "Hapus Booking Selesai",
        "Menghapus data booking ID {$id} yang berstatus selesai."
    );

    flash("booking", "Data booking selesai berhasil dihapus.", "success");
} else {
    flash("booking", "Data booking gagal dihapus. Silakan coba lagi.", "error");
}

redirect("/booking-lapangan-sekolah/admin/booking/index.php");

?>
