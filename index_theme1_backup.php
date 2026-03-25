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
        <?= get_setting('school_name', 'PMBM MTsN 1') ?> - PMBM Online
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

    <style>
        :root {
            --primary-green: #198754;
            --dark-overlay: rgba(30, 60, 40, 0.85);
            /* Greenish dark overlay */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .navbar-brand {
            font-weight: 700;
            color: #0d6efd;
            /* Blue color for text as in image */
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin-left: 15px;
            font-size: 0.95rem;
        }

        .nav-link i {
            margin-right: 5px;
            color: #555;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            /* Full screen */
            background-color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding-top: 80px;
            /* Space for fixed navbar */
            overflow: hidden;
        }

        .hero-slider-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-bg-img {
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 100%;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark-overlay);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 900px;
            padding: 20px;
        }

        .logo-center {
            width: 120px;
            height: auto;
            margin-bottom: 2rem;
            /* filter: drop-shadow(0 0 10px rgba(255,255,255,0.5)); */
        }

        .hero-title {
            font-weight: 800;
            font-size: 2.5rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        @media (min-width: 768px) {
            .hero-title {
                font-size: 3.5rem;
            }
        }

        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            margin-bottom: 3rem;
            opacity: 0.9;
        }

        /* Card Surat Pernyataan */
        .download-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 20px 40px;
            display: inline-flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 4rem;
            transition: transform 0.3s;
        }

        .download-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.25);
        }

        .download-icon {
            font-size: 2.5rem;
            color: #ffc107;
            /* Warning yellow */
        }

        .download-text text-start {
            text-align: left;
        }

        .download-text h5 {
            margin: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
        }

        .download-text a {
            color: #ffc107;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Action Buttons Row */
        .action-btns {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-action {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 700;
            text-transform: uppercase;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .btn-green {
            background-color: #198754;
            color: white;
        }

        .btn-red {
            background-color: #dc3545;
            color: white;
        }

        .btn-cyan {
            background-color: #0dcaf0;
            color: white;
        }

        .btn-purple {
            background-color: #6f42c1;
            color: white;
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
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
        }

        .fab-whatsapp:hover {
            transform: scale(1.1);
            color: white;
        }

        .fab-whatsapp:hover {
            transform: scale(1.1);
            color: white;
        }

        /* Countdown Stylings */
        .countdown-hero {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            padding: 20px 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 193, 7, 0.4);
            display: inline-block;
            margin-bottom: 2rem;
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        .countdown-box {
            text-align: center;
            min-width: 80px;
        }

        .countdown-box span {
            display: block;
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            color: #ffc107;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .countdown-box small {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            opacity: 0.9;
        }

        /* Alur Section */
        .alur-img-container {
            cursor: pointer;
            transition: transform 0.3s;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alur-img-container:hover {
            transform: scale(1.02);
        }

        .alur-slide-img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            background: #fff;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <?php if ($navLogo = get_setting('school_logo')): ?>
                    <img src="<?= BASE_URL . $navLogo ?>" alt="Logo" height="40" class="me-2">
                    <span class="text-primary fw-bold d-none d-md-inline">PMBM Online</span>
                <?php else: ?>
                    <i class="fas fa-school text-primary me-2 fa-lg"></i>
                    <span class="text-primary fw-bold">PMBM Online</span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($show_countdown): ?>
                        <li class="nav-item">
                            <span class="badge bg-warning text-dark me-2">
                                <i class="fas fa-clock me-1"></i> Segera Dibuka
                            </span>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="#alur"><i class="fas fa-random small"></i> Alur</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#info"><i class="fas fa-info-circle small"></i>
                            Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="#syarat"><i class="fas fa-clipboard-list small"></i>
                            Syarat</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak"><i class="fas fa-phone small"></i>
                            Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Slider Background -->
        <div class="hero-slider-container">
            <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel"
                data-bs-interval="3000">
                <div class="carousel-inner h-100">
                    <?php if (count($sliders) > 0): ?>
                        <?php foreach ($sliders as $index => $slide): ?>
                            <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                <div class="hero-bg-img"
                                    style="background-image: url('<?= BASE_URL . $slide['image_path'] ?>');">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default Image if no sliders -->
                        <div class="carousel-item h-100 active">
                            <div class="hero-bg-img"
                                style="background-image: url('<?= BASE_URL ?>assets/img/hero-illustration.png');"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="hero-overlay"></div>
        <div class="hero-content" data-aos="fade-up">

            <!-- School Logo Center -->
            <!-- School Logo Center -->
            <div class="mb-4">
                <?php if ($headerLogo = get_setting('header_logo')): ?>
                    <img src="<?= BASE_URL . $headerLogo ?>" alt="Logo Header"
                        style="max-height: 120px; width: auto; filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));">
                <?php else: ?>
                    <!-- Using FontAwesome icon as logo placeholder since actual logo file logic is generic -->
                    <i class="fas fa-school fa-5x text-warning" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));"></i>
                <?php endif; ?>
            </div>

            <h1 class="hero-title"><?= strtoupper(get_setting('hero_title', 'SELAMAT DATANG DI WEBSITE PMBM')) ?></h1>
            <h2 class="hero-subtitle mb-5 opacity-75"><?= get_setting('school_name', 'MTsN 1 Kota Pekanbaru') ?></h2>

            <!-- Surat Pernyataan / Download Card -->
            <?php if ($ppdb_status == 'belum' && $show_countdown): ?>
                <!-- Countdown Display (Replaces Download Card when Not Open) -->
                <div class="countdown-hero" data-aos="zoom-in">
                    <h5 class="text-white text-uppercase ls-2 mb-3"><i class="fas fa-clock text-warning me-2"></i>
                        <?= $countdown_label ?></h5>
                    <div class="d-flex justify-content-center gap-4">
                        <div class="countdown-box">
                            <span id="d_days">00</span>
                            <small>Hari</small>
                        </div>
                        <div class="countdown-box">
                            <span id="d_hours">00</span>
                            <small>Jam</small>
                        </div>
                        <div class="countdown-box">
                            <span id="d_minutes">00</span>
                            <small>Menit</small>
                        </div>
                        <div class="countdown-box">
                            <span id="d_seconds">00</span>
                            <small>Detik</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="download-card">
                    <div class="download-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="download-text text-start">
                        <h5>SURAT PERNYATAAN</h5>
                        <a href="<?= BASE_URL ?>surat_keterangan.php" target="_blank"
                            class="stretched-link text-warning">DOWNLOAD <i class="fas fa-download ms-1"></i></a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-btns">
                <?php if ($show_register): ?>
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-action btn-purple">
                        <i class="fas fa-edit"></i> DAFTAR SEKARANG
                    </a>
                <?php endif; ?>

                <a href="#info" class="btn btn-action btn-green">
                    <i class="fas fa-file-alt"></i> INFORMASI PENDAFTARAN
                </a>

                <?php if ($show_login): ?>
                    <a href="<?= BASE_URL ?>login_siswa.php" class="btn btn-action btn-red">
                        <i class="fas fa-sign-in-alt"></i> MASUK MURID
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </section>

    </section>

    <!-- Alur Section -->
    <section id="alur" class="py-5 bg-light">
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
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#alurCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            Belum ada informasi alur pendaftaran yang diunggah.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Zoom Modal -->
    <div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body p-0 position-relative text-center">
                    <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-white rounded-circle opacity-100"
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 1051;"></button>
                    <img src="" id="zoomImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
                    <h5 id="zoomCaption" class="text-white mt-2 text-shadow"></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Info / Brochure Section -->
    <section id="info" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Informasi & Brosur</h2>
                <div class="d-flex justify-content-center">
                    <div style="width: 50px; height: 3px; background: var(--primary-green);"></div>
                </div>
            </div>

            <!-- Dynamic Panduan & Brosur Buttons from Database -->
            <?php
            // Reuse existing logic to fetch brochures
            try {
                $stmt_panduan = $pdo->query("SELECT * FROM panduan_brosur WHERE is_active = 1 ORDER BY urutan ASC, id ASC");
                $panduan_items = $stmt_panduan->fetchAll();
            } catch (Exception $e) {
                $panduan_items = [];
            }
            ?>

            <?php if (!empty($panduan_items)): ?>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <?php foreach ($panduan_items as $item): ?>
                        <?php
                        // Determine URL logic
                        if ($item['tipe'] == 'video' && !empty($item['video_url'])) {
                            $url = $item['video_url'];
                            $icon = 'fa-play-circle';
                        } elseif ($item['tipe'] == 'file' && !empty($item['file_path'])) {
                            $url = BASE_URL . $item['file_path'];
                            $icon = $item['icon_class'] ?? 'fa-book-open';
                        } else {
                            continue;
                        }

                        // Map colors
                        $colorMap = [
                            'primary' => 'btn-primary',
                            'success' => 'btn-success',
                            'danger' => 'btn-danger',
                            'warning' => 'btn-warning',
                            'info' => 'btn-info',
                            'dark' => 'btn-dark'
                        ];
                        $btnClass = $colorMap[$item['color_class']] ?? 'btn-primary';
                        ?>

                        <a href="<?= $url ?>" target="_blank"
                            class="btn <?= $btnClass ?> rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                            <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($item['judul']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">Belum ada informasi tambahan yang tersedia.</p>
            <?php endif; ?>

            <!-- Info Slider Section -->
            <div class="row justify-content-center mt-5" data-aos="fade-up">
                <div class="col-lg-10">
                    <?php if (count($infos) > 0): ?>
                        <div id="infoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <!-- Indicators -->
                            <?php if (count($infos) > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($infos as $index => $info): ?>
                                        <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="<?= $index ?>"
                                            class="<?= $index === 0 ? 'active' : '' ?>" aria-current="true"
                                            aria-label="Slide <?= $index + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="carousel-inner rounded-4 shadow-sm">
                                <?php foreach ($infos as $index => $info): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="alur-img-container"
                                            onclick="showZoom('<?= BASE_URL . $info['image_path'] ?>', '<?= htmlspecialchars($info['title']) ?>')">
                                            <img src="<?= BASE_URL . $info['image_path'] ?>"
                                                class="d-block w-100 alur-slide-img"
                                                alt="<?= htmlspecialchars($info['title']) ?>">
                                            <?php if (!empty($info['title'])): ?>
                                                <div
                                                    class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 py-2">
                                                    <h5><?= htmlspecialchars($info['title']) ?></h5>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($infos) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#infoCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#infoCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"
                                        style="background-size: 50%"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <!-- Floating WhatsApp -->
    <?php
    $wa_number = get_setting('contact_wa', '6281234567890');
    // Sanitize
    $wa_number = preg_replace('/[^0-9]/', '', $wa_number);
    if (substr($wa_number, 0, 1) == '0') {
        $wa_number = '62' . substr($wa_number, 1);
    }
    ?>
    <a href="https://wa.me/<?= $wa_number ?>?text=Assalamualaikum,%20saya%20ingin%20bertanya%20tentang%20PPDB"
        target="_blank" class="fab-whatsapp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 text-center mt-auto" id="kontak">
        <div class="container">
            <p class="mb-1 fw-bold">&copy; <?= date('Y') ?> <?= get_setting('school_name', 'MTsN 1 Kota Pekanbaru') ?>
            </p>
            <small class="text-white-50">Jl. Amal Hamzah, Simpang Empat, Kota Pekanbaru</small>
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

        <?php if ($show_countdown && isset($target_date)): ?>
                // Countdown Script
                (function () {
                    const targetDate = new Date("<?= date('Y-m-d\TH:i:s', strtotime($target_date)) ?>").getTime();

                    const timer = setInterval(function () {
                        const now = new Date().getTime();
                        const distance = targetDate - now;

                        if (distance < 0) {
                            clearInterval(timer);
                            // Reload to update status automatically
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

        // Zoom Modal Function
        function showZoom(src, title) {
            document.getElementById('zoomImage').src = src;
            document.getElementById('zoomCaption').innerText = title;
            var myModal = new bootstrap.Modal(document.getElementById('zoomModal'));
            myModal.show();
        }
    </script>
</body>

</html>