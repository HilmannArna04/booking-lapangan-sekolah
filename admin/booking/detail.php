<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_admin();

$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {
    redirect(
        "/booking-lapangan-sekolah/admin/booking/index.php"
    );
}


$stmt = mysqli_prepare(
    $conn,
    "SELECT

        b.id,
        b.kode_booking,
        b.catatan,
        b.status AS booking_status,
        b.created_at AS tanggal_booking,

        u.name AS nama_user,
        u.email AS email_user,
        u.phone AS phone_user,

        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,

        l.nama AS nama_lapangan,
        l.jenis AS jenis_lapangan,
        l.lokasi

     FROM bookings b

     INNER JOIN users u
        ON b.user_id = u.id

     INNER JOIN jadwal j
        ON b.jadwal_id = j.id

     INNER JOIN lapangan l
        ON j.lapangan_id = l.id

     WHERE b.id = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$booking) {

    exit("Data booking tidak ditemukan.");
}


$page_title = "Detail Booking";
require_once __DIR__ . "/../../includes/header.php";
?>
<link rel="stylesheet" href="/booking-lapangan-sekolah/assets/css/admin-booking-detail.css">
<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>
    <main class="content booking-detail-page admin-booking-detail-v3">
        <a class="db-back" href="index.php"><span aria-hidden="true">←</span> Kembali</a>
        <header class="db-heading">
            <h2>Detail Booking</h2>
            <p>Lihat informasi lengkap mengenai pemesanan lapangan.</p>
        </header>
        <div class="db-layout">
            <section class="db-main-column">
                <article class="db-panel db-summary">
                    <div class="db-summary-item">
                        <span class="db-eyebrow">Kode Booking</span>
                        <strong class="db-code"><?= e($booking['kode_booking']) ?></strong>
                    </div>
                    <div class="db-summary-item">
                        <span class="db-eyebrow">Status</span>
                        <span class="db-status db-status-<?= preg_replace('/[^a-z0-9_-]/', '', strtolower($booking['booking_status'])) ?>"><?= e(ucfirst($booking['booking_status'])) ?></span>
                    </div>
                    <div class="db-summary-item">
                        <span class="db-eyebrow">Tanggal Pemesanan</span>
                        <strong class="db-summary-date"><?= e(date('d M Y, H:i', strtotime($booking['tanggal_booking']))) ?></strong>
                    </div>
                </article>
                <article class="db-panel db-info-panel">
                    <section class="db-info-section">
                        <h3><span class="db-icon">♙</span> Informasi Pemesan</h3>
                        <dl class="db-detail-list">
                            <div>
                                <dt>Nama Pemesan</dt>
                                <dd><?= e($booking['nama_user']) ?></dd>
                            </div>
                            <div>
                                <dt>Email</dt>
                                <dd><?= e($booking['email_user'] ?: '-') ?></dd>
                            </div>
                            <div>
                                <dt>No. WhatsApp</dt>
                                <dd><?= e($booking['phone_user'] ?: '-') ?></dd>
                            </div>
                        </dl>
                    </section>
                    <section class="db-info-section">
                        <h3><span class="db-icon">▦</span> Detail Booking</h3>
                        <dl class="db-detail-list">
                            <div>
                                <dt>Lapangan</dt>
                                <dd><?= e($booking['nama_lapangan']) ?></dd>
                            </div>
                            <div>
                                <dt>Jenis Lapangan</dt>
                                <dd><?= e($booking['jenis_lapangan']) ?></dd>
                            </div>
                            <div>
                                <dt>Tanggal</dt>
                                <dd><?= e(date('d M Y', strtotime($booking['tanggal']))) ?></dd>
                            </div>
                            <div>
                                <dt>Jam</dt>
                                <dd><?= e(substr($booking['jam_mulai'], 0, 5)) ?> – <?= e(substr($booking['jam_selesai'], 0, 5)) ?></dd>
                            </div>
                        </dl>
                    </section>
                    <section class="db-info-section db-notes-section">
                        <h3><span class="db-icon">▤</span> Catatan</h3>
                        <div class="db-note-box"><?= nl2br(e(trim($booking['catatan'] ?? '') !== '' ? $booking['catatan'] : 'Tidak ada catatan tambahan dari pemesan.')) ?></div>
                    </section>
                </article>
                <article class="db-panel db-history-panel">
                    <h3><span class="db-icon">◷</span> Riwayat Status</h3>
                    <div class="db-timeline">
                        <div class="db-timeline-step <?= strtolower($booking['booking_status']) === 'menunggu' ? 'is-current' : 'is-done' ?>"><i></i><span>Menunggu</span><small><?= e(date('d M Y, H:i', strtotime($booking['tanggal_booking']))) ?></small></div>
                        <div class="db-timeline-step <?= strtolower($booking['booking_status']) === 'dikonfirmasi' ? 'is-current' : (in_array(strtolower($booking['booking_status']), ['selesai']) ? 'is-done' : '') ?>"><i></i><span>Dikonfirmasi</span><small>—</small></div>
                        <div class="db-timeline-step <?= strtolower($booking['booking_status']) === 'selesai' ? 'is-current' : '' ?>"><i></i><span>Selesai</span><small>—</small></div>
                        <div class="db-timeline-step <?= strtolower($booking['booking_status']) === 'dibatalkan' ? 'is-current' : '' ?>"><i></i><span>Dibatalkan</span><small>—</small></div>
                    </div>
                    <p class="db-history-note">Waktu perubahan status selain waktu pemesanan belum tersedia pada data saat ini.</p>
                </article>
            </section>
            <aside class="db-side-column">
                <article class="db-panel db-court-panel">
                    <div class="db-court-art" aria-hidden="true"><span>▦</span></div>
                    <div class="db-court-title">
                        <h3><?= e($booking['nama_lapangan']) ?></h3><span><?= e($booking['jenis_lapangan']) ?></span>
                    </div>
                    <p class="db-location">⌖ <?= e($booking['lokasi'] ?: 'Lokasi belum diisi') ?></p>
                    <p class="db-court-description">Informasi lapangan yang dipilih untuk pemesanan ini.</p>
                </article>
                <article class="db-panel db-actions-panel">
                    <h3><span class="db-icon">✓</span> Aksi Booking</h3>
                    <p class="db-actions-description">Perbarui status pemesanan sesuai hasil pemeriksaan admin.</p>
                    <?php if (strtolower($booking['booking_status']) === 'menunggu'): ?>
                        <div class="db-action-buttons">
                            <form method="POST" action="update_status.php">
                                <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                <input type="hidden" name="status" value="dikonfirmasi">
                                <button class="db-action-btn db-action-confirm" type="submit">✓ Konfirmasi</button>
                            </form>
                            <form method="POST" action="update_status.php" onsubmit="return confirm('Yakin ingin menolak booking ini?')">
                                <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                <input type="hidden" name="status" value="ditolak">
                                <button class="db-action-btn db-action-reject" type="submit">× Tolak</button>
                            </form>
                        </div>
                    <?php elseif (strtolower($booking['booking_status']) === 'dikonfirmasi'): ?>
                        <button class="db-action-btn db-action-finish" type="button" id="openDamageModal">✓ Tandai Selesai</button>
                        <div class="db-modal-backdrop" id="damageModal" aria-hidden="true">
                            <section class="db-damage-modal" role="dialog" aria-modal="true" aria-labelledby="damageModalTitle">
                                <button class="db-modal-close" type="button" id="closeDamageModal" aria-label="Tutup">×</button>
                                <h3 id="damageModalTitle">Selesaikan Booking</h3>
                                <p>Tambahkan keterangan kondisi atau kerusakan lapangan sebelum menyelesaikan booking.</p>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                                    <input type="hidden" name="status" value="selesai">
                                    <label for="damageNote">Keterangan Kerusakan</label>
                                    <textarea id="damageNote" name="keterangan_kerusakan" rows="4" placeholder="Contoh: Tidak ada kerusakan / jaring lapangan rusak..." required></textarea>
                                    <div class="db-modal-actions">
                                        <button class="db-modal-cancel" type="button" id="cancelDamageModal">Batal</button>
                                        <button class="db-action-btn db-action-finish" type="submit">Simpan & Selesaikan</button>
                                    </div>
                                </form>
                            </section>
                        </div>
                    <?php else: ?>
                        <div class="db-action-locked">Booking berstatus <?= e(ucfirst($booking['booking_status'])) ?>. Tidak ada aksi yang tersedia.</div>
                    <?php endif; ?>
                </article>
            </aside>
        </div>
    </main>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
<script>
    (() => {
        const modal = document.getElementById('damageModal');
        const openButton = document.getElementById('openDamageModal');
        const closeButton = document.getElementById('closeDamageModal');
        const cancelButton = document.getElementById('cancelDamageModal');
        if (!modal || !openButton) return;
        const openModal = () => {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('db-modal-open');
            document.getElementById('damageNote')?.focus();
        };
        const closeModal = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('db-modal-open');
        };
        openButton.addEventListener('click', openModal);
        closeButton?.addEventListener('click', closeModal);
        cancelButton?.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeModal();
        });
    })();
</script>