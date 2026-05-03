<?php

/**
 * Get the OpenRouter API key from the environment.
 *
 * @return string OpenRouter API key
 */
function openrouter_api_key()
{
  return $_ENV['OPENROUTER_API_KEY'] ?? getenv('OPENROUTER_API_KEY') ?? '';
}

/**
 * Get the OpenRouter model name from the environment.
 *
 * @return string OpenRouter model name
 */
function openrouter_model()
{
  return $_ENV['OPENROUTER_MODEL'] ?? getenv('OPENROUTER_MODEL') ?? '';
}

/**
 * Build recipe profile text from a user's recent recipes.
 *
 * @param int $user_id - user ID to build profile text for
 * @param int $limit - maximum number of recipes to include
 * 
 * @return string recipe profile text
 */
function openrouter_recipe_profile_text($user_id, $limit = 5)
{
  $recipes = Recipe::find_by_user_id($user_id, $limit);

  if (empty($recipes)) {
    return '';
  }

  $blocks = [];

  foreach ($recipes as $index => $recipe) {
    $ingredients = $recipe->ingredients();
    $ingredient_names = [];

    foreach ($ingredients as $ingredient) {
      $name = trim($ingredient['name_ing'] ?? '');
      if ($name !== '') {
        $ingredient_names[] = $name;
      }
      if (count($ingredient_names) >= 8) {
        break;
      }
    }

    $meal_types = array_map('display_title_case', $recipe->meal_types());
    $cuisines = array_map('display_title_case', $recipe->cuisines());
    $dietary_styles = array_map('display_title_case', $recipe->dietary_styles());

    $block = [];
    $block[] = 'Recipe ' . ($index + 1) . ': ' . $recipe->title_rcp;

    if (!is_blank($recipe->description_rcp)) {
      $block[] = 'Description: ' . trim($recipe->description_rcp);
    }

    if (!empty($meal_types)) {
      $block[] = 'Meal Types: ' . implode(', ', $meal_types);
    }

    if (!empty($cuisines)) {
      $block[] = 'Cuisines: ' . implode(', ', $cuisines);
    }

    if (!empty($dietary_styles)) {
      $block[] = 'Dietary Styles: ' . implode(', ', $dietary_styles);
    }

    if (!empty($ingredient_names)) {
      $block[] = 'Key Ingredients: ' . implode(', ', $ingredient_names);
    }

    $block[] = 'Estimated Total Time: ' . $recipe->total_time_minutes() . ' minutes';

    $blocks[] = implode("\n", $block);
  }

  return implode("\n\n", $blocks);
}

/**
 * Extract message content from an OpenRouter response.
 *
 * @param array $response_data - OpenRouter response data
 * 
 * @return string extracted message content
 */
function openrouter_extract_message_content($response_data)
{
  $content = $response_data['choices'][0]['message']['content'] ?? '';

  if (is_string($content)) {
    return trim($content);
  }

  if (is_array($content)) {
    $parts = [];

    foreach ($content as $item) {
      if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
        $parts[] = $item['text'];
      }
    }

    return trim(implode("\n", $parts));
  }

  return '';
}

/**
 * Generate AI recipe recommendations for a user's dashboard.
 *
 * @param int $user_id - user ID to generate recommendations for
 * @param string $username - username to include in the recommendation prompt
 * 
 * @return array recommendation result data
 */
