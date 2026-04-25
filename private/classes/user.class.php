<?php

class User extends DatabaseObject
{
  static protected $table_name = 'user_usr';
  static protected $primary_key = 'id_usr';
  static protected $db_columns = ['id_usr', 'username_usr', 'display_name_usr', 'email_usr', 'password_hash_usr', 'status_usr', 'id_img_usr', 'last_login_at_usr', 'bio_usr', 'location_usr'];

  // DB Columns
  public $id_usr;
  public $username_usr;
  public $display_name_usr;
  public $email_usr;
  protected $password_hash_usr;
  public $status_usr;
  public $id_img_usr;
  public $created_at_usr;
  public $updated_at_usr;
  public $last_login_at_usr;
  public $bio_usr;
  public $location_usr;

  // Not DB Columns (from form)
  public $password;
  public $confirm_password;
  protected $password_required = true;
  protected $roles = null;

  public function __construct($args = [])
  {
    $this->username_usr = $args['username_usr'] ?? null;
    $this->email_usr = $args['email_usr'] ?? null;
    $this->status_usr = $args['status_usr'] ?? 'pending';
    $this->id_img_usr = $args['id_img_usr'] ?? null;
    $this->last_login_at_usr = $args['last_login_at_usr'] ?? null;

    $this->password = $args['password'] ?? null;
    $this->confirm_password = $args['confirm_password'] ?? null;
  }

  protected function set_hashed_password()
  {
    $this->password_hash_usr = password_hash($this->password, PASSWORD_BCRYPT);
  }

  public function verify_password($password)
  {
    return password_verify($password, $this->password_hash_usr);
  }

