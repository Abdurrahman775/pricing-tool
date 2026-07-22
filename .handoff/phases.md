# Project Phases — Pricing & PRD Generator

## Phase 0: Foundation (Parallel Setup)

All agents work simultaneously. No dependencies between them.

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Database** | Schema design per PRD data model; seed data (default pricing template) | `database/schema.sql`, `database/seed.sql`, `database/migrations/` |
| **Backend** | Project scaffolding: config files, PDO connection singleton, env loading, helper classes | `backend/config/database.php`, `backend/config/app.php`, `backend/helpers/Response.php`, `backend/helpers/Validator.php`, `.env.example` |
| **Frontend** | Tailwind + package setup; input.css with custom colors; base layout templates (header, footer, nav) | `frontend/package.json`, `frontend/tailwind.config.js`, `frontend/src/input.css`, `frontend/public/assets/css/output.css` |
| **Security** | Middleware skeleton; CSRF token generation; session config hardening | `security/middleware.php`, `security/ratelimit.php`, `security/audit_trail_schema.sql` |
| **Doc Generator** | No tasks yet (depends on backend quotes) | — |
| **Testing** | Read PRD + AGENTS.md; prepare test infrastructure; write test templates | `tests/bootstrap.php` (shared helpers), `tests/security_audit.md` |

**Finish condition:** All agents write `.handoff/phase0-ready.md`. Backend + Frontend + Security + Database can all be tested in isolation.

---

## Phase 1: Authentication System

Dependencies: Phase 0 complete. Backend needs Database (users table). Frontend needs Backend (API routes).

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Backend** | Register, login, logout, session management, User model | `backend/api/auth/register.php`, `login.php`, `logout.php`, `backend/models/User.php` |
| **Frontend** | Login page + register page with design system (centered card on blue-50 bg, responsive) | `frontend/public/index.html`, `register.html`, `frontend/public/assets/js/auth.js` |
| **Security** | Auth middleware (requireAuth), CSRF token injection on pages, rate limiting on login, password hashing config, session regeneration | Update `security/middleware.php`, `security/ratelimit.php` |
| **Testing** | Test auth flow: signup → login → protected route → logout → protected route rejected | `tests/test_auth_flow.php` |
| **Database** | Users table (part of schema.sql from Phase 0) | Already in schema |
| **Doc Generator** | No tasks | — |

**Finish condition:** A user can register, log in, see a protected page, and log out. Tests pass.

---

## Phase 2: Pricing Configuration (Wizard + Settings)

Dependencies: Phase 1 complete (user must be logged in).

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Backend** | Config CRUD: save/get/duplicate/list; PricingConfig model; default template endpoint | `backend/api/config/save_config.php`, `get_config.php`, `duplicate_config.php`, `list_configs.php`, `backend/models/PricingConfig.php` |
| **Frontend** | Dashboard page (with logged-in nav, empty state); 7-step config wizard (wizard.html + wizard.js); settings page | `frontend/public/dashboard.html`, `wizard.html`, `assets/js/dashboard.js`, `assets/js/wizard.js` |
| **Database** | pricing_config table (part of schema.sql from Phase 0) | Already in schema |
| **Doc Generator** | Config export (JSON download) endpoint | `generator/export.php` |
| **Security** | CSRF enforcement on config POST endpoints; audit logging for config changes | Update middleware + audit log |
| **Testing** | Test config save/load/duplicate; test wizard flow with valid/invalid data | `tests/test_config_flow.php` |

**Finish condition:** Logged-in user sees a dashboard, can create/manage pricing configs via wizard, and export config as JSON.

---

## Phase 3: Project Intake + Pricing Engine

