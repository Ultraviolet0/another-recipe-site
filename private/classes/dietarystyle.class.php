<?php

class DietaryStyle extends DatabaseObject {
  static protected $table_name = "dietary_style_dst";
  static protected $primary_key = "id_dst";
  static protected $db_columns = ['id_dst', 'name_dst'];

  public $id_dst;
  public $name_dst;
}
