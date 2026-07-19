// Intake Module — Project intake form with live pricing preview

const API = '/api';
let configs = [];
let tiers = [];
let integrations = [];
let platformMultipliers = [];
let configCurrency = '₦';
let calcTimer = null;
let editProjectId = 0;
let editProject = null;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        await checkAuth();

        const params = new URLSearchParams(window.location.search);
        editProjectId = parseInt(params.get('edit')) || 0;
        if (editProjectId) {
            const res = await fetch(`${API}/projects/get_project.php?id=${editProjectId}`, {
                headers: { 'X-CSRF-Token': getCsrfToken() },
            });
            const data = await res.json();
            if (data.success) editProject = data.data.project;
        }

        await loadConfigs(editProject?.pricing_config_id);
        loadIntegrations();
        loadPlatforms();

        if (editProject) {
            populateEditForm();
        }

        initEventListeners();
        if (!editProject) {
            addScreenRow();
        }

        // Update page title for edit mode
        if (editProject) {
            document.title = 'Pricing Tool — Edit Project';
            const h1 = document.querySelector('h1');
            if (h1) h1.textContent = 'Edit Project';
        }
    } catch {}
});

async function loadConfigs(selectConfigId) {
    const res = await fetch(`${API}/config/list_configs.php`, {
        headers: { 'X-CSRF-Token': getCsrfToken() },
    });
    const data = await res.json();
    if (!data.success) return;

    configs = data.data.configs || [];
    const sel = document.getElementById('configSelect');
    const configWarning = document.getElementById('noConfigWarning');

    if (configs.length === 0) {
        sel.innerHTML = '<option value="">No configurations available</option>';
        sel.disabled = true;
        if (configWarning) configWarning.classList.remove('hidden');
        return;
    }

    sel.innerHTML = configs.map(c =>
        `<option value="${c.id}">${escHtml(c.name)} (${c.currency}${parseFloat(c.base_rate).toLocaleString()})</option>`
    ).join('');
    sel.disabled = false;
    if (configWarning) configWarning.classList.add('hidden');

    if (selectConfigId && configs.find(c => c.id == selectConfigId)) {
        sel.value = selectConfigId;
    } else if (configs.length) {
        sel.value = configs[0].id;
    }
    if (configs.length) {
        await loadConfigDetails(parseInt(sel.value));
    }
    sel.addEventListener('change', () => loadConfigDetails(parseInt(sel.value)));
}

async function loadConfigDetails(configId) {
    const res = await fetch(`${API}/config/get_config.php?id=${configId}`, {
        headers: { 'X-CSRF-Token': getCsrfToken() },
    });
    const data = await res.json();
    if (!data.success || !data.data.config) return;

    const cfg = data.data.config;
    tiers = cfg.complexity_tiers || [];
    integrations = cfg.integrations || [];
    platformMultipliers = cfg.platform_multipliers || [];
    configCurrency = cfg.currency || '₦';

    loadIntegrations();
    loadPlatforms();
    recalculate();
}

function loadIntegrations() {
    const container = document.getElementById('integrationsList');
    const checked = getSelectedIntegrations();

    container.innerHTML = integrations.map(int =>
        `<label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" value="${int.id}" ${checked.includes(int.id) ? 'checked' : ''} class="int-checkbox rounded text-indigo-600">
            <span class="text-gray-700 text-sm">${escHtml(int.name)} (${int.points} pts)</span>
        </label>`
    ).join('');

    document.querySelectorAll('.int-checkbox').forEach(cb =>
        cb.addEventListener('change', recalculate)
    );
}

function getSelectedIntegrations() {
    return Array.from(document.querySelectorAll('.int-checkbox:checked')).map(cb => parseInt(cb.value));
}