function generate_dashboard_ai_recommendations($user_id, $username = '')
{
  $api_key = trim(openrouter_api_key());
  $model = trim(openrouter_model());

  if ($api_key === '') {
    return [
      'ok' => false,
      'content' => '',
      'error' => 'OpenRouter API key is missing from the environment file.'
    ];
  }

  if ($model === '') {
    return [
      'ok' => false,
      'content' => '',
      'error' => 'OpenRouter model name is missing from the environment file.'
    ];
  }

  $recipe_profile = openrouter_recipe_profile_text($user_id, 5);

  if ($recipe_profile === '') {
    return [
      'ok' => false,
      'content' => '',
      'error' => 'Create a few recipes first, then generate recommendations.'
    ];
  }

  $display_name = trim((string)$username);
  if ($display_name === '') {
    $display_name = 'this user';
  }

  $system_prompt = "You are helping a recipe website suggest new recipe directions based on a user's existing recipes. Suggest ideas that feel genuinely connected to the user's current tastes, ingredients, cuisines, dietary patterns, and cooking style. Avoid repeating the same recipes they already have. Be specific, practical, and appetizing.";

  $user_prompt = "User: {$display_name}\n\n";
  $user_prompt .= "Here are this user's recent recipes:\n\n";
  $user_prompt .= $recipe_profile . "\n\n";
  $user_prompt .= "Please suggest exactly 5 recipe ideas they may enjoy next.\n";
  $user_prompt .= "Do not include an intro, summary, or conclusion.\n";
  $user_prompt .= "Do not use markdown, bold, or bullet points.\n";
  $user_prompt .= "Return exactly 5 lines in this exact format:\n";
  $user_prompt .= "Recipe Name | short description\n\n";
  $user_prompt .= "Keep the total response under 180 words.";

  $payload = [
    'model' => $model,
    'messages' => [
      [
        'role' => 'system',
        'content' => $system_prompt
      ],
      [
        'role' => 'user',
        'content' => $user_prompt
      ]
    ],
    'temperature' => 0.9
  ];

  $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_TIMEOUT, 45);

  $response = curl_exec($ch);
  $curl_error = curl_error($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($response === false) {
    return [
      'ok' => false,
      'content' => '',
      'error' => 'OpenRouter request failed: ' . $curl_error
    ];
  }

  $data = json_decode($response, true);

  if ($http_code < 200 || $http_code >= 300) {
    $api_error = $data['error']['message'] ?? 'Unexpected API error.';
    return [
      'ok' => false,
      'content' => '',
      'error' => 'OpenRouter returned an error: ' . $api_error
    ];
  }

  $content = openrouter_extract_message_content($data);

  if ($content === '') {
    return [
      'ok' => false,
      'content' => '',
      'error' => 'OpenRouter returned an empty response.'
    ];
  }

  return [
    'ok' => true,
    'content' => $content,
    'error' => ''
  ];
}

/**
 * Format dashboard AI recommendation text as HTML.
 *
 * @param string $text - recommendation text to format
 * 
 * @return string formatted recommendation HTML
 */
function format_dashboard_ai_recommendations_html($text)
{
  $text = trim((string)$text);

  if ($text === '') {
    return '';
  }

  $text = str_replace(["\r\n", "\r"], "\n", $text);
  $lines = explode("\n", $text);

  $items = [];

  foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '') {
      continue;
    }

    $line = str_replace('**', '', $line);
    $line = preg_replace('/^[-*•]\s*/u', '', $line);
    $line = preg_replace('/^Recipe Idea:\s*/i', '', $line);

    $title = '';
    $description = '';

    if (strpos($line, '|') !== false) {
      $parts = array_map('trim', explode('|', $line, 2));

      $title = $parts[0] ?? '';
      $description = $parts[1] ?? '';
    } else {
      $dash_parts = preg_split('/\s+[—–-]\s+/u', $line, 2);

      if (count($dash_parts) === 2) {
        $title = trim($dash_parts[0]);
        $description = trim($dash_parts[1]);
      } else {
        $title = trim($line);
      }
    }

    if ($title === '') {
      continue;
    }

    $item_html = '<li>';
    $item_html .= '<strong>' . h($title) . '</strong>';

    if ($description !== '') {
      $item_html .= ': ' . h($description);
    }

    $item_html .= '</li>';

    $items[] = $item_html;
  }

  if (empty($items)) {
    return '<p>' . h($text) . '</p>';
  }

  $html = '<h4>Recipe Ideas</h4>';
  $html .= '<ul>';
  $html .= implode('', $items);
  $html .= '</ul>';

  return $html;
}
