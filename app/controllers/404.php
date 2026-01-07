<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* NONCE ------------------------------------------- */
$nonce = csrf_set();

if (isset($glob['view'])) {
	require_once($glob['view']);
}