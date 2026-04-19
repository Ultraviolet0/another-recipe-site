<?php

/** @var User $user */

$selected_role_names = $selected_role_names ?? ['member'];
$is_super_admin = $is_super_admin ?? false;
$form_mode = $form_mode ?? 'new';
$available_role_names = $available_role_names ?? ['member'];
?>

<fieldset>
  <legend>Account</legend>

  <label for="username_usr">Username*</label><br>
  <input
    type="text"
    id="username_usr"
    name="user[username_usr]"
    maxlength="32"
    value="<?php echo h($user->username_usr ?? ''); ?>"
    required><br>

  <label for="display_name_usr">Display Name</label><br>
  <input
    type="text"
    id="display_name_usr"
    name="user[display_name_usr]"
    maxlength="100"
    value="<?php echo h($user->display_name_usr ?? ''); ?>"><br>

  <label for="email_usr">Email*</label><br>
  <input
    type="email"
    id="email_usr"
    name="user[email_usr]"
    maxlength="255"
    value="<?php echo h($user->email_usr ?? ''); ?>"
    required><br>

  <label for="status_usr">Status</label><br>
  <select id="status_usr" name="user[status_usr]">
    <option value="pending" <?php echo (($user->status_usr ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
    <option value="active" <?php echo (($user->status_usr ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
    <option value="disabled" <?php echo (($user->status_usr ?? '') === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
  </select><br>
</fieldset>

<fieldset>
  <legend>Profile</legend>

  <label for="location_usr">Location</label><br>
  <input
    type="text"
    id="location_usr"
    name="user[location_usr]"
    maxlength="100"
    value="<?php echo h($user->location_usr ?? ''); ?>"><br>

  <label for="bio_usr">Bio</label><br>
  <textarea id="bio_usr" name="user[bio_usr]" rows="4"><?php echo h($user->bio_usr ?? ''); ?></textarea><br>

  <?php if ($form_mode === 'edit') { ?>
    <p><strong>Profile Image ID:</strong> <?php echo empty($user->id_img_usr) ? 'None' : h($user->id_img_usr); ?></p>
  <?php } ?>
</fieldset>

<fieldset>
  <legend>Security</legend>

  <label for="password"><?php echo $form_mode === 'edit' ? 'New Password' : 'Password*'; ?></label><br>
  <input
    type="password"
    id="password"
    name="user[password]"
    <?php echo $form_mode === 'new' ? 'required' : ''; ?>><br>

  <?php if ($form_mode === 'edit') { ?>
    <p class="form-help">Leave password fields blank to keep the current password.</p>
  <?php } ?>

  <label for="confirm_password"><?php echo $form_mode === 'edit' ? 'Confirm New Password' : 'Confirm Password*'; ?></label><br>
  <input
    type="password"
    id="confirm_password"
    name="user[confirm_password]"
    <?php echo $form_mode === 'new' ? 'required' : ''; ?>><br>
</fieldset>

<fieldset>
  <legend>Roles</legend>

  <?php if ($is_super_admin) { ?>
    <?php foreach ($available_role_names as $role_name) { ?>
      <?php $input_id = 'role-' . preg_replace('/[^a-z0-9]+/i', '-', $role_name); ?>

      <?php if ($role_name === 'member') { ?>
        <input type="hidden" name="user[role_names][]" value="member">

        <label for="<?php echo h($input_id); ?>">
          <input
            type="checkbox"
            id="<?php echo h($input_id); ?>"
            checked
            disabled>
          <?php echo h(display_title_case($role_name)); ?>
        </label><br>
      <?php } else { ?>
        <label for="<?php echo h($input_id); ?>">
          <input
            type="checkbox"
            id="<?php echo h($input_id); ?>"
            name="user[role_names][]"
            value="<?php echo h($role_name); ?>"
            <?php echo in_array($role_name, $selected_role_names, true) ? 'checked' : ''; ?>>
          <?php echo h(display_title_case($role_name)); ?>
        </label><br>
      <?php } ?>
    <?php } ?>
    <p class="form-help">Super admins can assign roles. Member is assigned to all users automatically.</p>
  <?php } else { ?>
    <input type="hidden" name="user[role_names][]" value="member">
    <p>Member</p>
    <p class="form-help">Regular admins can only create and edit member accounts.</p>
  <?php } ?>
</fieldset>

<?php if ($form_mode === 'edit') { ?>
  <fieldset>
    <legend>User Details</legend>

    <p><strong>User ID:</strong> <?php echo h($user->id_usr); ?></p>
    <p><strong>Top Role:</strong> <?php echo h($user->get_top_role()); ?></p>
    <p><strong>Created:</strong> <?php echo !is_blank($user->created_at_usr ?? null) ? h(date('M j, Y g:i A', strtotime($user->created_at_usr))) : 'N/A'; ?></p>
    <p><strong>Updated:</strong> <?php echo !is_blank($user->updated_at_usr ?? null) ? h(date('M j, Y g:i A', strtotime($user->updated_at_usr))) : 'N/A'; ?></p>
    <p><strong>Last Login:</strong> <?php echo !is_blank($user->last_login_at_usr ?? null) ? h(date('M j, Y g:i A', strtotime($user->last_login_at_usr))) : 'Never'; ?></p>
  </fieldset>
<?php } ?>
