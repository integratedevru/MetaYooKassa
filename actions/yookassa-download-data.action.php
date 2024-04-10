<?php

function yookassa_download_data() {
  error_log('yookassa_send_data method executed at ' . current_time('mysql'));
  // check_ajax_referer('yookassa_donwload_data_nonce', 'nonce');
  send_message();
  exit();
}

function send_message() {
  $data = get_success_payments_data();
  $attachments = array();
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
    $key_code = get_district_key_code($key);
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Payings.txt';
    $content = $value;
    $temp_file = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($temp_file, $content);
    // $mail->addAttachment($temp_file, $filename);
    $attachments[] = $temp_file;
    echo 'Added attachment: ' . $filename . "\n";
  }
  foreach ($counters_array as $key => $value) {
    $key_code = get_district_key_code($key);
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Counters.txt';
    $content = iconv('UTF-8', 'CP866', $value);
    $temp_file = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($temp_file, $content);
    // $mail->addAttachment($temp_file, $filename);
    $attachments[] = $temp_file;
    echo 'Added attachment: ' . $filename . "\n";
  }

  $to = get_option('meta_yookassa_mail_address');
  $subject = get_option('meta_yookassa_mail_subject');
  $message = 'Реестр успешных платежей за последние сукти.';

  $sent = wp_mail($to, $subject, $message, '', $attachments);

  if ($sent) {
    echo 'Email sent successfully!';
  } else {
    echo 'Error: Email not sent.';
  }
}

function get_district_key_code($district) {
  global $wpdb;
  $table_name = $wpdb->prefix . "metayookassa_payment_types";
  $result = $wpdb->get_var("SELECT reester_number FROM $table_name WHERE region = '$district' LIMIT 1");
  return $result;
}

function push_payments($payments_data, $payments_array, $counters_array) {
  foreach ($payments_data as $payment) {
    $district = $payment['metadata']['district'];
    $type_of_payment = $payment['metadata']['type_of_payment'];
    $account_number = $payment['metadata']['account_number'];
    $value = $payment['amount']['value'];
    $incomeValue = $payment['income_amount']['value'];
    $counters = explode(';', $payment['metadata']['counters']);
    $payments_string = ";$type_of_payment;$account_number;$incomeValue;";
    $counters_string = null;
    if (count($counters) > 0) {
      $counters_string = "";
      foreach ($counters as $counter) {
        if ($counter === '') continue;
        $counters_string .= "$account_number@$counter@@@@@\n";
      }
    }
    if (array_key_exists($district, $payments_array)) {
      $payments_array[$district] .= "\n" . $payments_string;
    } else {
      $payments_array[$district] = $payments_string;
    }
    if ($counters_string !== null && $counters_string !== '') {
      if (array_key_exists($district, $counters_array)) {
        $counters_array[$district] .= $counters_string;
      } else {
        $counters_array[$district] = $counters_string;
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
