<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/app.php';
require_admin();
$page_title = 'Laporan & Statistik';
$active_menu = 'laporan';
function report_e($v)
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
$today = date('Y-m-d');
$from = $_GET['dari'] ?? date('Y-m-01');
$to = $_GET['sampai'] ?? $today;
$status = $_GET['status'] ?? '';
$court_id = (int)($_GET['lapangan_id'] ?? 0);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = $today;
if ($from > $to) [$from, $to] = [$to, $from];
$statuses = ['menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'];
if (!in_array($status, $statuses, true)) $status = '';
$courts = [];
$cq = mysqli_query($conn, 'SELECT id, nama FROM lapangan ORDER BY nama');
if ($cq) while ($c = mysqli_fetch_assoc($cq)) $courts[] = $c;
$conditions = ['j.tanggal BETWEEN ? AND ?'];
$types = 'ss';
$params = [$from, $to];
if ($status !== '') {
    $conditions[] = 'b.status = ?';
    $types .= 's';
    $params[] = $status;
}
if ($court_id > 0) {
    $conditions[] = 'j.lapangan_id = ?';
    $types .= 'i';
    $params[] = $court_id;
}
$where = implode(' AND ', $conditions);
$sql = "SELECT b.kode_booking,b.status,u.name AS nama_pemesan,j.tanggal,j.jam_mulai,j.jam_selesai,l.nama AS nama_lapangan FROM bookings b JOIN users u ON u.id=b.user_id JOIN jadwal j ON j.id=b.jadwal_id JOIN lapangan l ON l.id=j.lapangan_id WHERE $where ORDER BY j.tanggal DESC,j.jam_mulai DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
$bookings = [];
while ($row = mysqli_fetch_assoc($r)) $bookings[] = $row;
mysqli_stmt_close($stmt);
// Ringkasan menampilkan seluruh status walau tabel difilter berdasarkan status.
$sum_conditions = ['j.tanggal BETWEEN ? AND ?'];
$sum_types = 'ss';
$sum_params = [$from, $to];
if ($court_id > 0) {
    $sum_conditions[] = 'j.lapangan_id = ?';
    $sum_types .= 'i';
    $sum_params[] = $court_id;
}
$sum_where = implode(' AND ', $sum_conditions);
$sum_stmt = mysqli_prepare($conn, "SELECT COUNT(*) total, SUM(b.status='selesai') selesai, SUM(b.status='menunggu') menunggu, SUM(b.status='dikonfirmasi') dikonfirmasi, SUM(b.status='dibatalkan') dibatalkan FROM bookings b JOIN jadwal j ON j.id=b.jadwal_id WHERE $sum_where");
mysqli_stmt_bind_param($sum_stmt, $sum_types, ...$sum_params);
mysqli_stmt_execute($sum_stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($sum_stmt)) ?: [];
mysqli_stmt_close($sum_stmt);
foreach (['total', 'selesai', 'menunggu', 'dikonfirmasi', 'dibatalkan'] as $k) $summary[$k] = (int)($summary[$k] ?? 0);
$stat_stmt = mysqli_prepare($conn, "SELECT l.nama nama_lapangan, COUNT(*) total FROM bookings b JOIN jadwal j ON j.id=b.jadwal_id JOIN lapangan l ON l.id=j.lapangan_id WHERE j.tanggal BETWEEN ? AND ? AND b.status='selesai' GROUP BY l.id,l.nama ORDER BY total DESC,l.nama");
mysqli_stmt_bind_param($stat_stmt, 'ss', $from, $to);
mysqli_stmt_execute($stat_stmt);
$sr = mysqli_stmt_get_result($stat_stmt);
$stats = [];
$max_stat = 1;
while ($row = mysqli_fetch_assoc($sr)) {
    $stats[] = $row;
    $max_stat = max($max_stat, (int)$row['total']);
}
mysqli_stmt_close($stat_stmt);
require_once __DIR__ . "/../../includes/header.php";
?>
<link rel="stylesheet" href="/booking-lapangan-sekolah/assets/css/laporan.css">
<div class="main report-main">
    <?php require_once __DIR__ . "/../../includes/sidebar.php"; ?>
    <section class="report-content">
        <header class="report-topbar">
            <div class="report-breadcrumb">Dashboard <span>/</span> Laporan</div>
            <div class="report-admin"><span class="report-avatar">A</span><span><?= report_e($_SESSION['name'] ?? 'Admin') ?><small>Administrator</small></span></div>
        </header>
        <main class="report-page">
            <div class="report-heading">
                <div>
                    <p class="report-eyebrow">ANALISIS SISTEM</p>
                    <h1>Laporan Booking</h1>
                    <p class="report-subtitle">Pantau pemesanan dan penggunaan lapangan sekolah berdasarkan periode.</p>
                </div><button class="report-btn report-primary no-print" type="button" onclick="window.print()">▤ Cetak / Simpan PDF</button>
            </div>
            <form class="report-filter no-print" method="GET">
                <label>Dari tanggal<input type="date" name="dari" value="<?= report_e($from) ?>" required></label>
                <label>Sampai tanggal<input type="date" name="sampai" value="<?= report_e($to) ?>" required></label>
                <label>Lapangan<select name="lapangan_id">
                        <option value="0">Semua lapangan</option><?php foreach ($courts as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $court_id === (int)$c['id'] ? 'selected' : '' ?>><?= report_e($c['nama']) ?></option><?php endforeach; ?>
                    </select></label>
                <label>Status booking<select name="status">
                        <option value="">Semua status</option><?php foreach ($statuses as $s): ?><option value="<?= report_e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= report_e(ucfirst($s)) ?></option><?php endforeach; ?>
                    </select></label>
                <div class="report-actions"><button class="report-btn report-primary" type="submit">Tampilkan</button><a class="report-btn report-light" href="index.php">Reset</a></div>
            </form>
            <div class="report-print-title">
                <h2>Sistem Booking Lapangan Sekolah</h2>
                <p>Laporan periode <?= report_e(date('d/m/Y', strtotime($from))) ?> – <?= report_e(date('d/m/Y', strtotime($to))) ?></p>
                <p>Dicetak <?= report_e(date('d/m/Y H:i')) ?></p>
            </div>
            <section class="report-stats">
                <article class="stat-card"><span class="stat-icon blue">▤</span>
                    <div><small>Total booking</small><strong><?= $summary['total'] ?></strong>
                        <p>Dalam periode terpilih</p>
                    </div>
                </article>
                <article class="stat-card"><span class="stat-icon green">✓</span>
                    <div><small>Booking selesai</small><strong><?= $summary['selesai'] ?></strong>
                        <p>Penggunaan terlaksana</p>
                    </div>
                </article>
                <article class="stat-card"><span class="stat-icon amber">◷</span>
                    <div><small>Menunggu</small><strong><?= $summary['menunggu'] ?></strong>
                        <p>Perlu ditindaklanjuti</p>
                    </div>
                </article>
                <article class="stat-card"><span class="stat-icon red">↶</span>
                    <div><small>Dibatalkan</small><strong><?= $summary['dibatalkan'] ?></strong>
                        <p>Booking dibatalkan</p>
                    </div>
                </article>
            </section>
            <section class="report-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Statistik Penggunaan Lapangan</h2>
                        <p>Jumlah booking berstatus selesai per lapangan.</p>
                    </div><span class="period"><?= report_e(date('d/m/Y', strtotime($from))) ?> – <?= report_e(date('d/m/Y', strtotime($to))) ?></span>
                </div>
                <?php if ($stats): ?><div class="bar-list"><?php foreach ($stats as $s): ?><div class="bar-item">
                                <div><span><?= report_e($s['nama_lapangan']) ?></span><strong><?= (int)$s['total'] ?> booking</strong></div>
                                <div class="bar-track"><span style="width:<?= round((int)$s['total'] / $max_stat * 100, 2) ?>%"></span></div>
                            </div><?php endforeach; ?></div><?php else: ?><p class="empty-small">Belum ada booking selesai pada periode ini.</p><?php endif; ?>
                <p class="report-note">Penggunaan aktual dihitung dari booking berstatus <strong>selesai</strong>.</p>
            </section>
            <section class="report-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Detail Booking</h2>
                        <p><?= count($bookings) ?> data sesuai filter yang dipilih.</p>
                    </div><span class="period"><?= count($bookings) ?> data</span>
                </div>
                <div class="table-scroll">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Kode Booking</th>
                                <th>Nama Pemesan</th>
                                <th>Lapangan</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings): foreach ($bookings as $i => $b): ?><tr>
                                        <td><?= $i + 1 ?></td>
                                        <td class="code"><?= report_e($b['kode_booking']) ?></td>
                                        <td><?= report_e($b['nama_pemesan']) ?></td>
                                        <td><?= report_e($b['nama_lapangan']) ?></td>
                                        <td><?= report_e(date('d/m/Y', strtotime($b['tanggal']))) ?></td>
                                        <td><?= report_e(substr($b['jam_mulai'], 0, 5)) ?>–<?= report_e(substr($b['jam_selesai'], 0, 5)) ?></td>
                                        <td><span class="status status-<?= report_e($b['status']) ?>"><?= report_e(ucfirst($b['status'])) ?></span></td>
                                    </tr><?php endforeach;
                                    else: ?><tr>
                                    <td colspan="7" class="empty-row">Tidak ada data booking untuk filter ini.</td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer"><span>Menampilkan <?= count($bookings) ? '1–' . count($bookings) : '0' ?> dari <?= count($bookings) ?> data</span><span>Laporan internal sekolah</span></div>
            </section>
            <footer class="report-footer">Sistem Booking Lapangan Sekolah <span>•</span> Data diambil langsung dari database.</footer>
        </main>
    </section>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>