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
    <title>Pricing Tool — Configuration Wizard</title>
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
            <a href="/intake.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                New Quote
            </a>
            <a href="/quotes.php" class="sidebar-link">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Quote History
            </a>
            <a href="/wizard.php" class="sidebar-link-active">
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
                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-sm text-gray-500">Welcome,</span>
                    <span class="font-medium text-gray-900 text-sm" id="userName">loading...</span>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6 max-w-4xl mx-auto">
            <!-- Step Progress -->
            <div class="card mb-6">
                <div class="flex items-center justify-between text-xs font-medium" id="stepProgress">
                    <?php $steps = ['Welcome', 'Currency', 'Complexity', 'Integrations', 'Platforms', 'Maintenance', 'Packages', 'Review']; ?>
                    <?php foreach ($steps as $i => $s): ?>
                        <div class="flex items-center gap-1 <?= $i === 0 ? 'text-indigo-600' : 'text-gray-400' ?>" data-step="<?= $i + 1 ?>">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold <?= $i === 0 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' ?>"><?= $i + 1 ?></span>
                            <span class="hidden sm:inline"><?= $s ?></span>
                        </div>
                        <?php if ($i < count($steps) - 1): ?>
                            <div class="flex-1 h-px bg-gray-200 mx-2"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Steps -->
            <form id="wizardForm" novalidate>
                <!-- Step 1: Welcome -->
                <div class="card step" data-step="1">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Welcome</h2>
                    <p class="text-sm text-gray-500 mb-4">Choose how to start building your pricing configuration.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <label class="template-option border-2 border-indigo-500 bg-indigo-50 rounded-xl p-4 cursor-pointer">
                            <input type="radio" name="template_choice" value="default" checked class="sr-only">
                            <div class="text-indigo-600 font-medium">Start with Default Template</div>
                            <div class="text-sm text-gray-500 mt-1">Pre-filled with standard tiers, integrations, and packages</div>
                        </label>
                        <label class="template-option border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                            <input type="radio" name="template_choice" value="scratch" class="sr-only">
                            <div class="text-gray-900 font-medium">Start from Scratch</div>
                            <div class="text-sm text-gray-500 mt-1">Blank configuration — add everything manually</div>
                        </label>
                    </div>
                    <div>
                        <label class="label">Configuration Name</label>
                        <input type="text" id="configName" name="config_name" value="Standard" class="input-field max-w-sm" placeholder="e.g. Standard">
                    </div>
                </div>

                <!-- Step 2: Currency & Base Rate -->
                <div class="card step hidden" data-step="2">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Currency &amp; Base Rate</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Currency</label>
                            <select id="currency" name="currency" class="input-field">
                                <option value="₦">₦ — Nigerian Naira</option>
                                <option value="$">$ — US Dollar</option>
                                <option value="€">€ — Euro</option>
                                <option value="£">£ — British Pound</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Base Rate (per point)</label>
                            <input type="number" id="baseRate" name="base_rate" value="20000" min="0" step="100" class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Complexity Tiers -->
                <div class="card step hidden" data-step="3">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Screen Complexity Tiers</h2>
                        <button type="button" class="btn-secondary text-sm" onclick="addRow('tiersBody', ['text', 'number'], ['', 1.0])">+ Add Tier</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead><tr class="text-gray-500 text-xs uppercase border-b"><th class="py-2 pr-4">Tier Name</th><th class="py-2 pr-4">Multiplier</th><th class="py-2"></th></tr></thead>
                            <tbody id="tiersBody">
                                <tr>
                                    <td class="py-2 pr-4"><input type="text" name="tier_name[]" value="Simple" class="input-field"></td>
                                    <td class="py-2 pr-4"><input type="number" name="tier_multiplier[]" value="1.0" min="0" step="0.1" class="input-field"></td>
                                    <td class="py-2"><button type="button" class="remove-tier text-red-500 hover:text-red-600 text-sm font-medium">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 4: Integrations -->
                <div class="card step hidden" data-step="4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Integrations</h2>
                        <button type="button" class="btn-secondary text-sm" onclick="addRow('intsBody', ['text', 'number'], ['', 1])">+ Add Integration</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead><tr class="text-gray-500 text-xs uppercase border-b"><th class="py-2 pr-4">Integration</th><th class="py-2 pr-4">Points</th><th class="py-2"></th></tr></thead>
                            <tbody id="intsBody">
                                <tr>
                                    <td class="py-2 pr-4"><input type="text" name="int_name[]" value="Payment Gateway" class="input-field"></td>
                                    <td class="py-2 pr-4"><input type="number" name="int_points[]" value="3" min="0" class="input-field"></td>
                                    <td class="py-2"><button type="button" class="remove-int text-red-500 hover:text-red-600 text-sm font-medium">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 5: Platforms -->
                <div class="card step hidden" data-step="5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Platform Multipliers</h2>
                        <button type="button" class="btn-secondary text-sm" onclick="addRow('platformsBody', ['text', 'number'], ['', 1.0])">+ Add Platform</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead><tr class="text-gray-500 text-xs uppercase border-b"><th class="py-2 pr-4">Platform</th><th class="py-2 pr-4">Multiplier</th><th class="py-2"></th></tr></thead>
                            <tbody id="platformsBody">
                                <tr>
                                    <td class="py-2 pr-4"><input type="text" name="platform_name[]" value="Web" class="input-field"></td>
                                    <td class="py-2 pr-4"><input type="number" name="platform_multiplier[]" value="1.0" min="0" step="0.1" class="input-field"></td>
                                    <td class="py-2"><button type="button" class="remove-platform text-red-500 hover:text-red-600 text-sm font-medium">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 6: Maintenance -->
                <div class="card step hidden" data-step="6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Maintenance Pricing</h2>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 p-4 border-2 border-indigo-500 bg-indigo-50 rounded-xl cursor-pointer">
                            <input type="radio" name="maintenance_model" value="percentage" checked class="h-4 w-4 text-indigo-600">
                            <div>
                                <div class="font-medium text-gray-900">% of project price / month</div>
                                <div class="text-sm text-gray-500">Default: 15%</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-gray-300">
                            <input type="radio" name="maintenance_model" value="flat" class="h-4 w-4 text-indigo-600">
                            <div>
                                <div class="font-medium text-gray-900">Flat monthly rate</div>
                                <div class="text-sm text-gray-500">Set a fixed amount per month</div>
                            </div>
                        </label>
                        <div class="flex items-center gap-3 max-w-xs">
                            <label class="label mb-0 whitespace-nowrap">Value:</label>
                            <input type="number" id="maintValue" name="maintenance_value" value="15" min="0" step="0.5" class="input-field">
                            <span id="maintenanceUnit" class="text-sm font-medium text-gray-700 w-6">%</span>
                        </div>
                    </div>
                </div>

                <!-- Step 7: Packages -->
                <div class="card step hidden" data-step="7">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Package Tiers</h2>
                        <button type="button" class="btn-secondary text-sm" onclick="addRow('packagesBody', ['text', 'number'], ['', 1.0])">+ Add Package Tier</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead><tr class="text-gray-500 text-xs uppercase border-b"><th class="py-2 pr-4">Package Name</th><th class="py-2 pr-4">Multiplier</th><th class="py-2"></th></tr></thead>
                            <tbody id="packagesBody">
                                <tr>
                                    <td class="py-2 pr-4"><input type="text" name="pkg_name[]" value="Basic" class="input-field"></td>
                                    <td class="py-2 pr-4"><input type="number" name="pkg_multiplier[]" value="1.0" min="0" step="0.1" class="input-field"></td>
                                    <td class="py-2"><button type="button" class="remove-pkg text-red-500 hover:text-red-600 text-sm font-medium">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 8: Review -->
                <div class="card step hidden" data-step="8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Review &amp; Save</h2>
                    <div id="reviewContent" class="text-sm text-gray-500 space-y-3"></div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-6">
                    <button type="button" id="prevBtn" class="btn-secondary hidden">Back</button>
                    <button type="button" id="nextBtn" class="btn-primary ml-auto">Continue</button>
                    <button type="submit" id="saveBtn" class="btn-primary hidden">Save Configuration</button>
                </div>
            </form>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script src="/assets/js/wizard.js"></script>
</body>
</html>
