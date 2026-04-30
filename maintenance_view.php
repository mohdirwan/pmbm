<?php
// Maintenance View for PMBM
$school_name = get_setting('school_name', 'MTsN 1 Kota Pekanbaru');
$school_logo = get_setting('school_logo', 'assets/img/logo.png');
$contact_phone = get_setting('contact_phone', '');
$contact_email = get_setting('contact_email', '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan - <?= $school_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0f5132;
            --accent-color: #ffc107;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .maintenance-container {
            max-width: 600px;
            width: 90%;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }
        .logo-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            opacity: 0.03;
            z-index: -1;
        }
        .school-logo {
            max-height: 100px;
            margin-bottom: 25px;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(255, 193, 7, 0.1);
            color: var(--accent-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            animation: pulse 2s infinite;
        }
        h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }
        p {
            color: #6c757d;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .contact-item {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .contact-item:hover {
            color: var(--accent-color);
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(255, 193, 7, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
        .decor-circle {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            opacity: 0.05;
            border-radius: 50%;
            z-index: 0;
        }
        .circle-1 { top: -100px; right: -100px; }
        .circle-2 { bottom: -100px; left: -100px; }
    </style>
</head>
<body>
    <div class="decor-circle circle-1"></div>
    <div class="decor-circle circle-2"></div>

    <div class="maintenance-container">
        <?php if($school_logo): ?>
            <img src="<?= BASE_URL . $school_logo ?>" alt="Logo" class="school-logo">
        <?php endif; ?>

        <div class="icon-box">
            <i class="fas fa-info-circle"></i>
        </div>

        <h1>Pemberitahuan</h1>
        <p><?= nl2br(htmlspecialchars($maintenance_message)) ?></p>

        <div class="contact-info">
            <?php if($contact_phone): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact_phone) ?>" class="contact-item">
                    <i class="fab fa-whatsapp"></i> <?= $contact_phone ?>
                </a>
            <?php endif; ?>
            <?php if($contact_email): ?>
                <a href="mailto:<?= $contact_email ?>" class="contact-item">
                    <i class="fas fa-envelope"></i> <?= $contact_email ?>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">&copy; <?= date('Y') ?> <?= $school_name ?>. All rights reserved.</small>
        </div>
    </div>
</body>
</html>
