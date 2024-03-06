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
  generate_files();
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
  $mail->Body = 'This is a test email sent from PHP using Google SMTP.';

  $file_content = 'This is the content of the attached file.';
  $temp_file = tempnam(sys_get_temp_dir(), 'attachment');
  file_put_contents($temp_file, $file_content);
  $mail->addAttachment($temp_file, 'attachment.txt');

  if ($mail->send()) {
      echo 'Email sent successfully!';
  } else {
      echo 'Error: ' . $mail->ErrorInfo;
  }
}

function generate_files() {
  $data = get_success_payments_data();
  $payments_array = array();
  $counters_array = array();
  if (array_key_exists('next_cursor', $data)) {
    $next_cursor = $data['next_cursor'];
  }
  // while (!empty($next_cursor)) {
  //   $data = get_success_payments_data($next_cursor);
  //   $next_cursor = $data['next_cursor'];
  // }

  foreach ($payments_array as $key => $value) {
    $key_code = 123;
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Payings.txt';
    $content = $value;
    create_file($filename, $content);
  }
  foreach ($counters_array as $key => $value) {
    $key_code = 123;
    $filename = $key_code . '_' . date('y_m_d') . '_Inary_Counters.txt';
    $content = $value;
    create_file($filename, $content);
  }
}

function push_payments($payments_data, $payments_array, $counters_array) {
  foreach ($payments_data as $payment) {
    $region = $payment['metadata']['region'];
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
}

function get_success_payments_data($cursor = null) {
  $api_url = 'https://api.yookassa.ru/v3/payments?status=succeeded&limit=100';
  if ($cursor) {
    $api_url .= '&cursor=' . $cursor;
  }
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

function connect_fs($url, $method, $context, $fields = null)
{
  global $wp_filesystem;
  if(false === ($credentials = request_filesystem_credentials($url, $method, false, $context, $fields))) 
  {
    return false;
  }
  if(!WP_Filesystem($credentials)) 
  {
    request_filesystem_credentials($url, $method, true, $context);
    return false;
  }
  return true;
}

function write_file_demo($text)
{
  global $wp_filesystem;

  $url = wp_nonce_url("options-general.php?page=demo", "filesystem-nonce");
  $form_fields = array("file-data");

  if(connect_fs($url, "", WP_PLUGIN_DIR . "/filesystem/filesystem-demo", $form_fields))
  {
    $dir = $wp_filesystem->find_folder(WP_PLUGIN_DIR . "/filesystem/filesystem-demo");
    $file = trailingslashit($dir) . "demo.txt";
    $wp_filesystem->put_contents($file, $text, FS_CHMOD_FILE);

    return $text;
  }
  else
  {
    return new WP_Error("filesystem_error", "Cannot initialize filesystem");
  }
}

function create_file($filename, $content) {
  global $wp_filesystem;
  $url = wp_nonce_url("options-general.php?page=demo", "filesystem-nonce");
  $form_fields = array("file-data");
  if(connect_fs($url, "", WP_PLUGIN_DIR . "/filesystem/filesystem-demo", $form_fields))
  {
    $dir = $wp_filesystem->find_folder(WP_PLUGIN_DIR . "/filesystem/filesystem-demo");
    $file = trailingslashit($dir) . $filename;
    $wp_filesystem->put_contents($file, $content, FS_CHMOD_FILE);
    return $content;
  }
  else
  {
    return new WP_Error("filesystem_error", "Cannot initialize filesystem");
  }
  // $upload_dir = wp_upload_dir();
  // $directory = $upload_dir['basedir'];
  // $filepath = $directory . '/'.'metayookassa'.'/' . $filename;
  // $file = fopen($filepath, 'w');
  // fwrite($file, $content);
  // fclose($file);
  // return $filepath;
}