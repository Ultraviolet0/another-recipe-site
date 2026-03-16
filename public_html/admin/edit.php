<?php
require_once('../../private/initialize.php');
require_admin_login();

if (!isset($_GET['id'])) {
  redirect_to(url_for('/admin/index.php'));
}
$id = $_GET['id'];
$user = User::find_by_id($id);
if ($user == false) {
  redirect_to(url_for('/admin/index.php'));
}

if (is_post_request()) {

  // Save record using post parameters
  $args = $_POST['user'];
  $user->merge_attributes($args);
  $result = $user->save();

  if ($result === true) {
    $session->message('The user was updated successfully.');
    redirect_to(url_for('/admin/show.php?id=' . $id_usr));
  } else {
    // show errors
  }
} else {

  // display the form

}

$page_title = 'Edit User';
include(SHARED_PATH . '/public_header.php'); ?>

<div class="wrapper">
  <a class="back-link" href="<?php echo url_for('/admin/index.php'); ?>">&laquo; Back to List</a>
  <h2>Edit User</h2>

  <?php echo display_errors($user->errors); ?>

  <form action="<?php echo url_for('/admin/edit.php?id=' . h(u($id_usr))); ?>" method="post">

    <?php include('form_fields.php'); ?>

    <div id="operations">
      <button type="submit" class="button">Edit User</button>
      <a class="button button-secondary" href="<?php echo url_for('/admin/index.php'); ?>">Discard Edit</a>
    </div>
  </form>

</div>


<?php include(SHARED_PATH . '/public_footer.php'); ?>
