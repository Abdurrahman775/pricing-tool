<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class PricingConfig
{
    /**
     * Copy the default template (user_id = NULL) for a new user.
     * Copies: complexity_tiers, integrations, platform_multipliers, package_tiers, maintenance_settings.
     */
    public static function createFromTemplate(int $userId, string $name = 'Standard'): int
    {
        $db = getDB();

        // Find the default template
        $stmt = $db->prepare('SELECT * FROM pricing_configs WHERE is_default = 1 AND user_id IS NULL LIMIT 1');
        $stmt->execute();
        $template = $stmt->fetch();

        if (!$template) {
            return self::createDefault($userId, $name);
        }

        // Create new config
        $stmt = $db->prepare(
            'INSERT INTO pricing_configs (user_id, name, currency, base_rate, is_default)
             VALUES (:user_id, :name, :currency, :base_rate, 0)'
        );
        $stmt->execute([
            'user_id'   => $userId,
            'name'      => $name,
            'currency'  => $template['currency'],
            'base_rate' => $template['base_rate'],
        ]);
        $newConfigId = (int) $db->lastInsertId();

        // Copy complexity tiers
        $stmt = $db->prepare(
            'INSERT INTO complexity_tiers (pricing_config_id, name, multiplier, sort_order)
             SELECT :new_id, name, multiplier, sort_order FROM complexity_tiers WHERE pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'template_id' => $template['id']]);

        // Copy screen templates (remapped to the new config's complexity tier ids, matched by name)
        $stmt = $db->prepare(
            'INSERT INTO screen_templates (pricing_config_id, name, complexity_tier_id, sort_order)
             SELECT :new_id, st.name, newtier.id, st.sort_order
             FROM screen_templates st
             JOIN complexity_tiers oldtier ON oldtier.id = st.complexity_tier_id
             JOIN complexity_tiers newtier ON newtier.pricing_config_id = :new_id2 AND newtier.name = oldtier.name
             WHERE st.pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'new_id2' => $newConfigId, 'template_id' => $template['id']]);

        // Copy integrations
        $stmt = $db->prepare(
            'INSERT INTO integrations (pricing_config_id, name, points, sort_order)
             SELECT :new_id, name, points, sort_order FROM integrations WHERE pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'template_id' => $template['id']]);

        // Copy platform multipliers
        $stmt = $db->prepare(
            'INSERT INTO platform_multipliers (pricing_config_id, platform, multiplier)
             SELECT :new_id, platform, multiplier FROM platform_multipliers WHERE pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'template_id' => $template['id']]);

        // Copy package tiers
        $stmt = $db->prepare(
            'INSERT INTO package_tiers (pricing_config_id, name, multiplier, sort_order)
             SELECT :new_id, name, multiplier, sort_order FROM package_tiers WHERE pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'template_id' => $template['id']]);

        // Copy maintenance settings
        $stmt = $db->prepare(
            'INSERT INTO maintenance_settings (pricing_config_id, model, value)
             SELECT :new_id, model, value FROM maintenance_settings WHERE pricing_config_id = :template_id'
        );
        $stmt->execute(['new_id' => $newConfigId, 'template_id' => $template['id']]);

        return $newConfigId;
    }

    /**
     * Create a hardcoded default config (fallback if no template in DB).
     */
    private static function createDefault(int $userId, string $name): int
    {
        $db = getDB();

        $stmt = $db->prepare(
            'INSERT INTO pricing_configs (user_id, name, currency, base_rate) VALUES (:user_id, :name, :currency, :base_rate)'
        );
        $stmt->execute([
            'user_id'   => $userId,
            'name'      => $name,
            'currency'  => '₦',
            'base_rate' => 20000,
        ]);
        $configId = (int) $db->lastInsertId();

        $tiers = [
            ['name' => 'Simple',     'multiplier' => 1.0,  'sort_order' => 1],
            ['name' => 'Medium',     'multiplier' => 2.0,  'sort_order' => 2],
            ['name' => 'Complex',    'multiplier' => 3.5,  'sort_order' => 3],
        ];
        $stmt = $db->prepare('INSERT INTO complexity_tiers (pricing_config_id, name, multiplier, sort_order) VALUES (:cid, :name, :mul, :so)');
        $tierIdByName = [];
        foreach ($tiers as $t) {
            $stmt->execute(['cid' => $configId, 'name' => $t['name'], 'mul' => $t['multiplier'], 'so' => $t['sort_order']]);
            $tierIdByName[$t['name']] = (int) $db->lastInsertId();
        }

        $screenTemplates = [
            ['name' => 'Login / Sign Up',    'tier' => 'Simple',  'sort_order' => 1],
            ['name' => 'Dashboard / Home',   'tier' => 'Medium',  'sort_order' => 2],
            ['name' => 'User Profile',       'tier' => 'Simple',  'sort_order' => 3],
            ['name' => 'Settings',           'tier' => 'Simple',  'sort_order' => 4],
            ['name' => 'Search & Filters',   'tier' => 'Medium',  'sort_order' => 5],
            ['name' => 'Checkout / Payment', 'tier' => 'Complex', 'sort_order' => 6],
        ];
        $stmt = $db->prepare('INSERT INTO screen_templates (pricing_config_id, name, complexity_tier_id, sort_order) VALUES (:cid, :name, :tier_id, :so)');
        foreach ($screenTemplates as $s) {
            $stmt->execute(['cid' => $configId, 'name' => $s['name'], 'tier_id' => $tierIdByName[$s['tier']], 'so' => $s['sort_order']]);
        }

        $integrations = [
            ['name' => 'Payment Gateway',              'points' => 3, 'sort_order' => 1],
            ['name' => 'SMS / Email Notifications',     'points' => 2, 'sort_order' => 2],
            ['name' => 'Maps / Location',               'points' => 3, 'sort_order' => 3],
            ['name' => 'Social Login',                  'points' => 1, 'sort_order' => 4],
            ['name' => 'Analytics',                     'points' => 1, 'sort_order' => 5],
            ['name' => 'Custom API Integration',        'points' => 4, 'sort_order' => 6],
        ];
        $stmt = $db->prepare('INSERT INTO integrations (pricing_config_id, name, points, sort_order) VALUES (:cid, :name, :pts, :so)');
        foreach ($integrations as $i) {
            $stmt->execute(['cid' => $configId, 'name' => $i['name'], 'pts' => $i['points'], 'so' => $i['sort_order']]);
        }

        $platforms = [
            ['platform' => 'Web',      'multiplier' => 1.0],
            ['platform' => 'Android',  'multiplier' => 1.3],
            ['platform' => 'iOS',      'multiplier' => 1.3],
            ['platform' => 'Desktop',  'multiplier' => 1.2],
            ['platform' => 'PWA',      'multiplier' => 1.1],
        ];
        $stmt = $db->prepare('INSERT INTO platform_multipliers (pricing_config_id, platform, multiplier) VALUES (:cid, :platform, :mul)');
        foreach ($platforms as $p) {
            $stmt->execute(['cid' => $configId, 'platform' => $p['platform'], 'mul' => $p['multiplier']]);
        }

        $packages = [
            ['name' => 'Basic',         'multiplier' => 1.0, 'sort_order' => 1],
            ['name' => 'Professional',  'multiplier' => 1.5, 'sort_order' => 2],
            ['name' => 'Premium',       'multiplier' => 2.5, 'sort_order' => 3],
        ];
        $stmt = $db->prepare('INSERT INTO package_tiers (pricing_config_id, name, multiplier, sort_order) VALUES (:cid, :name, :mul, :so)');
        foreach ($packages as $pkg) {
            $stmt->execute(['cid' => $configId, 'name' => $pkg['name'], 'mul' => $pkg['multiplier'], 'so' => $pkg['sort_order']]);
        }

        $stmt = $db->prepare('INSERT INTO maintenance_settings (pricing_config_id, model, value) VALUES (:cid, :model, :val)');
        $stmt->execute(['cid' => $configId, 'model' => 'percentage', 'val' => 15.00]);

        return $configId;
    }

    public static function getFullConfig(int $configId, int $userId): ?array
    {
        $db = getDB();

        $stmt = $db->prepare('SELECT * FROM pricing_configs WHERE id = :id AND (user_id = :uid OR user_id IS NULL)');
        $stmt->execute(['id' => $configId, 'uid' => $userId]);
        $config = $stmt->fetch();
        if (!$config) return null;

        $stmt = $db->prepare('SELECT * FROM complexity_tiers WHERE pricing_config_id = :cid ORDER BY sort_order');
        $stmt->execute(['cid' => $configId]);
        $config['complexity_tiers'] = $stmt->fetchAll();

        $stmt = $db->prepare(
            'SELECT st.*, ct.name AS tier_name, ct.multiplier AS tier_multiplier
             FROM screen_templates st
             LEFT JOIN complexity_tiers ct ON ct.id = st.complexity_tier_id
             WHERE st.pricing_config_id = :cid ORDER BY st.sort_order'
        );
        $stmt->execute(['cid' => $configId]);
        $config['screen_templates'] = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM integrations WHERE pricing_config_id = :cid ORDER BY sort_order');
        $stmt->execute(['cid' => $configId]);
        $config['integrations'] = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM platform_multipliers WHERE pricing_config_id = :cid');
        $stmt->execute(['cid' => $configId]);
        $config['platform_multipliers'] = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM package_tiers WHERE pricing_config_id = :cid ORDER BY sort_order');
        $stmt->execute(['cid' => $configId]);
        $config['package_tiers'] = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM maintenance_settings WHERE pricing_config_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $configId]);
        $config['maintenance'] = $stmt->fetch() ?: null;

        return $config;
    }

    public static function listByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, name, currency, base_rate, created_at FROM pricing_configs WHERE user_id = :uid ORDER BY created_at DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function duplicate(int $configId, int $userId, string $newName): ?int
    {
        $db = getDB();

        $stmt = $db->prepare('SELECT * FROM pricing_configs WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute(['id' => $configId, 'uid' => $userId]);
        $original = $stmt->fetch();
        if (!$original) return null;

        $stmt = $db->prepare(
            'INSERT INTO pricing_configs (user_id, name, currency, base_rate) VALUES (:uid, :name, :currency, :base_rate)'
        );
        $stmt->execute([
            'uid'      => $userId,
            'name'     => $newName,
            'currency' => $original['currency'],
            'base_rate'=> $original['base_rate'],
        ]);
        $newId = (int) $db->lastInsertId();

        // Copy all child tables (each table only has a subset of these columns)
        $tableColumns = [
            'complexity_tiers'      => ['name', 'multiplier', 'sort_order'],
            'integrations'          => ['name', 'points', 'sort_order'],
            'platform_multipliers'  => ['platform', 'multiplier'],
            'package_tiers'         => ['name', 'multiplier', 'sort_order'],
            'maintenance_settings'  => ['model', 'value'],
        ];
        foreach ($tableColumns as $table => $columns) {
            $columnList = implode(', ', $columns);
            $copyStmt = $db->prepare(
                "INSERT INTO {$table} (pricing_config_id, {$columnList})
                 SELECT :new_id, {$columnList}
                 FROM {$table} WHERE pricing_config_id = :old_id"
            );
            $copyStmt->execute(['new_id' => $newId, 'old_id' => $configId]);
        }

        // Screen templates need their complexity_tier_id remapped to the new config's tiers (matched by name)
        $stmt = $db->prepare(
            'INSERT INTO screen_templates (pricing_config_id, name, complexity_tier_id, sort_order)
             SELECT :new_id, st.name, newtier.id, st.sort_order
             FROM screen_templates st
             JOIN complexity_tiers oldtier ON oldtier.id = st.complexity_tier_id
             JOIN complexity_tiers newtier ON newtier.pricing_config_id = :new_id2 AND newtier.name = oldtier.name
             WHERE st.pricing_config_id = :old_id'
        );
        $stmt->execute(['new_id' => $newId, 'new_id2' => $newId, 'old_id' => $configId]);

        return $newId;
    }
}
