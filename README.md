# Pricing & PRD Generator Tool

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38BDF8?logo=tailwindcss)
![AI](https://img.shields.io/badge/AI-Groq_API-orange)
![License](https://img.shields.io/badge/License-MIT-green)
![Live](https://img.shields.io/badge/Live-Demo-brightgreen)

**AI-powered web tool for generating software pricing estimates and professional PRD/proposal documents.**

[Live Demo](http://ab-dev.infinityfree.me/pricing-tool)

</div>

---

## Features

- **Multi-tenant pricing** — Each user has isolated pricing configs, projects, and quotes
- **Pricing engine** — Screen points + integration points × platform multiplier × base rate → package tiers
- **Transparent breakdowns** — See exactly how each price is calculated
- **Scope locking** — Lock a project version once a package is selected
- **AI document generation** — Generate professional proposals and PRDs via Groq AI API
- **Document export** — PDF (Dompdf) and Word (PHPWord)
- **Config export/import** — Export/import pricing configs as JSON

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP, PDO, MySQL (InnoDB) |
| **Frontend** | HTML, Tailwind CSS, Vanilla JavaScript, SweetAlert2 |
| **Documents** | Dompdf, PHPWord, docx-js |
| **AI** | OpenAI-compatible API (Groq) |

## Quick Start

```bash
# 1. Setup database
mysql -u root -p -e "CREATE DATABASE pricing_tool CHARACTER SET utf8mb4;"
mysql -u root -p pricing_tool < database/schema.sql

# 2. Configure environment
cp .env.example .env
# Fill in DB credentials and Groq API key

# 3. Start dev server
php -S localhost:8000 router.php

# 4. Compile Tailwind
cd frontend && npx tailwindcss -i ./src/input.css -o ./public/assets/css/output.css --watch
```

## Project Structure

```
pricing/
├── backend/      # PHP API, models, helpers
├── frontend/     # HTML pages, JS, Tailwind CSS
├── generator/    # PDF/Word generation, AI client
├── database/     # Schema, migrations, seed data
├── security/     # CSRF, rate limiting, auth middleware
└── router.php    # Single entry point
```

## License

This project is licensed under the [MIT License](LICENSE).

---

**Abdurrahman Alhassan** · [Portfolio](https://abdurrahman775.vercel.app) · [GitHub](https://github.com/Abdurrahman775)
