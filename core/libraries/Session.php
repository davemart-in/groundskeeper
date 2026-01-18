<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * SQLite Session Handler
 *
 * Stores PHP sessions in SQLite database instead of files or Redis
 */
class SQLiteSession implements SessionHandlerInterface {
	private $db;
	private $maxLifetime;

	/**
	 * Constructor - receives Database instance
	 *
	 * @param Database $db The Database instance
	 */
	public function __construct($db) {
		$this->db = $db;

		// Set timeout to 30 days
		ini_set('session.gc_maxlifetime', 60*60*24*30);
		$this->maxLifetime = ini_get('session.gc_maxlifetime');
	}

	/**
	 * Initialize session
	 *
	 * @param string $savePath The path where to store the session
	 * @param string $name     The session name
	 */
	public function open(string $savePath, string $name): bool {
		return true;
	}

	/**
	 * Garbage collection - remove expired sessions
	 *
	 * @param int $maxLifetime The max lifetime of a session
	 */
	public function gc(int $maxLifetime): int|false {
		$expireTime = time() - $maxLifetime;

		try {
			$sql = "DELETE FROM sessions WHERE last_activity < ?";
			$this->db->execute($sql, [$expireTime]);
			return 0; // Return number deleted (not critical)
		} catch (Exception $e) {
			error_log('Session GC failed: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Close the current session
	 */
	public function close(): bool {
		return true;
	}

	/**
	 * Destroy a session
	 *
	 * @param string $sessionId The session id
	 */
	public function destroy(string $sessionId): bool {
		try {
			$sql = "DELETE FROM sessions WHERE session_id = ?";
			$this->db->execute($sql, [$sessionId]);
			return true;
		} catch (Exception $e) {
			error_log('Session destroy failed: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Read session data from database
	 *
	 * @param string $sessionId The session id
	 * @return string The serialized session data
	 */
	public function read(string $sessionId): string|false {
		try {
			$sql = "SELECT session_data FROM sessions WHERE session_id = ? LIMIT 1";
			$row = $this->db->fetch($sql, [$sessionId]);

			if ($row && isset($row['session_data'])) {
				// Update last activity time
				$updateSql = "UPDATE sessions SET last_activity = ? WHERE session_id = ?";
				$this->db->execute($updateSql, [time(), $sessionId]);

				return $row['session_data'];
			}

			return '';
		} catch (Exception $e) {
			error_log('Session read failed: ' . $e->getMessage());
			return '';
		}
	}

	/**
	 * Write session data to database
	 *
	 * @param string $sessionId   The session id
	 * @param string $sessionData The serialized session data
	 */
	public function write(string $sessionId, string $sessionData): bool {
		try {
			$now = time();

			// Try to update first
			$sql = "UPDATE sessions SET session_data = ?, last_activity = ? WHERE session_id = ?";
			$this->db->execute($sql, [$sessionData, $now, $sessionId]);

			// If no rows affected, insert new session
			$affected = $this->db->getPDO()->lastInsertId();
			if ($affected == 0) {
				$insertSql = "INSERT OR REPLACE INTO sessions (session_id, session_data, last_activity) VALUES (?, ?, ?)";
				$this->db->execute($insertSql, [$sessionId, $sessionData, $now]);
			}

			return true;
		} catch (Exception $e) {
			error_log('Session write failed: ' . $e->getMessage());
			return false;
		}
	}
}
