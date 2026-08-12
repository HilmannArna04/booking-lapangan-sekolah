<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Tambah Lapangan";
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
            "INSERT INTO lapangan (nama, jenis, lokasi, deskripsi, status) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sssss", $nama, $jenis, $lokasi, $deskripsi, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        log_activity($conn, $_SESSION['user_id'], "Tambah Lapangan", "Menambahkan $nama.");
        flash("lapangan", "Data lapangan berhasil ditambahkan.");
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
                <h2>Tambah Lapangan</h2>
                <p>Tambahkan data lapangan baru.</p>
            </div>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lapangan</label>
                        <input class="form-control" name="nama" required value="<?= e(old('nama')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Jenis Lapangan</label>
                        <select class="form-control" name="jenis" required>
                            <option value="">Pilih jenis</option>
                            <option value="Basket">Basket</option>
                            <option value="Futsal">Futsal</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Lokasi</label>
                        <input class="form-control" name="lokasi" required value="<?= e(old('lokasi')) ?>">
                    </div>
                    <div class="form-group full">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi"><?= e(old('deskripsi')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Simpan</button>
                    <a class="btn btn-secondary" href="index.php">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
