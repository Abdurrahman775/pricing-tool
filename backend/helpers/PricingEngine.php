<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class PricingEngine
{
    /**
     * Calculate a full pricing breakdown from intake data and a config.
     * Returns an array with transparent calculation steps.
     */
    public static function calculate(array $screens, array $integrationIds, array $platforms, ?array $maintenance, array $config): array
    {
        // 1. Screen points
        $tiers = [];
        foreach ($config['complexity_tiers'] as $t) {
            $tiers[(int) $t['id']] = $t;
        }

        $screenBreakdown = [];
        $totalScreenPoints = 0;

        foreach ($screens as $s) {
            $tierId = (int) ($s['complexity_tier_id'] ?? 0);
            $tier = $tiers[$tierId] ?? ['name' => 'Unknown', 'multiplier' => 1.0];
            $points = (float) $tier['multiplier'];
            $totalScreenPoints += $points;
            $screenBreakdown[] = [
                'name'      => $s['name'] ?? 'Untitled',
                'tier'      => $tier['name'],
                'points'    => $points,
                'notes'     => $s['notes'] ?? '',
            ];
        }

        // 2. Integration points
        $integrationMap = [];
        foreach ($config['integrations'] as $int) {
            $integrationMap[(int) $int['id']] = $int;
        }

        $integrationBreakdown = [];
        $totalIntegrationPoints = 0;

        foreach ($integrationIds as $intId) {
            $int = $integrationMap[(int) $intId] ?? null;
            if ($int) {
                $pts = (int) $int['points'];
                $totalIntegrationPoints += $pts;
                $integrationBreakdown[] = [
                    'name'   => $int['name'],
                    'points' => $pts,
                ];
            }
        }
        // Also handle custom integrations (passed as arrays with name/points)
        foreach ($integrationIds as $item) {
            if (is_array($item) && !empty($item['custom_name'])) {
                $pts = (int) ($item['custom_points'] ?? 0);
                $totalIntegrationPoints += $pts;
                $integrationBreakdown[] = [
                    'name'   => $item['custom_name'],
                    'points' => $pts,
                ];
            }
        }

        // 3. Subtotal before platform multiplier
        $subtotal = $totalScreenPoints + $totalIntegrationPoints;

        // 4. Platform multiplier (use the highest multiplier among selected platforms)
        $platformMap = [];
        foreach ($config['platform_multipliers'] as $pm) {
            $platformMap[$pm['platform']] = (float) $pm['multiplier'];
        }

        $platformMultiplier = 1.0;
        $platformBreakdown = [];
        foreach ($platforms as $p) {
            $mul = $platformMap[$p] ?? 1.0;
            $platformMultiplier = max($platformMultiplier, $mul);
            $platformBreakdown[] = [
                'platform'   => $p,
                'multiplier' => $mul,
            ];
        }

        $adjustedSubtotal = $subtotal * $platformMultiplier;

        // 5. Base price
        $baseRate = (float) ($config['base_rate'] ?? 0);
        $basePrice = $adjustedSubtotal * $baseRate;

        // 6. Package tiers
        $packages = [];
        foreach ($config['package_tiers'] as $pkg) {
            $price = $basePrice * (float) $pkg['multiplier'];
            $packages[] = [
                'name'       => $pkg['name'],
                'multiplier' => (float) $pkg['multiplier'],
                'price'      => round($price, 2),
            ];
        }

        // 7. Maintenance
        $maintenanceBreakdown = null;
        if ($maintenance && !empty($maintenance['model'])) {
            $model = $maintenance['model'];
            $value = (float) ($maintenance['value'] ?? 0);
            $maintenanceBreakdown = [
                'model'      => $model,
                'value'      => $value,
                'per_month'  => $model === 'percentage'
                    ? round($basePrice * $value / 100, 2)
                    : round($value, 2),
            ];
        }

        return [
            'currency'               => $config['currency'] ?? '₦',
            'base_rate'              => $baseRate,
            'screen_breakdown'       => $screenBreakdown,
            'total_screen_points'    => round($totalScreenPoints, 1),
            'integration_breakdown'  => $integrationBreakdown,
            'total_integration_points' => $totalIntegrationPoints,
            'subtotal'               => round($subtotal, 1),
            'platform_breakdown'     => $platformBreakdown,
            'platform_multiplier'    => $platformMultiplier,
            'adjusted_subtotal'      => round($adjustedSubtotal, 1),
            'base_price'             => round($basePrice, 2),
            'packages'               => $packages,
            'maintenance'            => $maintenanceBreakdown,
        ];
    }
}
