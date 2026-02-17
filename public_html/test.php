<?php
require_once('../private/initialize.php');

$measurements = Measurement::find_all();
$page_title = 'Measurements';
include(SHARED_PATH . '/public_header.php');
?>

<main id="main-content" class="wrapper">
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Abbr</th>
      <th>Type</th>
    </tr>
    <?php foreach ($measurements as $m) { ?>
      <tr>
        <td><?php echo h($m->id_mes); ?></td>
        <td><?php echo h($m->name_mes); ?></td>
        <td><?php echo h($m->abbr_mes); ?></td>
        <td><?php echo h($m->unit_type_mes); ?></td>
      </tr>
    <?php } ?>
  </table>
</main>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
