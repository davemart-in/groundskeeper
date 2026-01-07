<?php if (!defined('COREPATH')) exit('No direct script access allowed');

// MIT license
// Author: Timothy Boronczyk
// https://github.com/phpmasterdotcom/SavingPHPSessionsInRedis/blob/master/include/RedisSessionHandler.php

use Predis\Client;

class RedisSession implements SessionHandlerInterface {
	private $redis;
	private $keyPrefix;
	private $maxLifetime;

	/**
	 * Let's get this redis party started.
	 * 
	 * @param Predis\Client $redis     The Predis Client
	 * @param string        $keyPrefix The Redis Key prefix
	 */
	public function __construct(Client $redis, $keyPrefix = 'session:') {
		$this->redis = $redis;
		$this->keyPrefix = $keyPrefix;

		// Set timeout to 30 days
		ini_set('session.gc_maxlifetime', 60*60*24*30);
		$this->maxLifetime = ini_get('session.gc_maxlifetime');
	}
 
	/**
	 * We don't need to do anything extra to initialize the session since
	 * we get the Redis connection in the constructor.
	 *
	 * @param  string $savePath The path where to storethe session.
	 * @param  string $name     The session name.
	 */
	public function open(string $savePath, string $name): bool { 
		return true;
	}

	/**
	 * Since we use Redis EXPIRES, we don't need to do any special garbage
	 * collecting.
	 *
	 * @param  string $maxLifetime The max lifetime of a session.
	 */
	public function gc(int $maxLifetime): int|false { 
		return true;
	}
 
	/**
	 * Close the current session by disconnecting from Redis.
	 */
	public function close(): bool {
		// This will force Predis to disconnect.
		unset($this->redis);
		return true;
	}
 
	/**
	 * Destroys the session by deleting the key from Redis.
	 * 
	 * @param  string $sessionId The session id.
	 */
	public function destroy(string $sessionId): bool {
		$this->redis->del($this->keyPrefix.$sessionId);
		return true;
	}

	/**
	 * Impersonate a user.
	 * 
	 * @param string $userId The ID of the user to impersonate.
	 * @param string $username The username of the user to impersonate.
	 */
	// public function impersonateUser(string $userId, string $username): bool {
	// 	// Clear current session data
	// 	session_unset();

	// 	// Set session variables to impersonated user
	// 	$_SESSION['uid'] = (int)$userId;
	// 	$_SESSION['username'] = (string)$username;

	// 	// Set an impersonation flag
	// 	$_SESSION['isImpersonating'] = true;

	// 	return true;
	// }

	/**
	 * Read the session data from Redis.
	 * 
	 * @param  string $sessionId The session id.
	 * @return string            The serialized session data.
	 */
	public function read(string $sessionId): string|false {
		$sessionId = $this->keyPrefix.$sessionId;
		$sessionData = $this->redis->get($sessionId);

		// Refresh the Expire
		$this->redis->expire($sessionId, $this->maxLifetime);
		return (string)$sessionData;
	}
 
	/**
	 * Write the serialized session data to Redis. This also sets
	 * the Redis key EXPIRES time so we don't have to rely on the
	 * PHP gc.
	 * 
	 * @param  string $sessionId   The session id.
	 * @param  string $sessionData The serialized session data.
	 */
	public function write(string $sessionId, string $sessionData): bool {
		$sessionId = $this->keyPrefix.$sessionId;

		// Write the session data to Redis.
		$this->redis->set($sessionId, $sessionData);

		// Set the expire so we don't have to rely on PHP's gc.
		$this->redis->expire($sessionId, $this->maxLifetime);

		return true;
	}

}