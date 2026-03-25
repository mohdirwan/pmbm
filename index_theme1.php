<?php
// Modern Theme Concept (Matching User Request)
// Replaces the previous theme with a new design: 
// White navbar, School Background Hero, Center Document Card, Action Buttons
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= get_setting('school_name', 'PMBM MTsN 1 Kota Pekanbaru') ?> - PMBM Online
    </title>
    <?php
    // Fetch Active Sliders
    try {
        $stmt_slider = $pdo->query("SELECT * FROM app_sliders WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        $sliders = $stmt_slider->fetchAll();
    } catch (Exception $e) {
        $sliders = [];
    }

    // Fetch Alur Images
    try {
        $stmt_alur = $pdo->query("SELECT * FROM app_alur WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $alurs = $stmt_alur->fetchAll();
    } catch (Exception $e) {
        $alurs = [];
    }

    // Fetch Info Images
    try {
        $stmt_info = $pdo->query("SELECT * FROM app_info WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $infos = $stmt_info->fetchAll();
    } catch (Exception $e) {
        $infos = [];
    }

    // Fetch Panduan & Brosur (Moved to top for Hero Section usage)
    try {
        $stmt_panduan = $pdo->query("SELECT * FROM panduan_brosur WHERE is_active = 1 ORDER BY urutan ASC, id ASC");
        $panduan_items = $stmt_panduan->fetchAll();
    } catch (Exception $e) {
        $panduan_items = [];
    }

    // Fetch Surat Keterangan
    try {
        $stmt_suket = $pdo->query("SELECT * FROM surat_keterangan WHERE is_active = 1 ORDER BY urutan ASC");
        $suket_list = $stmt_suket->fetchAll();
    } catch (Exception $e) {
        $suket_list = [];
    }

    // Check PPDB Status and Target Date for Countdown
    $ppdb_status = get_setting('ppdb_status', 'belum');
    $show_countdown = false;
    $target_date = null;

    if ($ppdb_status == 'belum') {
        $active_scheme = get_setting('active_scheme', '1');
        if ($active_scheme == '1') {
            $target_date = get_setting('scheme_1_start') . ' ' . get_setting('scheme_daily_start');
        } elseif ($active_scheme == '2') {
            $target_date = get_setting('scheme_2_start') . ' 00:00';
        } else {
            $target_date = get_setting('scheme_period_start') . ' 00:00';
        }

        // Validate if target date is in the future
        if (strtotime($target_date) > time()) {
            $show_countdown = true;
        }
    }
    ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animate -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <?php if ($favicon = get_setting('school_logo')): ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL . $favicon ?>">
    <?php endif; ?>

    <style>
        :root {
            --primary-green: #065f2e;
            --dark-green: #044d25;
            --btn-yellow: #ffc107;
            --btn-red: #ff4d4d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styles */
        .navbar {
            background-color: transparent;
            padding-top: 20px;
            padding-bottom: 20px;
            transition: all 0.3s;
        }

        .navbar.scrolled {
            background-color: var(--primary-green);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            height: 45px;
            margin-right: 12px;
            filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.2));
        }

        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            margin-left: 20px;
            font-size: 0.95rem;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .nav-link:hover {
            opacity: 1;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            background-color: var(--primary-green);
            display: flex;
            align-items: center;
            padding-top: 100px;
            padding-bottom: 60px;
            color: white;
            overflow: hidden;
        }

        .hero-content {
            z-index: 2;
        }

        .hero-title {
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            font-weight: 400;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Slider Container on Right */
        .hero-slider-box {
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 8px solid rgba(255, 255, 255, 0.1);
        }

        .hero-bg-img {
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 450px;
        }

        /* Action Buttons */
        .btn-group-custom {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn-daftar {
            background-color: #ffffff;
            color: var(--primary-green);
            border: none;
            padding: 12px 35px;
            font-weight: 700;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-daftar:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            color: var(--primary-green);
        }

        .btn-login {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
            padding: 10px 35px;
            font-weight: 700;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-login:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .btn-guide {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 12px;
            text-decoration: none;
            font-weight: 600;
            width: 100%;
            max-width: 380px;
            transition: all 0.3s;
            border: 1.5px solid var(--btn-yellow);
        }

        .btn-guide-red {
            border-color: var(--btn-red);
        }

        .btn-guide-info {
            border-color: #0dcaf0;
        }


        .btn-guide:hover {
            background-color: #033a1c;
            transform: translateX(5px);
            color: white;
        }

        .btn-guide i {
            font-size: 1.2rem;
        }

        /* Floating WA */
        .fab-whatsapp {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 60px;
            height: 60px;
            background-color: #25d366;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .fab-whatsapp:hover {
            transform: scale(1.1);
            color: white;
        }

        /* Alur & Info Section */
        .section-title h2 {
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-green);
        }

        .alur-img-container {
            cursor: pointer;
            transition: transform 0.3s;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .alur-slide-img {
            width: 100%;
            height: auto;
            max-height: 550px;
            object-fit: contain;
            background: #fff;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-section {
                padding-top: 80px;
                text-align: center;
            }

            .hero-subtitle {
                margin-left: auto;
                margin-right: auto;
            }

            .btn-group-custom {
                justify-content: center;
            }

            .btn-guide {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-slider-box {
                margin-top: 40px;
            }

            .hero-slider-box {
                margin-top: 40px;
            }
        }

        /* Surat Keterangan Card Styles */
        .card-surat {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-surat:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-surat .card-body {
            padding: 1.5rem;
        }

        .badge-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
            flex-shrink: 0;
        }

        .btn-preview {
            background: #0dcaf0;
            border: none;
            color: white;
        }

        .btn-preview:hover {
            background: #0bb5d8;
            color: white;
        }

        .btn-download-template {
            background: #198754;
            border: none;
            color: white;
        }

        .btn-download-template:hover {
            background: #157347;
            color: white;
        }

        .bg-custom-green {
            background-color: var(--primary-green);
        }

        /* Surat Keterangan Card Styles */
        .card-surat {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-surat:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-surat .card-body {
            padding: 1.5rem;
        }

        .badge-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
            flex-shrink: 0;
        }

        .btn-preview {
            background: #0dcaf0;
            border: none;
            color: white;
        }

        .btn-preview:hover {
            background: #0bb5d8;
            color: white;
        }

        .btn-download-template {
            background: #198754;
            border: none;
            color: white;
        }

        .btn-download-template:hover {
            background: #157347;
            color: white;
        }

        .bg-custom-green {
            background-color: var(--primary-green);
        }

        /* Glowing Social Icons Style */
        .social-link-glow {
            width: 40px;
            height: 40px;
            background-color: #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-link-glow i {
            z-index: 2;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .social-link-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent 20%, var(--glow-color) 50%, transparent 80%);
            opacity: 0.6;
            transition: all 0.3s ease;
            transform: translateY(100%);
        }

        .social-link-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 20px var(--glow-color);
            border-color: var(--glow-color);
        }

        .social-link-glow:hover::before {
            transform: translateY(0);
        }

        .social-link-glow:hover i {
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark">
        <div class="container"><a class="navbar-brand" href="#">
                <?php if ($navLogo = get_setting('school_logo')): ?>
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= BASE_URL . $navLogo ?>" alt="Logo" height="45" class="d-inline-block">
                        <div class="d-flex flex-column lh-1">
                            <span class="fw-bold text-uppercase text-white"
                                style="font-size: 1.1rem; letter-spacing: 0.5px;">PMBM MTsN 1 Kota Pekanbaru</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-school text-white fa-2x"></i>
                        <div class="d-flex flex-column lh-1">
                            <span class="fw-bold text-uppercase text-white" style="font-size: 1.1rem;">PMBM MTsN 1 Kota
                                Pekanbaru</span>
                        </div>
                    </div>
                <?php endif; ?>
            </a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($show_countdown): ?>
                        <li class="nav-item"><span class="badge bg-warning text-dark me-2"><i
                                    class="fas fa-clock me-1"></i>Segera Dibuka </span></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link text-uppercase fw-bold" href="#">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link text-uppercase fw-bold" href="#info">Informasi</a></li>
                    <li class="nav-item"><a class="nav-link text-uppercase fw-bold" href="#suket">Download</a></li>
                    <li class="nav-item"><a class="nav-link text-uppercase fw-bold" href="#kontak">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Left Content -->
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <h1 class="hero-title">
                        SELAMAT DATANG DI WEBSITE PMBM
                    </h1>
                    <p class="hero-subtitle">
                        Penerimaan Murid Baru Madrasah MTsN 1 Kota Pekanbaru<br>Tahun Ajaran 2026/2027
                    </p>
                    <?php if ($show_countdown): ?>
                        <div
                            class="countdown-box d-inline-block bg-dark bg-opacity-25 p-3 rounded-4 backdrop-blur mb-4 border border-white border-opacity-10">
                            <p class="text-warning mb-2 small text-uppercase letter-spacing-1 fw-bold"><i
                                    class="fas fa-stopwatch me-2"></i>Menuju Buka Pendaftaran</p>
                            <div class="d-flex gap-3 text-center">
                                <div>
                                    <div class="bg-white text-success rounded-3 p-2 fw-bold h4 mb-0 shadow-sm"
                                        style="min-width: 50px;" id="d_days">00</div>
                                    <small class="text-white-50" style="font-size: 0.7rem;">Hari</small>
                                </div>
                                <div class="text-white align-self-start mt-1 fw-bold">:</div>
                                <div>
                                    <div class="bg-white text-success rounded-3 p-2 fw-bold h4 mb-0 shadow-sm"
                                        style="min-width: 50px;" id="d_hours">00</div>
                                    <small class="text-white-50" style="font-size: 0.7rem;">Jam</small>
                                </div>
                                <div class="text-white align-self-start mt-1 fw-bold">:</div>
                                <div>
                                    <div class="bg-white text-success rounded-3 p-2 fw-bold h4 mb-0 shadow-sm"
                                        style="min-width: 50px;" id="d_minutes">00</div>
                                    <small class="text-white-50" style="font-size: 0.7rem;">Menit</small>
                                </div>
                                <div class="text-white align-self-start mt-1 fw-bold">:</div>
                                <div>
                                    <div class="bg-white text-success rounded-3 p-2 fw-bold h4 mb-0 shadow-sm"
                                        style="min-width: 50px;" id="d_seconds">00</div>
                                    <small class="text-white-50" style="font-size: 0.7rem;">Detik</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="btn-group-custom">
                        <?php if ($show_register): ?>
                            <a href="<?= BASE_URL ?>register.php" class="btn btn-daftar"><i
                                    class="fas fa-user-plus"></i>Daftar </a>
                        <?php endif; ?>

                        <?php if ($show_login): ?>
                            <a href="<?= BASE_URL ?>login_siswa.php" class="btn btn-login"><i
                                    class="fas fa-sign-in-alt"></i>Login </a>
                        <?php endif; ?>
                    </div>
                    <!-- Guide Buttons -->
                    <div class="mt-4">
                        <?php
                        // Try to find specific items or use defaults
                        $petunjuk = null;
                        $tutorial = null;

                        // New Logic: Check setting first
                        $startUrl = '#';
                        $juknisSetting = get_setting('juknis_file');

                        // Find tutorial video
                        foreach ($panduan_items as $item) {
                            // If no setting, look for petunjuk in items
                            if (!$juknisSetting && stripos($item['judul'], 'petunjuk') !== false)
                                $petunjuk = $item;

                            if (stripos($item['judul'], 'tutorial') !== false || stripos($item['judul'], 'video') !== false)
                                $tutorial = $item;
                        }

                        // Fallback logic for Petunjuk
                        if ($juknisSetting) {
                            $petunjukUrl = BASE_URL . $juknisSetting;
                        } elseif ($petunjuk) {
                            $petunjukUrl = ($petunjuk['tipe'] == 'file') ? BASE_URL . $petunjuk['file_path'] : $petunjuk['video_url'];
                        } else {
                            // Last resort: first item if exists
                            if (count($panduan_items) > 0 && !$tutorial) {
                                $first = $panduan_items[0];
                                $petunjukUrl = ($first['tipe'] == 'file') ? BASE_URL . $first['file_path'] : $first['video_url'];
                            } else {
                                $petunjukUrl = '#';
                            }
                        }

                        // Fallback for Tutorial
                        if (!$tutorial && count($panduan_items) > 1) {
                            $tutorial = $panduan_items[1];
                        }
                        ?>

                        <a href="<?= $petunjukUrl ?>" target="_blank" class="btn btn-guide"><i
                                class="fas fa-book"></i>Petunjuk
                            Teknis </a>

                        <?php if ($tutorial): ?>
                            <?php
                            $url = ($tutorial['tipe'] == 'file') ? BASE_URL . $tutorial['file_path'] : $tutorial['video_url'];
                            ?>
                            <a href="<?= $url ?>" target="_blank" class="btn btn-guide btn-guide-red"><i
                                    class="fas fa-desktop"></i>Tutorial Pendaftaran </a>
                        <?php endif; ?>

                        <!-- If no items in DB, show static ones pointing to common files if they exist
                                            or just placeholders -->
                        <?php if (empty($panduan_items)): ?>
                            <a href="#" class="btn btn-guide"><i class="fas fa-book"></i>Petunjuk Teknis
                            </a><a href="#" class="btn btn-guide btn-guide-red"><i class="fas fa-desktop"></i>Tutorial
                                Pendaftaran </a>
                        <?php endif; ?>

                        <!-- Added Pakta Integritas & Surat Keterangan Button -->
                        <a href="#suket" class="btn btn-guide btn-guide-info"><i class="fas fa-file-contract"></i>Pakta
                            Integritas & Surat Keterangan </a>
                    </div>
                </div>
                <!-- Right Content (Slider) -->
                <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="hero-slider-box">
                        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel"
                            data-bs-interval="3000">
                            <div class="carousel-inner">
                                <?php if (count($sliders) > 0): ?>
                                    <?php foreach ($sliders as $index => $slide): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <div class="hero-bg-img"
                                                style="background-image: url('<?= BASE_URL . $slide['image_path'] ?>');">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="carousel-item active">
                                        <div class="hero-bg-img"
                                            style="background-image: url('<?= BASE_URL ?>assets/img/hero-illustration.png');">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (count($sliders) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel"
                                    data-bs-slide="prev"><span class="carousel-control-prev-icon"
                                        aria-hidden="true"></span><span
                                        class="visually-hidden">Previous</span></button><button
                                    class="carousel-control-next" type="button" data-bs-target="#heroCarousel"
                                    data-bs-slide="next"><span class="carousel-control-next-icon"
                                        aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Alur Section -->
    <section id="alur" class="py-5 bg-light d-none">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-bold">Alur Pendaftaran</h2>
                <div class="d-flex justify-content-center">
                    <div style="width: 50px; height: 3px; background: var(--primary-green);"></div>
                </div>
                <p class="text-muted mt-3">Berikut adalah tahapan pendaftaran murid baru.</p>
            </div>
            <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-10">
                    <?php if (count($alurs) > 0): ?>
                        <div id="alurCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <!-- Indicators -->
                            <?php if (count($alurs) > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($alurs as $index => $alur): ?>
                                        <button type="button" data-bs-target="#alurCarousel" data-bs-slide-to="<?= $index ?>"
                                            class="<?= $index === 0 ? 'active' : '' ?>" aria-current="true"
                                            aria-label="Slide <?= $index + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="carousel-inner rounded-4 shadow-sm">
                                <?php foreach ($alurs as $index => $alur): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="alur-img-container"
                                            onclick="showZoom('<?= BASE_URL . $alur['image_path'] ?>', '<?= htmlspecialchars($alur['title']) ?>')">
                                            <img src="<?= BASE_URL . $alur['image_path'] ?>"
                                                class="d-block w-100 alur-slide-img"
                                                alt="<?= htmlspecialchars($alur['title']) ?>">
                                            <?php if (!empty($alur['title'])): ?>
                                                <div
                                                    class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 py-2">
                                                    <h5><?= htmlspecialchars($alur['title']) ?></h5>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($alurs) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#alurCarousel"
                                    data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"
                                        aria-hidden="true" style="background-size: 50%"></span><span
                                        class="visually-hidden">Previous</span></button><button class="carousel-control-next"
                                    type="button" data-bs-target="#alurCarousel" data-bs-slide="next"><span
                                        class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span><span class="visually-hidden">Next</span></button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Belum ada informasi alur pendaftaran yang
                            diunggah. </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Zoom Modal -->
    <div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body p-0 position-relative text-center"><button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-white rounded-circle opacity-100"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 1051;"></button><img src=""
                        id="zoomImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
                    <h5 id="zoomCaption" class="text-white mt-2 text-shadow"></h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Info / Brochure Section -->
    <section id="info" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h6 class="text-uppercase fw-bold mb-2" style="color: var(--primary-green); letter-spacing: 1px;">
                    Informasi</h6>
                <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">Informasi PMBM</h2>
                <p class="text-muted">Geser gambar di bawah untuk melihat detail informasi penerimaan murid baru.</p>
            </div>
            <!-- Info Slider Section -->
            <div class="row justify-content-center mt-3" data-aos="fade-up">
                <div class="col-lg-10">
                    <?php
                    // Merge Panduan Items (Files) and Info Items for the slider
                    $slider_items = [];

                    // Add Panduan Items (uploaded to Panduan & Brosur)
                    if (!empty($panduan_items)) {
                        foreach ($panduan_items as $item) {
                            if ($item['tipe'] == 'file' && !empty($item['file_path'])) {
                                $slider_items[] = [
                                    'image_path' => $item['file_path'], // Note: uses file_path
                                    'title' => $item['judul']
                                ];
                            }
                        }
                    }

                    // Add Info Items (uploaded to Info Pendaftaran)
                    if (!empty($infos)) {
                        foreach ($infos as $item) {
                            $slider_items[] = [
                                'image_path' => $item['image_path'],
                                'title' => $item['title']
                            ];
                        }
                    }
                    ?>

                    <?php if (count($slider_items) > 0): ?>
                        <div id="infoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <!-- Indicators -->
                            <?php if (count($slider_items) > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($slider_items as $index => $item): ?>
                                        <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="<?= $index ?>"
                                            class="<?= $index === 0 ? 'active' : '' ?>" aria-current="true"
                                            aria-label="Slide <?= $index + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="carousel-inner rounded-4 shadow-sm">
                                <?php foreach ($slider_items as $index => $item): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="alur-img-container"
                                            onclick="showZoom('<?= BASE_URL . $item['image_path'] ?>', '<?= htmlspecialchars($item['title']) ?>')">

                                            <?php
                                            $isPdf = (strpos(strtolower($item['image_path']), '.pdf') !== false);
                                            if ($isPdf):
                                                ?>
                                                <div class="d-flex flex-column align-items-center justify-content-center bg-light text-secondary"
                                                    style="height: 400px;">
                                                    <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                                                    <h5><?= htmlspecialchars($item['title']) ?></h5>
                                                    <span class="badge bg-primary mt-2">Klik untuk Membuka Dokumen</span>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= BASE_URL . $item['image_path'] ?>"
                                                    class="d-block w-100 alur-slide-img"
                                                    alt="<?= htmlspecialchars($item['title']) ?>">
                                            <?php endif; ?>

                                            <?php if (!empty($item['title']) && !$isPdf): ?>
                                                <div
                                                    class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 py-2">
                                                    <h5><?= htmlspecialchars($item['title']) ?></h5>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($slider_items) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#infoCarousel"
                                    data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"
                                        aria-hidden="true" style="background-size: 50%"></span><span
                                        class="visually-hidden">Previous</span></button><button class="carousel-control-next"
                                    type="button" data-bs-target="#infoCarousel" data-bs-slide="next"><span
                                        class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span><span class="visually-hidden">Next</span></button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Belum ada informasi yang diunggah.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Surat Keterangan Section -->
    <section id="suket" class="py-5 bg-custom-green text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fas fa-file-alt me-2"></i> Surat Keterangan</h2>
                    <p class="mb-0 opacity-75">Template Surat Keterangan untuk PMBM MTsN 1 Kota Pekanbaru</p>
                </div>
                <a href="#hero" class="btn btn-light rounded-pill px-4 fw-bold text-success d-none">
                    <i class="fas fa-arrow-up me-2"></i> Kembali ke Atas
                </a>
            </div>

            <div class="row g-4">
                <?php if (!empty($suket_list)): ?>
                    <?php foreach ($suket_list as $index => $surat): ?>
                        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <div class="card card-surat h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="badge-number me-3 text-white">
                                            <?= $index + 1 ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold text-dark">
                                                <?= htmlspecialchars($surat['nama_surat']) ?>
                                            </h5>
                                            <small class="text-muted">PMBM MTsN 1 Kota Pekanbaru</small>
                                        </div>
                                    </div>

                                    <div class="mb-4 bg-light p-2 rounded">
                                        <div class="d-flex">
                                            <i class="fas fa-info-circle text-primary me-2 mt-1"></i>
                                            <p class="mb-0 small text-muted"><?= htmlspecialchars($surat['keterangan']) ?></p>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mb-3">
                                        <?php
                                        // Determine preview URL
                                        $preview_url = !empty($surat['file_preview_pdf'])
                                            ? BASE_URL . 'uploads/suket_templates/' . $surat['file_preview_pdf']
                                            : 'preview_suket.php?id=' . $surat['id'];

                                        // Determine download URL
                                        $download_url = !empty($surat['file_template_docx'])
                                            ? BASE_URL . 'uploads/suket_templates/' . $surat['file_template_docx']
                                            : 'preview_suket.php?id=' . $surat['id'];

                                        $download_attr = !empty($surat['file_template_docx']) ? 'download' : 'target="_blank"';
                                        ?>

                                        <a href="<?= $preview_url ?>" target="_blank"
                                            class="btn btn-preview btn-sm flex-fill rounded-pill fw-bold py-2 shadow-sm text-white">
                                            <i class="fas fa-eye me-1"></i> Preview
                                        </a>
                                        <a href="<?= $download_url ?>" <?= $download_attr ?>
                                            class="btn btn-download-template btn-sm flex-fill rounded-pill fw-bold py-2 shadow-sm text-white">
                                            <i class="fas fa-download me-1"></i> Download Template
                                        </a>
                                    </div>

                                    <div class="bg-light p-3 rounded-3 border-start border-4 border-warning">
                                        <small class="d-block text-muted">
                                            <i class="fas fa-lightbulb text-warning me-1"></i> <strong>Tips:</strong>
                                        </small>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.8rem; line-height: 1.4;">
                                            Download template, isi sesuai data Anda, kemudian minta tanda tangan dari pihak
                                            berwenang.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info bg-opacity-10 border-0 text-white">
                            <i class="fas fa-info-circle me-2"></i> Belum ada template surat keterangan yang tersedia.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Informasi Penting -->
            <div class="card border-0 rounded-4 shadow-sm mt-5 " data-aos="fade-up">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-question-circle text-primary fa-2x me-3"></i>
                        <h4 class="fw-bold text-primary mb-0">Informasi Penting</h4>
                    </div>
                    <ul class="text-secondary mb-0" style="line-height: 1.8;">
                        <li class="mb-2">Surat keterangan harus ditandatangani oleh Kepala Sekolah asal dan diberi
                            stempel sekolah</li>
                        <li class="mb-2">Pastikan semua data yang diisi sudah benar dan sesuai dengan dokumen asli, jika
                            terjadi kesalahan dalam pengisian data dan unggah persyaratan bukan tanggung jawab panitia,
                            karena data tidak bisa diubah setelah di simpan</li>
                        <li class="mb-2">Surat keterangan yang telah diisi harus di-scan dan diupload saat pendaftaran
                        </li>
                        <li>Untuk bantuan lebih lanjut, hubungi panitia PMBM</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <!-- Footer -->
    <footer class="text-white pt-5 pb-4 mt-auto" style="background-color: #0d1b2a;" id="kontak">
        <div class="container">
            <div class="row">
                <!-- Column 1: Logo & Info -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($navLogo = get_setting('school_logo')): ?>
                            <img src="<?= BASE_URL . $navLogo ?>" alt="Logo" height="50" class="me-2">
                        <?php else: ?>
                            <i class="fas fa-school fa-2x me-2 text-warning"></i>
                        <?php endif; ?>
                        <h5 class="fw-bold mb-0 text-white">MTsN 1 Kota Pekanbaru</h5>
                    </div>
                    <p class="text-white-50 mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                        MTsN 1 Kota Pekanbaru adalah madrasah unggulan yang berkomitmen mencetak generasi cerdas,
                        berakhlak mulia, dan berwawasan teknologi.
                    </p>
                    <div class="d-flex gap-3 mb-4">
                        <a href="https://www.facebook.com/HumasMTsn1.pekanbaru/" target="_blank"
                            class="social-link-glow" style="--glow-color: #1877F2;"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/mtsn.1.pekanbaru/?hl=en" target="_blank"
                            class="social-link-glow" style="--glow-color: #E1306C;"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/@mtsn1pekanbaru223/videos" target="_blank"
                            class="social-link-glow" style="--glow-color: #FF0000;"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.tiktok.com/@mtsn1kotapekanbaru" target="_blank" class="social-link-glow"
                            style="--glow-color: #00f2ea;"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Column 2: Hubungi Kami -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase fw-bold mb-4 text-warning">### Hubungi Kami</h6>
                    <ul class="list-unstyled text-white-50" style="font-size: 0.9rem;">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-map-marker-alt mt-1 me-3 text-warning"></i>
                            <span>Jl. Amal Hamzah No.1, Cinta Raja, Kec. Sail, Kota Pekanbaru, Riau 28127</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-phone-alt mt-1 me-3 text-warning"></i>
                            <div class="d-flex flex-column gap-1">
                                <a href="tel:081234597311"
                                    class="text-white-50 text-decoration-none hover-warning">081234597311 (Telefon
                                    Only)</a>
                                <a href="tel:081234597312"
                                    class="text-white-50 text-decoration-none hover-warning">081234597312 (Telefon
                                    Only)</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Link Terkait -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase fw-bold mb-4 text-warning">### Link Terkait</h6>
                    <ul class="list-unstyled" style="font-size: 0.9rem;">
                        <li class="mb-2"><a href="#"
                                class="text-white-50 text-decoration-none hover-warning">Beranda</a></li>
                        <li class="mb-2"><a href="#alur" class="text-white-50 text-decoration-none hover-warning">Alur
                                Pendaftaran</a></li>
                        <li class="mb-2"><a href="#suket"
                                class="text-white-50 text-decoration-none hover-warning">Download Template</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-warning">Web Utama
                                Madrasah</a></li>
                    </ul>
                </div>

                <!-- Column 4: Jam Pelayanan -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-uppercase fw-bold mb-4 text-warning">### Jam Pelayanan</h6>
                    <ul class="list-unstyled text-white-50" style="font-size: 0.9rem;">
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Senin - Kamis:</span>
                            <span>08.00 - 15.00</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Jumat:</span>
                            <span>08.00 - 15.30</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Sabtu - Minggu:</span>
                            <span>Libur</span>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-4 opacity-25">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-white-50 small">
                        &copy; 2026 PMBM MTsN 1 Kota Pekanbaru. All Rights Reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Navbar Scroll Logic
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar');

            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        <?php if ($show_countdown && isset($target_date)): ?>
                // Countdown Script
                (function () {
                    const targetDate = new Date("<?= date('Y-m-d\TH:i:s', strtotime($target_date)) ?>").getTime();

                    const timer = setInterval(function () {
                        const now = new Date().getTime();
                        const distance = targetDate - now;

                        if (distance < 0) {
                            clearInterval(timer);
                            window.location.reload();
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        const elDays = document.getElementById("d_days");

                        if (elDays) {
                            document.getElementById("d_days").innerText = days < 10 ? "0" + days : days;
                            document.getElementById("d_hours").innerText = hours < 10 ? "0" + hours : hours;
                            document.getElementById("d_minutes").innerText = minutes < 10 ? "0" + minutes : minutes;
                            document.getElementById("d_seconds").innerText = seconds < 10 ? "0" + seconds : seconds;
                        }
                    }, 1000);
                })();
        <?php endif; ?>

        // Zoom Modal Function with PDF Support
        function showZoom(src, title) {
            var modalBody = document.querySelector('#zoomModal .modal-body');
            var closeBtn = '<button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-white rounded-circle opacity-100" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1051;"></button>';

            if (src.toLowerCase().endsWith('.pdf')) {
                // PDF Viewer
                modalBody.innerHTML = closeBtn +
                    '<div class="ratio ratio-16x9 rounded overflow-hidden" style="height: 85vh;">' +
                    '   <iframe src="' + src + '#toolbar=0" style="border:none;"></iframe>' +
                    '</div>' +
                    '<h5 id="zoomCaption" class="text-white mt-2 text-shadow">' + title + '</h5>';
            } else {
                // Image Viewer
                modalBody.innerHTML = closeBtn +
                    '<img src="' + src + '" id="zoomImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">' +
                    '<h5 id="zoomCaption" class="text-white mt-2 text-shadow">' + title + '</h5>';
            }

            var myModal = new bootstrap.Modal(document.getElementById('zoomModal'));
            myModal.show();
        }
    </script>
    <?php include 'includes/popup.php'; ?>
</body>

</html>