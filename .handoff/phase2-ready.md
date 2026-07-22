# Phase 2 Complete — Pricing Configuration (Wizard + Settings)

**Date:** 2026-07-17

## What was built

### Backend
- `backend/api/config/list_configs.php` — GET, returns all configs for the authenticated user
- `backend/api/config/get_config.php?id=X` — GET, returns full config with all child records (tiers, integrations, platforms, packages, maintenance)
- `backend/api/config/save_config.php` — POST, creates or updates a config with full nested data (upsert pattern: delete + re-insert children)
- `backend/api/config/duplicate_config.php` — POST, deep-copies a config with all children
- `backend/models/PricingConfig.php` — `createFromTemplate()`, `getFullConfig()`, `listByUser()`, `duplicate()` with hardcoded fallback defaults

### Frontend
- `frontend/public/wizard.php` — 8-step configuration wizard (Welcome → Currency → Complexity Tiers → Integrations → Platforms → Maintenance → Packages → Review), responsive, navy cards with cyan accents
- `frontend/public/assets/js/wizard.js` — Full wizard logic: step navigation, data collection, add/remove dynamic rows, review builder, save with CSRF
- `frontend/public/dashboard.php` — Updated to fetch and display configs from `/api/config/list_configs.php`

### Doc Generator
- `generator/export.php` — Config JSON download endpoint, strips internal IDs, sets Content-Disposition header for download

### Changes to existing files
- `database/schema.sql` — Changed `pricing_configs.user_id` to `DEFAULT NULL` to allow reference default template
- `backend/api/auth/register.php` — Now auto-creates a default pricing config for new users via `PricingConfig::createFromTemplate()`

### Testing
- `tests/test_config_flow.php` — 6 manual tests (list, get, duplicate, 404s, save new config), all passing

## Design Notes
- Config save uses delete+re-insert for child records (simpler than diff-based updates)
- Default template (user_id = NULL) in seed.sql is copied to new users on registration
- Config name limited to 255 chars
- All config endpoints protected by `requireAuth()` + CSRF for POST

## Next Phase: Phase 3 — Project Intake + Pricing Engine
Needs: Phase 2 complete (pricing configs exist)

Agents needed: Backend (project CRUD, pricing engine), Frontend (intake form), Database, Security, Testing
