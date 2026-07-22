<?php
$pageTitle = 'Pricing Tool — Quote';
$activeNav = '';
$mobileTitle = 'Quote';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
        <main class="p-4 md:p-6 max-w-6xl mx-auto">
            <!-- Loading -->
            <div id="loadingState" class="card loading-state">
                <span class="spinner" aria-hidden="true"></span> Loading quote&hellip;
            </div>

            <!-- Quote Content -->
            <div id="quoteContent" class="hidden space-y-6">
                <!-- Project Info Bar -->
                <div class="card flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900" id="projectName">-</h2>
                        <p class="text-gray-500 text-sm mt-1">Client: <span id="clientName" class="font-medium text-gray-700">-</span></p>
                    </div>
                    <div class="text-right">
                        <span id="projectStatus" class="badge-draft">Draft</span>
                        <p class="text-xs text-gray-400 mt-1" id="projectDate"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main: Pricing Breakdown + Packages -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Pricing Breakdown -->
                        <div class="card-elevated">
                            <h2 class="font-semibold text-gray-900 mb-4">Pricing Breakdown</h2>
                            <div id="pricingContent"></div>
                        </div>

                        <!-- Packages -->
                        <div class="card-elevated">
                            <h2 class="font-semibold text-gray-900 mb-4">Select a Package</h2>
                            <div id="packagesGrid" class="grid grid-cols-1 md:grid-cols-3 gap-4"></div>
                        </div>
                    </div>

                    <!-- Sidebar: Actions + Version History -->
                    <div class="space-y-6">
                        <!-- Actions -->
                        <div class="card-elevated">
                            <h2 class="font-semibold text-gray-900 mb-4">Actions</h2>
                            <div class="space-y-3">
                                <div class="text-sm text-gray-500">
                                    <span id="selectedPackageDisplay" class="text-gray-400">No package selected</span>
                                </div>
                                <button id="editProjectBtn" class="btn-secondary w-full justify-center text-sm">Edit Project</button>
                                <button id="lockProjectBtn" class="btn-secondary w-full justify-center text-sm">Lock Project</button>
                                <button id="versionProjectBtn" class="btn-secondary w-full justify-center text-sm hidden">Create New Version</button>
                                <button id="genProposalBtn" class="btn-primary w-full justify-center text-sm" disabled>Generate Proposal</button>
                                <button id="genPrdBtn" class="btn-success w-full justify-center text-sm" disabled>Generate PRD</button>
                                <button id="genDocsBtn" class="btn-info w-full justify-center text-sm" disabled>Generate Documentation</button>
                            </div>
                        </div>

                        <!-- Version History -->
                        <div class="card-elevated">
                            <h3 class="font-semibold text-gray-900 mb-4">Version History</h3>
                            <div id="versionHistory" class="text-gray-500 text-sm text-center py-4">
                                No versions yet. Lock the project to create version 1.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script src="/assets/js/quote.js"></script>
</body>
</html>
