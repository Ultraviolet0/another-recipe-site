<?php
require_once('../private/initialize.php');

$newest_recipes = Recipe::find_homepage_newest($session, 6);
$top_rated_recipes = Recipe::find_homepage_top_rated($session, 6);
$quick_recipes = Recipe::find_homepage_quick_recipes($session, 6);

$page_title = 'Home';
include(SHARED_PATH . '/public_header.php');
?>

<div class="home-page">

  <section class="home-hero">
    <div class="wrapper">
      <div class="home-hero-content">
        <h1 class="home-hero-kicker">Welcome to anotherrecipe.site</h1>
        <h2>Find and share recipes without the fluff.</h2>
        <p class="home-hero-text">anotherrecipe.site is a simple, no-nonsense place to discover recipes, save favorites, and share your own cooking with the community.</p>

        <div class="home-hero-actions">
          <?php if ($session->is_logged_in()) { ?>
            <a class="button" href="<?php echo url_for('/recipes/new.php'); ?>">Add Your Recipe</a>
          <?php } else { ?>
            <a class="button" href="<?php echo url_for('/signup.php'); ?>">Create a Free Account</a>
          <?php } ?>
          <a class="button button-secondary" href="<?php echo url_for('/recipes'); ?>">Browse Recipes</a>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($newest_recipes)) { ?>
    <section class="home-carousel-section">
      <div class="wrapper">
        <div class="home-section-header">
          <h2>Newest Recipes</h2>
          <a href="<?php echo url_for('/recipes'); ?>">View All</a>
        </div>

        <div class="home-carousel-wrapper">
          <button class="carousel-button carousel-prev" aria-label="Scroll left">&lsaquo;</button>
          <div class="home-carousel">
            <?php foreach ($newest_recipes as $recipe) {
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
          <button class="carousel-button carousel-next" aria-label="Scroll right">&rsaquo;</button>
        </div>
      </div>
    </section>
  <?php } ?>

  <?php if (!empty($top_rated_recipes)) { ?>
    <section class="home-carousel-section">
      <div class="wrapper">
        <div class="home-section-header">
          <h2>Top-Rated Recipes</h2>
          <a href="<?php echo url_for('/recipes'); ?>">View All</a>
        </div>

        <div class="home-carousel-wrapper">
          <button class="carousel-button carousel-prev" aria-label="Scroll left">&lsaquo;</button>
          <div class="home-carousel">
            <?php foreach ($top_rated_recipes as $recipe) {
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
          <button class="carousel-button carousel-next" aria-label="Scroll right">&rsaquo;</button>
        </div>
      </div>
    </section>
  <?php } ?>

  <?php if (!empty($quick_recipes)) { ?>
    <section class="home-carousel-section">
      <div class="wrapper">
        <div class="home-section-header">
          <h2>30 Minute Recipes</h2>
          <a href="<?php echo url_for('/recipes'); ?>">View All</a>
        </div>

        <div class="home-carousel-wrapper">
          <button class="carousel-button carousel-prev" aria-label="Scroll left">&lsaquo;</button>
          <div class="home-carousel">
            <?php foreach ($quick_recipes as $recipe) {
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
          <button class="carousel-button carousel-next" aria-label="Scroll right">&rsaquo;</button>
        </div>
      </div>
    </section>
  <?php } ?>

</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
