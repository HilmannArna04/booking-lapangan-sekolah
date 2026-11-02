<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_admin();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash("jadwal", "ID jadwal tidak valid.");
    redirect("index.php");
}

mysqli_begin_transaction($conn);

try {
    // Kunci jadwal agar tidak berubah saat sedang diperiksa/dihapus.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, lapangan_id, tanggal, jam_mulai, jam_selesai
         FROM jadwal
         WHERE id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jadwal = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$jadwal) {
        throw new RuntimeException("Jadwal tidak ditemukan atau sudah dihapus.");
    }

    // Jadwal yang memiliki booking/riwayat booking tidak boleh dihapus.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM bookings
         WHERE jadwal_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $total_booking = (int) ($data["total"] ?? 0);

    if ($total_booking > 0) {
        throw new RuntimeException(
            "Jadwal tidak dapat dihapus karena memiliki {$total_booking} data booking."
        );
    }

    // Simpan pengecualian sebelum menghapus slot agar generator otomatis
    // tidak membuat ulang kombinasi lapangan, tanggal, dan jam ini.
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO jadwal_dikecualikan
            (lapangan_id, tanggal, jam_mulai, jam_selesai, dihapus_oleh)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            dihapus_oleh = VALUES(dihapus_oleh),
            dihapus_pada = CURRENT_TIMESTAMP"
    );

    $lapangan_id = (int) $jadwal["lapangan_id"];
    $tanggal = $jadwal["tanggal"];
    $jam_mulai = $jadwal["jam_mulai"];
    $jam_selesai = $jadwal["jam_selesai"];
    $admin_id = (int) ($_SESSION["user_id"] ?? 0);

    mysqli_stmt_bind_param(
        $stmt,
        "isssi",
        $lapangan_id,
        $tanggal,
        $jam_mulai,
        $jam_selesai,
        $admin_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException("Gagal menyimpan pengecualian jadwal.");
    }
    mysqli_stmt_close($stmt);

    // Hapus jadwal dari daftar admin karena tidak memiliki booking.
    $stmt = mysqli_prepare($conn, "DELETE FROM jadwal WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
        mysqli_stmt_close($stmt);
        throw new RuntimeException("Jadwal gagal dihapus.");
    }
    mysqli_stmt_close($stmt);

    log_activity(
        $conn,
        $admin_id,
        "Hapus Jadwal",
        "Menghapus jadwal ID {$id} ({$tanggal}, {$jam_mulai}-{$jam_selesai}, lapangan ID {$lapangan_id})."
    );

    mysqli_commit($conn);
    flash("jadwal", "Jadwal berhasil dihapus dan tidak akan dibuat ulang otomatis.");

} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log("Gagal menghapus jadwal: " . $e->getMessage());

    flash("jadwal", $e->getMessage(), "error");
}

redirect("index.php");
