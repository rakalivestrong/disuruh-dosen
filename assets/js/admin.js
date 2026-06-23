// ============================================
// ADMIN PAGE JAVASCRIPT
// ============================================

let currentId = null;
let currentStatus = null;

function openModal(id, status, nama) {
    currentId = id;
    currentStatus = status;

    const modal = document.getElementById('modalOverlay');
    const title = document.getElementById('modalTitle');
    const desc = document.getElementById('modalDesc');
    const btn = document.getElementById('btnConfirm');
    const catatan = document.getElementById('catatanAdmin');

    if (!modal) return;

    if (status === 'diterima') {
        title.textContent = '✅ Terima Pendaftaran';
        title.style.color = '#34d399';
        desc.textContent = `Yakin ingin MENERIMA pendaftaran dari ${nama}? Mahasiswa akan mendapat notifikasi.`;
        btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
        btn.textContent = 'Ya, Terima';
    } else if (status === 'ditolak') {
        title.textContent = '❌ Tolak Pendaftaran';
        title.style.color = '#f87171';
        desc.textContent = `Yakin ingin MENOLAK pendaftaran dari ${nama}? Mahasiswa akan mendapat notifikasi.`;
        btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
        btn.textContent = 'Ya, Tolak';
    }

    catatan.value = '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('modalOverlay');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    currentId = null;
    currentStatus = null;
}

async function submitDecision() {
    if (!currentId || !currentStatus) return;

    const catatan = document.getElementById('catatanAdmin').value;
    const btn = document.getElementById('btnConfirm');

    btn.textContent = '⏳ Memproses...';
    btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', 'update_status');
        formData.append('id', currentId);
        formData.append('status', currentStatus);
        formData.append('catatan', catatan);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            const savedStatus = currentStatus;
            closeModal();

            // Tampilkan toast dulu, lalu reload halaman
            const pesanToast = savedStatus === 'diterima'
                ? '✅ Pendaftaran berhasil DITERIMA!'
                : '❌ Pendaftaran berhasil DITOLAK!';
            const tipeToast = savedStatus === 'diterima' ? 'success' : 'error';

            showToast(pesanToast, tipeToast);

            // Reload setelah 1.2 detik agar toast sempat terlihat
            setTimeout(() => location.reload(), 1200);

        } else {
            alert('Terjadi kesalahan: ' + (result.message || 'Unknown error'));
            btn.textContent = 'Konfirmasi';
            btn.disabled = false;
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
        console.error(err);
        btn.textContent = 'Konfirmasi';
        btn.disabled = false;
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px;
        padding: 14px 20px; border-radius: 12px;
        font-size: 14px; font-weight: 600; z-index: 9999;
        animation: slideUp 0.3s ease; box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        max-width: 360px;
    `;
    if (type === 'success') {
        toast.style.cssText += 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#34d399;';
    } else {
        toast.style.cssText += 'background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;';
    }
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.4s'; toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// Keyboard shortcut: ESC to close modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});