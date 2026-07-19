<?php
declare(strict_types=1);

/**
 * Developer PRD Template
 * Variables: $projectName, $clientName, $packageName, $packagePrice,
 *            $currency, $breakdown (full array), $versionNumber, $date
 */

$cur   = $currency ?? '₦';
$date  = $date ?? date('F j, Y');
$bd    = $breakdown ?? [];
$maint = $bd['maintenance'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1a1a2e; line-height: 1.6; margin: 40px; }
    .header { border-bottom: 3px solid #51459e; padding-bottom: 15px; margin-bottom: 25px; }
    .header h1 { color: #51459e; font-size: 20pt; margin: 0 0 5px 0; }
    .header .meta { color: #666; font-size: 9pt; margin: 0; }
    .section-title { color: #51459e; font-size: 13pt; font-weight: bold; margin-top: 25px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #51459e; }
    .subsection-title { color: #333; font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
    th { background: #51459e; color: white; padding: 6px 8px; text-align: left; }
    td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
    tr:nth-child(even) td { background: #f8f6ff; }
    ul { margin: 5px 0 10px 0; padding-left: 20px; }
    li { font-size: 9pt; margin-bottom: 2px; }
    .badge { display: inline-block; background: #e8e0ff; color: #51459e; padding: 1px 6px; border-radius: 3px; font-size: 8pt; }
    .out-of-scope { background: #fff3f3; border: 1px solid #f5c6c6; border-radius: 6px; padding: 12px; margin: 10px 0; }
    .out-of-scope h3 { color: #c0392b; font-size: 11pt; margin: 0 0 5px 0; }
    .out-of-scope ul { color: #666; }
    .footer { margin-top: 40px; padding-top: 10px; border-top: 1px solid #e0e0e0; font-size: 8pt; color: #999; }
</style>
</head>
<body>
<div class="header">
    <h1>Product Requirements Document (PRD)</h1>
    <p class="meta"><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?> &mdash; Prepared <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?> &mdash; Version #<?= (int) ($versionNumber ?? 1) ?></p>
</div>

<div class="section-title">1. Project Overview</div>
<table>
    <tr><td style="width:150px;font-weight:bold;">Project</td><td><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td style="font-weight:bold;">Client</td><td><?= htmlspecialchars($clientName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><td style="font-weight:bold;">Package</td><td><?= htmlspecialchars($packageName ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($packagePrice ?? 0), 2) ?>)</td></tr>
    <tr><td style="font-weight:bold;">Total Points</td><td><?= (int) ($bd['adjusted_subtotal'] ?? 0) ?> pts (<?= (int) ($bd['total_screen_points'] ?? 0) ?> screen + <?= (int) ($bd['total_integration_points'] ?? 0) ?> integration) &times; <?= (float) ($bd['platform_multiplier'] ?? 1.0) ?> platform</td></tr>
    <tr><td style="font-weight:bold;">Base Rate</td><td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_rate'] ?? 0), 2) ?>/pt</td></tr>
</table>

<div class="section-title">2. Screens &amp; Complexity</div>
<table>
    <thead><tr><th>Screen</th><th>Complexity</th><th>Points</th><th>Notes</th></tr></thead>
    <tbody>
    <?php if (!empty($bd['screen_breakdown'])): ?>
        <?php foreach ($bd['screen_breakdown'] as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge"><?= htmlspecialchars($s['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?= (float) $s['points'] ?></td>
            <td><?= htmlspecialchars($s['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4">No screens defined.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="section-title">3. Integrations</div>
<ul>
    <?php if (!empty($bd['integration_breakdown'])): ?>
        <?php foreach ($bd['integration_breakdown'] as $i): ?>
        <li><?= htmlspecialchars($i['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= (int) ($i['points'] ?? 0) ?> pts)</li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No integrations required.</li>
    <?php endif; ?>
</ul>

<div class="section-title">4. Platforms</div>
<ul>
    <?php if (!empty($bd['platform_breakdown'])): ?>
        <?php foreach ($bd['platform_breakdown'] as $p): ?>
        <li><?= htmlspecialchars($p['platform'] ?? '', ENT_QUOTES, 'UTF-8') ?> (multiplier: &times;<?= (float) ($p['multiplier'] ?? 1.0) ?>)</li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No platforms selected.</li>
    <?php endif; ?>
</ul>

<div class="section-title">5. Pricing Breakdown</div>
<table>
    <tr><td>Screen Points</td><td><?= (int) ($bd['total_screen_points'] ?? 0) ?> pts</td></tr>
    <tr><td>Integration Points</td><td>+<?= (int) ($bd['total_integration_points'] ?? 0) ?> pts</td></tr>
    <tr><td>Subtotal</td><td><?= (float) ($bd['subtotal'] ?? 0) ?> pts</td></tr>
    <tr><td>Platform Multiplier</td><td>&times; <?= (float) ($bd['platform_multiplier'] ?? 1.0) ?></td></tr>
    <tr><td>Adjusted Subtotal</td><td><?= (float) ($bd['adjusted_subtotal'] ?? 0) ?> pts</td></tr>
    <tr><td>Base Rate</td><td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_rate'] ?? 0), 2) ?>/pt</td></tr>
    <tr style="font-weight:bold;background:#f4f0ff;"><td>Base Price</td><td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($bd['base_price'] ?? 0), 2) ?></td></tr>
    <?php if (!empty($bd['packages'])): ?>
        <?php foreach ($bd['packages'] as $pkg): ?>
        <tr><td>Package: <?= htmlspecialchars($pkg['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (&times;<?= (float) ($pkg['multiplier'] ?? 1.0) ?>)</td><td><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($pkg['price'] ?? 0), 2) ?></td></tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php if ($maint): ?>
<div class="section-title">6. Maintenance Terms</div>
<p style="font-size:9pt;">
    Model: <strong><?= htmlspecialchars($maint['model'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong><br>
    <?php if ($maint['model'] === 'percentage'): ?>
        Rate: <?= (float) $maint['value'] ?>% of project price per month<br>
        Monthly cost: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['per_month'] ?? 0), 2) ?>/month</strong>
    <?php else: ?>
        Flat rate: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['value'] ?? 0), 2) ?>/month</strong>
    <?php endif; ?>
</p>
<?php endif; ?>

<div class="section-title">7. Out of Scope</div>
<div class="out-of-scope">
    <h3>The following items are explicitly excluded from this scope:</h3>
    <ul>
        <li>Content creation — all copy, images, videos, and media must be provided by the client</li>
        <li>Third-party API keys, subscriptions, or licensing fees</li>
        <li>Domain registration, SSL certificates, and hosting infrastructure</li>
        <li>Search engine optimization (SEO) and digital marketing</li>
        <li>Post-launch support beyond the agreed maintenance period</li>
        <li>Feature additions or modifications not listed in this document</li>
        <li>Cross-platform porting (e.g., converting iOS app to Android)</li>
    </ul>
</div>

<div class="section-title">8. Maintenance Terms</div>
<p style="font-size:9pt;">
    This project includes <?php if ($maint): ?>maintenance at the rate specified above<?php else: ?>no ongoing maintenance<?php endif; ?>.
    Maintenance covers bug fixes, security patches, and platform compatibility updates.
    Feature development and scope changes are not included and will be quoted separately.
</p>

<div class="section-title">9. Estimated Timeline</div>
<p style="font-size:9pt;">
    Development timeline will be finalized at project kickoff. Typical milestones:
</p>
<ul>
    <li><strong>Kickoff &amp; Requirements Finalization:</strong> Week 1</li>
    <li><strong>Design Phase:</strong> Weeks 2-3</li>
    <li><strong>Development Sprint 1 (Core Screens):</strong> Weeks 4-6</li>
    <li><strong>Development Sprint 2 (Integrations &amp; Features):</strong> Weeks 7-9</li>
    <li><strong>Testing &amp; QA:</strong> Week 10</li>
    <li><strong>Deployment &amp; Handover:</strong> Week 11</li>
</ul>

<div class="footer">
    <p>PRD generated from Pricing &amp; PRD Generator Tool. This document is based on the selected package and project intake data.</p>
</div>
</body>
</html>
