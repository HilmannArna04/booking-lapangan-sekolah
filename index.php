<?php
require_once __DIR__ . "/config/app.php";

if (is_logged_in()) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        redirect("/booking-lapangan-sekolah/admin/index.php");
    }
    redirect("/booking-lapangan-sekolah/profile.php");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Lapangan Sekolah</title>
    <link rel="stylesheet" href="/booking-lapangan-sekolah/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
</head>

<body class="landing-page">
    <div class="landing-scroll-progress" id="landingScrollProgress" aria-hidden="true"></div>
    <header class="landing-nav">
        <a class="landing-brand" href="#beranda" aria-label="Sistem Booking Lapangan Sekolah">
            <span class="landing-brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40">
                    <circle cx="20" cy="20" r="18" fill="currentColor" />
                    <path d="M5 15h30M5 25h30M20 2c-7 7-7 29 0 36M20 2c7 7 7 29 0 36" fill="none" stroke="#fff" stroke-width="1.4" />
                </svg></span>
            <span class="landing-brand-copy"><strong>SISTEM BOOKING</strong><small>LAPANGAN SEKOLAH</small></span>
        </a>
        <button class="landing-menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false" id="landingMenuToggle">☰</button>
        <nav class="landing-links" id="landingLinks" aria-label="Navigasi utama">
            <a class="active" href="#beranda">Beranda</a><a href="#lapangan">Lapangan</a><a href="#jadwal">Jadwal</a><a href="#tentang">Tentang</a>
            <div class="landing-nav-actions"><a class="landing-login" href="login.php">Login</a><a class="landing-register" href="register.php">Daftar</a></div>
        </nav>
    </header>

    <main>
        <section class="landing-hero landing-container" id="beranda">
            <div class="landing-hero-copy">
                <span class="landing-eyebrow">SISTEM BOOKING LAPANGAN SEKOLAH</span>
                <h1>Booking Lapangan<br><span>Lebih Mudah</span></h1>
                <p>Temukan lapangan basket dan futsal sekolah, periksa jadwal yang tersedia, lalu ajukan pemesanan secara online.</p>
                <div class="landing-hero-actions"><a class="landing-primary-btn" href="login.php">Booking Sekarang <span aria-hidden="true">→</span></a><a class="landing-secondary-btn" href="#jadwal">Lihat Jadwal</a></div>
            </div>
            <div class="landing-hero-image-wrap">
                <img class="landing-hero-image" src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1400&q=85" alt="Lapangan basket dengan ring basket">
                <div class="landing-image-label"><span class="landing-label-icon" aria-hidden="true">▦</span><span><strong>Atur jadwal dengan mudah</strong><small>Pilih lapangan dan waktu yang sesuai</small></span></div>
            </div>
        </section>

        <section class="landing-benefit-section" aria-labelledby="benefitTitle">
            <div class="landing-container">
                <div class="landing-section-intro">
                    <span class="landing-section-kicker">LEBIH MUDAH, LEBIH TERATUR</span>
                    <h2 id="benefitTitle">Lebih Praktis untuk Mengatur<br class="desktop-break"> Jadwal Lapangan</h2>
                </div>
                <div class="landing-benefits">
                    <article class="landing-benefit"><span class="landing-benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                <rect x="4" y="5" width="16" height="15" rx="2" />
                                <path d="M8 3v4m8-4v4M7 11h10M8 15l2 2 5-5" />
                            </svg></span>
                        <div>
                            <h3>Jadwal Lebih Jelas</h3>
                            <p>Periksa jadwal dan status lapangan sebelum memilih waktu.</p>
                        </div>
                    </article>
                    <article class="landing-benefit"><span class="landing-benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                <path d="M12 3 20 7v5c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7z" />
                                <path d="m8 12 2.5 2.5L16 9" />
                            </svg></span>
                        <div>
                            <h3>Pemesanan Terarah</h3>
                            <p>Ajukan booking melalui alur yang mudah dipahami.</p>
                        </div>
                    </article>
                    <article class="landing-benefit"><span class="landing-benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5z" />
                                <path d="M4 6h13M8 10h8M8 14h6" />
                            </svg></span>
                        <div>
                            <h3>Informasi Terpusat</h3>
                            <p>Informasi lapangan dan riwayat pemesanan dalam satu sistem.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="landing-courts-section" id="lapangan">
            <div class="landing-container">
                <div class="landing-section-heading">
                    <div><span class="landing-section-kicker">FASILITAS OLAHRAGA</span>
                        <h2>Pilih Lapangan yang Kamu Butuhkan</h2>
                        <p>Lihat informasi lapangan sebelum menentukan jadwal pemesanan.</p>
                    </div><a class="landing-outline-btn" href="login.php">Lihat Semua Lapangan <span aria-hidden="true">→</span></a>
                </div>
                <div class="landing-court-grid">
                    <article class="landing-court-card"><img src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=85" alt="Contoh lapangan basket" loading="lazy">
                        <div class="landing-court-info"><span class="landing-court-type">BASKET</span>
                            <h3>Lapangan Basket</h3>
                            <p>Lapangan basket untuk kegiatan olahraga sekolah.</p><a href="login.php">Lihat Detail <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                    <article class="landing-court-card"><img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=85" alt="Contoh lapangan olahraga" loading="lazy">
                        <div class="landing-court-info"><span class="landing-court-type">FUTSAL</span>
                            <h3>Lapangan Futsal</h3>
                            <p>Lapangan untuk bermain dan berolahraga bersama tim.</p><a href="login.php">Lihat Detail <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                </div>
                <p class="landing-demo-note">Foto dan nama di atas merupakan contoh tampilan. Daftar lapangan sebenarnya dapat dilihat setelah masuk ke sistem.</p>
            </div>
        </section>

        <section class="landing-schedule-section" id="jadwal">
            <div class="landing-container landing-schedule-layout">
                <div class="landing-schedule-copy"><span class="landing-section-kicker">CEK KETERSEDIAAN</span>
                    <h2>Periksa Jadwal Sebelum Booking</h2>
                    <p>Pilih tanggal dan lapangan untuk memeriksa slot yang tersedia. Masuk ke sistem untuk melihat jadwal yang tersimpan dan mengajukan booking.</p><a class="landing-primary-btn" href="login.php">Lihat Jadwal <span aria-hidden="true">→</span></a>
                </div>
                <div class="landing-schedule-preview">
                    <div class="landing-preview-head">
                        <div><strong>Jadwal Lapangan</strong><small>Pratinjau status jadwal</small></div><span class="landing-calendar-icon" aria-hidden="true">▦</span>
                    </div>
                    <div class="landing-preview-row"><span class="landing-preview-time">07.00 – 08.00</span><span class="landing-status status-available"><i></i>Tersedia</span></div>
                    <div class="landing-preview-row"><span class="landing-preview-time">08.00 – 09.00</span><span class="landing-status status-booked"><i></i>Terbooking</span></div>
                    <div class="landing-preview-row"><span class="landing-preview-time">09.00 – 10.00</span><span class="landing-status status-unavailable"><i></i>Tidak tersedia</span></div>
                    <p class="landing-preview-note">Contoh status untuk menjelaskan tampilan. Jadwal aktual ditampilkan setelah masuk.</p>
                </div>
            </div>
        </section>

        <section class="landing-steps-section">
            <div class="landing-container">
                <div class="landing-section-intro"><span class="landing-section-kicker">MULAI DALAM BEBERAPA LANGKAH</span>
                    <h2>Booking dalam Tiga Langkah</h2>
                    <p>Alur sederhana agar proses pemesanan lebih mudah diikuti.</p>
                </div>
                <div class="landing-steps">
                    <article class="landing-step"><span class="landing-step-number">01</span>
                        <div>
                            <h3>Login atau Daftar</h3>
                            <p>Gunakan akun untuk mengajukan pemesanan lapangan.</p>
                        </div>
                    </article>
                    <article class="landing-step"><span class="landing-step-number">02</span>
                        <div>
                            <h3>Pilih Jadwal</h3>
                            <p>Tentukan lapangan, tanggal, dan waktu yang masih tersedia.</p>
                        </div>
                    </article>
                    <article class="landing-step"><span class="landing-step-number">03</span>
                        <div>
                            <h3>Pantau Status</h3>
                            <p>Periksa perkembangan booking melalui akunmu.</p>
                        </div>
                    </article>
                </div>
                <div class="landing-steps-action"><a class="landing-primary-btn" href="login.php">Mulai Booking <span aria-hidden="true">→</span></a></div>
            </div>
        </section>

        <section class="landing-about-section" id="tentang">
            <div class="landing-container landing-about-layout">
                <div><span class="landing-section-kicker">TENTANG SISTEM</span>
                    <h2>Membantu Pengelolaan Lapangan Sekolah</h2>
                </div>
                <div class="landing-about-copy">
                    <p>Sistem Booking Lapangan Sekolah merupakan website yang dirancang untuk membantu proses pemesanan dan pengelolaan jadwal lapangan basket dan futsal. Melalui sistem ini, pengguna dapat melihat informasi lapangan, memeriksa ketersediaan jadwal, serta memantau status pemesanan.</p>
                    <p>Sistem juga membantu administrator mengelola data lapangan, jadwal, booking, dan laporan secara lebih terstruktur.</p><a class="landing-text-link" href="login.php">Masuk ke sistem <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-container landing-footer-main">
            <div class="landing-footer-about"><a class="landing-brand landing-brand-footer" href="#beranda"><span class="landing-brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40">
                            <circle cx="20" cy="20" r="18" fill="currentColor" />
                            <path d="M5 15h30M5 25h30M20 2c-7 7-7 29 0 36M20 2c7 7 7 29 0 36" fill="none" stroke="#fff" stroke-width="1.4" />
                        </svg></span><span class="landing-brand-copy"><strong>SISTEM BOOKING</strong><small>LAPANGAN SEKOLAH</small></span></a>
                <p>Membantu proses pemesanan dan pengelolaan jadwal lapangan basket dan futsal sekolah.</p>
            </div>
            <div class="landing-footer-links">
                <div><strong>Navigasi</strong><a href="#beranda">Beranda</a><a href="#lapangan">Lapangan</a><a href="#jadwal">Jadwal</a><a href="#tentang">Tentang</a></div>
                <div><strong>Akun</strong><a href="login.php">Login</a><a href="register.php">Daftar</a></div>
            </div>
        </div>
        <div class="landing-container landing-footer-bottom"><span>© <?= date('Y') ?> Sistem Booking Lapangan Sekolah</span><span>Dibuat untuk memudahkan pemesanan lapangan sekolah.</span></div>
    </footer>

    <script>
        const menuToggle = document.getElementById('landingMenuToggle');
        const navLinks = document.getElementById('landingLinks');
        const progressBar = document.getElementById('landingScrollProgress');
        const navAnchors = Array.from(navLinks.querySelectorAll('a[href^="#"]'));
        const pageSections = Array.from(document.querySelectorAll('main section[id]'));

        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function() {
                const open = navLinks.classList.toggle('is-open');
                menuToggle.setAttribute('aria-expanded', String(open));
                menuToggle.textContent = open ? '×' : '☰';
                menuToggle.classList.toggle('is-active', open);
            });

            navLinks.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    navLinks.classList.remove('is-open');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.textContent = '☰';
                    menuToggle.classList.remove('is-active');
                });
            });
        }

        /* Smoothly move to sections when an in-page navigation item is clicked. */
        document.querySelectorAll('a[href^="#"]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                const targetId = link.getAttribute('href');
                if (!targetId || targetId === '#') return;
                const target = document.querySelector(targetId);
                if (!target) return;
                event.preventDefault();
                target.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    block: 'start'
                });
                if (history.replaceState) history.replaceState(null, '', targetId);
            });
        });

        /* Scroll reveal: elements animate in when they enter the viewport. */
        const revealTargets = document.querySelectorAll(
            '.landing-hero-copy, .landing-hero-image-wrap, .landing-section-intro, ' +
            '.landing-benefit, .landing-section-heading, .landing-court-card, ' +
            '.landing-schedule-copy, .landing-schedule-preview, .landing-step, ' +
            '.landing-about-layout > div, .landing-bottom-cta'
        );

        revealTargets.forEach(function(element, index) {
            element.classList.add('landing-reveal');
            if (element.matches('.landing-benefit, .landing-court-card, .landing-step')) {
                element.style.setProperty('--reveal-delay', (index % 3) * 90 + 'ms');
            }
        });

        if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const revealObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -35px 0px'
            });

            revealTargets.forEach(function(element) {
                revealObserver.observe(element);
            });
        } else {
            revealTargets.forEach(function(element) {
                element.classList.add('is-visible');
            });
        }

        /* Update active menu color based on the section currently being viewed. */
        if ('IntersectionObserver' in window && pageSections.length) {
            const sectionObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    navAnchors.forEach(function(anchor) {
                        const active = anchor.getAttribute('href') === '#' + entry.target.id;
                        anchor.classList.toggle('active', active);
                        if (active) anchor.setAttribute('aria-current', 'location');
                        else anchor.removeAttribute('aria-current');
                    });
                });
            }, {
                rootMargin: '-30% 0px -55% 0px',
                threshold: 0
            });
            pageSections.forEach(function(section) {
                sectionObserver.observe(section);
            });
        }

        /* Thin progress line at the top of the page. */
        function updateScrollProgress() {
            if (!progressBar) return;
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;
            const progress = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
            progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
        }
        window.addEventListener('scroll', updateScrollProgress, {
            passive: true
        });
        window.addEventListener('resize', updateScrollProgress);
        updateScrollProgress();

        /* Give buttons immediate press feedback, without changing their destinations. */
        document.querySelectorAll('.landing-primary-btn, .landing-secondary-btn, .landing-register, .landing-login, .landing-outline-btn').forEach(function(button) {
            button.addEventListener('pointerdown', function() {
                button.classList.add('is-pressed');
            });
            ['pointerup', 'pointercancel', 'pointerleave'].forEach(function(eventName) {
                button.addEventListener(eventName, function() {
                    button.classList.remove('is-pressed');
                });
            });
        });
    </script>
</body>

</html>