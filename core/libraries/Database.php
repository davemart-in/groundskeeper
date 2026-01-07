<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * SQLite Database Wrapper
 *
 * Simple PDO wrapper for SQLite operations with singleton pattern
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $dbPath;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->dbPath = COREPATH . 'database/groundskeeper.sqlite';

        try {
            // Create directory if it doesn't exist
            $dbDir = dirname($this->dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            // Connect to SQLite database
            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Initialize schema if needed
            $this->initializeSchema();
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }

    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize database schema if tables don't exist
     */
    private function initializeSchema() {
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            github_id INTEGER UNIQUE,
            github_username TEXT NOT NULL,
            github_access_token TEXT,
            avatar_url TEXT,
            access_mode TEXT NOT NULL DEFAULT 'readonly',
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_github_id ON users(github_id)");

        // Repositories table
        $sql = "CREATE TABLE IF NOT EXISTS repositories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            owner TEXT NOT NULL,
            name TEXT NOT NULL,
            full_name TEXT NOT NULL,
            bug_label TEXT DEFAULT 'type: bug',
            last_synced_at INTEGER,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(user_id, owner, name)
        )";
        $this->pdo->exec($sql);

        // Issues table
        $sql = "CREATE TABLE IF NOT EXISTS issues (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            github_issue_id INTEGER NOT NULL,
            issue_number INTEGER NOT NULL,
            title TEXT NOT NULL,
            body TEXT,
            state TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            closed_at INTEGER,
            author TEXT,
            labels TEXT,
            url TEXT,
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
            UNIQUE(repository_id, github_issue_id)
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_repository_id ON issues(repository_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_github_issue_id ON issues(github_issue_id)");
    }

    /**
     * Execute a query and return PDOStatement
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage());
            throw new Exception('Database query failed');
        }
    }

    /**
     * Execute a query (INSERT, UPDATE, DELETE)
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return bool Success status
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Database execute failed: ' . $e->getMessage());
            throw new Exception('Database execute failed');
        }
    }

    /**
     * Fetch a single row
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows
     *
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get last insert ID
     *
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Get PDO instance for advanced operations
     *
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }
}
