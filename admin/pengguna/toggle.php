<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/app.php';
require_admin();

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/pengguna/index.php'));
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    flash("pengguna", "ID pengguna tidak valid.", "error");
    redirect(base_url('admin/pengguna/index.php'));
}

// Pastikan user ada dan bukan admin
$stmt = mysqli_prepare($conn, "SELECT id, status FROM users WHERE id = ? AND role = 'user' LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    flash("pengguna", "Pengguna tidak ditemukan.", "error");
    redirect(base_url('admin/pengguna/index.php'));
}

$stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='aktif','nonaktif','aktif') WHERE id = ? AND role = 'user'");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$status_baru = $user['status'] === 'aktif' ? 'nonaktif' : 'aktif';
log_activity($conn, $_SESSION['user_id'], "Ubah Status Pengguna", "Mengubah status pengguna ID {$id} menjadi {$status_baru}.");
flash("pengguna", "Status pengguna berhasil diubah.");
redirect(base_url('admin/pengguna/index.php'));
