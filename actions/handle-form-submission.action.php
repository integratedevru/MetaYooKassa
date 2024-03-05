<?php

function handle_form_submission() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['type_of_payment'])
    && isset($_POST['district']) 
    && isset($_POST['account_number']) 
    && isset($_POST['amount'])
  ) {
    $type_of_payment = sanitize_text_field($_POST['type_of_payment']);
    $district = sanitize_text_field($_POST['district']);
    $account_number = sanitize_text_field($_POST['account_number']);
    $amount = sanitize_text_field($_POST['amount']);

    $counters = array();

    for ($i = 0; $i <= 5; $i++) {
      if (isset($_POST['counter' . $i . 'ServiceName']) && isset($_POST['counter' . $i . 'MeterNumber']) &&
        isset($_POST['counter' . $i . 'OldReading']) && isset($_POST['counter' . $i . 'NewReading'])) {
        $service_name = sanitize_text_field($_POST['counter' . $i . 'ServiceName']);
        $meter_number = sanitize_text_field($_POST['counter' . $i . 'MeterNumber']);
        $old_reading = sanitize_text_field($_POST['counter' . $i . 'OldReading']);
        $new_reading = sanitize_text_field($_POST['counter' . $i . 'NewReading']);
        $counters[] = $service_name . '@' . $meter_number . '@@' . $new_reading;
        // $counters[] = array(
        //   'service_name' => sanitize_text_field($_POST['counter' . $i . 'ServiceName']),
        //   'meter_number' => sanitize_text_field($_POST['counter' . $i . 'MeterNumber']),
        //   'old_reading' => sanitize_text_field($_POST['counter' . $i . 'OldReading']),
        //   'new_reading' => sanitize_text_field($_POST['counter' . $i . 'NewReading']),
        // );
      }
    }

    $api_url = 'https://api.yookassa.ru/v3/payments';
    $api_key = get_option('meta_yookassa_shop_id') . ':' . get_option('meta_yookassa_secret_key');
    $idempotence_key = 'key' . uniqid();
    $return_url = home_url('');

    $enable_test_mode = get_option('meta_yookassa_enable_test_mode');

    $request_data = array(
      'amount' => array(
        'value' => $amount,
        'currency' => 'RUB',
      ),
      'confirmation' => array(
        'type' => 'redirect',
        'return_url' => $return_url,
      ),
      'description' => "Оплата услуг жкх по счёту $account_number ($district, тип платежа $type_of_payment). Переданы показатели счётчиков: " . count($counters),
      'metadata' => array(
        'type_of_payment' => $type_of_payment,
        'district' => $district,
        'account_number' => $account_number,
        'counters' => implode('\n', array_column($counters, 'service_name')),
      ),
      'test' => $enable_test_mode,
      'capture' => true,
      'refundable' => false,
    );

    $json_data = json_encode($request_data);
    echo ''. $json_data .'';

    $curl_options = array(
      CURLOPT_URL => $api_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_USERPWD => $api_key,
      CURLOPT_HTTPHEADER => array(
        'Idempotence-Key: ' . $idempotence_key,
        'Content-Type: application/json',
      ),
      CURLOPT_POSTFIELDS => $json_data,
    );

    $curl = curl_init();
    curl_setopt_array($curl, $curl_options);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'cURL Error: ' . curl_error($curl);
    }

    curl_close($curl);
    echo ''. $response .'';
    wp_redirect(json_decode($response)->confirmation->confirmation_url);
    exit();
  }
}