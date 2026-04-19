<?php

class Badge extends DatabaseObject {
  static protected $table_name = "badge_bdg";
  static protected $primary_key = "id_bdg";
  static protected $db_columns = ['id_bdg', 'name_bdg'];

  public $id_bdg;
  public $name_bdg;
}
