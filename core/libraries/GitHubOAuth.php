<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * GitHub OAuth Library
 *
 * Handles GitHub OAuth 2.0 authentication flow
 */
class GitHubOAuth {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;

    /**
     * Constructor
     *
     * @throws Exception if required environment variables are not set
     */
    public function __construct() {
        $this->clientId = getenv('GITHUB_CLIENT_ID');
        $this->clientSecret = getenv('GITHUB_CLIENT_SECRET');

        if (empty($this->clientId) || empty($this->clientSecret)) {
            error_log('GitHub OAuth Error: GITHUB_CLIENT_ID or GITHUB_CLIENT_SECRET not set');
            throw new Exception('GitHub OAuth not configured');
        }

        $this->redirectUri = BASEURL . 'auth/github/callback';
        $this->scope = 'repo'; // Full access to public and private repos
    }

    /**
     * Get GitHub authorization URL
     *
     * @param string $state CSRF state token
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($state) {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'state' => $state
        ];

        return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code Authorization code from GitHub
     * @return string|false Access token or false on failure
     */
    public function getAccessToken($code) {
        $url = 'https://github.com/login/oauth/access_token';

        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Groundskeeper'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('GitHub OAuth: Failed to get access token. HTTP Code: ' . $httpCode);
            return false;
        }

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            return $data['access_token'];
        }

        error_log('GitHub OAuth: No access token in response: ' . $response);
        return false;
    }

    /**
     * Get GitHub user information
     *
     * @param string $accessToken GitHub access token
     * @return array|false User data or false on failure
     */
    public function getUserInfo($accessToken) {
        $url = 'https://api.github.com/user';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
            'User-Agent: Groundskeeper'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('GitHub OAuth: Failed to get user info. HTTP Code: ' . $httpCode);
            return false;
        }

        $userData = json_decode($response, true);

        if (!isset($userData['id'])) {
            error_log('GitHub OAuth: Invalid user data received');
            return false;
        }

        return [
            'github_id' => $userData['id'],
            'github_username' => $userData['login'],
            'avatar_url' => $userData['avatar_url'] ?? null,
            'name' => $userData['name'] ?? null,
            'email' => $userData['email'] ?? null
        ];
    }

    /**
     * Verify the CSRF state token
     *
     * @param string $state State from callback
     * @return bool True if valid, false otherwise
     */
    public function verifyState($state) {
        if (!isset($_SESSION['oauth_state'])) {
            return false;
        }

        $valid = hash_equals($_SESSION['oauth_state'], $state);

        // Clear the state from session
        unset($_SESSION['oauth_state']);

        return $valid;
    }

    /**
     * Generate a random state token for CSRF protection
     *
     * @return string Random state token
     */
    public static function generateState() {
        return bin2hex(random_bytes(32));
    }
}
