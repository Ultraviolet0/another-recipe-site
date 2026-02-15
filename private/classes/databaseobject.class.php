<?php

class DatabaseObject
{

  static protected $database;
  static protected $table_name = "";
  static protected $db_columns = [];
  static protected $primary_key = "id";
  public $errors = [];

  static public function set_database($database)
  {
    self::$database = $database;
  }

  static public function find_by_sql($sql)
  {
    $result = self::$database->query($sql);
    if (!$result) {
      exit("Database query failed: " . self::$database->error);
    }

    // results into objects
    $object_array = [];
    while ($record = $result->fetch_assoc()) {
      $object_array[] = static::instantiate($record);
    }

    $result->free();
    return $object_array;
  }

  static public function find_all()
  {
    $sql = "SELECT * FROM " . static::$table_name;
    return static::find_by_sql($sql);
  }

  static public function find_by_id($id)
  {
    $pk = static::$primary_key;
    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= "WHERE {$pk}='" . self::$database->escape_string($id) . "' ";
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

  static protected function instantiate($record)
  {
    $object = new static;

    foreach ($record as $property => $value) {
      if (property_exists($object, $property)) {
        $object->$property = $value;
      }
    }

    return $object;
  }

  protected function validate()
  {
    $this->errors = [];

    // Add custom validations

    return $this->errors;
  }
  
  public function attributes()
  {
    $attributes = [];
    foreach (static::$db_columns as $column) {
      if ($column == static::$primary_key) { continue; }
      $attributes[$column] = $this->$column;
    }
    return $attributes;
  }

  protected function sanitized_attributes()
  {
    $sanitized = [];
    foreach ($this->attributes() as $key => $value) {
      if ($value === null) {
        $sanitized[$key] = null;
      } else {
        $sanitized[$key] = self::$database->escape_string($value);
      }
    }
    return $sanitized;
  }

  protected function create()
  {
    $this->validate();
    if (!empty($this->errors)) { return false; }

    $attributes = $this->sanitized_attributes();

    $sql = "INSERT INTO " . static::$table_name . " (";
    $sql .= join(', ', array_keys($attributes));
    $sql .= ") VALUES ('";
    $sql .= join("', '", array_values($attributes));
    $sql .= "')";

    $result = self::$database->query($sql);
    if ($result) {
      $pk = static::$primary_key;
      $this->$pk = self::$database->insert_id;
    }
    return $result;
  }

  protected function update()
  {
    $this->validate();
    if (!empty($this->errors)) { return false; }

    $attributes = $this->sanitized_attributes();
    $attribute_pairs = [];
    foreach ($attributes as $key => $value) {
      $attribute_pairs[] = "{$key}='{$value}'";
    }

    $pk = static::$primary_key;
    $sql = "UPDATE " . static::$table_name . " SET ";
    $sql .= join(', ', $attribute_pairs);
    $sql .= " WHERE {$pk}='" . self::$database->escape_string($this->$pk) . "' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);
    return $result;
  }

  public function save()
  {
    $pk = static::$primary_key;

    //if (isset($this->$pk)) {
    //  return $this->update();
    //} else {
    //  return $this->create();
    //}
    // Above improved with ternary operator:
    return isset($this->$pk) ? $this->update() : $this->create();
  }

  public function merge_attributes($args = [])
  {
    foreach ($args as $key => $value) {
      if (property_exists($this, $key) && !is_null($value)) {
        $this->$key = $value;
      }
    }
  }

  public function delete()
  {
    $pk = static::$primary_key;

    $sql = "DELETE FROM " . static::$table_name . " ";
    $sql .= "WHERE {$pk}='" . self::$database->escape_string($this->$pk) . "' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);
    return $result;
  }
}
