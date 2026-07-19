<?php
declare(strict_types=1);

class AiPrompts
{
    public static function proposalSystemPrompt(): string
    {
        return <<<PROMPT
You are a professional proposal writer for a software development agency.
Generate a client proposal as JSON only — no markdown, no code fences, no explanation.

Output a JSON object with this exact schema:
{
  "title": "Client Proposal",
  "subtitle": "Prepared for [Client Name]",
  "date": "July 19, 2026",
  "sections": [
    { "type": "cover", "project": "...", "client": "...", "package": "...", "price": "...", "currency": "..." },
    { "type": "heading", "level": 1, "text": "..." },
    { "type": "text", "content": "..." },
    { "type": "highlight", "content": "..." },
    { "type": "list", "style": "bullet", "items": ["...", "..."] },
    { "type": "list", "style": "number", "items": ["...", "..."] },
    { "type": "table", "headers": ["Col1", "Col2"], "rows": [["val1", "val2"]] },
    { "type": "signature", "clientLabel": "Authorized Signature — Client", "providerLabel": "Authorized Signature — Provider" }
  ]
}

Section types:
- cover: Document cover with project summary (shown in a branded box)
- heading: Section heading (level 1 = major section, level 2 = subsection)
- text: Body paragraph
- highlight: Important info shown in a tinted background box
- list: Bullet or numbered list
- table: Data table with header row
- signature: Signature lines at the end

Rules:
- Use the actual project data provided
- Write in a professional, persuasive business tone
- Be specific about screens, integrations, and platforms
- Keep content factual and compelling
- No placeholder text
- Return ONLY the JSON object
PROMPT;
    }

    public static function prdSystemPrompt(): string
    {
        return <<<PROMPT
You are a senior technical product manager writing a Product Requirements Document (PRD).
Generate the PRD as JSON only — no markdown, no code fences, no explanation.

Output a JSON object with this exact schema:
{
  "title": "Product Requirements Document (PRD)",
  "subtitle": "...",
  "date": "July 19, 2026",
  "version": 1,
  "sections": [
    { "type": "heading", "level": 1, "text": "1. Project Overview" },
    { "type": "text", "content": "..." },
    { "type": "table", "headers": ["Field", "Value"], "rows": [["key", "val"]] },
    { "type": "heading", "level": 2, "text": "..." },
    { "type": "list", "style": "bullet", "items": ["...", "..."] },
    { "type": "warning", "title": "Out of Scope", "items": ["...", "..."] }
  ]
}

Section types:
- heading: Section heading (level 1 = major section, level 2 = subsection)
- text: Body paragraph describing technical details
- table: Data table (first row is header)
- list: Bullet or numbered list
- warning: Red-bordered box for out-of-scope items (has title + items array)

Rules:
- Use the actual project data provided
- Write in a technical but clear style for developers
- Be specific about screen names, complexity tiers, integration details
- Include architecture implications where relevant
- List 7-8 out-of-scope items in the warning section
- No placeholder text
- Return ONLY the JSON object
PROMPT;
    }

    public static function buildProposalPrompt(array $data): string
    {
        $cur      = $data['currency'] ?? '₦';
        $pkgName  = $data['packageName'] ?? 'Standard';
        $pkgPrice = number_format((float) ($data['packagePrice'] ?? 0), 2);
        $projName = $data['projectName'] ?? 'Project';
        $client   = $data['clientName'] ?? 'Client';
        $date     = $data['date'] ?? date('F j, Y');
        $bd       = $data['breakdown'] ?? [];

        $screenCount = count($bd['screen_breakdown'] ?? []);
        $intCount    = count($bd['integration_breakdown'] ?? []);
        $platforms   = array_column($bd['platform_breakdown'] ?? [], 'platform');
        $platformStr = !empty($platforms) ? implode(', ', $platforms) : 'Web';

        $screensDetail = '';
        foreach ($bd['screen_breakdown'] ?? [] as $s) {
            $screensDetail .= sprintf("- %s (%s): %s\n", $s['name'] ?? '', $s['tier'] ?? '', $s['notes'] ?? '');
        }

        $intsDetail = '';
        foreach ($bd['integration_breakdown'] ?? [] as $i) {
            $intsDetail .= sprintf("- %s (%d pts)\n", $i['name'] ?? '', (int) ($i['points'] ?? 0));
        }

        $maintDetail = '';
        if (!empty($bd['maintenance'])) {
            $m = $bd['maintenance'];
            if ($m['model'] === 'percentage') {
                $maintDetail = sprintf("%d%% of project price per month (%s%s/mo)", (int) $m['value'], $cur, number_format((float) ($m['per_month'] ?? 0), 2));
            } else {
                $maintDetail = sprintf("Flat %s%s/mo", $cur, number_format((float) ($m['value'] ?? 0), 2));
            }
        }

        return <<<PROMPT
Generate a CLIENT PROPOSAL JSON with this project data:

Project: {$projName}
Client: {$client}
Date: {$date}
Selected Package: {$pkgName} ({$cur}{$pkgPrice})
Platforms: {$platformStr}

Screens ({$screenCount} total):
{$screensDetail}
Integrations ({$intCount} total):
{$intsDetail}
Maintenance: {$maintDetail}

Create sections in this order:
1. cover — project, client, package name, package price, currency
2. heading (level 1) — "What's Included"
3. text — narrative describing the {$screenCount} screens, {$intCount} integrations, and platforms
4. list (bullet) — each screen with its complexity tier
5. list (bullet) — each integration
6. heading (level 1) — "What's Excluded"
7. list (bullet) — typical exclusions: content creation, third-party licenses, hosting, SEO, scope changes
8. heading (level 1) — "Timeline"
9. text — realistic milestones
10. heading (level 1) — "Payment Terms"
11. list (bullet) — 50% deposit, 25% development milestone, 25% delivery
12. heading (level 1) — "Maintenance" (only if maintenance data exists)
13. highlight — maintenance details
14. signature — standard signature lines

Return ONLY the JSON object. No markdown.
PROMPT;
    }

