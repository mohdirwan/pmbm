<?php
$page_title = $_GET['title'] ?? "Halaman";
require_once 'layout_top.php';
?>

<div class="card glass-card border-0 p-5 text-center">
    <div class="mb-4">
        <i class="fas fa-tools text-warning" style="font-size: 4rem;"></i>
    </div>
    <h2 class="fw-bold">
        <?= htmlspecialchars($page_title) ?>
    </h2>
    <p class="text-muted">Halaman ini sedang dalam pengembangan oleh tim pengembang.</p>
    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">Kembali ke Dashboard</a>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>