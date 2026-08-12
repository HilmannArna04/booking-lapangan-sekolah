<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT id,name,email,phone,status FROM users WHERE id = ? AND role = 'user'");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) exit("Pengguna tidak ditemukan.");

$page_title = "Edit Pengguna";
$active_menu = "pengguna";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $status = $_POST['status'] ?? "aktif";
    $new_password = $_POST['password'] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Nama dan email harus valid.";
    } else {
        if ($new_password !== "") {
            if (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter.";
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=?, status=?, password=? WHERE id=? AND role='user'");
                mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $status, $hash, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=?, status=? WHERE id=? AND role='user'");
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $status, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($error === "") {
            log_activity($conn, $_SESSION['user_id'], "Edit Pengguna", "Mengubah pengguna ID $id.");
            flash("pengguna", "Data pengguna berhasil diperbarui.");
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
        <div class="page-heading"><div><h2>Edit Pengguna</h2><p>Perbarui data pengguna.</p></div></div>
        <?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nama</label>
                    <input class="form-control" name="name" required value="<?= e($data['name']) ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" type="email" name="email" required value="<?= e($data['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input class="form-control" name="phone" value="<?= e($data['phone']) ?>">
                </div>
                <div class="form-group">
                    <label>Password Baru <small>(kosongkan jika tidak diubah)</small></label>
                    <input class="form-control" type="password" name="password" minlength="6">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="aktif" <?= $data['status']==='aktif'?'selected':'' ?>>Aktif</option>
                        <option value="nonaktif" <?= $data['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Simpan Perubahan</button>
                    <a class="btn btn-secondary" href="index.php">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
