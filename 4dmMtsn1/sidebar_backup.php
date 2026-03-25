<?php
// Function to determine active menu
function is_active($pages)
{
    $current = basename($_SERVER['PHP_SELF']);
    if (is_array($pages)) {
        return in_array($current, $pages) ? 'active' : '';
    }
    return $current == $pages ? 'active' : '';
}
?>
<div class="sidebar">
    <div class="text-center mb-4 pt-2">
        <h4 class="fw-bold"><i class="fas fa-school me-2"></i>Admin PPDB</h4>
        <small class="opacity-75">MTsN 1 Kota Pekanbaru</small>
    </div>

    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?= is_active('dashboard.php') ?>" href="<?= BASE_URL ?>admin/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <!-- Manajemen Pendaftar -->
        <li class="nav-item mt-3">
            <span class="text-uppercase small fw-bold px-3 text-white-50">Kesiswaan</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['pendaftar', 'detail_pendaftar.php']) ?>"
                href="<?= BASE_URL ?>admin/pendaftar/index.php">
                <i class="fas fa-user-graduate"></i> Data Pendaftar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['verifikasi']) ?>" href="<?= BASE_URL ?>admin/verifikasi/index.php">
                <i class="fas fa-clipboard-check"></i> Verifikasi Data
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['dokumen']) ?>" href="<?= BASE_URL ?>admin/dokumen/index.php">
                <i class="fas fa-file-alt"></i> Manajemen Dokumen
            </a>
        </li>

        <!-- Manajemen Sekolah -->
        <li class="nav-item mt-3">
            <span class="text-uppercase small fw-bold px-3 text-white-50">Sekolah</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['jalur']) ?>" href="<?= BASE_URL ?>admin/jalur/index.php">
                <i class="fas fa-road"></i> Jalur Pendaftaran
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['kuota']) ?>" href="<?= BASE_URL ?>admin/kuota/index.php">
                <i class="fas fa-chart-pie"></i> Kuota & Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['seleksi']) ?>" href="<?= BASE_URL ?>admin/seleksi/index.php">
                <i class="fas fa-filter"></i> Seleksi & Ranking
            </a>
        </li>

        <!-- Pasca Seleksi -->
        <li class="nav-item mt-3">
            <span class="text-uppercase small fw-bold px-3 text-white-50">Pasca Seleksi</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['pengumuman']) ?>" href="<?= BASE_URL ?>admin/pengumuman/index.php">
                <i class="fas fa-bullhorn"></i> Pengumuman
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['daftar_ulang']) ?>" href="<?= BASE_URL ?>admin/daftar_ulang/index.php">
                <i class="fas fa-user-check"></i> Daftar Ulang
            </a>
        </li>

        <!-- System -->
        <li class="nav-item mt-3">
            <span class="text-uppercase small fw-bold px-3 text-white-50">Sistem</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['users']) ?>" href="<?= BASE_URL ?>admin/users/index.php">
                <i class="fas fa-users-cog"></i> User Management
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['laporan']) ?>" href="<?= BASE_URL ?>admin/laporan/index.php">
                <i class="fas fa-file-excel"></i> Laporan & Export
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['settings.php']) ?>" href="<?= BASE_URL ?>admin/settings.php">
                <i class="fas fa-cogs"></i> Pengaturan Sistem
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= is_active(['log']) ?>" href="<?= BASE_URL ?>admin/log/index.php">
                <i class="fas fa-history"></i> Log Aktivitas
            </a>
        </li>

        <li class="nav-item mt-5 pt-3 border-top border-secondary">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>