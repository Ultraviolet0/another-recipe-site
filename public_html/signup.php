<?php

require_once('../private/initialize.php');

if ($session->is_logged_in()) {
  redirect_to(url_for('/'));
}

if (is_post_request()) {

  // Create record using post parameters
  $args = $_POST['user'] ?? [];

  // defend against user injected roles
  unset($args['roles']);

  // auto activate new users
  $args['status_usr'] = 'active';

  $user = new User($args);
  $result = $user->save();

  if ($result === true) {
    $new_id = $user->id_usr;

    // auto set member role
    // $user->set_role_names('member');

    // Mark user as logged in
    $session->login($user);
    $session->message('You signed up successfully.');
    redirect_to(url_for('/'));
  } else {
    // show errors
  }
} else {
  // display the form
  $user = new User;
}

?>

<?php $page_title = 'Signup'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<main role="main" tabindex="-1" id="main-content">
  <div class="wrapper">
    <div class="container">
      <h2>Signup</h2>
      <?php echo display_errors($user->errors); ?>
      <form action="<?php echo url_for('/signup.php'); ?>" method="post">
        <?php include('admin/form_fields.php'); ?>
        <input type="submit" value="Sign Up">
      </form>
      <span>Already have an account? <a href="<?php echo url_for('/login.php'); ?>">Login</a></span>
    </div>
  </div>

</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
