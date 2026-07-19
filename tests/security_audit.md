# Security Audit Checklist

Run through this checklist manually or via `tests/test_e2e_flow.php` (which verifies items 1-5 programmatically).

---

## 1. CSRF Protection

- [ ] Every POST endpoint includes `requireCsrfToken()` after `startSecureSession()`
- [ ] Every page has `<meta name="csrf-token" content="...">` with `generateCsrfToken()`
- [ ] All AJAX requests include `X-CSRF-Token` header from the meta tag
- [ ] Token is validated with `hash_equals()` (constant-time comparison)

**Endpoints:** login, register, logout, save_config, duplicate_config, create_project, lock_project, version_project, calculate_price, select_package, generate_doc, export, import

---

## 2. XSS Prevention

- [ ] All user-supplied text in PHP templates uses `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- [ ] All dynamic JS content inserted via `innerHTML` uses `escHtml()` sanitizer
- [ ] No `innerHTML` assignments without sanitization
- [ ] SweetAlert2 uses `text:` property (safe) rather than `html:` with user input
- [ ] `textContent` is preferred over `innerHTML` where possible

**Key files:** `generator/templates/*.php`, `frontend/public/assets/js/*.js`

---

## 3. SQL Injection

- [ ] All queries use PDO prepared statements with named parameters
- [ ] `PDO::ATTR_EMULATE_PREPARES` is set to `false`
- [ ] No raw string interpolation in SQL (no `"WHERE id = $var"`)
- [ ] Table names in dynamic queries come from hardcoded arrays, not user input

---

## 4. Authentication

- [ ] Passwords hashed with `password_hash(PASSWORD_BCRYPT, ['cost' => 12])`
- [ ] `session_regenerate_id(true)` called after successful login
- [ ] `session_regenerate_id(true)` called every hour for active sessions
- [ ] Session cookie: `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS
- [ ] Rate limiting: 10 login attempts per IP per 15 minutes
- [ ] Failed login attempts logged to `audit_log`

---

## 5. Authorization (Multi-Tenant)

- [ ] Every query scoped by `user_id` — no user sees another's data
- [ ] `requireAuth()` called on all protected endpoints
- [ ] No admin role exists (per PRD: no cross-user access)
- [ ] Project/Config ownership verified in `getFull()` / `getFullConfig()` by passing `userId`

---

## 6. Audit Trail

- [ ] All auth events logged: `user.register`, `user.login`, `user.login.failed`, `user.logout`
- [ ] All config mutations logged: `config.create`, `config.update`, `config.duplicate`, `config.export`, `config.import`
- [ ] All project mutations logged: `project.create`, `project.lock`, `project.version`
- [ ] All quote actions logged: `quote.select_package`
- [ ] All document generations logged: `doc.generate`
- [ ] Audit entries include: `user_id`, `action`, `ip_address`, `user_agent`, `details` (JSON)

---

## 7. Rate Limiting

- [ ] Login: 10 attempts per IP per 15 minutes
- [ ] Document generation: 30 per user per hour
- [ ] Rate limit returns HTTP 429 with descriptive message
- [ ] Storage is file-based with `LOCK_EX` for atomicity

---

## 8. File Security

- [ ] Router validates resolved path stays within `backend/` directory (uses `realpath()` + `str_starts_with()`)
- [ ] Composer dependencies: Dompdf `isPhpEnabled` set to `false`
- [ ] No file upload endpoints (future: validate MIME server-side, store outside webroot)

---

## 9. Output & Error Handling

- [ ] API always returns JSON with consistent shape: `{"success": bool, "data": ..., "error": "...", "errors": {...}}`
- [ ] No stack traces or debug info leaked in production errors
- [ ] HTTP status codes appropriate: 200 success, 201 created, 400 validation, 401 auth, 403 CSRF, 404 not found, 409 conflict, 422 unprocessable, 429 rate limit, 500 server error

---

## 10. Dependency Security

- [ ] `composer.json` lists only required packages (`dompdf/dompdf` + PHP)
- [ ] No dev dependencies in production (`--no-dev` for deploy)

---

## Automated Test Coverage

| Test Suite | Coverage |
|-----------|----------|
| `tests/test_auth_flow.php` | Auth flow, rate limiting, CSRF on login/register |
| `tests/test_e2e_flow.php` | Full flow + audit trail verification |
| `tests/test_pricing_engine.php` | Pricing calculation edge cases |
| `tests/test_document_gen.php` | Document generation with rate limiting |
