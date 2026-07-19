// Quote Module — View, select package, lock, generate documents

const API = '/api';
let projectId = 0;
let project = null;
let selectedPackage = null;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        await checkAuth();
        const params = new URLSearchParams(window.location.search);
        projectId = parseInt(params.get('id'));
        if (!projectId) {
            document.getElementById('loadingState').textContent = 'No project ID specified.';
            return;
        }
        await loadProject();
    } catch {}
});

async function loadProject() {
    try {
        const res = await fetch(`${API}/projects/get_project.php?id=${projectId}`, {
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });
        const data = await res.json();
        if (!data.success) {
            document.getElementById('loadingState').textContent = data.error || 'Failed to load project.';
            return;
        }

        project = data.data.project;
        renderProject();
    } catch {
        document.getElementById('loadingState').textContent = 'Network error loading project.';
    }
}

function renderProject() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('quoteContent').classList.remove('hidden');

    document.getElementById('projectName').textContent = escHtml(project.project_name);
    document.getElementById('clientName').textContent = escHtml(project.client_name);
    document.getElementById('projectDate').textContent = new Date(project.created_at).toLocaleDateString();

    const statusBadge = document.getElementById('projectStatus');
    if (project.status === 'locked') {
        statusBadge.textContent = 'Locked';
        statusBadge.className = 'inline-block px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
    } else {
        statusBadge.textContent = 'Draft';
        statusBadge.className = 'inline-block px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
    }

    // Show/hide buttons based on status
    document.getElementById('editProjectBtn').classList.toggle('hidden', project.status !== 'draft');
    document.getElementById('lockProjectBtn').classList.toggle('hidden', project.status === 'locked');
    document.getElementById('versionProjectBtn').classList.toggle('hidden', project.status !== 'locked');

    // Pricing breakdown
    renderPricing();

    // Package selection
    renderPackages();

    // Version history
    renderVersionHistory();

    // Event listeners
    document.getElementById('editProjectBtn').addEventListener('click', () => {
        window.location.href = `/intake.php?edit=${projectId}`;
    });
    document.getElementById('lockProjectBtn').addEventListener('click', handleLock);
    document.getElementById('versionProjectBtn').addEventListener('click', handleVersion);
    document.getElementById('genProposalBtn').addEventListener('click', () => generateDoc('proposal'));
    document.getElementById('genPrdBtn').addEventListener('click', () => generateDoc('prd'));
}

