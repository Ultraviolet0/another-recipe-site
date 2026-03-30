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

if (!property_exists($user, 'bio_usr')) {
  $user->bio_usr = '';
}
if (!property_exists($user, 'display_name_usr')) {
  $user->display_name_usr = '';
}
if (!property_exists($user, 'location_usr')) {
  $user->location_usr = '';
}

if (is_post_request()) {
  $args = $_POST['user'] ?? [];

  $user->display_name_usr = blank_to_null($args['display_name_usr'] ?? null);
  $user->bio_usr = blank_to_null($args['bio_usr'] ?? null);
  $user->location_usr = blank_to_null($args['location_usr'] ?? null);

  $result = $user->save();

  if ($result) {
    $session->message('Profile updated.');
    redirect_to(url_for('/dashboard/profile.php'));
  }
}

$profile_image_url = null;
if (method_exists($user, 'profile_image_url')) {
  $profile_image_url = $user->profile_image_url();
}

$page_title = 'Edit Profile';
include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page dashboard-profile-page">

    <section class="dashboard-hero">
      <h1>Dashboard</h1>
      <h2>Edit Profile</h2>
      <p class="dashboard-intro">Update the public parts of your profile. Everything here is optional except your account basics.</p>
    </section>

    <?php echo display_errors($user->errors); ?>

    <div class="dashboard-profile-layout">
      <section class="dashboard-profile-card">
        <h3>Profile Preview</h3>

        <div class="dashboard-profile-preview">
          <div class="profile-avatar-wrap">
            <?php if ($profile_image_url) { ?>
              <img
                class="profile-avatar"
                src="<?php echo h($profile_image_url); ?>"
                alt="<?php echo h($user->username_usr); ?> profile picture">
            <?php } else { ?>
              <div class="profile-avatar profile-avatar-placeholder" aria-hidden="true">
                <?php echo h(strtoupper(substr($user->username_usr, 0, 1))); ?>
              </div>
            <?php } ?>
          </div>

          <div class="dashboard-profile-preview-text">
            <h4><?php echo h($user->username_usr); ?></h4>

            <?php if (!is_blank($user->display_name_usr)) { ?>
              <p><strong>Name:</strong> <?php echo h($user->display_name_usr); ?></p>
            <?php } ?>

            <?php if (!is_blank($user->location_usr)) { ?>
              <p><strong>Location:</strong> <?php echo h($user->location_usr); ?></p>
            <?php } ?>

            <?php if (!is_blank($user->bio_usr)) { ?>
              <p class="dashboard-profile-preview-bio"><?php echo h($user->bio_usr); ?></p>
            <?php } else { ?>
              <p class="dashboard-profile-preview-bio dashboard-profile-preview-empty">
                No bio added yet.
              </p>
            <?php } ?>
          </div>
        </div>

        <div class="dashboard-profile-preview-actions">
          <a class="button button-secondary" href="<?php echo url_for('/profile.php?id=' . u($user->id_usr)); ?>">
            View Public Profile
          </a>
        </div>
      </section>

      <section class="dashboard-profile-form-section">
        <form action="<?php echo url_for('/dashboard/profile.php'); ?>" method="post" class="recipe-form">

          <fieldset>
            <legend>Account Basics</legend>

            <label for="username-readonly">Username</label>
            <input type="text" id="username-readonly" value="<?php echo h($user->username_usr); ?>" disabled>

            <label for="email-readonly">Email</label>
            <input type="email" id="email-readonly" value="<?php echo h($user->email_usr); ?>" disabled>

            <p class="form-help">Username and email cannot be changed.</p>
          </fieldset>

          <fieldset>
            <legend>Public Profile Details</legend>

            <label for="display-name">Display Name</label>
            <input type="text" id="display-name" name="user[display_name_usr]" value="<?php echo h($user->display_name_usr); ?>" maxlength="100">
            <p class="form-help">Optional. Leave blank if you prefer to use only your username publicly.</p>

            <label for="location">Location</label>
            <input type="text" id="location" name="user[location_usr]" value="<?php echo h($user->location_usr); ?>" maxlength="100">
            <p class="form-help">Optional. Keep this general if you choose to share it.</p>

            <label for="bio">Bio</label>
            <textarea id="bio" name="user[bio_usr]" rows="5" maxlength="500"><?php echo h($user->bio_usr); ?></textarea>
            <p class="form-help">Optional. A short cooking-focused intro works best here.</p>
          </fieldset>

          <div class="form-actions">
            <button type="submit" class="button">Save Profile</button>
            <a class="button button-secondary" href="<?php echo url_for('/dashboard'); ?>">Back to Dashboard</a>
          </div>
        </form>
      </section>

    </div>
  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
