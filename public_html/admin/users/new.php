<?php
require_once('../../../private/initialize.php');
require_admin_login();

$is_super_admin = $session->is_super_admin_logged_in();

$user = new User();
$user->status_usr = 'pending';
$user->display_name_usr = '';
$user->location_usr = '';
$user->bio_usr = '';
/** @var User $user */

$selected_role_names = ['member'];
$available_role_names = $is_super_admin ? User::all_role_names() : ['member'];

if (is_post_request()) {
  $args = $_POST['user'] ?? [];
  $selected_role_names = $args['role_names'] ?? ['member'];

  if (!$is_super_admin) {
    $selected_role_names = ['member'];
  }

  $user = new User();
  $user->merge_attributes($args);

  if ($user->save()) {
    if ($user->set_role_names($selected_role_names)) {
      $session->message('User created successfully.');
      redirect_to(url_for('/admin/users/edit.php?id=' . u($user->id_usr)));
    } else {
      $user->errors[] = 'User was created, but roles could not be assigned.';
    }
  }
}

$page_title = 'Create User';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper recipe-form">
  <h1>Create User</h1>
  <p class="form-help">Fields marked with a * are required.</p>
  <?php echo display_errors($user->errors); ?>

  <form action="<?php echo url_for('/admin/users/new.php'); ?>" method="post">
    <?php
    $form_mode = 'new';
    include('form_fields.php');
    ?>

    <div>
      <button type="submit" class="button">Create User</button>
      <a class="button button-secondary" href="<?php echo url_for('/admin/users'); ?>">Cancel</a>
    </div>
  </form>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