  protected function create()
  {
    $this->set_hashed_password();
    $result = parent::create();

    if ($result) {
      $this->add_role_by_name('member');
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
    } else {
      $existing_user = static::find_by_username($this->username_usr);
      if ($existing_user && (!isset($this->id_usr) || $existing_user->id_usr != $this->id_usr)) {
        $this->errors[] = "Username is already taken.";
      }
    }

    // Email
    if (is_blank($this->email_usr)) {
      $this->errors[] = "Email cannot be blank.";
    } elseif (!has_length($this->email_usr, array('max' => 255))) {
      $this->errors[] = "Email must be less than 255 characters.";
    } elseif (!has_valid_email_format($this->email_usr)) {
      $this->errors[] = "Email must be a valid format.";
    } else {
      $existing_email = static::find_by_email($this->email_usr);
      if ($existing_email && (!isset($this->id_usr) || $existing_email->id_usr != $this->id_usr)) {
        $this->errors[] = "Email is already in use.";
      }
    }

    // Password (only when required)
    if ($this->password_required) {
      if (is_blank($this->password)) {
        $this->errors[] = "Password cannot be blank.";
      } else {
        if (!has_length($this->password, array('min' => 8))) {
          $this->errors[] = "Password must contain 8 or more characters.";
        }
        if (!preg_match('/[A-Z]/', $this->password)) {
          $this->errors[] = "Password must contain at least 1 uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $this->password)) {
          $this->errors[] = "Password must contain at least 1 lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $this->password) && !preg_match('/[^A-Za-z0-9\s]/', $this->password)) {
          $this->errors[] = "Password must contain at least 1 number or symbol.";
        }
        // if (!preg_match('/[0-9]/', $this->password)) {
        //   $this->errors[] = "Password must contain at least 1 number.";
        // }
        // if (!preg_match('/[^A-Za-z0-9\s]/', $this->password)) {
        //   $this->errors[] = "Password must contain at least 1 symbol.";
        // }
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

  static public function find_by_email($email)
  {
    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= "WHERE email_usr='" . self::$database->escape_string($email) . "' ";
    $sql .= "LIMIT 1";
    $object_array = static::find_by_sql($sql);
    return !empty($object_array) ? array_shift($object_array) : false;
  }

  static public function count_by_status($status)
  {
    $status = static::$database->escape_string($status);
    $sql = "SELECT COUNT(*) FROM " . static::$table_name;
    $sql .= " WHERE status_usr='" . $status . "'";
    return static::$database->query($sql)->fetch_row()[0] ?? 0;
  }

  // Roles Functions

  public static function all_role_names()
  {
    $sql = "SELECT name_rol ";
    $sql .= "FROM role_rol ";
    $sql .= "ORDER BY FIELD(name_rol, 'member', 'admin', 'super admin')";

    $result = static::$database->query($sql);
    if (!$result) {
      return [];
    }

    $roles = [];
    while ($row = $result->fetch_assoc()) {
      $roles[] = $row['name_rol'];
    }
    $result->free();

    return $roles;
  }

  public function get_role_names()
  {
    if (!isset($this->id_usr)) {
      return [];
    }

    if (is_array($this->roles)) {
      return $this->roles;
    } // already loaded

    $sql = "SELECT r.name_rol ";
    $sql .= "FROM user_role_usrrol ur ";
    $sql .= "JOIN role_rol r ON ur.id_rol_usrrol = r.id_rol ";
    $sql .= "WHERE ur.id_usr_usrrol = '" . self::$database->escape_string($this->id_usr) . "'";

    $result = self::$database->query($sql);
    if (!$result) {
      return [];
    }

    $roles = [];
    while ($row = $result->fetch_assoc()) {
      $roles[] = $row['name_rol'];
    }
    $result->free();

    return $this->roles = $roles;
  }

  public function has_role($role_name)
  {
    $role_name = strtolower($role_name);
    $roles = array_map('strtolower', $this->get_role_names());
    return in_array($role_name, $roles, true);
  }

  public static function has_role_by_user_id($user_id, $role_name)
  {
    $user_id = (int)$user_id;
    if ($user_id < 1) {
      return false;
    }

    $role_name = static::$database->escape_string(trim($role_name));

    $sql = "SELECT 1 ";
    $sql .= "FROM user_role_usrrol ur ";
    $sql .= "INNER JOIN role_rol r ON ur.id_rol_usrrol = r.id_rol ";
    $sql .= "WHERE ur.id_usr_usrrol = {$user_id} ";
    $sql .= "AND r.name_rol = '{$role_name}' ";
    $sql .= "LIMIT 1";

    $result = static::$database->query($sql);
    if (!$result) {
      return false;
    }

    $has_role = $result->num_rows > 0;
    $result->free();

    return $has_role;
  }

  public function get_top_role()
  {
    if ($this->has_role('super admin')) {
      return 'Super Admin';
    } elseif ($this->has_role('admin')) {
      return 'Admin';
    } else {
      return 'Member';
    }
  }

  public static function is_member_only($user_id)
  {
    return !static::has_role_by_user_id($user_id, 'admin')
      && !static::has_role_by_user_id($user_id, 'super admin');
  }

  public function add_role_by_name($role_name)
  {
    if (!isset($this->id_usr)) {
      return false;
    }

    $role_name_esc = self::$database->escape_string($role_name);

    // Find role id
    $sql = "SELECT id_rol ";
    $sql .= "FROM role_rol ";
    $sql .= "WHERE name_rol='{$role_name_esc}' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);
    if (!$result) {
      return false;
    }

    $row = $result->fetch_assoc();
    $result->free();
    if (!$row) {
      return false;
    }

    $role_id = $row['id_rol'];

    $sql = "INSERT IGNORE INTO user_role_usrrol (id_usr_usrrol, id_rol_usrrol) ";
    $sql .= "VALUES ('" . self::$database->escape_string($this->id_usr) . "', '{$role_id}')";

    $result = self::$database->query($sql);

    if ($result) {
      if (is_array($this->roles)) {
        $this->roles[] = $role_name;
        $this->roles = array_values(array_unique($this->roles));
      }
    }

    if (!$result) {
      return false;
    }

    return true;
  }

  public function set_role_names($role_names)
  {
    if (!isset($this->id_usr)) {
      return false;
    }

    // convert passed strings to array
    if (!is_array($role_names)) {
      $role_names = [$role_names];
    }

    // normalize
    $clean_role_names = [];
    foreach ($role_names as $name) {
      $name = trim((string)$name);
      if ($name !== '') {
        $clean_role_names[] = $name;
      }
    }
    $clean_role_names = array_values(array_unique($clean_role_names));

    if (!in_array('member', array_map('strtolower', $clean_role_names), true)) {
      $clean_role_names[] = 'member';
    }

    $user_id = self::$database->escape_string($this->id_usr);

    // remove existing roles
    $sql = "DELETE FROM user_role_usrrol WHERE id_usr_usrrol = '{$user_id}'";
    $result = self::$database->query($sql);
    if (!$result) {
      return false;
    }

    foreach ($clean_role_names as $role_name) {
      $result = $this->add_role_by_name($role_name);
      if (!$result) {
        return false;
      }
    }

    $this->roles = $clean_role_names;

    return true;
  }

  public function profile_image_url(): ?string
  {
    if (is_blank($this->id_img_usr)) {
      return null;
    }

    $safe_id = self::$database->escape_string($this->id_img_usr);
    $sql = "SELECT file_name_img
          FROM image_img
          WHERE id_img = '{$safe_id}'
          LIMIT 1";

    $result = self::$database->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
      $result->free();
      return url_for('/uploads/profile/270/' . u($row['file_name_img']));
    }

    return null;
  }

  public function update_last_login(): bool
  {
    if (is_blank($this->id_usr)) {
      return false;
    }

    $safe_id = self::$database->escape_string($this->id_usr);

    $sql = "UPDATE " . static::$table_name . " ";
    $sql .= "SET last_login_at_usr = CURRENT_TIMESTAMP ";
    $sql .= "WHERE id_usr = '{$safe_id}' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);

    if ($result) {
      $this->last_login_at_usr = date('Y-m-d H:i:s');
      return true;
    }

    return false;
  }

  public function last_active_display(): ?string
  {
    if (is_blank($this->last_login_at_usr)) {
      return null;
    }

    return date('F Y', strtotime($this->last_login_at_usr));
  }

  public static function find_recent_signups($limit = 8)
  {
    $limit = (int)$limit;
    if ($limit < 1) {
      $limit = 8;
    }

    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= "ORDER BY created_at_usr DESC ";
    $sql .= "LIMIT " . $limit;

    return static::find_by_sql($sql);
  }

  public static function admin_summary_counts()
  {
    $counts = [
      'total_users' => 0,
      'pending_users' => 0,
      'active_users' => 0,
      'disabled_users' => 0
    ];

    $sql = "SELECT ";
    $sql .= "COUNT(*) AS total_users, ";
    $sql .= "SUM(CASE WHEN status_usr = 'pending' THEN 1 ELSE 0 END) AS pending_users, ";
    $sql .= "SUM(CASE WHEN status_usr = 'active' THEN 1 ELSE 0 END) AS active_users, ";
    $sql .= "SUM(CASE WHEN status_usr = 'disabled' THEN 1 ELSE 0 END) AS disabled_users ";
    $sql .= "FROM " . static::$table_name;

    $result = static::$database->query($sql);
    if ($result) {
      $row = $result->fetch_assoc();
      $result->free();

      $counts['total_users'] = (int)($row['total_users'] ?? 0);
      $counts['pending_users'] = (int)($row['pending_users'] ?? 0);
      $counts['active_users'] = (int)($row['active_users'] ?? 0);
      $counts['disabled_users'] = (int)($row['disabled_users'] ?? 0);
    }
    
    return $counts;
  }

  public static function admin_find_all($filters = [])
  {
    $search = trim($filters['user_search'] ?? '');
    $status = trim($filters['status'] ?? '');
    $role = trim($filters['role'] ?? '');

    $where = [];

    if ($search !== '') {
      $search_esc = static::$database->escape_string($search);
      $like = "'%" . $search_esc . "%'";

      $where[] = "("
        . "u.username_usr LIKE {$like} "
        . "OR u.display_name_usr LIKE {$like} "
        . "OR u.email_usr LIKE {$like}"
        . ")";
    }

    if ($status !== '') {
      $status_esc = static::$database->escape_string($status);
      $where[] = "u.status_usr = '{$status_esc}'";
    }

    if ($role !== '') {
      $role_esc = static::$database->escape_string($role);
      $where[] = "EXISTS ("
        . "SELECT 1 "
        . "FROM user_role_usrrol ur2 "
        . "INNER JOIN role_rol r2 ON ur2.id_rol_usrrol = r2.id_rol "
        . "WHERE ur2.id_usr_usrrol = u.id_usr "
        . "AND r2.name_rol = '{$role_esc}'"
        . ")";
    }

    $sql = "SELECT ";
    $sql .= "u.id_usr, u.username_usr, u.display_name_usr, u.email_usr, u.status_usr, ";
    $sql .= "u.created_at_usr, u.last_login_at_usr, ";
    $sql .= "COUNT(DISTINCT rcp.id_rcp) AS recipe_count, ";
    $sql .= "GROUP_CONCAT(DISTINCT rol.name_rol ORDER BY ";
    $sql .= "FIELD(rol.name_rol, 'super admin', 'admin', 'member') SEPARATOR ', ') AS role_names ";
    $sql .= "FROM user_usr u ";
    $sql .= "LEFT JOIN recipe_rcp rcp ON rcp.id_usr_rcp = u.id_usr ";
    $sql .= "LEFT JOIN user_role_usrrol ur ON ur.id_usr_usrrol = u.id_usr ";
    $sql .= "LEFT JOIN role_rol rol ON rol.id_rol = ur.id_rol_usrrol ";

    if (!empty($where)) {
      $sql .= "WHERE " . implode(' AND ', $where) . " ";
    }

    $sql .= "GROUP BY ";
    $sql .= "u.id_usr, u.username_usr, u.display_name_usr, u.email_usr, u.status_usr, u.created_at_usr, u.last_login_at_usr ";
    $sql .= "ORDER BY u.created_at_usr DESC, u.username_usr ASC";

    $result = static::$database->query($sql);
    if (!$result) {
      exit('Database query failed.');
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    $result->free();

    return $rows;
  }

  public static function count_recipes_by_user_id($user_id)
  {
    $user_id = (int)$user_id;
    if ($user_id < 1) {
      return 0;
    }

    $sql = "SELECT COUNT(*) ";
    $sql .= "FROM recipe_rcp ";
    $sql .= "WHERE id_usr_rcp = '{$user_id}'";

    return static::$database->query($sql)->fetch_row()[0] ?? 0;
  }

  public function delete_with_recipes()
  {
    if (!isset($this->id_usr)) {
      return false;
    }

    $safe_user_id = self::$database->escape_string($this->id_usr);

    self::$database->begin_transaction();

    try {
      $sql = "DELETE FROM recipe_rcp ";
      $sql .= "WHERE id_usr_rcp = '{$safe_user_id}'";

      if (!self::$database->query($sql)) {
        throw new Exception('Could not delete user recipes.');
      }

      $sql = "DELETE FROM " . static::$table_name . " ";
      $sql .= "WHERE id_usr = '{$safe_user_id}' ";
      $sql .= "LIMIT 1";

      if (!self::$database->query($sql)) {
        throw new Exception('Could not delete user.');
      }

      self::$database->commit();
      return true;
    } catch (Throwable $e) {
      self::$database->rollback();
      return false;
    }
  }
}
