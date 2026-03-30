<?php

function require_login()
{
  global $session;
  if (!$session->is_logged_in()) {
    redirect_to(url_for('/login.php'));
  }
}

function require_admin_login()
{
  global $session;
  if (!$session->is_admin_logged_in()) {
    redirect_to(url_for('/'));
  }
}

function display_errors($errors = array())
{
  $output = '';
  if (!empty($errors)) {
    $output .= '<div class="errors">';
    $output .= '<p><strong>Please fix the following errors:</strong></p>';
    $output .= '<ul>';
    foreach ($errors as $error) {
      $output .= '<li>' . h($error) . '</li>';
    }
    $output .= '</ul>';
    $output .= '</div>';
  }
  return $output;
}

function display_session_message()
{
  global $session;
  $msg = $session->message();
  if (isset($msg) && $msg != '') {
    $session->clear_message();
    return '
    <div class="flash-toast" role="status" aria-live="polite">
      <div class="flash-toast-inner">
        <p>' . h($msg) . '</p>
        <button type="button" class="flash-toast-close" aria-label="Dismiss message">&times;</button>
      </div>
    </div>';
  }
}
