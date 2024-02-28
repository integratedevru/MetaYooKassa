<?php

function yookassa_download_data() {
  check_ajax_referer('yookassa_donwload_data_nonce', 'nonce');
  $api_url = 'https://api.yookassa.ru/v3/payments?status=succeeded';
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

  $csv_content = "Full Name;Captured At;District;Account Number;Amount;Income Amount\n";

  foreach ($data['items'] as $item) {
    $amount = $item['amount']['value'];
    $income_amount = $item['income_amount']['value'];
    $captured_at = $item['captured_at'];
    $account_number = isset($item['metadata']['account_number']) ? $item['metadata']['account_number'] : '';
    $full_name = isset($item['metadata']['full_name']) ? $item['metadata']['full_name'] : '';
    $district = isset($item['metadata']['district']) ? $item['metadata']['district'] : '';

    $csv_content .= "$full_name;$captured_at;$district;$account_number;$amount;$income_amount\n";
  }

  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="yookassa_data.csv"');

  echo $csv_content;
  exit();
}