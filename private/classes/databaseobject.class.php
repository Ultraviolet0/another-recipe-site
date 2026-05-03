<?php

class DatabaseObject
{

  static protected $database;
  static protected $table_name = "";
  static protected $db_columns = [];
  static protected $primary_key = "id";
  public $errors = [];

  /**
   * Set the database connection for database objects.
   * 
   * @param mysqli $database - database connection to use
   */
  static public function set_database($database)
  {
    self::$database = $database;
  }

  /**
   * Find database records using a SQL query.
   * 
   * @param string $sql - SQL query to run
   * 
   * @return array objects created from query results
   */
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

  /**
   * Find all records in the database table.
   * 
   * @return array objects created from all table records
   */
  static public function find_all()
  {
    $sql = "SELECT * FROM " . static::$table_name;
    return static::find_by_sql($sql);
  }

  /**
   * Find one record by its primary key ID.
   * 
   * @param mixed $id - primary key ID to find
   * 
   * @return object|false object if found or false if not found
   */
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

  /**
   * Count all records in the database table.
   * 
   * @return int total record count
   */
  static public function count_all()
  {
    $sql = "SELECT COUNT(*) AS count FROM " . static::$table_name;
    return static::$database->query($sql)->fetch_row()[0] ?? 0;
  }

  /**
   * Create an object instance from a database record.
   * 
   * @param array $record - database record values
   * 
   * @return object object instance
   */
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

  /**
   * Validate object attributes before saving.
   * 
   * @return array validation errors
   */
  protected function validate()
  {
    $this->errors = [];

    // Add custom validations

    return $this->errors;
  }

  /**
   * Get object attributes for database columns.
   * 
   * @return array object attributes
   */
  public function attributes()
  {
    $attributes = [];
    foreach (static::$db_columns as $column) {
      if ($column == static::$primary_key) {
        continue;
      }
      $attributes[$column] = $this->$column;
    }
    return $attributes;
  }

  /**
   * Get escaped object attributes for database use.
   * 
   * @return array sanitized object attributes
   */
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

  /**
   * Insert the object as a new database record.
   * 
   * @return bool true if the record was created
   */
  protected function create()
  {
    $this->validate();
    if (!empty($this->errors)) {
      return false;
    }

    $attributes = $this->sanitized_attributes();

    $values = [];
    foreach ($attributes as $value) {
      if ($value === null) {
        $values[] = "NULL";
      } else {
        $values[] = "'" . $value . "'";
      }
    }

    $sql = "INSERT INTO " . static::$table_name . " (";
    $sql .= join(', ', array_keys($attributes));
    $sql .= ") VALUES (";
    $sql .= join(', ', $values);
    $sql .= ")";

    try {
      $result = self::$database->query($sql);
      if ($result) {
        $pk = static::$primary_key;
        $this->$pk = self::$database->insert_id;
      }
      return $result;
    } catch (mysqli_sql_exception $e) {
      $message = $e->getMessage();

      if (str_contains($message, 'username_usr')) {
        $this->errors[] = "Username is already taken.";
      } elseif (str_contains($message, 'email_usr')) {
        $this->errors[] = "Email is already in use.";
      } else {
        $this->errors[] = "Save failed.";
      }

      return false;
    }
  }

  /**
   * Update the existing database record for the object.
   * 
   * @return bool true if the record was updated
   */
  protected function update()
  {
    $this->validate();
    if (!empty($this->errors)) {
      return false;
    }

    $attributes = $this->sanitized_attributes();
    $attribute_pairs = [];
    foreach ($attributes as $key => $value) {
      if ($value === null) {
        $attribute_pairs[] = "{$key}=NULL";
      } else {
        $attribute_pairs[] = "{$key}='{$value}'";
      }
    }

    $pk = static::$primary_key;
    $sql = "UPDATE " . static::$table_name . " SET ";
    $sql .= join(', ', $attribute_pairs);
    $sql .= " WHERE {$pk}='" . self::$database->escape_string($this->$pk) . "' ";
    $sql .= "LIMIT 1";

    $result = self::$database->query($sql);
    return $result;
  }

  /**
   * Save the object by creating or updating its database record.
   * 
   * @return bool true if the object was saved
   */
  public function save()
  {
    $pk = static::$primary_key;
    return isset($this->$pk) ? $this->update() : $this->create();
  }

  /**
   * Merge values into matching object properties.
   * 
   * @param array $args - values to merge into the object
   */
  public function merge_attributes($args = [])
  {
    foreach ($args as $key => $value) {
      if (property_exists($this, $key) && !is_null($value)) {
        $this->$key = $value;
      }
    }
  }

  /**
   * Delete the object's database record.
   * 
   * @return bool true if the record was deleted
   */
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
