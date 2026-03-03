<?php
require_once('../../private/initialize.php');

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
  redirect_to(url_for('/recipes'));
}

$recipe = Recipe::find_by_id($id);
/** @var Recipe $recipe */

if (!$recipe) {
  redirect_to(url_for('/recipes'));
}

// Privacy enforcement
if (!$recipe->can_view($session)) {
  $session->message('That recipe is private.');
  redirect_to(url_for('/recipes'));
}

// Handle rating POST (PRG)
if (is_post_request()) {
  $action = $_POST['action'] ?? '';

  if ($action === 'rate') {

    if (!$session->is_logged_in()) {
      $return_to = url_for('/recipes/show.php?id=' . u($recipe->id_rcp));
      redirect_to(url_for('/login.php?return_to=' . u($return_to)));
    }

    $rating = $_POST['rating'] ?? '';
    if (!ctype_digit((string)$rating) || (int)$rating < 1 || (int)$rating > 5) {
      $session->message('Rating must be between 1 and 5.');
      redirect_to(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)));
    }

    $ok = $recipe->save_rating($session->get_user_id(), (int)$rating);
    $session->message($ok ? 'Rating saved.' : 'Could not save rating.');

    redirect_to(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)));
  }
}

$ingredients    = $recipe->ingredients();
$directions     = $recipe->directions();
$images         = $recipe->images();
$meal_types     = $recipe->meal_types();
$cuisines       = $recipe->cuisines();
$dietary_styles = $recipe->dietary_styles();

$rating = $recipe->rating_summary();
$rating_avg   = $rating['avg'];
$rating_count = $rating['count'];

$user_rating = $recipe->user_rating($session->get_user_id());

$page_title = h($recipe->title_rcp) . ' Recipe';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="container">
    <div class="recipe-show">


      <h2><?php echo h($recipe->title_rcp); ?></h2>

      <?php if (!is_blank($recipe->description_rcp)) { ?>
        <p class="recipe-description"><?php echo h($recipe->description_rcp); ?></p>
      <?php } ?>

      <div class="recipe-meta">
        <?php if (!is_blank($recipe->serving_rcp)) { ?>
          <span><strong>Servings:</strong> <?php echo h(format_number_clean($recipe->serving_rcp)); ?></span>
        <?php } ?>

        <span><strong>Prep:</strong> <?php echo h((int)$recipe->prep_time_minutes_rcp); ?> min</span>
        <span><strong>Cook:</strong> <?php echo h((int)$recipe->cook_time_minutes_rcp); ?> min</span>

        <?php if (!is_blank($recipe->youtube_url_rcp)) { ?>
          <span><strong>Video:</strong>
            <a href="<?php echo h($recipe->youtube_url_rcp); ?>" target="_blank" rel="noopener noreferrer">YouTube</a>
          </span>
        <?php } ?>

        <span><strong>Privacy:</strong> <?php echo h(display_title_case($recipe->privacy_rcp)); ?></span>
      </div>

      <div class="recipe-actions">
        <?php if ($recipe->can_edit($session)) { ?>
          <a class="button" href="<?php echo url_for('/recipes/edit.php?id=' . h(u($recipe->id_rcp))); ?>">Edit Recipe</a>
        <?php } ?>

        <!-- Placeholder for later -->
        <button type="button" class="button" disabled title="Coming soon">Print / PDF</button>
      </div>

      <?php if (!empty($images)) { ?>
        <section class="recipe-images">

          <?php
          $first = $images[0];
          $hero_url = url_for('/uploads/recipes/800/' . h(u($first)));
          ?>
          <div class="recipe-image-hero">
            <a href="<?php echo $hero_url; ?>" target="_blank" rel="noopener noreferrer">
              <img src="<?php echo $hero_url; ?>" alt="<?php echo h($recipe->title_rcp); ?>">
            </a>
          </div>

          <?php if (count($images) > 1) { ?>
            <div class="recipe-image-thumbs">
              <?php foreach ($images as $idx => $file_name) {
                $thumb_url = url_for('/uploads/recipes/270/' . h(u($file_name)));
                $full_url  = url_for('/uploads/recipes/800/' . h(u($file_name)));

                $alt = $recipe->title_rcp . ' photo ' . ($idx + 1) . '.';
              ?>
                <a class="recipe-thumb" href="<?php echo $full_url; ?>" target="_blank" rel="noopener noreferrer">
                  <img src="<?php echo $thumb_url; ?>" alt="<?php echo h($alt); ?>">
                </a>
              <?php } ?>
            </div>
          <?php } ?>

        </section>
      <?php } ?>

      <?php
      $chips = array_merge($meal_types, $cuisines, $dietary_styles);

      // Title-case them, then unique + sort (optional but looks nice)
      $chips = array_map('display_title_case', $chips);
      $chips = array_values(array_unique($chips));
      sort($chips, SORT_NATURAL | SORT_FLAG_CASE);
      ?>

      <?php if (!empty($chips)) { ?>
        <section class="recipe-chips" aria-label="Recipe tags">
          <?php foreach ($chips as $chip) { ?>
            <span class="chip"><?php echo h($chip); ?></span>
          <?php } ?>
        </section>
      <?php } ?>

      <section class="recipe-rating">
        <div class="rating-summary">
          <strong>Rating:</strong>
          <?php if ($rating_avg === null) { ?>
            <span>Not rated yet</span>
          <?php } else { ?>
            <span><?php echo h(number_format($rating_avg, 1)); ?>/5</span>
            <span>(<?php echo h($rating_count); ?>)</span>
          <?php } ?>
        </div>

        <div class="rating-form">
          <form action="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->id_rcp))); ?>" method="post">
            <input type="hidden" name="action" value="rate">

            <fieldset>
              <legend>Your Rating</legend>

              <?php for ($i = 1; $i <= 5; $i++) {
                $checked = ($user_rating === $i) ? 'checked' : '';
                $rid = "rate-" . $recipe->id_rcp . "-" . $i;
              ?>
                <input type="radio" id="<?php echo h($rid); ?>" name="rating" value="<?php echo h($i); ?>" <?php echo $checked; ?>>
                <label for="<?php echo h($rid); ?>"><?php echo h($i); ?></label>
              <?php } ?>

              <button type="submit">Save Rating</button>
            </fieldset>
          </form>
        </div>
      </section>

      <div class="recipe-body">
        <section class="recipe-ingredients">
          <h2>Ingredients</h2>

          <!-- Placeholder buttons for JS scaling later -->
          <div class="scale-buttons">
            <button type="button" class="button" disabled>½x</button>
            <button type="button" class="button" disabled>1x</button>
            <button type="button" class="button" disabled>2x</button>
            <button type="button" class="button" disabled>3x</button>
          </div>

          <?php if (empty($ingredients)) { ?>
            <p>No ingredients listed.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($ingredients as $ing) { ?>
                <li>
                  <?php
                  $qty = format_number_clean($ing['quantity_rcping']);
                  $abbr = $ing['abbr_mes'] ?? '';
                  $iname = $ing['name_ing'] ?? '';
                  ?>
                  <span class="qty"><?php echo h($qty); ?></span>
                  <?php if (!is_blank($abbr)) { ?>
                    <span class="unit"><?php echo h($abbr); ?></span>
                  <?php } ?>
                  <span class="name"><?php echo h($iname); ?></span>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>
        </section>

        <section class="recipe-directions">
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

    </div>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
