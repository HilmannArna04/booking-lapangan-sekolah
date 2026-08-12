<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">B</div>
        <div>
            <strong>Booking Lapangan</strong>
            <small>Admin Panel</small>
        </div>
    </div>

    <nav class="menu">
        <a href="/booking-lapangan-sekolah/admin/index.php" class="<?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>">
            <span>▦</span> Dashboard
        </a>
        <a href="/booking-lapangan-sekolah/admin/lapangan/index.php" class="<?= ($active_menu ?? '') === 'lapangan' ? 'active' : '' ?>">
            <span>▣</span> Data Lapangan
        </a>
        <a href="/booking-lapangan-sekolah/admin/jadwal/index.php" class="<?= ($active_menu ?? '') === 'jadwal' ? 'active' : '' ?>">
            <span>◷</span> Data Jadwal
        </a>
        <a href="/booking-lapangan-sekolah/admin/pengguna/index.php" class="<?= ($active_menu ?? '') === 'pengguna' ? 'active' : '' ?>">
            <span>◉</span> Data Pengguna
        </a>
        <a href="/booking-lapangan-sekolah/admin/activity/index.php" class="<?= ($active_menu ?? '') === 'activity' ? 'active' : '' ?>">
            <span>≡</span> Aktivitas Admin
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="/booking-lapangan-sekolah/profile.php">Profil Saya</a>
        <a href="/booking-lapangan-sekolah/logout.php">Logout</a>
    </div>
</aside>
