<?php

function handle_form_submission() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['type_of_payment'])
    && isset($_POST['district']) 
    && isset($_POST['account_number']) 
    && isset($_POST['amount'])
  ) {
    error_log('Form was submitted');
    $type_of_payment = sanitize_text_field($_POST['type_of_payment']);
    $district = sanitize_text_field($_POST['district']);
    $account_number = sanitize_text_field($_POST['account_number']);
    $amount = sanitize_text_field($_POST['amount']);

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
      'capture' => true,
      'confirmation' => array(
        'type' => 'redirect',
        'return_url' => $return_url,
      ),
      'description' => "Оплата услуг жкх по району $district и типу $type_of_payment. Счёт $account_number",
      'metadata' => array(
        'type_of_payment' => $type_of_payment,
        'district' => $district,
        'account_number' => $account_number,
      ),
      'test' => $enable_test_mode,
      "capture" => true,
      "refundable" => false,
    );

    $json_data = json_encode($request_data);

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