<?php
declare(strict_types=1);

/**
 * Migration 002: Screen Templates
 * Run: php database/migrations/002_screen_templates.php
 *
 * Adds screen_templates (predefined, checkbox-selectable screens per pricing config)
 * and links project_screens to the template it was created from, if any.
 */

require_once __DIR__ . '/../../backend/config/app.php';
loadEnv();
require_once __DIR__ . '/../../backend/config/database.php';

$db = getDB();
echo "Running migration 002: Screen templates...\n";

$db->exec("
    CREATE TABLE IF NOT EXISTS _migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$stmt = $db->prepare('SELECT id FROM _migrations WHERE migration = :m LIMIT 1');
$stmt->execute(['m' => '002_screen_templates']);
if ($stmt->fetch()) {
    echo "  Already applied. Skipping.\n";
    exit(0);
}

$db->exec("
    CREATE TABLE IF NOT EXISTS screen_templates (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        pricing_config_id INT UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        complexity_tier_id INT UNSIGNED NOT NULL,
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pricing_config_id) REFERENCES pricing_configs(id) ON DELETE CASCADE,
        FOREIGN KEY (complexity_tier_id) REFERENCES complexity_tiers(id),
        INDEX idx_config_id (pricing_config_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  Created: screen_templates\n";

// Add screen_template_id to project_screens if it doesn't already exist
$stmt = $db->query("SHOW COLUMNS FROM project_screens LIKE 'screen_template_id'");
if ($stmt->rowCount() === 0) {
    $db->exec("
        ALTER TABLE project_screens
        ADD COLUMN screen_template_id INT UNSIGNED DEFAULT NULL AFTER complexity_tier_id,
        ADD CONSTRAINT fk_project_screens_template
            FOREIGN KEY (screen_template_id) REFERENCES screen_templates(id) ON DELETE SET NULL
    ");
    echo "  Altered: project_screens (added screen_template_id)\n";
} else {
    echo "  Column project_screens.screen_template_id already exists. Skipping alter.\n";
}

$stmt = $db->prepare('INSERT INTO _migrations (migration) VALUES (:m)');
$stmt->execute(['m' => '002_screen_templates']);

echo "Migration 002 complete.\n";
