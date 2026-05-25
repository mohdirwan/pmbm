<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Fetch all registration paths with student counts
$stmt = $pdo->query("SELECT j.*, 
                    (SELECT COUNT(*) FROM pendaftar WHERE jalur_id = j.id AND (no_pendaftaran IS NOT NULL AND no_pendaftaran != '')) as total_pendaftar
                    FROM jalur_pendaftaran j 
                    ORDER BY j.nama_jalur ASC");
$jalurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Berkas Jalur - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f4f7fe;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
            transition: transform 0.3s;
        }

        .table-premium thead th {
            background: #f8faff;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #666;
            padding: 15px;
            border: none;
        }

        .table-premium tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f4f9;
        }

        .badge-count {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: bold;
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div id="loadingOverlay">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status text-primary">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="fw-bold text-primary">Sedang Menyiapkan ZIP...</h5>
        <p class="text-muted small">Mohon tunggu, proses ini mungkin memakan waktu beberapa menit tergantung jumlah
            berkas.</p>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1"><i class="fas fa-file-archive me-2"></i>Rekap Berkas Jalur
                    </h2>
                    <p class="text-muted small">Unduh semua berkas pendaftaran dalam satu file ZIP berdasarkan jalur.
                    </p>
                </div>
            </div>

            <div class="card card-premium border-0 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0">Daftar Jalur Pendaftaran</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-premium mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nama Jalur</th>
                                <th class="text-center">Total Pendaftar</th>
                                <th class="text-end pe-4">Aksi Rekap</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jalurs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada data jalur pendaftaran.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jalurs as $j): ?>
                                    <tr>
                                        <td><span class="text-muted fw-bold">#
                                                <?= $j['id'] ?>
                                            </span></td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($j['nama_jalur']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($j['keterangan'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-count bg-primary bg-opacity-10 text-primary">
                                                <?= $j['total_pendaftar'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if ($j['total_pendaftar'] > 0): ?>
                                                <button onclick="startRekap(<?= $j['id'] ?>, '<?= addslashes($j['nama_jalur']) ?>', <?= $j['total_pendaftar'] ?>)"
                                                    class="btn btn-primary rounded-pill px-4 shadow-sm">
                                                    <i class="fas fa-download me-2"></i> Rekap ZIP
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-light rounded-pill px-4 border" disabled>
                                                    <i class="fas fa-download me-2"></i> Tidak ada data
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 p-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Informasi Struktur ZIP:</h6>
                        <ul class="mb-0 small">
                            <li>File ZIP akan berisi folder-folder utama berdasarkan <strong>Nomor Pendaftaran</strong>.
                            </li>
                            <li>Di dalam tiap folder tersebut terdapat dokumen yang diunggah (Foto, KK, Akta, dll).</li>
                            <li>Hanya pendaftar yang sudah memiliki nomor pendaftaran yang akan dimasukkan ke dalam
                                rekap.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function startRekap(jalurId, namaJalur, totalPendaftar) {
            let html = '<div class="d-grid gap-2 mt-3">';
            let maxPerPart = 250;
            let parts = Math.ceil(totalPendaftar / maxPerPart);
            
            if (parts > 1) {
                html += '<p class="text-muted small mb-3">Data terlalu besar ('+totalPendaftar+' pendaftar). Silakan unduh per bagian agar proses server tidak berat:</p>';
                for(let i=0; i<parts; i++) {
                    let start = i * maxPerPart + 1;
                    let end = Math.min((i+1) * maxPerPart, totalPendaftar);
                    let targetCount = end - start + 1;
                    html += `<button onclick="Swal.close(); processRekapBatch(${jalurId}, ${start-1}, ${targetCount}, 'Part_${i+1}')" class="btn btn-primary shadow-sm"><i class="fas fa-download me-2"></i> Unduh Part ${i+1} (${start} - ${end})</button>`;
                }
            } else {
                html += `<p class="text-muted small mb-3">Total ${totalPendaftar} pendaftar akan direkap menjadi satu file ZIP.</p>`;
                html += `<button onclick="Swal.close(); processRekapBatch(${jalurId}, 0, ${totalPendaftar}, 'Full')" class="btn btn-primary shadow-sm"><i class="fas fa-download me-2"></i> Mulai Rekap Semua</button>`;
            }
            html += '</div>';

            Swal.fire({
                title: 'Konfirmasi Rekap',
                html: html,
                icon: 'info',
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal'
            });
        }

        async function processRekapBatch(jalurId, globalOffset, totalToProcess, partName) {
            const overlay = document.getElementById('loadingOverlay');
            const overlayText = overlay.querySelector('h5');
            const overlayDesc = overlay.querySelector('p');
            
            overlay.style.display = 'flex';
            overlayText.innerText = "Memulai inisialisasi ZIP...";
            overlayDesc.innerText = "Mempersiapkan sistem...";

            try {
                // 1. Inisialisasi proses
                const initRes = await fetch(`proses_rekap.php?action=init&jalur_id=${jalurId}`);
                const initData = await initRes.json();

                if (!initData.success) {
                    throw new Error(initData.error || "Gagal inisialisasi");
                }

                const sessionId = initData.session_id;
                // Append partName to zip filename if not full
                let zipFilename = initData.zip_filename;
                if(partName !== 'Full') {
                    zipFilename = zipFilename.replace('.zip', `_${partName}.zip`);
                }

                const limit = 50; // Proses 50 siswa per request batching copy file
                let localOffset = 0;

                // 2. Looping Batching
                while (localOffset < totalToProcess) {
                    let fetchOffset = globalOffset + localOffset;
                    let fetchLimit = Math.min(limit, totalToProcess - localOffset);
                    let endDisp = localOffset + fetchLimit;
                    
                    overlayText.innerText = `Memproses: ${localOffset + 1} s.d ${endDisp} dari ${totalToProcess} Siswa (${partName})`;
                    overlayDesc.innerText = `Mohon jangan tutup halaman ini. Sedang menyalin file ke folder sementara...`;
                    
                    const formData = new FormData();
                    formData.append('action', 'process_batch');
                    formData.append('jalur_id', jalurId);
                    formData.append('session_id', sessionId);
                    formData.append('offset', fetchOffset);
                    formData.append('limit', fetchLimit);

                    const batchRes = await fetch('proses_rekap.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const batchData = await batchRes.json();
                    
                    if (!batchData.success) {
                        throw new Error(batchData.error || "Gagal memproses batch");
                    }

                    localOffset += fetchLimit;
                }

                // 3. Compress Folder menjadi ZIP
                overlayText.innerText = "Mengompresi Berkas...";
                overlayDesc.innerText = "Mohon tunggu, sedang membuat file ZIP (proses ini bisa memakan waktu beberapa menit untuk file besar)...";
                
                const compressData = new FormData();
                compressData.append('action', 'compress');
                compressData.append('jalur_id', jalurId);
                compressData.append('session_id', sessionId);

                const compRes = await fetch('proses_rekap.php', {
                    method: 'POST',
                    body: compressData
                });
                
                const compData = await compRes.json();
                if (!compData.success) {
                    throw new Error(compData.error || "Gagal mengompresi ZIP");
                }

                // 4. Download hasil akhir
                overlayText.innerText = "Proses Selesai!";
                overlayDesc.innerText = "Mengunduh file ZIP...";
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                    window.location.href = `proses_rekap.php?action=download_file&session_id=${sessionId}&filename=${encodeURIComponent(zipFilename)}`;
                }, 1500);

            } catch (error) {
                console.error(error);
                overlay.style.display = 'none';
                Swal.fire('Error', 'Terjadi kesalahan saat rekap: ' + error.message, 'error');
            }
        }
    </script>
</body>

</html>