function loadPlatforms() {
    const container = document.getElementById('platformsList');
    container.innerHTML = platformMultipliers.map(pm =>
        `<label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border ${pm.platform === 'Web' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'} hover:border-indigo-300 transition-colors platform-label">
            <input type="checkbox" value="${escHtml(pm.platform)}" ${pm.platform === 'Web' ? 'checked' : ''} class="platform-checkbox sr-only">
            <span class="text-sm font-medium ${pm.platform === 'Web' ? 'text-indigo-600' : 'text-gray-600'}">${escHtml(pm.platform)}</span>
            <span class="text-xs text-gray-400">×${pm.multiplier}</span>
        </label>`
    ).join('');

    document.querySelectorAll('.platform-checkbox').forEach(cb =>
        cb.addEventListener('change', function () {
            const label = this.closest('.platform-label');
            if (this.checked) {
                label.classList.add('border-indigo-600', 'bg-indigo-50');
                label.querySelector('span').classList.add('text-indigo-600');
            } else {
                label.classList.remove('border-indigo-600', 'bg-indigo-50');
                label.querySelector('span').classList.remove('text-indigo-600');
            }
            updatePlatformInfo();
            recalculate();
        })
    );

    updatePlatformInfo();
}

function updatePlatformInfo() {
    const container = document.getElementById('platformsList');
    let info = container.querySelector('.platform-info');
    if (!info) {
        info = document.createElement('p');
        info.className = 'platform-info text-xs text-gray-400 mt-2';
        container.appendChild(info);
    }

    const checked = Array.from(document.querySelectorAll('.platform-checkbox:checked')).map(cb => cb.value);
    if (checked.length <= 1) {
        info.textContent = checked.length === 1
            ? `Platform multiplier: ×${platformMultipliers.find(p => p.platform === checked[0])?.multiplier || 1}`
            : 'Select a platform to apply its multiplier.';
        return;
    }

    const muls = checked.map(p => platformMultipliers.find(pm => pm.platform === p)?.multiplier || 1);
    const maxMul = Math.max(...muls);
    const maxPlatforms = checked.filter(p => (platformMultipliers.find(pm => pm.platform === p)?.multiplier || 1) === maxMul);
    info.innerHTML = `${checked.length} platforms selected — using highest multiplier <strong>×${maxMul}</strong> <span class="text-emerald-600">(from ${escHtml(maxPlatforms.join(', '))})</span>`;
}

function initEventListeners() {
    document.getElementById('addScreenBtn').addEventListener('click', () => addScreenRow());
    document.getElementById('screensBody').addEventListener('change', recalculate);
    document.getElementById('screensBody').addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-screen')) {
            e.target.closest('tr')?.remove();
            recalculate();
        }
    });
    document.getElementById('includeMaintenance').addEventListener('change', function () {
        document.getElementById('maintenanceFields').classList.toggle('hidden', !this.checked);
        recalculate();
    });
    document.getElementById('maintModel').addEventListener('change', recalculate);
    document.getElementById('maintValue').addEventListener('input', recalculate);
    document.getElementById('intakeForm').addEventListener('submit', handleSave);
    document.getElementById('addCustomIntBtn').addEventListener('click', () => addCustomIntegration());
}

function addScreenRow(data) {
    data = data || {};
    const tbody = document.getElementById('screensBody');
    const row = document.createElement('tr');
    row.className = 'border-b border-gray-100';
    row.innerHTML = `
        <td class="py-2 pr-4">
            <input type="text" class="screen-name w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="e.g. Login Screen" value="${escHtml(data.name || '')}">
        </td>
        <td class="py-2 pr-4">
            <select class="screen-tier w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                ${tiers.map(t => `<option value="${t.id}" ${t.id == data.complexity_tier_id ? 'selected' : ''}>${escHtml(t.name)} (×${t.multiplier})</option>`).join('')}
            </select>
        </td>
        <td class="py-2 pr-4">
            <input type="text" class="screen-notes w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="Optional notes" value="${escHtml(data.notes || '')}">
        </td>
        <td class="py-2">
            <button type="button" class="remove-screen text-red-400 hover:text-red-600 text-sm">Remove</button>
        </td>
    `;
    tbody.appendChild(row);
    row.querySelectorAll('input, select').forEach(el => el.addEventListener('change', recalculate));
}

function addCustomIntegration(data) {
    data = data || {};
    const container = document.getElementById('customInts');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2';
    div.innerHTML = `
        <input type="text" class="custom-int-name w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="Integration name" value="${escHtml(data.name || '')}">
        <input type="number" class="custom-int-pts w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="Pts" min="0" value="${data.points || 0}">
        <button type="button" class="remove-custom-int text-red-400 hover:text-red-600 text-sm">Remove</button>
    `;
    container.appendChild(div);
    div.querySelectorAll('input').forEach(el => el.addEventListener('input', recalculate));
    div.querySelector('.remove-custom-int').addEventListener('click', () => {
        div.remove();
        recalculate();
    });
}

