<?php

class Role extends DatabaseObject
{

  static protected $table_name = 'role_rol';
  static protected $primary_key = 'id_rol';
  static protected $db_columns = ['id_rol', 'name_rol'];

  public $id_rol;
  public $name_rol;

  public function __construct($args = [])
  {
    $this->name_rol = $args['name_rol'] ?? '';
  }

  protected function validate()
  {
    $this->errors = [];

    if (is_blank($this->name_rol)) {
      $this->errors[] = "Role name cannot be blank.";
    }

    return $this->errors;
  }

  static public function find_by_name($name)
  {
    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= "WHERE name_rol='" . self::$database->escape_string($name) . "' ";
    $sql .= "LIMIT 1";
    $object_array = static::find_by_sql($sql);
    return !empty($object_array) ? array_shift($object_array) : false;
  }
}
