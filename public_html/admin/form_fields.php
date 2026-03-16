<?php
// prevents this code from being loaded directly in the browser
// or without first setting the necessary object
if (!isset($user)) {
  redirect_to(url_for('/'));
}
?>

<label for="email">Email</label><br>
<input type="text" id="email" name="user[email_usr]" value="<?php echo h($user->email_usr); ?>"><br>

<label for="username">Username</label><br>
<input type="text" id="username" name="user[username_usr]" value="<?php echo h($user->username_usr); ?>"><br>

<label for="password">Password</label><br>
<input type="password" id="password" name="user[password]" value=""><br>

<p class="form-help">Password must contain at least 12 characters, including an uppercase letter, lowercase letter, number, and symbol.</p>

<label for="confirm-password">Confirm Password</label><br>
<input type="password" id="confirm-password" name="user[confirm_password]" value=""><br>

<?php if ($session->is_admin_logged_in()) { ?>
  <label for="user-status">Status</label><br>
  <select id="user-status" name="user[status_usr]">
    <?php
    $statuses = ['pending' => 'Pending', 'active' => 'Active', 'disabled' => 'Disabled'];
    foreach ($statuses as $value => $label) {
      $selected = ($user->status_usr === $value) ? 'selected="selected"' : '';
      echo '<option value="' . h($value) . '" ' . $selected . '>' . h($label) . '</option>';
    }
    ?>
  </select><br>
<?php } ?>

<?php if ($session->is_super_admin_logged_in()) { ?>

  <?php
  $all_roles = Role::find_all();
  $current_roles = $user->get_role_names();
  ?>

  <label for="user-roles">Roles</label><br>
  <select id="user-roles" name="user[roles][]" multiple="multiple" size="<?php echo count($all_roles); ?>">
    <?php foreach ($all_roles as $role) {
      $selected = in_array($role->name_rol, $current_roles) ? 'selected="selected"' : '';
      echo '<option value="' . h($role->name_rol) . '" ' . $selected . '>' . h($role->name_rol) . '</option>';
    } ?>
  </select><br>
  <p class="form-help">Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</p>

<?php } ?>
