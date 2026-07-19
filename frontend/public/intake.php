<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php
        require_once dirname(__DIR__, 2) . '/security/middleware.php';
        startSecureSession();
        echo generateCsrfToken();
    ?>">
    <title>Pricing Tool — New Project</title>
    <link rel="stylesheet" href="/assets/css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-indigo-950 text-white z-50 -translate-x-full lg:translate-x-0 transition-transform duration-200 flex flex-col">
        <div class="flex items-center gap-3 px-4 h-16 border-b border-indigo-800">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <span class="font-bold text-sm">Pricing &amp; PRD</span>
        </div>
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            <a href="/dashboard.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="/intake.php" class="sidebar-link-active">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                New Quote
            </a>
            <a href="/quotes.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Quote History
            </a>
            <a href="/wizard.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configurations
            </a>
            <a href="/settings.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>
        <div class="p-3 border-t border-indigo-800">
            <button onclick="handleLogout()" class="sidebar-link w-full">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <header class="sticky top-0 bg-white border-b border-gray-200 z-20">
            <div class="flex items-center justify-between px-4 h-16">
                <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900 lg:hidden">New Quote</h1>
                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-sm text-gray-500">Welcome,</span>
                    <span class="font-medium text-gray-900 text-sm" id="userName">loading...</span>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6 max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form -->
                <div class="lg:col-span-2 space-y-6">
                    <form id="intakeForm" novalidate>
                        <!-- Client & Config -->
                        <div class="card">
                            <h2 class="font-semibold text-gray-900 mb-4">Project Details</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label">Client Name</label>
                                    <input type="text" id="clientName" required class="input-field" placeholder="Acme Corp">
                                </div>
                                <div>
                                    <label class="label">Pricing Config</label>
                                    <select id="configSelect" class="input-field"></select>
                                    <p id="noConfigWarning" class="hidden text-xs text-red-500 mt-1">No configurations found. <a href="/wizard.php" class="underline font-medium">Create one</a> first.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="label">Project Name</label>
                                <input type="text" id="projectName" required class="input-field" placeholder="E-commerce Mobile App">
                            </div>
                        </div>

                        <!-- Screens -->
                        <div class="card">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="font-semibold text-gray-900">Screens</h2>
                                <button type="button" id="addScreenBtn" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">+ Add Screen</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="text-gray-500 text-xs uppercase border-b">
                                            <th class="py-2 pr-4">Screen Name</th>
                                            <th class="py-2 pr-4">Complexity</th>
                                            <th class="py-2 pr-4">Notes</th>
                                            <th class="py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="screensBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Integrations -->
                        <div class="card">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="font-semibold text-gray-900">Integrations</h2>
                                <button type="button" id="addCustomIntBtn" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">+ Custom</button>
                            </div>
                            <div id="integrationsList" class="space-y-2"></div>
                            <div id="customInts" class="space-y-2 mt-2"></div>
                        </div>

                        <!-- Platforms -->
                        <div class="card">
                            <h2 class="font-semibold text-gray-900 mb-4">Platforms</h2>
                            <div id="platformsList" class="flex flex-wrap gap-3"></div>
                        </div>

                        <!-- Maintenance -->
                        <div class="card">
                            <h2 class="font-semibold text-gray-900 mb-4">Maintenance</h2>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" id="includeMaintenance" class="rounded text-indigo-600 h-4 w-4">
                                <span class="text-sm text-gray-700">Include maintenance pricing</span>
                            </label>
                            <div id="maintenanceFields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="label">Model</label>
                                    <select id="maintModel" class="input-field">
                                        <option value="percentage">% of project price / month</option>
                                        <option value="flat">Flat monthly rate</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label">Value</label>
                                    <input type="number" id="maintValue" value="15" min="0" step="0.5" class="input-field">
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end gap-3">
                            <a href="/dashboard.php" class="btn-secondary text-sm">Cancel</a>
                            <button type="submit" class="btn-primary">Save Project</button>
                        </div>
                    </form>
                </div>

                <!-- Pricing Preview (sticky sidebar) -->
                <div class="lg:col-span-1">
                    <div class="card-elevated sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Pricing Breakdown</h2>
                        <div id="pricingLoader" class="text-gray-400 text-sm">Add screens and select options to see the price.</div>
                        <div id="pricingContent" class="hidden"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script src="/assets/js/intake.js"></script>
</body>
</html>
