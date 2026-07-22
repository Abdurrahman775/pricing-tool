# Phase 4 Complete — Quote Selection + Scope Locking + Document Generation

**Date:** 2026-07-17

## What was built

### Backend
- `backend/api/projects/lock_project.php` — POST, locks a project (sets status='locked') and creates the initial quote_version with full pricing breakdown snapshot
- `backend/api/projects/version_project.php` — POST, creates a new version of a locked project (increments version_number, copies screens)
- `backend/api/quotes/select_package.php` — POST, updates the selected_package and package_price on a quote_version
- `backend/api/quotes/list_versions.php` — GET, returns all quote_versions for a project

### Frontend
- `frontend/public/quote.php` — Quote view page: shows project summary, pricing breakdown, 3 package tier cards with color coding, select/lock actions, document generation buttons, edit button for drafts
- `frontend/public/assets/js/quote.js` — Quote interaction: load quote breakdown, render tier cards, select package, lock project, generate DOCX (proposal or PRD), edit draft project

### Doc Generator
- `generator/DocxGenerator.php` — PHPWord wrapper: sanitizes HTML (strips <html>/<head>/<style>), converts to DOCX, returns as string
- `generator/PdfGenerator.php` — Dompdf wrapper (kept for reference, DOCX is default output)
- `generator/templates/proposal.php` — Client proposal template (original PDF version)
- `generator/templates/proposal_docx.php` — Client proposal DOCX template (inline styles, no wrapper tags)
- `generator/templates/prd.php` — Developer PRD template (original PDF version)
- `generator/templates/prd_docx.php` — Developer PRD DOCX template (inline styles, no wrapper tags)
- `generator/generate_doc.php` — Unified generation endpoint: rate-limited (30/hr/user), audited, outputs DOCX with proper Content-Type and Content-Disposition headers
- `generator/export.php` — Config JSON export (strips internal IDs, downloads as .json)

### Infrastructure
- `router.php` — Added `/generate-doc` route
- `composer.json` — Added `phpoffice/phpword` dependency

### Security
- Rate limiting on doc generation: 30 per hour per user via checkRateLimit()
- CSRF on all POST endpoints
- Audit logging for doc.generate events

### Testing
- `tests/test_document_gen.php` — 9 tests covering: project creation, lock, double-lock rejection, package selection, proposal DOCX content, PRD out-of-scope section, invalid doc_type, new version creation, draft project rejection. All pass.

## Design Notes
- DOCX is the output format (PDF was replaced due to Dompdf limitations)
- Templates check for `_docx.php` variant first, fall back to `.php` variant
- Selected package is stored on the quote_version row (selected_package, package_price columns)
- Versioning: each lock/relock creates version_number+1; screens are copied; pricing is recalculated

## Next Phase: Phase 5 — History, Settings & Polish
Needs: All prior phases complete
