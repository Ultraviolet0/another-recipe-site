<?php
require_once('../../../private/initialize.php');
require_admin_login();

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
  redirect_to(url_for('/admin/users/index.php'));
}

$user = User::find_by_id($id);
/** @var User $user */

if (!$user) {
  redirect_to(url_for('/admin/users/index.php'));
}

$is_super_admin = $session->is_super_admin_logged_in();

if (!$is_super_admin && !User::is_member_only($user->id_usr)) {
  $session->message('You do not have permission to edit that user.');
  redirect_to(url_for('/admin/users/index.php'));
}

$selected_role_names = $user->get_role_names();
$available_role_names = $is_super_admin ? User::all_role_names() : ['member'];

if (is_post_request()) {
  $args = $_POST['user'] ?? [];
  $selected_role_names = $args['role_names'] ?? $selected_role_names;

  if (!$is_super_admin) {
    $selected_role_names = ['member'];
  }

  $user->merge_attributes($args);

  if ($user->save()) {
    if ($user->set_role_names($selected_role_names)) {
      $session->message('User updated successfully.');
      redirect_to(url_for('/admin/users/edit.php?id=' . u($user->id_usr)));
    } else {
      $user->errors[] = 'User was updated, but roles could not be synchronized.';
    }
  }
}

$page_title = 'Edit User';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper recipe-form">
  <h1>Edit User</h1>
  <p class="form-help">Fields marked with a * are required for new values when applicable.</p>
  <?php echo display_errors($user->errors); ?>

  <form action="<?php echo url_for('/admin/users/edit.php?id=' . u($user->id_usr)); ?>" method="post">
    <?php
    $form_mode = 'edit';
    include('form_fields.php');
    ?>

    <div>
      <button type="submit" class="button">Save Changes</button>
      <a class="button button-secondary" href="<?php echo url_for('/admin/users'); ?>">Discard Changes</a>
    </div>
  </form>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
