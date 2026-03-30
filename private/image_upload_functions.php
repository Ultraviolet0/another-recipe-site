<?php

function ensure_dir($path)
{
  if (!is_dir($path)) {
    mkdir($path, 0755, true);
  }
}

function gd_load_image($filepath, $image_type)
{
  return match ($image_type) {
    IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
    IMAGETYPE_PNG  => imagecreatefrompng($filepath),
    IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($filepath) : null,
    default        => null,
  };
}

function save_webp_image($img, $filepath, $quality = 80)
{
  if (!function_exists('imagewebp')) {
    return false;
  }

  imagepalettetotruecolor($img);
  imagealphablending($img, false);
  imagesavealpha($img, true);

  return imagewebp($img, $filepath, $quality);
}

function make_square_crop($src_img, $src_w, $src_h, $size)
{
  $dst = imagecreatetruecolor($size, $size);

  imagealphablending($dst, false);
  imagesavealpha($dst, true);

  $side = min($src_w, $src_h);
  $src_x = (int)(($src_w - $side) / 2);
  $src_y = (int)(($src_h - $side) / 2);

  imagecopyresampled(
    $dst,
    $src_img,
    0,
    0,
    $src_x,
    $src_y,
    $size,
    $size,
    $side,
    $side
  );

  return $dst;
}

function make_resize_to_fit($src_img, $src_w, $src_h, $max_w, $max_h)
{
  $scale = min($max_w / $src_w, $max_h / $src_h, 1);

  $new_w = (int)round($src_w * $scale);
  $new_h = (int)round($src_h * $scale);

  $dst = imagecreatetruecolor($new_w, $new_h);

  imagealphablending($dst, false);
  imagesavealpha($dst, true);

  imagecopyresampled(
    $dst,
    $src_img,
    0,
    0,
    0,
    0,
    $new_w,
    $new_h,
    $src_w,
    $src_h
  );

  return $dst;
}

function process_recipe_upload($file, $upload_root_public)
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new Exception("Upload error.");
  }

  $info = @getimagesize($file['tmp_name']);
  if ($info === false) {
    throw new Exception("File is not a valid image.");
  }

  [$src_w, $src_h] = $info;
  $image_type = $info[2];

  if (!in_array($image_type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
    throw new Exception("Unsupported image type. Use JPG, PNG, or WebP.");
  }

  if (!function_exists('imagewebp')) {
    throw new Exception("Server does not support WebP conversion.");
  }

  $dir_1600 = rtrim($upload_root_public, '/') . "/uploads/recipes/1600";
  $dir_800  = rtrim($upload_root_public, '/') . "/uploads/recipes/800";
  $dir_540  = rtrim($upload_root_public, '/') . "/uploads/recipes/540";
  $dir_400  = rtrim($upload_root_public, '/') . "/uploads/recipes/400";
  $dir_270  = rtrim($upload_root_public, '/') . "/uploads/recipes/270";

  ensure_dir($dir_1600);
  ensure_dir($dir_800);
  ensure_dir($dir_540);
  ensure_dir($dir_400);
  ensure_dir($dir_270);

  $base = bin2hex(random_bytes(16));
  $file_name = $base . ".webp";

  $path_1600 = $dir_1600 . "/" . $file_name;
  $path_800  = $dir_800 . "/" . $file_name;
  $path_540  = $dir_540 . "/" . $file_name;
  $path_400  = $dir_400 . "/" . $file_name;
  $path_270  = $dir_270 . "/" . $file_name;

  $src_img = gd_load_image($file['tmp_name'], $image_type);
  if (!$src_img) {
    throw new Exception("Server cannot read this image type.");
  }

  $img_1600 = make_resize_to_fit($src_img, $src_w, $src_h, 1600, 1600);
  $img_800  = make_resize_to_fit($src_img, $src_w, $src_h, 800, 800);

  $master_w = imagesx($img_1600);
  $master_h = imagesy($img_1600);

  $img_540 = make_square_crop($img_1600, $master_w, $master_h, 540);
  $img_400 = make_square_crop($img_1600, $master_w, $master_h, 400);
  $img_270 = make_square_crop($img_1600, $master_w, $master_h, 270);

  $ok1600 = save_webp_image($img_1600, $path_1600, 76);
  $ok800  = save_webp_image($img_800,  $path_800,  72);
  $ok540  = save_webp_image($img_540,  $path_540,  68);
  $ok400  = save_webp_image($img_400,  $path_400,  72);
  $ok270  = save_webp_image($img_270,  $path_270,  74);

  $src_img = null;
  $img_1600 = null;
  $img_800 = null;
  $img_540 = null;
  $img_400 = null;
  $img_270 = null;

  if (!$ok1600 || !$ok800 || !$ok540 || !$ok400 || !$ok270) {
    @unlink($path_1600);
    @unlink($path_800);
    @unlink($path_540);
    @unlink($path_400);
    @unlink($path_270);
    throw new Exception("Failed to generate WebP image variants.");
  }

  return [
    'file_name' => $file_name,
    'paths' => [
      '1600' => $path_1600,
      '800'  => $path_800,
      '540'  => $path_540,
      '400'  => $path_400,
      '270'  => $path_270
    ]
  ];
}
