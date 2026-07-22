# AGENTS.md — Pricing & PRD Generator Tool

## Project Overview

Multi-user web tool for estimating software project pricing and auto-generating client proposals & developer PRDs. Each user is independent (multi-tenant by `user_id`), configures their own pricing rules, and produces quotes with transparent breakdowns.

**Stack:** PHP backend · MySQL (InnoDB, utf8mb4) · HTML + Tailwind CSS + vanilla JS frontend · SweetAlert2 · Dompdf/TCPDF for PDF generation

**Source of truth for all requirements:** `PRD_Pricing_Tool.docx`

---

## Agent Roles & Directory Ownership

Each agent owns its directory exclusively. **No agent edits files outside its assigned directory** without explicit cross-agent coordination documented in a shared file (`AGENTS.md` or a `.handoff/` note).

| Agent | Directory | Responsibilities |
|-------|-----------|-----------------|
| **Database** | `database/` | Schema, seed data, migration SQL files. Every table scoped by `user_id`. |
| **Backend** | `backend/` | PHP API endpoints, auth (session-based), pricing engine, PDO data layer, config CRUD. |
| **Frontend** | `frontend/` | HTML pages, Tailwind CSS, vanilla JS, SweetAlert2, responsive design. Communicates via `fetch()` to `backend/api/`. |
| **Doc Generator** | `generator/` | PDF (Dompdf) and Word (PHPWord) generation, client proposal templates, developer PRD templates, optional AI-assisted drafting (OpenAI-compatible chat completions API, falls back to static templates when unconfigured), export/import of config JSON. |
| **Security** | `security/` | Input validation middleware, CSRF tokens, rate limiting, XSS prevention, session hardening, audit trail. |
| **Testing** | `tests/` | Manual test scripts, integration test PHP files, security audit checklists. Runs tests against all other agents' work. |

### Directory Layout

```
pricing/
├── PRD_Pricing_Tool.docx         # Requirements — read before any feature work
├── AGENTS.md                     # This file
├── router.php                    # Single entry point (php -S router). Maps /api/* to backend/api/*
│                                  #   by path, /generate-doc to generator/generate_doc.php,
│                                  #   /api/config/export|import to generator/export.php|import.php,
│                                  #   everything else to frontend/public/*
├── .env.example                  # DB creds, app key, base URL, optional AI_API_KEY/AI_API_URL/AI_MODEL
├── database/                     # Database Agent
│   ├── schema.sql                # All tables (plural, snake_case: users, projects, pricing_configs, ...)
│   ├── seed.sql
│   └── migrations/
│       └── 001_initial_schema.php
├── backend/                      # Backend Agent
│   ├── config/
│   │   ├── database.php          # PDO connection singleton (getDB())
│   │   └── app.php                # App constants, env loading (loadEnv())
│   ├── api/
│   │   ├── auth/                  # login.php, register.php, logout.php, me.php, update_profile.php
│   │   ├── config/                # save_config.php, get_config.php, list_configs.php, duplicate_config.php
│   │   ├── projects/               # create_project.php, get_project(s).php, update_project.php,
│   │   │                          #   lock_project.php, version_project.php
│   │   └── quotes/                # calculate_price.php, select_package.php, list_versions.php
│   ├── models/                   # User.php, Project.php, PricingConfig.php, QuoteVersion.php
│   └── helpers/                  # PricingEngine.php, Response.php, Validator.php
├── frontend/                     # Frontend Agent
│   ├── public/
│   │   ├── index.php             # Landing / login page
│   │   ├── register.php
│   │   ├── dashboard.php
│   │   ├── wizard.php            # First-time config wizard
│   │   ├── intake.php            # New project intake form
│   │   ├── quote.php             # Quote view with tier selection
│   │   ├── quotes.php            # Quote/version history list
│   │   ├── settings.php          # Config CRUD, export/import
│   │   └── assets/
│   │       ├── css/output.css    # Compiled Tailwind
│   │       └── js/               # *.js files, one per page
│   ├── src/input.css             # Tailwind source
│   ├── tailwind.config.js
│   └── package.json
├── generator/                    # Doc Generator Agent
│   ├── templates/
│   │   ├── proposal.php          # Client proposal template (PDF)
│   │   ├── proposal_docx.php     # Client proposal template (Word)
│   │   ├── prd.php                # Developer PRD template (PDF)
│   │   └── prd_docx.php          # Developer PRD template (Word)
│   ├── AiClient.php              # OpenAI-compatible chat completions client
│   ├── AiPrompts.php             # System/user prompt builders for AI-assisted drafting
│   ├── generate_doc.php          # Entry point (routed from /generate-doc); picks AI vs. template path
│   ├── PdfGenerator.php          # Dompdf wrapper
│   ├── DocxGenerator.php         # PHPWord wrapper
│   ├── export.php                # Config JSON export
│   └── import.php                # Config JSON import
├── tests/                        # Testing Agent
│   ├── test_pricing_engine.php   # Exercises backend pricing calculation
│   ├── test_auth_flow.php        # Signup → login → logout → protected route
│   ├── test_config_flow.php      # Pricing config CRUD, duplicate, export/import
│   ├── test_document_gen.php     # Generate PDF, verify content includes out-of-scope section
│   ├── test_e2e_flow.php         # Full signup → intake → quote → lock → document flow
│   └── security_audit.md         # Checklist of security requirements to verify manually
└── security/                     # Security Agent
    ├── middleware.php             # CSRF check, rate-limit check, auth check
    └── ratelimit.php               # Rate-limit state persisted to backend/cache/ratelimit/*.json
```

