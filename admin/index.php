<?php

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";

require_admin();

$page_title  = "Dashboard";
$active_menu = "dashboard";

/*
|--------------------------------------------------------------------------
| STATISTIK RINGKASAN
|--------------------------------------------------------------------------
*/

$total_booking = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS n FROM bookings")
)['n'];

$total_pengguna = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS n FROM users WHERE role='user'")
)['n'];

$booking_selesai = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS n FROM bookings WHERE status='selesai'")
)['n'];

$booking_menunggu = (int) mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS n FROM bookings WHERE status='menunggu'")
)['n'];

/*
|--------------------------------------------------------------------------
| TREN BOOKING – jumlah booking per hari 30 hari terakhir
|--------------------------------------------------------------------------
*/

$tren_q = mysqli_query($conn,
    "SELECT DATE(created_at) AS tgl, COUNT(*) AS n
     FROM bookings
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
     GROUP BY DATE(created_at)
     ORDER BY tgl ASC"
);

$tren_map = [];
while ($r = mysqli_fetch_assoc($tren_q)) {
    $tren_map[$r['tgl']] = (int)$r['n'];
}

$tren_labels = [];
$tren_values = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $tren_labels[] = date('j M', strtotime($d));
    $tren_values[] = $tren_map[$d] ?? 0;
}

$tren_max = max(max($tren_values), 1);

/*
|--------------------------------------------------------------------------
| BOOKING TERBARU (5 terakhir)
|--------------------------------------------------------------------------
*/

$booking_q = mysqli_query($conn,
    "SELECT b.kode_booking, b.status AS booking_status,
            b.created_at AS tanggal_booking,
            u.name AS nama_pemesan,
            j.tanggal, j.jam_mulai, j.jam_selesai,
            l.nama AS nama_lapangan
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN jadwal j ON j.id = b.jadwal_id
     JOIN lapangan l ON l.id = j.lapangan_id
     ORDER BY b.created_at DESC
     LIMIT 5"
);

if (!$booking_q) die("Query booking gagal: " . mysqli_error($conn));

