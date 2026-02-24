<?php
// prevents this code from being loaded directly in the browser
// or without first setting the necessary object
if (!isset($user)) {
  redirect_to(url_for('/'));
}
?>

<dl>
  <dt>Email</dt>
  <dd><input type="text" name="user[email_usr]" value="<?php echo h($user->email_usr); ?>"></dd>
</dl>

<dl>
  <dt>Username</dt>
  <dd><input type="text" name="user[username_usr]" value="<?php echo h($user->username_usr); ?>"></dd>
</dl>

<dl>
  <dt>Password</dt>
  <dd><input type="password" name="user[password]" value=""></dd>
</dl>

<dl>
  <dt>Confirm Password</dt>
  <dd><input type="password" name="user[confirm_password]" value=""></dd>
</dl>

<?php if ($session->is_admin_logged_in()) { ?>
  <dl>
    <dt>Status</dt>
    <dd>
      <select name="user[status_usr]">
        <?php
          $statuses = ['pending' => 'Pending', 'active' => 'Active', 'disabled' => 'Disabled'];
          foreach ($statuses as $value => $label) {
            $selected = ($user->status_usr === $value) ? 'selected="selected"' : '';
            echo '<option value="' . h($value) . '" ' . $selected . '>' . h($label) . '</option>';
          }
        ?>
      </select>
    </dd>
  </dl>
<?php } ?>

<?php if ($session->is_super_admin_logged_in()) { ?>

  <?php
    $all_roles = Role::find_all();
    $current_roles = $user->role_names();
  ?>

  <dl>
    <dt>Roles</dt>
    <dd>
      <select name="user[roles][]" multiple="multiple" size="<?php echo count($all_roles); ?>">
        <?php foreach($all_roles as $role) {
          $selected = in_array($role->name_rol, $current_roles) ? 'selected="selected"' : '';
          echo '<option value="' . h($role->name_rol) . '" ' . $selected . '>' . h($role->name_rol) . '</option>';
        } ?>
      </select>
      <span>Hold Ctrl (Windows) / Cmd (Mac) to select multiple.</span>
    </dd>
  </dl>

<?php } ?>
