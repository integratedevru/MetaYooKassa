<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../dependencies/PHPMailer/src/Exception.php';
require __DIR__ . '/../dependencies/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../dependencies/PHPMailer/src/SMTP.php';

function yookassa_download_data() {
  check_ajax_referer('yookassa_donwload_data_nonce', 'nonce');
  send_message();
  exit();
}

function send_message() {
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->Host = get_option('meta_yookassa_mail_host');
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->SMTPAuth = true;
  $mail->SMTPAutoTLS = false;
  $mail->Username = get_option('meta_yookassa_mail_username');
  $mail->Password = get_option('meta_yookassa_mail_password');
  $mail->Port = get_option('meta_yookassa_mail_port');
  $mail->setFrom(get_option('meta_yookassa_mail_username'), get_option('meta_yookassa_mail_name'));
  $mail->addAddress(get_option('meta_yookassa_mail_address'));
  $mail->Subject = get_option('meta_yookassa_mail_subject');
  $mail->Body = 'Реестр успешных платежей.';

  $data = get_success_payments_data();
  $payments_array = array();
  $counters_array = array();
  $new_arrays = push_payments($data["items"], $payments_array, $counters_array);
  $payments_array = $new_arrays['payments'];
  $counters_array = $new_arrays['counters'];

  while (array_key_exists('next_cursor', $data)) {
    $next_cursor = $data['next_cursor'];
    $data = get_success_payments_data($next_cursor);
    $new_arrays = push_payments($data["items"], $payments_array, $counters_array);
    $payments_array = $new_arrays['payments'];
    $counters_array = $new_arrays['counters'];
  }

  echo count($payments_array) . ' payments added' . "\n";
  echo count($counters_array) . ' counters added' . "\n";

  foreach ($payments_array as $key => $value) {
    $key_code = get_region_key_code($key);
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Payings.txt';
    $content = $value;
    $temp_file = tempnam(sys_get_temp_dir(), 'attachment');
    file_put_contents($temp_file, $content);
    $mail->addAttachment($temp_file, $filename);
    echo 'Added attachment: ' . $filename . "\n";
  }
  foreach ($counters_array as $key => $value) {
    $key_code = get_region_key_code($key);
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Counters.txt';
    $content = iconv('UTF-8', 'CP866', $value);
    $temp_file = tempnam(sys_get_temp_dir(), 'attachment');
    file_put_contents($temp_file, $content);
    $mail->addAttachment($temp_file, $filename);
    echo 'Added attachment: ' . $filename . "\n";
  }
  if ($mail->send()) {
      echo 'Email sent successfully!';
  } else {
      echo 'Error: ' . $mail->ErrorInfo;
  }
}

function get_region_key_code($region) {
  global $wpdb;
  $table_name = $wpdb->prefix . "metayookassa_payment_types";
  $result = $wpdb->get_var("SELECT reester_number FROM $table_name WHERE region = '$region' LIMIT 1");
  return $result;
}

function push_payments($payments_data, $payments_array, $counters_array) {
  foreach ($payments_data as $payment) {
    $region = $payment['metadata']['district'];
    $type_of_payment = $payment['metadata']['type_of_payment'];
    $account_number = $payment['metadata']['account_number'];
    $value = $payment['amount']['value'];
    $counters = explode(';', $payment['metadata']['counters']);
    $payments_string = ";$type_of_payment;$region;$account_number;$value;";
    $counters_string = null;
    if (count($counters) > 0) {
      $counters_string = "";
      foreach ($counters as $counter) {
        $counters_string .= "$account_number@$counter@@@@@\n";
      }
    }
    if (array_key_exists($region, $payments_array)) {
      $payments_array[$region] .= "\n" . $payments_string;
    } else {
      $payments_array[$region] = $payments_string;
    }
    if ($counters_string) {
      if (array_key_exists($region, $counters_array)) {
        $counters_array[$region] .= $counters_string;
      } else {
        $counters_array[$region] = $counters_string;
      } 
    }
  }
  return array(
    'payments' => $payments_array,
    'counters' => $counters_array,
  );
}

function get_success_payments_data($cursor = null) {
  $api_url = 'https://api.yookassa.ru/v3/payments?status=succeeded&limit=100';
  if ($cursor) {
    $api_url .= '&cursor=' . $cursor;
  }
  $api_url .= '&created_at.gte=' . date('Y-m-d\TH:i:s.000\Z', strtotime('-1 day'));
  echo $api_url . "\n";
  $api_key = get_option('meta_yookassa_shop_id') . ':' . get_option('meta_yookassa_secret_key');
  $curl_options = array(
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_USERPWD => $api_key,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
    ),
  );
  $curl = curl_init();
  curl_setopt_array($curl, $curl_options);
  $response = curl_exec($curl);
  if (curl_errno($curl)) {
    wp_send_json_error('cURL Error: ' . curl_error($curl));
  }
  curl_close($curl);
  $data = json_decode($response, true);
  return $data;
}
