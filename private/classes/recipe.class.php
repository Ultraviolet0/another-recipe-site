<?php

class Recipe extends DatabaseObject
{

  static protected $table_name = "recipe_rcp";
  static protected $primary_key = "id_rcp";
  static protected $db_columns = [
    'id_rcp',
    'id_usr_rcp',
    'title_rcp',
    'description_rcp',
    'serving_rcp',
    'id_bdg_rcp',
    'privacy_rcp',
    'prep_time_minutes_rcp',
    'cook_time_minutes_rcp',
    'youtube_url_rcp'
  ];

  public $id_rcp;
  public $id_usr_rcp;
  public $title_rcp;
  public $description_rcp;
  public $serving_rcp;
  public $id_bdg_rcp;
  public $privacy_rcp;
  public $prep_time_minutes_rcp;
  public $cook_time_minutes_rcp;
  public $youtube_url_rcp;
  public $created_at_rcp;
  public $updated_at_rcp;

  public function __construct($args = [])
  {
    $this->id_usr_rcp = $args['id_usr_rcp'] ?? null;
    $this->title_rcp = $args['title_rcp'] ?? '';
    $this->description_rcp = blank_to_null($args['description_rcp'] ?? null);
    $this->serving_rcp = blank_to_null($args['serving_rcp'] ?? null);
    $this->id_bdg_rcp = blank_to_null($args['id_bdg_rcp'] ?? null);
    $this->privacy_rcp = $args['privacy_rcp'] ?? 'public';
    $this->prep_time_minutes_rcp = $args['prep_time_minutes_rcp'] ?? 0;
    $this->cook_time_minutes_rcp = $args['cook_time_minutes_rcp'] ?? 0;
    $this->youtube_url_rcp = blank_to_null($args['youtube_url_rcp'] ?? null);
  }

  protected function validate()
  {
    $this->errors = [];

    if (is_blank($this->title_rcp)) {
      $this->errors[] = "Title cannot be blank.";
    } elseif (!has_length($this->title_rcp, ['max' => 255])) {
      $this->errors[] = "Title must be 255 characters or fewer.";
    }

    if (!is_blank($this->description_rcp) && !has_length($this->description_rcp, ['max' => 255])) {
      $this->errors[] = "Description must be 255 characters or fewer.";
    }

    $valid_privacy = ['public', 'unlisted', 'private'];
    if (!in_array($this->privacy_rcp, $valid_privacy, true)) {
      $this->errors[] = "Privacy is invalid.";
    }

    if (!isset($this->id_usr_rcp) || !ctype_digit((string)$this->id_usr_rcp)) {
      $this->errors[] = "User is required.";
    }

    return $this->errors;
  }

  public function save_with_children(
    $ingredients = [],
    $directions = [],
    $photo_files = null,
    $upload_root_public = null,
    $meal_type_ids = [],
    $cuisine_ids = [],
    $dietary_style_ids = []
  ) {

    $this->validate();
    if (!empty($this->errors)) {
      return false;
    }

    $child_errors = $this->validate_children($ingredients, $directions, $photo_files);
    if (!empty($child_errors)) {
      $this->errors = array_merge($this->errors, $child_errors);
      return false;
    }

    self::$database->begin_transaction();

    try {
      $ok = $this->save(); // creates recipe_rcp row
      if (!$ok) {
        throw new Exception("Recipe insert failed.");
      }
      $recipe_id = $this->id_rcp;

      $this->insert_meal_types($recipe_id, $meal_type_ids);
      $this->insert_cuisines($recipe_id, $cuisine_ids);
      $this->insert_dietary_styles($recipe_id, $dietary_style_ids);
      $this->insert_ingredients($recipe_id, $ingredients);
      $this->insert_directions($recipe_id, $directions);

      if ($photo_files && $upload_root_public) {
        $this->insert_images($recipe_id, $photo_files, $upload_root_public);
      }

      self::$database->commit();
      return true;
    } catch (Exception $e) {
      self::$database->rollback();
      $this->errors[] = "Save failed: " . $e->getMessage();
      return false;
    }
  }

