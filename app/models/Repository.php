<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Repository Model
 *
 * Handles repository data operations
 */
class Repository {
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find all repositories
     *
     * @return array Array of repositories
     */
    public function findAll() {
        $sql = "SELECT * FROM repositories ORDER BY created_at DESC";
        $rows = $this->db->fetchAll($sql);

        return array_map(function($row) {
            return $this->rowToArray($row);
        }, $rows);
    }

    /**
     * Find repository by ID
     *
     * @param int $id Repository ID
     * @return array|null Repository data or null if not found
     */
    public function findById($id) {
        $sql = "SELECT * FROM repositories WHERE id = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$id]);

        return $row ? $this->rowToArray($row) : null;
    }

    /**
     * Find repository by owner/name
     *
     * @param string $owner Repository owner
     * @param string $name Repository name
     * @return array|null Repository data or null if not found
     */
    public function findByOwnerName($owner, $name) {
        $sql = "SELECT * FROM repositories WHERE owner = ? AND name = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$owner, $name]);

        return $row ? $this->rowToArray($row) : null;
    }

    /**
     * Create a new repository
     *
     * @param array $data Repository data (owner, name, bug_label)
     * @return array Created repository data
     */
    public function create($data) {
        $now = time();

        $owner = $data['owner'];
        $name = $data['name'];
        $fullName = $owner . '/' . $name;

        $sql = "INSERT INTO repositories (owner, name, full_name, bug_label, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?)";

        $this->db->execute($sql, [
            $owner,
            $name,
            $fullName,
            $data['bug_label'] ?? 'bug',
            $now,
            $now
        ]);

        $repoId = $this->db->lastInsertId();

        return $this->findById($repoId);
    }

    /**
     * Update repository
     *
     * @param int $id Repository ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];

        if (isset($data['bug_label'])) {
            $updates[] = 'bug_label = ?';
            $params[] = $data['bug_label'];
        }

        if (isset($data['priority_labels'])) {
            $updates[] = 'priority_labels = ?';
            $params[] = $data['priority_labels'];
        }

        if (isset($data['last_synced_at'])) {
            $updates[] = 'last_synced_at = ?';
            $params[] = $data['last_synced_at'];
        }

        if (array_key_exists('last_audited_at', $data)) {
            $updates[] = 'last_audited_at = ?';
            $params[] = $data['last_audited_at'];
        }

        // Always update timestamp
        $updates[] = 'updated_at = ?';
        $params[] = time();

        // Add ID to params
        $params[] = $id;

        $sql = "UPDATE repositories SET " . implode(', ', $updates) . " WHERE id = ?";

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete repository
     *
     * @param int $id Repository ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM repositories WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Convert database row to array
     *
     * @param array $row Database row
     * @return array Repository data
     */
    private function rowToArray($row) {
        return [
            'id' => $row['id'],
            'owner' => $row['owner'],
            'name' => $row['name'],
            'full_name' => $row['full_name'],
            'bug_label' => $row['bug_label'],
            'priority_labels' => $row['priority_labels'] ?? null,
            'last_synced_at' => $row['last_synced_at'],
            'last_audited_at' => $row['last_audited_at'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    /**
     * Parse repository slug (owner/name format)
     *
     * @param string $slug Repository slug (e.g., "owner/repo")
     * @return array|null Array with 'owner' and 'name' keys, or null if invalid
     */
    public static function parseSlug($slug) {
        // Remove leading/trailing slashes and whitespace
        $slug = trim($slug, " \t\n\r\0\x0B/");
        $parts = explode('/', $slug);

        // Filter out empty parts (in case of double slashes)
        $parts = array_filter($parts, function($part) {
            return !empty($part);
        });
        $parts = array_values($parts); // Re-index array

        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            return null;
        }

        return [
            'owner' => $parts[0],
            'name' => $parts[1]
        ];
    }

    /**
     * Auto-detect bug label from GitHub repository
     *
     * @param string $owner Repository owner
     * @param string $name Repository name
     * @param GitHubAPI $githubApi GitHub API instance
     * @return string Detected bug label name
     */
    public static function detectBugLabel($owner, $name, $githubApi) {
        $labels = $githubApi->getLabels($owner, $name);

        if ($labels === false || empty($labels)) {
            return 'bug';
        }

        // Extract label names
        $labelNames = array_map(function($label) {
            return $label['name'];
        }, $labels);

        // Detect bug label
        $bugKeywords = ['bug', 'defect', '🐛', 'type: bug', 'kind: bug'];

        foreach ($labelNames as $labelName) {
            foreach ($bugKeywords as $keyword) {
                if (stripos($labelName, $keyword) !== false) {
                    return $labelName;
                }
            }
        }

        return 'bug'; // Default
    }
}
