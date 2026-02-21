<?php

require_once('../private/initialize.php');

if($session->is_logged_in()) {
  redirect_to(url_for('index.php'));
}

if(is_post_request()) {

  // Create record using post parameters
  $args = $_POST['user'];
  $user = new User($args);
  $result = $user->save();

  if($result === true) {
    $new_id = $user->id_usr;
    // Mark user as logged in
    $session->login($user);
    redirect_to(url_for('index.php'));
    $session->message('You signed up successfully.');
  } else { 
    // show errors
  }

} else {
  // display the form
  $user = new User;
}

?>

<?php $page_title = 'New User Signup'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<main id="main-content">

  <a class="back-link" href="<?php echo url_for('/index.php'); ?>">&laquo; Back to Menu</a>

  <div class="user new">
    <h1>New User Signup</h1>

    <?php echo display_errors($user->errors); ?>

    <form action="<?php echo url_for('signup.php'); ?>" method="post">

      <?php include('users/form_fields.php'); ?>

      <div id="operations">
        <input type="submit" value="Sign Up">
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
