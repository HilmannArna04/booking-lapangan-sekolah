<?php
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";
require_admin();

$page_title = "Dashboard";
$active_menu = "dashboard";

$lapangan_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM lapangan"))['total'];
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'user'"))['total'];
$jadwal_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM jadwal"))['total'];
$booking_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings"))['total'];

require_once __DIR__ . "/../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">
        <?php show_flash(); ?>

        <div class="page-heading">
            <div>
                <h2>Ringkasan Sistem</h2>
                <p>Informasi utama pengelolaan booking lapangan.</p>
            </div>
        </div>

        <section class="cards">
            <div class="card">
                <div class="stat-label">Total Lapangan</div>
                <div class="stat-value"><?= e($lapangan_count) ?></div>
            </div>
            <div class="card">
                <div class="stat-label">Total Pengguna</div>
                <div class="stat-value"><?= e($user_count) ?></div>
            </div>
            <div class="card">
                <div class="stat-label">Total Jadwal</div>
                <div class="stat-value"><?= e($jadwal_count) ?></div>
            </div>
            <div class="card">
                <div class="stat-label">Total Booking</div>
                <div class="stat-value"><?= e($booking_count) ?></div>
            </div>
        </section>

        <div class="table-card">
            <div class="table-header">
                <h3>Menu Pengelolaan</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Fungsi</th>
                            <th>Akses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Data Lapangan</td>
                            <td>Menambah, mengubah, dan mengelola status lapangan.</td>
                            <td><a class="btn btn-secondary" href="lapangan/index.php">Buka</a></td>
                        </tr>
                        <tr>
                            <td>Data Jadwal</td>
                            <td>Mengelola jadwal operasional setiap lapangan.</td>
                            <td><a class="btn btn-secondary" href="jadwal/index.php">Buka</a></td>
                        </tr>
                        <tr>
                            <td>Data Pengguna</td>
                            <td>Mengelola akun pengguna sistem.</td>
                            <td><a class="btn btn-secondary" href="pengguna/index.php">Buka</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
