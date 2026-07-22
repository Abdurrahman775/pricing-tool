const API = '/api';

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const user = await checkAuth();
        document.getElementById('profileName').value = user.name || '';
        document.getElementById('profileEmail').value = user.email || '';
    } catch {}

    document.getElementById('profileForm').addEventListener('submit', handleProfileUpdate);
    document.getElementById('passwordForm').addEventListener('submit', handlePasswordUpdate);

    await loadConfigsForExport();
    document.getElementById('exportConfigSelect').addEventListener('change', toggleExportBtn);
    document.getElementById('exportConfigBtn').addEventListener('click', handleExport);
    document.getElementById('importFileInput').addEventListener('change', toggleImportBtn);
    document.getElementById('importConfigBtn').addEventListener('click', handleImport);
});

async function handleProfileUpdate(e) {
    e.preventDefault();
    const name = document.getElementById('profileName').value.trim();
    if (!name) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Name is required' });
        return;
    }
    try {
        const res = await fetch(`${API}/auth/update_profile.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ action: 'profile', name }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Saved!', text: 'Profile updated successfully.', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.error || 'Unknown error' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

async function handlePasswordUpdate(e) {
    e.preventDefault();
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    if (!current || !newPass) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Both passwords are required' });
        return;
    }
    if (newPass.length < 8) {
        Swal.fire({ icon: 'warning', title: 'Too short', text: 'New password must be at least 8 characters' });
        return;
    }
    const result = await Swal.fire({
        title: 'Change Password?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Update',
    });
    if (!result.isConfirmed) return;
    try {
        const res = await fetch(`${API}/auth/update_profile.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ action: 'password', current_password: current, new_password: newPass }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Password Updated!', timer: 1500, showConfirmButton: false });
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.error || 'Unknown error' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

async function loadConfigsForExport() {
    try {
        const res = await fetch(`${API}/config/list_configs.php`, {
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });
        const data = await res.json();
        if (data.success && Array.isArray(data.data)) {
            const sel = document.getElementById('exportConfigSelect');
            data.data.forEach(cfg => {
                const opt = document.createElement('option');
                opt.value = cfg.id;
                opt.textContent = cfg.name;
                sel.appendChild(opt);
            });
        }
    } catch {
        const sel = document.getElementById('exportConfigSelect');
        const opt = document.createElement('option');
        opt.textContent = 'Failed to load configurations';
        opt.disabled = true;
        sel.appendChild(opt);
    }
}

function toggleExportBtn() {
    const sel = document.getElementById('exportConfigSelect');
    document.getElementById('exportConfigBtn').disabled = !sel.value;
}

function toggleImportBtn() {
    document.getElementById('importConfigBtn').disabled = !document.getElementById('importFileInput').files.length;
}

async function handleExport() {
    const configId = document.getElementById('exportConfigSelect').value;
    if (!configId) return;
    try {
        const res = await fetch(`${API}/config/export`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ config_id: parseInt(configId) }),
        });
        if (!res.ok) {
            const err = await res.json();
            Swal.fire({ icon: 'error', title: 'Export Failed', text: err.error || 'Unknown error' });
            return;
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `pricing_config_${configId}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        Swal.fire({ icon: 'success', title: 'Exported!', timer: 1500, showConfirmButton: false });
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

async function handleImport() {
    const fileInput = document.getElementById('importFileInput');
    const file = fileInput.files[0];
    if (!file) return;

    const result = await Swal.fire({
        title: 'Import Config?',
        text: 'This will create a new pricing configuration from the selected file.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Import',
    });
    if (!result.isConfirmed) return;

    try {
        const text = await file.text();
        const config = JSON.parse(text);

        const res = await fetch(`${API}/config/import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(config),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Imported!', text: 'Config imported successfully.', timer: 1500, showConfirmButton: false });
            fileInput.value = '';
            toggleImportBtn();
        } else {
            Swal.fire({ icon: 'error', title: 'Import Failed', text: data.error || 'Unknown error' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Invalid File', text: 'Could not parse JSON file.' });
    }
}
