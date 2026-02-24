<?php
require_once('../private/initialize.php');

if ($session->is_logged_in()) {
  redirect_to(url_for('/'));
}

$errors = [];
$username = '';
$password = '';

if (is_post_request()) {

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Validations
  if (is_blank($username)) {
    $errors[] = "Username cannot be blank.";
  }
  if (is_blank($password)) {
    $errors[] = "Password cannot be blank.";
  }

  // if there were no errors, try to login
  if (empty($errors)) {
    $user = User::find_by_username($username);
    // test if user found and password is correct
    if ($user != false && $user->verify_password($password)) {
      // Mark user as logged in
      $session->login($user);
      redirect_to(url_for('/'));
    } else {
      if (!$user) {
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

<?php $page_title = 'Login'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<main role="main" tabindex="-1" id="main-content">
  <div class="wrapper container">
    <h2>Login</h2>
    <?php echo display_errors($errors); ?>
    <form action="login.php" method="post">
      Username:<br>
      <input type="text" name="username" value="<?php echo h($username); ?>"><br>
      Password:<br>
      <input type="password" name="password" value=""><br>
      <input type="submit" name="submit" value="Log In">
    </form>

    <span>New to AnotherRecipe.Site? <a href="<?php echo url_for('/signup.php'); ?>">Create an Account</a></span>
  </div>
</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
