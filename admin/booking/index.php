<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_admin();

$page_title = "Data Booking";
$active_menu = "booking";


$search = trim($_GET['search'] ?? "");
$status = $_GET['status'] ?? "";
$tanggal = $_GET['tanggal'] ?? "";


$sql = "
    SELECT

        b.id,
        b.kode_booking,
        b.catatan,
        b.status AS booking_status,
        b.created_at AS tanggal_booking,

        u.name AS nama_user,
        u.email AS email_user,

        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,

        l.nama AS nama_lapangan,
        l.jenis AS jenis_lapangan

    FROM bookings b

    INNER JOIN users u
        ON b.user_id = u.id

    INNER JOIN jadwal j
        ON b.jadwal_id = j.id

    INNER JOIN lapangan l
        ON j.lapangan_id = l.id

    WHERE 1=1
";


$params = [];
$types = "";


if ($search !== "") {

    $sql .= "
        AND (
            b.kode_booking LIKE ?
            OR u.name LIKE ?
            OR l.nama LIKE ?
        )
    ";

    $like = "%$search%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;

    $types .= "sss";
}


if ($status !== "") {

    $sql .= " AND b.status = ?";

    $params[] = $status;

    $types .= "s";
}


if ($tanggal !== "") {

    $sql .= " AND j.tanggal = ?";

    $params[] = $tanggal;

    $types .= "s";
}


$sql .= "
    ORDER BY b.created_at DESC
";


$stmt = mysqli_prepare($conn, $sql);

if ($params) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);



$bookings = [];
while ($item = mysqli_fetch_assoc($result)) {
    $bookings[] = $item;
}
mysqli_stmt_close($stmt);

$counts = ['total' => count($bookings), 'menunggu' => 0, 'dikonfirmasi' => 0, 'ditolak' => 0, 'selesai' => 0, 'dibatalkan' => 0];
foreach ($bookings as $item) {
    if (isset($counts[$item['booking_status']])) $counts[$item['booking_status']]++;
}
require_once __DIR__ . "/../../includes/header.php";
?>

<?php require __DIR__ . "/../../includes/navbar.php"; ?>
<?php require __DIR__ . "/../../includes/sidebar.php"; ?>
<link rel="stylesheet" href="/booking-lapangan-sekolah/assets/css/admin-booking.css">

<main class="content booking-management-page admin-booking-v2">
    <?php show_flash(); ?>
    <div class="booking-page-heading">
        <a class="booking-back" href="../dashboard.php">← Kembali</a>
        <div>
            <h1>Data Booking</h1>
            <p>Kelola semua data pemesanan lapangan dari pengguna.</p>
        </div>
    </div>

    <section class="booking-filters-panel">
        <form method="GET" class="booking-filter-form">
            <label class="booking-search"><span>⌕</span><input name="search" value="<?= e($search) ?>" placeholder="Cari kode booking, nama pemesan, atau lapangan..."></label>
            <select name="status" aria-label="Filter status">
                <option value="">Semua Status</option>
                <?php foreach (['menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'] as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="booking-date"><span>Tanggal</span><input type="date" name="tanggal" value="<?= e($tanggal) ?>"></label>
            <button class="booking-btn booking-btn-primary" type="submit">Tampilkan</button>
        </form>
    </section>

    <section class="booking-stats">
        <div class="booking-stat"><span class="stat-icon">▣</span>
            <div><small>Total Booking</small><strong><?= $counts['total'] ?></strong></div>
        </div>
        <div class="booking-stat stat-pending"><span class="stat-icon">⌛</span>
            <div><small>Menunggu</small><strong><?= $counts['menunggu'] ?></strong></div>
        </div>
        <div class="booking-stat stat-confirmed"><span class="stat-icon">✓</span>
            <div><small>Dikonfirmasi</small><strong><?= $counts['dikonfirmasi'] ?></strong></div>
        </div>
        <div class="booking-stat stat-rejected"><span class="stat-icon">×</span>
            <div><small>Ditolak</small><strong><?= $counts['ditolak'] ?></strong></div>
        </div>
        <div class="booking-stat stat-completed"><span class="stat-icon">✓</span>
            <div><small>Selesai</small><strong><?= $counts['selesai'] ?></strong></div>
        </div>
    </section>

    <div class="booking-content-layout">
        <section class="booking-card-list">
            <?php if (!$bookings): ?>
                <div class="booking-empty">Belum ada data booking yang sesuai filter.</div>
                <?php else: foreach ($bookings as $row): ?>
                    <article class="booking-record">
                        <a class="booking-record-main" href="detail.php?id=<?= (int)$row['id'] ?>">
                            <div class="booking-record-avatar">▦</div>
                            <div class="booking-record-info">
                                <div class="booking-record-title"><strong><?= e($row['kode_booking']) ?></strong><span class="booking-badge badge-<?= e($row['booking_status']) ?>"><?= e(ucfirst($row['booking_status'])) ?></span></div>
                                <div class="booking-person"><?= e($row['nama_user']) ?></div>
                                <div class="booking-meta"><span>⌂ <?= e($row['nama_lapangan']) ?></span><span>▦ <?= date('d M Y', strtotime($row['tanggal'])) ?> · <?= e(substr($row['jam_mulai'], 0, 5)) ?>–<?= e(substr($row['jam_selesai'], 0, 5)) ?></span></div>
                                <small class="booking-created">Dipesan <?= date('d M Y, H:i', strtotime($row['tanggal_booking'])) ?></small>
                            </div>
                        </a>
                        <a class="booking-more" href="detail.php?id=<?= (int)$row['id'] ?>" aria-label="Detail booking <?= e($row['kode_booking']) ?>">•••</a>
                    </article>
            <?php endforeach;
            endif; ?>
            <div class="booking-list-footer">Menampilkan <?= count($bookings) ?> data booking</div>
        </section>

    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.js-status-form').forEach(form => form.addEventListener('submit', event => {
        event.preventDefault();
        const status = form.dataset.status;
        const configs = {
            dikonfirmasi: ['Konfirmasi booking?', 'Booking ini akan disetujui.'],
            ditolak: ['Tolak booking?', 'Booking akan ditolak dan riwayatnya tetap disimpan.'],
            selesai: ['Selesaikan booking?', 'Pastikan penggunaan lapangan sudah selesai.']
        };
        Swal.fire({
            title: configs[status][0],
            text: configs[status][1],
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonText: 'Lanjutkan',
            confirmButtonColor: '#155EEF',
            cancelButtonColor: '#667085',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    }));
</script>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>