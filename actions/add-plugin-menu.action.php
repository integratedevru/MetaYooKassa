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
  </div>
  <?php
}

function payment_types_page() {
  include plugin_dir_path(__FILE__) . '../pages/payment-types.page.php';
}

function receipts_page() {
  include plugin_dir_path(__FILE__) . '../pages/receipts.page.php';
}