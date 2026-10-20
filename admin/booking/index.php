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


require_once __DIR__ . "/../../includes/header.php";

?>

    <?php require __DIR__ . "/../../includes/navbar.php"; ?>

    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>


    <main class="content booking-management-page">

        <?php show_flash(); ?>


        <div class="page-heading">

            <div>

                <h2>Kelola Booking</h2>

                <p>
                    Kelola konfirmasi dan status booking lapangan.
                </p>

            </div>

        </div>


        <div class="table-card">


            <!-- FILTER -->

            <div class="table-header">

                <form
                    method="GET"
                    class="filters">

                    <input
                        type="text"
                        name="search"
                        placeholder="Cari kode, pengguna, lapangan..."
                        value="<?= e($search) ?>">


                    <select name="status">

                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="menunggu"
                            <?= $status === 'menunggu' ? 'selected' : '' ?>>
                            Menunggu
                        </option>

                        <option
                            value="dikonfirmasi"
                            <?= $status === 'dikonfirmasi' ? 'selected' : '' ?>>
                            Dikonfirmasi
                        </option>

                        <option
                            value="ditolak"
                            <?= $status === 'ditolak' ? 'selected' : '' ?>>
                            Ditolak
                        </option>

                        <option
                            value="selesai"
                            <?= $status === 'selesai' ? 'selected' : '' ?>>
                            Selesai
                        </option>

                    </select>


                    <input
                        type="date"
                        name="tanggal"
                        value="<?= e($tanggal) ?>">


                    <button
                        class="btn btn-secondary"
                        type="submit">
                        Filter
                    </button>

                </form>

            </div>


            <!-- TABLE -->

            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Kode</th>
                            <th>Pemesan</th>
                            <th>Lapangan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php $no = 1; ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td>
                                    <?= $no++ ?>
                                </td>


                                <td>

                                    <strong>
                                        <?= e($row['kode_booking']) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= e($row['nama_user']) ?>

                                    <br>

                                    <small>
                                        <?= e($row['email_user']) ?>
                                    </small>

                                </td>


                                <td>

                                    <?= e($row['nama_lapangan']) ?>

                                </td>


                                <td>

                                    <?= date(
                                        'd-m-Y',
                                        strtotime($row['tanggal'])
                                    ) ?>

                                </td>


                                <td>

                                    <?= e($row['jam_mulai']) ?>

                                    -

                                    <?= e($row['jam_selesai']) ?>

                                </td>


                                <td>

                                    <span class="booking-status status-<?= e($row['booking_status']) ?>">

                                        <?= e(
                                            ucfirst(
                                                $row['booking_status']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <div class="actions">

                                        <a
                                            class="btn btn-secondary"
                                            href="detail.php?id=<?= $row['id'] ?>">
                                            Detail
                                        </a>


                                        <?php if ($row['booking_status'] === 'menunggu'): ?>

                                            <form
                                                method="POST"
                                                action="update_status.php"
                                                style="display:inline;">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $row['id'] ?>">

                                                <input
                                                    type="hidden"
                                                    name="status"
                                                    value="dikonfirmasi">

                                                <button
                                                    class="btn btn-primary"
                                                    type="submit"
                                                    onclick="return confirm('Konfirmasi booking ini?')">
                                                    Konfirmasi
                                                </button>

                                            </form>


                                            <form
                                                method="POST"
                                                action="update_status.php"
                                                style="display:inline;">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $row['id'] ?>">

                                                <input
                                                    type="hidden"
                                                    name="status"
                                                    value="ditolak">

                                                <button
                                                    class="btn btn-secondary"
                                                    type="submit"
                                                    onclick="return confirm('Tolak booking ini?')">
                                                    Tolak
                                                </button>

                                            </form>

                                        <?php elseif ($row['booking_status'] === 'dikonfirmasi'): ?>

                                            <form
                                                method="POST"
                                                action="update_status.php">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $row['id'] ?>">

                                                <input
                                                    type="hidden"
                                                    name="status"
                                                    value="selesai">

                                                <button
                                                    class="btn btn-primary"
                                                    type="submit"
                                                    onclick="return confirm('Tandai booking sebagai selesai?')">
                                                    Selesai
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                        <?php if (mysqli_num_rows($result) === 0): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="empty">
                                    Belum ada data booking.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>


<?php

mysqli_stmt_close($stmt);

require_once __DIR__ . "/../../includes/footer.php";

?>