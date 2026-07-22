<?php
$pageTitle = 'Pricing Tool — Configuration Wizard';
$activeNav = 'wizard';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
        <main class="p-4 md:p-6 max-w-4xl mx-auto">
            <!-- Step Progress -->
            <div class="card mb-6">
                <div class="flex items-center justify-between text-xs font-medium" id="stepProgress">
                    <?php $steps = ['Welcome', 'Currency', 'Complexity', 'Screens', 'Integrations', 'Platforms', 'Maintenance', 'Packages', 'Review']; ?>
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
                    <h2 id="step1Heading" class="text-lg font-semibold text-gray-900 mb-4">Welcome</h2>
                    <p id="step1Desc" class="text-sm text-gray-500 mb-4">Choose how to start building your pricing configuration.</p>
                    <div id="templateChoiceGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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

                <!-- Step 4: Screens -->
                <div class="card step hidden" data-step="4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Screen Templates</h2>
                        <button type="button" id="addScreenTmplBtn" class="btn-secondary text-sm">+ Add Screen</button>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Define common screens for this configuration. They'll show up as quick-select checkboxes when creating a new project — custom screens can still be typed in on top of these.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead><tr class="text-gray-500 text-xs uppercase border-b"><th class="py-2 pr-4">Screen Name</th><th class="py-2 pr-4">Default Complexity</th><th class="py-2"></th></tr></thead>
                            <tbody id="screenTmplBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 5: Integrations -->
                <div class="card step hidden" data-step="5">
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

                <!-- Step 6: Platforms -->
                <div class="card step hidden" data-step="6">
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

                <!-- Step 7: Maintenance -->
                <div class="card step hidden" data-step="7">
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

                <!-- Step 8: Packages -->
                <div class="card step hidden" data-step="8">
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

                <!-- Step 9: Review -->
                <div class="card step hidden" data-step="9">
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