function dash_e($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function dash_status_class($s) {
    return match ($s) {
        'selesai'     => 'ds-badge-green',
        'dikonfirmasi'=> 'ds-badge-blue',
        'dibatalkan'  => 'ds-badge-red',
        default       => 'ds-badge-amber',   // menunggu
    };
}

require_once __DIR__ . "/../includes/header.php";
?>
<link rel="stylesheet" href="/../assets/css/dashboard.css">

<div class="main ds-main">

    <?php require __DIR__ . "/../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../includes/sidebar.php"; ?>
    
    <main class="content ds-content">

        <?php show_flash(); ?>

        <!-- ── HEADING ─────────────────────────────────────────── -->
        <div class="ds-heading-row">
            <div>
                <p class="ds-eyebrow">ADMIN PANEL</p>
                <h1>Dashboard</h1>
                <p class="ds-subtitle">Ringkasan dan pemantauan sistem booking lapangan.</p>
            </div>
        </div>

        <!-- ── STAT CARDS ──────────────────────────────────────── -->
        <section class="ds-stat-cards">

            <article class="ds-stat-card">
                <div class="ds-stat-icon ds-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <div>
                    <small>Total Booking</small>
                    <strong><?= $total_booking ?></strong>
                    <p>Semua transaksi tercatat</p>
                </div>
            </article>

            <article class="ds-stat-card">
                <div class="ds-stat-icon ds-icon-indigo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <small>Pengguna</small>
                    <strong><?= $total_pengguna ?></strong>
                    <p>Akun aktif terdaftar</p>
                </div>
            </article>

            <article class="ds-stat-card">
                <div class="ds-stat-icon ds-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <div>
                    <small>Selesai</small>
                    <strong><?= $booking_selesai ?></strong>
                    <p>Booking terlaksana</p>
                </div>
            </article>

            <article class="ds-stat-card">
                <div class="ds-stat-icon ds-icon-amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <small>Menunggu</small>
                    <strong><?= $booking_menunggu ?></strong>
                    <p>Perlu dikonfirmasi</p>
                </div>
            </article>

        </section>

        <!-- ── TREN BOOKING (bar chart SVG) ────────────────────── -->
        <section class="ds-panel">
            <div class="ds-panel-head">
                <div>
                    <h2>Tren Booking</h2>
                    <p>30 hari terakhir — jumlah booking per hari.</p>
                </div>
                <span class="ds-legend"><span class="ds-legend-dot"></span>Total Booking</span>
            </div>
            <div class="ds-chart-wrap">
                <?php
                // Hanya tampilkan 10 label sumbu X (tiap 3 hari agar tidak penuh)
                $point_count = count($tren_values);
                $w = 900; $h = 220; $pad_l = 42; $pad_b = 32; $pad_t = 16; $pad_r = 20;
                $cw = $w - $pad_l - $pad_r;
                $ch = $h - $pad_t - $pad_b;

                // Langkah Y yang rapi
                $y_step = 1;
                foreach ([1,2,5,10,20,25,50,100,200,500] as $c) { $y_step=$c; if(ceil($tren_max/$c)<=5) break; }
                $y_ticks = max(1,(int)ceil($tren_max/$y_step));
                $y_max   = $y_step * $y_ticks;

                // Koordinat titik
                $pts = [];
                for ($i = 0; $i < $point_count; $i++) {
                    $x = $pad_l + ($i / max($point_count-1,1)) * $cw;
                    $y = $pad_t + $ch - ($tren_values[$i] / $y_max) * $ch;
                    $pts[] = [$x, $y];
                }

                // Smooth polyline (cardinal spline simpel)
                $poly = implode(' ', array_map(fn($p)=>round($p[0],1).','.round($p[1],1), $pts));

                // Area fill polygon
                $area_pts = round($pts[0][0],1).','.($pad_t+$ch);
                foreach ($pts as $p) $area_pts .= ' '.round($p[0],1).','.round($p[1],1);
                $area_pts .= ' '.round($pts[$point_count-1][0],1).','.($pad_t+$ch);
                ?>
                <svg class="ds-tren-svg" viewBox="0 0 <?= $w ?> <?= $h ?>" preserveAspectRatio="none" aria-label="Grafik tren booking 30 hari">
                    <defs>
                        <linearGradient id="tren-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#155EEF" stop-opacity="0.18"/>
                            <stop offset="100%" stop-color="#155EEF" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    <!-- Grid lines & Y labels -->
                    <?php for ($t = 0; $t <= $y_ticks; $t++):
                        $gy = $pad_t + $ch - ($t / $y_ticks) * $ch;
                    ?>
                        <line x1="<?= $pad_l ?>" y1="<?= round($gy,1) ?>" x2="<?= $w-$pad_r ?>" y2="<?= round($gy,1) ?>" stroke="<?= $t===0 ? '#DCE3EE' : '#EDF0F5' ?>" stroke-width="1"/>
                        <text x="<?= $pad_l - 7 ?>" y="<?= round($gy,1)+4 ?>" text-anchor="end" font-size="10" fill="#667085"><?= $t*$y_step ?></text>
                    <?php endfor; ?>

                    <!-- Area fill -->
                    <polygon points="<?= $area_pts ?>" fill="url(#tren-grad)"/>

                    <!-- Line -->
                    <polyline points="<?= $poly ?>" fill="none" stroke="#155EEF" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round"/>

                    <!-- Dots -->
                    <?php foreach ($pts as $i => $p):
                        // Tampilkan titik hanya setiap ~4 hari agar tidak penuh
                        if ($point_count <= 10 || $i % 4 === 0 || $i === $point_count-1):
                    ?>
                        <circle cx="<?= round($p[0],1) ?>" cy="<?= round($p[1],1) ?>" r="4" fill="#155EEF" stroke="#fff" stroke-width="2"/>
                    <?php endif; endforeach; ?>

                    <!-- X labels (tiap ~5 hari) -->
                    <?php foreach ($pts as $i => $p):
                        $show = ($i === 0 || $i === $point_count-1 || $i % 5 === 0);
                        if ($show):
                    ?>
                        <text x="<?= round($p[0],1) ?>" y="<?= $h - 6 ?>" text-anchor="middle" font-size="10" fill="#667085"><?= htmlspecialchars($tren_labels[$i]) ?></text>
                    <?php endif; endforeach; ?>
                </svg>
            </div>
        </section>

        <!-- ── BOOKING TERBARU (card horizontal) ───────────────── -->
        <section class="ds-panel">
            <div class="ds-panel-head">
                <h2>Data Booking Terbaru</h2>
                <a class="ds-link-all" href="/admin/booking/index.php">Lihat Semua →</a>
            </div>
            <div class="ds-booking-cards">
                <?php if (mysqli_num_rows($booking_q) > 0):
                    while ($b = mysqli_fetch_assoc($booking_q)): ?>
                    <div class="ds-bcard">
                        <div class="ds-bcard-img">
                            <span class="ds-bcard-symbol">▦</span>
                        </div>
                        <div class="ds-bcard-body">
                            <div class="ds-bcard-top">
                                <span class="ds-bcard-code"><?= dash_e($b['kode_booking']) ?></span>
                                <span class="ds-badge <?= dash_status_class($b['booking_status']) ?>"><?= dash_e(ucfirst($b['booking_status'])) ?></span>
                            </div>
                            <p class="ds-bcard-pemesan"><?= dash_e($b['nama_pemesan']) ?></p>
                            <p class="ds-bcard-meta">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2" y="2" width="12" height="12" rx="2"/><path d="M2 6h12M5 1v2M11 1v2"/></svg>
                                <?= dash_e(date('d M Y', strtotime($b['tanggal']))) ?>
                                &middot;
                                <?= dash_e(substr($b['jam_mulai'],0,5)) ?> – <?= dash_e(substr($b['jam_selesai'],0,5)) ?>
                                &middot;
                                <?= dash_e($b['nama_lapangan']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endwhile;
                else: ?>
                    <p class="ds-empty">Belum ada data booking.</p>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>