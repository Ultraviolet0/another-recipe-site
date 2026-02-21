<?php
require_once('../../private/initialize.php');
require_admin_login();

if(is_post_request()) {

  // Create record using post parameters
  $args = $_POST['user'];
  $user = new User($args);
  $result = $user->save();

  if($result === true) {
    $new_id = $user->id_usr;
    $session->message('The user was created successfully.');
    redirect_to(url_for('/users/show.php?id=' . $new_id));
  } else { 
    // show errors
  }

} else {
  // display the form
  $user = new User;
}

?>

<?php $page_title = 'Create User'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<main id="main-content">

  <a class="back-link" href="<?php echo url_for('/users/index.php'); ?>">&laquo; Back to List</a>

  <div class="user new">
    <h1>Create User</h1>

    <?php echo display_errors($user->errors); ?>

    <form action="<?php echo url_for('/users/new.php'); ?>" method="post">

      <?php include('form_fields.php'); ?>

      <div id="operations">
        <input type="submit" value="Create User">
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