---

## Build, Run & Dev Commands

### Dev Server
```bash
php -S localhost:8000 router.php
```

### Tailwind CSS (run alongside dev server)
```bash
cd frontend && npx tailwindcss -i ./src/input.css -o ./public/assets/css/output.css --watch
```

### Database Setup
```bash
mysql -u root -p -e "CREATE DATABASE pricing_tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p pricing_tool < database/schema.sql
mysql -u root -p pricing_tool < database/seed.sql
```

### Lint / Syntax Check
```bash
php -l backend/api/auth/login.php          # Lint single file
find backend frontend/public generator security tests -name '*.php' -exec php -l {} \;  # Lint all PHP
```

### Running Tests (manual test scripts)
```bash
php tests/test_pricing_engine.php
php tests/test_auth_flow.php
php tests/test_document_gen.php
```

---

## Code Style Guidelines

### PHP (Backend + Generator + Security)
- **Standard:** PSR-12. 4-space indentation. No tabs.
- **Strict types:** `declare(strict_types=1);` at top of every PHP file.
- **Database:** PDO prepared statements only — never raw SQL string interpolation. Use `getDB()` from `backend/config/database.php`.
- **Naming:**
  | Element | Convention | Example |
  |---------|-----------|---------|
  | Classes | `PascalCase` | `class PricingEngine` |
  | Functions/methods | `camelCase` | `calculateProjectPrice()` |
  | Variables | `camelCase` | `$totalPoints` |
  | Files | `snake_case` | `create_project.php` |
  | Constants | `UPPER_SNAKE` | `DEFAULT_BASE_RATE` |
- **Error handling:** Specific exception types (`PDOException`, `ValidationException`). Never bare `catch`. Never `@` error suppression.
- **API responses:** Always return JSON with shape `{"success": true/false, "data": ..., "error": "..."}`. Use `Response::json()` helper.
- **Session:** Use PHP native sessions with `session_regenerate_id(true)` on login.

### JavaScript (Frontend)
- **Naming:** `camelCase` for variables and functions. `PascalCase` for constructors/classes.
- **Declarations:** `const` by default, `let` only when rebinding needed. No `var`.
- **APIs:** Use `fetch()` with async/await. Never use `XMLHttpRequest`.
- **SweetAlert2:** All user-facing modals, confirmations, toasts, and errors use `Swal.fire()`. Never `alert()`, `confirm()`, or `prompt()`.
- **DOM queries:** Prefer `querySelector`/`querySelectorAll`. Avoid jQuery.

### HTML
- Semantic HTML5 elements (`<header>`, `<main>`, `<section>`, `<nav>`, `<form>`, `<footer>`).
- Every `<form>` has a `<label>` for each input, with `for` attribute.
- Responsive viewport meta tag on every page.
- No inline styles — use Tailwind utility classes exclusively.

### Tailwind CSS
- Utility-first approach. Extract components with `@apply` only when the same 5+ utility classes repeat in 3+ places.
- Mobile-first responsive: `sm:`, `md:`, `lg:`, `xl:` breakpoints. Design for mobile first, then expand.
- Primary color palette: use Tailwind's blue/indigo or custom in `tailwind.config.js`.

---

## API Conventions (Backend ↔ Frontend)

- All API routes live under `backend/api/` and map to URL path after `/api/`.
- **Method convention:** POST for all mutations (create, update, delete). GET for reads.
- **Request body:** `application/json`. Frontend sends `JSON.stringify()` in `fetch()`.
- **Response shape (consistent everywhere):**
  ```json
  {"success": true, "data": { ... }}
  {"success": false, "error": "Human-readable message"}
  {"success": false, "errors": {"field": ["Message"]}}  // validation failures
  ```
- **Auth:** Session-based. `PHPSESSID` cookie set on login. Protected API routes call `requireAuth()` from `security/middleware.php`.
- **CSRF:** Every POST request must include a `X-CSRF-Token` header. Token stored in session, injected into page as `<meta>` tag by frontend.

---

## Database Conventions