function renderPricing() {
    const ver = project.latest_version;
    const container = document.getElementById('pricingContent');

    if (!ver || !ver.pricing_breakdown) {
        container.innerHTML = '<div class="text-indigo-600 text-sm">Recalculate pricing to see breakdown. Lock the project to create a snapshot.</div>';
        return;
    }

    const bd = typeof ver.pricing_breakdown === 'string'
        ? JSON.parse(ver.pricing_breakdown)
        : ver.pricing_breakdown;

    const cur = bd.currency || '₦';
    let html = '';

    // Screens
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="text-indigo-600 font-medium mb-2">Screens</div>`;
    if (bd.screen_breakdown) {
        bd.screen_breakdown.forEach(s => {
            html += `<div class="flex justify-between text-gray-700 py-0.5"><span>${escHtml(s.name)} <span class="text-indigo-600">(${escHtml(s.tier)})</span></span><span>${s.points} pts</span></div>`;
        });
    }
    html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Total Screen Points</span><span>${bd.total_screen_points || 0} pts</span></div>`;
    html += `</div>`;

    // Integrations
    if (bd.integration_breakdown && bd.integration_breakdown.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
        html += `<div class="text-indigo-600 font-medium mb-2">Integrations</div>`;
        bd.integration_breakdown.forEach(i => {
            html += `<div class="flex justify-between text-gray-700 py-0.5"><span>${escHtml(i.name)}</span><span>+${i.points} pts</span></div>`;
        });
        html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Total Integration Points</span><span>+${bd.total_integration_points || 0} pts</span></div>`;
        html += `</div>`;
    }

    // Subtotal + Platform
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Subtotal (screens + integrations)</span><span>${bd.subtotal || 0} pts</span></div>`;
    if (bd.platform_breakdown && bd.platform_breakdown.length) {
        html += `<div class="text-indigo-600 font-medium mt-3 mb-1">Selected Platforms</div>`;
        const maxMul = Math.max(...bd.platform_breakdown.map(p => parseFloat(p.multiplier)));
        bd.platform_breakdown.forEach(p => {
            const isMax = parseFloat(p.multiplier) === maxMul;
            html += `<div class="flex items-center justify-between py-0.5 ${isMax ? 'text-emerald-700 font-medium' : 'text-gray-500'}"><span>${escHtml(p.platform)} <span class="text-xs">(×${p.multiplier})</span>${isMax ? ' <span class="text-xs text-emerald-600">← applied</span>' : ''}</span></div>`;
        });
    }
    html += `<div class="flex justify-between text-emerald-600 font-medium pt-2 border-t border-indigo-200 mt-2"><span>Platform Multiplier</span><span>×${bd.platform_multiplier || 1}</span></div>`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Adjusted Subtotal</span><span>${bd.adjusted_subtotal || 0} pts</span></div>`;
    html += `</div>`;

    // Base price
    html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Base Rate</span><span>${cur}${Number(bd.base_rate || 0).toLocaleString()}/pt</span></div>`;
    html += `<div class="flex justify-between text-gray-700 py-0.5"><span>Base Price</span><span>${cur}${Number(bd.base_price || 0).toLocaleString()}</span></div>`;
    html += `</div>`;

    // Packages shown as list
    if (bd.packages && bd.packages.length) {
        html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
        html += `<div class="text-indigo-600 font-medium mb-2">Package Options</div>`;
        bd.packages.forEach(pkg => {
            const active = ver.selected_package === pkg.name ? 'text-emerald-600 font-semibold' : 'text-gray-700';
            html += `<div class="flex justify-between ${active} py-1"><span>${escHtml(pkg.name)} <span class="text-indigo-600">(×${pkg.multiplier})</span></span><span>${cur}${Number(pkg.price).toLocaleString()}</span></div>`;
        });
        html += `</div>`;
    }

    // Maintenance
    if (bd.maintenance) {
        html += `<div class="bg-indigo-50 rounded-lg p-4 mb-3 text-sm">`;
        html += `<div class="flex justify-between text-gray-700"><span>Maintenance</span><span class="text-emerald-600">${cur}${Number(bd.maintenance.per_month || 0).toLocaleString()}/mo</span></div>`;
        html += `</div>`;
    }

    container.innerHTML = html;
}

function renderPackages() {
    const container = document.getElementById('packagesGrid');
    const ver = project.latest_version;

    if (!ver || !ver.pricing_breakdown) {
        container.innerHTML = '<div class="col-span-3 text-center text-gray-400 text-sm py-8">Lock the project first to see package options.</div>';
        return;
    }

    const bd = typeof ver.pricing_breakdown === 'string'
        ? JSON.parse(ver.pricing_breakdown)
        : ver.pricing_breakdown;

    const cur = bd.currency || '₦';
    const packages = bd.packages || [];

    if (!packages.length) {
        container.innerHTML = '<div class="col-span-3 text-center text-gray-400 text-sm py-8">No packages defined in this configuration.</div>';
        return;
    }

    // If a package is already selected, highlight it
    const selectedPkg = ver.selected_package;

    container.innerHTML = packages.map((pkg, idx) => {
        const isSelected = selectedPkg === pkg.name;
        const multipliers = ['', '', ''];
        const features = getPackageFeatures(idx);

        return `
        <div class="package-card rounded-xl border-2 p-5 cursor-pointer transition-all duration-200 ${isSelected ? 'border-indigo-600 bg-indigo-50 shadow-md' : 'border-gray-200 bg-white hover:border-indigo-300 hover:shadow-sm'}" data-pkg-name="${escHtml(pkg.name)}" data-pkg-price="${pkg.price}" data-pkg-idx="${idx}">
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900 mb-1">${escHtml(pkg.name)}</h3>
                <div class="text-3xl font-bold text-indigo-600 mb-2">${cur}${Number(pkg.price).toLocaleString()}</div>
                <div class="text-xs text-gray-400 mb-4">${isSelected ? '✓ Selected' : 'Click to select'}</div>
                <ul class="text-left text-sm text-gray-600 space-y-1.5">
                    ${features.map(f => `<li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">✓</span> <span>${escHtml(f)}</span></li>`).join('')}
                </ul>
            </div>
        </div>
        `;
    }).join('');

    // Add click handlers
    container.querySelectorAll('.package-card').forEach(card => {
        card.addEventListener('click', () => selectPackage(card));
    });
}

function getPackageFeatures(idx) {
    // Generate contextual features based on package index
    const features = [
        ['All screens developed', 'All screens developed', 'All screens developed'],
        ['Standard integrations', 'All integrations', 'All + priority integrations'],
        ['Single platform', 'Up to 2 platforms', 'All platforms included'],
        ['Email support (48h)', 'Email + chat support (24h)', 'Priority support (4h)'],
        ['Standard documentation', 'Detailed documentation', 'Full documentation + handover'],
    ];
    return features.map(f => f[idx] || f[0]);
}

async function selectPackage(card) {
    if (project.status !== 'locked') {
        Swal.fire({
            icon: 'warning',
            title: 'Lock Required',
            text: 'You need to lock the project first before selecting a package.',
        });
        return;
    }

    const name = card.dataset.pkgName;
    const price = parseFloat(card.dataset.pkgPrice);

    selectedPackage = { name, price };

    // Update UI
    document.querySelectorAll('.package-card').forEach(c => {
        c.classList.remove('border-indigo-600', 'bg-indigo-50', 'shadow-md');
        c.classList.add('border-gray-200', 'bg-white');
        const selLabel = c.querySelector('.text-xs.text-gray-400');
        if (selLabel) selLabel.textContent = 'Click to select';
    });
    card.classList.remove('border-gray-200', 'bg-white');
    card.classList.add('border-indigo-600', 'bg-indigo-50', 'shadow-md');
    const selLabel = card.querySelector('.text-xs.text-gray-400');
    if (selLabel) selLabel.textContent = '✓ Selected';

    document.getElementById('selectedPackageDisplay').innerHTML = `Package: <strong>${escHtml(name)}</strong> (${project.latest_version ? JSON.parse(typeof project.latest_version.pricing_breakdown === 'string' ? project.latest_version.pricing_breakdown : '{}').currency || '₦' : '₦'}${Number(price).toLocaleString()})`;
    document.getElementById('genProposalBtn').disabled = false;
    document.getElementById('genPrdBtn').disabled = false;

    // Save selection to backend
    try {
        const ver = project.latest_version;
        if (ver) {
            await fetch(`${API}/quotes/select_package.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body: JSON.stringify({
                    project_id: projectId,
                    version_id: ver.id,
                    package_name: name,
                    package_price: price,
                }),
            });
        }
    } catch {}
}

