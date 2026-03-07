<?php

function ensure_dir($path)
{
  if (!is_dir($path)) {
    mkdir($path, 0755, true);
  }
}

function normalize_ext_from_type($image_type)
{
  // IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP
  return match ($image_type) {
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp',
    default        => null,
  };
}

function gd_load_image($filepath, $image_type)
{
  return match ($image_type) {
    IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($filepath) : null,
    IMAGETYPE_PNG  => function_exists('imagecreatefrompng')  ? imagecreatefrompng($filepath)  : null,
    IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($filepath) : null,
    default        => null,
  };
}

function gd_save_image($img, $filepath, $image_type)
{
  // Keep simple defaults; you can tune quality later.
  return match ($image_type) {
    IMAGETYPE_JPEG => imagejpeg($img, $filepath, 85),
    IMAGETYPE_PNG  => imagepng($img, $filepath, 6),
    IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($img, $filepath, 80) : false,
    default        => false,
  };
}

function make_square_crop($src_img, $src_w, $src_h, $size)
{
  $dst = imagecreatetruecolor($size, $size);

  // handle transparency for PNG/WebP
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

function make_resize_width($src_img, $src_w, $src_h, $new_w)
{
  if ($src_w <= $new_w) {
    // No upscale
    $new_w = $src_w;
  }
  $new_h = (int)round(($new_w / $src_w) * $src_h);

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

/**
 * Saves original + variants. Returns ['file_name' => 'abc123.jpg', 'paths' => [...]].
 * Throws Exception on failure.
 */
function process_recipe_upload($file, $upload_root_public)
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new Exception("Upload error.");
  }

  // Verify it is an image
  $info = @getimagesize($file['tmp_name']);
  if ($info === false) {
    throw new Exception("File is not a valid image.");
  }

  [$src_w, $src_h] = $info;
  $image_type = $info[2];
  $ext = normalize_ext_from_type($image_type);
  if ($ext === null) {
    throw new Exception("Unsupported image type. Use JPG/PNG/WebP.");
  }

  // Create directories
  $dir_original = rtrim($upload_root_public, '/') . "/uploads/recipes/original";
  $dir_270      = rtrim($upload_root_public, '/') . "/uploads/recipes/270";
  $dir_800      = rtrim($upload_root_public, '/') . "/uploads/recipes/800";

  ensure_dir($dir_original);
  ensure_dir($dir_270);
  ensure_dir($dir_800);

  // Generate unique base filename
  $base = bin2hex(random_bytes(16)); // 32 chars
  $file_name = $base . "." . $ext;

  $path_original = $dir_original . "/" . $file_name;
  $path_270      = $dir_270 . "/" . $file_name;
  $path_800      = $dir_800 . "/" . $file_name;

  // Move original first
  if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $path_original)) {
    throw new Exception("Failed to move uploaded file.");
  }

  // Load from original
  $src_img = gd_load_image($path_original, $image_type);
  if (!$src_img) {
    @unlink($path_original);
    throw new Exception("Server cannot read this image type (GD missing support).");
  }

  // Create variants
  $img_270 = make_square_crop($src_img, $src_w, $src_h, 270);
  $img_800 = make_resize_width($src_img, $src_w, $src_h, 800);

  $ok270 = gd_save_image($img_270, $path_270, $image_type);
  $ok800 = gd_save_image($img_800, $path_800, $image_type);

  unset($src_img, $img_270, $img_800);

  if (!$ok270 || !$ok800) {
    // Clean up all
    @unlink($path_original);
    @unlink($path_270);
    @unlink($path_800);
    throw new Exception("Failed to generate image variants.");
  }

  return [
    'file_name' => $file_name,
    'paths' => [
      'original' => $path_original,
      '270' => $path_270,
      '800' => $path_800
    ]
  ];
}
