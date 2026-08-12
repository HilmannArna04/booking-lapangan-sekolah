<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Tambah Jadwal";
$active_menu = "jadwal";
$error = "";

$courts = mysqli_query($conn, "SELECT id,nama FROM lapangan WHERE status='aktif' ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lapangan_id = (int)($_POST['lapangan_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? "";
    $jam_mulai = $_POST['jam_mulai'] ?? "";
    $jam_selesai = $_POST['jam_selesai'] ?? "";
    $status = $_POST['status'] ?? "tersedia";

    if ($lapangan_id <= 0 || $tanggal === "" || $jam_mulai === "" || $jam_selesai === "") {
        $error = "Semua data jadwal wajib diisi.";
    } elseif ($jam_mulai >= $jam_selesai) {
        $error = "Jam selesai harus lebih besar dari jam mulai.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM jadwal WHERE lapangan_id=? AND tanggal=? AND jam_mulai=? AND jam_selesai=?");
        mysqli_stmt_bind_param($stmt, "isss", $lapangan_id, $tanggal, $jam_mulai, $jam_selesai);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = "Jadwal yang sama sudah tersedia.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO jadwal (lapangan_id,tanggal,jam_mulai,jam_selesai,status) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "issss", $lapangan_id, $tanggal, $jam_mulai, $jam_selesai, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($conn, $_SESSION['user_id'], "Tambah Jadwal", "Menambahkan jadwal lapangan ID $lapangan_id.");
            flash("jadwal", "Jadwal berhasil ditambahkan.");
            redirect("index.php");
        }
    }
}

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>
    <main class="content">
        <div class="page-heading"><div><h2>Tambah Jadwal</h2><p>Tambahkan slot waktu lapangan.</p></div></div>
        <?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Lapangan</label>
                    <select class="form-control" name="lapangan_id" required>
                        <option value="">Pilih lapangan</option>
                        <?php while($c=mysqli_fetch_assoc($courts)): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input class="form-control" type="date" name="tanggal" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input class="form-control" type="time" name="jam_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input class="form-control" type="time" name="jam_selesai" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="tersedia">Tersedia</option>
                        <option value="tidak_tersedia">Tidak Tersedia</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Simpan</button>
                    <a class="btn btn-secondary" href="index.php">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