- **Engine:** InnoDB.
- **Charset:** `utf8mb4` / `utf8mb4_unicode_ci`.
- **Naming:** `snake_case`, plural table names (`users`, `projects`, `pricing_configs`, `quote_versions`, etc.).
- **Every table** has:
  - `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
  - `user_id INT UNSIGNED NOT NULL` (multi-tenant scoping)
  - `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
  - `updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
- **Foreign keys** with `ON DELETE CASCADE` for user-owned data.
- **Indexes** on all `user_id` columns and any column used in `WHERE`/`JOIN`/`ORDER BY`.
- **Prepared statements only** — never concatenate user input into SQL.

---

## Security Requirements

These are enforced by the Security Agent and verified by the Testing Agent.

1. **CSRF:** Double-submit cookie pattern. Every POST endpoint verifies `X-CSRF-Token` header matches session token. Exempt GET-only endpoints.
2. **XSS:** All user-supplied text rendered in HTML must pass through `htmlspecialchars($text, ENT_QUOTES, 'UTF-8')` on the backend. Frontend uses `textContent()` over `innerHTML` for dynamic text.
3. **SQL injection:** PDO prepared statements everywhere. Zero exceptions.
4. **Auth:**
   - Passwords: `password_hash(PASSWORD_BCRYPT)` with cost 12. Never md5/sha1.
   - Session: `session_regenerate_id(true)` after login.
   - Rate limit: 10 login attempts per IP per 15 min (implemented in `security/ratelimit.php`).
5. **Rate limiting (per PRD §10.2):** Document generation capped at 30/hour per user. Auth attempts at 10/15min per IP.
6. **Audit trail:** All auth events (login, failed login, logout), config changes, and document generations logged to `audit_log` table.
7. **File uploads (if any):** Validate MIME type server-side, never trust extension. Store outside webroot.
8. **Output escaping:** `htmlspecialchars()` everywhere. No unescaped `echo $var`.

---

## Testing Approach (Manual Test Scripts)

The Testing Agent writes standalone PHP scripts in `tests/` that simulate HTTP requests and validate responses. These are run with `php tests/script_name.php` and report pass/fail to stdout.

**What every test must cover:**
- **Auth:** signup with valid/invalid data, login, session persistence, logout destroys session, protected routes reject unauthenticated users.
- **Pricing engine:** correct point calculation, integration point addition, platform multiplier, package tier generation, zero/invalid inputs handled gracefully.
- **Document generation:** PDF output is valid, proposal contains package name + price, PRD contains "out of scope" section, document generation fails gracefully without required data.
- **Security test checklist (from `tests/security_audit.md`):** CSRF missing → 403, XSS payload in project name → escaped in output, SQL injection in ID param → no error/leak, rate limit exceeded → 429.

**Before marking a feature complete:** the Testing Agent must have a passing test script for it, and the Security Agent must sign off that no new vulnerability was introduced.

---

## PRD-Specific Constraints (from PRD_Pricing_Tool.docx)

- **Multi-tenant isolation:** Every row scoped to `user_id`. No user sees another's data. No admin role exists.
- **Multiple pricing configs per user:** Users hold named configs (e.g., "Standard", "Discount Client"). New quotes select which config to use. Built from day one.
- **Scope locking:** Once a package tier is selected, the project becomes read-only. Changes create a new version — previous version remains frozen.
- **Pricing breakdown:** The engine must show a transparent breakdown (screen points + integration points + platform multiplier → subtotal → × base rate → package multipliers → final prices), never just a final number.
- **Generated documents must include:** For proposals — selected package, price, what's included, what's excluded, timeline/milestone placeholders, payment terms placeholders. For PRDs — project overview, screens with complexity tags (and notes), integrations, platforms, explicit out-of-scope section, maintenance terms, timeline.
- **Default pricing template:** Ships with values so a new user can produce a quote on day one without filling any config fields.
- **Config export/import:** As JSON from settings page. No account merging.
- **Schema fields for future:** `actual_cost` and `actual_hours` columns on the project/quote table (PRD §10.1 — estimate-vs-actual feedback loop, v1.5).
- **Document export:** Both PDF (Dompdf) and Word (PHPWord) are implemented, generated via `generator/generate_doc.php`.
- **Maintenance pricing:** Supports both flat monthly rate and percentage-of-project-price per month (default 10–15%).

---

## Handoff Between Agents

When one agent finishes a task that another agent depends on:

1. Create a brief note in the project root or use `.handoff/` with files like `backend-ready.md` or `schema-ready.md`.
2. Specify what was completed, any design decisions made, and what the next agent needs to know.
3. The Testing Agent validates the integration point before marking both tasks done.

**Example:** Database Agent finishes `schema.sql` → creates `.handoff/schema-ready.md` listing all tables and columns → Backend and Security agents can begin their work in parallel knowing the exact table structure.

---

## Design Reference & Phases

- **Design system:** `.handoff/design-system.md` — Color palette, layout patterns, Tailwind config extracted from `images/Screenshot from 2026-07-17 12-08-58.png`.
- **Project phases:** `.handoff/phases.md` — Phased build order with agent assignments, dependencies, and deliverables for each phase.
- **Design screenshot:** `images/Screenshot from 2026-07-17 12-08-58.png` — Visual reference showing header gradient, navy hero card, cyan accents, and centered content layout on blue-50 page background.
