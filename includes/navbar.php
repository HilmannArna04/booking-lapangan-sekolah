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
