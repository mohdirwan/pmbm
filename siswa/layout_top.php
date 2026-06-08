<?php
require_once '../includes/config.php';

// Auth Check for Student
if (!isset($_SESSION['siswa_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login_siswa.php");
    exit();
}

// Get Student Data
$stmt = $pdo->prepare("SELECT p.*, j.nama_jalur, p.status_tahfidz 
                       FROM pendaftar p 
                       LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                       WHERE p.id = ?");
$stmt->execute([$_SESSION['siswa_id']]);
$siswa = $stmt->fetch();

if (!$siswa) {
    session_destroy();
    header("Location: ../login_siswa.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Guard Status Administrasi Page (Closed/Disabled for now)
if ($current_page == 'status_administrasi.php') {
    header("Location: dashboard.php");
    exit();
}

// --- CENTRALIZED ACCESS GUARD ---
$ppdb_status = get_setting('ppdb_status', 'belum');
$tahap_admin = get_setting('tahap_administrasi', 'verifikasi');

// Define Restriction Categories
$exam_pages = ['status_ujian.php', 'ujian_akun.php', 'ujian_jadwal.php', 'ujian_mulai.php', 'ujian_panduan.php'];
$after_selection_pages = ['pengumuman.php', 'informasi_daftar_ulang.php', 'upload_daftar_ulang.php', 'cetak_bukti_lulus.php'];

// 1. Guard Exam Pages
if (in_array($current_page, $exam_pages)) {
    $is_tahfidz = stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false;
    $failed_tahfidz = ($is_tahfidz && ($siswa['status_tahfidz'] ?? '') == 'Tidak Lulus');
    
    $rejected_at_admin = ($siswa['status'] == 'Ditolak' && $tahap_admin == 'pengumuman' && !$failed_tahfidz);
    
    // Forbidden if rejected at admin, or if admin results not yet published, or if still pending
    if ($rejected_at_admin || $tahap_admin !== 'pengumuman' || $siswa['status'] == 'Pending') {
        header("Location: dashboard.php?msg=access_denied");
        exit();
    }
}

// 2. Guard Final Results / Re-registration Pages
if (in_array($current_page, $after_selection_pages)) {
    if ($ppdb_status !== 'pengumuman') {
        header("Location: dashboard.php?msg=not_published");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Disable Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title><?= $page_title ?? 'Dashboard Murid' ?> - PMBM MTsN 1 Kota Pekanbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <?php if ($favicon = get_setting('school_logo')): ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL . $favicon ?>">
    <?php endif; ?>
    <style>
        :root {
            --sidebar-bg: #0b2c24;
            --sidebar-text: rgba(255, 255, 255, 0.7);
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --accent-color: #ffc107;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #33475b;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            align-items: stretch;
        }

        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2.5rem 1.5rem;
            background: #08211b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .menu-header {
            padding: 1rem 1.5rem;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .menu-header:hover {
            background: var(--sidebar-hover);
        }

        .menu-header i.main-icon {
            width: 25px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .menu-header .chevron {
            transition: transform 0.3s;
            font-size: 0.8rem;
            opacity: 0.5;
        }

        .menu-header:not(.collapsed) .chevron {
            transform: rotate(180deg);
            opacity: 1;
            color: var(--accent-color);
        }

        .sub-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: rgba(0, 0, 0, 0.15);
        }

        .sub-item .nav-link {
            padding: 0.75rem 1.5rem 0.75rem 3.5rem;
            color: var(--sidebar-text);
            font-size: 0.88rem;
            display: block;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sub-item .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .sub-item .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-left-color: var(--accent-color);
            font-weight: 500;
        }

        #content {
            width: 100%;
            padding: 0;
            min-height: 100vh;
        }

        .top-header {
            background: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .page-body {
            padding: 2.5rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
        }

        .form-label-premium {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control-premium {
            border: 1.5px solid #eef0f2;
            border-radius: 14px;
            padding: 12px 18px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #f8f9fa;
        }

        .form-control-premium:focus {
            background-color: #fff;
            border-color: var(--sidebar-bg);
            box-shadow: 0 0 0 4px rgba(11, 44, 36, 0.1);
            outline: none;
        }

        .btn-premium-action {
            background: linear-gradient(135deg, #0b2c24, #1a4d40);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(11, 44, 36, 0.2);
        }

        .btn-premium-action:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(11, 44, 36, 0.4);
            color: white;
        }

        .badge-premium {
            padding: 8px 16px;
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forward;
        }

        @media (max-width: 992px) {
            #sidebar {
                position: fixed;
                height: 100vh;
                left: 0;
                margin-left: calc(-1 * var(--sidebar-width));
                z-index: 2000;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.2);
            }

            #sidebar.active {
                margin-left: 0;
            }

            #content {
                width: 100%;
                margin-left: 0 !important;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 1500;
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.active {
                display: block;
                opacity: 1;
            }

            .top-header {
                padding: 1rem;
            }

            .page-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-brand">
                    <div class="bg-warning rounded-3 p-2 me-3 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="fas fa-mosque text-dark fs-5"></i>
                    </div>
                    <div>
                        <div class="lh-1">MURID PMBM</div>
                        <small class="fw-normal opacity-50" style="font-size: 0.65rem;">MTsN 1 Kota Pekanbaru</small>
                    </div>
                </a>
            </div>

            <div class="nav-container mt-3">
                <div class="nav-item">
                    <button class="menu-header" data-bs-toggle="collapse" data-bs-target="#menuDashboard">
                        <span><i class="fas fa-columns main-icon"></i> Dashboard</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="collapse show" id="menuDashboard">
                        <ul class="sub-menu">
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"
                                    href="dashboard.php">Ringkasan Pendaftaran</a></li>
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'identitas.php' ? 'active' : '' ?>"
                                    href="identitas.php">Identitas Calon Murid</a></li>
                            <?php
                            $ppdb_status = get_setting('ppdb_status', 'tutup');
                            $status = $siswa['status'] ?? 'Pending';
                            $allowed_edit_stages = ['buka', 'verifikasi', 'pengumuman_adm'];
                            $can_edit = (!in_array($status, ['Terverifikasi', 'Diterima', 'Lulus']) && in_array($ppdb_status, $allowed_edit_stages));
                            if ($status == 'Ditolak' && in_array($ppdb_status, $allowed_edit_stages)) $can_edit = true;
                            if (isset($siswa['finalisasi']) && $siswa['finalisasi'] == 'ya') $can_edit = false;
                            ?>
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'upload_berkas.php' ? 'active' : '' ?>"
                                    href="upload_berkas.php">Upload Berkas Persyaratan</a></li>
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'preview_finalisasi.php' ? 'active' : '' ?>"
                                    href="preview_finalisasi.php"><i class="fas fa-check-double me-1 small opacity-75"></i> Finalisasi Pendaftaran & Cetak Formulir</a></li>
                        </ul>
                    </div>
                </div>

                <?php
                $ppdb_status = get_setting('ppdb_status', 'belum');
                $tahap_admin = get_setting('tahap_administrasi', 'verifikasi');

                $is_tahfidz = stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false;
                $failed_tahfidz = ($is_tahfidz && ($siswa['status_tahfizh'] ?? $siswa['status_tahfidz'] ?? '') == 'Tidak Lulus');
                
                // --- SIDEBAR LEAK PROTECTION (WATERTIGHT) ---
                // We define 'is_verified' as the status that allows access to exam menus.
                // It should ONLY become false for those rejected specifically at the administrative stage.
                
                $is_in_final_announcement = ($ppdb_status == 'pengumuman');
                
                if ($is_in_final_announcement) {
                    // Final announcement is out: Access to all menus for everyone (rejection handled inside pages)
                    $is_verified = ($siswa['status'] != 'Pending');
                } elseif ($ppdb_status == 'cbt' || $ppdb_status == 'finalisasi') {
                    // During CBT/Finalization: They should only see menus if they are not rejected.
                    $is_verified = ($siswa['status'] != 'Ditolak' && $siswa['status'] != 'Pending');
                } else {
                    // Before CBT/Finalization: Standard administrative rejection check.
                    $rejected_at_admin = ($siswa['status'] == 'Ditolak' && $tahap_admin == 'pengumuman' && !$failed_tahfidz);
                    $is_verified = ($siswa['status'] != 'Pending' && !$rejected_at_admin);
                }

                $is_tanpa_tes = stripos(($siswa['nama_jalur'] ?? ''), 'Tanpa Tes') !== false;
                $needs_exam = !($is_tanpa_tes || ($is_tahfidz && ($siswa['status_tahfizh'] ?? $siswa['status_tahfidz'] ?? '') == 'Lulus'));

                // Reveal Exam Info ONLY after admin results are out, and only for those who passed admin (Verified/Diterima/Lulus)
                // AND ensure it doesn't disappear if they are final-rejected until the big day.
                if ($tahap_admin == 'pengumuman' && $is_verified && $needs_exam):
                    ?>
                    <div class="nav-item">
                        <a class="menu-header text-decoration-none <?= $current_page != 'status_ujian.php' ? 'collapsed' : '' ?>"
                            href="status_ujian.php?title=Status%20Ujian">
                            <span><i class="fas fa-laptop-code main-icon"></i> Informasi Ujian</span>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="nav-item">
                    <button
                        class="menu-header <?= !in_array($current_page, ['status_administrasi.php', 'status_ujian.php', 'status_akhir.php', 'pengumuman.php']) ? 'collapsed' : '' ?>"
                        data-bs-toggle="collapse" data-bs-target="#menuLulus">
                        <span><i class="fas fa-award main-icon"></i> Status Kelulusan</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="collapse <?= in_array($current_page, ['status_administrasi.php', 'status_ujian.php', 'status_akhir.php', 'pengumuman.php']) ? 'show' : '' ?>"
                        id="menuLulus">
                        <ul class="sub-menu">
                            <?php if (false): ?>
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'status_administrasi.php' ? 'active' : '' ?>"
                                    href="status_administrasi.php?title=Status Administrasi">Status Administrasi</a>
                            </li>
                            <?php endif; ?>
                            <?php if ($tahap_admin == 'pengumuman' && $is_verified): ?>
                            <li class="sub-item"><a
                                    class="nav-link <?= $current_page == 'status_akhir.php' ? 'active' : '' ?>"
                                    href="status_akhir.php?title=Status Akhir PMBM">Status Akhir PMBM</a></li>
                            <?php endif; ?>
                            <?php 
                            // Menu Pengumuman & Daftar Ulang dinonaktifkan sementara sesuai permintaan
                            if (false): 
                                $ann_status = get_setting('announcement_status', 'closed');
                                if ($ann_status == 'open'):
                                    ?>
                                    <li class="sub-item"><a
                                            class="nav-link <?= $current_page == 'pengumuman.php' ? 'active' : '' ?>"
                                            href="pengumuman.php?title=Pengumuman%20&%20Daftar%20Ulang">Pengumuman & Daftar Ulang</a>
                                    </li>
                                    <?php if ($siswa['status'] == 'Diterima' || $siswa['status'] == 'Lulus'): ?>
                                        <li class="sub-item"><a
                                                class="nav-link <?= $current_page == 'informasi_daftar_ulang.php' ? 'active' : '' ?>"
                                                href="informasi_daftar_ulang.php?title=Informasi%20Daftar%20Ulang">Informasi Daftar Ulang</a>
                                        </li>
                                        <li class="sub-item"><a
                                                class="nav-link <?= $current_page == 'upload_daftar_ulang.php' ? 'active' : '' ?>"
                                                href="upload_daftar_ulang.php?title=Upload%20Berkas%20Daftar%20Ulang">Upload Berkas Daftar Ulang</a>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="p-3 mt-auto">
                <a href="../logout.php" class="btn btn-outline-danger w-100 rounded-pill"><i
                        class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </nav>

        <div id="content">
            <header class="top-header">
                <button type="button" id="sidebarCollapse" class="btn btn-light rounded-pill px-3 d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <div class="text-end me-3 d-none d-md-block">
                        <div class="fw-bold lh-1"><?= htmlspecialchars($siswa['nama_lengkap']) ?></div>
                        <small class="text-muted small">Peserta PMBM</small>
                    </div>
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 45px; height: 45px;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </header>
            <div class="page-body">
                <h2 class="fw-bold mb-4"><?= $page_title ?></h2>