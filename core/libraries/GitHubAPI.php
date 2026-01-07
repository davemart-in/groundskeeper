<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * GitHub API Library
 *
 * Handles read-only GitHub API access (public API or Personal Access Token)
 */
class GitHubAPI {
    private $accessToken;
    private $baseUrl = 'https://api.github.com';

    /**
     * Constructor
     *
     * @param string|null $accessToken GitHub Personal Access Token (optional)
     */
    public function __construct($accessToken = null) {
        $this->accessToken = $accessToken;
    }

    /**
     * Make a GET request to GitHub API
     *
     * @param string $endpoint API endpoint (e.g., '/repos/owner/repo/issues')
     * @param array $params Query parameters
     * @return array|false Response data or false on failure
     */
    public function get($endpoint, $params = []) {
        $url = $this->baseUrl . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = [
            'Accept: application/vnd.github+json',
            'User-Agent: Groundskeeper',
            'X-GitHub-Api-Version: 2022-11-28'
        ];

        if ($this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // Get response headers for pagination
        curl_setopt($ch, CURLOPT_HEADER, true);
        $fullResponse = curl_exec($ch);
        $headerString = substr($fullResponse, 0, $headerSize);

        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('GitHub API Error: HTTP ' . $httpCode . ' - ' . $response);
            return false;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('GitHub API: Invalid JSON response');
            return false;
        }

        return $data;
    }

    /**
     * Fetch all issues from a repository (handles pagination)
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param array $filters Filters (state, labels, etc.)
     * @return array Array of issues
     */
    public function getIssues($owner, $repo, $filters = []) {
        $issues = [];
        $page = 1;
        $perPage = 100; // Max allowed by GitHub

        $params = array_merge([
            'state' => 'open',
            'per_page' => $perPage,
            'sort' => 'created',
            'direction' => 'desc'
        ], $filters);

        do {
            $params['page'] = $page;
            $endpoint = "/repos/{$owner}/{$repo}/issues";

            $pageIssues = $this->get($endpoint, $params);

            if ($pageIssues === false) {
                error_log("Failed to fetch issues page {$page} for {$owner}/{$repo}");
                break;
            }

            // Filter out pull requests (GitHub API returns PRs as issues)
            $pageIssues = array_filter($pageIssues, function($issue) {
                return !isset($issue['pull_request']);
            });

            $issues = array_merge($issues, $pageIssues);

            // If we got less than perPage, we're done
            if (count($pageIssues) < $perPage) {
                break;
            }

            $page++;

            // Safety limit to prevent infinite loops
            if ($page > 100) {
                error_log("GitHub API: Hit page limit for {$owner}/{$repo}");
                break;
            }

        } while (true);

        return $issues;
    }

    /**
     * Get repository information
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return array|false Repository data or false on failure
     */
    public function getRepository($owner, $repo) {
        return $this->get("/repos/{$owner}/{$repo}");
    }

    /**
     * Get authenticated user (requires token)
     *
     * @return array|false User data or false on failure
     */
    public function getAuthenticatedUser() {
        if (!$this->accessToken) {
            return false;
        }

        return $this->get('/user');
    }

    /**
     * Get rate limit status
     *
     * @return array|false Rate limit data or false on failure
     */
    public function getRateLimit() {
        return $this->get('/rate_limit');
    }

    /**
     * Check if a repository is accessible
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return bool True if accessible, false otherwise
     */
    public function isRepositoryAccessible($owner, $repo) {
        $repoData = $this->getRepository($owner, $repo);
        return $repoData !== false;
    }

    /**
     * Validate a Personal Access Token
     *
     * @param string $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token) {
        $api = new self($token);
        $user = $api->getAuthenticatedUser();
        return $user !== false;
    }
}
