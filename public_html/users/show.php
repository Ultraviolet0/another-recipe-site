<?php
require_once('../../private/initialize.php');
require_admin_login();

$id = $_GET['id'] ?? '1'; // PHP > 7.0

$user = User::find_by_id($id);

$page_title = 'Show User: ' . h($user->username_usr);
include(SHARED_PATH . '/public_header.php'); ?>

<main id="main-content">

  <a class="back-link" href="<?php echo url_for('/users/index.php'); ?>">&laquo; Back to List</a>

  <div class="user show">

    <h1>User: <?php echo h($user->username_usr); ?></h1>

    <div class="attributes">
      <dl>
        <dt>ID</dt>
        <dd><?php echo h($user->id_usr); ?></dd>
      </dl>
      <dl>
        <dt>Username</dt>
        <dd><?php echo h($user->username_usr); ?></dd>
      </dl>
      <dl>
        <dt>Email</dt>
        <dd><?php echo h($user->email_usr); ?></dd>
      </dl>
      <dl>
        <dt>Member Roles</dt>
        <dd><?php echo h($user->get_member_roles()); ?></dd>
      </dl>
      <dl>
        <dt>Status</dt>
        <dd><?php echo h($user->status_usr); ?></dd>
      </dl>
      <dl>
        <dt>Profile Image</dt>
        <dd><?php echo h($user->get_profile_image()); ?></dd>
      </dl>
      <dl>
        <dt>Created At</dt>
        <dd><?php echo h($user->created_at_usr); ?></dd>
      </dl>
      <dl>
        <dt>Updated At</dt>
        <dd><?php echo h($user->updated_at_usr); ?></dd>
      </dl>
    </div>

  </div>

</main>
