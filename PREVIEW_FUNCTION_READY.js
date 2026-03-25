// ========================================
// COPY CODE INI KE register.php
// Replace function showPreviewData() yang ada (line 1271-1326)
// dengan code di bawah ini
// ========================================

// Show preview data
function showPreviewData() {
    const form = document.getElementById('pmbmForm');
    const formData = new FormData(form);

    let html = '';

    // STEP 1: DATA MURID - LENGKAP
    html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-primary text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Data Murid</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="text-muted small">Jalur Pendaftaran</label>
                                <div class="fw-bold">${document.querySelector('select[name="jalur_id"] option:checked')?.text || '-'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">NISN</label>
                                <div class="fw-bold">${formData.get('nisn') || '-'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">NIK</label>
                                <div class="fw-bold">${formData.get('nik') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Lengkap</label>
                                <div class="fw-bold">${formData.get('nama_lengkap') || '-'}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Jenis Kelamin</label>
                                <div class="fw-bold">${formData.get('jenis_kelamin') === 'L' ? 'Laki-laki' : 'Perempuan'}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small">Agama</label>
                                <div class="fw-bold">${formData.get('agama') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Tempat Lahir</label>
                                <div class="fw-bold">${formData.get('tempat_lahir') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Tanggal Lahir</label>
                                <div class="fw-bold">${formData.get('tanggal_lahir') || '-'}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="text-muted small">Alamat</label>
                                <div class="fw-bold">${formData.get('alamat') || '-'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Kecamatan</label>
                                <div class="fw-bold">${formData.get('kecamatan') || '-'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Kabupaten/Kota</label>
                                <div class="fw-bold">${formData.get('kabupaten_kota') || '-'}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted small">Provinsi</label>
                                <div class="fw-bold">${formData.get('provinsi') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">No. HP</label>
                                <div class="fw-bold">${formData.get('no_hp') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Email</label>
                                <div class="fw-bold">${formData.get('email') || '-'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

    // STEP 2: ASAL SEKOLAH
    html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-info text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-school me-2"></i>Asal Sekolah</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="text-muted small">Asal Sekolah</label>
                                <div class="fw-bold">${formData.get('asal_sekolah') || '-'}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="text-muted small">Alamat Sekolah</label>
                                <div class="fw-bold">${formData.get('alamat_sekolah') || '-'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

    // STEP 3: DATA ORANG TUA
    html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-success text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Orang Tua</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12"><h6 class="fw-bold text-primary mb-0">Data Ayah</h6></div>
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Ayah</label>
                                <div class="fw-bold">${formData.get('nama_ayah') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">NIK Ayah</label>
                                <div class="fw-bold">${formData.get('nik_ayah') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Pekerjaan Ayah</label>
                                <div class="fw-bold">${formData.get('pekerjaan_ayah') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Penghasilan Ayah</label>
                                <div class="fw-bold">${formData.get('penghasilan_ayah') || '-'}</div>
                            </div>
                            
                            <div class="col-12"><hr><h6 class="fw-bold text-primary mb-0">Data Ibu</h6></div>
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Ibu</label>
                                <div class="fw-bold">${formData.get('nama_ibu') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">NIK Ibu</label>
                                <div class="fw-bold">${formData.get('nik_ibu') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Pekerjaan Ibu</label>
                                <div class="fw-bold">${formData.get('pekerjaan_ibu') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Penghasilan Ibu</label>
                                <div class="fw-bold">${formData.get('penghasilan_ibu') || '-'}</div>
                            </div>
                            
                            <div class="col-12"><hr><h6 class="fw-bold text-primary mb-0">Data Wali (jika ada)</h6></div>
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Wali</label>
                                <div class="fw-bold">${formData.get('nama_wali') || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Pekerjaan Wali</label>
                                <div class="fw-bold">${formData.get('pekerjaan_wali') || '-'}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

    // STEP 4: REKAP NILAI
    html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-warning text-dark p-3">
                        <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Rekap Nilai Rapor</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Semester</th>
                                        <th class="text-end">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Semester IV (Empat) Ganjil (1)</td>
                                        <td class="fw-bold text-end">${formData.get('nilai_sem_iv_1') || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td>Semester IV (Empat) Genap (2)</td>
                                        <td class="fw-bold text-end">${formData.get('nilai_sem_iv_2') || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td>Semester V (Lima) Ganjil (1)</td>
                                        <td class="fw-bold text-end">${formData.get('nilai_sem_v_1') || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td>Semester V (Lima) Genap (2)</td>
                                        <td class="fw-bold text-end">${formData.get('nilai_sem_v_2') || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td>Semester VI (Enam) Ganjil (1)</td>
                                        <td class="fw-bold text-end">${formData.get('nilai_sem_vi_1') || '-'}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Jumlah Nilai:</th>
                                        <th class="fw-bold text-end">${document.getElementById('jumlah_nilai_display')?.value || '-'}</th>
                                    </tr>
                                    <tr>
                                        <th>Rata-Rata Nilai:</th>
                                        <th class="fw-bold text-primary text-end">${document.getElementById('rata_rata_display')?.value || '-'}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            `;

    // STEP 5: DOKUMEN UPLOAD
    html += `
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-secondary text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>Dokumen Upload</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3" id="filesPreviewContainer">
                            <div class="col-12"><p class="text-muted mb-0">Memuat daftar file...</p></div>
                        </div>
                    </div>
                </div>
            `;

    // INFO
    html += `
                <div class="alert alert-info border-0 rounded-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Periksa kembali semua data Anda. Jika ada yang salah, klik "Edit Data" untuk kembali ke form. Jika sudah benar, klik "Kirim Pendaftaran" untuk menyelesaikan proses.
                </div>
            `;

    // Insert into modal
    document.getElementById('previewDataContent').innerHTML = html;

    // Generate file previews
    setTimeout(() => {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        const container = document.getElementById('filesPreviewContainer');
        let filesHtml = '';

        if (fileInputs.length === 0) {
            filesHtml = '<div class="col-12"><p class="text-muted mb-0">Tidak ada dokumen yang diupload.</p></div>';
        } else {
            fileInputs.forEach(input => {
                const file = input.files[0];
                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                    const fileExt = fileName.split('.').pop().toUpperCase();
                    const label = input.closest('.card')?.querySelector('label')?.textContent.replace('*', '').trim() || input.name;

                    let icon = 'fa-file-alt';
                    let iconColor = 'text-primary';
                    if (fileExt === 'PDF') {
                        icon = 'fa-file-pdf';
                        iconColor = 'text-danger';
                    } else if (['JPG', 'JPEG', 'PNG'].includes(fileExt)) {
                        icon = 'fa-file-image';
                        iconColor = 'text-success';
                    }

                    filesHtml += `
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="d-flex align-items-center">
                                            <i class="fas ${icon} fa-2x ${iconColor} me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small">${label}</div>
                                                <div class="text-muted small">${fileName}</div>
                                                <div class="badge bg-info text-dark mt-1">${fileExt} - ${fileSize}</div>
                                            </div>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            `;
                }
            });

            if (filesHtml === '') {
                filesHtml = '<div class="col-12"><p class="text-muted mb-0">Tidak ada dokumen yang diupload.</p></div>';
            }
        }

        container.innerHTML = filesHtml;
    }, 100);

    // Show modal
    const previewModal = new bootstrap.Modal(document.getElementById('previewDataModal'));
    previewModal.show();
}
