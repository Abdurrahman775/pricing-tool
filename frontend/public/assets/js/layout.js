// Layout Module — sidebar toggle, active page

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    highlightActivePage();
    initUserGreeting();
});

function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        if (overlay) {
            overlay.classList.toggle('hidden');
        }
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Close sidebar on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('-translate-x-full') === false) {
            sidebar.classList.add('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    });
}

function highlightActivePage() {
    const current = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === current) {
            link.classList.add('sidebar-link-active');
        }
    });
}

async function initUserGreeting() {
    const el = document.getElementById('userName');
    if (!el) return;
    try {
        const res = await fetch('/api/auth/me.php', {
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });
        const data = await res.json();
        if (data.success) {
            el.textContent = data.data.user.name;
        }
    } catch {}
}
