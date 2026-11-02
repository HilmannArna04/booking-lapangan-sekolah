<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_admin();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? "";
$keterangan_kerusakan = trim($_POST['keterangan_kerusakan'] ?? "");


$allowed_status = [
    'dikonfirmasi',
    'ditolak',
    'selesai'
];


if (
    $id <= 0 ||
    !in_array($status, $allowed_status, true)
) {

    flash(
        "booking",
        "Data status tidak valid.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL BOOKING
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, jadwal_id, status
     FROM bookings
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$booking) {

    flash(
        "booking",
        "Booking tidak ditemukan.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI PERUBAHAN STATUS
|--------------------------------------------------------------------------
*/

$current_status = $booking['status'];


if (
    $status === 'dikonfirmasi' &&
    $current_status !== 'menunggu'
) {

    flash(
        "booking",
        "Booking tidak dapat dikonfirmasi.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


if (
    $status === 'ditolak' &&
    $current_status !== 'menunggu'
) {

    flash(
        "booking",
        "Booking tidak dapat ditolak.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


if (
    $status === 'selesai' &&
    $current_status !== 'dikonfirmasi'
) {

    flash(
        "booking",
        "Booking belum dapat diselesaikan.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "UPDATE bookings
     SET status = ?, keterangan_kerusakan = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $status,
    $keterangan_kerusakan,
    $id
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------
    | Jika ditolak, jadwal dapat digunakan kembali.
    |--------------------------------------------------------------
    */

    if ($status === 'ditolak') {

        $stmt_jadwal = mysqli_prepare(
            $conn,
            "UPDATE jadwal
             SET status = 'tersedia'
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt_jadwal,
            "i",
            $booking['jadwal_id']
        );

        mysqli_stmt_execute($stmt_jadwal);

        mysqli_stmt_close($stmt_jadwal);
    }


    /*
    |--------------------------------------------------------------
    | Jika dikonfirmasi, jadwal menjadi tidak tersedia.
    |--------------------------------------------------------------
    */

    if ($status === 'dikonfirmasi') {

        $stmt_jadwal = mysqli_prepare(
            $conn,
            "UPDATE jadwal
             SET status = 'dibooking'
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt_jadwal,
            "i",
            $booking['jadwal_id']
        );

        mysqli_stmt_execute($stmt_jadwal);

        mysqli_stmt_close($stmt_jadwal);
    }


    flash(
        "booking",
        "Status booking berhasil diperbarui.",
        "success"
    );
} else {

    mysqli_stmt_close($stmt);

    flash(
        "booking",
        "Gagal memperbarui status booking.",
        "error"
    );
}


redirect(
    "/booking-lapangan-sekolah/admin/booking/index.php"
);
