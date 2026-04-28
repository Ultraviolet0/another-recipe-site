<?php
require_once('../private/initialize.php');

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
  redirect_to(url_for('/'));
}

$user = User::find_by_id($id);
/** @var User $user */

if (!$user) {
  redirect_to(url_for('/'));
}

$profile_image_url = null;
if (!is_blank($user->id_img_usr) && method_exists($user, 'profile_image_url')) {
  $profile_image_url = $user->profile_image_url();
}

$public_recipe_count = 0;
if (method_exists('Recipe', 'count_public_by_user_id')) {
  $public_recipe_count = Recipe::count_public_by_user_id($user->id_usr);
}

$public_recipe_average = null;
if (method_exists('Recipe', 'average_rating_for_public_recipes_by_user_id')) {
  $public_recipe_average = Recipe::average_rating_for_public_recipes_by_user_id($user->id_usr);
}

$recent_recipes = [];
if (method_exists('Recipe', 'find_public_by_user_id')) {
  $recent_recipes = Recipe::find_public_by_user_id($user->id_usr, 8);
}

$page_title = h($user->username_usr) . ' Profile';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="profile-page">
    <section class="profile-header">
      <div class="profile-avatar-wrap">
        <?php if ($profile_image_url) { ?>
          <img class="profile-avatar" src="<?php echo h($profile_image_url); ?>" alt="<?php echo h($user->username_usr); ?> profile picture.">
        <?php } else { ?>
          <div class="profile-avatar profile-avatar-placeholder" aria-hidden="true">
            <?php echo h(strtoupper(substr($user->username_usr, 0, 1))); ?>
          </div>
        <?php } ?>
      </div>

      <div class="profile-summary">
        <h1>Member Profile</h1>
        <?php $profile_name = !is_blank($user->display_name_usr ?? null) ? $user->display_name_usr : $user->username_usr; ?>

        <h2><?php echo h($profile_name); ?></h2>

        <?php if (!is_blank($user->display_name_usr ?? null)) { ?>
          <p class="profile-username"><?php echo h($user->username_usr); ?></p>
        <?php } ?>

        <div class="profile-meta">
          <?php if (!is_blank($user->created_at_usr ?? null)) { ?>
            <span><strong>Member since:</strong> <?php echo h(date('F Y', strtotime($user->created_at_usr))); ?></span>
          <?php } ?>

          <?php if (!is_blank($user->last_login_at_usr ?? null)) { ?>
            <span><strong>Last active:</strong> <?php echo h(date('F Y', strtotime($user->last_login_at_usr))); ?></span>
          <?php } ?>

          <span><strong>Public recipes:</strong> <?php echo h($public_recipe_count); ?></span>

          <?php if ($public_recipe_average !== null) { ?>
            <span><strong>Average recipe rating:</strong> <?php echo h(number_format($public_recipe_average, 1)); ?>/5</span>
          <?php } ?>

          <?php if (!is_blank($user->location_usr ?? null)) { ?>
            <span><strong>Location:</strong> <span class="profile-location"><?php echo h($user->location_usr); ?></span></span>
          <?php } ?>
        </div>

        <?php if (!is_blank($user->bio_usr)) { ?>
          <p class="profile-bio"><?php echo h($user->bio_usr); ?></p>
        <?php } ?>
      </div>
    </section>

    <section class="profile-recipes-section">
      <div class="profile-section-heading">
        <h3>Latest Recipes by <?php echo h($user->username_usr); ?></h3>
        <!-- <p><?php echo h($public_recipe_count); ?> public recipe<?php echo $public_recipe_count === 1 ? '' : 's'; ?></p> -->
      </div>

      <?php if (empty($recent_recipes)) { ?>
        <p>This user has not shared any public recipes yet.</p>
      <?php } else { ?>
        <div class="recipe-grid">
          <?php foreach ($recent_recipes as $recipe) {
            $rating = $recipe->rating_display();
            $rating_avg = $rating['avg'];
            $rating_count = $rating['count'];
            $total_time = $recipe->total_time_minutes();
            $image_src = method_exists($recipe, 'first_image_card_src') ? $recipe->first_image_card_src() : null;
            $image_srcset = method_exists($recipe, 'first_image_card_srcset') ? $recipe->first_image_card_srcset() : null;
            $image_sizes = method_exists($recipe, 'first_image_card_sizes') ? $recipe->first_image_card_sizes() : null;
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
                    <?php if ($image_srcset) { ?>srcset="<?php echo h($image_srcset); ?>" <?php } ?>
                    <?php if ($image_sizes) { ?>sizes="<?php echo h($image_sizes); ?>" <?php } ?>
                    width="270"
                    height="270"
                    alt="<?php echo h($recipe->title_rcp); ?>"
                    loading="lazy">
                <?php } else { ?>
                  <img
                    src="<?php echo url_for('/images/recipe-placeholder-270.webp'); ?>"
                    srcset="<?php echo url_for('/images/recipe-placeholder-270.webp'); ?> 270w,
                    <?php echo url_for('/images/recipe-placeholder-540.webp'); ?> 540w"
                    sizes="270px"
                    width="270"
                    height="270"
                    alt="No recipe image."
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
    </section>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
