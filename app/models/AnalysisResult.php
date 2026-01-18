<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * AnalysisResult Model
 *
 * Handles storage and retrieval of analysis results per repository
 */
class AnalysisResult {
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Save analysis results for a repository
     *
     * @param int $repositoryId Repository ID
     * @param array $duplicates Duplicate groups array
     * @return bool Success status
     */
    public function save($repositoryId, $duplicates) {
        $now = time();

        // Check if results exist for this repository
        $existing = $this->findByRepository($repositoryId);

        if ($existing) {
            // Update existing
            $sql = "UPDATE analysis_results SET duplicates = ?, created_at = ? WHERE repository_id = ?";
            return $this->db->execute($sql, [
                json_encode($duplicates),
                $now,
                $repositoryId
            ]);
        } else {
            // Insert new
            $sql = "INSERT INTO analysis_results (repository_id, duplicates, created_at) VALUES (?, ?, ?)";
            return $this->db->execute($sql, [
                $repositoryId,
                json_encode($duplicates),
                $now
            ]);
        }
    }

    /**
     * Find analysis results by repository
     *
     * @param int $repositoryId Repository ID
     * @return array|null Results array or null if not found
     */
    public function findByRepository($repositoryId) {
        $sql = "SELECT * FROM analysis_results WHERE repository_id = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$repositoryId]);

        if (!$row) {
            return null;
        }

        return [
            'id' => $row['id'],
            'repository_id' => $row['repository_id'],
            'duplicates' => json_decode($row['duplicates'], true) ?? [],
            'created_at' => $row['created_at']
        ];
    }

    /**
     * Delete analysis results for a repository
     *
     * @param int $repositoryId Repository ID
     * @return bool Success status
     */
    public function deleteByRepository($repositoryId) {
        $sql = "DELETE FROM analysis_results WHERE repository_id = ?";
        return $this->db->execute($sql, [$repositoryId]);
    }
}
