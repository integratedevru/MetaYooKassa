<?php
global $wpdb;

$tableName = $wpdb->prefix . 'metayookassa_payment_types';
$last = $wpdb->get_row("SELECT MAX(id) AS id FROM $tableName");
$new_id = $last->id + 1;
$new_region = '';
$new_reester_number = '';
$new_type_of_payment = '';
$new_receipt_name = '';
$new_is_manual = 1;
?>

<style>
  .table_header {
    text-align: right;
  }
</style>
<h2>Редактирование типа платежа</h2>
<form class="form-table" method="post">
  <table class="">
    <tbody>
      <tr>
        <th class="table_header" scope="row">ID</th>
        <td><input type="text" name="new_id" value="<?php echo $new_id; ?>"></td>
      </tr>
      <tr>
        <th class="table_header" scope="row">Район</th>
        <td><input type="text" name="new_region" value="<?php echo $new_region; ?>"></td>
      </tr>
      <tr>
        <th class="table_header" scope="row">Реестровый номер</th>
        <td><input type="text" name="new_reester_number" value="<?php echo $new_reester_number; ?>"></td>
      </tr>
      <tr>
        <th class="table_header" scope="row">Тип платежа</th>
        <td><input type="text" name="new_type_of_payment" value="<?php echo $new_type_of_payment; ?>"></td>
      </tr>
      <tr>
        <th class="table_header" scope="row">Наименование квитанции</th>
        <td><input type="text" name="new_receipt_name" value="<?php echo $new_receipt_name; ?>"></td>
      </tr>
      <tr>
        <th class="table_header" scope="row">Ручное редактирование</th>
        <td><input type="text" readonly name="new_is_manual" value="<?php echo ($new_is_manual ? 'Да' : 'Нет'); ?>"></td>
      </tr>
      <tr>
        <td></td>
        <td><input type="submit" name="button_create" value="Создать" class="button button-primary"> <a href="?page=meta_yookassa_payment_types" class="button button-secondary">Отменить</a></td>
      </tr>
    </tbody>
  </table>
</form>
