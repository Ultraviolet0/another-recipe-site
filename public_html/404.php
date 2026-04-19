<?php require_once(__DIR__ . '/../private/initialize.php');

http_response_code(404);
$page_title = '404 - Page Not Found';
include(SHARED_PATH . '/public_header.php');
include(SHARED_PATH . '/404_content.php');
include(SHARED_PATH . '/public_footer.php');
?>
