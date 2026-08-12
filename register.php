<?php
require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/app.php";

if (is_logged_in()) {
    redirect("/booking-lapangan-sekolah/");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $password = $_POST['password'] ?? "";
    $password_confirm = $_POST['password_confirm'] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $error = "Nama, email, dan password wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $password_confirm) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password, role, phone, status)
                 VALUES (?, ?, ?, 'user', ?, 'aktif')"
            );
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hash, $phone);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            flash("register", "Registrasi berhasil. Silakan login.");
            redirect("/booking-lapangan-sekolah/login.php");
        }
    }
}

$page_title = "Register";
require_once __DIR__ . "/includes/header.php";
?>

<div class="auth-page">
    <div class="auth-card">
        <h1>Buat Akun</h1>
        <p>Daftar sebagai pengguna sistem booking lapangan.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input class="form-control" type="text" name="name" required value="<?= e(old('name')) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" required value="<?= e(old('email')) ?>">
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input class="form-control" type="text" name="phone" value="<?= e(old('phone')) ?>">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input class="form-control" type="password" name="password_confirm" required>
                </div>
            </div>

            <button class="btn btn-primary" type="submit">Daftar</button>
        </form>

        <div class="auth-link">
            Sudah punya akun?
            <a href="/booking-lapangan-sekolah/login.php">Login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
