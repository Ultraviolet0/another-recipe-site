<?php
require_once('../../private/initialize.php');

require_admin_login();

$current_user_id = $session->get_user_id();
$user = User::find_by_id($current_user_id);
/** @var User $user */

if (!$user) {
  $session->message('User not found.');
  redirect_to(url_for('/login.php'));
}

$total_users = 0;
$pending_users = 0;
$active_users = 0;
$total_recipes = 0;

$total_users = User::count_all();
$pending_users = User::count_by_status('pending');
$active_users = User::count_by_status('active');
$disabled_users = User::count_by_status('disabled');

$total_recipes = Recipe::count_all();
$public_recipes = Recipe::count_by_privacy('public');
$unlisted_recipes = Recipe::count_by_privacy('unlisted');
$private_recipes = Recipe::count_by_privacy('private');

$recent_activity = build_admin_recent_activity(8);

$page_title = 'Admin Dashboard';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page">

    <section class="dashboard-hero">
      <h1>Admin Dashboard</h1>
      <h2>Welcome back, <?php echo h($user->username_usr); ?>.</h2>
      <p class="dashboard-intro">Manage users, categories, and site-wide content from one place.</p>
    </section>

    <section class="dashboard-summary">
      <div class="dashboard-stat-card">
        <h3>Total Users</h3>
        <p><?php echo h($total_users); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Pending Users</h3>
        <p><?php echo h($pending_users); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Active Users</h3>
        <p><?php echo h($active_users); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Disabled Users</h3>
        <p><?php echo h($disabled_users); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Total Recipes</h3>
        <p><?php echo h($total_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Public Recipes</h3>
        <p><?php echo h($public_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Unlisted Recipes</h3>
        <p><?php echo h($unlisted_recipes); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Private Recipes</h3>
        <p><?php echo h($private_recipes); ?></p>
      </div>
    </section>

    <section class="dashboard-actions">
      <h3>Quick Actions</h3>

      <div class="dashboard-action-grid">
        <a class="dashboard-action-card" href="<?php echo url_for('/admin/users'); ?>">
          <h4>Manage Users</h4>
          <p>Activate, disable, and review user accounts.</p>
        </a>

        <a class="dashboard-action-card" href="<?php echo url_for('/admin/categories.php'); ?>">
          <h4>Manage Categories</h4>
          <p>Update meal types, cuisines, dietary styles, and badges.</p>
        </a>

        <a class="dashboard-action-card" href="<?php echo url_for('/dashboard'); ?>">
          <h4>Personal Dashboard</h4>
          <p>Go to your personal dashboard and account tools.</p>
        </a>
      </div>
    </section>

    <section class="dashboard-recent">
      <div class="dashboard-section-heading">
        <h3>Recent Activity</h3>
      </div>

      <?php if (empty($recent_activity)) { ?>
        <p>No recent activity found.</p>
      <?php } else { ?>
        <div class="dashboard-activity-list">
          <?php foreach ($recent_activity as $activity) { ?>
            <article class="dashboard-activity-item">
              <div class="dashboard-activity-main">
                <h4><?php echo h($activity['title']); ?></h4>

                <p class="dashboard-activity-meta">
                  <span><strong>Type:</strong> <?php echo h($activity['type']); ?></span>

                  <?php foreach ($activity['meta'] as $meta_item) { ?>
                    <span><?php echo $meta_item; ?></span>
                  <?php } ?>
                </p>
              </div>

              <?php if (!empty($activity['action_url']) && !empty($activity['action_label'])) { ?>
                <div class="dashboard-activity-actions">
                  <a class="button button-secondary" href="<?php echo h($activity['action_url']); ?>">
                    <?php echo h($activity['action_label']); ?>
                  </a>
                </div>
              <?php } ?>
            </article>
          <?php } ?>
        </div>
      <?php } ?>
    </section>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
