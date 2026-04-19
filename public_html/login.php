<?php
require_once('../private/initialize.php');

if ($session->is_logged_in()) {
  redirect_to(url_for('/'));
}

$errors = [];
$username = '';
$password = '';
$return_to = $_GET['return_to'] ?? '';

if (is_post_request()) {

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $return_to = $_POST['return_to'] ?? $return_to;
  $turnstile_token = $_POST['cf-turnstile-response'] ?? '';

  // Validations
  if (is_blank($username)) {
    $errors[] = "Username cannot be blank.";
  }
  if (is_blank($password)) {
    $errors[] = "Password cannot be blank.";
  }
  if (!verify_turnstile_token($turnstile_token, $_SERVER['REMOTE_ADDR'] ?? null)) {
    $errors[] = "Captcha verification failed.";
  }

  // if there were no errors, try to login
  if (empty($errors)) {
    $user = User::find_by_username($username);
    // test if user found and password is correct
    if ($user != false && $user->verify_password($password)) {
      // Mark user as logged in
      $session->login($user);
      $user->update_last_login();
      $session->message('Login successful.');
      if ($return_to && strpos($return_to, '/') === 0 && strpos($return_to, '//') !== 0) {
        redirect_to($return_to);
      } else {
        if ($session->is_admin_logged_in()) {
          redirect_to(url_for('/admin'));
        } else {
          redirect_to(url_for('/dashboard'));
        }
      }
    } else {
      $errors[] = "Invalid username or password.";
    }
  }
}

$page_title = 'Login';
$use_turnstile = true;
include(SHARED_PATH . '/public_header.php'); ?>

<div class="wrapper">
  <div class="container">
    <h1>Login</h1>
    <?php echo display_errors($errors); ?>
    <form action="<?php echo url_for('/login.php'); ?>" method="post">
      <label for="username">Username:</label><br>
      <input type="text" name="username" id="username" value="<?php echo h($username); ?>" required><br>
      <label for="password">Password:</label><br>
      <input type="password" name="password" id="password" value="" required><br>
      <div class="cf-turnstile" data-sitekey="<?php echo h($_ENV['TURNSTILE_SITE_KEY'] ?? ''); ?>"></div>
      <button type="submit" class="button">Log in</button>
      <input type="hidden" name="return_to" value="<?php echo h($return_to); ?>">
    </form>
    <span>New to anotherrecipe.site? <a href="<?php echo url_for('/signup.php'); ?>">Create an Account</a></span>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
