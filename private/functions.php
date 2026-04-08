<?php

function url_for($script_path)
{
  // add the leading '/' if not present
  if ($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return rtrim(WWW_ROOT, '/') . $script_path;
}

function u($string = "")
{
  return urlencode($string);
}

function raw_u($string = "")
{
  return rawurlencode($string);
}

function h($string = "")
{
  return htmlspecialchars($string);
}

function error_404()
{
  header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
  exit();
}

function error_500()
{
  header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
  exit();
}

function redirect_to($location)
{
  header("Location: " . $location);
  exit;
}

function is_post_request()
{
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function is_get_request()
{
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

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

function blank_to_null($value)
{
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

function is_ajax_request()
{
  return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function render_json($data, $status_code = 200)
{
  http_response_code($status_code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

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

function youtube_embed_url($video_id)
{
  if (!is_string($video_id) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $video_id)) {
    return null;
  }

  return 'https://www.youtube.com/embed/' . $video_id;
}
