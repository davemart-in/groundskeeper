<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Issue Model
 *
 * Handles issue data operations
 */
class Issue {
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new issue from GitHub data
     *
     * @param int $repositoryId Repository ID
     * @param array $githubIssue GitHub issue data
     * @return int|false Issue ID or false on failure
     */
    public function create($repositoryId, $githubIssue) {
        // Process labels in a single pass
        $labelData = $this->extractLabels($githubIssue['labels'] ?? []);

        // Extract assignees
        $assignees = !empty($githubIssue['assignees'])
            ? json_encode(array_column($githubIssue['assignees'], 'login'))
            : null;

        // Determine last activity (max of updated_at and closed_at)
        $updatedAt = strtotime($githubIssue['updated_at']);
        $closedAt = isset($githubIssue['closed_at']) ? strtotime($githubIssue['closed_at']) : null;
        $lastActivityAt = $closedAt ? max($updatedAt, $closedAt) : $updatedAt;

        $sql = "INSERT INTO issues (
                    repository_id, github_issue_id, issue_number, title, body,
                    state, created_at, updated_at, closed_at, author, labels, url,
                    assignees, milestone, comments_count, reactions_total, is_locked,
                    label_colors, last_activity_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $success = $this->db->execute($sql, [
            $repositoryId,
            $githubIssue['id'],
            $githubIssue['number'],
            $githubIssue['title'],
            $githubIssue['body'] ?? null,
            $githubIssue['state'],
            strtotime($githubIssue['created_at']),
            $updatedAt,
            $closedAt,
            $githubIssue['user']['login'] ?? null,
            $labelData['names'],
            $githubIssue['html_url'] ?? null,
            $assignees,
            $githubIssue['milestone']['title'] ?? null,
            $githubIssue['comments'] ?? 0,
            $githubIssue['reactions']['total_count'] ?? 0,
            !empty($githubIssue['locked']) ? 1 : 0,
            $labelData['colors'],
            $lastActivityAt
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Extract label names and colors from GitHub issue data
     *
     * @param array $labels Label data from GitHub
     * @return array Array with 'names' and 'colors' keys
     */
    private function extractLabels($labels) {
        if (empty($labels)) {
            return ['names' => null, 'colors' => null];
        }

        $names = [];
        $colors = [];

        foreach ($labels as $label) {
            $names[] = $label['name'];
            $colors[$label['name']] = $label['color'] ?? null;
        }

        return [
            'names' => json_encode($names),
            'colors' => json_encode($colors)
        ];
    }

    /**
     * Find all issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return array Array of issues
     */
    public function findByRepository($repositoryId) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, [$repositoryId]);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Delete an issue by ID
     *
     * @param int $issueId Issue ID
     * @return bool Success status
     */
    public function delete($issueId) {
        $sql = "DELETE FROM issues WHERE id = ?";
        return $this->db->execute($sql, [$issueId]);
    }

    /**
     * Delete all issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return bool Success status
     */
    public function deleteByRepository($repositoryId) {
        $sql = "DELETE FROM issues WHERE repository_id = ?";
        return $this->db->execute($sql, [$repositoryId]);
    }

    /**
     * Convert database row to array
     *
     * @param array $row Database row
     * @return array Issue data
     */
    private function rowToArray($row) {
        return [
            'id' => $row['id'],
            'repository_id' => $row['repository_id'],
            'github_issue_id' => $row['github_issue_id'],
            'issue_number' => $row['issue_number'],
            'title' => $row['title'],
            'body' => $row['body'],
            'state' => $row['state'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'closed_at' => $row['closed_at'],
            'author' => $row['author'],
            'labels' => $row['labels'] ? json_decode($row['labels'], true) : [],
            'url' => $row['url'],
            'assignees' => isset($row['assignees']) && $row['assignees'] ? json_decode($row['assignees'], true) : [],
            'milestone' => $row['milestone'] ?? null,
            'comments_count' => $row['comments_count'] ?? 0,
            'reactions_total' => $row['reactions_total'] ?? 0,
            'is_locked' => isset($row['is_locked']) ? (bool)$row['is_locked'] : false,
            'label_colors' => isset($row['label_colors']) && $row['label_colors'] ? json_decode($row['label_colors'], true) : [],
            'last_activity_at' => $row['last_activity_at'] ?? null,
            'area_id' => $row['area_id'] ?? null,
            'is_high_signal' => isset($row['is_high_signal']) ? (bool)$row['is_high_signal'] : false,
            'is_cleanup_candidate' => isset($row['is_cleanup_candidate']) ? (bool)$row['is_cleanup_candidate'] : false,
            'is_missing_context' => isset($row['is_missing_context']) ? (bool)$row['is_missing_context'] : false,
            'missing_elements' => isset($row['missing_elements']) && $row['missing_elements'] ? json_decode($row['missing_elements'], true) : [],
            'is_missing_labels' => isset($row['is_missing_labels']) ? (bool)$row['is_missing_labels'] : false,
            'suggested_labels' => isset($row['suggested_labels']) && $row['suggested_labels'] ? json_decode($row['suggested_labels'], true) : [],
            'summary' => $row['summary'] ?? null,
            'embedding' => isset($row['embedding']) && $row['embedding'] ? json_decode($row['embedding'], true) : null,
            'analyzed_at' => $row['analyzed_at'] ?? null
        ];
    }

    /**
     * Update issue fields
     *
     * @param int $issueId Issue ID
     * @param array $data Fields to update
     * @return bool Success status
     */
    public function update($issueId, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $issueId;

        $sql = "UPDATE issues SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $values);
    }

    /**
     * Update issue area
     *
     * @param int $issueId Issue ID
     * @param int|null $areaId Area ID
     * @return bool Success status
     */
    public function updateArea($issueId, $areaId) {
        $sql = "UPDATE issues SET area_id = ? WHERE id = ?";
        return $this->db->execute($sql, [$areaId, $issueId]);
    }

    /**
     * Update issue analysis data
     *
     * @param int $issueId Issue ID
     * @param array $data Analysis data
     * @return bool Success status
     */
    public function updateAnalysis($issueId, $data) {
        $sql = "UPDATE issues SET
                is_high_signal = ?,
                is_cleanup_candidate = ?,
                is_missing_context = ?,
                missing_elements = ?,
                is_missing_labels = ?,
                suggested_labels = ?,
                summary = ?,
                embedding = ?,
                analyzed_at = ?
                WHERE id = ?";

        return $this->db->execute($sql, [
            isset($data['is_high_signal']) ? (int)$data['is_high_signal'] : 0,
            isset($data['is_cleanup_candidate']) ? (int)$data['is_cleanup_candidate'] : 0,
            isset($data['is_missing_context']) ? (int)$data['is_missing_context'] : 0,
            isset($data['missing_elements']) ? json_encode($data['missing_elements']) : null,
            isset($data['is_missing_labels']) ? (int)$data['is_missing_labels'] : 0,
            isset($data['suggested_labels']) ? json_encode($data['suggested_labels']) : null,
            $data['summary'] ?? null,
            isset($data['embedding']) ? json_encode($data['embedding']) : null,
            time(),
            $issueId
        ]);
    }

    /**
     * Find unanalyzed issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return array Array of issues
     */
    public function findUnanalyzed($repositoryId) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND analyzed_at IS NULL ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, [$repositoryId]);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find issues that need analysis (new or updated since last analysis)
     *
     * @param int $repositoryId Repository ID
     * @return array Array of issues
     */
    public function findNeedingAnalysis($repositoryId) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND (analyzed_at IS NULL OR analyzed_at < updated_at) ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, [$repositoryId]);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find issues with embeddings for a repository
     *
     * @param int $repositoryId Repository ID
     * @return array Array of issues
     */
    public function findWithEmbeddings($repositoryId) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND embedding IS NOT NULL ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, [$repositoryId]);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Count total issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return int Count
     */
    public function countByRepository($repositoryId) {
        $sql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ?";
        $result = $this->db->fetch($sql, [$repositoryId]);
        return $result['count'] ?? 0;
    }

