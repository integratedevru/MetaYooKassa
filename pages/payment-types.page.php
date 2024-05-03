<?php
global $wpdb;

$tableName = $wpdb->prefix . 'metayookassa_payment_types';

function redirect($url)
{
  $string = '<script type="text/javascript">';
  $string .= 'window.location = "' . $url . '"';
  $string .= '</script>';
  echo $string;
}

if (isset($_POST['button_update'])) {
  $edit_id = intval($_POST['edit_id']);
  $new_id = intval($_POST['new_id']);
  $edit_region = sanitize_text_field($_POST['edit_region']);
  $edit_reester_number = sanitize_text_field($_POST['edit_reester_number']);
  $edit_type_of_payment = sanitize_text_field($_POST['edit_type_of_payment']);
  $edit_receipt_name = sanitize_text_field($_POST['edit_receipt_name']);
  if ($edit_id !== $new_id) {
    $found = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableName WHERE id = %d", $new_id));
    if ($found) {
      echo '<div class="notice notice-error notice-alt"><p><b>Запись c таким ID = ' . $new_id . ' уже существует!</b></p></div><br /><a href="#" class="button button-primary" onclick="history.go(-1)">Назад</a>';
    }
  } else {
    $wpdb->update(
      $tableName,
      array(
        'id' => $new_id,
        'region' => $edit_region,
        'reester_number' => $edit_reester_number,
        'type_of_payment' => $edit_type_of_payment,
        'receipt_name' => $edit_receipt_name,
        'is_manual' => true
      ),
      array('id' => $edit_id)
    );
    echo '<div class="notice notice-success notice-alt"><p><b>Запись ' . $edit_id . ' успешно обновлена!</b></p></div> ';
    redirect('?page=meta_yookassa_payment_types');
  }
  exit;
}

if (isset($_POST['button_create'])) {
  $new_id = intval($_POST['new_id']);
  $new_region = sanitize_text_field($_POST['new_region']);
  $new_reester_number = sanitize_text_field($_POST['new_reester_number']);
  $new_type_of_payment = sanitize_text_field($_POST['new_type_of_payment']);
  $new_receipt_name = sanitize_text_field($_POST['new_receipt_name']);
  $found = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableName WHERE id = %d", $new_id));
  if ($found) {
    echo '<div class="notice notice-error notice-alt"><p><b>Запись c таким ID = ' . $new_id . ' уже существует!</b></p></div><br /><a href="#" class="button button-primary"  onclick="history.go(-1)">Назад</a>';
  } else {
    $wpdb->insert(
      $tableName,
      array(
        'id' => $new_id,
        'region' => $new_region,
        'reester_number' => $new_reester_number,
        'type_of_payment' => $new_type_of_payment,
        'receipt_name' => $new_receipt_name,
        'is_manual' => true
      )
    );
    echo '<div class="notice notice-success notice-alt"><p><b>Запись ' . $new_id . ' успешно добавлена!</b></p></div> ';
    redirect('?page=meta_yookassa_payment_types');
  }
  exit;
}

if (isset($_GET['edit'])) {
  include plugin_dir_path(__FILE__) . '../subpages/payment-type-edit.subpage.php';
}

if (isset($_GET['create'])) {
  include plugin_dir_path(__FILE__) . '../subpages/payment-type-create.subpage.php';
}

if (isset($_GET['delete'])) {
  $delete_id = intval($_GET['delete']);
  $result = $wpdb->delete($tableName, array('id' => $delete_id), array('%d'));
  if (!$result) {
    echo '<div class="notice notice-error is-dismissible"><p><b>Не удалось удалить запись ' . $delete_id . '!</b></p></div>';
  } else {
    echo '<div class="notice notice-success is-dismissible"><p><b>Запись ' . $delete_id . ' успешно удалена!</b></p></div>';
  }
}

function parseRegion($region)
{
  $pattern = '/\S* *\(.+\)|\S* *\S*/';
  preg_match($pattern, $region, $matches);
  return $matches[0];
}

function parseReesterAndPayment($data)
{
  $pattern = '/(\d+_ *\d*)\D*(\d*)/';
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
  if (!empty($_FILES['import_file']['name']) && ($extension == 'csv' || $extension == 'txt')) {
    $totalInserted = 0;
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');
    $wpdb->query("DELETE FROM $tableName WHERE is_manual = 'false'");
    $wpdb->query("OPTIMIZE TABLE $tableName");
    $district = '';
    while (($csvData = fgetcsv($csvFile, 1000, ';')) !== false) {
      $district = empty($csvData[0]) ? $district : parseRegion($csvData[1]);
      $dataLen = count($csvData);
      $extractedData = parseReesterAndPayment($csvData[2]);
      $reesterNumber = $extractedData['reesterNumber'];
      $typeOfPayment = $extractedData['typeOfPayment'];
      $receiptName = trim($csvData[3], "! ");
      if (!empty($district) && !empty($reesterNumber) && !empty($typeOfPayment) && !empty($receiptName)) {
        $found = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableName WHERE reester_number = %s AND type_of_payment = %s AND region = %s", $reesterNumber, $typeOfPayment, $district));
        if ($found) {
          echo '<div class="notice notice-error is-dismissible"><p>Запись с реестровым номером "' . $reesterNumber . '", типом платежа "' . $typeOfPayment . '" и районом "' . $district . '" уже существует (ID = ' . $found->id . ').</p></div>';
        } else {
          $wpdb->insert(
            $tableName,
            array(
              'region' => sanitize_text_field($district),
              'reester_number' => sanitize_text_field($reesterNumber),
              'type_of_payment' => sanitize_text_field($typeOfPayment),
              'receipt_name' => sanitize_text_field($receiptName)
            )
          );
          $totalInserted++;
        }
      }
    }
    echo '<div class="notice notice-success is-dismissible"><p>Успешно добавлено: ' . $totalInserted . '</p></div>';
  }
}
?>

<?php
if (!isset($_GET['edit']) && !isset($_GET['create'])) {
  include plugin_dir_path(__FILE__) . '../subpages/payment-types-list.subpage.php';
}
