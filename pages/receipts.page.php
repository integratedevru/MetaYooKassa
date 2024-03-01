<?php
global $wpdb;

$paymentTypesTable = $wpdb->prefix . 'metayookassa_payment_types';
$invoicesTable = $wpdb->prefix . 'metayookassa_invoice';
$counterValuesTable = $wpdb->prefix . 'metayookassa_counter_value';

function findRegion($reesterNumber) {
  global $wpdb;
  $paymentTypesTable = $wpdb->prefix . 'metayookassa_payment_types';

  $startsWith = explode('_', $reesterNumber)[0];
  $result = $wpdb->get_row(
    "SELECT region FROM $paymentTypesTable WHERE reester_number LIKE '$startsWith%' LIMIT 1");
  return $result->region;
}

if (isset($_POST['button_import'])) {
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
  if (!empty($_FILES['import_file']['name']) && ($extension == 'csv' || $extension == 'txt')) {
    $totalInserted = 0;
    $countersInserted = 0;
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
    // $wpdb->query("SET FOREIGN_KEY_CHECKS = 0;");
    // $wpdb->query("TRUNCATE TABLE $counterValuesTable;");
    // $wpdb->query("TRUNCATE TABLE $invoicesTable;");
    // $wpdb->query("SET FOREIGN_KEY_CHECKS = 1;");
    $reesterNumber = explode('.', $_FILES['import_file']['name'])[0];
    $region = findRegion($reesterNumber);
    while (($csvData = fgetcsv($csvFile, 1000, ';')) !== FALSE) {
      $address = iconv('windows-1251', 'utf-8', $csvData[3]);
      $invoiceNumber = $csvData[4];
      $amount = $csvData[5];
      $unifiedNumber = $csvData[6];
      $counters = explode(':', $csvData[7]);
      for ($i = 0; $i < count($counters); $i += 3) {
        if ($counters[$i] == '[!]') {
          $typeOfPayment = $counters[$i+1];
        }
      }
      if (!empty($reesterNumber) && !empty($address) && !empty($invoiceNumber) && !empty($amount)) {
        $wpdb->insert(
          $invoicesTable,
          array(
            'reester_number' => sanitize_text_field($reesterNumber),
            'address' => sanitize_text_field($address),
            'region' => sanitize_text_field($region),
            'invoice_number' => sanitize_text_field($invoiceNumber),
            'amount' => sanitize_text_field($amount),
            'unified_number' => sanitize_text_field($unifiedNumber),
            'type_of_payment' => $typeOfPayment,
          )
        );
        $insertId = $wpdb->insert_id;
        for ($i = 0; $i < count($counters); $i += 3) {
          if ($counters[$i] != '[!]') {
            $serviceName = iconv('windows-1251', 'utf-8', $counters[$i]);
            $meterNumber = iconv('windows-1251', 'utf-8', $counters[$i+1]);
            $oldReading = $counters[$i+2];
            $wpdb->insert(
              $counterValuesTable,
              array(
                'invoice_id' => $insertId,
                'service_name' => $serviceName,
                'meter_number' => $meterNumber,
                'old_reading' => $oldReading,
              )
            );
            $countersInserted++;
          }
        }
        $totalInserted++;
      }
    }
    echo 'Успешно добавлено: ' . $totalInserted . '. Показателей добавлено: ' . $countersInserted . '. Район: ' . $region;
  }
}
?>

<h2>Квитанции</h2>

<form method="post" enctype="multipart/form-data">
  <input type="file" name="import_file" accept=".csv,.txt">
  <input type="submit" name="button_import" value="Импортировать (.csv или .txt)">
</form>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Лицевой счёт</th>
      <th>Район</th>
      <th>Реестровый номер</th>
      <th>Тип платежа</th>
      <th>Адрес</th>
      <th>Сумма к оплате</th>
      <th>Показатели счётчиков</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $invoices = $wpdb->get_results("SELECT * FROM $invoicesTable");
    foreach ($invoices as $invoice) {
      echo '<tr>';
      echo '<td>' . $invoice->id . '</td>';
      echo '<td>' . $invoice->invoice_number . '</td>';
      echo '<td>' . $invoice->region . '</td>';
      echo '<td>' . $invoice->reester_number . '</td>';
      echo '<td>' . $invoice->type_of_payment . '</td>';
      echo '<td>' . $invoice->address . '</td>';
      echo '<td>' . $invoice->amount . '</td>';
      echo '<td>';
      $counters = $wpdb->get_results("SELECT * FROM $counterValuesTable WHERE invoice_id = " . $invoice->id);
      foreach ($counters as $counter) {
        echo $counter->service_name . ' ' . $counter->meter_number . ' ' . $counter->old_reading . '<br>';
      }
      echo '</td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>
