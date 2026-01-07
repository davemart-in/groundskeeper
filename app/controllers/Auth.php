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
    try {
        // Check for errors from GitHub
        if (isset($_GET['error'])) {
            $_SESSION['error'] = 'GitHub authorization failed: ' . $_GET['error'];
            redirect('');
            exit;
        }

        // Verify we have a code
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            $_SESSION['error'] = 'Invalid OAuth callback parameters';
            redirect('');
            exit;
        }

        $code = $_GET['code'];
        $state = $_GET['state'];

        // Initialize OAuth
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
            $_SESSION['error'] = 'Failed to obtain access token from GitHub';
            redirect('');
            exit;
        }

        // Get user info from GitHub
        $userInfo = $oauth->getUserInfo($accessToken);

        if (!$userInfo) {
            $_SESSION['error'] = 'Failed to get user information from GitHub';
            redirect('');
            exit;
        }

        // Check if user exists
        $userModel = new User();
        $user = $userModel->findByGitHubId($userInfo['github_id']);

        if ($user) {
            // Update existing user
            $userModel->update($user->id, [
                'github_username' => $userInfo['github_username'],
                'github_access_token' => $accessToken,
                'avatar_url' => $userInfo['avatar_url']
            ]);

            $userId = $user->id;
        } else {
            // Create new user
            $userInfo['github_access_token'] = $accessToken;
            $newUser = $userModel->create($userInfo);
            $userId = $newUser->id;
        }

        // Store user ID in session
        $_SESSION['user_id'] = $userId;

        // Success message
        $_SESSION['success'] = 'Successfully connected to GitHub!';

        // Redirect to settings
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
