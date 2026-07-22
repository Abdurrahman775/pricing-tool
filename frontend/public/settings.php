<?php
$pageTitle = 'Pricing Tool — Settings';
$activeNav = 'settings';
$mobileTitle = 'Settings';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
        <main class="p-4 md:p-6 max-w-4xl mx-auto space-y-6">
            <!-- Profile -->
            <div class="card-elevated">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile</h2>
                <form id="profileForm" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Name</label>
                            <input type="text" id="profileName" class="input-field" required>
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input type="email" id="profileEmail" class="input-field" readonly disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary text-sm">Save Changes</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card-elevated">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h2>
                <form id="passwordForm" class="space-y-4">
                    <div>
                        <label class="label">Current Password</label>
                        <input type="password" id="currentPassword" class="input-field" required>
                    </div>
                    <div>
                        <label class="label">New Password</label>
                        <input type="password" id="newPassword" class="input-field" required minlength="8">
                        <p class="text-xs text-gray-400 mt-1">At least 8 characters</p>
                    </div>
                    <button type="submit" class="btn-primary text-sm">Update Password</button>
                </form>
            </div>

            <!-- Config Import / Export -->
            <div class="card-elevated">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Config Import / Export</h2>
                <p class="text-sm text-gray-500 mb-4">Export a pricing configuration as JSON, or import one from a file.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Export -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Export Config</h3>
                        <select id="exportConfigSelect" class="input-field mb-3">
                            <option value="">— Select config —</option>
                        </select>
                        <button id="exportConfigBtn" class="btn-secondary text-sm w-full" disabled>Download JSON</button>
                    </div>
                    <!-- Import -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Import Config</h3>
                        <p class="text-xs text-gray-400 mb-2">JSON file exported from Pricing Tool.</p>
                        <input type="file" id="importFileInput" accept=".json" class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mb-3">
                        <button id="importConfigBtn" class="btn-primary text-sm w-full" disabled>Import Config</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script src="/assets/js/settings.js"></script>
</body>
</html>
