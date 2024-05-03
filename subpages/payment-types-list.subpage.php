<?php
global $wpdb;

$tableName = $wpdb->prefix . 'metayookassa_payment_types';
?>

<style>
  .numeric-cell {
    text-align: right;
  }
  .meta-table {
    width: auto;
  }
  .meta-table td,
  .meta-table th {
    padding: 1px 2px;
  }
</style>

<h2>Все типы платежей</h2>

<form class="form-table" method="post" enctype="multipart/form-data">
  <input type="file" name="import_file" accept=".csv,.txt">
  <input class="button button-primary" type="submit" name="button_import" value="Импортировать (.csv или .txt)">
</form>

<a class="button button-secondary" href="?page=meta_yookassa_payment_types&create">Создать новый тип платежей</a>
<br />

<table class="wp-list-table widefat fixed striped meta-table">
  <thead>
    <tr>
      <th><b>ID</b></th>
      <th><b>Район</b></th>
      <th><b>Реестровый номер</b></th>
      <th><b>Тип платежа</b></th>
      <th><b>Наименование квитанции</b></th>
      <th><b>Ручное редактирование</b></th>
      <th><b>Действия</b></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $allPaymentTypes = $wpdb->get_results('SELECT * FROM ' . $tableName);
    foreach ($allPaymentTypes as $invoice) {
      echo '<tr>';
      echo '<td class="numeric-cell" id="id-' . $invoice->id . '">' . $invoice->id . '</td>';
      echo '<td id="region-' . $invoice->id . '">' . $invoice->region . '</td>';
      echo '<td class="numeric-cell" id="reester_number-' . $invoice->id . '">' . $invoice->reester_number . '</td>';
      echo '<td class="numeric-cell" id="type_of_payment-' . $invoice->id . '">' . $invoice->type_of_payment . '</td>';
      echo '<td id="receipt_name-' . $invoice->id . '">' . $invoice->receipt_name . '</td>';
      echo '<td id="is_manual-' . $invoice->id . '">' . ($invoice->is_manual ? 'Да' : 'Нет') . '</td>';
      echo '<td id="actions-' . $invoice->id . '"><a class="button button-secondary button-small" href="?page=meta_yookassa_payment_types&edit=' . $invoice->id . '">Редактировать</a> <a class="button button-secondary button-small" href="#"class="delete-link" data-id="' . $invoice->id . '">Удалить</a></td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>

<script>
  var deleteLinks = document.querySelectorAll('.delete-link');
  deleteLinks.forEach(function(link) {
    link.addEventListener('click', function(event) {
      event.preventDefault();
      var delete_id = link.getAttribute('data-id');
      var confirmation = confirm("Вы уверены, что хотите удалить эту запись?");
      if (confirmation) {
        window.location.href = "<?php echo esc_url_raw(admin_url('admin.php?page=meta_yookassa_payment_types&delete=')); ?>" + delete_id;
      }
    });
  });
</script>