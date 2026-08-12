<?php

require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/app.php";

if (is_logged_in()) {
    redirect($_SESSION['role'] === 'admin'
        ? "/booking-lapangan-sekolah/admin/index.php"
        : "/booking-lapangan-sekolah/profile.php");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Email atau password salah.";
        } elseif ($user['status'] !== 'aktif') {
            $error = "Akun sedang nonaktif.";
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                log_activity($conn, $user['id'], "Login", "Admin login ke sistem.");
                redirect("/booking-lapangan-sekolah/admin/index.php");
            }

            redirect("/booking-lapangan-sekolah/profile.php");
        }
    }
}

$page_title = "Login";
require_once __DIR__ . "/includes/header.php";
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="brand" style="padding:0 0 20px;border:0;">
            <div class="brand-mark">B</div>
            <div>
                <strong>Booking Lapangan</strong>
                <small>Login Sistem</small>
            </div>
        </div>

        <h1>Masuk ke Sistem</h1>
        <p>Silakan masukkan akun untuk melanjutkan.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" required value="<?= e(old('email')) ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>

            <button class="btn btn-primary" type="submit">Login</button>
        </form>

        <div class="auth-link">
            Belum punya akun?
            <a href="/booking-lapangan-sekolah/register.php">Daftar</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
