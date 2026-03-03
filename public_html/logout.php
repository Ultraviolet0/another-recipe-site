<?php
require_once('../private/initialize.php');

// Log out the member
$session->logout();
$session->message('Logout successful.');
redirect_to(url_for('/'));
