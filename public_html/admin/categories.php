<?php
require_once('../../private/initialize.php');

require_admin_login();

$page_title = 'Category Management';

$configs = admin_category_configs();

$section_data = [];
foreach ($configs as $key => $config) {
  $section_data[$key] = [
    'selected_id' => '',
    'input_value' => '',
    'items' => [],
    'count' => 0
  ];
}

$pending_delete = null;
$return_anchor = '';

if (is_post_request()) {
  $category_key = $_POST['category_key'] ?? '';
  $category_action = $_POST['category_action'] ?? '';
  $return_anchor = $_POST['return_anchor'] ?? '';

  $config = admin_category_config($category_key);

  if (!$config) {
    $session->message('Invalid category request.');
    redirect_to(url_for('/admin/categories.php'));
  }

  $selected_id = $_POST['selected_id'] ?? '';
  $selected_id = ctype_digit((string)$selected_id) ? (int)$selected_id : 0;

  $submitted_name = trim($_POST['category_name'] ?? '');

  $section_data[$category_key]['selected_id'] = $selected_id > 0 ? (string)$selected_id : '';
  $section_data[$category_key]['input_value'] = $submitted_name;

  if ($category_action === 'load_selected') {
    if ($selected_id < 1) {
      $session->message('Select an item first.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    $item = admin_category_find_by_id($category_key, $selected_id);

    if (!$item) {
      $session->message($config['label'] . ' not found.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    $section_data[$category_key]['input_value'] = $item['item_name'];
  }

  if ($category_action === 'create') {
    $error = admin_category_validate_name($category_key, $submitted_name);

    if ($error) {
      $session->message($error);
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    if (admin_category_name_exists($category_key, $submitted_name)) {
      $session->message($config['label'] . ' already exists.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    if (admin_category_create($category_key, $submitted_name)) {
      $session->message($config['label'] . ' created.');
    } else {
      $session->message('Could not create ' . strtolower($config['label']) . '.');
    }

    redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
  }

  if ($category_action === 'edit') {
    if ($selected_id < 1) {
      $session->message('Select an item first.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    $error = admin_category_validate_name($category_key, $submitted_name);

    if ($error) {
      $session->message($error);
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    if (admin_category_name_exists($category_key, $submitted_name, $selected_id)) {
      $session->message($config['label'] . ' already exists.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    if (admin_category_update($category_key, $selected_id, $submitted_name)) {
      $session->message($config['label'] . ' updated.');
    } else {
      $session->message('Could not update ' . strtolower($config['label']) . '.');
    }

    redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
  }

  if ($category_action === 'delete') {
    if ($selected_id < 1) {
      $session->message('Select an item first.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    $item = admin_category_find_by_id($category_key, $selected_id);

    if (!$item) {
      $session->message($config['label'] . ' not found.');
      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }

    $usage_count = admin_category_usage_count($category_key, $selected_id);
    $confirm_delete = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === '1';

    if ($usage_count > 0 && !$confirm_delete) {
      $pending_delete = [
        'category_key' => $category_key,
        'category_label' => $config['label'],
        'section_id' => $config['section_id'],
        'item_id' => $selected_id,
        'item_name' => $item['item_name'],
        'usage_count' => $usage_count
      ];

      $section_data[$category_key]['selected_id'] = (string)$selected_id;
      $section_data[$category_key]['input_value'] = $item['item_name'];
    } else {
      if (admin_category_delete($category_key, $selected_id)) {
        $session->message($config['label'] . ' deleted.');
      } else {
        $session->message('Could not delete ' . strtolower($config['label']) . '.');
      }

      redirect_to(url_for('/admin/categories.php#' . u($config['section_id'])));
    }
  }
}

foreach ($configs as $key => $config) {
  $section_data[$key]['items'] = admin_category_items_with_usage($key);
  $section_data[$key]['count'] = admin_category_count($key);

  if ($section_data[$key]['selected_id'] !== '' && $section_data[$key]['input_value'] === '') {
    $selected_item = admin_category_find_by_id($key, $section_data[$key]['selected_id']);
    if ($selected_item) {
      $section_data[$key]['input_value'] = $selected_item['item_name'];
    }
  }
}

include(SHARED_PATH . '/public_header.php');
?>

<div class="wrapper">
  <div class="dashboard-page admin-categories-page">

    <section class="dashboard-hero">
      <h1>Admin Dashboard</h1>
      <h2>Manage Recipe Categories</h2>
      <p class="dashboard-intro">Create, update, and delete meal types, cuisines, dietary styles, and badges from one place.</p>
    </section>

    <section class="dashboard-summary">
      <?php foreach ($configs as $key => $config) { ?>
        <div class="dashboard-stat-card">
          <h3><?php echo h($config['label_plural']); ?></h3>
          <p><?php echo h($section_data[$key]['count']); ?></p>
        </div>
      <?php } ?>
    </section>

    <section class="dashboard-actions">
      <h3>Quick Actions</h3>

      <div class="dashboard-action-grid">
        <?php foreach ($configs as $key => $config) { ?>
          <a class="dashboard-action-card" href="#<?php echo h($config['section_id']); ?>">
            <h4>Create <?php echo h($config['label']); ?></h4>
            <p>Jump to the <?php echo h(strtolower($config['label_plural'])); ?> section.</p>
          </a>
        <?php } ?>

        <a class="dashboard-action-card" href="<?php echo url_for('/admin'); ?>">
          <h4>Admin Dashboard</h4>
          <p>Return to the main admin dashboard.</p>
        </a>
      </div>
    </section>

    <section class="dashboard-recent admin-category-sections">
      <div class="dashboard-section-heading">
        <h3>Manage Categories</h3>
      </div>

      <div class="admin-category-grid">
        <?php foreach ($configs as $key => $config) { ?>
          <?php
          $selected_id = $section_data[$key]['selected_id'];
          $input_value = $section_data[$key]['input_value'];
          $items = $section_data[$key]['items'];
          ?>
          <article class="admin-category-card" id="<?php echo h($config['section_id']); ?>" data-category-panel>
            <h4><?php echo h($config['label_plural']); ?></h4>
            <p class="admin-category-meta"><?php echo h($section_data[$key]['count']); ?> total</p>

            <form action="<?php echo url_for('/admin/categories.php#' . u($config['section_id'])); ?>" method="post" class="admin-category-form">
              <input type="hidden" name="category_key" value="<?php echo h($key); ?>">
              <input type="hidden" name="return_anchor" value="<?php echo h($config['section_id']); ?>">

              <div class="form-field">
                <label for="<?php echo h($key); ?>_selected_id">Existing <?php echo h($config['label_plural']); ?></label>
                <select name="selected_id" id="<?php echo h($key); ?>_selected_id" data-category-select>
                  <option value="">Select an item</option>
                  <?php foreach ($items as $item) { ?>
                    <option
                      value="<?php echo h($item['item_id']); ?>"
                      data-name="<?php echo h($item['item_name']); ?>"
                      data-usage-count="<?php echo h($item['usage_count']); ?>"
                      <?php echo ((string)$item['item_id'] === (string)$selected_id) ? 'selected' : ''; ?>>
                      <?php echo h(display_title_case($item['item_name'])); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div class="form-field">
                <label for="<?php echo h($key); ?>_category_name"><?php echo h($config['label']); ?> Name</label>
                <input
                  type="text"
                  name="category_name"
                  id="<?php echo h($key); ?>_category_name"
                  value="<?php echo h($input_value); ?>"
                  maxlength="<?php echo h($config['max_length']); ?>"
                  data-category-input>
              </div>

              <p class="admin-category-helper" data-category-helper>
                Select an item to edit or delete, or enter a new name to create one.
              </p>

              <div class="admin-category-actions">
                <button type="submit" class="button" name="category_action" value="create">
                  Create <?php echo h($config['label']); ?>
                </button>

                <button type="submit" class="button button-secondary admin-category-load-button" name="category_action" value="load_selected" data-category-load>
                  Load Selected
                </button>

                <button type="submit" class="button button-secondary" name="category_action" value="edit" data-category-edit>
                  Edit
                </button>

                <button type="submit" class="button button-danger" name="category_action" value="delete" data-category-delete>
                  Delete
                </button>
              </div>
            </form>

            <?php if ($pending_delete && $pending_delete['category_key'] === $key) { ?>
              <div class="admin-category-confirm">
                <p>
                  <strong><?php echo h(display_title_case($pending_delete['item_name'])); ?></strong>
                  is currently assigned to
                  <strong><?php echo h($pending_delete['usage_count']); ?></strong>
                  recipe<?php echo $pending_delete['usage_count'] === 1 ? '' : 's'; ?>.
                </p>

                <p>Deleting it will remove it from those recipes. This cannot be undone.</p>

                <div class="admin-category-confirm-actions">
                  <form action="<?php echo url_for('/admin/categories.php#' . u($config['section_id'])); ?>" method="post">
                    <input type="hidden" name="category_key" value="<?php echo h($key); ?>">
                    <input type="hidden" name="return_anchor" value="<?php echo h($config['section_id']); ?>">
                    <input type="hidden" name="selected_id" value="<?php echo h($pending_delete['item_id']); ?>">
                    <input type="hidden" name="category_name" value="<?php echo h($pending_delete['item_name']); ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="button button-danger" name="category_action" value="delete">
                      Delete Anyway
                    </button>
                  </form>

                  <a class="button button-secondary" href="<?php echo url_for('/admin/categories.php#' . u($config['section_id'])); ?>">
                    Cancel
                  </a>
                </div>
              </div>
            <?php } ?>
          </article>
        <?php } ?>
      </div>
    </section>

  </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
