<?php
$pageTitle = 'Pricing Tool — Create Account';
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
                <h2 class="text-lg font-semibold text-gray-900 mb-5">Create Account</h2>
                <form id="registerForm" novalidate>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="label">Full Name</label>
                            <input type="text" id="name" name="name" required class="input-field" placeholder="John Doe">
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input type="email" id="email" name="email" required class="input-field" placeholder="you@example.com">
                        </div>
                        <div>
                            <label for="password" class="label">Password</label>
                            <input type="password" id="password" name="password" required minlength="8" class="input-field" placeholder="Min. 8 characters">
                        </div>
                        <div>
                            <label for="confirm_password" class="label">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required class="input-field" placeholder="Repeat password">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full mt-6">Create Account</button>
                </form>
                <p class="text-center text-sm text-gray-500 mt-5">
                    Already have an account? <a href="/" class="text-indigo-600 font-medium hover:text-indigo-700">Sign in</a>
                </p>
            </div>
        </div>
    </main>
    <footer class="text-center py-4 text-gray-400 text-xs">
        &copy; 2026 Pricing &amp; PRD Generator
    </footer>
    <script src="/assets/js/auth.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (!name || !email || !password || !confirm) {
                Swal.fire({ icon: 'warning', title: 'Required fields', text: 'Please fill in all fields' });
                return;
            }
            if (password !== confirm) {
                Swal.fire({ icon: 'error', title: 'Passwords do not match', text: 'Please re-enter your password' });
                return;
            }
            if (password.length < 8) {
                Swal.fire({ icon: 'error', title: 'Password too short', text: 'Password must be at least 8 characters' });
                return;
            }
            await handleRegister(name, email, password);
        });
    </script>
</body>
</html>
