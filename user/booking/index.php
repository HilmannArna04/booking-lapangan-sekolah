<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_user();

$page_title = "Booking Lapangan";
$active_menu = "booking";

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');


/*
|--------------------------------------------------------------------------
| VALIDASI TANGGAL
|--------------------------------------------------------------------------
*/

$date_object = DateTime::createFromFormat('Y-m-d', $tanggal);

if (!$date_object || $date_object->format('Y-m-d') !== $tanggal) {
    $tanggal = date('Y-m-d');
}

$today = date('Y-m-d');
$max_date = date('Y-m-d', strtotime('+1 day'));
$is_past_date = ($tanggal < $today);
$is_outside_window = ($tanggal > $max_date);


/*
|--------------------------------------------------------------------------
| AMBIL SEMUA LAPANGAN AKTIF
|--------------------------------------------------------------------------
*/

$lapangan_query = mysqli_query(
    $conn,
    "SELECT
        id,
        nama,
        jenis,
        lokasi,
        status
     FROM lapangan
     WHERE status = 'aktif'
     ORDER BY nama ASC"
);

$lapangan_list = [];

while ($lapangan = mysqli_fetch_assoc($lapangan_query)) {
    $lapangan_list[] = $lapangan;
}

// Bentuk slot otomatis setiap hari: 08.00-15.00, interval 60 menit.
$slot_list = [];
for ($hour = 8; $hour < 15; $hour++) {
    $slot_list[] = ['mulai' => sprintf('%02d:00', $hour), 'selesai' => sprintf('%02d:00', $hour + 1)];
}

if ($lapangan_list && !$is_past_date && !$is_outside_window) {
    // Slot yang sengaja dihapus admin disimpan sebagai pengecualian,
    // supaya generator tidak membuatnya kembali.
    $excluded_slots = [];
    $excluded_stmt = mysqli_prepare(
        $conn,
        "SELECT lapangan_id, jam_mulai, jam_selesai
         FROM jadwal_dikecualikan
         WHERE tanggal = ?"
    );
    mysqli_stmt_bind_param($excluded_stmt, "s", $tanggal);
    mysqli_stmt_execute($excluded_stmt);
    $excluded_result = mysqli_stmt_get_result($excluded_stmt);

    while ($excluded = mysqli_fetch_assoc($excluded_result)) {
        $key = (int) $excluded['lapangan_id'] . '|' .
            substr($excluded['jam_mulai'], 0, 8) . '|' .
            substr($excluded['jam_selesai'], 0, 8);
        $excluded_slots[$key] = true;
    }
    mysqli_stmt_close($excluded_stmt);

    $check_slot = mysqli_prepare(
        $conn,
        "SELECT id FROM jadwal
         WHERE lapangan_id = ? AND tanggal = ?
           AND jam_mulai = ? AND jam_selesai = ?
         LIMIT 1"
    );
    $insert_slot = mysqli_prepare(
        $conn,
        "INSERT INTO jadwal
         (lapangan_id, tanggal, jam_mulai, jam_selesai, status)
         VALUES (?, ?, ?, ?, 'tersedia')"
    );

    foreach ($lapangan_list as $court) {
        foreach ($slot_list as $slot) {
            $court_id = (int) $court['id'];
            $start = $slot['mulai'] . ':00';
            $end = $slot['selesai'] . ':00';
            $key = $court_id . '|' . $start . '|' . $end;

            // Jangan membuat ulang slot yang sengaja dihapus admin.
            if (isset($excluded_slots[$key])) {
                continue;
            }

            mysqli_stmt_bind_param($check_slot, "isss", $court_id, $tanggal, $start, $end);
            mysqli_stmt_execute($check_slot);
            $result_slot = mysqli_stmt_get_result($check_slot);
            $exists = mysqli_fetch_assoc($result_slot);
            mysqli_free_result($result_slot);

            if (!$exists) {
                mysqli_stmt_bind_param($insert_slot, "isss", $court_id, $tanggal, $start, $end);
                if (!mysqli_stmt_execute($insert_slot) && mysqli_errno($conn) !== 1062) {
                    throw new RuntimeException("Gagal membuat slot otomatis: " . mysqli_error($conn));
                }
            }
        }
    }

    mysqli_stmt_close($check_slot);
    mysqli_stmt_close($insert_slot);
}


