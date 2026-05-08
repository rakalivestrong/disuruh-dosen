// ============================================
// FORM PAGE JAVASCRIPT
// ============================================

// Format currency input (tambah titik ribuan)
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Update file upload display
function updateFileName(input, wrapperId) {
    const wrapper = document.getElementById(wrapperId);
    const content = wrapper.querySelector('.fu-content');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        if (file.size > 5 * 1024 * 1024) {
            alert('File terlalu besar! Maksimal 5MB.');
            input.value = '';
            return;
        }
        content.innerHTML = `
            <span class="fu-icon">✅</span>
            <span class="fu-filename">${file.name}</span>
            <span style="font-size:11px;color:var(--text-dim)">${sizeMB} MB</span>
        `;
        wrapper.classList.add('has-file');
    }
}

// Character count for textarea
document.addEventListener('DOMContentLoaded', () => {
    const alasanField = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');

    if (alasanField && charCount) {
        alasanField.addEventListener('input', () => {
            const len = alasanField.value.length;
            charCount.textContent = len;
            if (len < 100) {
                charCount.style.color = '#f87171';
            } else {
                charCount.style.color = '#34d399';
            }
        });
    }

    // Form submit loading state
    const form = document.getElementById('formDaftar');
    if (form) {
        form.addEventListener('submit', function(e) {
            const alasan = document.getElementById('alasan');
            if (alasan && alasan.value.length < 100) {
                e.preventDefault();
                alert('Esai motivasi minimal 100 karakter! Saat ini: ' + alasan.value.length + ' karakter.');
                alasan.focus();
                return;
            }

            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.querySelector('.btn-text').classList.add('hidden');
                btn.querySelector('.btn-loading').classList.remove('hidden');
                btn.disabled = true;
            }
        });
    }

    // IPK validation (max 4.00)
    const ipkInput = document.getElementById('ipk');
    if (ipkInput) {
        ipkInput.addEventListener('blur', () => {
            let val = parseFloat(ipkInput.value.replace(',', '.'));
            if (val > 4.0) {
                ipkInput.value = '4.00';
                alert('IPK tidak bisa lebih dari 4.00');
            }
        });
    }
});