  protected function validate_children($ingredients, $directions, $photo_files = null)
  {
    $errors = [];

    // Require at least one ingredient name
    $has_ing = false;
    foreach ($ingredients as $row) {
      if (trim($row['name_ing'] ?? '') !== '') {
        $has_ing = true;
        break;
      }
    }
    if (!$has_ing) {
      $errors[] = "Add at least one ingredient.";
    }

    // Require at least one direction instruction
    $has_dir = false;
    foreach ($directions as $row) {
      if (trim($row['instruction_dir'] ?? '') !== '') {
        $has_dir = true;
        break;
      }
    }
    if (!$has_dir) {
      $errors[] = "Add at least one direction step.";
    }

    // Per-ingredient validation (only when name present)
    foreach ($ingredients as $i => $row) {
      $name = trim($row['name_ing'] ?? '');
      if ($name === '') {
        continue;
      }

      $qty = $row['quantity_rcping'] ?? '';
      $mes = $row['id_mes_rcping'] ?? '';

      if ($qty === '' || !is_numeric($qty) || (float)$qty <= 0) {
        $errors[] = "Ingredient row " . ($i + 1) . ": quantity must be a positive number.";
      }
      if ($mes === '' || !ctype_digit((string)$mes)) {
        $errors[] = "Ingredient row " . ($i + 1) . ": measurement is required.";
      }
      if (!has_length($name, ['max' => 255])) {
        $errors[] = "Ingredient row " . ($i + 1) . ": name must be 255 characters or fewer.";
      }
    }

    // Directions: step text required if present
    foreach ($directions as $i => $row) {
      $text = trim($row['instruction_dir'] ?? '');
      if ($text === '') {
        continue;
      }
      // TEXT column, so no hard limit here; you could set a sane max if desired.
    }

    // Image validation (count + size)
    if ($photo_files && is_array($photo_files['name'] ?? null)) {

      $max_files = 6;
      $max_bytes = 5 * 1024 * 1024; // 5 MB each

      $count = count($photo_files['name']);
      $real_uploads = 0;

      for ($i = 0; $i < $count; $i++) {

        $err = $photo_files['error'][$i] ?? UPLOAD_ERR_NO_FILE;

        // ignore empty slots
        if ($err === UPLOAD_ERR_NO_FILE) {
          continue;
        }

        $real_uploads++;

        // handle upload error codes cleanly
        if ($err !== UPLOAD_ERR_OK) {
          $errors[] = "Image " . ($i + 1) . " failed to upload (error code {$err}).";
          continue;
        }

        // per-file size check
        $size = (int)($photo_files['size'][$i] ?? 0);
        if ($size > $max_bytes) {
          $errors[] = "Image " . ($i + 1) . " is too large. Max size is 5MB per image.";
        }
      }

      if ($real_uploads > $max_files) {
        $errors[] = "You can upload a maximum of {$max_files} images.";
      }
    }

    return $errors;
  }

  protected function insert_meal_types($recipe_id, $meal_type_ids)
  {
    if (!is_array($meal_type_ids)) {
      return;
    }

    $meal_type_ids = array_values(array_unique($meal_type_ids));

    foreach ($meal_type_ids as $id_mty) {
      if (!ctype_digit((string)$id_mty)) {
        continue;
      }

      $sql = "INSERT INTO recipe_meal_type_rcpmty (id_rcp_rcpmty, id_mty_rcpmty) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($id_mty) . "'";
      $sql .= ")";
      $ok = self::$database->query($sql);
      if (!$ok) {
        throw new Exception("Failed to add meal type: " . self::$database->error);
      }
    }
  }

  protected function insert_cuisines($recipe_id, $cuisine_ids)
  {
    if (!is_array($cuisine_ids)) {
      return;
    }

    $cuisine_ids = array_values(array_unique($cuisine_ids));

    foreach ($cuisine_ids as $id_csn) {
      if (!ctype_digit((string)$id_csn)) {
        continue;
      }

      $sql = "INSERT INTO recipe_cuisine_rcpcsn (id_rcp_rcpcsn, id_csn_rcpcsn) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($id_csn) . "'";
      $sql .= ")";
      $ok = self::$database->query($sql);
      if (!$ok) {
        throw new Exception("Failed to add cuisine: " . self::$database->error);
      }
    }
  }

  protected function insert_dietary_styles($recipe_id, $dietary_style_ids)
  {
    if (!is_array($dietary_style_ids)) {
      return;
    }

    $dietary_style_ids = array_values(array_unique($dietary_style_ids));

    foreach ($dietary_style_ids as $id_dst) {
      if (!ctype_digit((string)$id_dst)) {
        continue;
      }

      $sql = "INSERT INTO recipe_dietary_style_rcpdst (id_rcp_rcpdst, id_dst_rcpdst) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($id_dst) . "'";
      $sql .= ")";
      $ok = self::$database->query($sql);
      if (!$ok) {
        throw new Exception("Failed to add dietary style: " . self::$database->error);
      }
    }
  }

