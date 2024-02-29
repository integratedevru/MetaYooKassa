<?php
global $wpdb;

$invoicesTable = $wpdb->prefix . 'metayookassa_invoices';
$counterValuesTable = $wpdb->prefix . 'metayookassa_counter_values';

if (isset($_POST['button_import'])) {
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
  if (!empty($_FILES['import_file']['name']) && ($extension == 'csv' || $extension == 'txt')) {
    $totalInserted = 0;
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
    $wpdb->query("TRUNCATE TABLE $invoicesTable;");
    $wpdb->query("TRUNCATE TABLE $counterValuesTable;");
    while (($csvData = fgetcsv($csvFile, 1000, ';')) !== FALSE) {
      
    }
    echo 'Успешно добавлено: ' . $totalInserted;
  }
}
?>

<h2>Квитанции</h2>

<form method="post" enctype="multipart/form-data">
  <input type="file" name="import_file" accept=".csv">
  <input type="submit" name="button_import" value="Импортировать (.csv или .txt)">
</form>
