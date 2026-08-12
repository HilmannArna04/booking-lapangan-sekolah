<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/app.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM lapangan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    exit("Data lapangan tidak ditemukan.");
}

$page_title = "Edit Lapangan";
$active_menu = "lapangan";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? "");
    $jenis = $_POST['jenis'] ?? "";
    $lokasi = trim($_POST['lokasi'] ?? "");
    $deskripsi = trim($_POST['deskripsi'] ?? "");
    $status = $_POST['status'] ?? "aktif";

    if ($nama === "" || $lokasi === "" || !in_array($jenis, ['Basket', 'Futsal'], true)) {
        $error = "Nama, jenis, dan lokasi wajib diisi dengan benar.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE lapangan SET nama = ?, jenis = ?, lokasi = ?, deskripsi = ?, status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssssi", $nama, $jenis, $lokasi, $deskripsi, $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        log_activity($conn, $_SESSION['user_id'], "Edit Lapangan", "Mengubah $nama.");
        flash("lapangan", "Data lapangan berhasil diperbarui.");
        redirect("index.php");
    }
}

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>

    <main class="content">
        <div class="page-heading">
            <div>
                <h2>Edit Lapangan</h2>
                <p>Perbarui informasi lapangan.</p>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lapangan</label>
                        <input class="form-control" name="nama" required value="<?= e($data['nama']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Jenis Lapangan</label>
                        <select class="form-control" name="jenis" required>
                            <option value="Basket" <?= $data['jenis'] === 'Basket' ? 'selected' : '' ?>>Basket</option>
                            <option value="Futsal" <?= $data['jenis'] === 'Futsal' ? 'selected' : '' ?>>Futsal</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Lokasi</label>
                        <input class="form-control" name="lokasi" required value="<?= e($data['lokasi']) ?>">
                    </div>
                    <div class="form-group full">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi"><?= e($data['deskripsi']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="aktif" <?= $data['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $data['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    <a class="btn btn-secondary" href="index.php">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
