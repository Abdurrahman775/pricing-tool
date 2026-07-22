# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Multi-user (multi-tenant by `user_id`) web tool for estimating software project pricing and auto-generating client proposals & developer PRDs. Each user configures their own pricing rules and produces quotes with a fully transparent breakdown.

**Stack:** PHP 8.1+ (no framework, custom router) · MySQL/InnoDB (utf8mb4) · HTML rendered from `.php` view files + Tailwind CSS + vanilla JS · SweetAlert2 · Dompdf/PHPWord for PDF/DOCX generation · optional AI (OpenAI-compatible chat completions API) for document generation.

**Source of truth for product requirements:** `PRD_Pricing_Tool.docx`. **Full architecture/agent-role spec:** `AGENTS.md` (this file summarizes what's needed for day-to-day work — read `AGENTS.md` for the complete convention set, security requirements, and PRD constraints).

## Build & Run Commands

```bash
# Dev server (single command, routes everything through router.php)
php -S localhost:8000 router.php

# Tailwind, run alongside dev server (rebuilds frontend/public/assets/css/output.css)
cd frontend && npm run dev          # watch mode
cd frontend && npm run build        # minified build

# Database setup
mysql -u root -p -e "CREATE DATABASE pricing_tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p pricing_tool < database/schema.sql
mysql -u root -p pricing_tool < database/seed.sql

# Lint
php -l backend/api/auth/login.php                                                  # single file
find backend frontend/public generator security tests -name '*.php' -exec php -l {} \;   # all PHP

# Tests (standalone scripts, not a framework — print pass/fail to stdout)
php tests/test_pricing_engine.php
php tests/test_auth_flow.php
php tests/test_config_flow.php
php tests/test_document_gen.php
php tests/test_e2e_flow.php
```

Copy `.env.example` to `.env` before running — the router loads it via `loadEnv()` in `backend/config/app.php`. Key vars: `DB_HOST/PORT/NAME/USER/PASS`, `BASE_URL`, `APP_KEY`, and optional `AI_API_KEY`/`AI_API_URL`/`AI_MODEL` (falls back to template-based doc generation if `AI_API_KEY` is unset).

## Architecture

### Request routing — everything goes through `router.php`
There is no framework front controller beyond `router.php`, used both as the `php -S` router and conceptually as the single entry point:
- `/api/*` → resolved relative to `backend/` and `require`d directly (path-traversal-checked via `realpath()` + prefix match). So a request to `/api/auth/login.php` executes `backend/api/auth/login.php` directly — there is no separate route table, the URL *is* the file path.
- `/generate-doc` → `generator/generate_doc.php`
- `/api/config/export` and `/api/config/import` → routed to `generator/export.php` / `generator/import.php` (these live outside `backend/api/` because they're owned by the Doc Generator concern, not backend CRUD)
- `/` → `frontend/public/index.php`
- Anything else matching a file under `frontend/public/` is served (`.php` files are `require`d so they execute; static assets get a mime-typed `readfile()`)

Because `/api/` paths map 1:1 to files under `backend/api/`, adding a new endpoint is just adding a new PHP file in the right subdirectory (`auth/`, `config/`, `projects/`, `quotes/`) — no registration step.

### Backend layering
- `backend/config/database.php` — `getDB()` returns a lazily-created singleton `PDO` connection (exceptions on error, prepared statements enforced by disabling emulated prepares).
- `backend/config/app.php` — `.env` loader and app-wide constants (`SESSION_LIFETIME`, `BCRYPT_COST`, `CSRF_TOKEN_LENGTH`).
- `backend/helpers/` — `PricingEngine` (pure calculation logic, see below), `Response` (JSON envelope helper), `Validator`.
- `backend/models/` — `User`, `Project`, `PricingConfig`, `QuoteVersion` — thin data-access classes over PDO.
- `backend/api/**` — one file per endpoint, calls into `security/middleware.php` for auth/CSRF, then models/helpers, then `Response::json()`.

### Pricing engine (`backend/helpers/PricingEngine.php`)
Pure static calculation, no side effects — takes intake data (screens, integration IDs, platforms, maintenance) plus a user's `PricingConfig` array, and returns a fully transparent breakdown object. The calculation chain, in order, is load-bearing and must stay visible in any output (per PRD): screen points (by complexity tier) → integration points (config-defined + custom) → subtotal → × platform multiplier (highest among selected platforms, not summed) → × base_rate → base price → × package tier multipliers → final prices per package. Maintenance is computed separately as either a flat monthly rate or a percentage of base price. Never collapse this into a single output number — every consumer (quote view, proposal/PRD generator) depends on the itemized breakdown.

### Security (`security/middleware.php`)
Three functions used across nearly every protected endpoint: `requireAuth()` (401 if no `$_SESSION['user_id']`), `requireCsrfToken()` (403 if `X-CSRF-Token` header doesn't match session token, checked only on POST), `startSecureSession()` (hardened cookie params + periodic `session_regenerate_id`). Rate limiting state is persisted to JSON files in `backend/cache/ratelimit/` (see `security/ratelimit.php`) — these are runtime-generated, not something to hand-edit.

### Document generation (`generator/`)
`generate_doc.php` is the entry point (routed from `/generate-doc`). It picks between AI-assisted generation (`AiClient.php` + `AiPrompts.php`, calling an OpenAI-compatible `/chat/completions` endpoint configured via `AI_API_URL`/`AI_MODEL`/`AI_API_KEY`) and static templates (`templates/proposal.php`, `templates/prd.php`, plus `_docx` variants), then renders via `PdfGenerator.php` (Dompdf) or `DocxGenerator.php` (PHPWord). `export.php`/`import.php` handle pricing-config JSON export/import, independent of document rendering.

### Frontend
Pages are `.php` files under `frontend/public/` (not static `.html` — they're `require`d by the router so they can run PHP, e.g. session checks, CSRF token injection into a `<meta>` tag). Each page has a matching JS file in `frontend/public/assets/js/` that talks to `backend/api/*` via `fetch()`. Tailwind source is `frontend/src/input.css`, compiled to `frontend/public/assets/css/output.css` — always rebuild after touching Tailwind classes or `tailwind.config.js`.

### Multi-tenancy & data model
Every table has `id`, `user_id` (scoping — no admin role, no cross-user visibility), `created_at`/`updated_at`. Users can hold multiple named `PricingConfig`s. Once a package tier is selected on a project (`lock_project.php`), the project becomes read-only and further changes create a new `QuoteVersion` rather than mutating history (`version_project.php`, `list_versions.php`).

## Conventions (see `AGENTS.md` for full detail)

- PHP: `declare(strict_types=1);` in every file, PSR-12, PDO prepared statements only (never string-interpolated SQL), `snake_case` filenames, `PascalCase` classes, `camelCase` methods/vars.
- API responses always JSON: `{"success": true, "data": ...}` or `{"success": false, "error": "..."}` / `{"success": false, "errors": {...}}` for validation.
- JS: `const`/`let` (no `var`), `fetch()` + async/await, SweetAlert2 (`Swal.fire()`) for all user-facing dialogs — never `alert()`/`confirm()`.
- Output escaping: `htmlspecialchars($text, ENT_QUOTES, 'UTF-8')` on every user-supplied string rendered server-side.
