<?php
function get_payment_data() {
  global $wpdb;
  $invoiceTableName = $wpdb->prefix . 'metayookassa_invoice';
  $counterValuesTableName = $wpdb->prefix . 'metayookassa_counter_value';

  $invoice_number = isset($_POST['invoice_number']) ? sanitize_text_field($_POST['invoice_number']) : '';
  $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : '';
  $type_of_payment = isset($_POST['type_of_payment']) ? sanitize_text_field($_POST['type_of_payment']) : '';

  $data = $wpdb->get_row("
    SELECT id, address, amount
    FROM $invoiceTableName
    WHERE invoice_number = '$invoice_number'
      AND region = '$region'
      AND type_of_payment = '$type_of_payment'
    ORDER BY id DESC
    LIMIT 1");
  if (!$data) {
    echo json_encode(array('error' => 'Ничего не найдено. Проверьте правильность введенных данных.'));
    exit();
  }
  $countersData = $wpdb->get_results("
    SELECT service_name, meter_number, old_reading
    FROM $counterValuesTableName
    WHERE invoice_id = $data->id
    ORDER BY id");
  $response = array(
    'id' => $data->id,
    'address' => $data->address,
    'amount' => $data->amount,
    'counters' => $countersData
  );
  echo json_encode($response);
  exit();
}