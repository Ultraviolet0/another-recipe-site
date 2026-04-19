<?php
require_once('../../private/initialize.php');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
  error_404();
}

$recipe = Recipe::find_with_creator_by_id($id);
/** @var Recipe $recipe */

if (!$recipe) {
  error_404();
}

if (!$recipe->can_view($session)) {
  $session->message('That recipe is private.');
  redirect_to(url_for('/recipes'));
}

$ingredients    = $recipe->ingredients();
$directions     = $recipe->directions();
$images         = $recipe->images();
$meal_types     = $recipe->meal_types();
$cuisines       = $recipe->cuisines();
$dietary_styles = $recipe->dietary_styles();

$chips = array_merge($meal_types, $cuisines, $dietary_styles);
$chips = array_map('display_title_case', $chips);
$chips = array_values(array_unique($chips));
sort($chips, SORT_NATURAL | SORT_FLAG_CASE);

$hero_src = null;
if (!empty($images) && method_exists($recipe, 'first_image_hero_src')) {
  $hero_src = $recipe->first_image_hero_src();
}

$page_title = $recipe->title_rcp . ' Recipe';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?php echo h($page_title); ?> | Print</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo url_for('/css/styles.css?v=' . filemtime(PUBLIC_PATH . '/css/styles.css')); ?>">
</head>

<body class="print-recipe-page">
  <div class="print-recipe wrapper">
    <div class="print-recipe-toolbar">
      <a class="button button-secondary" href="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>">Back to Recipe</a>
      <button type="button" class="button print-recipe-print-button">Print / Save as PDF</button>
      <div class="print-recipe-toggle">
        <label for="print-hide-images-toggle" class="button button-danger">Hide Image</label>
        <input type="checkbox" id="print-hide-images-toggle" class="visually-hidden">
      </div>
    </div>

    <header class="print-recipe-header">
      <h1><?php echo h($recipe->title_rcp); ?> Recipe</h1>

      <?php if (!is_blank($recipe->description_rcp)) { ?>
        <p class="print-recipe-description"><?php echo h($recipe->description_rcp); ?></p>
      <?php } ?>

      <div class="print-recipe-meta">
        <?php if (!is_blank($recipe->serving_rcp)) { ?>
          <span><strong>Servings:</strong> <?php echo h(format_quantity_kitchen($recipe->serving_rcp)); ?></span>
        <?php } ?>

        <span><strong>Prep:</strong> <?php echo h((int)$recipe->prep_time_minutes_rcp); ?> min</span>
        <span><strong>Cook:</strong> <?php echo h((int)$recipe->cook_time_minutes_rcp); ?> min</span>

        <?php if ($recipe->creator_username()) { ?>
          <span><strong>By:</strong> <?php echo h($recipe->creator_username()); ?></span>
        <?php } ?>
      </div>

      <?php if (!empty($chips)) { ?>
        <div class="print-recipe-chips" aria-label="Recipe tags">
          <?php foreach ($chips as $chip) { ?>
            <span class="print-recipe-chip"><?php echo h($chip); ?></span>
          <?php } ?>
        </div>
      <?php } ?>
    </header>

    <?php if ($hero_src) { ?>
      <div class="print-recipe-image">
        <img src="<?php echo h($hero_src); ?>" alt="<?php echo h($recipe->title_rcp); ?>">
      </div>
    <?php } ?>

    <div class="print-recipe-body">
      <section class="print-recipe-section">
        <h2>Ingredients</h2>

        <?php if (empty($ingredients)) { ?>
          <p>No ingredients listed.</p>
        <?php } else { ?>
          <ul>
            <?php foreach ($ingredients as $ing) { ?>
              <li>
                <?php
                $qty = $ing['quantity_rcping'];
                $abbr = $ing['abbr_mes'] ?? '';
                $iname = $ing['name_ing'] ?? '';
                ?>
                <?php echo h(format_quantity_kitchen($qty)); ?>
                <?php if (!is_blank($abbr)) { ?>
                  <?php echo ' ' . h($abbr); ?>
                <?php } ?>
                <?php echo ' ' . h($iname); ?>
              </li>
            <?php } ?>
          </ul>
        <?php } ?>
      </section>

      <section class="print-recipe-section">
        <h2>Directions</h2>

        <?php if (empty($directions)) { ?>
          <p>No directions listed.</p>
        <?php } else { ?>
          <ol>
            <?php foreach ($directions as $dir) { ?>
              <li><?php echo h($dir['instruction_dir']); ?></li>
            <?php } ?>
          </ol>
        <?php } ?>
      </section>
    </div>

    <footer class="print-recipe-footer">
      <p>Printed from anotherrecipe.site</p>
    </footer>
  </div>

  <script src="<?php echo url_for('/js/scripts.js?v=' . filemtime(PUBLIC_PATH . '/js/scripts.js')); ?>"></script>
</body>

</html>
