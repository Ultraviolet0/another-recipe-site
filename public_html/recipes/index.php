<?php
require_once('../../private/initialize.php');

$per_page = 12;
$page = $_GET['page'] ?? 1;
$page = ctype_digit((string)$page) ? (int)$page : 1;
$page = max(1, $page);

$selected_meal_types = $_GET['meal_types'] ?? [];
$selected_cuisines = $_GET['cuisines'] ?? [];
$selected_dietary_styles = $_GET['dietary_styles'] ?? [];

$meal_types = MealType::find_all();
$cuisines = Cuisine::find_all();
$dietary_styles = DietaryStyle::find_all();

$meal_type_map = [];
foreach ($meal_types as $mty) {
  $meal_type_map[(string)$mty->id_mty] = display_title_case($mty->name_mty);
}

$cuisine_map = [];
foreach ($cuisines as $csn) {
  $cuisine_map[(string)$csn->id_csn] = display_title_case($csn->name_csn);
}

$dietary_style_map = [];
foreach ($dietary_styles as $dst) {
  $dietary_style_map[(string)$dst->id_dst] = display_title_case($dst->name_dst);
}

$total_count = Recipe::count_filtered(
  $session,
  $selected_meal_types,
  $selected_cuisines,
  $selected_dietary_styles
);

$total_pages = max(1, (int)ceil($total_count / $per_page));

if ($page > $total_pages) {
  $page = $total_pages;
}

$recipes = Recipe::find_filtered_paginated(
  $session,
  $page,
  $per_page,
  $selected_meal_types,
  $selected_cuisines,
  $selected_dietary_styles
);

$page_title = 'Recipes';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <section class="recipes-index-header">
    <h2>Recipes</h2>
    <p><?php echo h($total_count); ?> recipe<?php echo $total_count === 1 ? '' : 's'; ?></p>
  </section>

  <?php
  $has_active_filters = !empty($selected_meal_types) || !empty($selected_cuisines) || !empty($selected_dietary_styles);
  ?>

  <details class="recipe-filters" open>
    <summary>Filters</summary>

    <form action="<?php echo url_for('/recipes/index.php'); ?>" method="get">
      <fieldset>

        <div class="filter-grid">

          <!-- Meal Types -->
          <div class="filter-field">
            <label for="meal-types">Meal Types</label>
            <select name="meal_types[]" id="meal-types" multiple>
              <?php foreach ($meal_types as $mty) {
                $id = (string)$mty->id_mty;
                $selected = in_array($id, $selected_meal_types, true) ? 'selected' : '';
              ?>
                <option value="<?php echo h($id); ?>" <?php echo $selected; ?>>
                  <?php echo h(display_title_case($mty->name_mty)); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <!-- Cuisines -->
          <div class="filter-field">
            <label for="cuisines">Cuisines</label>
            <select name="cuisines[]" id="cuisines" multiple>
              <?php foreach ($cuisines as $csn) {
                $id = (string)$csn->id_csn;
                $selected = in_array($id, $selected_cuisines, true) ? 'selected' : '';
              ?>
                <option value="<?php echo h($id); ?>" <?php echo $selected; ?>>
                  <?php echo h(display_title_case($csn->name_csn)); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <!-- Dietary Styles -->
          <div class="filter-field">
            <label for="dietary-styles">Dietary Styles</label>
            <select name="dietary_styles[]" id="dietary-styles" multiple>
              <?php foreach ($dietary_styles as $dst) {
                $id = (string)$dst->id_dst;
                $selected = in_array($id, $selected_dietary_styles, true) ? 'selected' : '';
              ?>
                <option value="<?php echo h($id); ?>" <?php echo $selected; ?>>
                  <?php echo h(display_title_case($dst->name_dst)); ?>
                </option>
              <?php } ?>
            </select>
          </div>

        </div>

        <p class="filter-help">
          Hold Ctrl (Windows) or Command (Mac) to select multiple options.
        </p>

        <div class="filter-actions">
          <button type="submit" class="apply-filters-button">Apply Filters</button>
          <a class="button" href="<?php echo url_for('/recipes/index.php'); ?>">Clear Filters</a>
        </div>

      </fieldset>
    </form>

  </details>

  <?php if ($has_active_filters) { ?>
    <section class="active-filters" aria-label="Active filters">
      <p><strong>Active Filters:</strong></p>

      <div class="active-filter-chips">
        <?php foreach ($selected_meal_types as $id) {
          if (!isset($meal_type_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('meal_types', $id, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($meal_type_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>

        <?php foreach ($selected_cuisines as $id) {
          if (!isset($cuisine_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('cuisines', $id, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($cuisine_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>

        <?php foreach ($selected_dietary_styles as $id) {
          if (!isset($dietary_style_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('dietary_styles', $id, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($dietary_style_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>
      </div>
    </section>
  <?php } ?>

  <?php if (empty($recipes)) { ?>
    <p>No recipes matched your filters.</p>
  <?php } else { ?>
    <div class="recipe-grid">
      <?php foreach ($recipes as $recipe) {
        $rating = $recipe->rating_display();
        $rating_avg = $rating['avg'];
        $rating_count = $rating['count'];
        $total_time = $recipe->total_time_minutes();
        $image_url = $recipe->first_image_270_url();
        $badge_name = $recipe->badge_name();
      ?>
        <a href="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>" class="recipe-card">
          <div class="recipe-card-info">
            <h3><?php echo h($recipe->title_rcp); ?></h3>
            <div class="recipe-card-rating-time">
              <div class="recipe-card-rating">
                <span>⭐</span>
                <span><?php echo $rating_avg === null ? '—' : h(number_format($rating_avg, 1)); ?></span>
                <span>(<?php echo h($rating_count); ?>)</span>
              </div>
              <time datetime="<?php echo h('PT' . $total_time . 'M'); ?>" class="recipe-card-time">
                <?php echo h($total_time); ?> mins
              </time>
            </div>
          </div>

          <div class="recipe-card-media">
            <?php if ($image_url) { ?>
              <img
                src="<?php echo $image_url; ?>"
                width="270"
                height="270"
                alt="<?php echo h($recipe->title_rcp); ?>"
                loading="lazy">
            <?php } else { ?>
              <img
                src="<?php echo url_for('/images/recipe-placeholder-270.png'); ?>"
                width="270"
                height="270"
                alt=""
                loading="lazy">
            <?php } ?>

            <?php if (!is_blank($badge_name)) { ?>
              <span class="recipe-card-badge"><?php echo h(display_title_case($badge_name)); ?></span>
            <?php } ?>
          </div>
        </a>
      <?php } ?>
    </div>
  <?php } ?>

  <?php if ($total_pages > 1) { ?>
    <nav class="pagination" aria-label="Recipe pages">
      <ul>
        <?php if ($page > 1) { ?>
          <li><a href="<?php echo recipes_index_page_url($page - 1, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">&laquo; Prev</a></li>
        <?php } ?>

        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
          <li>
            <?php if ($i === $page) { ?>
              <span class="current"><?php echo h($i); ?></span>
            <?php } else { ?>
              <a href="<?php echo recipes_index_page_url($i, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
                <?php echo h($i); ?>
              </a>
            <?php } ?>
          </li>
        <?php } ?>

        <?php if ($page < $total_pages) { ?>
          <li><a href="<?php echo recipes_index_page_url($page + 1, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">Next &raquo;</a></li>
        <?php } ?>
      </ul>
    </nav>
  <?php } ?>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
