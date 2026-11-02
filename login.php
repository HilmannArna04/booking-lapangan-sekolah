<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/app.php";


/*
|--------------------------------------------------------------------------
| CEK JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/

if (is_logged_in()) {

    if ($_SESSION['role'] === 'admin') {

        redirect("/booking-lapangan-sekolah/admin/index.php");
    } else {

        redirect("/booking-lapangan-sekolah/user/index.php");
    }
}


$error = "";


/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";


    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    if ($email === "" || $password === "") {

        $error = "Email dan password wajib diisi.";
    } else {


        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA USER
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role, status
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        /*
        |--------------------------------------------------------------------------
        | CEK EMAIL & PASSWORD
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            !password_verify($password, $user['password'])
        ) {

            $error = "Email atau password salah.";
        }

        /*
        |--------------------------------------------------------------------------
        | CEK STATUS AKUN
        |--------------------------------------------------------------------------
        */ elseif ($user['status'] !== 'aktif') {

            $error = "Akun sedang nonaktif.";
        } else {


            /*
            |--------------------------------------------------------------------------
            | BUAT SESSION LOGIN
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];


            /*
            |--------------------------------------------------------------------------
            | LOGIN ADMIN
            |--------------------------------------------------------------------------
            */

            if ($user['role'] === 'admin') {

                log_activity(
                    $conn,
                    $user['id'],
                    "Login",
                    "Admin login ke sistem."
                );

                redirect(
                    "/booking-lapangan-sekolah/admin/index.php"
                );
            }


            /*
            |--------------------------------------------------------------------------
            | LOGIN USER
            |--------------------------------------------------------------------------
            */

            redirect(
                "/booking-lapangan-sekolah/user/index.php"
            );
        }
    }
}


$page_title = "Login";

require_once __DIR__ . "/includes/header.php";

?>


<main class="auth-screen auth-screen-login">
    <section class="auth-panel" aria-labelledby="login-title">
        <a class="auth-brand" href="/booking-lapangan-sekolah/" aria-label="Booking Lapangan, kembali ke beranda">
            <span class="auth-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 48 48" role="img">
                    <circle cx="24" cy="24" r="21" fill="none" stroke="currentColor" stroke-width="2" />
                    <ellipse cx="24" cy="24" rx="9" ry="21" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <path d="M4 24h40M8 13h32M8 35h32" fill="none" stroke="currentColor" stroke-width="1.5" />
                </svg>
            </span>
            <span class="auth-brand-copy">
                <strong>SISTEM BOOKING</strong>
                <small>LAPANGAN SEKOLAH</small>
            </span>
        </a>

        <div class="auth-heading">
            <span class="auth-kicker">SELAMAT DATANG KEMBALI</span>
            <h1 id="login-title">Masuk ke Sistem</h1>
            <p>Login untuk melanjutkan booking lapangan sekolah.</p>
        </div>

        <?php show_flash(); ?>
        <?php if ($error): ?>
            <div class="alert alert-error auth-alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="" autocomplete="on">
            <div class="auth-field">
                <label for="login-email">Alamat email <span>*</span></label>
                <input id="login-email" class="form-control" type="email" name="email"
                    required autocomplete="email" placeholder="nama@email.com"
                    value="<?= e(old('email')) ?>">
            </div>
            <div class="auth-field">
                <label for="login-password">Password <span>*</span></label>
                <input id="login-password" class="form-control" type="password" name="password"
                    required autocomplete="current-password" placeholder="Masukkan password">
            </div>
            <button class="auth-submit" type="submit">Login <span aria-hidden="true">→</span></button>
        </form>

        <div class="auth-divider"><span>atau</span></div>
        <p class="auth-switch">Belum punya akun? <a href="/booking-lapangan-sekolah/register.php">Daftar sekarang</a></p>
        <a class="auth-back-link" href="/booking-lapangan-sekolah/">← Kembali ke beranda</a>
        <p class="auth-footnote">Sistem Booking Lapangan Sekolah</p>
    </section>
</main>

<?php

require_once __DIR__ . "/includes/footer.php";

?>