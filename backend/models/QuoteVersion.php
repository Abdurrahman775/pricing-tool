<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class QuoteVersion
{
    public static function create(int $projectId, array $breakdown, ?string $selectedPackage = null, ?float $packagePrice = null): int
    {
        $db = getDB();

        // Get next version number
        $stmt = $db->prepare('SELECT COALESCE(MAX(version_number), 0) + 1 AS next_ver FROM quote_versions WHERE project_id = :pid');
        $stmt->execute(['pid' => $projectId]);
        $nextVer = (int) $stmt->fetchColumn();

        $maint = $breakdown['maintenance'] ?? null;

        $stmt = $db->prepare(
            'INSERT INTO quote_versions
                (project_id, version_number, screen_points, integration_points,
                 platform_multiplier, subtotal, base_rate, base_price,
                 selected_package, package_price,
                 maintenance_model, maintenance_value, pricing_breakdown, is_locked)
             VALUES
                (:pid, :ver, :sp, :ip, :pm, :sub, :br, :bp,
                 :pkg, :pp, :mm, :mv, :pb, 1)'
        );
        $stmt->execute([
            'pid'  => $projectId,
            'ver'  => $nextVer,
            'sp'   => (int) ($breakdown['total_screen_points'] ?? 0),
            'ip'   => (int) ($breakdown['total_integration_points'] ?? 0),
            'pm'   => (float) ($breakdown['platform_multiplier'] ?? 1.0),
            'sub'  => (float) ($breakdown['adjusted_subtotal'] ?? 0),
            'br'   => (float) ($breakdown['base_rate'] ?? 0),
            'bp'   => (float) ($breakdown['base_price'] ?? 0),
            'pkg'  => $selectedPackage,
            'pp'   => $packagePrice,
            'mm'   => $maint['model'] ?? null,
            'mv'   => $maint ? (float) ($maint['value'] ?? 0) : null,
            'pb'   => json_encode($breakdown),
        ]);

        return (int) $db->lastInsertId();
    }

    public static function getByProject(int $projectId, int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare(
            'SELECT qv.* FROM quote_versions qv
             JOIN projects p ON p.id = qv.project_id
             WHERE qv.project_id = :pid AND p.user_id = :uid
             ORDER BY qv.version_number DESC'
        );
        $stmt->execute(['pid' => $projectId, 'uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getLatest(int $projectId, int $userId): ?array
    {
        $db = getDB();
        $stmt = $db->prepare(
            'SELECT qv.* FROM quote_versions qv
             JOIN projects p ON p.id = qv.project_id
             WHERE qv.project_id = :pid AND p.user_id = :uid
             ORDER BY qv.version_number DESC LIMIT 1'
        );
        $stmt->execute(['pid' => $projectId, 'uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function selectPackage(int $versionId, int $projectId, int $userId, string $packageName, float $packagePrice): void
    {
        $db = getDB();
        $stmt = $db->prepare(
            'UPDATE quote_versions qv
             JOIN projects p ON p.id = qv.project_id
             SET qv.selected_package = :pkg, qv.package_price = :pp
             WHERE qv.id = :vid AND qv.project_id = :pid AND p.user_id = :uid'
        );
        $stmt->execute([
            'pkg' => $packageName,
            'pp'  => $packagePrice,
            'vid' => $versionId,
            'pid' => $projectId,
            'uid' => $userId,
        ]);
    }
}
