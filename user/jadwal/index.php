<?php

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

require_user();

$page_title = "Jadwal Saya";
$active_menu = "jadwal";


$user_id = $_SESSION['user_id'];


$stmt = mysqli_prepare(
    $conn,

    "SELECT
        b.id AS booking_id,
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

     AND b.status IN (
        'menunggu',
        'dikonfirmasi'
     )

     ORDER BY
        j.tanggal ASC,
        j.jam_mulai ASC"
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


require_once __DIR__ . "/../../includes/header.php";

?>


<div class="main">

    <?php require __DIR__ . "/../navbar.php"; ?>

    <main class="content">

        <div class="page-heading">

            <div>

                <h2>Jadwal Saya</h2>

                <p>
                    Daftar jadwal booking yang sedang berjalan.
                </p>

            </div>

        </div>


        <div class="table-card">

            <div class="table-header">

                <h3>Jadwal Booking</h3>

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

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (mysqli_num_rows($result) > 0): ?>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <tr>

                                    <td>
                                        <?= e($row['kode_booking']) ?>
                                    </td>

                                    <td>
                                        <?= e($row['nama_lapangan']) ?>
                                    </td>

                                    <td>
                                        <?= e(
                                            date(
                                                "d-m-Y",
                                                strtotime($row['tanggal'])
                                            )
                                        ) ?>
                                    </td>

                                    <td>

                                        <?= e(
                                            substr(
                                                $row['jam_mulai'],
                                                0,
                                                5
                                            )
                                        ) ?>

                                        -

                                        <?= e(
                                            substr(
                                                $row['jam_selesai'],
                                                0,
                                                5
                                            )
                                        ) ?>

                                    </td>

                                    <td>

                                        <span class="badge">

                                            <?= e(
                                                ucfirst(
                                                    $row['status']
                                                )
                                            ) ?>

                                        </span>

                                    </td>

                                    <td>
                                        <?php if ($row['status'] === 'menunggu'): ?>
                                            <form method="POST" action="batal.php" onsubmit="return confirm('Yakin ingin membatalkan booking ini?');">
                                                <input type="hidden" name="booking_id" value="<?= (int) $row['booking_id'] ?>">
                                                <button type="submit" class="btn btn-danger">Batalkan</button>
                                            </form>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align:center;"
                                >

                                    Belum ada jadwal booking.

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

require_once __DIR__ . "/../../includes/footer.php";

?>