async function handleLock() {
    const result = await Swal.fire({
        title: 'Lock Project?',
        text: 'This will freeze the current scope and create a quote version. Changes will require a new version.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Lock Project',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`${API}/projects/lock_project.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ project_id: projectId }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Project Locked!',
                text: 'Version 1 created. You can now select a package and generate documents.',
                timer: 2000,
                showConfirmButton: false,
            });
            await loadProject();
        } else {
            Swal.fire({ icon: 'error', title: 'Lock failed', text: data.error || 'Unknown error' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

async function handleVersion() {
    const result = await Swal.fire({
        title: 'Create New Version?',
        text: 'This will create a new draft copy of this project for modifications. The current version remains frozen.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Create Version',
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`${API}/projects/version_project.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ project_id: projectId }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'New Version Created!',
                text: 'Redirecting to the new draft...',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = `/quote.php?id=${data.data.project_id}`;
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.error || 'Unknown error' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

async function generateDoc(docType) {
    if (!selectedPackage && !project.latest_version?.selected_package) {
        Swal.fire({ icon: 'warning', title: 'Select Package', text: 'Please select a package first.' });
        return;
    }

    const pkg = selectedPackage || {
        name: project.latest_version.selected_package,
        price: project.latest_version.package_price,
    };

    Swal.fire({
        title: 'Generating...',
        text: 'Please wait while your document is prepared.',
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
    });

    try {
        const res = await fetch(`/generate-doc`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({
                project_id: projectId,
                doc_type: docType,
                package_name: pkg.name,
                package_price: pkg.price,
            }),
        });

        const contentType = res.headers.get('content-type') || '';

        if (contentType.includes('vnd.openxmlformats-officedocument.wordprocessingml.document')) {
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${project.project_name.replace(/[^a-z0-9]/gi, '_')}_${docType}.docx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            Swal.fire({
                icon: 'success',
                title: 'Download started!',
                timer: 1500,
                showConfirmButton: false,
            });
        } else {
            const data = await res.json();
            Swal.close();
            if (res.status === 429) {
                Swal.fire({ icon: 'warning', title: 'Rate limit exceeded', text: data.error || 'Maximum 30 documents per hour.' });
            } else {
                Swal.fire({ icon: 'error', title: 'Generation failed', text: data.error || 'Unknown error' });
            }
        }
    } catch (e) {
        Swal.close();
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Could not reach the server' });
    }
}

function renderVersionHistory() {
    const container = document.getElementById('versionHistory');

    // Reload project to get version list
    fetch(`${API}/projects/get_project.php?id=${projectId}`, {
        headers: { 'X-CSRF-Token': getCsrfToken() },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const proj = data.data.project;

        // We need to query quote versions directly
        return fetch(`${API}/quotes/list_versions.php?project_id=${projectId}`, {
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });
    })
    .then(r => r && r.json())
    .then(data => {
        if (!data || !data.success || !data.data.versions?.length) {
            container.innerHTML = '<div class="text-gray-400 text-sm">No versions yet. Lock the project to create version 1.</div>';
            return;
        }

        const cur = project.latest_version
            ? (JSON.parse(typeof project.latest_version.pricing_breakdown === 'string' ? project.latest_version.pricing_breakdown : '{}').currency || '₦')
            : '₦';

        container.innerHTML = data.data.versions.map(v => {
            const isLatest = v.id === project.latest_version?.id;
            return `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 ${isLatest ? 'bg-indigo-50 -mx-4 px-4 rounded' : ''}">
                <div>
                    <div class="font-medium text-gray-900">Version #${v.version_number} ${isLatest ? '<span class="text-xs text-indigo-600 font-medium">(current)</span>' : ''}</div>
                    <div class="text-xs text-gray-500">${v.screen_points} screen pts · ${v.integration_points} int pts · ×${v.platform_multiplier} platform</div>
                    <div class="text-xs text-gray-500">Base: ${cur}${Number(v.base_price).toLocaleString()} · Package: ${v.selected_package || '—'}</div>
                </div>
                <div class="text-right text-xs text-gray-400">
                    <div>${new Date(v.created_at).toLocaleDateString()}</div>
                    <div>${new Date(v.created_at).toLocaleTimeString()}</div>
                </div>
            </div>`;
        }).join('');
    })
    .catch(() => {
        container.innerHTML = '<div class="text-gray-400 text-sm">Unable to load version history.</div>';
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
