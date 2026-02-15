<?php

class User extends DatabaseObject
{
  static protected $table_name = 'user_usr';
  static protected $primary_key = 'id_usr';
  static protected $db_columns = ['id_usr', 'username_usr', 'email_usr', 'password_hash_usr', 'status_usr', 'id_img_usr'];

  // DB Columns
  public $id_usr;
  public $username_usr;
  public $email_usr;
  protected $password_hash_usr;
  public $status_usr;
  public $id_img_usr;
  public $created_at_usr;
  public $updated_at_usr;

  // Not DB Columns (from form)
  public $password;
  public $confirm_password;
  protected $password_required = true;

  public function __construct($args = [])
  {
    $this->username_usr = $args['username_usr'] ?? null;
    $this->email_usr = $args['email_usr'] ?? null;
    $this->status_usr = $args['status_usr'] ?? 'pending';
    $this->id_img_usr = $args['id_img_usr'] ?? null;

    $this->password = $args['password'] ?? null;
    $this->confirm_password = $args['confirm_password'] ?? null;
  }

  protected function set_hashed_password()
  {
    $this->password_hash_usr = password_hash($this->password, PASSWORD_BCRYPT);
  }

  public function verify_password($password) {
    return password_verify($password, $this->password_hash_usr);
  }

  protected function create()
  {
    $this->set_hashed_password();
    $result = parent::create();

    if ($result) {
      $this->assign_role_by_name('member');
    }

    return $result;
  }

  protected function update()
  {
    if ($this->password !== null && $this->password !== '') {
      $this->set_hashed_password();
      // validate password
    } else {
      // password not being updated, skip hashing and validation
      $this->password_required = false;
    }
    return parent::update();
  }

  protected function validate()
  {
    $this->errors = [];

    // Username
    if (is_blank($this->username_usr)) {
      $this->errors[] = "Username cannot be blank.";
    } elseif (!has_length($this->username_usr, array('min' => 3, 'max' => 32))) {
      $this->errors[] = "Username must be between 3 and 32 characters.";
    }

    // Email
    if (is_blank($this->email_usr)) {
      $this->errors[] = "Email cannot be blank.";
    } elseif (!has_length($this->email_usr, array('max' => 255))) {
      $this->errors[] = "Email must be less than 255 characters.";
    } elseif (!has_valid_email_format($this->email_usr)) {
      $this->errors[] = "Email must be a valid format.";
    }

    // Password (only when required)
    if ($this->password_required) {
      if (is_blank($this->password)) {
        $this->errors[] = "Password cannot be blank.";
      } elseif (!has_length($this->password, array('min' => 12))) {
        $this->errors[] = "Password must contain 12 or more characters";
      } elseif (!preg_match('/[A-Z]/', $this->password)) {
        $this->errors[] = "Password must contain at least 1 uppercase letter";
      } elseif (!preg_match('/[a-z]/', $this->password)) {
        $this->errors[] = "Password must contain at least 1 lowercase letter";
      } elseif (!preg_match('/[0-9]/', $this->password)) {
        $this->errors[] = "Password must contain at least 1 number";
      } elseif (!preg_match('/[^A-Za-z0-9\s]/', $this->password)) {
        $this->errors[] = "Password must contain at least 1 symbol";
      }

      if (is_blank($this->confirm_password)) {
        $this->errors[] = "Confirm password cannot be blank.";
      } elseif ($this->password !== $this->confirm_password) {
        $this->errors[] = "Password and confirm password must match.";
      }
    }

    return $this->errors;
  }

  static public function find_by_username($username)
  {
    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= "WHERE username_usr='" . self::$database->escape_string($username) . "' ";
    $sql .= "LIMIT 1";

    $object_array = static::find_by_sql($sql);
    //if (!empty($object_array)) {
    //  return array_shift($object_array);
    //} else {
    //  return false;
    //}
    // Above improved with ternary operator:
    return !empty($object_array) ? array_shift($object_array) : false;
  }

  // Roles Functions

  public function role_names()
  {
    if (!isset($this->id_usr)) { return []; }

    $sql = "SELECT r.name_rol ";
    $sql .= "FROM user_role_usrrol ur ";
    $sql .= "JOIN role_rol r ON ur.id_rol_usrrol = r.id_rol ";
    $sql .= "WHERE ur.id_usr_usrrol = '" . self::$database->escape_string($this->id_usr) . "'";

    $result = self::$database->query($sql);
    if (!$result) { return []; }

    $roles = [];
    while ($row = $result->fetch_assoc()) {
      $roles[] = $row['name_rol'];
    }
    $result->free();

    return $roles;
  }

  public function has_role($role_name)
  {
    $role_name = strtolower($role_name);
    $roles = array_map('strtolower', $this->role_names());
    return in_array($role_name, $roles, true);
  }

  public function assign_role_by_name($role_name)
  {
    if (!isset($this->id_usr)) { return false; }

    $role_name_esc = self::$database->escape_string($role_name);

    // Find role id
    $sql = "SELECT id_rol ";
    $sql .= "FROM role_rol ";
    $sql .= "WHERE name_rol='{$role_name_esc}' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);
    if (!$result) { return false; }

    $row = $result->fetch_assoc();
    $result->free();
    if (!$row) { return false; }

    $role_id = $row['id_rol'];

    $sql = "INSERT IGNORE INTO user_role_usrrol (id_usr_usrrol, id_rol_usrrol) ";
    $sql .= "VALUES ('" . self::$database->escape_string($this->id_usr) . "', '{$role_id}')";

    $result = self::$database->query($sql);
    return $result;
  }
}
