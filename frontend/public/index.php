<?php
$pageTitle = 'Pricing Tool — Sign In';
$bodyClass = 'min-h-screen bg-gradient-to-br from-indigo-50 via-white to-indigo-100 flex flex-col';
require __DIR__ . '/partials/_head.php';
?>
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Pricing &amp; PRD Generator</h1>
                <p class="text-sm text-gray-500 mt-1">Estimate. Quote. Document.</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-5">Sign In</h2>
                <form id="loginForm" novalidate>
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="label">Email</label>
                            <input type="email" id="email" required class="input-field" placeholder="you@example.com">
                        </div>
                        <div>
                            <label for="password" class="label">Password</label>
                            <input type="password" id="password" required class="input-field" placeholder="Enter your password">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full mt-6">Sign In</button>
                </form>
                <p class="text-center text-sm text-gray-500 mt-5">
                    Don't have an account? <a href="/register.php" class="text-indigo-600 font-medium hover:text-indigo-700">Create one</a>
                </p>
            </div>
        </div>
    </main>
    <script src="/assets/js/auth.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            if (!email || !password) {
                Swal.fire({ icon: 'warning', title: 'Required fields', text: 'Email and password are required' });
                return;
            }
            handleLogin(email, password);
        });
    </script>
</body>
</html>
