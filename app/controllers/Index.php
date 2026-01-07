<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* GET CURRENT USER ---- */
$user = User::getCurrentUser();
$glob['user'] = $user ? $user->toArray() : null;

/* LOAD VIEW ---- */
if (isset($glob['view'])) {
	require_once($glob['view']);
}
