<?php

require_once('../private/initialize.php');

if ($session->is_logged_in()) {
  redirect_to(url_for('/'));
}

if (is_post_request()) {

  // Create record using post parameters
  $args = $_POST['user'] ?? [];
  $turnstile_token = $_POST['cf-turnstile-response'] ?? '';

  // defend against user injected roles
  unset($args['roles']);

  // auto activate new users
  $args['status_usr'] = 'active';

  $user = new User($args);

  if (!verify_turnstile_token($turnstile_token, $_SERVER['REMOTE_ADDR'] ?? null)) {
    $user->errors[] = "Captcha verification failed.";
  } else {
    $result = $user->save();

    if ($result === true) {
      $new_id = $user->id_usr;

      // auto set member role
      // $user->set_role_names('member');

      // Mark user as logged in
      $session->login($user);
      $user->update_last_login();
      $session->message('Signup successful.');
      redirect_to(url_for('/'));
    } else {
      // show errors
    }
  }
} else {
  // display the form
  $user = new User;
}

$page_title = 'Signup';
$use_turnstile = true;
include(SHARED_PATH . '/public_header.php'); ?>

<div class="wrapper">
  <div class="container">
    <noscript><h1>Signing up on anotherrecipe.site requires Javascript to be enabled.</h1></noscript>
    <section id="signup-page">
      <h1>Signup</h1>
      <?php echo display_errors($user->errors); ?>
      <form action="<?php echo url_for('/signup.php'); ?>" method="post">
        <label for="email">Email</label><br>
        <input type="text" id="email" name="user[email_usr]" value="<?php echo h($user->email_usr); ?>"><br>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="user[username_usr]" value="<?php echo h($user->username_usr); ?>"><br>
        <label for="password">Password</label><br>
        <input type="password" id="password" name="user[password]" value=""><br>
        <p class="form-help">Password must contain at least 8 characters, including an uppercase letter, lowercase letter, and a number or symbol.</p>
        <label for="confirm-password">Confirm Password</label><br>
        <input type="password" id="confirm-password" name="user[confirm_password]" value=""><br>
        <div class="cf-turnstile" data-sitekey="<?php echo h($_ENV['TURNSTILE_SITE_KEY'] ?? ''); ?>"></div>
        <button type="submit" class="button">Sign Up</button>
      </form>
      <span>Already have an account? <a href="<?php echo url_for('/login.php'); ?>">Login</a></span>
    </section>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