/*
|--------------------------------------------------------------------------
| AMBIL SEMUA JADWAL PADA TANGGAL YANG DIPILIH
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,

    "SELECT
        j.id,
        j.lapangan_id,
        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,
        j.status,

        l.nama AS nama_lapangan,
        l.jenis AS jenis_lapangan

    FROM jadwal j

    INNER JOIN lapangan l
        ON j.lapangan_id = l.id

    WHERE j.tanggal = ?

    AND l.status = 'aktif'

    ORDER BY
        l.nama ASC,
        j.jam_mulai ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $tanggal
);

mysqli_stmt_execute($stmt);

$jadwal_query = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| SUSUN DATA JADWAL
|--------------------------------------------------------------------------
|
| Bentuk data:
|
| $jadwal_data[lapangan_id][jam_mulai]
|
*/

$jadwal_data = [];
$jam_list = [];

while ($jadwal = mysqli_fetch_assoc($jadwal_query)) {

    $jam_mulai = substr(
        $jadwal['jam_mulai'],
        0,
        5
    );

    $jam_selesai = substr(
        $jadwal['jam_selesai'],
        0,
        5
    );

    $jam_key = $jam_mulai . "-" . $jam_selesai;

    $jam_list[$jam_key] = [
        'mulai' => $jam_mulai,
        'selesai' => $jam_selesai
    ];

    $jadwal_data[$jadwal['lapangan_id']][$jam_key] = $jadwal;
}

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| AMBIL DATA BOOKING UNTUK JADWAL YANG SUDAH DIBOOKING
|--------------------------------------------------------------------------
*/

$booking_data = [];

$booking_stmt = mysqli_prepare(
    $conn,

    "SELECT
        b.jadwal_id,
        b.kode_booking,
        b.status,
        u.name AS nama_pemesan

    FROM bookings b

    INNER JOIN jadwal j
        ON b.jadwal_id = j.id

    INNER JOIN users u
        ON b.user_id = u.id

    WHERE j.tanggal = ?

    AND b.status IN (
        'menunggu',
        'dikonfirmasi',
        'selesai'
    )

    ORDER BY b.id DESC"
);

mysqli_stmt_bind_param(
    $booking_stmt,
    "s",
    $tanggal
);

mysqli_stmt_execute($booking_stmt);

$booking_query = mysqli_stmt_get_result(
    $booking_stmt
);

while ($booking = mysqli_fetch_assoc($booking_query)) {

    if (!isset($booking_data[$booking['jadwal_id']])) {
        $booking_data[$booking['jadwal_id']] = $booking;
    }
}

mysqli_stmt_close($booking_stmt);

/*
|--------------------------------------------------------------------------
| FORMAT TANGGAL UNTUK TAMPILAN
|--------------------------------------------------------------------------
*/

$nama_hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$date_timestamp = strtotime($tanggal);

$hari_tampil = $nama_hari[date('l', $date_timestamp)];

$tanggal_tampil = date(
    'j',
    $date_timestamp
);

$bulan_tampil = $nama_bulan[(int) date('n', $date_timestamp)];

$tahun_tampil = date(
    'Y',
    $date_timestamp
);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../../includes/header.php";

?>
<link rel="stylesheet" href="/booking-lapangan-sekolah/assets/css/booking-page.css">

