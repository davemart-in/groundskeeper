<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/* AUTH ---- */
// End user session
auth_end_session();

/* REDIRECT ---- */
return redirect('/', 'You have been signed out successfully.');

