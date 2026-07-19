<?php
declare(strict_types=1);

/**
 * Client Proposal Template
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
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #1a1a2e; line-height: 1.6; margin: 40px; }
    .header { border-bottom: 3px solid #51459e; padding-bottom: 15px; margin-bottom: 25px; }
    .header h1 { color: #51459e; font-size: 22pt; margin: 0 0 5px 0; }
    .header p { color: #666; font-size: 10pt; margin: 0; }
    .project-info { margin-bottom: 25px; }
    .project-info table { width: 100%; border-collapse: collapse; }
    .project-info td { padding: 4px 8px; font-size: 10pt; }
    .project-info td:first-child { font-weight: bold; width: 140px; color: #51459e; }
    .package-card { background: #f4f0ff; border: 1px solid #d4c8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
    .package-card h2 { color: #51459e; font-size: 16pt; margin: 0 0 5px 0; }
    .package-card .price { font-size: 20pt; font-weight: bold; color: #1a1a2e; }
    .package-card .label { font-size: 9pt; color: #888; }
    .section-title { color: #51459e; font-size: 13pt; font-weight: bold; margin-top: 25px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #e0e0e0; }
    ul { margin: 5px 0 15px 0; padding-left: 20px; }
    li { font-size: 10pt; margin-bottom: 3px; }
    .excluded { color: #999; }
    .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e0e0e0; font-size: 9pt; color: #999; }
    .signature { margin-top: 40px; }
    .signature table { width: 100%; }
    .signature td { width: 50%; padding: 10px; }
    .signature .line { border-top: 1px solid #333; padding-top: 5px; font-size: 9pt; color: #666; }
</style>
</head>
<body>
<div class="header">
    <h1>Client Proposal</h1>
    <p>Prepared on <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="project-info">
    <table>
        <tr><td>Project</td><td><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td>Client</td><td><?= htmlspecialchars($clientName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><td>Version</td><td>#<?= (int) ($versionNumber ?? 1) ?></td></tr>
        <tr><td>Valid Until</td><td><?= date('F j, Y', strtotime('+30 days')) ?></td></tr>
    </table>
</div>

<div class="package-card">
    <div class="label">Selected Package</div>
    <h2><?= htmlspecialchars($packageName ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="price"><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($packagePrice ?? 0), 2) ?></div>
</div>

<div class="section-title">What's Included</div>
<ul>
    <?php if (!empty($bd['screen_breakdown'])): ?>
        <li><strong>Screens:</strong> <?= count($bd['screen_breakdown']) ?> screens designed and developed</li>
        <?php foreach ($bd['screen_breakdown'] as $s): ?>
            <li>&mdash; <?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($s['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</li>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($bd['integration_breakdown'])): ?>
        <li><strong>Integrations:</strong> <?= count($bd['integration_breakdown']) ?> integrations configured</li>
        <?php foreach ($bd['integration_breakdown'] as $i): ?>
            <li>&mdash; <?= htmlspecialchars($i['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($bd['platform_breakdown'])): ?>
        <li><strong>Platforms:</strong> <?= implode(', ', array_column($bd['platform_breakdown'], 'platform')) ?></li>
    <?php endif; ?>
</ul>

<div class="section-title">What's Excluded</div>
<ul class="excluded">
    <li>Content creation (copywriting, images, videos)</li>
    <li>Third-party software licenses and subscription fees</li>
    <li>Domain registration and hosting fees</li>
    <li>Ongoing SEO, digital marketing, or advertising</li>
    <li><?= htmlspecialchars($packageName ?? 'This', ENT_QUOTES, 'UTF-8') ?> package scope only — additional features will require a change order</li>
</ul>

<?php if ($maint): ?>
<div class="section-title">Maintenance</div>
<p style="font-size:10pt;">
    <?php if ($maint['model'] === 'percentage'): ?>
        Monthly maintenance at <?= (float) $maint['value'] ?>% of project price: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['per_month'] ?? 0), 2) ?>/month</strong>
    <?php else: ?>
        Flat monthly maintenance: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['value'] ?? 0), 2) ?>/month</strong>
    <?php endif; ?>
</p>
<?php endif; ?>

<div class="section-title">Timeline</div>
<p style="font-size:10pt;">Estimated development timeline: To be confirmed upon project kickoff. Typical delivery: 6-12 weeks from approval.</p>

<div class="section-title">Payment Terms</div>
<p style="font-size:10pt;">
    50% deposit to commence work.<br>
    25% upon completion of development phase.<br>
    25% upon project delivery and sign-off.
</p>

<div class="signature">
    <table>
        <tr>
            <td><div class="line">Authorized Signature &mdash; Client</div></td>
            <td><div class="line">Authorized Signature &mdash; Provider</div></td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>This proposal is valid for 30 days. Pricing and scope are based on the information provided at the time of preparation.</p>
</div>
</body>
</html>