function populateEditForm() {
    const p = editProject;
    if (!p) return;

    document.getElementById('clientName').value = p.client_name || '';
    document.getElementById('projectName').value = p.project_name || '';

    // Screens
    if (p.screens && p.screens.length) {
        p.screens.forEach(s => addScreenRow({
            name: s.name,
            complexity_tier_id: s.complexity_tier_id,
            notes: s.notes || '',
        }));
    }

    // Integration checkboxes
    const projIntIds = (p.integrations || [])
        .filter(i => i.integration_id)
        .map(i => parseInt(i.integration_id));
    document.querySelectorAll('.int-checkbox').forEach(cb => {
        cb.checked = projIntIds.includes(parseInt(cb.value));
    });

    // Custom integrations
    (p.integrations || [])
        .filter(i => i.custom_name)
        .forEach(i => addCustomIntegration({ name: i.custom_name, points: i.custom_points }));

    // Platform checkboxes
    const projPlatforms = p.platforms || [];
    document.querySelectorAll('.platform-checkbox').forEach(cb => {
        if (projPlatforms.includes(cb.value)) {
            cb.checked = true;
            const label = cb.closest('.platform-label');
            if (label) {
                label.classList.add('border-indigo-600', 'bg-indigo-50');
                label.querySelector('span').classList.add('text-indigo-600');
            }
        }
    });

    recalculate();
}

function getScreens() {
    const rows = document.querySelectorAll('#screensBody tr');
    return Array.from(rows).map(row => ({
        name: row.querySelector('.screen-name')?.value || '',
        complexity_tier_id: parseInt(row.querySelector('.screen-tier')?.value || '0'),
        notes: row.querySelector('.screen-notes')?.value || '',
    })).filter(s => s.name.trim());
}

function getPlatforms() {
    return Array.from(document.querySelectorAll('.platform-checkbox:checked')).map(cb => cb.value);
}

function getMaintenance() {
    const checked = document.getElementById('includeMaintenance').checked;
    if (!checked) return null;
    return {
        model: document.getElementById('maintModel').value,
        value: parseFloat(document.getElementById('maintValue').value) || 0,
    };
}

function getCustomIntegrations() {
    const items = document.querySelectorAll('.custom-int-name');
    return Array.from(items).map(el => ({
        custom_name: el.value || '',
        custom_points: parseInt(el.closest('div')?.querySelector('.custom-int-pts')?.value) || 0,
    })).filter(i => i.custom_name.trim());
}

function recalculate() {
    clearTimeout(calcTimer);
    calcTimer = setTimeout(doCalculate, 300);
}