    public static function buildPrdPrompt(array $data): string
    {
        $cur      = $data['currency'] ?? '₦';
        $pkgName  = $data['packageName'] ?? 'Standard';
        $pkgPrice = number_format((float) ($data['packagePrice'] ?? 0), 2);
        $projName = $data['projectName'] ?? 'Project';
        $client   = $data['clientName'] ?? 'Client';
        $version  = $data['versionNumber'] ?? 1;
        $date     = $data['date'] ?? date('F j, Y');
        $bd       = $data['breakdown'] ?? [];

        $screenCount = count($bd['screen_breakdown'] ?? []);
        $intCount    = count($bd['integration_breakdown'] ?? []);
        $platforms   = array_column($bd['platform_breakdown'] ?? [], 'platform');
        $platformStr = !empty($platforms) ? implode(', ', $platforms) : 'Web';
        $platMul     = $bd['platform_multiplier'] ?? 1.0;
        $totalScreenPts = $bd['total_screen_points'] ?? 0;
        $totalIntPts    = $bd['total_integration_points'] ?? 0;
        $subtotal    = $bd['subtotal'] ?? 0;
        $adjSubtotal = $bd['adjusted_subtotal'] ?? 0;
        $baseRate    = $bd['base_rate'] ?? 0;
        $basePrice   = $bd['base_price'] ?? 0;

        $screensTable = '';
        foreach ($bd['screen_breakdown'] ?? [] as $s) {
            $screensTable .= sprintf("| %s | %s | %.1f pts | %s |\n", $s['name'] ?? '', $s['tier'] ?? '', (float) ($s['points'] ?? 0), $s['notes'] ?? '');
        }

        $intsList = '';
        foreach ($bd['integration_breakdown'] ?? [] as $i) {
            $intsList .= sprintf("- %s (%d pts)\n", $i['name'] ?? '', (int) ($i['points'] ?? 0));
        }

        $pkgRows = '';
        foreach ($bd['packages'] ?? [] as $pkg) {
            $pkgRows .= sprintf("| %s | x%.1f | %s%s |\n", $pkg['name'] ?? '', (float) ($pkg['multiplier'] ?? 1), $cur, number_format((float) ($pkg['price'] ?? 0), 2));
        }

        $maintDetail = '';
        if (!empty($bd['maintenance'])) {
            $m = $bd['maintenance'];
            if ($m['model'] === 'percentage') {
                $maintDetail = sprintf("%d%% of project price per month (%s%s/mo)", (int) $m['value'], $cur, number_format((float) ($m['per_month'] ?? 0), 2));
            } else {
                $maintDetail = sprintf("Flat %s%s/mo", $cur, number_format((float) ($m['value'] ?? 0), 2));
            }
        }

        return <<<PROMPT
Generate a PRD JSON with this project data:

Project: {$projName}
Client: {$client}
Version: #{$version}
Date: {$date}
Package: {$pkgName} ({$cur}{$pkgPrice})
Platforms: {$platformStr} (highest multiplier: x{$platMul})

SCREENS ({$screenCount} total):
{$screensTable}
INTEGRATIONS ({$intCount} total):
{$intsList}
PRICING:
- Screen Points: {$totalScreenPts}
- Integration Points: +{$totalIntPts}
- Subtotal: {$subtotal} pts
- Platform Multiplier: x{$platMul}
- Adjusted Subtotal: {$adjSubtotal} pts
- Base Rate: {$cur}{$baseRate}/pt
- Base Price: {$cur}{$basePrice}
- Package Tiers:
{$pkgRows}
Maintenance: {$maintDetail}

Create sections in this order:
1. heading (level 1) — "1. Project Overview"
2. table — project, client, package, total points, base rate
3. text — narrative about architecture and approach
4. heading (level 1) — "2. Screens & Complexity"
5. table — headers: [Screen, Complexity, Points, Notes]; rows from screen data
6. heading (level 1) — "3. Integrations"
7. list (bullet) — each integration with points
8. heading (level 1) — "4. Platforms"
9. list (bullet) — each platform with multiplier, note which one is applied
10. heading (level 1) — "5. Pricing Breakdown"
11. table — headers: [Item, Value]; rows: screen pts, int pts, subtotal, platform multiplier, adjusted subtotal, base rate, base price, each package
12. heading (level 1) — "6. Maintenance Terms" (only if maintenance exists)
13. text — maintenance model description
14. heading (level 1) — "7. Out of Scope"
15. warning — title "Explicitly Excluded", 7-8 items covering content, licensing, hosting, SEO, post-launch support, feature additions, porting, testing beyond scope
16. heading (level 1) — "8. Estimated Timeline"
17. list (bullet) — week-by-week milestones

Return ONLY the JSON object. No markdown.
PROMPT;
    }
}
