<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Area Model
 *
 * Handles functional area data operations
 */
class Area {
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find all areas for a repository
     *
     * @param int $repositoryId Repository ID
     * @return array Array of areas
     */
    public function findByRepository($repositoryId) {
        $sql = "SELECT * FROM areas WHERE repository_id = ? ORDER BY name ASC";
        $rows = $this->db->fetchAll($sql, [$repositoryId]);

        if (empty($rows)) {
            return [];
        }

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Create a new area
     *
     * @param int $repositoryId Repository ID
     * @param string $name Area name
     * @return int|false Area ID or false on failure
     */
    public function create($repositoryId, $name) {
        $sql = "INSERT INTO areas (repository_id, name, created_at) VALUES (?, ?, ?)";

        $success = $this->db->execute($sql, [
            $repositoryId,
            $name,
            time()
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Delete all areas for a repository
     *
     * @param int $repositoryId Repository ID
     * @return bool Success status
     */
    public function deleteByRepository($repositoryId) {
        $sql = "DELETE FROM areas WHERE repository_id = ?";
        return $this->db->execute($sql, [$repositoryId]);
    }

    /**
     * Convert database row to array
     *
     * @param array $row Database row
     * @return array Area data
     */
    private function rowToArray($row) {
        return [
            'id' => $row['id'],
            'repository_id' => $row['repository_id'],
            'name' => $row['name'],
            'created_at' => $row['created_at']
        ];
    }

    /**
     * Get area statistics with issue counts for a repository
     *
     * @param int $repositoryId Repository ID
     * @param int $totalIssues Total issues for percentage calculation
     * @return array Array of area stats sorted by count descending
     */
    public function getStatsForRepository($repositoryId, $totalIssues = 0) {
        $areas = $this->findByRepository($repositoryId);

        // Get issue counts per area
        $sql = "SELECT area_id, COUNT(*) as count
                FROM issues
                WHERE repository_id = ? AND area_id IS NOT NULL
                GROUP BY area_id";
        $areaCounts = $this->db->fetchAll($sql, [$repositoryId]);

        // Create lookup map
        $areaCountMap = [];
        foreach ($areaCounts as $row) {
            $areaCountMap[$row['area_id']] = $row['count'];
        }

        // Build area stats array
        $areaStats = [];
        foreach ($areas as $area) {
            $count = $areaCountMap[$area['id']] ?? 0;
            if ($count > 0) {
                $areaStats[] = [
                    'id' => $area['id'],
                    'name' => $area['name'],
                    'count' => $count,
                    'percentage' => $totalIssues > 0 ? round(($count / $totalIssues) * 100) : 0
                ];
            }
        }

        // Sort by count descending
        usort($areaStats, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return $areaStats;
    }
}
