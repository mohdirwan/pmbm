<?php
/**
 * includes/popup.php
 * Pemanggil pop-up iklan terbaru yang aktif.
 */
try {
    $stmt_popup = $pdo->query("SELECT * FROM app_popup WHERE status = 1 ORDER BY id DESC LIMIT 1");
    $popup_data = $stmt_popup->fetch();
} catch (Exception $e) {
    $popup_data = null;
}

if ($popup_data):
?>
<!-- Pop-up Iklan Overlay -->
<div id="popup-ad-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 999999; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.4s ease; pointer-events: none;">
    <div id="popup-ad-content" style="position: relative; max-width: 90%; max-height: 90%; transform: scale(0.7); transition: all 0.4s ease;">
        
        <!-- Tombol Close (X) -->
        <button id="close-popup-ad" style="position: absolute; top: -20px; right: -20px; width: 45px; height: 45px; border-radius: 50%; background: #ff4d4d; color: white; border: 4px solid white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); z-index: 1000001;">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Gambar Iklan -->
        <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.5); background: white;">
            <?php if (!empty($popup_data['link'])): ?>
                <a href="<?= $popup_data['link'] ?>" target="_blank" style="display: block;">
                    <img src="<?= BASE_URL . $popup_data['image_path'] ?>" style="max-width: 100%; max-height: 80vh; display: block; object-fit: contain;">
                </a>
            <?php else: ?>
                <img src="<?= BASE_URL . $popup_data['image_path'] ?>" style="max-width: 100%; max-height: 80vh; display: block; object-fit: contain;">
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const overlay = document.getElementById('popup-ad-overlay');
    const closeBtn = document.getElementById('close-popup-ad');
    const timerValue = <?= (int)$popup_data['timer'] ?>;

    function showPopup() {
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
        overlay.style.pointerEvents = 'auto';
        document.getElementById('popup-ad-content').style.transform = 'scale(1)';
        
        if (timerValue > 0) {
            setTimeout(closePopup, timerValue);
        }
    }

    function closePopup() {
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        overlay.style.pointerEvents = 'none';
        document.getElementById('popup-ad-content').style.transform = 'scale(0.7)';
    }

    // Tampilkan pop-up setelah 2 detik halaman terbuka
    setTimeout(showPopup, 2000);

    closeBtn.addEventListener('click', closePopup);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePopup();
    });
});
</script>
<?php endif; ?>
