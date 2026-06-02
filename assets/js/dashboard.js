// ============================================
// DASHBOARD JAVASCRIPT
// ============================================

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
        sidebar.classList.toggle('collapsed');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    if (window.innerWidth <= 900 && sidebar && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // Animate stat numbers
    document.querySelectorAll('.sc-num').forEach(el => {
        const target = parseInt(el.textContent);
        if (isNaN(target) || target === 0) return;
        let current = 0;
        const step = Math.ceil(target / 20);
        const timer = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 50);
    });

    // Success / error from URL params
    const params = new URLSearchParams(window.location.search);
    if (params.get('success') === '1') {
        showToast('✅ Pendaftaran berhasil dikirim!', 'success');
    }
    if (params.get('already') === '1') {
        showToast('⚠️ Kamu sudah mendaftar beasiswa ini.', 'warning');
    }
});

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        z-index: 9999;
        animation: slideUp 0.3s ease;
        box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 360px;
    `;
    if (type === 'success') {
        toast.style.background = 'rgba(16,185,129,0.15)';
        toast.style.border = '1px solid rgba(16,185,129,0.3)';
        toast.style.color = '#34d399';
    } else {
        toast.style.background = 'rgba(245,158,11,0.15)';
        toast.style.border = '1px solid rgba(245,158,11,0.3)';
        toast.style.color = '#fbbf24';
    }
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.4s, transform 0.4s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// Notification Dropdown Logic
function toggleNotif() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('notifWrapper');
    const dropdown = document.getElementById('notifDropdown');
    if (wrapper && dropdown && !wrapper.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Mark all as read via AJAX
function markAllRead() {
    fetch('?mark_read=1')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove badge
                const badge = document.getElementById('badgeCount');
                if (badge) badge.remove();
                
                // Remove unread styling from items
                document.querySelectorAll('.notif-dd-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                
                // Hide mark as read button
                const markBtn = document.querySelector('.notif-mark-btn');
                if (markBtn) markBtn.remove();
            }
        });
}
