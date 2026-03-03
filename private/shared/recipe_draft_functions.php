<?php

function recipe_default_draft()
{
  $ing_count = 6;
  $dir_count = 6;

  return [
    'recipe' => [
      'title_rcp' => '',
      'description_rcp' => null,
      'serving_rcp' => null,
      'id_bdg_rcp' => null,
      'privacy_rcp' => 'public',
      'prep_time_minutes_rcp' => 0,
      'cook_time_minutes_rcp' => 0,
      'youtube_url_rcp' => null
    ],
    'ingredients' => array_fill(0, $ing_count, [
      'quantity_rcping' => '',
      'id_mes_rcping' => '',
      'name_ing' => ''
    ]),
    'directions' => array_fill(0, $dir_count, [
      'instruction_dir' => ''
    ]),
    'meal_types' => [],
    'cuisines' => [],
    'dietary_styles' => [],
    'counts' => ['ingredients' => $ing_count, 'directions' => $dir_count],
    'errors' => []
  ];
}

function recipe_get_draft()
{
  if (!isset($_SESSION['recipe_draft'])) {
    $_SESSION['recipe_draft'] = recipe_default_draft();
  }
  return $_SESSION['recipe_draft'];
}

function recipe_save_draft($draft)
{
  $_SESSION['recipe_draft'] = $draft;
}

function recipe_clear_draft()
{
  unset($_SESSION['recipe_draft']);
}

function recipe_merge_post_into_draft($draft, $post)
{
  $draft['errors'] = [];

  // Merge base recipe fields
  $incoming_recipe = $post['recipe'] ?? [];
  $draft['recipe'] = array_merge($draft['recipe'] ?? [], $incoming_recipe);

  // Merge ingredients/directions arrays, sizing to whichever is larger:
  $ings = $post['ingredients'] ?? [];
  $dirs = $post['directions'] ?? [];

  $draft['counts']['ingredients'] = max($draft['counts']['ingredients'] ?? 0, count($ings));
  $draft['counts']['directions']  = max($draft['counts']['directions'] ?? 0,  count($dirs));

  $draft['meal_types'] = $post['meal_types'] ?? [];
  $draft['cuisines'] = $post['cuisines'] ?? [];
  $draft['dietary_styles'] = $post['dietary_styles'] ?? [];

  // Rebuild ingredients to exact count
  $draft['ingredients'] = [];
  for ($i = 0; $i < $draft['counts']['ingredients']; $i++) {
    $row = $ings[$i] ?? [];
    $draft['ingredients'][] = [
      'quantity_rcping' => $row['quantity_rcping'] ?? '',
      'id_mes_rcping'   => $row['id_mes_rcping'] ?? '',
      'name_ing'        => $row['name_ing'] ?? ''
    ];
  }

  // Rebuild directions to exact count
  $draft['directions'] = [];
  for ($i = 0; $i < $draft['counts']['directions']; $i++) {
    $row = $dirs[$i] ?? [];
    $draft['directions'][] = [
      'instruction_dir' => $row['instruction_dir'] ?? ''
    ];
  }

  return $draft;
}

function recipe_add_ingredient_row($draft)
{
  $draft['counts']['ingredients'] = ($draft['counts']['ingredients'] ?? 0) + 1;
  $draft['ingredients'][] = [
    'quantity_rcping' => '',
    'id_mes_rcping' => '',
    'name_ing' => ''
  ];
  return $draft;
}

function recipe_add_direction_row($draft)
{
  $draft['counts']['directions'] = ($draft['counts']['directions'] ?? 0) + 1;
  $draft['directions'][] = ['instruction_dir' => ''];
  return $draft;
}
