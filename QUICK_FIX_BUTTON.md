# QUICK FIX: Remove Button Update Code

## Problem:
Ada code yang update button text yang sudah tidak diperlukan karena button sudah statis "Lanjut ke Pakta Integritas".

## Manual Edit:

Buka file `register.php`, cari lines 1245-1249:

```javascript
// Change button to Preview
const btnSubmit = document.getElementById('btnSubmitForm');
btnSubmit.innerHTML = '<i class="fas fa-eye me-2"></i><span>Preview Data</span>';
btnSubmit.classList.remove('btn-success');
btnSubmit.classList.add('btn-info');
```

**HAPUS 5 baris tersebut.**

Line 1242-1243 tetap (jangan dihapus):
```javascript
btnContainer.insertAdjacentHTML('beforebegin', noticeHtml);
}
```

Line 1250 tetap:
```javascript
});
```

## Result After Delete:

```javascript
btnContainer.insertAdjacentHTML('beforebegin', noticeHtml);
}
});  // Langsung closing tanpa button update
```

## Optional: Update Notice Text

Di line 1234, ubah text notice jadi:
```javascript
<small>Anda telah menyetujui bahwa semua data yang diisi adalah benar. Silakan klik "Lanjut ke Pakta Integritas" lagi untuk melihat preview data pendaftaran.</small>
```

## Testing:

Setelah edit, test flow:
1. Isi form → Step 5
2. Klik "Lanjut ke Pakta Integritas"
3. Modal Pakta muncul
4. Check + klik Pakta
5. Notice hijau muncul
6. Klik "Lanjut ke Pakta Integritas" lagi
7. Modal Preview harus muncul
