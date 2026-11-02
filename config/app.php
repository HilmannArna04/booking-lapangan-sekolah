<?php
// Gunakan WIB untuk seluruh tanggal dan waktu aplikasi.
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect("/booking-lapangan-sekolah/login.php");
    }
}

function require_admin() {
    require_login();

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit("Akses ditolak.");
    }
}

function require_user() {
    require_login();

    if ($_SESSION['role'] !== 'user') {
        http_response_code(403);
        exit("Akses ditolak.");
    }
}

function flash($key, $message, $type = "success") {
    $_SESSION['flash'] = [
        'key' => $key,
        'message' => $message,
        'type' => $type
    ];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = strtolower((string)($flash['type'] ?? 'success'));
        if ($type === 'danger') $type = 'error';
        $allowed = ['success', 'error', 'warning', 'info', 'question'];
        if (!in_array($type, $allowed, true)) $type = 'info';
        echo '<div id="app-flash-message" hidden data-type="' . e($type) . '" data-message="' . e($flash['message'] ?? '') . '"></div>';
    }
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function log_activity($conn, $user_id, $aktivitas, $deskripsi = null) {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO activity_logs (user_id, aktivitas, deskripsi) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $aktivitas, $deskripsi);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
