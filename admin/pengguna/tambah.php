<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$page_title = "Tambah Pengguna";
$active_menu = "pengguna";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($name === "" || $email === "" || strlen($password) < 6) {
        $error = "Nama, email, dan password minimal 6 karakter wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = "Email sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name,email,password,role,phone,status) VALUES (?,?,?,'user',?,'aktif')");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hash, $phone);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            log_activity($conn, $_SESSION['user_id'], "Tambah Pengguna", "Menambahkan pengguna $name.");
            flash("pengguna", "Pengguna berhasil ditambahkan.");
            redirect("index.php");
        }
    }
}

require_once __DIR__ . "/../../includes/header.php";
?>

<div class="main">
    <?php require __DIR__ . "/../../includes/navbar.php"; ?>
    <?php require __DIR__ . "/../../includes/sidebar.php"; ?>
    <main class="content">
        <div class="page-heading"><div><h2>Tambah Pengguna</h2><p>Tambahkan akun pengguna baru.</p></div></div>
        <?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nama</label>
                    <input class="form-control" name="name" required value="<?= e(old('name')) ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email" name="email" required value="<?= e(old('email')) ?>">
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input class="form-control" name="phone" value="<?= e(old('phone')) ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" required minlength="6">
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Simpan</button>
                    <a class="btn btn-secondary" href="index.php">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
