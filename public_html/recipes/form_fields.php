<?php
if (!isset($draft)) {
  $draft = recipe_get_draft();
}

$measurements = Measurement::find_all();
$meal_types = MealType::find_all();
$cuisines = Cuisine::find_all();
$dietary_styles = DietaryStyle::find_all();

$selected_meal_types = $draft['meal_types'] ?? [];
$selected_cuisines = $draft['cuisines'] ?? [];
$selected_dietary_styles = $draft['dietary_styles'] ?? [];
?>

<fieldset>
  <legend>Recipe</legend>

  <label for="title">Title*</label><br>
  <input type="text" id="title" name="recipe[title_rcp]" value="<?php echo h($draft['recipe']['title_rcp'] ?? ''); ?>" required><br>

  <label for="description">Description (500 characters max)</label><br>
  <textarea id="description" name="recipe[description_rcp]" maxlength="500" rows="3"><?php echo h($draft['recipe']['description_rcp'] ?? ''); ?></textarea><br>

  <label for="servings">Servings</label><br>
  <input type="number" step=".25" min="0" id="servings" name="recipe[serving_rcp]" value="<?php echo h($draft['recipe']['serving_rcp'] ?? ''); ?>"><br>

  <label for="privacy">Privacy</label><br>
  <?php $privacy = $draft['recipe']['privacy_rcp'] ?? 'public'; ?>
  <select id="privacy" name="recipe[privacy_rcp]">
    <?php foreach (['public', 'unlisted', 'private'] as $p) { ?>
      <option value="<?php echo h($p); ?>" <?php echo ($privacy === $p) ? 'selected' : ''; ?>>
        <?php echo h(ucfirst($p)); ?>
      </option>
    <?php } ?>
  </select><br>

  <label for="prep-time">Prep Time (min)*</label><br>
  <input type="number" min="0" id="prep-time" name="recipe[prep_time_minutes_rcp]" value="<?php echo h($draft['recipe']['prep_time_minutes_rcp'] ?? 0); ?>" required><br>

  <label for="cook-time">Cook Time (min)*</label><br>
  <input type="number" min="0" id="cook-time" name="recipe[cook_time_minutes_rcp]" value="<?php echo h($draft['recipe']['cook_time_minutes_rcp'] ?? 0); ?>" required><br>

  <label for="youtube-url">YouTube URL</label><br>
  <input type="url" id="youtube-url" name="recipe[youtube_url_rcp]" value="<?php echo h($draft['recipe']['youtube_url_rcp'] ?? ''); ?>">
</fieldset>

<fieldset>
  <legend>Meal Type</legend>
  <div class="pill-group">
    <?php foreach ($meal_types as $mty) {
      $id = (string)$mty->id_mty;
      $checked = in_array($id, $selected_meal_types, true) ? 'checked' : '';
      $input_id = "meal-type-" . $id;
    ?>
      <input class="pill-check" type="checkbox" id="<?php echo h($input_id); ?>" name="meal_types[]" value="<?php echo h($id); ?>" <?php echo $checked; ?>>
      <label class="pill" for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($mty->name_mty)); ?></label>
    <?php } ?>
  </div>
</fieldset>

<fieldset>
  <legend>Cuisine</legend>
  <div class="pill-group">
    <?php foreach ($cuisines as $csn) {
      $id = (string)$csn->id_csn;
      $checked = in_array($id, $selected_cuisines, true) ? 'checked' : '';
      $input_id = "cuisine-" . $id;
    ?>
      <input class="pill-check" type="checkbox" id="<?php echo h($input_id); ?>" name="cuisines[]" value="<?php echo h($id); ?>" <?php echo $checked; ?>>
      <label class="pill" for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($csn->name_csn)); ?></label>
    <?php } ?>
  </div>
</fieldset>

<fieldset>
  <legend>Dietary Style</legend>
  <div class="pill-group">
    <?php foreach ($dietary_styles as $dst) {
      $id = (string)$dst->id_dst;
      $checked = in_array($id, $selected_dietary_styles, true) ? 'checked' : '';
      $input_id = "dietary-style-" . $id;
    ?>
      <input class="pill-check" type="checkbox" id="<?php echo h($input_id); ?>" name="dietary_styles[]" value="<?php echo h($id); ?>" <?php echo $checked; ?>>
      <label class="pill" for="<?php echo h($input_id); ?>"><?php echo h(display_title_case($dst->name_dst)); ?></label>
    <?php } ?>
  </div>
</fieldset>

<fieldset>
  <legend>Ingredients*</legend>

  <?php foreach (($draft['ingredients'] ?? []) as $i => $ing) { ?>
    <div class="ingredient-row">
      <div>
        <label for="qty-ingredient-<?php echo h($i); ?>">Qty</label>
        <input type="number" step=".01" min="0" id="qty-ingredient-<?php echo h($i); ?>" name="ingredients[<?php echo $i; ?>][quantity_rcping]" value="<?php echo h($ing['quantity_rcping'] ?? ''); ?>">
      </div>

      <div>
        <label for="unit-ingredient-<?php echo h($i); ?>">Unit</label>
        <select id="unit-ingredient-<?php echo h($i); ?>" name="ingredients[<?php echo $i; ?>][id_mes_rcping]">
          <option value=""></option>
          <?php foreach ($measurements as $m) { ?>
            <option value="<?php echo h($m->id_mes); ?>"
              <?php echo (($ing['id_mes_rcping'] ?? '') == $m->id_mes) ? 'selected' : ''; ?>>
              <?php echo h($m->abbr_mes . " (" . $m->name_mes . ")"); ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div>
        <label for="name-ingredient-<?php echo h($i); ?>">Name</label>
        <input type="text" id="name-ingredient-<?php echo h($i); ?>" name="ingredients[<?php echo $i; ?>][name_ing]" value="<?php echo h($ing['name_ing'] ?? ''); ?>">
      </div>
    </div>
  <?php } ?>
  <button type="submit" class="button button-secondary" name="action" value="add_ingredient" formnovalidate>Add Ingredient</button>
</fieldset>

<fieldset>
  <legend>Directions*</legend>

  <?php foreach (($draft['directions'] ?? []) as $i => $dir) { ?>
    <div class="recipe-row-group">
      <label for="direction-step-<?php echo h($i); ?>">Step <?php echo $i + 1; ?></label><br>
      <textarea id="direction-step-<?php echo h($i); ?>" name="directions[<?php echo $i; ?>][instruction_dir]" rows="3" cols="60"><?php echo h($dir['instruction_dir'] ?? ''); ?></textarea>
    </div>
  <?php } ?>
  <button type="submit" class="button button-secondary" name="action" value="add_direction" formnovalidate>Add Step</button>
</fieldset>

<fieldset>
  <legend>Attachments</legend>

  <label for="photos">Upload Photos</label><br>
  <input id="photos" type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
  <p class="form-help">You may upload up to 6 images (JPG/PNG/WebP). Add photos after finishing ingredients and steps (file selections reset when the page reloads).</p>
</fieldset>
