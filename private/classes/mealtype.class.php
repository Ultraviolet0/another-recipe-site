<?php

class MealType extends DatabaseObject {
  static protected $table_name = "meal_type_mty";
  static protected $primary_key = "id_mty";
  static protected $db_columns = ['id_mty', 'name_mty'];

  public $id_mty;
  public $name_mty;
}
