// Auth Module — login, register, logout, CSRF

const API_BASE = '/api';

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function apiRequest(endpoint, data = {}) {
    const res = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken(),
        },
        body: JSON.stringify(data),
    });
    return res.json();
}

async function handleLogin(email, password) {
    const result = await apiRequest('/auth/login.php', { email, password });
    if (result.success) {
        Swal.fire({
            icon: 'success',
            title: 'Welcome back!',
            text: `Signed in as ${result.data.user.name}`,
            timer: 1500,
            showConfirmButton: false,
        }).then(() => {
            window.location.href = '/dashboard.php';
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Login failed',
            text: result.error || 'Invalid email or password',
        });
    }
    return result;
}

async function handleRegister(name, email, password) {
    const result = await apiRequest('/auth/register.php', { name, email, password });
    if (result.success) {
        Swal.fire({
            icon: 'success',
            title: 'Account created!',
            text: `Welcome, ${result.data.user.name}!`,
            timer: 1500,
            showConfirmButton: false,
        }).then(() => {
            window.location.href = '/dashboard.php';
        });
    } else {
        const msg = result.errors
            ? Object.values(result.errors).flat().join('<br>')
            : result.error;
        Swal.fire({
            icon: 'error',
            title: 'Registration failed',
            html: msg,
        });
    }
    return result;
}

async function handleLogout() {
    const result = await Swal.fire({
        title: 'Sign out?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sign out',
        cancelButtonText: 'Cancel',
    });
    if (!result.isConfirmed) return;

    const res = await apiRequest('/auth/logout.php', {});
    if (res.success) {
        Swal.fire({
            icon: 'success',
            title: 'Signed out',
            timer: 1000,
            showConfirmButton: false,
        }).then(() => {
            window.location.href = '/';
        });
    }
}

async function checkAuth() {
    const res = await fetch(`${API_BASE}/auth/me.php`, {
        headers: { 'X-CSRF-Token': getCsrfToken() },
    });
    const result = await res.json();
    if (!result.success) {
        window.location.href = '/';
        return null;
    }
    return result.data.user;
}

async function checkAuthSilent() {
    const res = await fetch(`${API_BASE}/auth/me.php`, {
        headers: { 'X-CSRF-Token': getCsrfToken() },
    });
    return res.json();
}
