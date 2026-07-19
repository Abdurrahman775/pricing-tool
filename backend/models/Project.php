<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class Project
{
    public static function create(int $userId, array $data): int
    {
        $db = getDB();

        $stmt = $db->prepare(
            'INSERT INTO projects (user_id, pricing_config_id, client_name, project_name, notes, status)
             VALUES (:uid, :config_id, :client, :project, :notes, :status)'
        );
        $stmt->execute([
            'uid'       => $userId,
            'config_id' => (int) $data['pricing_config_id'],
            'client'    => trim($data['client_name'] ?? ''),
            'project'   => trim($data['project_name'] ?? ''),
            'notes'     => $data['notes'] ?? '',
            'status'    => 'draft',
        ]);
        $projectId = (int) $db->lastInsertId();

        // Save screens
        if (!empty($data['screens'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_screens (project_id, name, complexity_tier_id, notes)
                 VALUES (:pid, :name, :tier_id, :notes)'
            );
            foreach ($data['screens'] as $s) {
                if (!empty($s['name'])) {
                    $stmt->execute([
                        'pid'     => $projectId,
                        'name'    => $s['name'],
                        'tier_id' => (int) ($s['complexity_tier_id'] ?? 0),
                        'notes'   => $s['notes'] ?? '',
                    ]);
                }
            }
        }

        // Save integrations
        if (!empty($data['integration_ids'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_integrations (project_id, integration_id, custom_name, custom_points)
                 VALUES (:pid, :iid, :cname, :cpts)'
            );
            foreach ($data['integration_ids'] as $item) {
                if (is_array($item) && !empty($item['custom_name'])) {
                    $stmt->execute([
                        'pid'   => $projectId,
                        'iid'   => null,
                        'cname' => $item['custom_name'],
                        'cpts'  => (int) ($item['custom_points'] ?? 0),
                    ]);
                } elseif (is_numeric($item)) {
                    $stmt->execute([
                        'pid'   => $projectId,
                        'iid'   => (int) $item,
                        'cname' => null,
                        'cpts'  => null,
                    ]);
                }
            }
        }

        // Save platforms
        if (!empty($data['platforms'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_platforms (project_id, platform) VALUES (:pid, :platform)'
            );
            foreach ($data['platforms'] as $p) {
                if (!empty($p)) {
                    $stmt->execute(['pid' => $projectId, 'platform' => $p]);
                }
            }
        }

        return $projectId;
    }

    public static function getFull(int $projectId, int $userId): ?array
    {
        $db = getDB();

        $stmt = $db->prepare('SELECT * FROM projects WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $projectId, 'uid' => $userId]);
        $project = $stmt->fetch();
        if (!$project) return null;

        $stmt = $db->prepare(
            'SELECT ps.*, ct.name AS tier_name, ct.multiplier AS tier_multiplier
             FROM project_screens ps
             LEFT JOIN complexity_tiers ct ON ct.id = ps.complexity_tier_id
             WHERE ps.project_id = :pid
             ORDER BY ps.id'
        );
        $stmt->execute(['pid' => $projectId]);
        $project['screens'] = $stmt->fetchAll();

        $stmt = $db->prepare(
            'SELECT pi.*, i.name AS integration_name, i.points AS integration_points
             FROM project_integrations pi
             LEFT JOIN integrations i ON i.id = pi.integration_id
             WHERE pi.project_id = :pid'
        );
        $stmt->execute(['pid' => $projectId]);
        $project['integrations'] = $stmt->fetchAll();

        $stmt = $db->prepare('SELECT * FROM project_platforms WHERE project_id = :pid');
        $stmt->execute(['pid' => $projectId]);
        $project['platforms'] = array_column($stmt->fetchAll(), 'platform');

        $stmt = $db->prepare('SELECT * FROM quote_versions WHERE project_id = :pid ORDER BY version_number DESC LIMIT 1');
        $stmt->execute(['pid' => $projectId]);
        $project['latest_version'] = $stmt->fetch() ?: null;

        return $project;
    }

    public static function updateDraft(int $projectId, int $userId, array $data): void
    {
        $db = getDB();

        // Verify project exists, belongs to user, and is draft
        $stmt = $db->prepare('SELECT id, status FROM projects WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $projectId, 'uid' => $userId]);
        $project = $stmt->fetch();
        if (!$project) {
            throw new RuntimeException('Project not found');
        }
        if ($project['status'] !== 'draft') {
            throw new RuntimeException('Only draft projects can be edited');
        }

        // Update project row
        $stmt = $db->prepare(
            'UPDATE projects SET client_name = :client, project_name = :project, notes = :notes WHERE id = :id'
        );
        $stmt->execute([
            'client'  => trim($data['client_name'] ?? ''),
            'project' => trim($data['project_name'] ?? ''),
            'notes'   => $data['notes'] ?? '',
            'id'      => $projectId,
        ]);

        // Replace screens
        $db->prepare('DELETE FROM project_screens WHERE project_id = :pid')->execute(['pid' => $projectId]);
        if (!empty($data['screens'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_screens (project_id, name, complexity_tier_id, notes)
                 VALUES (:pid, :name, :tier_id, :notes)'
            );
            foreach ($data['screens'] as $s) {
                if (!empty($s['name'])) {
                    $stmt->execute([
                        'pid'     => $projectId,
                        'name'    => $s['name'],
                        'tier_id' => (int) ($s['complexity_tier_id'] ?? 0),
                        'notes'   => $s['notes'] ?? '',
                    ]);
                }
            }
        }

        // Replace integrations
        $db->prepare('DELETE FROM project_integrations WHERE project_id = :pid')->execute(['pid' => $projectId]);
        if (!empty($data['integration_ids'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_integrations (project_id, integration_id, custom_name, custom_points)
                 VALUES (:pid, :iid, :cname, :cpts)'
            );
            foreach ($data['integration_ids'] as $item) {
                if (is_array($item) && !empty($item['custom_name'])) {
                    $stmt->execute([
                        'pid'   => $projectId,
                        'iid'   => null,
                        'cname' => $item['custom_name'],
                        'cpts'  => (int) ($item['custom_points'] ?? 0),
                    ]);
                } elseif (is_numeric($item)) {
                    $stmt->execute([
                        'pid'   => $projectId,
                        'iid'   => (int) $item,
                        'cname' => null,
                        'cpts'  => null,
                    ]);
                }
            }
        }

        // Replace platforms
        $db->prepare('DELETE FROM project_platforms WHERE project_id = :pid')->execute(['pid' => $projectId]);
        if (!empty($data['platforms'])) {
            $stmt = $db->prepare(
                'INSERT INTO project_platforms (project_id, platform) VALUES (:pid, :platform)'
            );
            foreach ($data['platforms'] as $p) {
                if (!empty($p)) {
                    $stmt->execute(['pid' => $projectId, 'platform' => $p]);
                }
            }
        }
    }

    public static function listByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare(
            'SELECT p.id, p.project_name, p.client_name, p.status, p.created_at,
                    pc.name AS config_name, pc.currency,
                    (SELECT COUNT(*) FROM project_screens WHERE project_id = p.id) AS screen_count
             FROM projects p
             LEFT JOIN pricing_configs pc ON pc.id = p.pricing_config_id
             WHERE p.user_id = :uid
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
