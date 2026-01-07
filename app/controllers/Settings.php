<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Settings Controller
 *
 * Handles settings page and related actions
 */

// Helper function for token validation
function validateToken($token) {
    return empty($token) || GitHubAPI::validateToken($token);
}

// Get current user
$user = User::getCurrentUser();
$userModel = new User();
$segment1 = $glob['route'][1] ?? '';

// Route: /settings/disconnect
if ($segment1 === 'disconnect') {
    if ($user) {
        $userModel->delete($user->id);
    }
    unset($_SESSION['user_id']);
    $_SESSION['success'] = 'Successfully disconnected from GitHub';
    redirect('');
    exit;
}

// Route: /settings/connect-readonly
if ($segment1 === 'connect-readonly' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['github_username'] ?? '';
    $token = $_POST['personal_access_token'] ?? '';

    if (empty($username)) {
        $_SESSION['error'] = 'GitHub username is required';
        redirect('settings');
        exit;
    }

    if (!validateToken($token)) {
        $_SESSION['error'] = 'Invalid Personal Access Token';
        redirect('settings');
        exit;
    }

    try {
        $newUser = $userModel->create([
            'github_username' => $username,
            'github_access_token' => $token ?: null,
            'access_mode' => 'readonly'
        ]);

        $_SESSION['user_id'] = $newUser->id;
        $_SESSION['success'] = 'Successfully connected in read-only mode!';
    } catch (Exception $e) {
        error_log('Connect readonly error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to connect. Please try again.';
    }

    redirect('settings');
    exit;
}

// Route: /settings/update-token
if ($segment1 === 'update-token' && $_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $token = $_POST['personal_access_token'] ?? '';

    if (!validateToken($token)) {
        $_SESSION['error'] = 'Invalid Personal Access Token';
        redirect('settings');
        exit;
    }

    try {
        $userModel->update($user->id, [
            'github_access_token' => $token ?: null
        ]);
        $_SESSION['success'] = 'Token updated successfully!';
    } catch (Exception $e) {
        error_log('Update token error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to update token';
    }

    redirect('settings');
    exit;
}

// Pass user data to view
$glob['user'] = $user ? $user->toArray() : null;

// Load view
if (isset($glob['view'])) {
    require_once($glob['view']);
}
