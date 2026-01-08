<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* GET CURRENT USER ---- */
$user = User::getCurrentUser();
$glob['user'] = $user ? $user->toArray() : null;

/* LOAD REPOSITORIES ---- */
$repoModel = new Repository();
$glob['repositories'] = $repoModel->findAll();
$glob['selected_repo'] = !empty($glob['repositories']) ? $glob['repositories'][0] : null;

/* LOAD ISSUES FOR SELECTED REPO ---- */
if ($glob['selected_repo']) {
	$issueModel = new Issue();
	$glob['issues'] = $issueModel->findByRepository($glob['selected_repo']['id']);
} else {
	$glob['issues'] = [];
}

/* LOAD ANALYSIS RESULTS ---- */
$glob['analysis'] = $_SESSION['analysis_results'] ?? null;

/* LOAD VIEW ---- */
if (isset($glob['view'])) {
	require_once($glob['view']);
}
