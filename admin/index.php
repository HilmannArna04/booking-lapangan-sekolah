<?php

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";

require_admin();

$page_title = "Dashboard";
$active_menu = "dashboard";

/*
|--------------------------------------------------------------------------
| STATISTIK DASHBOARD
|--------------------------------------------------------------------------
*/

$lapangan_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM lapangan"
    )
)['total'];

$user_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'user'"
    )
)['total'];

$jadwal_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM jadwal"
    )
)['total'];

$booking_count = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM bookings"
    )
)['total'];


/*
|--------------------------------------------------------------------------
| BOOKING TERBARU
|--------------------------------------------------------------------------
|
| Relasi:
| bookings.jadwal_id
|        ↓
| jadwal.id
|        ↓
| jadwal.lapangan_id
|        ↓
| lapangan.id
|
*/

$booking_query = mysqli_query(
    $conn,
    "SELECT
        b.id,
        b.kode_booking,
        b.catatan,
        b.status AS booking_status,
        b.created_at AS tanggal_booking,

        j.tanggal,
        j.jam_mulai,
        j.jam_selesai,

        l.nama AS nama_lapangan

     FROM bookings b

     INNER JOIN jadwal j
        ON b.jadwal_id = j.id

     INNER JOIN lapangan l
        ON j.lapangan_id = l.id

     ORDER BY b.created_at DESC

     LIMIT 5"
);

if (!$booking_query) {
    die("Query booking gagal: " . mysqli_error($conn));
}


require_once __DIR__ . "/../includes/header.php";
?>

<div class="main">

    <?php require __DIR__ . "/../includes/navbar.php"; ?>

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>


    <main class="content">

        <?php show_flash(); ?>


        <!-- =====================================================
             HEADING
        ====================================================== -->

        <div class="page-heading">

            <div>

                <h2>Ringkasan Sistem</h2>

                <p>
                    Informasi utama pengelolaan booking lapangan.
                </p>

            </div>

        </div>


        <!-- =====================================================
             STATISTIK
        ====================================================== -->

        <section class="cards">

            <div class="card">

                <div class="stat-label">
                    Total Lapangan
                </div>

                <div class="stat-value">
                    <?= e($lapangan_count) ?>
                </div>

            </div>


            <div class="card">

                <div class="stat-label">
                    Total Pengguna
                </div>

                <div class="stat-value">
                    <?= e($user_count) ?>
                </div>

            </div>


            <div class="card">

                <div class="stat-label">
                    Total Jadwal
                </div>

                <div class="stat-value">
                    <?= e($jadwal_count) ?>
                </div>

            </div>


            <div class="card">

                <div class="stat-label">
                    Total Booking
                </div>

                <div class="stat-value">
                    <?= e($booking_count) ?>
                </div>

            </div>

        </section>


        <!-- =====================================================
             MENU PENGELOLAAN
        ====================================================== -->

        <div class="table-card">

            <div class="table-header">

                <h3>
                    Menu Pengelolaan
                </h3>

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

                            <td>
                                Data Lapangan
                            </td>

                            <td>
                                Menambah, mengubah, dan mengelola
                                status lapangan.
                            </td>

                            <td>

                                <a
                                    class="btn btn-secondary"
                                    href="lapangan/index.php"
                                >
                                    Buka
                                </a>

                            </td>

                        </tr>


                        <tr>

                            <td>
                                Data Jadwal
                            </td>

                            <td>
                                Mengelola jadwal operasional
                                setiap lapangan.
                            </td>

                            <td>

                                <a
                                    class="btn btn-secondary"
                                    href="jadwal/index.php"
                                >
                                    Buka
                                </a>

                            </td>

                        </tr>


                        <tr>

                            <td>
                                Data Pengguna
                            </td>

                            <td>
                                Mengelola akun pengguna sistem.
                            </td>

                            <td>

                                <a
                                    class="btn btn-secondary"
                                    href="pengguna/index.php"
                                >
                                    Buka
                                </a>

                            </td>

                        </tr>


                        <tr>

                            <td>
                                Data Booking
                            </td>

                            <td>
                                Melihat dan memantau transaksi
                                booking lapangan.
                            </td>

                            <td>

                                <a
                                    class="btn btn-secondary"
                                    href="booking/index.php"
                                >
                                    Buka
                                </a>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             BOOKING TERBARU
        ====================================================== -->

        <div class="table-card">

            <div class="table-header">

                <h3>
                    Booking Terbaru
                </h3>

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

                        <?php if (mysqli_num_rows($booking_query) > 0): ?>

                            <?php while ($booking = mysqli_fetch_assoc($booking_query)): ?>

                                <tr>

                                    <!-- Kode Booking -->

                                    <td>
                                        <?= e(
                                            $booking['kode_booking']
                                        ) ?>
                                    </td>


                                    <!-- Lapangan -->

                                    <td>
                                        <?= e(
                                            $booking['nama_lapangan']
                                        ) ?>
                                    </td>


                                    <!-- Tanggal -->

                                    <td>

                                        <?php
                                        echo date(
                                            'd-m-Y',
                                            strtotime(
                                                $booking['tanggal']
                                            )
                                        );
                                        ?>

                                    </td>


                                    <!-- Jam -->

                                    <td>

                                        <?= e(
                                            $booking['jam_mulai']
                                        ) ?>

                                        -

                                        <?= e(
                                            $booking['jam_selesai']
                                        ) ?>

                                    </td>


                                    <!-- Status -->

                                    <td>

                                        <?= e(
                                            $booking['booking_status']
                                        ) ?>

                                    </td>


                                    <!-- Tanggal Booking -->

                                    <td>

                                        <?php
                                        echo date(
                                            'd-m-Y H:i',
                                            strtotime(
                                                $booking['tanggal_booking']
                                            )
                                        );
                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align: center;"
                                >

                                    Belum ada data booking.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


    </main>

</div>


<?php require_once __DIR__ . "/../includes/footer.php"; ?>