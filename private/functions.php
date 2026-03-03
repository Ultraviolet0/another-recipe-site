<?php

function url_for($script_path) {
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return rtrim(WWW_ROOT, '/') . $script_path;
}

function u($string="") {
  return urlencode($string);
}

function raw_u($string="") {
  return rawurlencode($string);
}

function h($string="") {
  return htmlspecialchars($string);
}

function error_404() {
  header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
  exit();
}

function error_500() {
  header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
  exit();
}

function redirect_to($location) {
  header("Location: " . $location);
  exit;
}

function is_post_request() {
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function is_get_request() {
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function display_title_case($s) {
  $s = trim((string)$s);
  if($s === '') { return ''; }

  // Hard overrides (exact matches)
  $special = [
    'tex-mex' => 'Tex-Mex',
    'dash diet' => 'DASH Diet',
    'low-fodmap' => 'Low-FODMAP',
    'whole30' => 'Whole30',
  ];
  $key = strtolower($s);
  if(isset($special[$key])) {
    return $special[$key];
  }

  // Title-case words, including hyphenated parts
  $parts = preg_split('/\s+/', $s);
  $parts = array_map(function($word) {
    $hy = explode('-', $word);
    $hy = array_map(function($p) {
      $p = strtolower($p);
      return mb_convert_case($p, MB_CASE_TITLE, "UTF-8");
    }, $hy);
    return implode('-', $hy);
  }, $parts);

  return implode(' ', $parts);
}

function blank_to_null($value) {
  return ($value === '' || $value === null) ? null : $value;
}

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

?>
