<?php
// Modern Tailwind Theme (Theme 2) - Full Version
?>
<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_setting('school_name', 'PMBM MTsN 1 Kota Pekanbaru') ?> 2026/2027</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL . get_setting('school_logo') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Outfit:wght@400;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-scale-in {
            animation: scale-in 0.3s ease-out;
        }

        body {
            box-sizing: border-box;
        }

        .font-heading {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.03em;
        }

        .font-body {
            font-family: 'Poppins', sans-serif;
            letter-spacing: -0.01em;
        }

        .hero-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 200% 200%;
            animation: gradient-shift 15s ease infinite;
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .blob-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(40px);
            animation: blob-morph-1 20s ease-in-out infinite;
            top: -10%;
            left: -10%;
        }

        .blob-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(240, 147, 251, 0.4) 0%, transparent 70%);
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            filter: blur(40px);
            animation: blob-morph-2 18s ease-in-out infinite;
            top: 20%;
            right: -5%;
        }

        .blob-3 {
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(0, 242, 254, 0.4) 0%, transparent 70%);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            filter: blur(40px);
            animation: blob-morph-3 22s ease-in-out infinite;
            bottom: 10%;
            left: 15%;
        }

        @keyframes blob-morph-1 {

            0%,
            100% {
                border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            33% {
                border-radius: 70% 30% 50% 50% / 30% 30% 70% 70%;
                transform: translate(50px, -30px) rotate(120deg) scale(1.1);
            }

            66% {
                border-radius: 50% 50% 30% 70% / 60% 70% 30% 40%;
                transform: translate(-30px, 40px) rotate(240deg) scale(0.9);
            }
        }

        @keyframes blob-morph-2 {

            0%,
            100% {
                border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            33% {
                border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
                transform: translate(-40px, 50px) rotate(-120deg) scale(1.15);
            }

            66% {
                border-radius: 70% 30% 40% 60% / 40% 50% 60% 50%;
                transform: translate(30px, -40px) rotate(-240deg) scale(0.95);
            }
        }

        @keyframes blob-morph-3 {

            0%,
            100% {
                border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            33% {
                border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%;
                transform: translate(40px, -50px) rotate(135deg) scale(1.08);
            }

            66% {
                border-radius: 50% 50% 50% 50% / 60% 40% 60% 40%;
                transform: translate(-50px, 30px) rotate(270deg) scale(0.92);
            }
        }

        .neon-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .glow-btn {
            position: relative;
            background: linear-gradient(135deg, #667eea, #764ba2);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5), 0 0 40px rgba(118, 75, 162, 0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glow-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.8), 0 0 60px rgba(118, 75, 162, 0.5), 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .glow-btn::before {
            content: '';
            position: absolute;
            inset: -3px;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s;
            filter: blur(10px);
        }

        .glow-btn:hover::before {
            opacity: 0.7;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .bouncy-card {
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .bouncy-card:hover {
            transform: translateY(-15px) rotate(-2deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .shine-effect {
            position: relative;
            overflow: hidden;
        }

        .shine-effect::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to right, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
            transform: rotate(45deg);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }

        .floating-icon {
            animation: float-icon 6s ease-in-out infinite;
        }

        @keyframes float-icon {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg) scale(1);
            }

            25% {
                transform: translateY(-30px) rotate(5deg) scale(1.1);
            }

            50% {
                transform: translateY(-20px) rotate(-5deg) scale(1.05);
            }

            75% {
                transform: translateY(-35px) rotate(3deg) scale(1.08);
            }
        }

        .scroll-bounce {
            animation: scroll-bounce 2s ease-in-out infinite;
        }

        @keyframes scroll-bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-15px);
            }

            60% {
                transform: translateY(-8px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 1s ease-out forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .delay-5 {
            animation-delay: 0.5s;
        }

        .timeline-tab-btn.active {
            transform: scale(1.05);
            box-shadow: 0 25px 60px rgba(245, 158, 11, 0.5);
        }

        .accordion-btn {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .accordion-btn:hover {
            transform: translateX(10px);
        }

        .confetti-bg {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.15) 2px, transparent 2px),
                radial-gradient(circle at 60% 30%, rgba(240, 147, 251, 0.15) 2px, transparent 2px),
                radial-gradient(circle at 80% 70%, rgba(79, 172, 254, 0.15) 2px, transparent 2px),
                radial-gradient(circle at 40% 80%, rgba(0, 242, 254, 0.15) 2px, transparent 2px);
            background-size: 50px 50px, 70px 70px, 60px 60px, 80px 80px;
        }
    </style>
</head>

<body class="font-body h-full overflow-auto">
    <div id="app-wrapper" class="w-full min-h-full">
        <!-- Hero Section -->
        <section class="relative min-h-screen hero-bg overflow-hidden flex items-center">
            <div class="blob-1"></div>
            <div class="blob-2"></div>
            <div class="blob-3"></div>

            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-24 left-16 text-5xl opacity-20 floating-icon" style="animation-delay: 0s;">🏆
                </div>
                <div class="absolute top-24 right-16 text-5xl opacity-20 floating-icon" style="animation-delay: 1s;">🎓
                </div>
                <div class="absolute bottom-32 left-20 text-5xl opacity-15 floating-icon" style="animation-delay: 2s;">⭐
                </div>
                <div class="absolute bottom-32 right-20 text-5xl opacity-15 floating-icon" style="animation-delay: 3s;">
                    ✨</div>
            </div>

            <div class="container mx-auto px-6 py-20 relative z-10">
                <div class="text-center max-w-5xl mx-auto">
                    <div class="mb-12 fade-in-up delay-1">
                        <div
                            class="w-32 h-32 mx-auto bg-white rounded-3xl shadow-2xl flex items-center justify-center p-4 hover:scale-105 transition-transform duration-500 border-4 border-white/30">
                            <img src="<?= BASE_URL ?>logo.png" alt="Logo"
                                class="w-full h-full object-contain rounded-2xl"
                                onerror="this.src='https://pmbm.mtsn1kotamalang.sch.id/LOGO.png';">
                        </div>
                    </div>

                    <h1
                        class="font-heading text-5xl md:text-7xl lg:text-8xl font-extrabold text-white mb-8 fade-in-up delay-2 leading-tight drop-shadow-2xl">
                        <?= explode(' ', $hero_title, 2)[0] ?> <br>
                        <span class="inline-block bg-white text-transparent bg-clip-text shine-effect">
                            <?= isset(explode(' ', $hero_title, 2)[1]) ? explode(' ', $hero_title, 2)[1] : '' ?>
                        </span>
                    </h1>

                    <p
                        class="text-xl md:text-2xl text-white/95 font-medium mb-14 fade-in-up delay-3 max-w-4xl mx-auto drop-shadow-lg leading-relaxed">
                        <?= $hero_desc ?>
                    </p>

                    <div class="flex flex-col sm:flex-row gap-5 justify-center fade-in-up delay-4 mb-20">
                        <?php if ($show_register): ?>
                            <a href="<?= $btn_link ?>"
                                class="glow-btn text-white px-12 py-5 rounded-full font-bold text-xl hover:scale-105 transition-all duration-500 shadow-2xl">
                                <?= $btn_text ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($show_login): ?>
                            <a href="<?= BASE_URL ?>login_siswa.php"
                                class="bg-white/20 backdrop-blur-md border-2 border-white/60 text-white px-12 py-5 rounded-full font-bold text-xl hover:bg-white hover:text-purple-600 hover:scale-105 transition-all duration-500 shadow-xl">
                                <?= ($ppdb_status == 'pengumuman') ? 'Cek Kelulusan' : 'Login Murid' ?>
                            </a>
                        <?php endif; ?>

                        <button onclick="scrollToSection('timeline')"
                            class="bg-white/20 backdrop-blur-md border-2 border-white/60 text-white px-12 py-5 rounded-full font-bold text-xl hover:bg-white hover:text-purple-600 hover:scale-105 transition-all duration-500 shadow-xl">
                            🗓️ Timeline
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-5 justify-center w-full fade-in-up delay-5">
                        <?php
                        try {
                            $stmt_p = $pdo->query("SELECT * FROM panduan_brosur WHERE is_active = 1 ORDER BY urutan ASC");
                            $p_items = $stmt_p->fetchAll();
                        } catch (Exception $e) {
                            $p_items = [];
                        }

                        foreach ($p_items as $item):
                            $url = ($item['tipe'] == 'video') ? $item['video_url'] : BASE_URL . $item['file_path'];
                            ?>
                            <a href="<?= $url ?>" target="_blank"
                                class="inline-block bg-white/10 backdrop-blur-md border border-white/30 text-white px-6 py-3 rounded-full font-bold text-lg hover:bg-white hover:text-purple-600 transition-all">
                                <?= htmlspecialchars($item['judul']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="fade-in-up delay-5 mt-20">
                    <div class="scroll-bounce text-white text-center opacity-80">
                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="text-sm font-bold">SCROLL UNTUK INFO LENGKAP</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section id="timeline"
            class="py-24 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 relative overflow-hidden">
            <div class="container mx-auto px-6 relative z-10">
                <div class="text-center mb-16">
                    <div
                        class="inline-flex items-center gap-3 bg-white/10 text-white px-8 py-4 rounded-full text-lg font-black mb-6 shadow-2xl">
                        <span>🗓️ AGENDA PMBM</span>
                    </div>
                    <h2 class="font-heading text-5xl md:text-7xl font-black mb-6 text-white drop-shadow-2xl">Timeline
                        Kegiatan</h2>
                </div>

                <div class="max-w-5xl mx-auto">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Jalur Prestasi -->
                        <div class="bg-white/95 rounded-3xl p-8 shadow-2xl bouncy-card border-l-8 border-yellow-400">
                            <h3 class="font-black text-2xl mb-4 text-gray-900">Jalur Prestasi & Terpadu</h3>
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 shrink-0 font-bold">
                                        1</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Pendaftaran Online</p>
                                        <p class="text-sm text-gray-600 font-medium">Februari 2026</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 shrink-0 font-bold">
                                        2</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Tes Akademik & Psikologi</p>
                                        <p class="text-sm text-gray-600 font-medium">Maret 2026</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Jalur Reguler -->
                        <div class="bg-white/95 rounded-3xl p-8 shadow-2xl bouncy-card border-l-8 border-blue-400">
                            <h3 class="font-black text-2xl mb-4 text-gray-900">Jalur Reguler</h3>
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shrink-0 font-bold">
                                        1</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Pendaftaran Online</p>
                                        <p class="text-sm text-gray-600 font-medium">Maret 2026</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600 shrink-0 font-bold">
                                        2</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Tes Akademik & Seleksi</p>
                                        <p class="text-sm text-gray-600 font-medium">April 2026</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Persyaratan Section -->
        <section id="persyaratan" class="py-24 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="font-heading text-5xl font-black text-gray-900 mb-4">Syarat Pendaftaran</h2>
                    <p class="text-xl text-gray-600 font-medium">Silakan persiapkan berkas-berkas berikut</p>
                </div>

                <div class="max-w-4xl mx-auto space-y-4">
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border-2 border-gray-100 hover:border-purple-300 transition-all cursor-pointer">
                        <div class="flex items-center gap-4">
                            <span class="text-3xl">📜</span>
                            <span class="text-lg font-bold text-gray-800">Scan Akta Kelahiran & Kartu Keluarga</span>
                        </div>
                    </div>
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border-2 border-gray-100 hover:border-purple-300 transition-all cursor-pointer">
                        <div class="flex items-center gap-4">
                            <span class="text-3xl">🖼️</span>
                            <span class="text-lg font-bold text-gray-800">Pas Foto 3x4 Background Merah</span>
                        </div>
                    </div>
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border-2 border-gray-100 hover:border-purple-300 transition-all cursor-pointer">
                        <div class="flex items-center gap-4">
                            <span class="text-3xl">🏫</span>
                            <span class="text-lg font-bold text-gray-800">Rapot Semester 1-5 & NISN valid</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-16 bg-gray-900 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 confetti-bg"></div>
            <div class="container mx-auto px-6 relative z-10 text-center">
                <div class="w-24 h-24 mx-auto bg-white rounded-3xl p-3 mb-8 shadow-2xl">
                    <img src="<?= BASE_URL ?>logo.png" alt="Logo" class="w-full h-full object-contain"
                        onerror="this.src='https://pmbm.mtsn1kotamalang.sch.id/LOGO.png';">
                </div>
                <h3 class="font-heading text-3xl font-extrabold mb-2"><?= get_setting('school_name') ?></h3>
                <p class="text-gray-400 font-medium mb-8"><?= get_setting('contact_phone') ?> |
                    <?= get_setting('contact_email') ?>
                </p>
                <p class="text-gray-500 text-sm font-medium">© <?= date('Y') ?> <?= get_setting('school_name') ?>. All
                    rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }
    </script>

    <?php if ($ppdb_status == 'belum'): ?>
        <div id="info-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center animate-scale-in relative">
                <button onclick="document.getElementById('info-popup').remove()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl">✕</button>
                <div
                    class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-4xl text-white shadow-lg">
                    ⏳</div>
                <h3 class="text-2xl font-black text-gray-900 mb-3">Pendaftaran Belum Dibuka</h3>
                <p class="text-gray-600 leading-relaxed mb-6 font-medium">Pendaftaran murid baru akan dibuka sesuai dari
                    jadwal resmi madrasah. Silakan mengecek informasi selanjutnya melalui website ini.</p>
                <button onclick="document.getElementById('info-popup').remove()"
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:scale-105 transition-all">Baik,
                    Saya Mengerti</button>
            </div>
        </div>
    <?php endif; ?>
    <?php include 'includes/popup.php'; ?>
</body>

</html>