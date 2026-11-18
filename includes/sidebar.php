<?php
// includes/sidebar.php
// Sidebar unified — admin & user, menggunakan class dari style.css yang sudah ada.

$_sidebar_role   = $_SESSION['role']  ?? 'user';
$_sidebar_active = $active_menu       ?? '';

if (!function_exists('_sidebar_base')) {
    function _sidebar_base(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        foreach (['/admin/', '/user/'] as $seg) {
            $pos = strpos($script, $seg);
            if ($pos !== false) return substr($script, 0, $pos);
        }
        return rtrim(dirname($script), '/');
    }
}
$_base = _sidebar_base();
?>

<aside class="sidebar">

    <!-- ===== Brand ===== -->
    <div class="brand">
        <div class="brand-mark">
            <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" width="22" height="22">
                <circle cx="18" cy="18" r="17" stroke="white" stroke-width="2" />
                <path d="M5 18h26M18 5v26" stroke="white" stroke-width="2" />
                <path d="M8 8.5C11 12 11 24 8 27.5" stroke="white" stroke-width="1.5" fill="none" />
                <path d="M28 8.5C25 12 25 24 28 27.5" stroke="white" stroke-width="1.5" fill="none" />
            </svg>
        </div>
        <div>
            <strong>SISTEM BOOKING</strong>
            <small>LAPANGAN SEKOLAH</small>
        </div>
    </div>

    <!-- ===== Navigation ===== -->
    <nav class="menu">
        <?php if ($_sidebar_role === 'admin'): ?>

            <a class="<?= $_sidebar_active === 'dashboard' ? 'active' : '' ?>" href="<?= $_base ?>/admin/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                Dashboard
            </a>

            <a class="<?= $_sidebar_active === 'lapangan' ? 'active' : '' ?>" href="<?= $_base ?>/admin/lapangan/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <path d="M3 9h18M3 15h18M9 3v18M15 3v18" stroke-width="1.5" />
                </svg>
                Data Lapangan
            </a>

            <a class="<?= $_sidebar_active === 'jadwal' ? 'active' : '' ?>" href="<?= $_base ?>/admin/jadwal/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Jadwal Lapangan
            </a>

            <a class="<?= $_sidebar_active === 'booking' ? 'active' : '' ?>" href="<?= $_base ?>/admin/booking/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M9 10h6M9 14h4" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Data Booking
            </a>

            <a class="<?= $_sidebar_active === 'laporan' ? 'active' : '' ?>" href="<?= $_base ?>/admin/laporan/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10" />
                    <line x1="12" y1="20" x2="12" y2="4" />
                    <line x1="6" y1="20" x2="6" y2="14" />
                </svg>
                Laporan &amp; Statistik
            </a>

            <a class="<?= $_sidebar_active === 'pengguna' ? 'active' : '' ?>" href="<?= $_base ?>/admin/pengguna/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                </svg>
                Data Pengguna
            </a>

            <a class="<?= $_sidebar_active === 'aktivitas' ? 'active' : '' ?>" href="<?= $_base ?>/admin/activity/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                Aktivitas Admin
            </a>

        <?php else: ?>

            <!-- ---- Menu USER ---- -->
            <a class="<?= $_sidebar_active === 'dashboard' ? 'active' : '' ?>" href="<?= $_base ?>/user/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                Dashboard
            </a>

            <a class="<?= $_sidebar_active === 'jadwal' ? 'active' : '' ?>" href="<?= $_base ?>/user/jadwal/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Jadwal Lapangan
            </a>

            <a class="<?= $_sidebar_active === 'booking' ? 'active' : '' ?>" href="<?= $_base ?>/user/booking/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M9 10h6M9 14h4" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Booking Saya
            </a>

            <a class="<?= $_sidebar_active === 'riwayat' ? 'active' : '' ?>" href="<?= $_base ?>/user/riwayat/index.php">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 .49-4.95" />
                </svg>
                Riwayat Booking
            </a>

        <?php endif; ?>
    </nav>

    <!-- ===== Bottom: Profil & Logout ===== -->
    <div class="sidebar-bottom">
        <a class="<?= $_sidebar_active === 'profil' ? 'active' : '' ?>" href="<?= $_base ?>/profile.php">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            Profil Saya
        </a>

        <a href="<?= $_base ?>/logout.php">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            Logout
        </a>
    </div>

</aside>

<?php if ($_sidebar_role === 'user' && empty($_SESSION['phone'])): ?>
    <div class="alert alert-warning" style="display:flex; align-items:center; gap:10px; margin:1rem; border-radius:8px; padding:0.75rem 1rem;">
        <span style="font-size:1.2rem; flex-shrink:0;">⚠️</span>
        <span style="flex:1;">
            <strong>Nomor telepon belum diisi.</strong>
            Admin perlu kontakmu terkait booking.
        </span>
        <a href="<?= $_base ?>/profile.php" style="white-space:nowrap; font-weight:600; text-decoration:underline;">
            Isi Sekarang →
        </a>
    </div>
<?php endif; ?>