  protected function insert_ingredients($recipe_id, $ingredients)
  {
    foreach ($ingredients as $row) {
      $name = trim($row['name_ing'] ?? '');
      if ($name === '') {
        continue;
      }

      $qty = (float)$row['quantity_rcping'];
      $id_mes = (int)$row['id_mes_rcping'];

      $id_ing = $this->find_or_create_ingredient($name);

      $sql = "INSERT INTO recipe_ingredient_rcping ";
      $sql .= "(id_rcp_rcping, id_ing_rcping, quantity_rcping, id_mes_rcping) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($id_ing) . "', ";
      $sql .= "'" . self::$database->escape_string($qty) . "', ";
      $sql .= "'" . self::$database->escape_string($id_mes) . "'";
      $sql .= ")";

      $result = self::$database->query($sql);
      if (!$result) {
        throw new Exception("Failed to add ingredient: " . self::$database->error);
      }
    }
  }

  protected function find_or_create_ingredient($name_ing)
  {
    // Basic normalization to reduce duplicates (still not perfect, but helps)
    $name_ing = preg_replace('/\s+/', ' ', trim($name_ing));

    $safe = self::$database->escape_string($name_ing);

    $sql = "SELECT id_ing FROM ingredient_ing WHERE name_ing='{$safe}' LIMIT 1";
    $res = self::$database->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
      return (int)$row['id_ing'];
    }

    $sql = "INSERT INTO ingredient_ing (name_ing) VALUES ('{$safe}')";
    $result = self::$database->query($sql);
    if ($result) {
      return (int)self::$database->insert_id;
    }

    // fallback (rare unique collision or race)
    $res2 = self::$database->query("SELECT id_ing FROM ingredient_ing WHERE name_ing='{$safe}' LIMIT 1");
    if ($res2 && $row2 = $res2->fetch_assoc()) {
      return (int)$row2['id_ing'];
    }

