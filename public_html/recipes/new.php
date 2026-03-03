<?php
require_once('../../private/initialize.php');
require_login();

$draft = recipe_get_draft();

// On GET we create a Recipe object purely so display_errors() has something familiar
$recipe = new Recipe($draft['recipe'] ?? []);
$recipe->errors = $draft['errors'] ?? [];

if (is_post_request()) {

  // Merge submitted values into draft first so no data is lost
  $draft = recipe_merge_post_into_draft($draft, $_POST);

  $action = $_POST['action'] ?? '';

  if ($action === 'add_ingredient') {
    $draft = recipe_add_ingredient_row($draft);
  } elseif ($action === 'add_direction') {
    $draft = recipe_add_direction_row($draft);
  } elseif ($action === 'save_recipe') {

    $recipe_args = $draft['recipe'] ?? [];
    $recipe_args['id_usr_rcp'] = $session->get_user_id();

    $recipe = new Recipe($recipe_args);

    $upload_root_public = PUBLIC_PATH;

    $ok = $recipe->save_with_children(
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
      $session->message('Recipe created successfully.');
      redirect_to(url_for('/recipes/show.php?id=' . h(u($recipe->id_rcp))));
    } else {
      $draft['errors'] = $recipe->errors;
    }
  } elseif ($action === 'discard_draft') {
    recipe_clear_draft();
    $session->message('Recipe draft discarded.');
    redirect_to(url_for('/recipes/new.php'));
  }

  recipe_save_draft($draft);

  // PRG redirect: avoids resubmits and keeps "no JS" UX sane
  redirect_to(url_for('/recipes/new.php'));
}

?>

<?php $page_title = 'Add Recipe'; ?>
<?php include(SHARED_PATH . '/public_header.php'); ?>

<div class="wrapper">
  <div class="container">
    <h2>Add Recipe</h2>
    <p>Fields marked with a * are required.</p>
    <?php echo display_errors($recipe->errors); ?>
    <form action="<?php echo url_for('/recipes/new.php'); ?>" method="post" enctype="multipart/form-data">
      <?php include('form_fields.php'); ?>
      <div>
        <button type="submit" name="action" value="save_recipe">Add Recipe</button>
        <button type="submit" name="action" value="discard_draft" formnovalidate>Discard Draft</button>
      </div>
    </form>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
