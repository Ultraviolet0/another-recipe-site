<?php
require_once('../private/initialize.php');

if($session->is_logged_in()) {
  redirect_to(url_for('index.php'));
}

$errors = [];
$username = '';
$password = '';

if(is_post_request()) {

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Validations
  if(is_blank($username)) {
    $errors[] = "Username cannot be blank.";
  }
  if(is_blank($password)) {
    $errors[] = "Password cannot be blank.";
  }

  // if there were no errors, try to login
  if(empty($errors)) {
    $member = Member::find_by_username($username);
    // test if member found and password is correct
    if($member != false && $member->verify_password($password)) {
      // Mark member as logged in
      $session->login($member);
      redirect_to(url_for('/index.php'));
    } else {
      if(!$member) {
        // username not found
        $errors[] = "Username not found.";
      } else {
        // password does not match
        $errors[] = "Password does not match.";
      }
    }

  }

}

?>

<?php $page_title = 'Log in'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<main id="content">
  <h1>Log in</h1>

  <?php echo display_errors($errors); ?>

  <form action="login.php" method="post">
    Username:<br />
    <input type="text" name="username" value="<?php echo h($username); ?>" /><br />
    Password:<br />
    <input type="password" name="password" value="" /><br />
    <input type="submit" name="submit" value="Submit"  />
  </form>

</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
