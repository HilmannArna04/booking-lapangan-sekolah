<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/app.php";

require_login();

$error = "";


// =====================================================
// AMBIL DATA USER
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, phone, role, status
     FROM users
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$user) {
    exit("Data pengguna tidak ditemukan.");
}


// =====================================================
// UPDATE PROFILE
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? "");
    $phone = trim($_POST['phone'] ?? "");


    if ($name === "") {

        $error = "Nama wajib diisi.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET name = ?, phone = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $name,
            $phone,
            $_SESSION['user_id']
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        // Update nama di session
        $_SESSION['name'] = $name;


        flash(
            "profile",
            "Profil berhasil diperbarui."
        );


        redirect(
            "/booking-lapangan-sekolah/profile.php"
        );
    }
}


$page_title = "Profil Saya";


// Menu aktif
$active_menu = "profil";


require_once __DIR__ . "/includes/header.php";

?>


<div class="main">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <?php if ($_SESSION['role'] === 'admin'): ?>

        <?php
        require __DIR__ . "/includes/navbar.php";
        ?>

    <?php else: ?>

        <?php
        require __DIR__ . "/user/navbar.php";
        ?>

    <?php endif; ?>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <main class="content">


        <?php show_flash(); ?>


        <!-- PAGE HEADING -->

        <div class="page-heading">

            <div>

                <h2>Profil Saya</h2>

                <p>
                    Kelola informasi akun Anda.
                </p>

            </div>

        </div>


        <!-- ERROR -->

        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- PROFILE CARD -->

        <div class="form-card profile-card">


            <form method="POST">


                <!-- NAMA -->

                <div class="form-group">

                    <label for="name">
                        Nama
                    </label>

                    <input
                        id="name"
                        class="form-control"
                        type="text"
                        name="name"
                        required
                        value="<?= e($user['name']) ?>"
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        id="email"
                        class="form-control"
                        type="email"
                        value="<?= e($user['email']) ?>"
                        disabled
                    >

                    <small>
                        Email tidak dapat diubah.
                    </small>

                </div>


                <!-- NOMOR TELEPON -->

                <div class="form-group">

                    <label for="phone">
                        Nomor Telepon
                    </label>

                    <input
                        id="phone"
                        class="form-control"
                        type="text"
                        name="phone"
                        value="<?= e($user['phone'] ?? '') ?>"
                    >

                </div>


                <!-- ROLE -->

                <div class="form-group">

                    <label for="role">
                        Role
                    </label>

                    <input
                        id="role"
                        class="form-control"
                        type="text"
                        value="<?= e(ucfirst($user['role'])) ?>"
                        disabled
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status Akun
                    </label>

                    <input
                        id="status"
                        class="form-control"
                        type="text"
                        value="<?= e(ucfirst($user['status'])) ?>"
                        disabled
                    >

                </div>


                <!-- BUTTON -->

                <div class="form-actions">


                    <button
                        class="btn btn-primary"
                        type="submit"
                    >
                        Simpan Perubahan
                    </button>


                    <?php if ($_SESSION['role'] === 'admin'): ?>

                        <a
                            class="btn btn-secondary"
                            href="/booking-lapangan-sekolah/admin/index.php"
                        >
                            Kembali
                        </a>

                    <?php else: ?>

                        <a
                            class="btn btn-secondary"
                            href="/booking-lapangan-sekolah/user/index.php"
                        >
                            Kembali
                        </a>

                    <?php endif; ?>


                </div>


            </form>


        </div>


    </main>

</div>


<?php require_once __DIR__ . "/includes/footer.php"; ?>