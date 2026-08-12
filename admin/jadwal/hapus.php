<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "DELETE FROM jadwal WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

log_activity($conn, $_SESSION['user_id'], "Hapus Jadwal", "Menghapus jadwal ID $id.");
flash("jadwal", "Jadwal berhasil dihapus.");
redirect("index.php");
