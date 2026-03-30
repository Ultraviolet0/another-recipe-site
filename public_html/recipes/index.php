<?php
require_once('../../private/initialize.php');

$per_page = 12;
$page = $_GET['page'] ?? 1;
$page = ctype_digit((string)$page) ? (int)$page : 1;
$page = max(1, $page);

$search = $_GET['search'] ?? '';
$search = trim($search);

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
  $search,
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
  $search,
  $selected_meal_types,
  $selected_cuisines,
  $selected_dietary_styles
);

$page_title = 'Recipes';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <section class="recipes-index-header">
    <div>
      <h1>Browse Recipes</h1>
      <?php if ($search !== '') { ?>
        <p>Showing results for "<strong><?php echo h($search); ?></strong>"</p>
      <?php } ?>
    </div>
    <p><?php echo h($total_count); ?> recipe<?php echo $total_count === 1 ? '' : 's'; ?></p>
  </section>

  <?php
  $has_active_filters = $search !== '' || !empty($selected_meal_types) || !empty($selected_cuisines) || !empty($selected_dietary_styles);
  ?>

  <section class="recipe-filters" aria-label="Recipe filters">
    <form action="<?php echo url_for('/recipes/index.php'); ?>" method="get">
      <?php if ($search !== '') { ?>
        <input type="hidden" name="search" value="<?php echo h($search); ?>">
      <?php } ?>

      <div class="filter-menu-row">

        <div class="filter-menu" data-filter-menu="meal-types">
          <button type="button" class="filter-menu-toggle">Meal Types</button>
          <div class="filter-menu-panel">
            <?php foreach ($meal_types as $mty) {
              $id = (string)$mty->id_mty;
              $checked = in_array($id, $selected_meal_types, true) ? 'checked' : '';
              $input_id = 'filter-meal-type-' . $id;
            ?>
              <div class="filter-option">
                <input type="checkbox" id="<?php echo h($input_id); ?>" name="meal_types[]" value="<?php echo h($id); ?>" data-filter-menu="meal-types" <?php echo $checked; ?>>
                <label for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($mty->name_mty)); ?></label>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="filter-menu" data-filter-menu="cuisines">
          <button type="button" class="filter-menu-toggle">Cuisines</button>
          <div class="filter-menu-panel">
            <?php foreach ($cuisines as $csn) {
              $id = (string)$csn->id_csn;
              $checked = in_array($id, $selected_cuisines, true) ? 'checked' : '';
              $input_id = 'filter-cuisine-' . $id;
            ?>
              <div class="filter-option">
                <input type="checkbox" id="<?php echo h($input_id); ?>" name="cuisines[]" value="<?php echo h($id); ?>" data-filter-menu="cuisines" <?php echo $checked; ?>>
                <label for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($csn->name_csn)); ?></label>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="filter-menu" data-filter-menu="dietary-styles">
          <button type="button" class="filter-menu-toggle">Dietary Styles</button>
          <div class="filter-menu-panel">
            <?php foreach ($dietary_styles as $dst) {
              $id = (string)$dst->id_dst;
              $checked = in_array($id, $selected_dietary_styles, true) ? 'checked' : '';
              $input_id = 'filter-dietary-style-' . $id;
            ?>
              <div class="filter-option">
                <input type="checkbox" id="<?php echo h($input_id); ?>" name="dietary_styles[]" value="<?php echo h($id); ?>" data-filter-menu="dietary-styles" <?php echo $checked; ?>>
                <label for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($dst->name_dst)); ?></label>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="filter-actions">
          <button type="submit" class="button apply-filters-button">Apply Filters</button>
          <a class="button button-secondary" href="<?php echo recipes_index_page_url(1, $search); ?>">Clear Filters</a>
        </div>

      </div>
    </form>
  </section>

  <?php if ($has_active_filters) { ?>
    <section class="active-filters" aria-label="Active filters">
      <p><strong>Active Filters:</strong></p>

      <?php if ($search !== '') { ?>
        <a
          class="active-filter-chip"
          href="<?php echo recipes_index_page_url(1, '', $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
          Search: <?php echo h($search); ?>
          <span aria-hidden="true">&times;</span>
        </a>
      <?php } ?>

      <div class="active-filter-chips">
        <?php foreach ($selected_meal_types as $id) {
          if (!isset($meal_type_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('meal_types', $id, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($meal_type_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>

        <?php foreach ($selected_cuisines as $id) {
          if (!isset($cuisine_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('cuisines', $id, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($cuisine_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>

        <?php foreach ($selected_dietary_styles as $id) {
          if (!isset($dietary_style_map[(string)$id])) continue;
        ?>
          <a
            class="active-filter-chip"
            href="<?php echo recipes_index_remove_filter_url('dietary_styles', $id, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
            <?php echo h($dietary_style_map[(string)$id]); ?>
            <span aria-hidden="true">&times;</span>
          </a>
        <?php } ?>
      </div>
    </section>
  <?php } ?>

  <?php if (empty($recipes)) { ?>
    <?php if ($search !== '' || $has_active_filters) { ?>
      <p>No recipes matched your search and filters.</p>
    <?php } else { ?>
      <p>No recipes found.</p>
    <?php } ?>
  <?php } else { ?>
    <div class="recipe-grid">
      <?php foreach ($recipes as $recipe) {
        $rating = $recipe->rating_display();
        $rating_avg = $rating['avg'];
        $rating_count = $rating['count'];
        $total_time = $recipe->total_time_minutes();
        $image_src = $recipe->first_image_card_src();
        $image_srcset = $recipe->first_image_card_srcset();
        $image_sizes = $recipe->first_image_card_sizes();
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
            <?php if ($image_src) { ?>
              <img
                src="<?php echo h($image_src); ?>"
                srcset="<?php echo h($image_srcset); ?>"
                sizes="<?php echo h($image_sizes); ?>"
                width="270"
                height="270"
                alt="<?php echo h($recipe->title_rcp); ?>"
                loading="lazy"
                decoding="async">
            <?php } else { ?>
              <img
                src="<?php echo url_for('/images/recipe-placeholder-270.webp'); ?>"
                width="270"
                height="270"
                alt=""
                loading="lazy"
                decoding="async">
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
          <li><a href="<?php echo recipes_index_page_url($page - 1, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">&laquo; Prev</a></li>
        <?php } ?>

        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
          <li>
            <?php if ($i === $page) { ?>
              <span class="current"><?php echo h($i); ?></span>
            <?php } else { ?>
              <a href="<?php echo recipes_index_page_url($i, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">
                <?php echo h($i); ?>
              </a>
            <?php } ?>
          </li>
        <?php } ?>

        <?php if ($page < $total_pages) { ?>
          <li><a href="<?php echo recipes_index_page_url($page + 1, $search, $selected_meal_types, $selected_cuisines, $selected_dietary_styles); ?>">Next &raquo;</a></li>
        <?php } ?>
      </ul>
    </nav>
  <?php } ?>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
