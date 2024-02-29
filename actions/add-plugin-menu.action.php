<?php

function add_plugin_menu() {
  add_menu_page(
    'Настройки плагина Meta ЮKassa',
    'Meta ЮKassa',
    'manage_options',
    'meta_yookassa_settings',
    'settings_page'
  );
  add_submenu_page(
    'meta_yookassa_settings',
    'Типы платежей плагина Meta ЮKassa',
    'Типы платежей',
    'manage_options',
    'meta_yookassa_payment_types',
    'payment_types_page'
  );
  add_submenu_page(
    'meta_yookassa_settings',
    'Квитанции Meta ЮKassa',
    'Квитанции',
    'manage_options',
    'meta_yookassa_receipts',
    'receipts_page'
  );
}

function settings_page() {
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

function payment_types_page() {
  include plugin_dir_path(__FILE__) . '../pages/payment-types.page.php';
}

function receipts_page() {
  include plugin_dir_path(__FILE__) . '../pages/receipts.page.php';
}