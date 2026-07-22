# Pricing & PRD Generator Tool

A multi-user web application for estimating software project pricing and auto-generating client proposals and developer PRDs. Each user is fully isolated (multi-tenant), configures their own pricing rules, and produces quotes with transparent cost breakdowns.

## Features

- **Multi-tenant pricing** — Each user has isolated pricing configs, projects, and quotes
- **Pricing engine** — Screen points + integration points × platform multiplier × base rate → package tiers
- **Transparent breakdowns** — See exactly how each price is calculated
- **Multiple configs** — Create named configs (e.g., "Standard", "Discount Client") and select per quote
- **Scope locking** — Lock a project version once a package is selected; changes create new versions
- **AI-powered document generation** — Generate professional proposals and PRDs via Groq AI API (falls back to templates if unconfigured)
- **Document export** — PDF (Dompdf) and Word (PHPWord), with AI-assisted formatting via docx-js
- **Config export/import** — Export/import pricing configs as JSON

## Tech Stack

- **Backend:** PHP, PDO, MySQL (InnoDB, utf8mb4)
- **Frontend:** HTML, Tailwind CSS, vanilla JavaScript, SweetAlert2
- **Document generation:** Dompdf, PHPWord, docx-js (Node.js)
- **AI integration:** OpenAI-compatible API (Groq)

## Quick Start

```bash
# 1. Setup database
mysql -u root -p -e "CREATE DATABASE pricing_tool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p pricing_tool < database/schema.sql
mysql -u root -p pricing_tool < database/seed.sql

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Start dev server
php -S localhost:8000 router.php

# 4. Compile Tailwind (separate terminal)
cd frontend && npx tailwindcss -i ./src/input.css -o ./public/assets/css/output.css --watch
```

## Project Structure

```
pricing/
├── backend/          # PHP API, models, helpers
├── frontend/         # HTML pages, JS, Tailwind CSS
├── generator/        # PDF/Word generation, AI client
├── database/         # Schema, migrations, seed data
├── security/         # CSRF, rate limiting, auth middleware
├── tests/            # Manual test scripts
└── router.php        # Single entry point
```
