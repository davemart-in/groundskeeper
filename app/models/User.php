<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * User Model
 *
 * Handles user data operations for GitHub authenticated users
 */
class User {
    private $db;
    private $encryption;
    public $id;
    public $github_id;
    public $github_username;
    public $avatar_url;
    public $created_at;
    public $updated_at;
    private $github_access_token; // Encrypted in database

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->encryption = new Encryption();
    }

    /**
     * Find user by GitHub ID
     *
     * @param int $githubId GitHub user ID
     * @return User|null User object or null if not found
     */
    public function findByGitHubId($githubId) {
        $sql = "SELECT * FROM users WHERE github_id = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$githubId]);

        if ($row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * Find user by ID
     *
     * @param int $id User ID
     * @return User|null User object or null if not found
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $row = $this->db->fetch($sql, [$id]);

        if ($row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * Create a new user
     *
     * @param array $data User data (github_id, github_username, github_access_token, avatar_url)
     * @return User Created user object
     */
    public function create($data) {
        $now = time();

        // Encrypt the access token
        $encryptedToken = $this->encryption->encrypt($data['github_access_token']);

        $sql = "INSERT INTO users (github_id, github_username, github_access_token, avatar_url, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?)";

        $this->db->execute($sql, [
            $data['github_id'],
            $data['github_username'],
            $encryptedToken,
            $data['avatar_url'] ?? null,
            $now,
            $now
        ]);

        $userId = $this->db->lastInsertId();

        // Load and return the created user
        return $this->findById($userId);
    }

    /**
     * Update user data
     *
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];

        // Build dynamic update query
        if (isset($data['github_username'])) {
            $updates[] = 'github_username = ?';
            $params[] = $data['github_username'];
        }

        if (isset($data['github_access_token'])) {
            $updates[] = 'github_access_token = ?';
            $params[] = $this->encryption->encrypt($data['github_access_token']);
        }

        if (isset($data['avatar_url'])) {
            $updates[] = 'avatar_url = ?';
            $params[] = $data['avatar_url'];
        }

        // Always update timestamp
        $updates[] = 'updated_at = ?';
        $params[] = time();

        // Add ID to params
        $params[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete user (remove GitHub token)
     *
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Get decrypted access token
     *
     * @return string Decrypted GitHub access token
     */
    public function getDecryptedToken() {
        if (empty($this->github_access_token)) {
            return '';
        }

        try {
            return $this->encryption->decrypt($this->github_access_token);
        } catch (Exception $e) {
            error_log('Failed to decrypt user token: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get current authenticated user from session
     *
     * @return User|null Current user or null if not authenticated
     */
    public static function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userModel = new self();
        return $userModel->findById($_SESSION['user_id']);
    }

    /**
     * Hydrate user object from database row
     *
     * @param array $row Database row
     * @return User User object
     */
    private function hydrate($row) {
        $this->id = $row['id'];
        $this->github_id = $row['github_id'];
        $this->github_username = $row['github_username'];
        $this->github_access_token = $row['github_access_token']; // Keep encrypted
        $this->avatar_url = $row['avatar_url'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];

        return $this;
    }

    /**
     * Convert user to array (for passing to views)
     *
     * @return array User data (without encrypted token)
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'github_id' => $this->github_id,
            'github_username' => $this->github_username,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