<div class="main booking-page-shell">

    <?php
    $active_menu = "booking";
    require __DIR__ . "/../navbar.php";
    ?>


    <main class="content booking-page">

        <?php show_flash(); ?>


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="page-heading booking-heading">

            <div>

                <h2>Jadwal Lapangan</h2>

                <p>
                    Pilih tanggal untuk melihat semua lapangan dan seluruh jam yang tersedia.
                </p>

            </div>

        </div>


        <!-- =====================================================
             FILTER
        ====================================================== -->

        <div class="booking-filter-card">

            <form
                method="GET"
                class="booking-filter-form">

                <!-- TANGGAL -->

                <div class="booking-date-control">

                    <button
                        type="button"
                        class="date-arrow"
                        onclick="ubahTanggal(-1)">
                        ‹
                    </button>


                    <label class="date-display">

                        <span class="date-icon">
                            ▣
                        </span>

                        <span>

                            <strong>
                                <?= e($tanggal_tampil) ?>
                                <?= e($bulan_tampil) ?>
                                <?= e($tahun_tampil) ?>
                            </strong>

                            <small>
                                <?= e($hari_tampil) ?>
                            </small>

                        </span>

                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            min="<?= e($today) ?>"
                            max="<?= e($max_date) ?>"
                            value="<?= e($tanggal) ?>"
                            onchange="this.form.submit()">

                    </label>


                    <button
                        type="button"
                        class="date-arrow"
                        onclick="ubahTanggal(1)">
                        ›
                    </button>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary booking-search-btn">
                    Cari Jadwal
                </button>

            </form>

        </div>


        <!-- =====================================================
             JADWAL
        ====================================================== -->

        <div class="schedule-card">

            <div class="schedule-table-wrap">

                <table class="schedule-table">

                    <thead>

                        <tr>

                            <th class="court-column">
                                Lapangan
                            </th>

                            <?php foreach ($slot_list as $jam): ?>

                                <th>

                                    <?= e($jam['mulai']) ?>

                                </th>

                            <?php endforeach; ?>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($lapangan_list)): ?>

                            <tr>

                                <td
                                    colspan="<?= count($slot_list) + 1 ?>"
                                    class="schedule-empty">

                                    Belum ada lapangan aktif.

                                </td>

                            </tr>

                        <?php else: ?>


                            <?php foreach ($lapangan_list as $lapangan): ?>

                                <tr
                                    class="court-row"
                                    data-court-id="<?= e($lapangan['id']) ?>">

                                    <!-- LAPANGAN -->

                                    <td class="court-info">

                                        <div class="court-thumb">

                                            <?= e(
                                                strtoupper(
                                                    substr(
                                                        $lapangan['jenis'],
                                                        0,
                                                        1
                                                    )
                                                )
                                            ) ?>

                                        </div>


                                        <div>

                                            <strong>
                                                <?= e(
                                                    $lapangan['nama']
                                                ) ?>
                                            </strong>

                                            <span>
                                                <?= e(
                                                    $lapangan['jenis']
                                                ) ?>
                                            </span>

                                        </div>

                                    </td>


                                    <!-- SLOT JADWAL -->

                                    <?php foreach ($slot_list as $jam): ?>

                                        <?php

                                        $jam_key = $jam['mulai'] . "-" . $jam['selesai'];
                                        $jadwal = $jadwal_data[$lapangan['id']][$jam_key] ?? null;
                                        $is_booked = $jadwal && isset($booking_data[$jadwal['id']]);

                                        ?>


                                        <td class="schedule-cell">

                                            <?php $is_past_slot = ($tanggal === $today && $jam['mulai'] <= date('H:i')); ?>

                                            <?php if ($is_past_date): ?>
                                                <div class="slot slot-unavailable" title="Tanggal sudah lewat"><span>Tanggal Lewat</span></div>
                                            <?php elseif ($is_outside_window): ?>
                                                <div class="slot slot-unavailable" title="Booking hanya dibuka untuk hari ini dan besok"><span>Belum Dibuka</span></div>
                                            <?php elseif ($is_past_slot): ?>
                                                <div class="slot slot-unavailable" title="Jam booking sudah lewat"><span>Jam Lewat</span></div>
                                            <?php elseif (!$jadwal): ?>

                                                <div class="slot slot-unavailable">

                                                    <span>
                                                        Tidak Tersedia
                                                    </span>

                                                </div>


                                            <?php elseif (!$is_booked && $jadwal['status'] === 'tersedia'): ?>


                                                <button
                                                    type="button"
                                                    class="slot slot-available"
                                                    data-jadwal-id="<?= (int) $jadwal['id'] ?>"
                                                    data-lapangan="<?= e($lapangan['nama']) ?>"
                                                    data-tanggal="<?= e($tanggal) ?>"
                                                    data-mulai="<?= e($jam['mulai']) ?>"
                                                    data-selesai="<?= e($jam['selesai']) ?>">
                                                    <span>Booking</span>
                                                </button>


                                            <?php elseif ($is_booked || $jadwal['status'] !== 'tersedia'): ?>


                                                <div class="slot slot-booked">

                                                    <?php if (
                                                        isset(
                                                            $booking_data[$jadwal['id']]
                                                        )
                                                    ): ?>

                                                        <strong>
                                                            <?= e(
                                                                $booking_data[$jadwal['id']]['nama_pemesan']
                                                            ) ?>
                                                        </strong>

                                                        <small>
                                                            <?= e(
                                                                $booking_data[$jadwal['id']]['kode_booking']
                                                            ) ?>
                                                        </small>

                                                    <?php else: ?>

                                                        <strong>
                                                            Terbooking
                                                        </strong>

                                                    <?php endif; ?>


                                                    <span>
                                                        <?= e($jam['mulai']) ?>
                                                        -
                                                        <?= e($jam['selesai']) ?>
                                                    </span>

                                                </div>


                                            <?php else: ?>


                                                <div class="slot slot-unavailable">

                                                    <span>
                                                        Tidak Tersedia
                                                    </span>

                                                </div>


                                            <?php endif; ?>


                                        </td>

                                    <?php endforeach; ?>

                                </tr>

                            <?php endforeach; ?>


                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 LEGEND
            ================================================== -->

            <div class="schedule-legend">

                <div>
                    <span class="legend-dot available"></span>
                    Tersedia
                </div>

                <div>
                    <span class="legend-dot booked"></span>
                    Terbooking
                </div>

                <div>
                    <span class="legend-dot unavailable"></span>
                    Tidak Tersedia
                </div>

            </div>

        </div>


        <!-- FORM DETAIL BOOKING -->
        <div id="bookingModal" class="booking-modal" aria-hidden="true" style="display:none;">
            <div class="booking-modal__panel" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">
                <button type="button" class="booking-modal__close" onclick="tutupFormBooking()" aria-label="Tutup">&times;</button>
                <h2 id="bookingModalTitle">Form Booking Lapangan</h2>
                <p class="booking-modal__intro">Lengkapi data berikut sebelum mengajukan booking.</p>

                <div class="booking-summary">
                    <div><span>Lapangan</span><strong id="bookingLapangan">-</strong></div>
                    <div><span>Tanggal</span><strong id="bookingTanggal">-</strong></div>
                    <div><span>Jam</span><strong id="bookingJam">-</strong></div>
                </div>

                <form method="POST" action="proses.php" class="booking-detail-form">
                    <input type="hidden" name="jadwal_id" id="bookingJadwalId">

                    <label for="bookingKelas">Kelas / kelompok <span>*</span></label>
                    <input type="text" id="bookingKelas" name="kelas" maxlength="50" placeholder="Contoh: XI RPL 1" required>

                    <label for="bookingKeperluan">Keperluan booking <span>*</span></label>
                    <input type="text" id="bookingKeperluan" name="keperluan" maxlength="255" placeholder="Contoh: Pembelajaran PJOK" required>

                    <label for="bookingCatatan">Catatan tambahan (opsional)</label>
                    <textarea id="bookingCatatan" name="catatan" rows="3" maxlength="2000" placeholder="Informasi tambahan jika ada"></textarea>

                    <div class="booking-modal__actions">
                        <button type="button" class="btn btn-secondary" onclick="tutupFormBooking()">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan Booking</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

