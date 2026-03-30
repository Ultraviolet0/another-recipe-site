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
  $session->message('You do not have permission to edit that recipe.');
  redirect_to(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)));
}

// On first GET, preload draft from DB if there is no active draft
$draft_recipe_id = $_SESSION['recipe_draft_recipe_id'] ?? null;
$draft_mode = $_SESSION['recipe_draft_mode'] ?? null;

if (
  !isset($_SESSION['recipe_draft']) ||
  $draft_mode !== 'edit' ||
  (string)$draft_recipe_id !== (string)$recipe->id_rcp ||
  ($_GET['reset'] ?? '') === '1'
) {
  recipe_load_edit_draft($recipe);
}

$draft = recipe_get_draft();

if (is_post_request()) {
  $action = $_POST['action'] ?? '';

  if ($action === 'discard_draft') {
    recipe_clear_draft();
    recipe_load_edit_draft($recipe);
    $session->message('Recipe changes discarded.');
    redirect_to(url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)));
  }

  $draft = recipe_merge_post_into_draft($draft, $_POST);

  if ($action === 'add_ingredient') {
    $draft = recipe_add_ingredient_row($draft);
    recipe_save_draft($draft);
    redirect_to(url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)));
  }

  if ($action === 'add_direction') {
    $draft = recipe_add_direction_row($draft);
    recipe_save_draft($draft);
    redirect_to(url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)));
  }

  if ($action === 'save_recipe') {
    $recipe_args = $draft['recipe'] ?? [];
    $recipe_args['id_rcp'] = $recipe->id_rcp;
    $recipe_args['id_usr_rcp'] = $recipe->id_usr_rcp; // preserve owner

    $recipe = new Recipe($recipe_args);
    $recipe->id_rcp = $id;

    $ok = $recipe->update_with_children(
      $draft['ingredients'] ?? [],
      $draft['directions'] ?? [],
      $_FILES['photos'] ?? null,
      PUBLIC_PATH,
      $draft['meal_types'] ?? [],
      $draft['cuisines'] ?? [],
      $draft['dietary_styles'] ?? []
    );

    if ($ok) {
      recipe_clear_draft();
      $session->message('Recipe updated successfully.');
      redirect_to(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)));
    } else {
      $draft['errors'] = $recipe->errors;
      recipe_save_draft($draft);
      redirect_to(url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)));
    }
  }
}

$draft = recipe_get_draft();
$recipe_for_errors = new Recipe($draft['recipe'] ?? []);
$recipe_for_errors->errors = $draft['errors'] ?? [];

$page_title = 'Edit Recipe';
include(SHARED_PATH . '/public_header.php');
?>

  <div class="recipe-form">
    <h1>Edit Recipe</h1>
    <p class="form-help">Fields marked with a * are required.</p>

    <?php echo display_errors($recipe_for_errors->errors); ?>

    <form action="<?php echo url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)); ?>" method="post" enctype="multipart/form-data">
      <?php include('form_fields.php'); ?>

      <div>
        <button type="submit" class="button" name="action" value="save_recipe">Save Changes</button>
        <button type="submit" class="button button-secondary" name="action" value="discard_draft" formnovalidate>Discard Changes</button>
      </div>
    </form>
  </div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
