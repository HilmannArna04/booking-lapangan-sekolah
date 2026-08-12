<?php
require_once __DIR__ . "/config/koneksi.php";
require_once __DIR__ . "/config/app.php";
require_login();

$error = "";

$stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, role, status FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    exit("Data pengguna tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $phone = trim($_POST['phone'] ?? "");

    if ($name === "") {
        $error = "Nama wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, phone = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['name'] = $name;
        flash("profile", "Profil berhasil diperbarui.");
        redirect("/booking-lapangan-sekolah/profile.php");
    }
}

$page_title = "Profil Saya";
require_once __DIR__ . "/includes/header.php";
?>

<?php if ($_SESSION['role'] === 'admin'): ?>
<div class="main">
    <?php $active_menu = ""; require __DIR__ . "/includes/navbar.php"; ?>
    <main class="content">
<?php else: ?>
<div class="auth-page" style="display:block;">
    <div class="content" style="margin:0 auto;">
<?php endif; ?>

        <?php show_flash(); ?>

        <div class="page-heading">
            <div>
                <h2>Profil Saya</h2>
                <p>Kelola informasi akun Anda.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-card profile-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nama</label>
                    <input class="form-control" type="text" name="name" required value="<?= e($user['name']) ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email" value="<?= e($user['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input class="form-control" type="text" name="phone" value="<?= e($user['phone']) ?>">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input class="form-control" type="text" value="<?= e(ucfirst($user['role'])) ?>" disabled>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                   <a class="btn btn-secondary"
   href="/booking-lapangan-sekolah/logout.php">
    Kembali
</a>
                </div>
            </form>
        </div>

<?php if ($_SESSION['role'] === 'admin'): ?>
    </main>
</div>
<?php else: ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
