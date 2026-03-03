<?php

class Cuisine extends DatabaseObject {
  static protected $table_name = "cuisine_csn";
  static protected $primary_key = "id_csn";
  static protected $db_columns = ['id_csn', 'name_csn'];

  public $id_csn;
  public $name_csn;
}
