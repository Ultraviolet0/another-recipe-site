<?php
require_once('../../../private/initialize.php');

if (!$session->is_super_admin_logged_in()) {
  $session->message('You do not have permission to delete users.');
  redirect_to(url_for('/admin/users/index.php'));
}

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
  redirect_to(url_for('/admin/users/index.php'));
}

$user = User::find_by_id($id);
/** @var User $user */

if (!$user) {
  redirect_to(url_for('/admin/users/index.php'));
}

$recipe_count = User::count_recipes_by_user_id($user->id_usr);
$is_self_delete = ((int)$session->get_user_id() === (int)$user->id_usr);

if (is_post_request()) {
  $confirm = $_POST['confirm'] ?? '';
  $confirm_recipe_delete = $_POST['confirm_recipe_delete'] ?? '';

  if ($confirm === 'yes') {
    if ($recipe_count > 0 && $confirm_recipe_delete !== 'yes') {
      $session->message('Please confirm that the user recipes should also be deleted.');
      redirect_to(url_for('/admin/users/delete.php?id=' . u($user->id_usr)));
    }

    $username = $user->username_usr;
    $ok = $user->delete_with_recipes();

    if ($ok) {
      if ($is_self_delete) {
        $session->logout();
        redirect_to(url_for('/'));
      }

      $session->message("User \"{$username}\" deleted.");
      redirect_to(url_for('/admin/users/index.php'));
    } else {
      $session->message('Delete failed.');
      redirect_to(url_for('/admin/users/edit.php?id=' . u($user->id_usr)));
    }
  } else {
    redirect_to(url_for('/admin/users/index.php'));
  }
}

$page_title = 'Delete User';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="container">
    <h1>Delete User</h1>
    <p>Are you sure you want to delete this user?</p>
    <p><strong><?php echo h($user->username_usr); ?></strong></p>

    <?php if ($recipe_count > 0) { ?>
      <p><strong>Warning:</strong> This user has <?php echo h($recipe_count); ?> recipe<?php echo $recipe_count === 1 ? '' : 's'; ?>.</p>
      <p>Deleting this account will also permanently delete those recipes. This cannot be undone. Consider disabling the user instead if you want the recipes to remain on the site.</p>
    <?php } ?>

    <form action="<?php echo url_for('/admin/users/delete.php?id=' . u($user->id_usr)); ?>" method="post">
      <?php if ($recipe_count > 0) { ?>
        <label for="confirm_recipe_delete">
          <input type="checkbox" id="confirm_recipe_delete" name="confirm_recipe_delete" value="yes">
          I understand this will also permanently delete this user’s recipes.
        </label><br><br>
      <?php } ?>

      <button type="submit" name="confirm" value="yes" class="button button-danger">Yes, Delete User</button>
      <button type="submit" name="confirm" value="no" class="button button-secondary">Cancel</button>
    </form>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