    throw new Exception("Failed to create ingredient: " . self::$database->error);
  }

  protected function insert_directions($recipe_id, $directions)
  {
    $step = 1;

    foreach ($directions as $row) {
      $text = trim($row['instruction_dir'] ?? '');
      if ($text === '') {
        continue;
      }

      $safe = self::$database->escape_string($text);

      $sql = "INSERT INTO direction_dir (id_rcp_dir, step_dir, instruction_dir) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($step) . "', ";
      $sql .= "'{$safe}'";
      $sql .= ")";

      $result = self::$database->query($sql);
      if (!$result) {
        throw new Exception("Failed to add direction step: " . self::$database->error);
      }

      $step++;
    }
  }

  protected function insert_images($recipe_id, $files, $upload_root_public)
  {
    $image_ids = [];

    // Normalize the "multiple upload" structure
    $count = is_array($files['name'] ?? null) ? count($files['name']) : 0;

    for ($i = 0; $i < $count; $i++) {
      if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        continue;
      }

      $file = [
        'name' => $files['name'][$i] ?? '',
        'type' => $files['type'][$i] ?? '',
        'tmp_name' => $files['tmp_name'][$i] ?? '',
        'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
        'size' => $files['size'][$i] ?? 0
      ];

      $result = process_recipe_upload($file, $upload_root_public);
      $file_name = $result['file_name'];

      // Insert into image_img
      $safe = self::$database->escape_string($file_name);
      $sql = "INSERT INTO image_img (file_name_img) VALUES ('{$safe}')";
      $ok = self::$database->query($sql);
      if (!$ok) {
        // If DB insert fails, cleanup files
        @unlink($result['paths']['original']);
        @unlink($result['paths']['270']);
        @unlink($result['paths']['800']);
        throw new Exception("Failed to save image record: " . self::$database->error);
      }

      $id_img = (int)self::$database->insert_id;
      $image_ids[] = $id_img;

      // Insert junction
      $sql = "INSERT INTO recipe_image_rcpimg (id_rcp_rcpimg, id_img_rcpimg) VALUES (";
      $sql .= "'" . self::$database->escape_string($recipe_id) . "', ";
      $sql .= "'" . self::$database->escape_string($id_img) . "'";
      $sql .= ")";
      $ok2 = self::$database->query($sql);
      if (!$ok2) {
        throw new Exception("Failed to link image to recipe: " . self::$database->error);
      }
    }

    return $image_ids;
  }

  public function is_owner($user_id): bool
  {
    if ($user_id === null) {
      return false;
    }
    return (string)$this->id_usr_rcp === (string)$user_id;
  }

  public function can_view(Session $session): bool
  {
    $privacy = $this->privacy_rcp ?? 'public';

    if ($privacy === 'public' || $privacy === 'unlisted') {
      return true;
    }

    // private
    return $this->is_owner($session->get_user_id()) || $session->is_admin_logged_in() || $session->is_super_admin_logged_in();
  }

  public function can_edit(Session $session): bool
  {
    return $this->is_owner($session->get_user_id()) || $session->is_admin_logged_in() || $session->is_super_admin_logged_in();
  }

  public function ingredients(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT
            ri.quantity_rcping,
            m.abbr_mes,
            m.name_mes,
            i.name_ing
          FROM recipe_ingredient_rcping ri
          JOIN ingredient_ing i ON i.id_ing = ri.id_ing_rcping
          JOIN measurement_mes m ON m.id_mes = ri.id_mes_rcping
          WHERE ri.id_rcp_rcping = '{$recipe_id}'
          ORDER BY ri.id_rcping ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
      }
      $res->free();
    }
    return $rows;
  }

  public function directions(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT step_dir, instruction_dir
          FROM direction_dir
          WHERE id_rcp_dir = '{$recipe_id}'
          ORDER BY step_dir ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
      }
      $res->free();
    }
    return $rows;
  }

  public function images(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT img.file_name_img
          FROM recipe_image_rcpimg rimg
          JOIN image_img img ON img.id_img = rimg.id_img_rcpimg
          WHERE rimg.id_rcp_rcpimg = '{$recipe_id}'
          ORDER BY rimg.id_rcpimg ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row['file_name_img'];
      }
      $res->free();
    }
    return $rows;
  }

  public function meal_types(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT m.name_mty
          FROM recipe_meal_type_rcpmty rm
          JOIN meal_type_mty m ON m.id_mty = rm.id_mty_rcpmty
          WHERE rm.id_rcp_rcpmty = '{$recipe_id}'
          ORDER BY m.name_mty ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row['name_mty'];
      }
      $res->free();
    }
    return $rows;
  }

  public function cuisines(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT c.name_csn
          FROM recipe_cuisine_rcpcsn rc
          JOIN cuisine_csn c ON c.id_csn = rc.id_csn_rcpcsn
          WHERE rc.id_rcp_rcpcsn = '{$recipe_id}'
          ORDER BY c.name_csn ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row['name_csn'];
      }
      $res->free();
    }
    return $rows;
  }

  public function dietary_styles(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT d.name_dst
          FROM recipe_dietary_style_rcpdst rd
          JOIN dietary_style_dst d ON d.id_dst = rd.id_dst_rcpdst
          WHERE rd.id_rcp_rcpdst = '{$recipe_id}'
          ORDER BY d.name_dst ASC";

    $rows = [];
    $res = self::$database->query($sql);
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row['name_dst'];
      }
      $res->free();
    }
    return $rows;
  }

  public function rating_summary(): array
  {
    $recipe_id = self::$database->escape_string($this->id_rcp);

    $sql = "SELECT AVG(rating_rtg) AS avg_rating, COUNT(*) AS rating_count
          FROM rating_rtg
          WHERE id_rcp_rtg = '{$recipe_id}'";

    $avg = null;
    $count = 0;

    $res = self::$database->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
      $avg = ($row['avg_rating'] !== null) ? (float)$row['avg_rating'] : null;
      $count = (int)($row['rating_count'] ?? 0);
      $res->free();
    }

    return ['avg' => $avg, 'count' => $count];
  }

  public function user_rating($user_id): ?int
  {
    if ($user_id === null) {
      return null;
    }

    $recipe_id = self::$database->escape_string($this->id_rcp);
    $user_id = self::$database->escape_string($user_id);

    $sql = "SELECT rating_rtg
          FROM rating_rtg
          WHERE id_rcp_rtg = '{$recipe_id}'
            AND id_usr_rtg = '{$user_id}'
          LIMIT 1";

    $res = self::$database->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
      $res->free();
      return (int)$row['rating_rtg'];
    }
    return null;
  }

  public function save_rating($user_id, int $rating): bool
  {
    if ($user_id === null) {
      return false;
    }
    if ($rating < 1 || $rating > 5) {
      return false;
    }

    $recipe_id = self::$database->escape_string($this->id_rcp);
    $user_id = self::$database->escape_string($user_id);
    $rating = self::$database->escape_string($rating);

    $sql = "INSERT INTO rating_rtg (id_rcp_rtg, id_usr_rtg, rating_rtg)
          VALUES ('{$recipe_id}', '{$user_id}', '{$rating}')
          ON DUPLICATE KEY UPDATE
            rating_rtg=VALUES(rating_rtg),
            updated_at_rtg=CURRENT_TIMESTAMP";

    return (bool) self::$database->query($sql);
  }
}
