<?php

/**
 * Build a public URL for a script path.
 *
 * @param string $script_path - script path to convert to a URL
 * 
 * @return string public URL
 */
function url_for($script_path)
{
  // add the leading '/' if not present
  if ($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return rtrim(WWW_ROOT, '/') . $script_path;
}

/**
 * URL encode a string.
 *
 * @param string $string - string to encode
 * 
 * @return string encoded string
 */
function u($string = "")
{
  return urlencode($string);
}

/**
 * Raw URL encode a string.
 *
 * @param string $string - string to encode
 * 
 * @return string encoded string
 */
function raw_u($string = "")
{
  return rawurlencode($string);
}

/**
 * Escape a string for safe HTML output.
 *
 * @param string $string - string to escape
 * 
 * @return string escaped string
 */
function h($string = "")
{
  return htmlspecialchars($string);
}

/**
 * Render the custom 404 error page and stop execution.
 */
function error_404()
{
  global $session;

  http_response_code(404);
  $page_title = '404 - Page Not Found';

  include(SHARED_PATH . '/public_header.php');
  include(SHARED_PATH . '/404_content.php');
  include(SHARED_PATH . '/public_footer.php');
  exit;
}

/**
 * Send a 500 internal server error response and stop execution.
 */
function error_500()
{
  header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
  exit();
}

/**
 * Redirect the user to another location and stop execution.
 *
 * @param string $location - location to redirect to
 */
function redirect_to($location)
{
  header("Location: " . $location);
  exit;
}

/**
 * Check whether the current request uses the POST method.
 *
 * @return bool true if the current request is POST
 */
function is_post_request()
{
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
 * Check whether the current request uses the GET method.
 *
 * @return bool true if the current request is GET
 */
function is_get_request()
{
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

/**
 * Convert a string to display-friendly title case.
 *
 * @param string $s - string to format
 * 
 * @return string title-cased string
 */
function display_title_case($s)
{
  $s = trim((string)$s);
  if ($s === '') {
    return '';
  }

  // Hard overrides (exact matches)
  $special = [
    'tex-mex' => 'Tex-Mex',
    'dash diet' => 'DASH Diet',
    'low-fodmap' => 'Low-FODMAP',
    'whole30' => 'Whole30',
  ];
  $key = strtolower($s);
  if (isset($special[$key])) {
    return $special[$key];
  }

  // Title-case words, including hyphenated parts
  $parts = preg_split('/\s+/', $s);
  $parts = array_map(function ($word) {
    $hy = explode('-', $word);
    $hy = array_map(function ($p) {
      $p = strtolower($p);
      return mb_convert_case($p, MB_CASE_TITLE, "UTF-8");
    }, $hy);
    return implode('-', $hy);
  }, $parts);

  return implode(' ', $parts);
}

/**
 * Convert blank values to null.
 *
 * @param mixed $value - value to check
 * 
 * @return mixed null for blank values or the original value
 */
function blank_to_null($value)
{
  return ($value === '' || $value === null) ? null : $value;
}

/**
 * Format a number without unnecessary trailing zeroes.
 *
 * @param mixed $value - value to format
 * @param int $max_decimals - maximum number of decimal places
 * 
 * @return string formatted number
 */
function format_number_clean($value, int $max_decimals = 2): string
{
  if ($value === null || $value === '') {
    return '';
  }

  if (!is_numeric($value)) {
    return (string)$value;
  }

  $num = (float)$value;

  // Format with max decimals, then trim trailing zeros and trailing dot
  $str = number_format($num, $max_decimals, '.', '');
  $str = rtrim($str, '0');
  $str = rtrim($str, '.');

  // Avoid "-0"
  if ($str === '-0') {
    $str = '0';
  }

  return $str;
}

/**
 * Format a quantity as a kitchen-friendly whole number, fraction, or decimal.
 *
 * @param mixed $value - quantity value to format
 * 
 * @return string formatted quantity
 */
function format_quantity_kitchen($value): string
{
  if ($value === null || $value === '') {
    return '';
  }

  if (!is_numeric($value)) {
    return (string)$value;
  }

  $num = (float)$value;

  if ($num == 0.0) {
    return '0';
  }

  $whole = (int)floor($num);
  $fraction = $num - $whole;

  $common_fractions = [
    [0.125, '⅛'],
    [0.25,  '¼'],
    [0.3333333333, '⅓'],
    [0.375, '⅜'],
    [0.5,   '½'],
    [0.625, '⅝'],
    [0.6666666667, '⅔'],
    [0.75,  '¾'],
    [0.875, '⅞'],
  ];

  $closest_fraction = '';
  $smallest_diff = PHP_FLOAT_MAX;

  foreach ($common_fractions as [$decimal, $label]) {
    $diff = abs($fraction - $decimal);
    if ($diff < $smallest_diff) {
      $smallest_diff = $diff;
      $closest_fraction = $label;
    }
  }

  // Close enough to a known kitchen fraction
  if ($smallest_diff < 0.04) {
    if ($whole > 0) {
      return $whole . ' ' . $closest_fraction;
    }
    return $closest_fraction;
  }

  // Basically a whole number
  if ($fraction < 0.04) {
    return (string)$whole;
  }

  // Basically the next whole number
  if ((1 - $fraction) < 0.04) {
    return (string)($whole + 1);
  }

  // Fallback to clean decimal
  return format_number_clean($num);
}

/**
 * Check whether the current request is an AJAX request.
 *
 * @return bool true if the current request is AJAX
 */
function is_ajax_request()
{
  return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Render data as a JSON response and stop execution.
 *
 * @param mixed $data - data to encode as JSON
 * @param int $status_code - HTTP status code for the response
 */
function render_json($data, $status_code = 200)
{
  http_response_code($status_code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

/**
 * Extract a YouTube video ID from a valid YouTube URL.
 *
 * @param string $url - YouTube URL to parse
 * 
 * @return string|null YouTube video ID or null if invalid
 */
function extract_youtube_video_id($url)
{
  $url = trim((string)$url);
  if ($url === '') {
    return null;
  }

  if (!filter_var($url, FILTER_VALIDATE_URL)) {
    return null;
  }

  $parts = parse_url($url);
  if ($parts === false) {
    return null;
  }

  $host = strtolower($parts['host'] ?? '');
  $path = $parts['path'] ?? '';
  $query = $parts['query'] ?? '';

  if (str_starts_with($host, 'www.')) {
    $host = substr($host, 4);
  }

  $video_id = null;

  if ($host === 'youtube.com') {
    if ($path !== '/watch') {
      return null;
    }

    parse_str($query, $params);
    $video_id = $params['v'] ?? null;
  } elseif ($host === 'youtu.be') {
    $video_id = ltrim($path, '/');
  } else {
    return null;
  }

  if (!is_string($video_id) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) {
    return null;
  }

  return $video_id;
}

/**
 * Build a YouTube embed URL from a valid video ID.
 *
 * @param string $video_id - YouTube video ID
 * 
 * @return string|null YouTube embed URL or null if invalid
 */
function youtube_embed_url($video_id)
{
  if (!is_string($video_id) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) {
    return null;
  }

  return 'https://www.youtube.com/embed/' . $video_id;
}

/**
 * Build a list of recent admin activity items.
 *
 * @param int $limit - maximum number of activity items to return
 * 
 * @return array recent admin activity items
 */
function build_admin_recent_activity($limit = 8)
{
  $limit = (int)$limit;
  if ($limit < 1) {
    $limit = 8;
  }

  $items = [];

  $recent_users = User::find_recent_signups($limit);

  foreach ($recent_users as $recent_user) {
    $timestamp = strtotime($recent_user->created_at_usr);

    $items[] = [
      'type' => 'User Signup',
      'title' => $recent_user->username_usr . ' signed up.',
      'meta' => [
        '<strong>Status:</strong> ' . display_title_case($recent_user->status_usr),
        '<strong>Date:</strong> ' . date('M j, Y g:i A', $timestamp)
      ],
      'action_url' => url_for('/admin/users.php'),
      'action_label' => 'Review User',
      'timestamp' => $timestamp
    ];
  }

  $recent_recipe_activity = Recipe::find_recent_activity($limit);

  foreach ($recent_recipe_activity as $activity) {
    $created_ts = strtotime($activity['created_at_rcp']);
    $updated_ts = strtotime($activity['updated_at_rcp']);

    $is_update = ($updated_ts - $created_ts) > 5;

    if ($is_update) {
      $title = $activity['creator_username_usr'] . ' updated recipe "' . $activity['title_rcp'] . '".';
      $timestamp = $updated_ts;
      $type = 'Recipe Updated';
    } else {
      $title = $activity['creator_username_usr'] . ' created recipe "' . $activity['title_rcp'] . '".';
      $timestamp = $created_ts;
      $type = 'Recipe Created';
    }

    $items[] = [
      'type' => $type,
      'title' => $title,
      'meta' => [
        '<strong>Privacy:</strong> ' . display_title_case($activity['privacy_rcp']),
        '<strong>Date:</strong> ' . date('M j, Y g:i A', $timestamp)
      ],
      'action_url' => url_for('/recipes/show.php?id=' . u($activity['id_rcp'])),
      'action_label' => 'View Recipe',
      'timestamp' => $timestamp
    ];
  }

  usort($items, function ($a, $b) {
    return $b['timestamp'] <=> $a['timestamp'];
  });

  return array_slice($items, 0, $limit);
}

/**
 * Get admin category configuration options.
 *
 * @return array admin category configuration options
 */
function admin_category_configs()
{
  return [
    'meal_type' => [
      'key' => 'meal_type',
      'label' => 'Meal Type',
      'label_plural' => 'Meal Types',
      'model_class' => 'MealType',
      'table' => 'meal_type_mty',
      'id_column' => 'id_mty',
      'name_column' => 'name_mty',
      'max_length' => 255,
      'section_id' => 'meal-types',
      'delete_relation_mode' => 'junction',
      'junction_table' => 'recipe_meal_type_rcpmty',
      'junction_foreign_column' => 'id_mty_rcpmty'
    ],
    'cuisine' => [
      'key' => 'cuisine',
      'label' => 'Cuisine',
      'label_plural' => 'Cuisines',
      'model_class' => 'Cuisine',
      'table' => 'cuisine_csn',
      'id_column' => 'id_csn',
      'name_column' => 'name_csn',
      'max_length' => 255,
      'section_id' => 'cuisines',
      'delete_relation_mode' => 'junction',
      'junction_table' => 'recipe_cuisine_rcpcsn',
      'junction_foreign_column' => 'id_csn_rcpcsn'
    ],
    'dietary_style' => [
      'key' => 'dietary_style',
      'label' => 'Dietary Style',
      'label_plural' => 'Dietary Styles',
      'model_class' => 'DietaryStyle',
      'table' => 'dietary_style_dst',
      'id_column' => 'id_dst',
      'name_column' => 'name_dst',
      'max_length' => 255,
      'section_id' => 'dietary-styles',
      'delete_relation_mode' => 'junction',
      'junction_table' => 'recipe_dietary_style_rcpdst',
      'junction_foreign_column' => 'id_dst_rcpdst'
    ],
    'badge' => [
      'key' => 'badge',
      'label' => 'Badge',
      'label_plural' => 'Badges',
      'model_class' => 'Badge',
      'table' => 'badge_bdg',
      'id_column' => 'id_bdg',
      'name_column' => 'name_bdg',
      'max_length' => 25,
      'section_id' => 'badges',
      'delete_relation_mode' => 'recipe_fk',
      'recipe_foreign_column' => 'id_bdg_rcp'
    ]
  ];
}

/**
 * Get one admin category configuration by key.
 *
 * @param string $key - category configuration key
 * 
 * @return array|null category configuration or null if not found
 */
function admin_category_config($key)
{
  $configs = admin_category_configs();
  return $configs[$key] ?? null;
}

/**
 * Count all items for an admin category.
 *
 * @param string $key - category configuration key
 * 
 * @return int category item count
 */
function admin_category_count($key)
{
  global $database;

  $config = admin_category_config($key);
  if (!$config) {
    return 0;
  }

  $model_class = $config['model_class'] ?? null;
  if ($model_class && class_exists($model_class) && method_exists($model_class, 'count_all')) {
    return (int) $model_class::count_all();
  }

  $sql = "SELECT COUNT(*) AS count_total FROM {$config['table']}";
  $result = $database->query($sql);
  if (!$result) {
    return 0;
  }

  $row = $result->fetch_assoc();
  $result->free();

  return (int)($row['count_total'] ?? 0);
}

/**
 * Find all items for an admin category.
 *
 * @param string $key - category configuration key
 * 
 * @return array category items
 */
function admin_category_find_all($key)
{
  global $database;

  $config = admin_category_config($key);
  if (!$config) {
    return [];
  }

  $sql = "SELECT {$config['id_column']} AS item_id, {$config['name_column']} AS item_name ";
  $sql .= "FROM {$config['table']} ";
  $sql .= "ORDER BY {$config['name_column']} ASC";

  $result = $database->query($sql);
  if (!$result) {
    return [];
  }

  $rows = [];
  while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
  }
  $result->free();

  return $rows;
}

/**
 * Find one admin category item by ID.
 *
 * @param string $key - category configuration key
 * @param int $id - category item ID
 * 
 * @return array|null category item or null if not found
 */
function admin_category_find_by_id($key, $id)
{
  global $database;

  $config = admin_category_config($key);
  $id = (int)$id;

  if (!$config || $id < 1) {
    return null;
  }

  $sql = "SELECT {$config['id_column']} AS item_id, {$config['name_column']} AS item_name ";
  $sql .= "FROM {$config['table']} ";
  $sql .= "WHERE {$config['id_column']} = {$id} ";
  $sql .= "LIMIT 1";

  $result = $database->query($sql);
  if (!$result) {
    return null;
  }

  $row = $result->fetch_assoc();
  $result->free();

  return $row ?: null;
}

/**
 * Check whether an admin category name already exists.
 *
 * @param string $key - category configuration key
 * @param string $name - category item name to check
 * @param int $exclude_id - category item ID to exclude
 * 
 * @return bool true if the name already exists
 */
function admin_category_name_exists($key, $name, $exclude_id = 0)
{
  global $database;

  $config = admin_category_config($key);
  if (!$config) {
    return false;
  }

  $name = mb_strtolower(trim($name));
  $exclude_id = (int)$exclude_id;

  $escaped_name = $database->escape_string($name);

  $sql = "SELECT {$config['id_column']} ";
  $sql .= "FROM {$config['table']} ";
  $sql .= "WHERE LOWER({$config['name_column']}) = LOWER('{$escaped_name}') ";

  if ($exclude_id > 0) {
    $sql .= "AND {$config['id_column']} != {$exclude_id} ";
  }

  $sql .= "LIMIT 1";

  $result = $database->query($sql);
  if (!$result) {
    return false;
  }

  $exists = $result->num_rows > 0;
  $result->free();

  return $exists;
}

/**
 * Count how many recipes use an admin category item.
 *
 * @param string $key - category configuration key
 * @param int $id - category item ID
 * 
 * @return int category item usage count
 */
function admin_category_usage_count($key, $id)
{
  global $database;

  $config = admin_category_config($key);
  $id = (int)$id;

  if (!$config || $id < 1) {
    return 0;
  }

  if (($config['delete_relation_mode'] ?? '') === 'junction') {
    $sql = "SELECT COUNT(*) AS usage_total ";
    $sql .= "FROM {$config['junction_table']} ";
    $sql .= "WHERE {$config['junction_foreign_column']} = {$id}";
  } elseif (($config['delete_relation_mode'] ?? '') === 'recipe_fk') {
    $sql = "SELECT COUNT(*) AS usage_total ";
    $sql .= "FROM recipe_rcp ";
    $sql .= "WHERE {$config['recipe_foreign_column']} = {$id}";
  } else {
    return 0;
  }

  $result = $database->query($sql);
  if (!$result) {
    return 0;
  }

  $row = $result->fetch_assoc();
  $result->free();

  return (int)($row['usage_total'] ?? 0);
}

/**
 * Validate an admin category item name.
 *
 * @param string $key - category configuration key
 * @param string $name - category item name to validate
 * 
 * @return string|null error message or null if valid
 */
function admin_category_validate_name($key, $name)
{
  $config = admin_category_config($key);
  if (!$config) {
    return 'Invalid category type.';
  }

  $name = trim($name);

  if ($name === '') {
    return $config['label'] . ' name cannot be blank.';
  }

  $max_length = (int)$config['max_length'];
  if (mb_strlen($name) > $max_length) {
    return $config['label'] . ' name must be ' . $max_length . ' characters or fewer.';
  }

  return null;
}

/**
 * Create a new admin category item.
 *
 * @param string $key - category configuration key
 * @param string $name - category item name
 * 
 * @return bool true if the category item was created
 */
function admin_category_create($key, $name)
{
  global $database;

  $config = admin_category_config($key);
  if (!$config) {
    return false;
  }

  $normalized_name = mb_strtolower(trim($name));
  $escaped_name = $database->escape_string($normalized_name);

  $sql = "INSERT INTO {$config['table']} ({$config['name_column']}) ";
  $sql .= "VALUES ('{$escaped_name}')";

  return $database->query($sql);
}

/**
 * Update an existing admin category item.
 *
 * @param string $key - category configuration key
 * @param int $id - category item ID
 * @param string $name - new category item name
 * 
 * @return bool true if the category item was updated
 */
function admin_category_update($key, $id, $name)
{
  global $database;

  $config = admin_category_config($key);
  $id = (int)$id;

  if (!$config || $id < 1) {
    return false;
  }

  $normalized_name = mb_strtolower(trim($name));
  $escaped_name = $database->escape_string($normalized_name);

  $sql = "UPDATE {$config['table']} ";
  $sql .= "SET {$config['name_column']} = '{$escaped_name}' ";
  $sql .= "WHERE {$config['id_column']} = {$id} ";
  $sql .= "LIMIT 1";

  return $database->query($sql);
}

/**
 * Delete an admin category item and remove its recipe relationships.
 *
 * @param string $key - category configuration key
 * @param int $id - category item ID
 * 
 * @return bool true if the category item was deleted
 */
function admin_category_delete($key, $id)
{
  global $database;

  $config = admin_category_config($key);
  $id = (int)$id;

  if (!$config || $id < 1) {
    return false;
  }

  $database->begin_transaction();

  try {
    if (($config['delete_relation_mode'] ?? '') === 'junction') {
      $sql = "DELETE FROM {$config['junction_table']} ";
      $sql .= "WHERE {$config['junction_foreign_column']} = {$id}";
      if (!$database->query($sql)) {
        throw new Exception('Failed to remove category relationships.');
      }
    } elseif (($config['delete_relation_mode'] ?? '') === 'recipe_fk') {
      $sql = "UPDATE recipe_rcp ";
      $sql .= "SET {$config['recipe_foreign_column']} = NULL ";
      $sql .= "WHERE {$config['recipe_foreign_column']} = {$id}";
      if (!$database->query($sql)) {
        throw new Exception('Failed to remove badge relationships.');
      }
    }

    $sql = "DELETE FROM {$config['table']} ";
    $sql .= "WHERE {$config['id_column']} = {$id} ";
    $sql .= "LIMIT 1";

    if (!$database->query($sql)) {
      throw new Exception('Failed to delete category item.');
    }

    $database->commit();
    return true;
  } catch (Throwable $e) {
    $database->rollback();
    return false;
  }
}

/**
 * Get admin category items with recipe usage counts.
 *
 * @param string $key - category configuration key
 * 
 * @return array category items with usage counts
 */
function admin_category_items_with_usage($key)
{
  $items = admin_category_find_all($key);

  foreach ($items as &$item) {
    $item['usage_count'] = admin_category_usage_count($key, $item['item_id']);
  }

  return $items;
}
