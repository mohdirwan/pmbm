# Implementation Guide: Preview Data Before Submit

## JavaScript Functions yang Perlu Ditambahkan

Tambahkan functions berikut SEBELUM closing `</script>` tag di register.php (sekitar line 1190):

```javascript
// Handle submit button - Show Pakta or Preview based on status
function handleSubmit() {
    const paktaAccepted = document.getElementById('paktaAccepted').value;
    
    if (paktaAccepted === '0') {
        // Belum accept pakta, show modal pakta integritas
        const paktaModal = new bootstrap.Modal(document.getElementById('paktaIntegritasModal'));
        paktaModal.show();
    } else {
        // Sudah accept pakta, show preview
        showPreviewData();
    }
}

// Update btnPaktaIntegritas onclick untuk set flag dan update button
document.addEventListener('DOMContentLoaded', function() {
    const btnPakta = document.getElementById('btnPaktaIntegritas');
    if (btnPakta) {
        btnPakta.addEventListener('click', function() {
            // Set pakta accepted
            document.getElementById('paktaAccepted').value = '1';
            
            // Close modal
            const paktaModal = bootstrap.Modal.getInstance(document.getElementById('paktaIntegritasModal'));
            paktaModal.hide();
            
            // Show success notice
            const noticeHtml = `
                <div class="alert alert-success border-0 rounded-4 mt-3" id="paktaAcceptedNotice">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <div class="fw-bold">Pakta Integritas Diterima!</div>
                            <small>Anda telah menyetujui bahwa semua data yang diisi adalah benar. Silakan klik "Preview" untuk menyelesaikan proses.</small>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert notice before buttons
            const btnContainer = document.querySelector('#step5 .mt-4.d-flex');
            btnContainer.insertAdjacentHTML('beforebegin', noticeHtml);
            
            // Change button to Preview
            document.getElementById('btnSubmitText').textContent = 'Preview';
            document.getElementById('btnSubmitForm').innerHTML = '<i class="fas fa-eye me-2"></i><span>Preview Data</span>';
        });
    }
});

// Show preview data
function showPreviewData() {
    const form = document.getElementById('pmbmForm');
    const formData = new FormData(form);
    
    let html = '';
    
    // Step 1: Data Siswa
    html += `
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Data Siswa</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">NISN</label>
                        <div class="fw-bold">${formData.get('nisn') || '-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">NIK</label>
                        <div class="fw-bold">${formData.get('nik') || '-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Nama Lengkap</label>
                        <div class="fw-bold">${formData.get('nama_lengkap') || '-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Jenis Kelamin</label>
                        <div class="fw-bold">${formData.get('jenis_kelamin') === 'L' ? 'Laki-laki' : 'Perempuan'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Tempat, Tanggal Lahir</label>
                        <div class="fw-bold">${formData.get('tempat_lahir') || '-'}, ${formData.get('tanggal_lahir') || '-'}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Agama</label>
                        <div class="fw-bold">${formData.get('agama') || '-'}</div>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted small">Alamat</label>
                        <div class="fw-bold">${formData.get('alamat') || '-'}</div>
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
    
    // Add similar sections for Step 2 (Asal Sekolah), Step 3 (Orang Tua), Step 4 (Nilai)
    // This is a basic example - expand as needed
    
    // Insert into modal
    document.getElementById('previewDataContent').innerHTML = html;
    
    // Show modal
    const previewModal = new bootstrap.Modal(document.getElementById('previewDataModal'));
    previewModal.show();
}

// Close preview and go back to edit
function closePreview() {
    const previewModal = bootstrap.Modal.getInstance(document.getElementById('previewDataModal'));
    previewModal.hide();
}

// Submit form
function submitForm() {
    document.getElementById('pmbmForm').submit();
}
```

## Cara Implementasi:

1. Buka file `register.php`
2. Scroll ke akhir, cari `</script>` tag (sekitar line 1190)
3. SEBELUM `</script>`, paste code JavaScript di atas
4. Save file

## Testing:

1. Buka `http://localhost/pmbm/register.php`
2. Isi form sampai Step 5
3. Klik "Kirim Pendaftaran"
4. Modal Pakta Integritas muncul
5. Check checkbox, klik "Pakta Integritas"
6. **Expected:**
   - Modal close
   - Notice hijau muncul "Pakta Integritas Diterima!"
   - Tombol berubah jadi "Preview Data"
7. Klik "Preview Data"
8. Modal preview muncul dengan data yang diisi
9. Klik "Kirim Pendaftaran" untuk actual submit

## Note:

Function `showPreviewData()` di atas hanya contoh basic untuk Step 1 (Data Siswa).
Anda perlu expand untuk menampilkan semua data dari Step 2, 3, 4.

Karena kompleksitas nya tinggi, saya menyarankan test dulu dengan basic implementation ini.
