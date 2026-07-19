// Quote History Module — list all quotes

const API = '/api';

document.addEventListener('DOMContentLoaded', async () => {
    try {
        await checkAuth();
        await loadQuotes();
    } catch {}
});

async function loadQuotes() {
    const container = document.getElementById('quoteList');

    try {
        const res = await fetch(`${API}/projects/get_projects.php`, {
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });
        const data = await res.json();

        if (!data.success || !data.data.projects?.length) {
            container.innerHTML = 'No quotes yet. <a href="/intake.php" class="text-indigo-600 font-medium hover:underline">Create your first quote</a>.';
            return;
        }

        container.innerHTML = '';
        const table = document.createElement('table');
        table.className = 'w-full text-left';
        table.innerHTML = `
            <thead>
                <tr class="text-gray-500 text-xs uppercase border-b">
                    <th class="py-3 pr-4">Project</th>
                    <th class="py-3 pr-4">Client</th>
                    <th class="py-3 pr-4">Screens</th>
                    <th class="py-3 pr-4">Config</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3 pr-4">Created</th>
                    <th class="py-3"></th>
                </tr>
            </thead>
            <tbody>
                ${data.data.projects.map(p => `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 pr-4 font-medium text-gray-900">${escHtml(p.project_name)}</td>
                        <td class="py-3 pr-4 text-gray-700">${escHtml(p.client_name)}</td>
                        <td class="py-3 pr-4 text-gray-500">${p.screen_count}</td>
                        <td class="py-3 pr-4 text-gray-500">${escHtml(p.config_name || '—')}</td>
                        <td class="py-3 pr-4"><span class="${p.status === 'locked' ? 'badge-locked' : 'badge-draft'}">${p.status}</span></td>
                        <td class="py-3 pr-4 text-gray-500 text-sm">${new Date(p.created_at).toLocaleDateString()}</td>
                        <td class="py-3"><a href="/quote.php?id=${p.id}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View</a></td>
                    </tr>
                `).join('')}
            </tbody>
        `;
        container.appendChild(table);
    } catch {
        container.innerHTML = 'Failed to load quotes. Please try again.';
    }
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
