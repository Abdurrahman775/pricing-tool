<?php
declare(strict_types=1);
$cur   = $currency ?? '?';
$date  = $date ?? date('F j, Y');
$bd    = $breakdown ?? [];
$maint = $bd['maintenance'] ?? null;
?>
<h1 style="font-size:20pt; color:#1a1a2e; border-bottom:2px solid #51459e; padding-bottom:10px;">System Documentation</h1>
<p style="font-size:9pt; color:#666; margin-top:-5px; margin-bottom:20px;"><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?> — Prepared for <?= htmlspecialchars($clientName ?? '', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></p>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">1. System Overview</h2>
<p style="font-size:10pt;">
<?= htmlspecialchars($projectName ?? 'This system', ENT_QUOTES, 'UTF-8') ?> consists of <?= count($bd['screen_breakdown'] ?? []) ?> screen(s)
and <?= count($bd['integration_breakdown'] ?? []) ?> integration(s), covering the platform(s) listed below. This document describes the
system's structure and features as scoped, for reference by developers and maintainers.
</p>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">2. Screens &amp; Features</h2>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:15px;">
<thead><tr style="background:#51459e; color:white;"><th style="padding:6px 8px; text-align:left;">Screen</th><th style="padding:6px 8px; text-align:left;">Complexity</th><th style="padding:6px 8px; text-align:left;">Description / Notes</th></tr></thead>
<tbody>
<?php if (!empty($bd['screen_breakdown'])): ?>
<?php foreach ($bd['screen_breakdown'] as $s): ?>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['notes'] ?: 'No description provided.', ENT_QUOTES, 'UTF-8') ?></td></tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="3" style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">No screens defined.</td></tr>
<?php endif; ?>
</tbody>
</table>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">3. Integrations</h2>
<ul style="font-size:10pt;">
<?php if (!empty($bd['integration_breakdown'])): ?>
<?php foreach ($bd['integration_breakdown'] as $i): ?>
<li><strong><?= htmlspecialchars($i['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> — external system integration connected to this application.</li>
<?php endforeach; ?>
<?php else: ?>
<li>No integrations required.</li>
<?php endif; ?>
</ul>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">4. Platform Support</h2>
<ul style="font-size:10pt;">
<?php if (!empty($bd['platform_breakdown'])): ?>
<?php foreach ($bd['platform_breakdown'] as $p): ?>
<li><?= htmlspecialchars($p['platform'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
<?php else: ?>
<li>No platforms selected.</li>
<?php endif; ?>
</ul>

<?php if ($maint): ?>
<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">5. Maintenance &amp; Support</h2>
<p style="font-size:10pt;">
<?php if ($maint['model'] === 'percentage'): ?>
Ongoing maintenance is billed at <?= (float) $maint['value'] ?>% of the project price per month (<?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['per_month'] ?? 0), 2) ?>/month), covering monitoring, bug fixes, and minor updates.
<?php else: ?>
Ongoing maintenance is billed at a flat rate of <?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['value'] ?? 0), 2) ?>/month, covering monitoring, bug fixes, and minor updates.
<?php endif; ?>
</p>
<?php endif; ?>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;"><?= $maint ? '6' : '5' ?>. Glossary</h2>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:15px;">
<thead><tr style="background:#51459e; color:white;"><th style="padding:6px 8px; text-align:left;">Term</th><th style="padding:6px 8px; text-align:left;">Definition</th></tr></thead>
<tbody>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">Complexity Tier</td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">A classification of how much effort a screen requires, used to weight its contribution to the overall estimate.</td></tr>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">Integration</td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">A connection to an external system or service that the application depends on.</td></tr>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">Platform Multiplier</td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">A scaling factor applied when a platform (e.g. mobile, web) increases overall build effort.</td></tr>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">Package Tier</td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">A named pricing tier (e.g. Basic, Standard, Premium) offering different feature/price combinations.</td></tr>
</tbody>
</table>

<p style="font-size:8pt; color:#999; border-top:1px solid #e0e0e0; padding-top:10px; margin-top:30px;">Documentation generated from Pricing &amp; PRD Generator Tool.</p>