Dependencies: Phase 2 complete (needs pricing config to exist).

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Backend** | Project create/list endpoints; pricing engine (screen points + integrations + platform multiplier + base rate → package tiers); Project model, Quote model | `backend/api/projects/create_project.php`, `get_projects.php`, `backend/api/quotes/calculate_price.php`, `backend/models/Project.php`, `backend/models/Quote.php` |
| **Frontend** | Intake form (intake.html + intake.js): add screens with complexity, select integrations, platform, maintenance | `frontend/public/intake.html`, `assets/js/intake.js` |
| **Database** | project + quote tables (part of schema.sql) | Already in schema |
| **Security** | Input validation on intake fields; rate limiting on price calculations | Update middleware |
| **Testing** | Test pricing engine: point calculation, tier generation, edge cases (zero screens, missing config) | `tests/test_pricing_engine.php` |
| **Doc Generator** | No tasks yet | — |

**Finish condition:** User can fill intake form, see transparent pricing breakdown with package tiers.

---

## Phase 4: Quote Selection + Scope Locking + Document Generation

Dependencies: Phase 3 complete (quote/project data exists).

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Backend** | Lock project endpoint; versioning logic; regenerate on version bump | `backend/api/projects/lock_project.php`, `backend/api/quotes/get_quote.php` |
| **Frontend** | Quote view page (quote.html + quote.js): show 3 package tiers with compare cards, select tier, trigger document generation | `frontend/public/quote.html`, `assets/js/quote.js` |
| **Doc Generator** | Dompdf setup; client proposal template; developer PRD template; generate endpoint | `generator/PdfGenerator.php`, `generator/templates/proposal.php`, `generator/templates/prd.php`, `generator/generate_doc.php` |
| **Security** | Rate limiting on document generation (30/hour per PRD); audit logging for doc gen | Update `security/ratelimit.php` + audit |
| **Testing** | Test document gen: PDF validity, out-of-scope section in PRD, proposal contains price+package | `tests/test_document_gen.php` |
| **Database** | No schema changes | — |

**Finish condition:** User can select a package tier, lock the project, and download a PDF proposal + PRD with correct content.

---

## Phase 5: History, Settings & Polish

Dependencies: All prior phases complete.

| Agent | Tasks | Deliverables |
|-------|-------|-------------|
| **Backend** | History listing endpoints; project detail endpoint | `backend/api/projects/get_projects.php` (enhance with pagination/history), `backend/api/projects/get_project.php` |
| **Frontend** | History view on dashboard; project detail modal/page; responsive polish across all pages; empty states; loading states; success toasts via Swal | Update `dashboard.html`, `dashboard.js`; responsive CSS audit |
| **Doc Generator** | Config import endpoint; regenerate document for older versions | `generator/import.php` |
| **Security** | Full audit trail review; XSS audit on all rendered fields; CSRF test across all endpoints | Full security review |
| **Testing** | End-to-end flow test (register → config → intake → quote → document); regression test all prior tests | `tests/test_e2e_flow.php` |
| **Database** | No schema changes | — |

**Finish condition:** User can browse past projects, regenerate documents, import configs. Full E2E flow passes. Responsive on mobile/tablet/desktop.

---

## Agent Orchestration Diagram

```
Phase 0:  [DB] [BE] [FE] [SEC]    ← all parallel
              |    |    |
Phase 1:  [DB]─[BE]─[FE]─[SEC]─[TEST]    ← sequential within phase, parallel across agents
                   |    |
Phase 2:  [BE]─[FE]─[GEN]─[SEC]─[TEST]
                   |    |
Phase 3:  [BE]─[FE]─[SEC]─[TEST]
                   |    |
Phase 4:  [BE]─[FE]─[GEN]─[SEC]─[TEST]
                   |    |
Phase 5:  [BE]─[FE]─[GEN]─[SEC]─[TEST]
```

**Key:** DB=Database, BE=Backend, FE=Frontend, GEN=Doc Generator, SEC=Security, TEST=Testing

## Quick Start — Next Actions

1. **Immediately (parallel):** All Phase 0 agents begin — each creates their directory structure and core files
2. **After Phase 0 handoff:** Phase 1 begins — Backend builds auth API, Frontend builds login/register pages, Security wires up middleware
3. **Each phase:** Testing Agent writes test scripts concurrently with feature development; Security Agent reviews all endpoints before phase is marked complete
