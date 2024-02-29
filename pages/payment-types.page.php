<?php
global $wpdb;

$tableName = $wpdb->prefix . 'metayookassa_payment_types';

function parseRegion($region) {
  $pattern = '/\S* *\(.+\)|\S* *\S*/';
  preg_match($pattern, $region, $matches);
  return $matches[0];
}

function parseReesterAndPayment($data) {
  $pattern = '/(\d+_\d*)\D*(\d*)/';
  $matches = array();
  if (preg_match($pattern, $data, $matches)) {
      $reesterNumber = isset($matches[1]) ? $matches[1] : $matches[3];
      $typeOfPayment = isset($matches[2]) ? $matches[2] : $matches[4];
      return array('reesterNumber' => $reesterNumber, 'typeOfPayment' => $typeOfPayment);
  }
  return array('reesterNumber' => null, 'typeOfPayment' => null);
}

if (isset($_POST['button_import'])) {
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
  if (!empty($_FILES['import_file']['name']) && $extension == 'csv') {
    $totalInserted = 0;
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
    $wpdb->query("TRUNCATE TABLE $tableName;");
    while (($csvData = fgetcsv($csvFile, 1000, ';')) !== FALSE) {
      $region = empty($csvData[0]) ? $region : parseRegion($csvData[1]);
      $dataLen = count($csvData);
      $extractedData = parseReesterAndPayment($csvData[2]);
      $reesterNumber = $extractedData['reesterNumber'];
      $typeOfPayment = $extractedData['typeOfPayment'];
      $receiptName = trim($csvData[3], "! ");
      if (!empty($region) && !empty($reesterNumber) && !empty($typeOfPayment) && !empty($receiptName)) {
        $wpdb->insert(
            $tableName,
            array(
                'region' => sanitize_text_field($region),
                'reester_number' => sanitize_text_field($reesterNumber),
                'type_of_payment' => sanitize_text_field($typeOfPayment),
                'receipt_name' => sanitize_text_field($receiptName)
            )
        );
        $totalInserted++;
      }
    }
    echo 'Успешно добавлено: ' . $totalInserted;
  }
}
?>

<h2>Все типы платежей</h2>

<form method="post" enctype="multipart/form-data">
  <input type="file" name="import_file" accept=".csv">
  <input type="submit" name="button_import" value="Импортировать (.csv)">
</form>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Регион</th>
      <th>Реестровый номер</th>
      <th>Тип платежа</th>
      <th>Наименование квитанции</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $allPaymentTypes = $wpdb->get_results('SELECT * FROM ' . $tableName);
    foreach ($allPaymentTypes as $paymentType) {
      echo '<tr>';
      echo '<td>' . $paymentType->id . '</td>';
      echo '<td>' . $paymentType->region . '</td>';
      echo '<td>' . $paymentType->reester_number . '</td>';
      echo '<td>' . $paymentType->type_of_payment . '</td>';
      echo '<td>' . $paymentType->receipt_name . '</td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>