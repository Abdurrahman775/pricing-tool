<?php
$pageTitle = 'Pricing Tool — Quote History';
$activeNav = 'quotes';
$mobileTitle = 'Quote History';
require __DIR__ . '/partials/_head.php';
require __DIR__ . '/partials/_sidebar.php';
?>
        <main class="p-4 md:p-6 max-w-6xl mx-auto">
            <div class="card-elevated">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">All Quotes</h2>
                    <a href="/intake.php" class="btn-primary text-sm">+ New Quote</a>
                </div>
                <div id="quoteList" class="loading-state">
                    <span class="spinner" aria-hidden="true"></span> Loading quotes&hellip;
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/layout.js"></script>
    <script src="/assets/js/quotes.js"></script>
</body>
</html>