async function doCalculate() {
    const screens = getScreens();
    const integrationIds = getSelectedIntegrations();
    const customInts = getCustomIntegrations();
    const platforms = getPlatforms();
    const maintenance = getMaintenance();
    const configId = document.getElementById('configSelect').value;

    if (!configId || screens.length === 0) {
        document.getElementById('pricingLoader').classList.remove('hidden');
        document.getElementById('pricingContent').classList.add('hidden');
        return;
    }

    const payload = {
        config_id: parseInt(configId),
        screens,
        integration_ids: [...integrationIds, ...customInts],
        platforms,
        maintenance,
    };

    try {
        const res = await fetch(`${API}/quotes/calculate_price.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!data.success) return;

        renderPricing(data.data.breakdown);
    } catch {}
}

function renderPricing(bd) {
    document.getElementById('pricingLoader').classList.add('hidden');
    const container = document.getElementById('pricingContent');
    container.classList.remove('hidden');

    const cur = bd.currency;
    let html = '';

    // Screens breakdown
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="text-indigo-600 font-medium mb-2">Screens</div>`;
    bd.screen_breakdown.forEach(s => {
        html += `<div class="flex justify-between text-gray-700 py-0.5"><span>${escHtml(s.name)} <span class="text-indigo-600">(${escHtml(s.tier)})</span></span><span>${s.points} pts</span></div>`;
    });
    html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Total Screen Points</span><span>${bd.total_screen_points} pts</span></div>`;
    html += `</div>`;

    // Integrations
    if (bd.integration_breakdown.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
        html += `<div class="text-indigo-600 font-medium mb-2">Integrations</div>`;
        bd.integration_breakdown.forEach(i => {
            html += `<div class="flex justify-between text-gray-700 py-0.5"><span>${escHtml(i.name)}</span><span>+${i.points} pts</span></div>`;
        });
        html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Total Integration Points</span><span>+${bd.total_integration_points} pts</span></div>`;
        html += `</div>`;
    }

    // Subtotal + Platform
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Subtotal (screens + integrations)</span><span>${bd.subtotal} pts</span></div>`;
    if (bd.platform_breakdown && bd.platform_breakdown.length) {
        html += `<div class="text-indigo-600 font-medium mt-3 mb-1">Selected Platforms</div>`;
        const maxMul = Math.max(...bd.platform_breakdown.map(p => parseFloat(p.multiplier)));
        bd.platform_breakdown.forEach(p => {
            const isMax = parseFloat(p.multiplier) === maxMul;
            html += `<div class="flex items-center justify-between py-0.5 ${isMax ? 'text-emerald-700 font-medium' : 'text-gray-500'}"><span>${escHtml(p.platform)} <span class="text-xs">(×${p.multiplier})</span>${isMax ? ' <span class="text-xs text-emerald-600">← applied</span>' : ''}</span></div>`;
        });
    }
    html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Platform Multiplier</span><span>×${bd.platform_multiplier}</span></div>`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Adjusted Subtotal</span><span>${bd.adjusted_subtotal} pts</span></div>`;
    html += `</div>`;

    // Base price
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Base Rate</span><span>${cur}${Number(bd.base_rate).toLocaleString()}/pt</span></div>`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Base Price</span><span>${cur}${Number(bd.base_price).toLocaleString()}</span></div>`;
    html += `</div>`;

    // Packages
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="text-indigo-600 font-medium mb-2">Package Options</div>`;
    bd.packages.forEach(pkg => {
        html += `<div class="flex justify-between text-gray-700 py-1"><span>${escHtml(pkg.name)} <span class="text-indigo-600">(×${pkg.multiplier})</span></span><span class="font-semibold text-indigo-600">${cur}${Number(pkg.price).toLocaleString()}</span></div>`;
    });
    html += `</div>`;

    // Maintenance
    if (bd.maintenance) {
        html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
        html += `<div class="flex justify-between text-gray-700"><span>Maintenance</span><span class="text-emerald-600">${cur}${Number(bd.maintenance.per_month).toLocaleString()}/mo</span></div>`;
        html += `</div>`;
    }

    container.innerHTML = html;
}

async function handleSave(e) {
    e.preventDefault();

    const clientName = document.getElementById('clientName').value.trim();
    const projectName = document.getElementById('projectName').value.trim();
    const configId = document.getElementById('configSelect').value;
    const screens = getScreens();
    const platforms = getPlatforms();
    const maintenance = getMaintenance();

    if (!clientName || !projectName) {
        Swal.fire({ icon: 'warning', title: 'Required fields', text: 'Client name and project name are required' });
        return;
    }
    if (screens.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Screens required', text: 'Add at least one screen' });
        return;
    }

    const isEdit = !!editProject;
    const verb = isEdit ? 'Update' : 'Save';

    const result = await Swal.fire({
        title: isEdit ? 'Update Project?' : 'Save Project?',
        text: isEdit ? `Update quote for "${projectName}"` : `Create quote for "${projectName}"`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: verb,
    });
    if (!result.isConfirmed) return;

    const integrationIds = [...getSelectedIntegrations(), ...getCustomIntegrations()];

    const payload = {
        pricing_config_id: parseInt(configId),
        client_name: clientName,
        project_name: projectName,
        notes: '',
        screens,
        integration_ids: integrationIds,
        platforms,
        maintenance,
    };

    if (isEdit) {
        payload.project_id = editProjectId;
    }

    const endpoint = isEdit
        ? `${API}/projects/update_project.php`
        : `${API}/projects/create_project.php`;

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: isEdit ? 'Project Updated!' : 'Project Created!',
                text: isEdit ? 'Changes saved successfully.' : 'You can now select a package and generate documents.',
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                const id = isEdit ? editProjectId : data.data.project_id;
                window.location.href = `/quote.php?id=${id}`;
            });
        } else {
            Swal.fire({ icon: 'error', title: verb + ' failed', text: data.error || 'Unknown error' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
