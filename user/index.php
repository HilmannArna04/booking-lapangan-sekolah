<?php

require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";

require_user();

$page_title = "Dashboard User";
$active_menu = "dashboard";

$user_id = $_SESSION['user_id'];


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

     INNER JOIN jadwal j
        ON b.jadwal_id = j.id

     INNER JOIN lapangan l
        ON j.lapangan_id = l.id

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

     INNER JOIN jadwal j
        ON b.jadwal_id = j.id

     INNER JOIN lapangan l
        ON j.lapangan_id = l.id

     WHERE b.user_id = ?
     AND j.tanggal = ?

     ORDER BY j.jam_mulai ASC

     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt_today,
    "is",
    $user_id,
    $today
);

mysqli_stmt_execute($stmt_today);

$today_result = mysqli_stmt_get_result($stmt_today);

$jadwal_hari_ini = mysqli_fetch_assoc($today_result);

mysqli_stmt_close($stmt_today);


require_once __DIR__ . "/../includes/header.php";

?>


<div class="main">

    <?php require __DIR__ . "/navbar.php"; ?>


    <main class="content">

        <?php show_flash(); ?>


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="page-heading">

            <div>

                <h2>Dashboard</h2>

                <p>
                    Sistem Booking Lapangan
                </p>

            </div>


            <div class="user-info">

                <span>
                    <?= e($_SESSION['name']) ?>
                </span>

                <span class="badge">
                    User
                </span>

            </div>

        </div>


        <!-- =====================================================
             STATISTIK
        ====================================================== -->

        <section class="cards">


            <!-- BOOKING AKTIF -->

            <div class="card">

                <div class="stat-label">
                    Booking Aktif
                </div>

                <div class="stat-value">
                    <?= e($total_aktif) ?>
                </div>

            </div>


            <!-- JADWAL -->

            <div class="card">

                <div class="stat-label">
                    Jadwal Saya
                </div>

                <div class="stat-value">
                    <?= e($total_jadwal) ?>
                </div>

            </div>


            <!-- SELESAI -->

            <div class="card">

                <div class="stat-label">
                    Booking Selesai
                </div>

                <div class="stat-value">
                    <?= e($total_selesai) ?>
                </div>

            </div>


            <!-- BOOKING BARU -->

            <div class="card">

                <div class="stat-label">
                    Total Booking
                </div>

                <div class="stat-value">
                    <?= e($total_jadwal) ?>
                </div>

            </div>

        </section>


        <!-- =====================================================
             JADWAL HARI INI + INFORMASI
        ====================================================== -->

        <div class="table-card">

            <div class="table-header">

                <h3>
                    Jadwal Hari Ini
                </h3>

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

                                <td>
                                    <?= e(
                                        $jadwal_hari_ini['kode_booking']
                                    ) ?>
                                </td>


                                <td>
                                    <?= e(
                                        $jadwal_hari_ini['nama_lapangan']
                                    ) ?>
                                </td>


                                <td>

                                    <?= e(
                                        substr(
                                            $jadwal_hari_ini['jam_mulai'],
                                            0,
                                            5
                                        )
                                    ) ?>

                                    -

                                    <?= e(
                                        substr(
                                            $jadwal_hari_ini['jam_selesai'],
                                            0,
                                            5
                                        )
                                    ) ?>

                                </td>


                                <td>
                                    <?= e(
                                        ucfirst(
                                            $jadwal_hari_ini['status']
                                        )
                                    ) ?>
                                </td>

                            </tr>

                        </tbody>

                    </table>


                <?php else: ?>

                    <div class="empty">

                        Tidak ada jadwal booking hari ini.

                        <br><br>

                        <a
                            class="btn btn-primary"
                            href="/booking-lapangan-sekolah/user/booking/"
                        >
                            Booking Lapangan
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- =====================================================
             INFORMASI SISTEM
        ====================================================== -->

        <div class="table-card">

            <div class="table-header">

                <h3>
                    Informasi Sistem
                </h3>

            </div>


            <div class="table-wrap">

                <table>

                    <tbody>

                        <tr>

                            <td>

                                <strong>
                                    Selamat Datang
                                </strong>

                                <br><br>

                                Silakan melakukan booking
                                lapangan melalui menu
                                <strong>Booking Saya</strong>.

                            </td>

                        </tr>


                        <tr>

                            <td>

                                <strong>
                                    Informasi Booking
                                </strong>

                                <br><br>

                                Pastikan memilih tanggal,
                                lapangan, dan jadwal yang sesuai
                                sebelum melakukan booking.

                            </td>

                        </tr>


                        <tr>

                            <td>

                                <strong>
                                    Status Booking
                                </strong>

                                <br><br>

                                Pantau status booking melalui
                                menu <strong>Riwayat Booking</strong>.

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


                        <?php if (mysqli_num_rows($booking_result) > 0): ?>


                            <?php while ($booking = mysqli_fetch_assoc($booking_result)): ?>

                                <tr>

                                    <td>

                                        <?= e(
                                            $booking['kode_booking']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $booking['nama_lapangan']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            date(
                                                "d-m-Y",
                                                strtotime(
                                                    $booking['tanggal']
                                                )
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            substr(
                                                $booking['jam_mulai'],
                                                0,
                                                5
                                            )
                                        ) ?>

                                        -

                                        <?= e(
                                            substr(
                                                $booking['jam_selesai'],
                                                0,
                                                5
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            ucfirst(
                                                $booking['status']
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            date(
                                                "d-m-Y H:i",
                                                strtotime(
                                                    $booking['created_at']
                                                )
                                            )
                                        ) ?>

                                    </td>

                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align:center;"
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


<?php

mysqli_stmt_close($stmt);

require_once __DIR__ . "/../includes/footer.php";

?>