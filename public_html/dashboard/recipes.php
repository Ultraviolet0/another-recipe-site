<?php
require_once('../../private/initialize.php');

require_login();

$current_user_id = $session->get_user_id();
$user = User::find_by_id($current_user_id);

if (!$user) {
  $session->message('User not found.');
  redirect_to(url_for('/login.php'));
}

$privacy_filter = $_GET['privacy'] ?? '';
$valid_privacies = ['public', 'unlisted', 'private'];
if (!in_array($privacy_filter, $valid_privacies, true)) {
  $privacy_filter = '';
}

$recipes = [];
if (method_exists('Recipe', 'find_by_user_id_filtered')) {
  $recipes = Recipe::find_by_user_id_filtered($current_user_id, $privacy_filter);
} elseif (method_exists('Recipe', 'find_by_user_id')) {
  $recipes = Recipe::find_by_user_id($current_user_id, 100);
}

$page_title = 'My Recipes';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page dashboard-recipes-page">

    <section class="dashboard-hero">
      <h1>Dashboard</h1>
      <h2>My Recipes</h2>
      <p class="dashboard-intro">View, edit, and manage the recipes you've created.</p>
    </section>

    <section class="dashboard-toolbar">
      <div class="dashboard-toolbar-group">
        <a class="button" href="<?php echo url_for('/recipes/new.php'); ?>">Add Recipe</a>
        <a class="button button-secondary" href="<?php echo url_for('/dashboard'); ?>">Back to Dashboard</a>
      </div>

      <form class="dashboard-filter-form" action="<?php echo url_for('/dashboard/recipes.php'); ?>" method="get">
        <label for="privacy-filter">Filter by privacy</label>
        <select name="privacy" id="privacy-filter" onchange="this.form.submit()">
          <option value="">All</option>
          <option value="public" <?php echo $privacy_filter === 'public' ? 'selected' : ''; ?>>Public</option>
          <option value="unlisted" <?php echo $privacy_filter === 'unlisted' ? 'selected' : ''; ?>>Unlisted</option>
          <option value="private" <?php echo $privacy_filter === 'private' ? 'selected' : ''; ?>>Private</option>
        </select>
        <noscript>
          <button type="submit" class="button button-secondary">Apply</button>
        </noscript>
      </form>
    </section>

    <section class="dashboard-recipes-list-section">
      <div class="dashboard-section-heading">
        <h3>
          <?php
          if ($privacy_filter !== '') {
            echo h(display_title_case($privacy_filter)) . ' Recipes';
          } else {
            echo 'All Recipes';
          }
          ?>
        </h3>
        <p><?php echo h(count($recipes)); ?> recipe<?php echo count($recipes) === 1 ? '' : 's'; ?></p>
      </div>

      <?php if (empty($recipes)) { ?>
        <div class="dashboard-empty-state">
          <p>
            <?php if ($privacy_filter !== '') { ?>
              You do not have any <?php echo h($privacy_filter); ?> recipes yet.
            <?php } else { ?>
              You have not created any recipes yet.
            <?php } ?>
          </p>
          <a class="button" href="<?php echo url_for('/recipes/new.php'); ?>">Create Your First Recipe</a>
        </div>
      <?php } else { ?>
        <div class="dashboard-recipe-list">
          <?php foreach ($recipes as $recipe) {
            $rating = $recipe->rating_display();
            $rating_avg = $rating['avg'];
            $rating_count = $rating['count'];
            $total_time = $recipe->total_time_minutes();
            $badge_name = $recipe->badge_name();
          ?>
            <article class="dashboard-recipe-item dashboard-recipe-item-rich">
              <div class="dashboard-recipe-main">
                <h4><a href="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>" title="<?php echo h($recipe->title_rcp); ?>"><?php echo h($recipe->title_rcp); ?></a></h4>

                <?php if (!is_blank($recipe->description_rcp)) { ?>
                  <p class="dashboard-recipe-description"><?php echo h($recipe->description_rcp); ?></p>
                <?php } ?>

                <p class="dashboard-recipe-meta">
                  <span><strong>Privacy:</strong> <?php echo h(display_title_case($recipe->privacy_rcp)); ?></span>
                  <span><strong>Total time:</strong> <?php echo h($total_time); ?> min</span>
                  <span><strong>Rating:</strong> <?php echo $rating_avg === null ? '—' : h(number_format($rating_avg, 1)); ?> (<?php echo h($rating_count); ?>)</span>
                  <span><strong>Updated:</strong> <?php echo h(date('M j, Y', strtotime($recipe->updated_at_rcp))); ?></span>
                  <?php if (!is_blank($badge_name)) { ?>
                    <span><strong>Badge:</strong> <?php echo h(display_title_case($badge_name)); ?></span>
                  <?php } ?>
                </p>
              </div>

              <div class="dashboard-recipe-actions">
                <a class="button button-secondary" href="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>">View</a>
                <a class="button button-secondary" href="<?php echo url_for('/recipes/edit.php?id=' . u($recipe->id_rcp)); ?>">Edit</a>
                <a class="button button-danger" href="<?php echo url_for('/recipes/delete.php?id=' . u($recipe->id_rcp)); ?>">Delete</a>
              </div>
            </article>
          <?php } ?>
        </div>
      <?php } ?>
    </section>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
