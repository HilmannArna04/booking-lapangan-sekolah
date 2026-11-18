<header class="topbar">
    <div>
        <h1><?= e($page_title ?? "Dashboard") ?></h1>
        <p>Sistem Booking Lapangan</p>
    </div>
    <div class="topbar-user">
        <span><?= e($_SESSION['name'] ?? 'Pengunjung') ?></span>
        <?php if (is_logged_in()): ?>
            <span class="role-badge"><?= e(ucfirst($_SESSION['role'])) ?></span>
        <?php endif; ?>
    </div>
</header>

<?php if ($_SESSION['role'] === 'user'): ?>

<nav class="navbar">

    <div class="navbar-brand">
        Booking Lapangan
    </div>

    <div class="navbar-menu">

        <a
            href="/../index.php"
            class="<?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <a
            href="/../booking/index.php"
            class="<?= ($active_menu ?? '') === 'booking' ? 'active' : '' ?>"
        >
            Booking Lapangan
        </a>

        <a
            href="/../profile.php"
            class="<?= ($active_menu ?? '') === 'profile' ? 'active' : '' ?>"
        >
            Profil
        </a>

        <a
            href="/../logout.php"
        >
            Logout
        </a>

    </div>

</nav>

<?php endif; ?> 
