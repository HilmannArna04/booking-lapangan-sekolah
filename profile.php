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

    } elseif ($phone !== "" && (!preg_match('/^[0-9]+$/', $phone) || strlen($phone) < 10 || strlen($phone) > 13)) {

        $error = "Nomor telepon tidak valid. Masukkan 10–13 digit angka.";
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


        // Update nama & phone di session
        $_SESSION['name'] = $name;
        $_SESSION['phone'] = $phone;


        flash(
            "profile",
            "Profil berhasil diperbarui."
        );


        redirect(
            "/profile.php"
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
        require __DIR__ . "/includes/sidebar.php";
        ?>

    <?php else: ?>

        <?php
        require __DIR__ . "/includes/sidebar.php";
        ?>

    <?php endif; ?>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <main class="content profile-page">


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
            <div class="profile-card-heading">
                <span class="profile-avatar">
                    <?= e(strtoupper(substr($user['name'], 0, 1))) ?>
                </span>
                <h3>Informasi Akun</h3>
            </div>


            <form method="POST" class="profile-form">


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
                        value="<?= e($user['name']) ?>">

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
                        disabled>

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
                        pattern="[0-9]{10,13}"
                        minlength="10"
                        maxlength="13"
                        placeholder="Contoh: 08123456789"
                        value="<?= e($user['phone'] ?? '') ?>">

                    <small>Masukkan 10–13 digit angka. Contoh: 08123456789</small>

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
                        disabled>

                </div>


                <!-- STATUS -->
                <div class="form-group">
                    <label>Status Akun</label>

                    <span class="profile-status <?= strtolower($user['status']) === 'aktif' ? 'status-active' : 'status-inactive' ?>">
                        <span class="profile-status-dot"></span>
                        <?= e(ucfirst($user['status'])) ?>
                    </span>
                </div>


                <!-- BUTTON -->

                <div class="form-actions">


                    <button
                        class="btn btn-primary"
                        type="submit">
                        Simpan Perubahan
                    </button>


                    <?php if ($_SESSION['role'] === 'admin'): ?>

                        <a
                            class="btn btn-secondary"
                            href="/admin/index.php">
                            Kembali
                        </a>

                    <?php else: ?>

                        <a
                            class="btn btn-secondary"
                            href="/user/index.php">
                            Kembali
                        </a>

                    <?php endif; ?>


                </div>


            </form>


        </div>


    </main>

</div>


<?php require_once __DIR__ . "/includes/footer.php"; ?>