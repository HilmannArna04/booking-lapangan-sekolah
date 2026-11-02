<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_user();


// =====================================================
// HANYA BOLEH POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}


// =====================================================
// AMBIL DATA
// =====================================================

$user_id = (int) $_SESSION['user_id'];

$jadwal_id = (int) ($_POST['jadwal_id'] ?? 0);
$kelas = trim($_POST['kelas'] ?? '');
$keperluan = trim($_POST['keperluan'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');


// =====================================================
// VALIDASI
// =====================================================

if ($kelas === '' || $keperluan === '') {
    flash("booking", "Kelas/kelompok dan keperluan wajib diisi.", "error");
    redirect("/booking-lapangan-sekolah/user/booking/");
}

if (strlen($kelas) > 50 || strlen($keperluan) > 255 || strlen($catatan) > 2000) {
    flash("booking", "Data terlalu panjang. Periksa kembali isian formulir.", "error");
    redirect("/booking-lapangan-sekolah/user/booking/");
}

if ($jadwal_id <= 0) {

    flash(
        "booking",
        "Jadwal tidak valid.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}


// =====================================================
// CEK JADWAL
// =====================================================

$stmt = mysqli_prepare(
    $conn,

    "SELECT
        j.id,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        j.status,
        l.nama AS nama_lapangan

     FROM jadwal j

     INNER JOIN lapangan l
        ON j.lapangan_id = l.id

     WHERE j.id = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $jadwal_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$jadwal = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// =====================================================
// JADWAL TIDAK DITEMUKAN
// =====================================================

if (!$jadwal) {

    flash(
        "booking",
        "Jadwal tidak ditemukan.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}


// =====================================================
// BATASI MAKSIMAL 3 BOOKING AKTIF PER USER PER HARI
// Booking ditolak, dibatalkan, dan selesai tidak dihitung.
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings b
     INNER JOIN jadwal j ON j.id = b.jadwal_id
     WHERE b.user_id = ?
       AND j.tanggal = ?
       AND b.status IN ('menunggu', 'dikonfirmasi')"
);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $user_id,
    $booking_date
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data_booking_harian = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

$total_booking_harian = (int) ($data_booking_harian['total'] ?? 0);

if ($total_booking_harian >= 3) {
    flash(
        "booking",
        "Booking limit: maksimal 3 jadwal per hari. Anda sudah memiliki 3 booking aktif pada tanggal tersebut.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/user/booking/?tanggal=" .
        urlencode($booking_date)
    );
}

// =====================================================
// CEK STATUS JADWAL
// =====================================================

if ($jadwal['status'] !== 'tersedia') {

    flash(
        "booking",
        "Jadwal tersebut sudah tidak tersedia.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}


// =====================================================
// CEK APAKAH USER SUDAH BOOKING JADWAL INI
// =====================================================

$stmt = mysqli_prepare(
    $conn,

    "SELECT id

     FROM bookings

     WHERE user_id = ?

     AND jadwal_id = ?

     AND status IN (
        'menunggu',
        'dikonfirmasi'
     )

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $jadwal_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$sudah_booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if ($sudah_booking) {

    flash(
        "booking",
        "Anda sudah melakukan booking pada jadwal tersebut.",
        "error"
    );

    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}


// =====================================================
// GENERATE KODE BOOKING
// =====================================================

$kode_booking =
    "BK-" .
    date("YmdHis") .
    "-" .
    random_int(100, 999);


// =====================================================
// INSERT BOOKING
// =====================================================

mysqli_begin_transaction($conn);

try {


    /*
    |--------------------------------------------------------------------------
    | INSERT KE BOOKINGS
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,

        "INSERT INTO bookings
        (
            kode_booking,
            user_id,
            jadwal_id,
            status,
            kelas,
            keperluan,
            catatan
        )

        VALUES
        (
            ?,
            ?,
            ?,
            'menunggu',
            ?,
            ?,
            ?
        )"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "siisss",
        $kode_booking,
        $user_id,
        $jadwal_id,
        $kelas,
        $keperluan,
        $catatan
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS JADWAL
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,

        "UPDATE jadwal

         SET status = 'dibooking'

         WHERE id = ?

         AND status = 'tersedia'"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $jadwal_id
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PASTIKAN JADWAL BERHASIL DIUPDATE
    |--------------------------------------------------------------------------
    */

    if (mysqli_stmt_affected_rows($stmt) !== 1) {

        throw new Exception(
            "Jadwal sudah diambil oleh pengguna lain."
        );
    }


    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | SIMPAN TRANSAKSI
    |--------------------------------------------------------------------------
    */

    mysqli_commit($conn);


    /*
    |--------------------------------------------------------------------------
    | LOG AKTIVITAS
    |--------------------------------------------------------------------------
    */

    log_activity(
        $conn,
        $user_id,
        "Booking",
        "Melakukan booking {$kode_booking} untuk {$jadwal['nama_lapangan']} pada {$jadwal['tanggal']} {$jadwal['jam_mulai']}."
    );


    /*
    |--------------------------------------------------------------------------
    | PESAN SUKSES
    |--------------------------------------------------------------------------
    */

    flash(
        "booking",
        "Booking berhasil dibuat. Kode booking: {$kode_booking}",
        "success"
    );


    redirect(
        "/booking-lapangan-sekolah/user/jadwal/"
    );
} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK JIKA ERROR
    |--------------------------------------------------------------------------
    */

    mysqli_rollback($conn);


    flash(
        "booking",
        "Booking gagal diproses. Jadwal mungkin sudah dipesan; muat ulang dan coba lagi.",
        "error"
    );


    redirect(
        "/booking-lapangan-sekolah/user/booking/"
    );
}
