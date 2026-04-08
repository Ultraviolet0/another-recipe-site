<?php
require_once('../../private/initialize.php');

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
  redirect_to(url_for('/recipes'));
}

$recipe = Recipe::find_with_creator_by_id($id);
/** @var Recipe $recipe */

if (!$recipe) {
  redirect_to(url_for('/recipes'));
}

if (!$recipe->can_view($session)) {
  $session->message('That recipe is private.');
  redirect_to(url_for('/recipes'));
}

if (is_post_request()) {
  if (isset($_POST['clear_rating'])) {

    if (!$session->is_logged_in()) {
      redirect_to(url_for('/login.php'));
    }

    $ok = $recipe->delete_rating($session->get_user_id());
    $message = $ok ? 'Rating cleared.' : 'Could not clear rating.';

    if (is_ajax_request()) {
      $summary = $recipe->rating_summary();

      render_json([
        'ok' => $ok,
        'message' => $message,
        'rating_avg' => $summary['avg'],
        'rating_count' => $summary['count'],
        'user_rating' => null
      ], $ok ? 200 : 422);
    }

    $session->message($message);
    redirect_to(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)));
  }

  if (isset($_POST['save_rating'])) {
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
    $message = $ok ? 'Rating saved.' : 'Could not save rating.';

    if (is_ajax_request()) {
      $summary = $recipe->rating_summary();

      render_json([
        'ok' => $ok,
        'message' => $message,
        'rating_avg' => $summary['avg'],
        'rating_count' => $summary['count'],
        'user_rating' => $ok ? (int)$rating : $recipe->user_rating($session->get_user_id())
      ], $ok ? 200 : 422);
    }

    $session->message($message);
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

$youtube_video_id = null;
$youtube_embed = null;

if (!is_blank($recipe->youtube_url_rcp)) {
  $youtube_video_id = extract_youtube_video_id($recipe->youtube_url_rcp);
  $youtube_embed = $youtube_video_id ? youtube_embed_url($youtube_video_id) : null;
}

$page_title = $recipe->title_rcp . ' Recipe';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="container">
    <div class="recipe-show">

      <h1 title="<?php echo h($recipe->title_rcp); ?> Recipe"><?php echo h($recipe->title_rcp); ?> Recipe</h1>

      <?php if (!is_blank($recipe->description_rcp)) { ?>
        <p class="recipe-description"><?php echo h($recipe->description_rcp); ?></p>
      <?php } ?>

      <div class="recipe-meta">
        <?php if (!is_blank($recipe->serving_rcp)) { ?>
          <span>
            <strong>Servings:</strong>
            <span
              class="recipe-servings-value"
              data-base-servings="<?php echo h($recipe->serving_rcp); ?>"><?php echo h(format_quantity_kitchen($recipe->serving_rcp)); ?></span>
          </span>
        <?php } ?>

        <span><strong>Prep:</strong> <?php echo h((int)$recipe->prep_time_minutes_rcp); ?> min</span>
        <span><strong>Cook:</strong> <?php echo h((int)$recipe->cook_time_minutes_rcp); ?> min</span>

        <?php if ($session->is_admin_logged_in()) { ?>
          <span><strong>Privacy:</strong> <?php echo h(display_title_case($recipe->privacy_rcp)); ?></span>
        <?php } ?>

        <?php if ($recipe->creator_username()) { ?>
          <span><strong>By:</strong> <a href="<?php echo $recipe->creator_profile_url(); ?>"><?php echo h($recipe->creator_username()); ?></a></span>
        <?php } ?>

        <span data-rating-summary><strong>Rating:</strong>
          <?php if ($rating_avg === null) { ?>
            <span data-rating-text>Not rated</span>
          <?php } else { ?>
            <span data-rating-text><?php echo h(number_format($rating_avg, 1)); ?>/5</span>
            <span data-rating-count>(<?php echo h($rating_count); ?>)</span>
        </span>
      <?php } ?>
      </div>

      <section class="recipe-rating rating-form">
        <form action="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>" method="post" data-is-logged-in="<?php echo $session->is_logged_in() ? 'true' : 'false'; ?>" data-login-url="<?php echo url_for('/login.php?return_to=' . u(url_for('/recipes/show.php?id=' . u($recipe->id_rcp)))); ?>">
          <input type="hidden" name="save_rating" value="1">

          <fieldset>
            <legend>Your Rating</legend>

            <?php for ($i = 1; $i <= 5; $i++) {
              $checked = ($user_rating === $i) ? 'checked' : '';
              $rid = "rate-" . $recipe->id_rcp . "-" . $i;
            ?>
              <input type="radio" id="<?php echo h($rid); ?>" name="rating" value="<?php echo h($i); ?>" <?php echo $checked; ?>>
              <label for="<?php echo h($rid); ?>"><?php echo h($i); ?></label>
            <?php } ?>

            <button type="submit" class="button rating-save-button">Save Rating</button>
            <button type="submit" class="button button-secondary rating-clear-button" name="clear_rating" value="1" <?php echo $user_rating === null ? 'hidden' : ''; ?>>Clear Rating</button>
          </fieldset>
        </form>
      </section>

      <div class="recipe-actions">
        <?php if ($recipe->can_edit($session)) { ?>
          <a class="button" href="<?php echo url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)); ?>">Edit Recipe</a>
          <a class="button button-danger" href="<?php echo url_for('/recipes/delete.php?id=' . u($recipe->id_rcp)); ?>">Delete Recipe</a>
        <?php } ?>

        <button type="button" class="button" disabled title="Coming soon">Print / PDF</button>
      </div>

      <?php if (!empty($images)) { ?>
        <section class="recipe-images">

          <?php
          $hero_src = $recipe->first_image_hero_src();
          $hero_srcset = $recipe->first_image_hero_srcset();
          $hero_sizes = $recipe->first_image_hero_sizes();
          $hero_full_url = $recipe->first_image_full_url();
          ?>
          <div class="recipe-image-hero">
            <a href="<?php echo h($hero_full_url); ?>" target="_blank" rel="noopener noreferrer" class="recipe-hero-link">
              <img
                src="<?php echo h($hero_src); ?>"
                srcset="<?php echo h($hero_srcset); ?>"
                sizes="<?php echo h($hero_sizes); ?>"
                alt="<?php echo h($recipe->title_rcp); ?>"
                class="recipe-hero-image recipe-hero-image-current"
                decoding="async"
                fetchpriority="high">
              <img src="" alt="" class="recipe-hero-image recipe-hero-image-next" aria-hidden="true">
            </a>
          </div>

          <?php if (count($images) > 1) { ?>
            <div class="recipe-image-thumbs">
              <?php foreach ($images as $idx => $file_name) {
                $thumb_270 = url_for('/uploads/recipes/270/' . u($file_name));
                $thumb_540 = url_for('/uploads/recipes/540/' . u($file_name));
                $full_url  = url_for('/uploads/recipes/1600/' . u($file_name));

                $alt = $recipe->title_rcp . ' photo ' . ($idx + 1) . '.';
              ?>
                <a
                  class="recipe-thumb<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                  href="<?php echo h($full_url); ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                  data-full-url="<?php echo h($full_url); ?>"
                  data-alt="<?php echo h($alt); ?>">
                  <img
                    src="<?php echo h($thumb_270); ?>"
                    srcset="<?php echo h($thumb_270); ?> 1x, <?php echo h($thumb_540); ?> 2x"
                    width="270"
                    height="270"
                    alt="<?php echo h($alt); ?>"
                    loading="lazy"
                    decoding="async">
                </a>
              <?php } ?>
            </div>
          <?php } ?>

        </section>
      <?php } ?>

      <?php
      $chips = array_merge($meal_types, $cuisines, $dietary_styles);
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

      <?php if ($youtube_embed) { ?>
        <section class="recipe-video">
          <h2>Video</h2>

          <div class="recipe-video-embed">
            <iframe
              src="<?php echo h($youtube_embed); ?>"
              title="<?php echo h($recipe->title_rcp); ?> video"
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen></iframe>
          </div>
        </section>
      <?php } ?>

      <div class="recipe-body">
        <section class="recipe-ingredients">
          <h2>Ingredients</h2>

          <div class="scale-buttons">
            <button type="button" class="button button-secondary scale-button" data-scale="0.5" disabled>½x</button>
            <button type="button" class="button button-secondary scale-button is-active" data-scale="1">1x</button>
            <button type="button" class="button button-secondary scale-button" data-scale="2" disabled>2x</button>
            <button type="button" class="button button-secondary scale-button" data-scale="3" disabled>3x</button>
          </div>

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
                  <span class="qty" data-base-qty="<?php echo h($ing['quantity_rcping']); ?>"><?php echo h(format_quantity_kitchen($qty)); ?></span>
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
