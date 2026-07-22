# Phase 3 Complete — Project Intake + Pricing Engine

**Date:** 2026-07-17

## What was built

### Backend
- `backend/api/projects/create_project.php` — POST, creates a project with screens, integrations, platforms, and maintenance config. Validates config ownership first.
- `backend/api/projects/get_projects.php` — GET, returns all projects for the authenticated user
- `backend/api/projects/get_project.php?id=X` — GET, returns full project with screens, breakdown, versions
- `backend/api/quotes/calculate_price.php` — POST, pricing engine: computes screen points × complexity multipliers + integration points, applies platform multiplier, then × base rate for subtotal, then × each package tier multiplier for final prices. Returns transparent breakdown.
- `backend/models/Project.php` — Project model: create, getFull, listByUser, updateDraft
- `backend/models/QuoteVersion.php` — QuoteVersion model: create, getLatest, selectPackage, listVersions

### Frontend
- `frontend/public/intake.php` — Project intake form with: project name, client name, dynamic screens with complexity dropdowns, integration checkboxes, platform selection, maintenance config, config selector (picks the pricing config to use)
- `frontend/public/assets/js/intake.js` — Form handling: add/remove screens, calculate price preview on input change, submit to create_project.php, edit mode (?edit=ID)

### Security
- Input validation in create_project.php (Validator class): required fields, numeric ranges, array structure validation
- CSRF enforcement on POST
- Ownership checks on all project/config lookups

### Testing
- `tests/test_pricing_engine.php` — 7 tests covering: basic calculation, breakdown structure, package tiers, empty screens rejection, missing config, nonexistent config, maintenance calculation. All pass.

## Design Notes
- Pricing breakdown returned in calculate_price.php includes: screen_points, integration_points, total_points, platform_multiplier, subtotal, base_rate, package_tiers[{name, multiplier, price, maintenance{model, value, amount}}], currency
- Project supports edit mode for draft (unlocked) projects via ?edit=ID in intake.php
- Maintenance is calculated as flat monthly or percentage-of-price, stored in breakdown JSON but NOT written to a separate DB column until project lock

## Next Phase: Phase 4 — Quote Selection + Scope Locking + Document Generation
Needs: Phase 3 complete (project + pricing data exists)
