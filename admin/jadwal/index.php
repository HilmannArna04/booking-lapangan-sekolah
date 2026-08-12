<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Data Jadwal";
$active_menu = "jadwal";

$tanggal = $_GET['tanggal'] ?? "";
$lapangan_id = (int)($_GET['lapangan_id'] ?? 0);

$sql = "SELECT j.*, l.nama AS nama_lapangan
        FROM jadwal j
        JOIN lapangan l ON l.id = j.lapangan_id
        WHERE 1=1";
$params = [];
$types = "";

if ($tanggal !== "") {
    $sql .= " AND j.tanggal = ?";
    $params[] = $tanggal;
    $types .= "s";
}
if ($lapangan_id > 0) {
    $sql .= " AND j.lapangan_id = ?";
    $params[] = $lapangan_id;
    $types .= "i";
}
$sql .= " ORDER BY j.tanggal DESC, j.jam_mulai ASC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$courts = mysqli_query($conn, "SELECT id,nama FROM lapangan WHERE status='aktif' ORDER BY nama");

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>

    <main class="content">
        <?php show_flash(); ?>

        <div class="page-heading">
            <div><h2>Data Jadwal</h2><p>Kelola slot waktu operasional lapangan.</p></div>
            <a class="btn btn-primary" href="tambah.php">+ Tambah Jadwal</a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <form class="filters" method="GET">
                    <input type="date" name="tanggal" value="<?= e($tanggal) ?>">
                    <select name="lapangan_id">
                        <option value="0">Semua Lapangan</option>
                        <?php while($c=mysqli_fetch_assoc($courts)): ?>
                            <option value="<?= $c['id'] ?>" <?= $lapangan_id===$c['id']?'selected':'' ?>><?= e($c['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button class="btn btn-secondary">Filter</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>No</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= e($row['nama_lapangan']) ?></strong></td>
                            <td><?= e(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
                            <td><?= e(substr($row['jam_mulai'],0,5)) ?> - <?= e(substr($row['jam_selesai'],0,5)) ?></td>
                            <td><span class="badge <?= $row['status']==='tersedia'?'status-active':'status-inactive' ?>"><?= e(ucfirst(str_replace('_',' ',$row['status']))) ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-secondary" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                                    <a class="btn btn-danger" data-confirm="Hapus jadwal ini?" href="hapus.php?id=<?= $row['id'] ?>">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result)===0): ?>
                        <tr><td colspan="6" class="empty">Belum ada jadwal.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php mysqli_stmt_close($stmt); require_once __DIR__ . "/../../includes/footer.php"; ?>
