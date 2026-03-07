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

function recipe_load_new_draft() {
  $_SESSION['recipe_draft'] = recipe_default_draft();
  $_SESSION['recipe_draft_mode'] = 'new';
  unset($_SESSION['recipe_draft_recipe_id']);
}

function recipe_get_draft()
{
  if (!isset($_SESSION['recipe_draft'])) {
    $_SESSION['recipe_draft'] = recipe_default_draft();
  }
  return $_SESSION['recipe_draft'];
}

function recipe_clear_draft() {
  unset($_SESSION['recipe_draft']);
  unset($_SESSION['recipe_draft_mode']);
  unset($_SESSION['recipe_draft_recipe_id']);
}

function recipe_save_draft($draft)
{
  $_SESSION['recipe_draft'] = $draft;
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

function recipe_load_edit_draft(Recipe $recipe) {
  $_SESSION['recipe_draft'] = $recipe->draft_data();
  $_SESSION['recipe_draft_mode'] = 'edit';
  $_SESSION['recipe_draft_recipe_id'] = (string)$recipe->id_rcp;
}

function recipes_index_page_url($page_num, $meal_types = [], $cuisines = [], $dietary_styles = [])
{
  $params = ['page' => $page_num];

  if (!empty($meal_types)) {
    $params['meal_types'] = array_values($meal_types);
  }

  if (!empty($cuisines)) {
    $params['cuisines'] = array_values($cuisines);
  }

  if (!empty($dietary_styles)) {
    $params['dietary_styles'] = array_values($dietary_styles);
  }

  return url_for('/recipes/index.php?' . http_build_query($params));
}

function recipes_index_remove_filter_url($type, $remove_id, $meal_types = [], $cuisines = [], $dietary_styles = [])
{
  $filters = [
    'meal_types' => array_values($meal_types),
    'cuisines' => array_values($cuisines),
    'dietary_styles' => array_values($dietary_styles),
  ];

  if (isset($filters[$type])) {
    $filters[$type] = array_values(array_filter(
      $filters[$type],
      fn($id) => (string)$id !== (string)$remove_id
    ));
  }

  return recipes_index_page_url(
    1,
    $filters['meal_types'],
    $filters['cuisines'],
    $filters['dietary_styles']
  );
}
