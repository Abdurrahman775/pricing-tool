<?php
$pageTitle = 'Pricing Tool — Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
        <!-- Page Content -->
        <main class="p-4 md:p-6 max-w-7xl mx-auto">
            <!-- Welcome Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1" id="todayDate"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/intake.php" class="btn-primary text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        New Quote
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="configCount">0</div>
                            <div class="text-xs text-gray-500">Configurations</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="projectCount">0</div>
                            <div class="text-xs text-gray-500">Total Projects</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="quoteCount">0</div>
                            <div class="text-xs text-gray-500">Total Quotes</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900" id="draftCount">0</div>
                            <div class="text-xs text-gray-500">Active Drafts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Configurations -->
                <div class="card lg:col-span-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Pricing Configs</h3>
                        <a href="/wizard.php" class="text-indigo-600 text-sm font-medium hover:text-indigo-700">+ New</a>
                    </div>
                    <div id="configList" class="loading-state py-8">
                        <span class="spinner" aria-hidden="true"></span> Loading configurations&hellip;
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="card lg:col-span-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Recent Projects</h3>
                        <a href="/quotes.php" class="text-indigo-600 text-sm font-medium hover:text-indigo-700">View All</a>
                    </div>
                    <div id="projectList" class="loading-state py-8">
                        <span class="spinner" aria-hidden="true"></span> Loading projects&hellip;
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card lg:col-span-1">
                    <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="/intake.php" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">New Quote</div>
                                <div class="text-xs text-gray-500">Create a project and generate pricing</div>
                            </div>
                        </a>
                        <a href="/wizard.php" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">New Configuration</div>
                                <div class="text-xs text-gray-500">Set up pricing rules and tiers</div>
                            </div>
                        </a>
                        <a href="/quotes.php" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Quote History</div>
                                <div class="text-xs text-gray-500">Browse past quotes and generate docs</div>
                            </div>
                        </a>
                        <a href="/settings.php" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Settings</div>
                                <div class="text-xs text-gray-500">Manage profile, password, and imports</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- All Projects Table -->
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">All Projects</h3>
                    <a href="/quotes.php" class="text-indigo-600 text-sm font-medium hover:text-indigo-700">View Full History →</a>
                </div>
                <div id="allProjectsTable" class="loading-state py-8">
                    <span class="spinner" aria-hidden="true"></span> Loading projects&hellip;
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script>
        (async () => {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                // Set today's date
                document.getElementById('todayDate').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                // Load configs
                const configRes = await fetch('/api/config/list_configs.php', { headers: { 'X-CSRF-Token': csrf } });
                const configData = await configRes.json();
                const configList = document.getElementById('configList');

                if (configData.success && configData.data.configs?.length) {
                    document.getElementById('configCount').textContent = configData.data.configs.length;
                    configList.className = '';
                    configList.innerHTML = configData.data.configs.slice(0, 5).map(c =>
                        `<div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div>
                                <div class="font-medium text-gray-900">${escHtml(c.name)}</div>
                                <div class="text-xs text-gray-500">${c.currency}${parseFloat(c.base_rate).toLocaleString()}/pt</div>
                            </div>
                            <a href="/wizard.php?edit=${c.id}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Edit</a>
                        </div>`
                    ).join('');
                } else {
                    configList.className = 'empty-state';
                    configList.innerHTML = emptyStateHtml('No configurations yet.', '/wizard.php', 'Create one');
                }

                // Load projects
                const projRes = await fetch('/api/projects/get_projects.php', { headers: { 'X-CSRF-Token': csrf } });
                const projData = await projRes.json();
                const projectList = document.getElementById('projectList');
                const allProjectsTable = document.getElementById('allProjectsTable');

                if (projData.success && projData.data.projects?.length) {
                    const projects = projData.data.projects;
                    document.getElementById('projectCount').textContent = projects.length;

                    let drafts = 0;
                    let locked = 0;
                    projects.forEach(p => {
                        if (p.status === 'draft') drafts++;
                        if (p.status === 'locked') locked++;
                    });
                    document.getElementById('draftCount').textContent = drafts;
                    document.getElementById('quoteCount').textContent = locked;

                    // Recent projects (top 3)
                    projectList.className = '';
                    projectList.innerHTML = projects.slice(0, 3).map(p =>
                        `<a href="/quote.php?id=${p.id}" class="flex items-center justify-between py-3 px-1 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors rounded">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 truncate">${escHtml(p.project_name)}</div>
                                <div class="text-xs text-gray-500 truncate">${escHtml(p.client_name)}</div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="text-xs text-gray-400">${p.screen_count} screens</span>
                                <span class="${p.status === 'locked' ? 'badge-locked' : 'badge-draft'}">${p.status}</span>
                            </div>
                        </a>`
                    ).join('');

                    // All projects table
                    allProjectsTable.className = '';
                    allProjectsTable.innerHTML = `
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-gray-500 text-xs uppercase border-b">
                                        <th class="py-3 pr-4 font-medium">Project</th>
                                        <th class="py-3 pr-4 font-medium">Client</th>
                                        <th class="py-3 pr-4 font-medium">Config</th>
                                        <th class="py-3 pr-4 font-medium">Screens</th>
                                        <th class="py-3 pr-4 font-medium">Status</th>
                                        <th class="py-3 pr-4 font-medium">Created</th>
                                        <th class="py-3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${projects.slice(0, 8).map(p => `
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                            <td class="py-3 pr-4 font-medium text-gray-900">${escHtml(p.project_name)}</td>
                                            <td class="py-3 pr-4 text-gray-700">${escHtml(p.client_name)}</td>
                                            <td class="py-3 pr-4 text-gray-500">${escHtml(p.config_name || '—')}</td>
                                            <td class="py-3 pr-4 text-gray-500">${p.screen_count}</td>
                                            <td class="py-3 pr-4"><span class="${p.status === 'locked' ? 'badge-locked' : 'badge-draft'}">${p.status}</span></td>
                                            <td class="py-3 pr-4 text-gray-500 text-sm">${new Date(p.created_at).toLocaleDateString()}</td>
                                            <td class="py-3"><a href="/quote.php?id=${p.id}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View</a></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        ${projects.length > 8 ? `<div class="text-center mt-4"><a href="/quotes.php" class="text-indigo-600 text-sm font-medium hover:text-indigo-700">View all ${projects.length} projects →</a></div>` : ''}
                    `;
                } else {
                    projectList.className = 'empty-state';
                    projectList.innerHTML = emptyStateHtml('No projects yet.', '/intake.php', 'Create your first quote');
                    allProjectsTable.className = 'empty-state';
                    allProjectsTable.innerHTML = emptyStateHtml('No projects yet.', '/intake.php', 'Create a new quote');
                }
            } catch {
                document.querySelectorAll('.loading-state').forEach(el => {
                    el.className = 'empty-state';
                    el.textContent = 'Failed to load. Please refresh the page.';
                });
            }
        })();
        function escHtml(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
        function emptyStateHtml(message, href, linkText) {
            return `<svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-2.5 5a2 2 0 01-1.8 1H8.3a2 2 0 01-1.8-1L4 13m16 0h-3.5a1 1 0 00-.9.5l-.7 1.4a1 1 0 01-.9.6h-2a1 1 0 01-.9-.6l-.7-1.4a1 1 0 00-.9-.5H4"/></svg>
                <p>${message}</p>
                <a href="${href}" class="text-indigo-600 font-medium hover:underline">${linkText}</a>`;
        }
    </script>
</body>
</html>
