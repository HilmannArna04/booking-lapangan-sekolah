<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "db_booking_lapangan";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Terjadi kesalahan sistem. Silakan coba lagi.");
}

mysqli_set_charset($conn, "utf8mb4");
