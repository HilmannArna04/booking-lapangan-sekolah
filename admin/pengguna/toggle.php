<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/app.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='aktif','nonaktif','aktif') WHERE id=? AND role='user'");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

log_activity($conn, $_SESSION['user_id'], "Ubah Status Pengguna", "Mengubah status pengguna ID $id.");
flash("pengguna", "Status pengguna berhasil diubah.");
redirect("index.php");
