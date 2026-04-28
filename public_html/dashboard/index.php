<?php
require_once('../../private/initialize.php');

require_login();

$current_user_id = $session->get_user_id();
$user = User::find_by_id($current_user_id);
/** @var User $user */

if (!$user) {
  $session->message('User not found.');
  redirect_to(url_for('/login.php'));
}

$total_recipes = 0;
$public_recipes = 0;
$unlisted_recipes = 0;
$private_recipes = 0;
$recent_recipes = [];

$total_recipes = Recipe::count_by_user_id($current_user_id);
$public_recipes = Recipe::count_by_user_id_and_privacy($current_user_id, 'public');
$unlisted_recipes = Recipe::count_by_user_id_and_privacy($current_user_id, 'unlisted');
$private_recipes = Recipe::count_by_user_id_and_privacy($current_user_id, 'private');
$recent_recipes = Recipe::find_by_user_id($current_user_id, 5);

$ai_recommendations = $_SESSION['dashboard_ai_recommendations'] ?? '';
$ai_recommendations_error = '';

if (is_post_request()) {
  $action = $_POST['action'] ?? '';

  if ($action === 'generate_ai_recommendations') {
    $ai_result = generate_dashboard_ai_recommendations($current_user_id, $user->username_usr);

    if ($ai_result['ok']) {
      $ai_recommendations = trim($ai_result['content']);
      $_SESSION['dashboard_ai_recommendations'] = $ai_recommendations;
    } else {
      $ai_recommendations_error = $ai_result['error'];
    }
  } elseif ($action === 'clear_ai_recommendations') {
    unset($_SESSION['dashboard_ai_recommendations']);
    $ai_recommendations = '';
  }
}

$page_title = 'Dashboard';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page">

    <section class="dashboard-hero">
      <h1>Dashboard</h1>
      <h2>Welcome back, <?php echo h($user->username_usr); ?>.</h2>
      <p class="dashboard-intro">Manage your recipes, update your profile, and keep track of your account activity from one place.</p>
    </section>

    <section class="dashboard-summary">
      <div class="dashboard-stat-card">
        <h3>Total Recipes</h3>
        <p><?php echo h($total_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Public</h3>
        <p><?php echo h($public_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Unlisted</h3>
        <p><?php echo h($unlisted_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Private</h3>
        <p><?php echo h($private_recipes); ?></p>
      </div>
    </section>

    <section class="dashboard-actions">
      <h3>Quick Actions</h3>

      <div class="dashboard-action-grid">
        <a class="dashboard-action-card" href="<?php echo url_for('/recipes/new.php'); ?>">
          <h4>Add Recipe</h4>
          <p>Create and publish a new recipe.</p>
        </a>

        <a class="dashboard-action-card" href="<?php echo url_for('/dashboard/recipes.php'); ?>">
          <h4>My Recipes</h4>
          <p>View, edit, and manage your recipes.</p>
        </a>

        <a class="dashboard-action-card" href="<?php echo url_for('/dashboard/profile.php'); ?>">
          <h4>Edit Profile</h4>
          <p>Update your profile image and public details.</p>
        </a>

        <a class="dashboard-action-card" href="<?php echo url_for('/profile.php?id=' . u($user->id_usr)); ?>">
          <h4>View Public Profile</h4>
          <p>See how your profile appears to other users.</p>
        </a>

        <?php if ($session->is_admin_logged_in()) { ?>
          <a class="dashboard-action-card" href="<?php echo url_for('/admin'); ?>">
            <h4>Admin Dashboard</h4>
            <p>View, edit, and manage administrative features.</p>
          </a>
        <?php } ?>
      </div>
    </section>

    <section id="dashboard-ai-recommendations">
      <h3>AI Recommendations</h3>
      <p>Generate recipe ideas based on the recipes you have already created.</p>

      <form action="<?php echo url_for('/dashboard/index.php'); ?>" method="post" class="dashboard-ai-form">
        <button
          type="submit"
          class="button dashboard-ai-generate-button"
          name="action"
          value="generate_ai_recommendations"
          <?php echo $total_recipes < 1 ? 'disabled' : ''; ?>>
          Generate Recommendations
        </button>

        <?php if ($ai_recommendations !== '') { ?>
          <button
            type="submit"
            class="button button-secondary"
            name="action"
            value="clear_ai_recommendations">
            Clear
          </button>
        <?php } ?>
      </form>

      <?php if ($total_recipes < 1) { ?>
        <p class="form-help">Add at least one recipe first to generate AI recommendations.</p>
      <?php } ?>

      <?php if ($ai_recommendations_error !== '') { ?>
        <div class="errors">
          <p><?php echo h($ai_recommendations_error); ?></p>
        </div>
      <?php } ?>

      <?php if ($ai_recommendations !== '') { ?>
        <div class="dashboard-ai-output">
          <?php echo format_dashboard_ai_recommendations_html($ai_recommendations); ?>
        </div>
      <?php } ?>
    </section>

    <section class="dashboard-recent">
      <div class="dashboard-section-heading">
        <h3>Recent Recipes</h3>
        <a class="button button-secondary" href="<?php echo url_for('/dashboard/recipes.php'); ?>">View All</a>
      </div>

      <?php if (empty($recent_recipes)) { ?>
        <p>You have not created any recipes yet.</p>
      <?php } else { ?>
        <div class="dashboard-recipe-list">
          <?php foreach ($recent_recipes as $recipe) { ?>
            <article class="dashboard-recipe-item">
              <div class="dashboard-recipe-main">
                <h4>
                  <a href="<?php echo url_for('/recipes/show.php?id=' . u($recipe->id_rcp)); ?>">
                    <?php echo h($recipe->title_rcp); ?>
                  </a>
                </h4>

                <p class="dashboard-recipe-meta">
                  <span><strong>Privacy:</strong> <?php echo h(display_title_case($recipe->privacy_rcp)); ?></span>
                  <span><strong>Updated:</strong> <?php echo h(date('M j, Y', strtotime($recipe->updated_at_rcp))); ?></span>
                </p>
              </div>

              <div class="dashboard-recipe-actions">
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
