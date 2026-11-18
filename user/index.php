<?php

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";

require_user();

$page_title = "Dashboard User";
$active_menu = "dashboard";

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'Pengguna';
$user_initial = strtoupper(substr(trim($user_name), 0, 1)) ?: 'U';

/*
|--------------------------------------------------------------------------
| JUMLAH BOOKING AKTIF
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE user_id = ?
     AND status IN ('menunggu', 'dikonfirmasi')"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_aktif = mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| JUMLAH JADWAL / BOOKING USER
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_jadwal = mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| BOOKING SELESAI
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE user_id = ?
     AND status = 'selesai'"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_selesai = mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| BOOKING TERBARU
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        b.kode_booking,
        b.status,
        b.created_at,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        l.nama AS nama_lapangan
     FROM bookings b
     INNER JOIN jadwal j ON b.jadwal_id = j.id
     INNER JOIN lapangan l ON j.lapangan_id = l.id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC
     LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$booking_result = mysqli_stmt_get_result($stmt);

/*
|--------------------------------------------------------------------------
| JADWAL HARI INI
|--------------------------------------------------------------------------
*/
$today = date("Y-m-d");

$stmt_today = mysqli_prepare(
    $conn,
    "SELECT
        b.kode_booking,
        b.status,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        l.nama AS nama_lapangan
     FROM bookings b
     INNER JOIN jadwal j ON b.jadwal_id = j.id
     INNER JOIN lapangan l ON j.lapangan_id = l.id
     WHERE b.user_id = ?
     AND j.tanggal = ?
     ORDER BY j.jam_mulai ASC
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt_today, "is", $user_id, $today);
mysqli_stmt_execute($stmt_today);
$today_result = mysqli_stmt_get_result($stmt_today);
$jadwal_hari_ini = mysqli_fetch_assoc($today_result);
mysqli_stmt_close($stmt_today);

require_once __DIR__ . "/../includes/header.php";
?>

<div class="main">

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <span>Dashboard</span>
            <span>/</span>
            <strong>User</strong>
        </div>

        <div class="topbar-user">
            <a href="/user/riwayat/index.php" class="topbar-notif" title="Riwayat Booking">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($total_aktif > 0): ?>
                    <span class="topbar-notif-dot"></span>
                <?php endif; ?>
            </a>

            <a href="../profile.php" class="topbar-profile">
                <span class="topbar-avatar"><?= e($user_initial) ?></span>
                <span class="topbar-profile-info">
                    <strong><?= e($user_name) ?></strong>
                    <small>Pengguna</small>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#98A2B3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </a>
        </div>
    </header>

    <main class="content">

        <?php show_flash(); ?>

        <!-- HEADER -->
        <div class="page-heading">
            <div>
                <h2>Dashboard</h2>
                <p>Selamat datang di halaman utama.</p>
            </div>
        </div>

        <!-- STATISTIK -->
        <section class="cards">

            <!-- BOOKING AKTIF -->
            <div class="card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <div class="stat-label">Booking Aktif</div>
                    <div class="stat-value"><?= e($total_aktif) ?></div>
                </div>
            </div>

            <!-- JADWAL SAYA -->
            <div class="card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <div class="stat-label">Jadwal Saya</div>
                    <div class="stat-value"><?= e($total_jadwal) ?></div>
                </div>
            </div>

            <!-- BOOKING SELESAI -->
            <div class="card">
                <div class="stat-icon is-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div>
                    <div class="stat-label">Booking Selesai</div>
                    <div class="stat-value"><?= e($total_selesai) ?></div>
                </div>
            </div>

            <!-- TOTAL BOOKING -->
            <div class="card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div>
                    <div class="stat-label">Total Booking</div>
                    <div class="stat-value"><?= e($total_jadwal) ?></div>
                </div>
            </div>

        </section>

        <!-- JADWAL HARI INI -->
        <div class="table-card">
            <div class="table-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Jadwal Hari Ini
                </h3>
                <a href="riwayat/index.php" class="table-header-link">Lihat Semua &rarr;</a>
            </div>

            <div class="table-wrap">
                <?php if ($jadwal_hari_ini): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Lapangan</th>
                                <th>Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?= e($jadwal_hari_ini['kode_booking']) ?></strong></td>
                                <td><?= e($jadwal_hari_ini['nama_lapangan']) ?></td>
                                <td>
                                    <?= e(substr($jadwal_hari_ini['jam_mulai'], 0, 5)) ?>
                                    -
                                    <?= e(substr($jadwal_hari_ini['jam_selesai'], 0, 5)) ?>
                                </td>
                                <td>
                                    <span class="badge"><?= e(ucfirst($jadwal_hari_ini['status'])) ?></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <p>Tidak ada jadwal booking hari ini</p>
                        <a class="btn btn-primary" href="/user/booking/index.php">
                            Booking Lapangan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- INFORMASI SISTEM -->
        <div class="table-card">
            <div class="table-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Informasi Sistem
                </h3>
            </div>

            <div class="info-list">
                <div class="info-item">
                    <strong>Selamat Datang</strong>
                    <p>Silakan melakukan booking lapangan melalui menu <strong>Booking Saya</strong>.</p>
                </div>

                <div class="info-item">
                    <strong>Informasi Booking</strong>
                    <p>Pastikan memilih tanggal, lapangan, dan jadwal yang sesuai sebelum melakukan booking.</p>
                </div>

                <div class="info-item">
                    <strong>Status Booking</strong>
                    <p>Pantau status booking Anda di menu <strong>Booking Saya</strong>.</p>
                </div>
            </div>
        </div>

        <!-- BOOKING TERBARU -->
        <div class="table-card">
            <div class="table-header">
                <h3>Booking Terbaru</h3>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Lapangan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Tanggal Pemesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($booking_result) > 0): ?>
                            <?php while ($booking = mysqli_fetch_assoc($booking_result)): ?>
                                <tr>
                                    <td><strong><?= e($booking['kode_booking']) ?></strong></td>
                                    <td><?= e($booking['nama_lapangan']) ?></td>
                                    <td><?= e(date("d-m-Y", strtotime($booking['tanggal']))) ?></td>
                                    <td>
                                        <?= e(substr($booking['jam_mulai'], 0, 5)) ?>
                                        -
                                        <?= e(substr($booking['jam_selesai'], 0, 5)) ?>
                                    </td>
                                    <td><span class="badge"><?= e(ucfirst($booking['status'])) ?></span></td>
                                    <td><?= e(date("d-m-Y H:i", strtotime($booking['created_at']))) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">Belum ada data booking.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</div>

<?php
mysqli_stmt_close($stmt);
require_once __DIR__ . "/../includes/footer.php";
?>