    /**
     * Count high signal issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return int Count
     */
    public function countHighSignal($repositoryId) {
        $sql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_high_signal = 1";
        $result = $this->db->fetch($sql, [$repositoryId]);
        return $result['count'] ?? 0;
    }

    /**
     * Count cleanup candidate issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @return int Count
     */
    public function countCleanupCandidates($repositoryId) {
        $sql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_cleanup_candidate = 1";
        $result = $this->db->fetch($sql, [$repositoryId]);
        return $result['count'] ?? 0;
    }

    /**
     * Count issues missing context for a repository
     *
     * @param int $repositoryId Repository ID
     * @return int Count
     */
    public function countMissingContext($repositoryId) {
        $sql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_context = 1";
        $result = $this->db->fetch($sql, [$repositoryId]);
        return $result['count'] ?? 0;
    }

    /**
     * Count issues missing labels for a repository
     *
     * @param int $repositoryId Repository ID
     * @return int Count
     */
    public function countMissingLabels($repositoryId) {
        $sql = "SELECT COUNT(*) as count FROM issues WHERE repository_id = ? AND is_missing_labels = 1 AND suggested_labels IS NOT NULL";
        $result = $this->db->fetch($sql, [$repositoryId]);
        return $result['count'] ?? 0;
    }

    /**
     * Find high signal issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @param int|null $areaId Optional area filter
     * @return array Array of issues
     */
    public function findHighSignal($repositoryId, $areaId = null) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND is_high_signal = 1";
        $params = [$repositoryId];

        if ($areaId) {
            $sql .= " AND area_id = ?";
            $params[] = $areaId;
        }

        $sql .= " ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, $params);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find cleanup candidate issues for a repository
     *
     * @param int $repositoryId Repository ID
     * @param int|null $areaId Optional area filter
     * @return array Array of issues
     */
    public function findCleanupCandidates($repositoryId, $areaId = null) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND is_cleanup_candidate = 1";
        $params = [$repositoryId];

        if ($areaId) {
            $sql .= " AND area_id = ?";
            $params[] = $areaId;
        }

        $sql .= " ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, $params);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find issues missing context for a repository
     *
     * @param int $repositoryId Repository ID
     * @param int|null $areaId Optional area filter
     * @return array Array of issues
     */
    public function findMissingContext($repositoryId, $areaId = null) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND is_missing_context = 1";
        $params = [$repositoryId];

        if ($areaId) {
            $sql .= " AND area_id = ?";
            $params[] = $areaId;
        }

        $sql .= " ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, $params);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find issues missing labels for a repository
     *
     * @param int $repositoryId Repository ID
     * @param int|null $areaId Optional area filter
     * @return array Array of issues
     */
    public function findMissingLabels($repositoryId, $areaId = null) {
        $sql = "SELECT * FROM issues WHERE repository_id = ? AND is_missing_labels = 1 AND suggested_labels IS NOT NULL";
        $params = [$repositoryId];

        if ($areaId) {
            $sql .= " AND area_id = ?";
            $params[] = $areaId;
        }

        $sql .= " ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql, $params);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }
}
