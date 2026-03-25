<?php
// Function to determine active menu
function is_active($pages)
{
    $current = basename($_SERVER['PHP_SELF']);
    $path = $_SERVER['PHP_SELF'];

    if (is_array($pages)) {
        foreach ($pages as $page) {
            if ($current == $page || strpos($path, $page) !== false) {
                return 'active';
            }
        }
        return '';
    }

    // If it's a directory check (no extension usually passed in args like 'jalur')
    if (strpos($pages, '.') === false) {
        if (strpos($path, '/' . $pages . '/') !== false) {
            return 'active';
        }
    }

    return $current == $pages ? 'active' : '';
}

// Theme Detection
$admin_theme = get_setting('admin_theme', 'theme1');
if ($admin_theme === 'theme2') {
    echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/theme-modern.css">';
    echo '<style>
        @import url("https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap");
    </style>';
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.body.classList.add("theme-modern");
            if (!document.querySelector(".modern-bg-decor")) {
                const decor = document.createElement("div");
                decor.className = "modern-bg-decor";
                decor.innerHTML = `
                    <div style="position:fixed; top:10%; left:5%; opacity:0.1; font-size:4rem; z-index:-1; transform:rotate(-15deg)"><i class="fas fa-graduation-cap"></i></div>
                    <div style="position:fixed; top:40%; left:85%; opacity:0.1; font-size:5rem; z-index:-1; transform:rotate(15deg)"><i class="fas fa-book"></i></div>
                    <div style="position:fixed; top:70%; left:10%; opacity:0.1; font-size:3rem; z-index:-1; transform:rotate(-5deg)"><i class="fas fa-pencil-alt"></i></div>
                    <div style="position:fixed; top:85%; left:80%; opacity:0.1; font-size:4rem; z-index:-1; transform:rotate(20deg)"><i class="fas fa-microscope"></i></div>
                `;
                document.body.appendChild(decor);
            }
        });
    </script>';
}

// Favicon Injection for Admin
if ($favicon = get_setting('school_logo')) {
    echo '<script>
        (function() {
            var iconUrl = "' . BASE_URL . $favicon . '";
            var link = document.querySelector("link[rel*=\'icon\']");
            if (!link) {
                link = document.createElement(\'link\');
                link.rel = \'icon\';
                document.head.appendChild(link);
            }
            link.href = iconUrl;
        })();
    </script>';
}

?>
<style>
    /* Responsive Sidebar Styles */
    .sidebar {
        height: 100vh;
        background: #0f5132;
        color: white;
        padding-top: 20px;
        position: fixed;
        width: 260px;
        z-index: 1050;
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        left: 0;
    }

    .main-content {
        margin-left: 260px;
        padding: 30px;
        transition: margin-left 0.3s ease-in-out;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.85);
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 500;
        border-left: 4px solid transparent;
    }

    .sidebar .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.15);
        color: #ffc107;
        border-left-color: #ffc107;
    }

    .sidebar .nav-link i.icon-main {
        width: 25px;
        text-align: center;
        margin-right: 10px;
    }

    /* Submenu Styles */
    .submenu {
        background: rgba(0, 0, 0, 0.2);
        padding-left: 0;
        list-style: none;
    }

    .submenu .nav-link {
        padding-left: 55px;
        font-size: 0.9em;
        border-left: none;
    }

    .submenu .nav-link.active {
        border-left: none;
        color: #ffc107;
        background: transparent;
    }

    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }
    }
</style>

<!-- Mobile Toggle Button -->
<button class="btn btn-dark d-lg-none position-fixed top-0 start-0 m-3 z-3 shadow" type="button"
    id="mobileSidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Content -->
