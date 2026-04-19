<?php
require_once('../../../private/initialize.php');

require_admin_login();

$current_user_id = $session->get_user_id();
$user = User::find_by_id($current_user_id);
/** @var User $user */

if (!$user) {
  $session->message('User not found.');
  redirect_to(url_for('/login.php'));
}

$user_search = trim($_GET['user_search'] ?? '');
$status = trim($_GET['status'] ?? '');
$role = trim($_GET['role'] ?? '');

$allowed_statuses = ['', 'pending', 'active', 'disabled'];
$allowed_roles = ['', 'member', 'admin', 'super admin'];

if (!in_array($status, $allowed_statuses, true)) {
  $status = '';
}

if (!in_array($role, $allowed_roles, true)) {
  $role = '';
}

$filters = [
  'user_search' => $user_search,
  'status' => $status,
  'role' => $role
];

$summary = User::admin_summary_counts();
$users = User::admin_find_all($filters);

$page_title = 'User Management';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page admin-users-page">

    <section class="dashboard-hero">
      <h1>Admin Dashboard</h1>
      <h2>Manage Users</h2>
      <p class="dashboard-intro">Search, filter, and manage user accounts across the site.</p>
    </section>

    <section class="dashboard-summary">
      <div class="dashboard-stat-card">
        <h3>Total Users</h3>
        <p><?php echo h($summary['total_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Pending Users</h3>
        <p><?php echo h($summary['pending_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Active Users</h3>
        <p><?php echo h($summary['active_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Disabled Users</h3>
        <p><?php echo h($summary['disabled_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Members</h3>
        <p><?php echo h($summary['member_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Admins</h3>
        <p><?php echo h($summary['admin_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Super Admins</h3>
        <p><?php echo h($summary['super_admin_users']); ?></p>
      </div>

      <div class="dashboard-stat-card">
        <h3>Total Recipes</h3>
        <p><?php echo h($summary['total_recipes']); ?></p>
      </div>
    </section>

    <section class="dashboard-actions">
      <h3>Quick Actions</h3>

      <div class="dashboard-action-grid">
        <a class="dashboard-action-card" href="<?php echo url_for('/admin/users/new.php'); ?>">
          <h4>Create User</h4>
          <p>Add a new user account.</p>
        </a>

        <?php if ($user_search !== '' || $status !== '' || $role !== '') { ?>
          <a class="dashboard-action-card" href="<?php echo url_for('/admin/users'); ?>">
            <h4>Clear Filters</h4>
            <p>Reset the current search and filter settings.</p>
          </a>
        <?php } ?>

        <a class="dashboard-action-card" href="<?php echo url_for('/admin'); ?>">
          <h4>Admin Dashboard</h4>
          <p>Return to the main admin dashboard.</p>
        </a>
      </div>
    </section>

    <section class="dashboard-recent">
      <div class="dashboard-section-heading">
        <h3>Find Users</h3>
      </div>

      <form action="<?php echo url_for('/admin/users/index.php'); ?>" method="get" class="admin-user-filters" id="admin-user-filters">
        <div class="admin-user-filter-row">
          <div class="admin-user-filter-field admin-user-filter-field-search">
            <label for="admin-user-search">Search</label>
            <input type="search" name="user_search" id="admin-user-search" value="<?php echo h($user_search); ?>" placeholder="Username, display name, or email">
          </div>

          <div class="admin-user-filter-field">
            <label for="admin-user-status">Status</label>
            <select name="status" id="admin-user-status">
              <option value="">All statuses</option>
              <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
              <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
              <option value="disabled" <?php echo $status === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
            </select>
          </div>

          <div class="admin-user-filter-field">
            <label for="admin-user-role">Role</label>
            <select name="role" id="admin-user-role">
              <option value="">All roles</option>
              <option value="member" <?php echo $role === 'member' ? 'selected' : ''; ?>>Member</option>
              <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
              <option value="super admin" <?php echo $role === 'super admin' ? 'selected' : ''; ?>>Super Admin</option>
            </select>
          </div>

          <div class="admin-user-filter-actions">
            <button type="submit" class="button admin-user-apply-button">Apply Filters</button>
            <a class="button button-secondary admin-user-reset-link" href="<?php echo url_for('/admin/users/index.php'); ?>">Reset</a>
            <button type="button" class="button button-secondary admin-user-reset-button">Reset</button>
          </div>
        </div>
      </form>
    </section>

    <section class="dashboard-recent" id="admin-user-results">
      <div class="dashboard-section-heading">
        <h3>User Overview</h3>
      </div>

      <?php if (empty($users)) { ?>
        <p>No users matched your current filters.</p>
      <?php } else { ?>
        <div class="admin-user-table-wrap">
          <table class="admin-user-table">
            <thead>
              <tr>
                <th scope="col">User</th>
                <th scope="col">Email</th>
                <th scope="col">Status</th>
                <th scope="col">Role(s)</th>
                <th scope="col">Recipes</th>
                <th scope="col">Created</th>
                <th scope="col">Last Login</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($users as $managed_user) { ?>
                <?php
                $target_user_id = (int)$managed_user['id_usr'];

                $can_edit = false;
                $can_delete = false;

                if ($session->is_super_admin_logged_in()) {
                  $can_edit = true;
                  $can_delete = true;
                } elseif ($session->is_admin_logged_in()) {
                  $can_edit = User::is_member_only($target_user_id);
                }

                $display_name = !is_blank($managed_user['display_name_usr']) ? $managed_user['display_name_usr'] : $managed_user['username_usr'];
                ?>
                <tr>
                  <td>
                    <div class="admin-user-primary">
                      <strong class="admin-user-cell-truncate"><a href="<?php echo url_for('/profile.php?id=' . u($target_user_id)); ?>"><?php echo h($display_name); ?></a></strong>
                      <?php if (!is_blank($managed_user['display_name_usr'])) { ?>
                        <span class="admin-user-cell-truncate">@<?php echo h($managed_user['username_usr']); ?></span>
                      <?php } ?>
                    </div>
                  </td>

                  <td><span class="admin-user-cell-truncate"><?php echo h($managed_user['email_usr']); ?></span></td>
                  <td><?php echo h(display_title_case($managed_user['status_usr'])); ?></td>
                  <td><span class="admin-user-cell-truncate"><?php echo h(display_title_case($managed_user['role_names'] ?: 'member')); ?></span></td>
                  <td><?php echo h((int)$managed_user['recipe_count']); ?></td>
                  <td><?php echo h(date('M j, Y', strtotime($managed_user['created_at_usr']))); ?></td>
                  <td>
                    <?php if (!is_blank($managed_user['last_login_at_usr'])) { ?>
                      <?php echo h(date('M j, Y', strtotime($managed_user['last_login_at_usr']))); ?>
                    <?php } else { ?>
                      Never
                    <?php } ?>
                  </td>

                  <td>
                    <div class="admin-user-row-actions">
                      <?php if ($can_edit) { ?>
                        <a class="button button-secondary" href="<?php echo url_for('/admin/users/edit.php?id=' . u($target_user_id)); ?>">Edit</a>
                      <?php } ?>

                      <?php if ($can_delete) { ?>
                        <a class="button button-danger" href="<?php echo url_for('/admin/users/delete.php?id=' . u($target_user_id)); ?>">Delete</a>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
    </section>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
