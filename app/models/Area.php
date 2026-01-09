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
     * Find area by name
     *
     * @param int $repositoryId Repository ID
     * @param string $name Area name
     * @return array|null Area data or null if not found
     */
    public function findByName($repositoryId, $name) {
        $sql = "SELECT * FROM areas WHERE repository_id = ? AND name = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$repositoryId, $name]);

        return $row ? $this->rowToArray($row) : null;
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
}
