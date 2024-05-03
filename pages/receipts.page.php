<?php
global $wpdb;

$paymentTypesTable = $wpdb->prefix . 'metayookassa_payment_types';
$invoicesTable = $wpdb->prefix . 'metayookassa_invoice';
$counterValuesTable = $wpdb->prefix . 'metayookassa_counter_value';

function findDistrict($reesterNumber) {
  global $wpdb;
  $paymentTypesTable = $wpdb->prefix . 'metayookassa_payment_types';

  $startsWith = explode('_', $reesterNumber)[0];
  $result = $wpdb->get_row(
    "SELECT region FROM $paymentTypesTable WHERE reester_number LIKE '$startsWith%' LIMIT 1");
  return $result->region;
}

function isExistsTypeOfPaymentInRegion($type_of_payment, $region) {
  global $wpdb;
  $paymentTypesTable = $wpdb->prefix . 'metayookassa_payment_types';
  $result = $wpdb->get_var(
    "SELECT id FROM $paymentTypesTable WHERE type_of_payment = '$type_of_payment' AND region = '$region' LIMIT 1");
  return !empty($result);
}

function truncateAll() {
  global $wpdb;
  $invoicesTable = $wpdb->prefix . 'metayookassa_invoice';
  $counterValuesTable = $wpdb->prefix . 'metayookassa_counter_value';
  $wpdb->query("SET FOREIGN_KEY_CHECKS = 0;");
  $wpdb->query("TRUNCATE TABLE $counterValuesTable;");
  $wpdb->query("TRUNCATE TABLE $invoicesTable;");
  $wpdb->query("SET FOREIGN_KEY_CHECKS = 1;");
}

if (isset($_POST['button_import'])) {
  foreach ($_FILES['import_file']['tmp_name'] as $key => $tmp_name) {
    $file_name = $_FILES['import_file']['name'][$key];
    $file_tmp = $_FILES['import_file']['tmp_name'][$key];
    $file_type = $_FILES['import_file']['type'][$key];
    $file_error = $_FILES['import_file']['error'][$key];
    $region = sanitize_text_field($_POST['region']);
    $region = $region === 'null' ? null : $region;
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    if (!empty($file_name) && ($extension == 'csv' || $extension == 'txt')) {
      echo '<div class="notice notice-info is-dismissible"><p>Файл импортирован: ' . $file_name . '</p></div>';
      $totalInserted = 0;
      $countersInserted = 0;
      $csvFile = fopen($file_tmp, 'r');
      $reesterNumber = explode('.', $file_name)[0];
      $district = $region ? $region : findDistrict($reesterNumber);
      if (empty($district)) {
        echo '<div class="notice notice-error notice-alt"><p><b>Идентификатор услуги в имени файла (' . $reesterNumber .  ' или ' . explode('_', $reesterNumber)[0] . ') не соответствует загруженным в базу данных (номер услуги Сбербанка)</b></p></div>';
      } else {
        while (($csvData = fgetcsv($csvFile, 1000, ';')) !== FALSE) {
          $address = iconv('windows-1251', 'utf-8', $csvData[3]);
          $invoiceNumber = trim($csvData[4]);
          $amount = $csvData[5];
          $unifiedNumber = $csvData[6];
          $counters = explode(':', $csvData[7]);
          for ($i = 0; $i < count($counters); $i += 3) {
            if ($counters[$i] == '[!]') {
              $typeOfPayment = $counters[$i+1];
            }
          }
          if (!isExistsTypeOfPaymentInRegion($typeOfPayment, $district)) {
            echo '<div class="notice notice-error notice-alt"><p><b>Тип платежа ' . $typeOfPayment . ' в "' . $district . '" не найден</b></p></div>';
            break;
          }
          $found = $wpdb->get_row(
            "SELECT id 
            FROM $invoicesTable 
            WHERE region = '$district'
              AND type_of_payment = '$typeOfPayment' 
              AND invoice_number = '$invoiceNumber'
            LIMIT 1"
          );
          if ($found) {
            $wpdb->delete(
              $counterValuesTable,
              array(
                'invoice_id' => $found->id
              )
            );
            $wpdb->delete(
              $invoicesTable,
              array(
                'id' => $found->id
              )
            );
          }
          if (!empty($reesterNumber) && !empty($address) && !empty($invoiceNumber) && !empty($amount)) {
            $wpdb->insert(
              $invoicesTable,
              array(
                'reester_number' => sanitize_text_field($reesterNumber),
                'address' => sanitize_text_field($address),
                'region' => sanitize_text_field($district),
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
              } else {
                ++$i;
              }
            }
            $totalInserted++;
          }
        }
        fclose($csvFile);
        echo '<div class="notice notice-success is-dismissible">
                <p><b>' . $district . '</b></p>
                <p>Квитанций добавлено/обновлено: ' . $totalInserted . '</p>
                <p>Показателей добавлено/обновлено: ' . $countersInserted . '</p>
              </div>';
      }
    }
  }
}
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

<h2>Квитанции</h2>

<form class="form-table" method="post" enctype="multipart/form-data">
  <select name="region">
    <option value="null" selected><i>Автоматическое определение</i></option>
    <?php
    $regions = $wpdb->get_results("SELECT DISTINCT region FROM wp_metayookassa_payment_types ORDER BY region");
    foreach ($regions as $region) {
      echo '<option value="' . $region->region . '">' . $region->region . '</option>';
    }
    ?>
  </select>
  <input type="file" name="import_file[]" accept=".csv,.txt" multiple>
  <input class="button button-primary" type="submit" name="button_import" value="Импортировать (.csv или .txt)">
</form>
<br />

<table class="wp-list-table widefat fixed striped meta-table">
  <thead>
    <tr>
      <th><b>Район</b></th>
      <th><b>Тип платежа</b></th>
      <th><b>Квитанции</b></th>
      <th><b>Показания счётчиков</b></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $stats = $wpdb->get_results(
      "SELECT 
        region, 
        type_of_payment, 
        COUNT(invoice.id) as invoices,
        COUNT(counter.id) as counters
      FROM wp_metayookassa_invoice AS invoice
        LEFT JOIN wp_metayookassa_counter_value AS counter ON counter.invoice_id = invoice.id
      GROUP BY region, type_of_payment;");
    foreach ($stats as $stat) {
      echo '<tr>';
      echo '<td>' . $stat->region . '</td>';
      echo '<td class="numeric-cell">' . $stat->type_of_payment . '</td>';
      echo '<td class="numeric-cell">' . $stat->invoices . '</td>';
      echo '<td class="numeric-cell">' . $stat->counters . '</td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>