<div class="sidebar shadow-lg">
    <div class="d-flex align-items-center justify-content-center mb-4 pt-2 border-bottom border-secondary pb-3 mx-3">
        <i class="fas fa-school fa-2x me-2 text-warning"></i>
        <div class="text-start">
            <h5 class="fw-bold mb-0 text-white">Admin PMBM</h5>
            <small class="text-white-50"
                style="font-size: 0.75rem;"><?= get_setting('school_name', 'MTsN 1 Kota Pekanbaru') ?></small>
        </div>
    </div>

    <ul class="nav flex-column pb-5">
        <!-- Dashboard -->
        <?php if (has_access('dashboard')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link <?= is_active('dashboard.php') ?>" href="<?= BASE_URL . ADMIN_DIR ?>/dashboard.php">
                    <div><i class="fas fa-tachometer-alt icon-main"></i> Dashboard</div>
                </a>
            </li>
        <?php endif; ?>

        <!-- Dropdown: Kesiswaan -->
        <?php if (has_access('kesiswaan')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuKesiswaan" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-user-graduate icon-main"></i> Kesiswaan</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuKesiswaan">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('pendaftar') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/pendaftar/index.php">Data Pendaftar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('verifikasi') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/verifikasi/index.php">Verifikasi Data</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('dokumen') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/dokumen/index.php">Manajemen Dokumen</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('rekap') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/rekap/index.php">Rekap Jalur (ZIP)</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Dropdown: Sekolah -->
        <?php if (has_access('sekolah')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuSekolah" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-school icon-main"></i> Sekolah</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuSekolah">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('jalur') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/jalur/index.php">Jalur
                                Pendaftaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('berkas_pilihan.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/jalur/berkas_pilihan.php">
                                Atur Kata Berkas Pilihan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('skema') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/skema/index.php">Skema
                                PMBM</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('status') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/status/index.php">Status Pelaksanaan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('minimal_nilai.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/minimal_nilai.php">Minimal Nilai Rata-rata</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('upload_foto_contoh.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/upload_foto_contoh.php">Upload Foto Contoh</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('narasi') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/narasi/index.php">Keterangan Narasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('seleksi') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/seleksi/index.php">Seleksi & Ranking</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('finalisasi') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/finalisasi/index.php">Hasil Kelulusan Final</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Dropdown: Pengaturan Website -->
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuWebsite" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-globe icon-main"></i> Pengaturan Website</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuWebsite">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('slider') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/slider/index.php">Slider Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('alur') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/alur/index.php">Alur
                                Pendaftaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('info') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/info/index.php">Info
                                Pendaftaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('panduan_brosur.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/panduan_brosur.php">Panduan & Brosur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('surat_keterangan') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/surat_keterangan/index.php">Surat Keterangan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('batasan_umur.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/batasan_umur.php">Batasan Umur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('popup') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/popup/index.php">Pop-up Iklan</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Dropdown: Informasi Ujian -->
        <?php if (has_access('ujian')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuUjianAdmin" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-file-signature icon-main"></i> Manajemen Ujian</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuUjianAdmin">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('settings.php') && strpos($_SERVER['PHP_SELF'], '/ujian/') !== false ? 'active' : '' ?>"
                                href="<?= BASE_URL ?>4dmMtsn1/ujian/settings.php"
                                style="display: block !important; width: 100%; cursor: pointer;">
                                <i class="fas fa-calendar-check me-2"></i> Jadwal & Info Ujian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('nilai.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/ujian/nilai.php">
                                <i class="fas fa-poll me-2"></i> Data Nilai Ujian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('integrasi.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/ujian/integrasi.php">
                                <i class="fas fa-link me-2"></i> Integrasi CBT External
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Dropdown: Pasca Seleksi -->
        <?php if (has_access('pasca')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuPasca" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-bullhorn icon-main"></i> Pasca Seleksi</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuPasca">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('pengumuman') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/pengumuman/index.php">Pengumuman</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('pengaturan.php') && strpos($_SERVER['PHP_SELF'], 'daftar_ulang') !== false ? 'active' : '' ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/daftar_ulang/pengaturan.php">Syarat DU</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('index.php') && strpos($_SERVER['PHP_SELF'], 'daftar_ulang') !== false ? 'active' : '' ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/daftar_ulang/index.php">List Data DU</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('resume.php') && strpos($_SERVER['PHP_SELF'], 'daftar_ulang') !== false ? 'active' : '' ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/daftar_ulang/resume.php">Resume Pendaftar</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Dropdown: Sistem -->
        <?php if (has_access('sistem')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link collapsed" href="#menuSistem" data-bs-toggle="collapse" role="button"
                    aria-expanded="false">
                    <div><i class="fas fa-cogs icon-main"></i> Sistem</div>
                    <i class="fas fa-chevron-down small opacity-50"></i>
                </a>
                <div class="collapse" id="menuSistem">
                    <ul class="nav flex-column submenu">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('users') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/users/index.php">User
                                Management</a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_active('access_management.php') ?>"
                                    href="<?= BASE_URL . ADMIN_DIR ?>/users/access_management.php">Manajemen Akses</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('laporan') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/laporan/index.php">Laporan & Export</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('wa_gateway.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/wa_gateway.php">WA Gateway settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('dummy_register.php') ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/dummy_register.php">Dummy Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('settings.php') && strpos($_SERVER['PHP_SELF'], '/' . ADMIN_DIR . '/settings.php') !== false ? 'active' : '' ?>"
                                href="<?= BASE_URL . ADMIN_DIR ?>/settings.php">Pengaturan Sistem</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('log') ?>" href="<?= BASE_URL . ADMIN_DIR ?>/log/index.php">Log
                                Aktivitas</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-5 mx-3 pt-3 border-top border-secondary opacity-75">
            <div class="d-flex align-items-center text-white small mb-3">
                <div class="avatar-circle bg-warning text-dark rounded-circle me-2 d-flex align-items-center justify-content-center"
                    style="width: 30px; height: 30px;">A</div>
                <div>Server Time:<br><?= date('H:i') ?></div>
            </div>
            <a class="btn btn-danger w-100 btn-sm" href="<?= BASE_URL ?>logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>

<!-- Overlay for mobile -->
<div class="d-lg-none" onclick="document.querySelector('.sidebar').classList.remove('show')"
    style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:1040;display:none;"
    id="sidebarOverlay"></div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Expand menus that check 'active' class
        var activeLinks = document.querySelectorAll('.submenu .nav-link.active');
        activeLinks.forEach(function (link) {
            var parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                new bootstrap.Collapse(parentCollapse, {
                    toggle: true
                });
                parentCollapse.previousElementSibling.classList.remove('collapsed');
            }
        });

        // Mobile sidebar toggle overlay logic
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (toggleBtn && sidebar && overlay) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            });
        }
    });
</script>