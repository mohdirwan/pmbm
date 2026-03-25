<?php
$page_title = "Jadwal Ujian Seleksi";
require_once 'layout_top.php';
?>

<div class="card glass-card border-0 p-4">
    <h5 class="fw-bold mb-4">Agenda Pelaksanaan Tes Akademik</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 px-4 rounded-start">Mata Uji</th>
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4">Waktu</th>
                    <th class="py-3 px-4">Ruang / Link</th>
                    <th class="py-3 px-4 rounded-end">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt_j = $pdo->query("SELECT * FROM jadwal_ujian ORDER BY tanggal ASC");
                $all_jadwal = $stmt_j->fetchAll();
                if (empty($all_jadwal)) {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada jadwal ujian yang dipublikasikan.</td></tr>";
                }
                foreach ($all_jadwal as $j):
                    ?>
                    <tr>
                        <td class="py-3 px-4 fw-bold"><?= htmlspecialchars($j['mata_uji']) ?></td>
                        <td class="py-3 px-4 text-muted"><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                        <td class="py-3 px-4 text-muted"><?= htmlspecialchars($j['waktu']) ?></td>
                        <td class="py-3 px-4">
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                                <?= htmlspecialchars($j['lokasi']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <?php
                            $b_class = 'bg-info-subtle text-info';
                            if ($j['status'] == 'Berlangsung')
                                $b_class = 'bg-success-subtle text-success';
                            if ($j['status'] == 'Selesai')
                                $b_class = 'bg-danger-subtle text-danger';
                            ?>
                            <span class="badge <?= $b_class ?> px-3 py-2 rounded-pill">
                                <?= htmlspecialchars($j['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 border-start border-warning border-4 bg-warning bg-opacity-10 rounded-3">
        <h6 class="fw-bold"><i class="fas fa-bullhorn me-2"></i> PENTING:</h6>
        <p class="small mb-0 text-muted">Peserta wajib bersiap di depan perangkat masing-masing 15 menit sebelum waktu
            ujian dimulai. Keterlambatan dapat mengakibatkan berkurangnya waktu ujian.</p>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>