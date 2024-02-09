<?php
/*
Plugin Name: Meta YooKassa
Description: Form for paymeny via YooKassa and download data of successful payments.
Version: 1.0
Author: MetaSystems (for PKS)
*/

function custom_form_html() {
  ob_start(); ?>
  <style>
    #custom-form {
      max-width: 400px;
      margin: 0 auto;
      font-family: Arial, sans-serif;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 15px;
    }
    .form-label {
      display: block;
      margin-top: 10px;
    }
    .form-input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    #meta-yookassa-form .form-submit {
      background-color: #4caf50;
      color: white;
      cursor: pointer;
      margin-top: 10px;
      padding: 10px;
      border: none;
      border-radius: 5px;
      font-weight: 900;
    }
    #meta-yookassa-form .form-submit:hover {
      background-color: #45a049;
    }
  </style>
  <form id="meta-yookassa-form" method="post">
    <label class="form-label" for="full_name">ФИО:</label>
    <input class="form-input" style="width: 100%;" type="text" name="full_name" required>
    <label class="form-label" for="district">Район:</label>
    <select class="form-input" style="width: 100%;" name="district" required>
        <option value="" disabled selected>Выберите район</option>
        <option value="Бежаницкий район">Бежаницкий район</option>
        <option value="Великолукский район">Великолукский район</option>
        <option value="Гдовский район">Гдовский район</option>
        <option value="Дедовичский район">Дедовичский район</option>
        <option value="Дновский район">Дновский район</option>
        <option value="Красногородский район">Красногородский район</option>
        <option value="Куньинский район">Куньинский район</option>
        <option value="Локнянский район">Локнянский район</option>
        <option value="Невельский район">Невельский район</option>
        <option value="Новоржевский район">Новоржевский район</option>
        <option value="Новосокольнический район">Новосокольнический район</option>
        <option value="Опочецкий район">Опочецкий район</option>
        <option value="Островский район">Островский район</option>
        <option value="Палкинский район">Палкинский район</option>
        <option value="Печорский район">Печорский район</option>
        <option value="Плюсский район">Плюсский район</option>
        <option value="Порховский район">Порховский район</option>
        <option value="Псков">Псков</option>
        <option value="Псковский район">Псковский район</option>
        <option value="Пустошкинский район">Пустошкинский район</option>
        <option value="Пушкиногорский район">Пушкиногорский район</option>
        <option value="Пыталовский район">Пыталовский район</option>
        <option value="Себежский район">Себежский район</option>
        <option value="Стругокрасненский район">Стругокрасненский район</option>
        <option value="Усвятский район">Усвятский район</option>
    </select>
    <label class="form-label" for="account_number">Номер лицевого счёта ЖКХ:</label>
    <input class="form-input" type="text" pattern="\d+" name="account_number" required>
    <label class="form-label" for="amount">Сумма (руб.):</label>
    <input class="form-input" type="text" pattern="\d+(\.\d{1,2})?" name="amount" required> 
    <input class="form-submit" type="submit" value="Оплатить с помощью ЮKassa">
  </form>
  <?php
  return ob_get_clean();
}

function handle_form_submission() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['full_name']) 
    && isset($_POST['district']) 
    && isset($_POST['account_number']) 
    && isset($_POST['amount'])
  ) {
    $full_name = sanitize_text_field($_POST['full_name']);
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
      'description' => "Оплата услуг жкх $district по счёту $account_number от $full_name",
      'metadata' => array(
        'full_name' => $full_name,
        'district' => $district,
        'account_number' => $account_number,
      ),
      'test' => $enable_test_mode,
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
add_action('init', 'handle_form_submission');

function display_custom_form() {
  return custom_form_html();
}
add_shortcode('custom_form', 'display_custom_form');

function meta_yookassa_plugin_menu() {
  add_menu_page(
      'Настройки плагина Meta ЮKassa',
      'Meta ЮKassa',
      'manage_options',
      'meta_yookassa_settings',
      'meta_yookassa_settings_page'
  );
}

function meta_yookassa_settings_page() {
  ?>
  <div class="wrap">
      <h2>Настройки плагина Meta ЮKassa</h2>
      <form method="post" action="options.php">
          <?php
              settings_fields('meta_yookassa_settings');
              do_settings_sections('meta_yookassa_settings');
              submit_button();
          ?>
      </form>
      <button id="downloadDataButton" class="button">Скачать реестр успешных платежей (.csv)</button>
      <script>
        document.getElementById('downloadDataButton').addEventListener('click', downloadData);
        function downloadData() {
          let nonce = '<?php echo wp_create_nonce('yookassa_donwload_data_nonce'); ?>';
          jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'yookassa_download_data',
              nonce,
            },
            success: function (data) {
              const blob = new Blob([data], { type: 'text/csv' });
              const link = document.createElement('a');
              link.href = window.URL.createObjectURL(blob);
              link.download = `YookassaPayments${new Date().toISOString()}.csv`;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
            },
            error: function (error) {
              console.log('Error:', error);
            },
          });
        }
      </script>
  </div>
  <?php
}
add_action('admin_menu', 'meta_yookassa_plugin_menu');

function meta_yookassa_register_settings() {
  register_setting(
      'meta_yookassa_settings',
      'meta_yookassa_shop_id',
  );
  register_setting(
      'meta_yookassa_settings',
      'meta_yookassa_secret_key',
  );
  register_setting(
      'meta_yookassa_settings',
      'meta_yookassa_enable_test_mode',
  );

  add_settings_section(
      'meta_yookassa_settings_section',
      'Настройки интеграции с ЮKassa',
      'meta_yookassa_settings_section_callback',
      'meta_yookassa_settings',
  );
  add_settings_field(
      'meta_yookassa_shop_id',
      'ID магазина ЮKassa',
      'meta_yookassa_shop_id_callback',
      'meta_yookassa_settings',
      'meta_yookassa_settings_section',
  );
  add_settings_field(
      'meta_yookassa_secret_key',
      'Секретный ключ ЮKassa',
      'meta_yookassa_secret_key_callback',
      'meta_yookassa_settings',
      'meta_yookassa_settings_section',
  );
  add_settings_field(
      'meta_yookassa_enable_test_mode',
      'Включить тестовый режим',
      'meta_yookassa_enable_test_mode_callback',
      'meta_yookassa_settings',
      'meta_yookassa_settings_section'
  );
}
add_action('admin_init', 'meta_yookassa_register_settings');

function meta_yookassa_settings_section_callback() {
  echo '<p></p>';
}

function meta_yookassa_shop_id_callback() {
  $shop_id = esc_attr(get_option('meta_yookassa_shop_id'));
  echo '<input type="text" name="meta_yookassa_shop_id" value="' . $shop_id . '" />';
}

function meta_yookassa_secret_key_callback() {
  $secret_key = esc_attr(get_option('meta_yookassa_secret_key'));
  echo '<input type="text" name="meta_yookassa_secret_key" value="' . $secret_key . '" />';
}

function meta_yookassa_enable_test_mode_callback() {
  $enable_test_mode = get_option('meta_yookassa_enable_test_mode');
  ?>
  <input type="checkbox" name="meta_yookassa_enable_test_mode" <?php checked($enable_test_mode, 1); ?> value="1" />
  <?php
}

function yookassa_download_data_callback() {
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

  $csv_content = "Full Name,Captured At,District,Account Number,Amount,Income Amount\n";

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

add_action('wp_ajax_yookassa_download_data', 'yookassa_download_data_callback');
