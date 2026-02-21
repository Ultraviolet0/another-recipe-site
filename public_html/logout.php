<?php
require_once('../private/initialize.php');

// Log out the member
$session->logout();

redirect_to(url_for('/index.php'));

?>
