<?php
declare(strict_types=1);
$cur   = $currency ?? '?';
$date  = $date ?? date('F j, Y');
$bd    = $breakdown ?? [];
$maint = $bd['maintenance'] ?? null;
?>
<h1 style="font-size:20pt; color:#1a1a2e; border-bottom:2px solid #51459e; padding-bottom:10px;">Product Requirements Document (PRD)</h1>
<p style="font-size:9pt; color:#666; margin-top:-5px; margin-bottom:20px;"><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?> — Prepared <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?> — Version #<?= (int) ($versionNumber ?? 1) ?></p>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">1. Project Overview</h2>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:15px;">
<tr><td style="width:150px; font-weight:bold;">Project</td><td><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><td style="font-weight:bold;">Client</td><td><?= htmlspecialchars($clientName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><td style="font-weight:bold;">Package</td><td><?= htmlspecialchars($packageName ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($packagePrice ?? 0), 2) ?>)</td></tr>
<tr><td style="font-weight:bold;">Total Points</td><td><?= (int) ($bd['adjusted_subtotal'] ?? 0) ?> pts</td></tr>
<tr><td style="font-weight:bold;">Base Rate</td><td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_rate'] ?? 0), 2) ?>/pt</td></tr>
</table>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">2. Screens &amp; Complexity</h2>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:15px;">
<thead><tr style="background:#51459e; color:white;"><th style="padding:6px 8px; text-align:left;">Screen</th><th style="padding:6px 8px; text-align:left;">Complexity</th><th style="padding:6px 8px; text-align:left;">Points</th><th style="padding:6px 8px; text-align:left;">Notes</th></tr></thead>
<tbody>
<?php if (!empty($bd['screen_breakdown'])): ?>
<?php foreach ($bd['screen_breakdown'] as $s): ?>
<tr><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= (float) $s['points'] ?></td><td style="padding:5px 8px; border-bottom:1px solid #e0e0e0;"><?= htmlspecialchars($s['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4" style="padding:5px 8px; border-bottom:1px solid #e0e0e0;">No screens defined.</td></tr>
<?php endif; ?>
</tbody>
</table>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">3. Integrations</h2>
<ul style="font-size:10pt;">
<?php if (!empty($bd['integration_breakdown'])): ?>
<?php foreach ($bd['integration_breakdown'] as $i): ?>
<li><?= htmlspecialchars($i['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= (int) ($i['points'] ?? 0) ?> pts)</li>
<?php endforeach; ?>
<?php else: ?>
<li>No integrations required.</li>
<?php endif; ?>
</ul>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">4. Platforms</h2>
<ul style="font-size:10pt;">
<?php if (!empty($bd['platform_breakdown'])): ?>
<?php foreach ($bd['platform_breakdown'] as $p): ?>
<li><?= htmlspecialchars($p['platform'] ?? '', ENT_QUOTES, 'UTF-8') ?> (&times;<?= (float) ($p['multiplier'] ?? 1.0) ?>)</li>
<?php endforeach; ?>
<?php else: ?>
<li>No platforms selected.</li>
<?php endif; ?>
</ul>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">5. Pricing Breakdown</h2>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:15px;">
<tr><td style="padding:4px 8px;">Screen Points</td><td style="padding:4px 8px;"><?= (int) ($bd['total_screen_points'] ?? 0) ?> pts</td></tr>
<tr><td style="padding:4px 8px;">Integration Points</td><td style="padding:4px 8px;">+<?= (int) ($bd['total_integration_points'] ?? 0) ?> pts</td></tr>
<tr><td style="padding:4px 8px;">Subtotal</td><td style="padding:4px 8px;"><?= (float) ($bd['subtotal'] ?? 0) ?> pts</td></tr>
<tr><td style="padding:4px 8px;">Platform Multiplier</td><td style="padding:4px 8px;">&times; <?= (float) ($bd['platform_multiplier'] ?? 1.0) ?></td></tr>
<tr><td style="padding:4px 8px;">Adjusted Subtotal</td><td style="padding:4px 8px;"><?= (float) ($bd['adjusted_subtotal'] ?? 0) ?> pts</td></tr>
<tr><td style="padding:4px 8px;">Base Rate</td><td style="padding:4px 8px;"><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_rate'] ?? 0), 2) ?>/pt</td></tr>
<tr style="font-weight:bold; background:#f4f0ff;"><td style="padding:4px 8px;">Base Price</td><td style="padding:4px 8px;"><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_price'] ?? 0), 2) ?></td></tr>
<?php if (!empty($bd['packages'])): ?>
<?php foreach ($bd['packages'] as $pkg): ?>
<tr><td style="padding:4px 8px;">Package: <?= htmlspecialchars($pkg['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (&times;<?= (float) ($pkg['multiplier'] ?? 1.0) ?>)</td><td style="padding:4px 8px;"><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($pkg['price'] ?? 0), 2) ?></td></tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php if ($maint): ?>
<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">6. Maintenance Terms</h2>
<p style="font-size:10pt;">
Model: <strong><?= htmlspecialchars($maint['model'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong><br/>
<?php if ($maint['model'] === 'percentage'): ?>
Rate: <?= (float) $maint['value'] ?>% of project price per month<br/>
Monthly cost: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['per_month'] ?? 0), 2) ?>/month</strong>
<?php else: ?>
Flat rate: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['value'] ?? 0), 2) ?>/month</strong>
<?php endif; ?>
</p>
<?php endif; ?>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">7. Out of Scope</h2>
<div style="background:#fff3f3; border:1px solid #f5c6c6; border-radius:6px; padding:12px; margin-bottom:15px;">
<p style="font-size:10pt; color:#c0392b; font-weight:bold; margin:0 0 5px 0;">The following items are explicitly excluded from this scope:</p>
<ul style="font-size:10pt; color:#666;">
<li>Content creation — all copy, images, videos, and media must be provided by the client</li>
<li>Third-party API keys, subscriptions, or licensing fees</li>
<li>Domain registration, SSL certificates, and hosting infrastructure</li>
<li>Search engine optimization (SEO) and digital marketing</li>
<li>Post-launch support beyond the agreed maintenance period</li>
<li>Feature additions or modifications not listed in this document</li>
<li>Cross-platform porting (e.g., converting iOS app to Android)</li>
</ul>
</div>

<h2 style="font-size:13pt; color:#51459e; border-bottom:2px solid #51459e; padding-bottom:3px;">8. Estimated Timeline</h2>
<ul style="font-size:10pt;">
<li><strong>Kickoff &amp; Requirements Finalization:</strong> Week 1</li>
<li><strong>Design Phase:</strong> Weeks 2-3</li>
<li><strong>Development Sprint 1 (Core Screens):</strong> Weeks 4-6</li>
<li><strong>Development Sprint 2 (Integrations &amp; Features):</strong> Weeks 7-9</li>
<li><strong>Testing &amp; QA:</strong> Week 10</li>
<li><strong>Deployment &amp; Handover:</strong> Week 11</li>
</ul>

<p style="font-size:8pt; color:#999; border-top:1px solid #e0e0e0; padding-top:10px; margin-top:30px;">PRD generated from Pricing &amp; PRD Generator Tool.</p>
