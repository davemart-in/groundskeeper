<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Settings Controller
 *
 * Handles settings page and related actions
 */

// Get current user
$user = User::getCurrentUser();

// Check for disconnect action
$segment1 = isset($glob['route'][1]) ? $glob['route'][1] : '';

if ($segment1 === 'disconnect') {
    // Disconnect GitHub account
    if ($user) {
        $userModel = new User();
        $userModel->delete($user->id);
    }

    // Clear session
    unset($_SESSION['user_id']);

    // Success message
    $_SESSION['success'] = 'Successfully disconnected from GitHub';

    // Redirect to home
    redirect('');
    exit;
}

// Pass user data to view
$glob['user'] = $user ? $user->toArray() : null;

// Load view
if (isset($glob['view'])) {
    require_once($glob['view']);
}
