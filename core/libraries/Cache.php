<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Cache utility functions for Redis operations
 * Implements the caching strategy outlined in the database optimization plan
 */

/**
 * Get cached data or execute query callback and cache the result
 * @param string $key Redis cache key
 * @param int $ttl Time to live in seconds
 * @param callable $queryCallback Function to execute if cache miss
 * @return mixed Cached data or query result
 */
function cache_get_or_query($key, $ttl, $queryCallback) {
	global $redis;
	
	// Try to get from cache first
	$cached = $redis->get($key);
	if ($cached !== false && $cached !== null) {
		// Cache hit - return decoded data
		return json_decode($cached, true);
	}
	
	// Cache miss - execute query callback
	$result = $queryCallback();
	
	// Cache the result
	if ($result !== false && $result !== null) {
		$redis->setex($key, $ttl, json_encode($result));
	}
	
	return $result;
}

/**
 * Invalidate cache by key
 * @param string $key Redis cache key to invalidate
 * @return bool True if key was deleted, false otherwise
 */
function cache_invalidate($key) {
	global $redis;
	return $redis->del($key) > 0;
}

/**
 * Invalidate multiple cache keys
 * @param array $keys Array of Redis cache keys to invalidate
 * @return int Number of keys deleted
 */
function cache_invalidate_multiple($keys) {
	global $redis;
	if (empty($keys)) {
		return 0;
	}
	return $redis->del($keys);
}

/**
 * Set cache with TTL
 * @param string $key Redis cache key
 * @param mixed $value Value to cache
 * @param int $ttl Time to live in seconds
 * @return bool True if successful
 */
function cache_set($key, $value, $ttl) {
	global $redis;
	return $redis->setex($key, $ttl, json_encode($value));
}

/**
 * Get cache value
 * @param string $key Redis cache key
 * @return mixed Cached value or false if not found
 */
function cache_get($key) {
	global $redis;
	$cached = $redis->get($key);
	if ($cached !== false && $cached !== null) {
		return json_decode($cached, true);
	}
	return false;
}

/**
 * Check if cache key exists
 * @param string $key Redis cache key
 * @return bool True if key exists
 */
function cache_exists($key) {
	global $redis;
	return $redis->exists($key);
}

/**
 * Build cache key for company-specific data
 * @param int $company_id Company ID
 * @param string $type Data type (e.g., 'embed_settings', 'products', 'teams')
 * @return string Formatted cache key
 */
function cache_build_company_key($company_id, $type) {
	return 'company:' . $company_id . ':' . $type;
}

/**
 * Build cache key for user-specific data
 * @param int $user_id User ID
 * @param string $type Data type (e.g., 'data', 'permissions')
 * @return string Formatted cache key
 */
function cache_build_user_key($user_id, $type) {
	return 'user:' . $user_id . ':' . $type;
}

/**
 * Build cache key for project-specific data
 * @param int $project_id Project ID
 * @param string $type Data type (e.g., 'details', 'suggestions:monthly_count')
 * @return string Formatted cache key
 */
function cache_build_project_key($project_id, $type) {
	return 'project:' . $project_id . ':' . $type;
}

/**
 * Build cache key for role-specific data
 * @param int $role_id Role ID
 * @param string $type Data type (e.g., 'permissions')
 * @return string Formatted cache key
 */
function cache_build_role_key($role_id, $type) {
	return 'role:' . $role_id . ':' . $type;
} 