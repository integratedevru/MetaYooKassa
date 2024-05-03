<?php
global $wpdb;

$tableName = $wpdb->prefix . 'metayookassa_payment_types';
$edit_id = intval($_GET['edit']);
$last = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableName WHERE id = %d", $edit_id));
if ($last) {
  $new_id = $last->id;
  $edit_region = $last->region;
  $edit_reester_number = $last->reester_number;
  $edit_type_of_payment = $last->type_of_payment;
  $edit_receipt_name = $last->receipt_name;
  $edit_is_manual = $last->is_manual;
?>
  <style>
    .table_header {
      text-align: right;
    }
  </style>

  <h2>Редактирование типа платежа</h2>
  <form class="form-table" method="post">
    <table class="">
      <td><input type="text" hidden name="edit_id" value="<?php echo $edit_id; ?>"></td>
      <tbody>
        <tr>
          <th class="table_header" scope="row">ID</th>
          <td><input type="text" name="new_id" value="<?php echo $new_id; ?>"></td>
        </tr>
        <tr>
          <th class="table_header" scope="row">Район</th>
          <td><input type="text" name="edit_region" value="<?php echo $edit_region; ?>"></td>
        </tr>
        <tr>
          <th class="table_header" scope="row">Реестровый номер</th>
          <td><input type="text" name="edit_reester_number" value="<?php echo $edit_reester_number; ?>"></td>
        </tr>
        <tr>
          <th class="table_header" scope="row">Тип платежа</th>
          <td><input type="text" name="edit_type_of_payment" value="<?php echo $edit_type_of_payment; ?>"></td>
        </tr>
        <tr>
          <th class="table_header" scope="row">Наименование квитанции</th>
          <td><input type="text" name="edit_receipt_name" value="<?php echo $edit_receipt_name; ?>"></td>
        </tr>
        <tr>
          <th class="table_header" scope="row">Ручное редактирование</th>
          <td><input type="text" readonly name="edit_is_manual" value="<?php echo ($edit_is_manual ? 'Да' : 'Нет'); ?>"></td>
        </tr>
        <tr>
          <td></td>
          <td><input type="submit" class="button button-primary" name="button_update" value="Обновить"> <a class="button button-secondary" href="?page=meta_yookassa_payment_types">Вернуться</a></td>
        </tr>
      </tbody>
    </table>
  </form>
<?php
}