<?php
// prevents this code from being loaded directly in the browser
// or without first setting the necessary object
if (!isset($user)) {
  redirect_to(url_for('/users/index.php'));
}
?>

<dl>
  <dt>Username</dt>
  <dd><input type="text" name="user[username_usr]" value="<?php echo h($user->username_usr); ?>"></dd>
</dl>

<dl>
  <dt>Email</dt>
  <dd><input type="text" name="user[email_usr]" value="<?php echo h($user->email_usr); ?>"></dd>
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
    <dt>User Type</dt>
    <dd>
      <select name="user[member_type]">
        <?php if ($user->member_type === 'm') { ?>
          <option value="m" selected="selected">Member</option>
        <?php } else { ?>
          <option value="m">Member</option>
        <?php } ?>
        <?php if ($user->member_type === 'a') { ?>
          <option value="a" selected="selected">Admin</option>
        <?php } else { ?>
          <option value="a">Admin</option>
        <?php } ?>
      </select>
    </dd>
  </dl>
<?php } else { ?>
  <input type="hidden" name="user[member_type]" value="m">
<?php } ?>
