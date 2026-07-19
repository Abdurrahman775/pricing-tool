<?php
declare(strict_types=1);
$cur   = $currency ?? '?';
$date  = $date ?? date('F j, Y');
$bd    = $breakdown ?? [];
$maint = $bd['maintenance'] ?? null;
?>
<h1 style="font-size:22pt; color:#1a1a2e; border-bottom:2px solid #51459e; padding-bottom:10px;">Client Proposal</h1>
<p style="font-size:9pt; color:#666; margin-top:-5px; margin-bottom:20px;">Prepared on <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></p>

<table style="width:100%; border-collapse:collapse; font-size:10pt; margin-bottom:20px;">
<tr><td style="width:120px; font-weight:bold; color:#51459e;">Project</td><td><?= htmlspecialchars($projectName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><td style="font-weight:bold; color:#51459e;">Client</td><td><?= htmlspecialchars($clientName ?? '', ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><td style="font-weight:bold; color:#51459e;">Version</td><td>#<?= (int) ($versionNumber ?? 1) ?></td></tr>
<tr><td style="font-weight:bold; color:#51459e;">Valid Until</td><td><?= date('F j, Y', strtotime('+30 days')) ?></td></tr>
</table>

<div style="background:#f4f0ff; border:1px solid #d4c8f0; border-radius:6px; padding:15px; margin-bottom:20px;">
<p style="font-size:9pt; color:#888; margin:0 0 3px 0;">Selected Package</p>
<h2 style="font-size:16pt; color:#51459e; margin:0 0 5px 0;"><?= htmlspecialchars($packageName ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
<p style="font-size:20pt; font-weight:bold; color:#1a1a2e; margin:0;"><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($packagePrice ?? 0), 2) ?></p>
</div>

<h2 style="font-size:13pt; color:#51459e; border-bottom:1px solid #e0e0e0; padding-bottom:5px;">What's Included</h2>
<ul style="font-size:10pt;">
<?php if (!empty($bd['screen_breakdown'])): ?>
<li><strong>Screens:</strong> <?= count($bd['screen_breakdown']) ?> screens</li>
<?php foreach ($bd['screen_breakdown'] as $s): ?>
<li>— <?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($s['tier'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</li>
<?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($bd['integration_breakdown'])): ?>
<li><strong>Integrations:</strong> <?= count($bd['integration_breakdown']) ?> integrations</li>
<?php foreach ($bd['integration_breakdown'] as $i): ?>
<li>— <?= htmlspecialchars($i['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
<?php endif; ?>
</ul>

<h2 style="font-size:13pt; color:#51459e; border-bottom:1px solid #e0e0e0; padding-bottom:5px;">What's Excluded</h2>
<ul style="font-size:10pt; color:#999;">
<li>Content creation (copywriting, images, videos)</li>
<li>Third-party software licenses and subscription fees</li>
<li>Domain registration and hosting fees</li>
<li>Ongoing SEO, digital marketing, or advertising</li>
<li><?= htmlspecialchars($packageName ?? 'This', ENT_QUOTES, 'UTF-8') ?> package scope only</li>
</ul>

<?php if ($maint): ?>
<h2 style="font-size:13pt; color:#51459e; border-bottom:1px solid #e0e0e0; padding-bottom:5px;">Maintenance</h2>
<p style="font-size:10pt;">
<?php if ($maint['model'] === 'percentage'): ?>
Monthly maintenance at <?= (float) $maint['value'] ?>% of project price: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['per_month'] ?? 0), 2) ?>/month</strong>
<?php else: ?>
Flat monthly maintenance: <strong><?= htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') ?><?= number_format((float) ($maint['value'] ?? 0), 2) ?>/month</strong>
<?php endif; ?>
</p>
<?php endif; ?>

<h2 style="font-size:13pt; color:#51459e; border-bottom:1px solid #e0e0e0; padding-bottom:5px;">Timeline</h2>
<p style="font-size:10pt;">Estimated development timeline: To be confirmed upon project kickoff. Typical delivery: 6-12 weeks from approval.</p>

<h2 style="font-size:13pt; color:#51459e; border-bottom:1px solid #e0e0e0; padding-bottom:5px;">Payment Terms</h2>
<p style="font-size:10pt;">50% deposit to commence work.<br/>25% upon completion of development phase.<br/>25% upon project delivery and sign-off.</p>

<p style="font-size:9pt; color:#999; border-top:1px solid #e0e0e0; padding-top:10px; margin-top:30px;">This proposal is valid for 30 days. Pricing and scope are based on the information provided at the time of preparation.</p>
