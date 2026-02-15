<?php

class Measurement extends DatabaseObject {

  static protected $table_name = "measurement_mes";
  static protected $db_columns = ['id_mes', 'name_mes', 'abbr_mes', 'unit_type_mes'];

  public $id_mes;
  public $name_mes;
  public $abbr_mes;
  public $unit_type_mes;

}
