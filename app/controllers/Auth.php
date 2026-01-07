<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Auth Controller
 *
 * Handles GitHub OAuth authentication flow
 */

// Check the route segments
$segment1 = isset($glob['route'][1]) ? $glob['route'][1] : '';
$segment2 = isset($glob['route'][2]) ? $glob['route'][2] : '';

// Route: /auth/github - Initiate OAuth flow
if ($segment1 === 'github' && $segment2 === '') {
    try {
        $oauth = new GitHubOAuth();

        // Generate and store CSRF state token
        $state = GitHubOAuth::generateState();
        $_SESSION['oauth_state'] = $state;

        // Redirect to GitHub authorization
        $authUrl = $oauth->getAuthorizationUrl($state);
        header('Location: ' . $authUrl);
        exit;
    } catch (Exception $e) {
        error_log('Auth Error: ' . $e->getMessage());
        $_SESSION['error'] = 'GitHub authentication is not configured. Please check your settings.';
        redirect('');
        exit;
    }
}

// Route: /auth/github/callback - Handle OAuth callback
if ($segment1 === 'github' && $segment2 === 'callback') {
    // Check for errors from GitHub
    if (isset($_GET['error'])) {
        $_SESSION['error'] = 'GitHub authorization failed: ' . $_GET['error'];
        redirect('');
        exit;
    }

    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;

    if (!$code || !$state) {
        $_SESSION['error'] = 'Invalid OAuth callback parameters';
        redirect('');
        exit;
    }

    try {
        $oauth = new GitHubOAuth();

        // Verify CSRF state token
        if (!$oauth->verifyState($state)) {
            $_SESSION['error'] = 'Invalid OAuth state token. Please try again.';
            redirect('');
            exit;
        }

        // Exchange code for access token
        $accessToken = $oauth->getAccessToken($code);
        if (!$accessToken) {
            throw new Exception('Failed to obtain access token');
        }

        // Get user info from GitHub
        $userInfo = $oauth->getUserInfo($accessToken);
        if (!$userInfo) {
            throw new Exception('Failed to get user information');
        }

        // Create or update user
        $userModel = new User();
        $existingUser = $userModel->findByGitHubId($userInfo['github_id']);

        $userData = [
            'github_username' => $userInfo['github_username'],
            'github_access_token' => $accessToken,
            'avatar_url' => $userInfo['avatar_url'],
            'access_mode' => 'readwrite'
        ];

        if ($existingUser) {
            $userModel->update($existingUser->id, $userData);
            $userId = $existingUser->id;
        } else {
            $userData['github_id'] = $userInfo['github_id'];
            $newUser = $userModel->create($userData);
            $userId = $newUser->id;
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['success'] = 'Successfully connected to GitHub!';
        redirect('settings');
        exit;

    } catch (Exception $e) {
        error_log('OAuth Callback Error: ' . $e->getMessage());
        $_SESSION['error'] = 'An error occurred during authentication. Please try again.';
        redirect('');
        exit;
    }
}

// Default: redirect to home
redirect('');
exit;
