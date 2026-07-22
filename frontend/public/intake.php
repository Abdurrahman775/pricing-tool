<?php
$pageTitle = 'Pricing Tool — New Project';
$activeNav = 'intake';
$mobileTitle = 'New Quote';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
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
                                <button type="button" id="addScreenBtn" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">+ Custom Screen</button>
                            </div>
                            <div id="screenTemplatesList" class="flex flex-wrap gap-2 mb-4"></div>
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