</div>


<script>
    function bukaFormBooking(id, lapangan, tanggal, mulai, selesai) {
        document.getElementById("bookingJadwalId").value = id;
        document.getElementById("bookingLapangan").textContent = lapangan;
        document.getElementById("bookingTanggal").textContent = tanggal;
        document.getElementById("bookingJam").textContent = mulai + " - " + selesai;
        const modal = document.getElementById("bookingModal");
        if (!modal) {
            if (window.Swal) Swal.fire({
                icon: "error",
                title: "Form tidak ditemukan",
                text: "Muat ulang halaman lalu coba lagi.",
                confirmButtonColor: "#155EEF"
            });
            return;
        }
        modal.classList.add("is-open");
        modal.style.display = "flex";
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("booking-modal-open");
        const kelasInput = document.getElementById("bookingKelas");
        if (kelasInput) kelasInput.focus();
    }

    function tutupFormBooking() {
        const modal = document.getElementById("bookingModal");
        if (!modal) return;
        modal.classList.remove("is-open");
        modal.style.display = "none";
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("booking-modal-open");
    }

    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") tutupFormBooking();
    });

    const bookingModalElement = document.getElementById("bookingModal");
    if (bookingModalElement) {
        bookingModalElement.addEventListener("click", function(event) {
            if (event.target === this) tutupFormBooking();
        });
    }

    // Pasang handler pada tombol booking setelah halaman selesai dirender.
    document.querySelectorAll(".slot-available[data-jadwal-id]").forEach(function(button) {
        button.addEventListener("click", function() {
            bukaFormBooking(
                this.dataset.jadwalId,
                this.dataset.lapangan,
                this.dataset.tanggal,
                this.dataset.mulai,
                this.dataset.selesai
            );
        });
    });

    function ubahTanggal(jumlah) {

        const input =
            document.getElementById("tanggal");

        const tanggal =
            new Date(input.value);

        tanggal.setDate(
            tanggal.getDate() + jumlah
        );

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (tanggal < today) tanggal.setTime(today.getTime());
        const maxDate = new Date();
        maxDate.setHours(0, 0, 0, 0);
        maxDate.setDate(maxDate.getDate() + 1);
        if (tanggal > maxDate) tanggal.setTime(maxDate.getTime());

        const tahun =
            tanggal.getFullYear();

        const bulan =
            String(
                tanggal.getMonth() + 1
            ).padStart(2, "0");

        const hari =
            String(
                tanggal.getDate()
            ).padStart(2, "0");

        input.value =
            tahun + "-" + bulan + "-" + hari;

        input.form.submit();
    }
</script>


<?php

require_once __DIR__ . "/../../includes/footer.php";

?>