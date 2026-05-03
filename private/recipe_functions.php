<?php

/**
 * Build the default recipe draft data.
 *
 * @return array default recipe draft data
 */
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

/**
 * Load a new recipe draft into the session.
 */
function recipe_load_new_draft()
{
  $_SESSION['recipe_draft'] = recipe_default_draft();
  $_SESSION['recipe_draft_mode'] = 'new';
  unset($_SESSION['recipe_draft_recipe_id']);
}

/**
 * Get the current recipe draft from the session.
 *
 * @return array current recipe draft data
 */
function recipe_get_draft()
{
  if (!isset($_SESSION['recipe_draft'])) {
    $_SESSION['recipe_draft'] = recipe_default_draft();
  }
  return $_SESSION['recipe_draft'];
}

/**
 * Clear recipe draft data from the session.
 */
function recipe_clear_draft()
{
  unset($_SESSION['recipe_draft']);
  unset($_SESSION['recipe_draft_mode']);
  unset($_SESSION['recipe_draft_recipe_id']);
}

/**
 * Save recipe draft data to the session.
 *
 * @param array $draft - recipe draft data to save
 */
function recipe_save_draft($draft)
{
  $_SESSION['recipe_draft'] = $draft;
}

/**
 * Merge posted recipe form data into a recipe draft.
 *
 * @param array $draft - existing recipe draft data
 * @param array $post - posted recipe form data
 * 
 * @return array updated recipe draft data
 */
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

/**
 * Add a blank ingredient row to a recipe draft.
 *
 * @param array $draft - recipe draft data
 * 
 * @return array updated recipe draft data
 */
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

/**
 * Add a blank direction row to a recipe draft.
 *
 * @param array $draft - recipe draft data
 * 
 * @return array updated recipe draft data
 */
function recipe_add_direction_row($draft)
{
  $draft['counts']['directions'] = ($draft['counts']['directions'] ?? 0) + 1;
  $draft['directions'][] = ['instruction_dir' => ''];
  return $draft;
}

/**
 * Load an existing recipe into the session as an edit draft.
 *
 * @param Recipe $recipe - recipe object to edit
 */
function recipe_load_edit_draft(Recipe $recipe)
{
  $_SESSION['recipe_draft'] = $recipe->draft_data();
  $_SESSION['recipe_draft_mode'] = 'edit';
  $_SESSION['recipe_draft_recipe_id'] = (string)$recipe->id_rcp;
}

/**
 * Build a URL for the recipe index page with filters.
 *
 * @param int $page_num - page number to link to
 * @param string $search - search text
 * @param array $meal_types - selected meal type IDs
 * @param array $cuisines - selected cuisine IDs
 * @param array $dietary_styles - selected dietary style IDs
 * @param string $sort - selected sort option
 * 
 * @return string recipe index page URL
 */
function recipes_index_page_url($page_num, $search = '', $meal_types = [], $cuisines = [], $dietary_styles = [], $sort = 'newest')
{
  $params = ['page' => $page_num];

  $search = trim((string)$search);
  if ($search !== '') {
    $params['search'] = $search;
  }

  if (!empty($meal_types)) {
    $params['meal_types'] = array_values($meal_types);
  }

  if (!empty($cuisines)) {
    $params['cuisines'] = array_values($cuisines);
  }

  if (!empty($dietary_styles)) {
    $params['dietary_styles'] = array_values($dietary_styles);
  }

  $sort = trim((string)$sort);
  if ($sort !== '' && $sort !== 'newest') {
    $params['sort'] = $sort;
  }

  return url_for('/recipes/index.php?' . http_build_query($params));
}

/**
 * Build a recipe index URL with one filter removed.
 *
 * @param string $type - filter type to remove from
 * @param int $remove_id - filter ID to remove
 * @param string $search - search text
 * @param array $meal_types - selected meal type IDs
 * @param array $cuisines - selected cuisine IDs
 * @param array $dietary_styles - selected dietary style IDs
 * @param string $sort - selected sort option
 * 
 * @return string recipe index page URL
 */
function recipes_index_remove_filter_url($type, $remove_id, $search = '', $meal_types = [], $cuisines = [], $dietary_styles = [], $sort = 'newest')
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
    $search,
    $filters['meal_types'],
    $filters['cuisines'],
    $filters['dietary_styles'],
    $sort
  );
}
