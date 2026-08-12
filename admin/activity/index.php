<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Aktivitas Admin";
$active_menu = "activity";

$sql = "SELECT a.*, u.name FROM activity_logs a JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 50";
$result = mysqli_query($conn, $sql);

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>
    <main class="content">
        <div class="page-heading">
            <div><h2>Aktivitas Admin</h2><p>Riwayat aktivitas administrator.</p></div>
        </div>

        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>No</th><th>Admin</th><th>Aktivitas</th><th>Deskripsi</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['aktivitas']) ?></td>
                            <td><?= e($row['deskripsi']) ?></td>
                            <td><?= e($row['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result)===0): ?>
                        <tr><td colspan="5" class="empty">Belum ada aktivitas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
