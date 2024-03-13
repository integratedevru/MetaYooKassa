<?php

function metayookassa_register_settings() {
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
  register_setting(
      'meta_yookassa_settings',
      'meta_yookassa_mail_subject',
  );
  register_setting(
      'meta_yookassa_settings',
      'meta_yookassa_mail_address',
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
  add_settings_section(
      'meta_yookassa_settings_section_2',
      'Настройки отправки данных по электронной почте',
      'meta_yookassa_settings_section_2_callback',
      'meta_yookassa_settings',
  );
  add_settings_field(
      'meta_yookassa_mail_subject',
      'Тема письма',
      'meta_yookassa_mail_subject_callback',
      'meta_yookassa_settings',
      'meta_yookassa_settings_section_2',
  );
  add_settings_field(
      'meta_yookassa_mail_address',
      'Адрес электронной почты получателя',
      'meta_yookassa_mail_address_callback',
      'meta_yookassa_settings',
      'meta_yookassa_settings_section_2',
  );
}

function meta_yookassa_settings_section_callback() {
  echo '';
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

function meta_yookassa_settings_section_2_callback() {
  echo '';
}

function meta_yookassa_mail_subject_callback() {
  $mail_subject = esc_attr(get_option('meta_yookassa_mail_subject'));
  echo '<input type="text" name="meta_yookassa_mail_subject" value="' . $mail_subject . '" />';
}

function meta_yookassa_mail_address_callback() {
  $mail_address = esc_attr(get_option('meta_yookassa_mail_address'));
  echo '<input type="text" name="meta_yookassa_mail_address" value="' . $mail_address . '" />';
}