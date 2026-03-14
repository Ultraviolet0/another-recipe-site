<?php
require_once('../../private/initialize.php');
require_login();

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
  redirect_to(url_for('/recipes'));
}

$recipe = Recipe::find_by_id($id);
/** @var Recipe $recipe */

if (!$recipe) {
  redirect_to(url_for('/recipes'));
}

if (!$recipe->can_edit($session)) {
  $session->message('You do not have permission to delete that recipe.');
  redirect_to(url_for('/recipes/show.php?id=' . u($id)));
}

if (is_post_request()) {

  $confirm = $_POST['confirm'] ?? '';

  if ($confirm === 'yes') {

    $title = $recipe->title_rcp;

    $ok = $recipe->delete();

    if ($ok) {
      $session->message("Recipe \"{$title}\" deleted.");
      redirect_to(url_for('/recipes'));
    } else {
      $session->message("Delete failed.");
      redirect_to(url_for('/recipes/show.php?id=' . u($id)));
    }
  } else {
    redirect_to(url_for('/recipes/show.php?id=' . u($id)));
  }
}

$page_title = 'Delete Recipe';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="container">

    <h2>Delete Recipe</h2>

    <p>Are you sure you want to delete this recipe?</p>

    <p><strong><?php echo h($recipe->title_rcp); ?></strong></p>

    <form action="<?php echo url_for('/recipes/delete.php?id=' . u($id)); ?>" method="post">

      <button type="submit" name="confirm" value="yes" class="button button-danger">
        Yes, Delete Recipe
      </button>

      <button type="submit" name="confirm" value="no" class="button button-secondary">
        Cancel
      </button>

    </form>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
