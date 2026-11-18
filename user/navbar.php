<?php
$active_menu = $active_menu ?? '';
?>

<aside class="sidebar">

    <!-- BRAND -->
    <div class="brand">

        <div class="brand-mark">
            B
        </div>

        <div>
            <strong>Booking Lapangan</strong>
            <small>User Panel</small>
        </div>

    </div>


    <!-- MENU -->
    <nav class="menu">

        <!-- DASHBOARD -->
        <a
            href="/booking-lapangan-sekolah/user/index.php"
            class="<?= $active_menu === 'dashboard' ? 'active' : '' ?>"
        >
            <span>▦</span>
            Dashboard
        </a>


        <!-- BOOKING -->
        <a
            href="/booking-lapangan-sekolah/user/booking/index.php"
            class="<?= $active_menu === 'booking' ? 'active' : '' ?>"
        >
            <span>▣</span>
            Booking Saya
        </a>


        <!-- JADWAL -->
        <a
            href="/booking-lapangan-sekolah/user/jadwal/index.php"
            class="<?= $active_menu === 'jadwal' ? 'active' : '' ?>"
        >
            <span>◷</span>
            Jadwal Saya
        </a>


        <!-- RIWAYAT -->
        <a
            href="/booking-lapangan-sekolah/user/riwayat/index.php"
            class="<?= $active_menu === 'riwayat' ? 'active' : '' ?>"
        >
            <span>↻</span>
            Riwayat Booking
        </a>


        <!-- PROFIL -->
        <a
            href="/booking-lapangan-sekolah/profile.php"
            class="<?= $active_menu === 'profil' ? 'active' : '' ?>"
        >
            <span>♙</span>
            Profil
        </a>

    </nav>


    <!-- BOTTOM -->
    <div class="sidebar-bottom">

        <a href="/booking-lapangan-sekolah/profile.php">
            Profil Saya
        </a>

        <a href="/booking-lapangan-sekolah/logout.php">
            Logout
        </a>

    </div>

</aside>