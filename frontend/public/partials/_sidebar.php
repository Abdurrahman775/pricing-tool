<?php
/**
 * Shared app-shell chrome: sidebar nav + topbar.
 * Caller must set $activeNav before requiring this file (one of:
 * 'dashboard', 'intake', 'quotes', 'wizard', 'settings', or '' for none).
 * Optional: $mobileTitle — shown as an <h1> in the topbar on small screens.
 * Opens the "lg:ml-64" content wrapper and <header>; caller is responsible
 * for closing it with </main></div> after their <main> content.
 */

$navItems = [
    'dashboard' => [
        'href' => '/dashboard.php',
        'label' => 'Dashboard',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    ],
    'intake' => [
        'href' => '/intake.php',
        'label' => 'New Quote',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    ],
    'quotes' => [
        'href' => '/quotes.php',
        'label' => 'Quote History',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
    ],
    'wizard' => [
        'href' => '/wizard.php',
        'label' => 'Configurations',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ],
    'settings' => [
        'href' => '/settings.php',
        'label' => 'Settings',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ],
];
?>
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-indigo-950 text-white z-50 -translate-x-full lg:translate-x-0 transition-transform duration-200 flex flex-col">
        <div class="flex items-center gap-3 px-4 h-16 border-b border-indigo-800">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <span class="font-bold text-sm">Pricing &amp; PRD</span>
        </div>
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto" aria-label="Main navigation">
            <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="<?= $activeNav === $key ? 'sidebar-link-active' : 'sidebar-link' ?>"
               <?= $activeNav === $key ? 'aria-current="page"' : '' ?>>
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><?= $item['icon'] ?></svg>
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-3 border-t border-indigo-800">
            <button type="button" onclick="handleLogout()" class="sidebar-link w-full">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <header class="sticky top-0 bg-white border-b border-gray-200 z-20">
            <div class="flex items-center justify-between px-4 h-16">
                <button type="button" id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Toggle navigation menu" aria-controls="sidebar" aria-expanded="false">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <?php if (!empty($mobileTitle)): ?>
                <h1 class="text-lg font-semibold text-gray-900 lg:hidden"><?= htmlspecialchars($mobileTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php endif; ?>
                <div class="flex items-center gap-2 ml-auto">
                    <span class="text-sm text-gray-500">Welcome,</span>
                    <span class="font-medium text-gray-900 text-sm" id="userName">loading...</span>
                </div>
            </div>
        </header>
