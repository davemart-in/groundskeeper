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
        // Repositories table
        $sql = "CREATE TABLE IF NOT EXISTS repositories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner TEXT NOT NULL,
            name TEXT NOT NULL,
            full_name TEXT NOT NULL,
            bug_label TEXT DEFAULT 'type: bug',
            priority_labels TEXT,
            last_synced_at INTEGER,
            last_audited_at INTEGER,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(owner, name)
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
            assignees TEXT,
            milestone TEXT,
            comments_count INTEGER DEFAULT 0,
            reactions_total INTEGER DEFAULT 0,
            is_locked INTEGER DEFAULT 0,
            label_colors TEXT,
            last_activity_at INTEGER,
            area_id INTEGER,
            is_high_signal INTEGER DEFAULT 0,
            is_cleanup_candidate INTEGER DEFAULT 0,
            is_missing_context INTEGER DEFAULT 0,
            missing_elements TEXT,
            is_missing_labels INTEGER DEFAULT 0,
            suggested_labels TEXT,
            summary TEXT,
            embedding TEXT,
            analyzed_at INTEGER,
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
            FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
            UNIQUE(repository_id, github_issue_id)
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_repository_id ON issues(repository_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_github_issue_id ON issues(github_issue_id)");

        // Areas table
        $sql = "CREATE TABLE IF NOT EXISTS areas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
            UNIQUE(repository_id, name)
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_area_repository_id ON areas(repository_id)");

        // Analysis jobs table
        $sql = "CREATE TABLE IF NOT EXISTS analysis_jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            total_issues INTEGER NOT NULL DEFAULT 0,
            processed_issues INTEGER NOT NULL DEFAULT 0,
            current_step TEXT,
            error_message TEXT,
            started_at INTEGER,
            completed_at INTEGER,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_analysis_job_repository ON analysis_jobs(repository_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_analysis_job_status ON analysis_jobs(status)");

        // Analysis results table
        $sql = "CREATE TABLE IF NOT EXISTS analysis_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            repository_id INTEGER NOT NULL,
            duplicates TEXT,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_analysis_results_repository ON analysis_results(repository_id)");

        // Sessions table
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            session_id TEXT PRIMARY KEY,
            session_data TEXT NOT NULL,
            last_activity INTEGER NOT NULL
        )";
        $this->pdo->exec($sql);
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity)");
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
     * Get PDO instance (for advanced operations like checking affected rows)
     *
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
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
}
