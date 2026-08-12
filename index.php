<?php
require_once __DIR__ . "/config/app.php";

if (is_logged_in()) {
    if ($_SESSION['role'] === 'admin') {
        redirect("/booking-lapangan-sekolah/admin/index.php");
    }
    redirect("/booking-lapangan-sekolah/profile.php");
}

redirect("/booking-lapangan-sekolah/login.php");
