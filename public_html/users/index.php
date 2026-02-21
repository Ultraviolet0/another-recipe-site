<?php
require_once('../../private/initialize.php');
require_admin_login();
// Find all users
$users = User::find_all();
$page_title = 'Users';
include(SHARED_PATH . '/public_header.php'); ?>

<main id="main-content">
  <div class="members listing">
    <h1>Members</h1>

    <div class="actions">
      <a class="action" href="<?php echo url_for('/users/new.php'); ?>">Add User</a>
    </div>

    <table class="list">
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Member Type</th>
        <th>Status</th>
        <th>Image Path</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
      </tr>

      <?php foreach ($users as $user) { ?>
        <tr>
          <td><?php echo h($user->id_usr); ?></td>
          <td><?php echo h($user->username_usr); ?></td>
          <td><?php echo h($user->email_usr); ?></td>
          <td><?php echo h($user->get_member_type()); ?></td>
          <td><?php echo h($user->status_usr); ?></td>
          <td><?php echo h($user->id_img_usr); ?></td>
          <td><?php echo h($user->created_at_usr); ?></td>
          <td><?php echo h($user->updated_at_usr); ?></td>
          <td><a class="action" href="<?php echo url_for('/users/show.php?id=' . h(u($user->id_usr))); ?>">View</a></td>
          <td><a class="action" href="<?php echo url_for('/users/edit.php?id=' . h(u($user->id_usr))); ?>">Edit</a></td>
          <td><a class="action" href="<?php echo url_for('/users/delete.php?id=' . h(u($user->id_usr))); ?>">Delete</a></td>
        </tr>
      <?php } ?>
    </table>

  </div>

</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
