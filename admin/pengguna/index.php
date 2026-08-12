<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Data Pengguna";
$active_menu = "pengguna";

$search = trim($_GET['search'] ?? "");
$status = $_GET['status'] ?? "";

$sql = "SELECT id, name, email, phone, role, status, created_at FROM users WHERE role = 'user'";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

if ($status !== "") {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>

    <main class="content">
        <?php show_flash(); ?>

        <div class="page-heading">
            <div>
                <h2>Data Pengguna</h2>
                <p>Kelola akun pengguna sistem.</p>
            </div>
            <a class="btn btn-primary" href="tambah.php">+ Tambah Pengguna</a>
        </div>

        <div class="table-card">
            <div class="table-header">
                <form class="filters" method="GET">
                    <input type="text" name="search" placeholder="Cari nama/email..." value="<?= e($search) ?>">
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                    <button class="btn btn-secondary" type="submit">Filter</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= e($row['name']) ?></strong></td>
                                <td><?= e($row['email']) ?></td>
                                <td><?= e($row['phone']) ?></td>
                                <td><span class="badge <?= $row['status'] === 'aktif' ? 'status-active' : 'status-inactive' ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-secondary" href="edit.php?id=<?= e($row['id']) ?>">Edit</a>
                                        <a class="btn btn-danger" data-confirm="Ubah status pengguna ini?" href="toggle.php?id=<?= e($row['id']) ?>">Ubah Status</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="6" class="empty">Belum ada pengguna.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php mysqli_stmt_close($stmt);
require_once __DIR__ . "/../../includes/footer.php"; ?>