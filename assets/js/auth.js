// ============================================
// AUTH PAGE JAVASCRIPT
// ============================================

function switchTab(tab) {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const alertBox = document.getElementById('alertBox');

    if (alertBox) alertBox.style.display = 'none';

    if (tab === 'login') {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
    } else {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        loginTab.classList.remove('active');
        registerTab.classList.add('active');
    }
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

// Auto-dismiss alert after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    const alert = document.getElementById('alertBox');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    }

    // Check if we should show register tab (from URL param)
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'register') switchTab('register');
});
