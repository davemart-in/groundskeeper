<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * AnalysisJob Model
 *
 * Tracks progress of issue analysis jobs
 */
class AnalysisJob {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new analysis job
     *
     * @param int $repositoryId Repository ID
     * @param int $totalIssues Total number of issues
     * @return int Job ID
     */
    public function create($repositoryId, $totalIssues) {
        $sql = "INSERT INTO analysis_jobs (repository_id, total_issues, created_at)
                VALUES (?, ?, ?)";

        $this->db->execute($sql, [$repositoryId, $totalIssues, time()]);
        return $this->db->lastInsertId();
    }

    /**
     * Find active job for repository
     *
     * @param int $repositoryId Repository ID
     * @return array|null Job data or null
     */
    public function findActive($repositoryId) {
        $sql = "SELECT * FROM analysis_jobs
                WHERE repository_id = ? AND status IN ('pending', 'processing', 'syncing')
                ORDER BY created_at DESC LIMIT 1";

        $row = $this->db->fetch($sql, [$repositoryId]);
        return $row ? $this->rowToArray($row) : null;
    }

    /**
     * Find job by ID
     *
     * @param int $jobId Job ID
     * @return array|null Job data or null
     */
    public function findById($jobId) {
        $sql = "SELECT * FROM analysis_jobs WHERE id = ?";
        $row = $this->db->fetch($sql, [$jobId]);
        return $row ? $this->rowToArray($row) : null;
    }

    /**
     * Update job progress
     *
     * @param int $jobId Job ID
     * @param int $processedIssues Number of issues processed
     * @param string $currentStep Current step description
     * @return bool Success status
     */
    public function updateProgress($jobId, $processedIssues, $currentStep = null) {
        $sql = "UPDATE analysis_jobs
                SET processed_issues = ?, current_step = ?, status = 'processing'
                WHERE id = ?";

        return $this->db->execute($sql, [$processedIssues, $currentStep, $jobId]);
    }

    /**
     * Update job fields
     *
     * @param int $jobId Job ID
     * @param array $data Fields to update
     * @return bool Success status
     */
    public function update($jobId, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $jobId;

        $sql = "UPDATE analysis_jobs SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $values);
    }

    /**
     * Mark job as completed
     *
     * @param int $jobId Job ID
     * @return bool Success status
     */
    public function complete($jobId) {
        $sql = "UPDATE analysis_jobs
                SET status = 'completed', completed_at = ?
                WHERE id = ?";

        return $this->db->execute($sql, [time(), $jobId]);
    }

    /**
     * Mark job as failed
     *
     * @param int $jobId Job ID
     * @param string $errorMessage Error message
     * @return bool Success status
     */
    public function fail($jobId, $errorMessage) {
        $sql = "UPDATE analysis_jobs
                SET status = 'failed', error_message = ?, completed_at = ?
                WHERE id = ?";

        return $this->db->execute($sql, [$errorMessage, time(), $jobId]);
    }

    /**
     * Delete all jobs for a repository
     *
     * @param int $repositoryId Repository ID
     * @return bool Success status
     */
    public function deleteByRepository($repositoryId) {
        $sql = "DELETE FROM analysis_jobs WHERE repository_id = ?";
        return $this->db->execute($sql, [$repositoryId]);
    }

    /**
     * Convert database row to array
     *
     * @param array $row Database row
     * @return array Job data
     */
    private function rowToArray($row) {
        return [
            'id' => $row['id'],
            'repository_id' => $row['repository_id'],
            'status' => $row['status'],
            'total_issues' => $row['total_issues'],
            'processed_issues' => $row['processed_issues'],
            'current_step' => $row['current_step'] ?? null,
            'error_message' => $row['error_message'] ?? null,
            'started_at' => $row['started_at'] ?? null,
            'completed_at' => $row['completed_at'] ?? null,
            'created_at' => $row['created_at']
        ];
    }
}
