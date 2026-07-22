// Wizard Module — 9-step pricing configuration wizard

const API = '/api/config';
let currentStep = 1;
const totalSteps = 9;
let wizardData = {};
let editConfigId = 0;

document.addEventListener('DOMContentLoaded', async () => {
    initWizard();
    initStep1();
    initDynamicRows();
    initMaintenanceToggle();

    const params = new URLSearchParams(window.location.search);
    editConfigId = parseInt(params.get('edit')) || 0;

    if (editConfigId) {
        await loadConfigForEdit(editConfigId);
    } else {
        addScreenTmplRow();
    }
});

async function loadConfigForEdit(id) {
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch(`${API}/get_config.php?id=${id}`, {
            headers: { 'X-CSRF-Token': csrf },
        });
        const data = await res.json();
        if (!data.success || !data.data.config) {
            Swal.fire({ icon: 'error', title: 'Failed to load', text: data.error || 'Could not load this configuration for editing.' });
            addScreenTmplRow();
            return;
        }
        populateWizardForEdit(data.data.config);
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server.' });
        addScreenTmplRow();
    }
}

function populateWizardForEdit(cfg) {
    document.title = 'Pricing Tool — Edit Configuration';

    const heading = document.getElementById('step1Heading');
    if (heading) heading.textContent = 'Edit Configuration';
    const desc = document.getElementById('step1Desc');
    if (desc) desc.textContent = `Update the details of "${cfg.name}" below.`;
    document.getElementById('templateChoiceGrid')?.classList.add('hidden');

    document.getElementById('configName').value = cfg.name || '';
    document.getElementById('currency').value = cfg.currency || '₦';
    document.getElementById('baseRate').value = parseFloat(cfg.base_rate) || 0;

    document.getElementById('tiersBody').innerHTML = '';
    (cfg.complexity_tiers || []).forEach(t => addRow('tiersBody', ['text', 'number'], [t.name, parseFloat(t.multiplier)]));

    document.getElementById('screenTmplBody').innerHTML = '';
    (cfg.screen_templates || []).forEach(st => addScreenTmplRow({ name: st.name, complexity_tier_name: st.tier_name }));
    if (!(cfg.screen_templates || []).length) addScreenTmplRow();

    document.getElementById('intsBody').innerHTML = '';
    (cfg.integrations || []).forEach(i => addRow('intsBody', ['text', 'number'], [i.name, parseInt(i.points)]));

    document.getElementById('platformsBody').innerHTML = '';
    (cfg.platform_multipliers || []).forEach(p => addRow('platformsBody', ['text', 'number'], [p.platform, parseFloat(p.multiplier)]));

    document.getElementById('packagesBody').innerHTML = '';
    (cfg.package_tiers || []).forEach(p => addRow('packagesBody', ['text', 'number'], [p.name, parseFloat(p.multiplier)]));

    if (cfg.maintenance) {
        const model = cfg.maintenance.model || 'percentage';
        const radio = document.querySelector(`input[name="maintenance_model"][value="${model}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
        document.getElementById('maintValue').value = cfg.maintenance.value ?? 15;
    }

    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.textContent = 'Save Changes';
}

function initWizard() {
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const saveBtn = document.getElementById('saveBtn');
    const form = document.getElementById('wizardForm');

    nextBtn.addEventListener('click', () => goNext());
    prevBtn.addEventListener('click', () => goPrev());
    form.addEventListener('submit', handleSave);

    showStep(1);
}

function showStep(step) {
    document.querySelectorAll('.step').forEach(el => el.classList.add('hidden'));
    document.querySelector(`.step[data-step="${step}"]`).classList.remove('hidden');

    document.querySelectorAll('[data-step]').forEach(el => {
        const s = parseInt(el.dataset.step);
        const isCurrentOrPast = s <= step;
        el.classList.toggle('text-indigo-600', isCurrentOrPast);
        el.classList.toggle('text-gray-400', !isCurrentOrPast);
        const circle = el.querySelector('span:first-child');
        if (circle) {
            circle.classList.toggle('bg-indigo-600', isCurrentOrPast);
            circle.classList.toggle('text-white', isCurrentOrPast);
            circle.classList.toggle('bg-gray-200', !isCurrentOrPast);
            circle.classList.toggle('text-gray-500', !isCurrentOrPast);
        }
    });

    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const saveBtn = document.getElementById('saveBtn');

    prevBtn.classList.toggle('hidden', step === 1);
    nextBtn.classList.toggle('hidden', step === totalSteps);
    saveBtn.classList.toggle('hidden', step !== totalSteps);

    if (step === 4) {
        refreshScreenTmplTierOptions();
    }

    if (step === totalSteps) {
        buildReview();
    }

    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goNext() {
    if (!validateStep(currentStep)) return;
    collectStepData(currentStep);
    showStep(currentStep + 1);
}

function goPrev() {
    collectStepData(currentStep);
    showStep(currentStep - 1);
}

function validateStep(step) {
    if (step === 1) {
        const name = document.querySelector('input[name="config_name"]');
        if (!name.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'Name required', text: 'Please enter a configuration name' });
            name.focus();
            return false;
        }
    }
    if (step === 2) {
        const rate = document.querySelector('input[name="base_rate"]');
        if (!rate.value || parseFloat(rate.value) <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid base rate', text: 'Please enter a positive base rate' });
            rate.focus();
            return false;
        }
    }
    if (step === 3) {
        const rows = document.querySelectorAll('#tiersBody tr');
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Tiers required', text: 'Add at least one complexity tier' });
            return false;
        }
    }
    if (step === 6) {
        const rows = document.querySelectorAll('#platformsBody tr');
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Platforms required', text: 'Add at least one platform' });
            return false;
        }
    }
    return true;
}

function collectStepData(step) {
    if (step === 1) {
        wizardData.template_choice = document.querySelector('input[name="template_choice"]:checked')?.value || 'default';
        wizardData.name = document.querySelector('input[name="config_name"]').value.trim();
    }
    if (step === 2) {
        wizardData.currency = document.querySelector('select[name="currency"]').value;
        wizardData.base_rate = parseFloat(document.querySelector('input[name="base_rate"]').value) || 0;
    }
    if (step === 3) {
        wizardData.complexity_tiers = [];
        const names = document.querySelectorAll('input[name="tier_name[]"]');
        const multis = document.querySelectorAll('input[name="tier_multiplier[]"]');
        names.forEach((n, i) => {
            if (n.value.trim()) {
                wizardData.complexity_tiers.push({
                    name: n.value.trim(),
                    multiplier: parseFloat(multis[i]?.value) || 1.0,
                });
            }
        });
    }
    if (step === 4) {
        wizardData.screen_templates = [];
        document.querySelectorAll('#screenTmplBody tr').forEach(row => {
            const name = row.querySelector('.screen-tmpl-name')?.value.trim();
            const tierName = row.querySelector('.screen-tmpl-tier')?.value;
            if (name && tierName) {
                wizardData.screen_templates.push({ name, complexity_tier_name: tierName });
            }
        });
    }
    if (step === 5) {
        wizardData.integrations = [];
        const names = document.querySelectorAll('input[name="int_name[]"]');
        const points = document.querySelectorAll('input[name="int_points[]"]');
        names.forEach((n, i) => {
            if (n.value.trim()) {
                wizardData.integrations.push({
                    name: n.value.trim(),
                    points: parseInt(points[i]?.value) || 0,
                });
            }
        });
    }
    if (step === 6) {
        wizardData.platform_multipliers = [];
        const names = document.querySelectorAll('input[name="platform_name[]"]');
        const multis = document.querySelectorAll('input[name="platform_multiplier[]"]');
        names.forEach((n, i) => {
            if (n.value.trim()) {
                wizardData.platform_multipliers.push({
                    platform: n.value.trim(),
                    multiplier: parseFloat(multis[i]?.value) || 1.0,
                });
            }
        });
    }
    if (step === 7) {
        wizardData.maintenance = {
            model: document.querySelector('input[name="maintenance_model"]:checked')?.value || 'percentage',
            value: parseFloat(document.querySelector('input[name="maintenance_value"]').value) || 0,
        };
    }
    if (step === 8) {
        wizardData.package_tiers = [];
        const names = document.querySelectorAll('input[name="pkg_name[]"]');
        const multis = document.querySelectorAll('input[name="pkg_multiplier[]"]');
        names.forEach((n, i) => {
            if (n.value.trim()) {
                wizardData.package_tiers.push({
                    name: n.value.trim(),
                    multiplier: parseFloat(multis[i]?.value) || 1.0,
                });
            }
        });
    }
}

function buildReview() {
    collectStepData(8);
    const container = document.getElementById('reviewContent');
    const d = wizardData;

    let html = '';
    html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium">Name:</span> <span class="text-gray-700">${escHtml(d.name)}</span></div>`;
    html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium">Currency:</span> <span class="text-gray-700">${escHtml(d.currency)}</span> &nbsp;|&nbsp; <span class="text-indigo-600 font-medium">Base Rate:</span> <span class="text-gray-700">${d.currency}${d.base_rate?.toLocaleString()}</span></div>`;

    if (d.complexity_tiers?.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium block mb-1">Complexity Tiers:</span>`;
        d.complexity_tiers.forEach(t => {
            html += `<span class="inline-block bg-indigo-100 rounded px-2 py-0.5 text-xs text-indigo-700 mr-1 mb-1">${escHtml(t.name)} ×${t.multiplier}</span>`;
        });
        html += `</div>`;
    }

    if (d.screen_templates?.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium block mb-1">Screen Templates:</span>`;
        d.screen_templates.forEach(s => {
            html += `<span class="inline-block bg-indigo-100 rounded px-2 py-0.5 text-xs text-indigo-700 mr-1 mb-1">${escHtml(s.name)} (${escHtml(s.complexity_tier_name)})</span>`;
        });
        html += `</div>`;
    }

    if (d.integrations?.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium block mb-1">Integrations:</span>`;
        d.integrations.forEach(i => {
            html += `<span class="inline-block bg-indigo-100 rounded px-2 py-0.5 text-xs text-indigo-700 mr-1 mb-1">${escHtml(i.name)} (${i.points}pts)</span>`;
        });
        html += `</div>`;
    }

    if (d.platform_multipliers?.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium block mb-1">Platforms:</span>`;
        d.platform_multipliers.forEach(p => {
            html += `<span class="inline-block bg-indigo-100 rounded px-2 py-0.5 text-xs text-indigo-700 mr-1 mb-1">${escHtml(p.platform)} ×${p.multiplier}</span>`;
        });
        html += `</div>`;
    }

    if (d.maintenance) {
        const unit = d.maintenance.model === 'percentage' ? '%' : d.currency;
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium">Maintenance:</span> <span class="text-gray-700">${d.maintenance.model === 'percentage' ? d.maintenance.value + '% of project price/month' : d.currency + d.maintenance.value + '/month flat'}</span></div>`;
    }

    if (d.package_tiers?.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4"><span class="text-indigo-600 font-medium block mb-1">Package Tiers:</span>`;
        d.package_tiers.forEach(p => {
            html += `<span class="inline-block bg-indigo-100 rounded px-2 py-0.5 text-xs text-indigo-700 mr-1 mb-1">${escHtml(p.name)} ×${p.multiplier}</span>`;
        });
        html += `</div>`;
    }

    container.innerHTML = html;
}

async function handleSave(e) {
    e.preventDefault();
    if (!validateStep(1)) return;
    collectStepData(8);

    const result = await Swal.fire({
        title: editConfigId ? 'Update Configuration?' : 'Save Configuration?',
        text: editConfigId ? `Update "${wizardData.name}"` : `Save "${wizardData.name}" as a new pricing configuration`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: editConfigId ? 'Update' : 'Save',
        cancelButtonText: 'Review again',
    });
    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Saving...', text: 'Please wait', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

    if (editConfigId) {
        wizardData.id = editConfigId;
    }

    // Use default template data if starting from default (never applies when editing an existing config)
    if (!editConfigId && wizardData.template_choice === 'default' && !wizardData.complexity_tiers?.length) {
        try {
            const resp = await fetch(`${API}/get_config.php?id=1`);
            const data = await resp.json();
            if (data.success && data.data.config) {
                const tmpl = data.data.config;
                wizardData.complexity_tiers = tmpl.complexity_tiers.map(t => ({ name: t.name, multiplier: parseFloat(t.multiplier) }));
                wizardData.screen_templates = (tmpl.screen_templates || []).map(s => ({ name: s.name, complexity_tier_name: s.tier_name }));
                wizardData.integrations = tmpl.integrations.map(i => ({ name: i.name, points: parseInt(i.points) }));
                wizardData.platform_multipliers = tmpl.platform_multipliers.map(p => ({ platform: p.platform, multiplier: parseFloat(p.multiplier) }));
                wizardData.package_tiers = tmpl.package_tiers.map(p => ({ name: p.name, multiplier: parseFloat(p.multiplier) }));
                wizardData.currency = tmpl.currency;
                wizardData.base_rate = parseFloat(tmpl.base_rate);
            }
        } catch {
            Swal.fire({
                icon: 'warning',
                title: 'Could not load default template',
                text: 'You can still continue and fill in the configuration manually.',
            });
        }
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const res = await fetch(`${API}/save_config.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify(wizardData),
        });
        const resultData = await res.json();

        if (resultData.success) {
            Swal.fire({
                icon: 'success',
                title: editConfigId ? 'Configuration Updated!' : 'Configuration Saved!',
                text: editConfigId ? `"${wizardData.name}" has been updated` : `"${wizardData.name}" is ready to use`,
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = '/dashboard.php';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Save failed', text: resultData.error || 'Unknown error' });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

// Initialization helpers
function initStep1() {
    document.querySelectorAll('input[name="template_choice"]').forEach(r => {
        r.addEventListener('change', function () {
            document.querySelectorAll('.template-option').forEach(el => {
                el.classList.toggle('border-indigo-500', el.querySelector('input') === this);
                el.classList.toggle('border-gray-200', el.querySelector('input') !== this);
            });
        });
    });
}

function initDynamicRows() {
    document.getElementById('tiersBody')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-tier')) e.target.closest('tr')?.remove();
    });
    document.getElementById('screenTmplBody')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-screen-tmpl')) e.target.closest('tr')?.remove();
    });
    document.getElementById('intsBody')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-int')) e.target.closest('tr')?.remove();
    });
    document.getElementById('platformsBody')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-platform')) e.target.closest('tr')?.remove();
    });
    document.getElementById('packagesBody')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-pkg')) e.target.closest('tr')?.remove();
    });
    document.getElementById('addScreenTmplBtn')?.addEventListener('click', () => addScreenTmplRow());
}

function currentTierNames() {
    return Array.from(document.querySelectorAll('input[name="tier_name[]"]'))
        .map(el => el.value.trim())
        .filter(Boolean);
}

function addScreenTmplRow(data) {
    data = data || {};
    const tbody = document.getElementById('screenTmplBody');
    if (!tbody) return;
    const tierNames = currentTierNames();
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-100';
    tr.innerHTML = `
        <td class="py-2 pr-4"><input type="text" class="screen-tmpl-name w-full px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300" placeholder="e.g. Login Screen" value="${escHtml(data.name || '')}"></td>
        <td class="py-2 pr-4">
            <select class="screen-tmpl-tier w-full px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                ${tierNames.map(name => `<option value="${escHtml(name)}" ${name === data.complexity_tier_name ? 'selected' : ''}>${escHtml(name)}</option>`).join('')}
            </select>
        </td>
        <td class="py-2"><button type="button" class="remove-screen-tmpl text-red-400 hover:text-red-300 text-sm">Remove</button></td>
    `;
    tbody.appendChild(tr);
}

function refreshScreenTmplTierOptions() {
    const tierNames = currentTierNames();
    document.querySelectorAll('#screenTmplBody .screen-tmpl-tier').forEach(select => {
        const current = select.value;
        select.innerHTML = tierNames.map(name => `<option value="${escHtml(name)}" ${name === current ? 'selected' : ''}>${escHtml(name)}</option>`).join('');
    });
}

function addRow(tbodyId, types, defaults) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    const nameMap = {
        'tiersBody': ['tier_name[]', 'tier_multiplier[]'],
        'intsBody': ['int_name[]', 'int_points[]'],
        'platformsBody': ['platform_name[]', 'platform_multiplier[]'],
        'packagesBody': ['pkg_name[]', 'pkg_multiplier[]'],
    };
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-100';
    const names = nameMap[tbodyId] || ['', ''];
    let html = '';
    names.forEach((name, i) => {
        const type = types[i] || 'text';
        html += `<td class="py-2 pr-4"><input type="${type}" name="${name}" value="${defaults[i] || ''}" class="w-full px-3 py-2 rounded bg-white border border-gray-300 text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300"></td>`;
    });
    const removeClassMap = {
        tiersBody: 'remove-tier',
        intsBody: 'remove-int',
        platformsBody: 'remove-platform',
        packagesBody: 'remove-pkg',
    };
    const removeClass = removeClassMap[tbodyId] || 'remove-tier';
    html += `<td class="py-2"><button type="button" class="${removeClass} text-red-400 hover:text-red-300 text-sm">Remove</button></td>`;
    tr.innerHTML = html;
    tbody.appendChild(tr);
}

function initMaintenanceToggle() {
    document.querySelectorAll('input[name="maintenance_model"]').forEach(r => {
        r.addEventListener('change', function () {
            const unit = document.getElementById('maintenanceUnit');
            if (unit) unit.textContent = this.value === 'percentage' ? '%' : (document.getElementById('currency')?.value || '₦');
        });
    });
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
