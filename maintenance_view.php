<?php
// Maintenance View for PMBM - Premium Version
$school_name = get_setting('school_name', 'MTsN 1 Kota Pekanbaru');
$school_logo = get_setting('school_logo', 'assets/img/logo.png');
$contact_phone = get_setting('contact_phone', '');
$contact_email = get_setting('contact_email', '');
$maintenance_bg = get_setting('maintenance_bg', '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan - <?= $school_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0f5132;
            --accent-color: #ffc107;
            --glass-bg: rgba(255, 255, 255, 0.85);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
        }

        .bg-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: <?= $maintenance_bg ? "url('".BASE_URL.$maintenance_bg."')" : "linear-gradient(135deg, #0f5132 0%, #1a8a56 100%)" ?>;
            background-size: cover;
            background-position: center;
            z-index: -1;
            transition: transform 10s linear;
        }

        /* Continuous slow zoom effect */
        .bg-wrapper.animate {
            animation: slowZoom 30s infinite alternate;
        }

        @keyframes slowZoom {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%);
            z-index: 0;
        }

        .content-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            padding: 20px;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 40px;
            padding: 50px;
            max-width: 650px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .school-logo {
            max-height: 120px;
            margin-bottom: 30px;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }

        .school-logo:hover {
            transform: scale(1.05);
        }

        .status-icon {
            width: 70px;
            height: 70px;
            background: var(--accent-color);
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.4);
            animation: pulseCustom 2s infinite;
        }

        @keyframes pulseCustom {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }

        h1 {
            color: var(--primary-color);
            font-weight: 800;
            font-size: 2.8rem;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .message-text {
            color: #444;
            font-size: 1.25rem;
            line-height: 1.6;
            font-weight: 400;
            margin-bottom: 40px;
        }

        .footer-info {
            border-top: 2px solid rgba(0,0,0,0.05);
            padding-top: 30px;
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .info-pill {
            background: rgba(15, 81, 50, 0.1);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .info-pill:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(15, 81, 50, 0.2);
        }

        .copyright {
            margin-top: 40px;
            font-size: 0.85rem;
            color: #888;
            font-weight: 500;
        }

        /* Decorative Floating Elements */
        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }

        .shape-1 { width: 300px; height: 300px; top: -150px; left: -150px; }
        .shape-2 { width: 200px; height: 200px; bottom: -100px; right: -50px; }

        @media (max-width: 768px) {
            .glass-card { padding: 35px 25px; }
            h1 { font-size: 2rem; }
            .message-text { font-size: 1.1rem; }
            .footer-info { gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="bg-wrapper animate"></div>
    <div class="overlay"></div>
    
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <div class="content-container">
        <div class="glass-card">
            <?php if($school_logo): ?>
                <img src="<?= BASE_URL . $school_logo ?>" alt="Logo" class="school-logo">
            <?php endif; ?>

            <div class="status-icon">
                <i class="fas fa-bullhorn"></i>
            </div>

            <h1>Pemberitahuan</h1>
            <p class="message-text"><?= nl2br(htmlspecialchars($maintenance_message)) ?></p>

            <div class="footer-info">
                <?php if($contact_phone): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact_phone) ?>" class="info-pill">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
                <?php if($contact_email): ?>
                    <a href="mailto:<?= $contact_email ?>" class="info-pill">
                        <i class="fas fa-envelope"></i> Email Kami
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="copyright">
                &copy; <?= date('Y') ?> <?= $school_name ?>. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        // Optional: Interaction effect on card
        document.querySelector('.glass-card').addEventListener('mousemove', (e) => {
            let xAxis = (window.innerWidth / 2 - e.pageX) / 45;
            let yAxis = (window.innerHeight / 2 - e.pageY) / 45;
            // e.currentTarget.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });
    </script>
</body>
</html>
