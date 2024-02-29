<?php

function form_html() {
  global $wpdb;

  $tableName = $wpdb->prefix . 'metayookassa_payment_types';
  $districtOptions = $wpdb->get_col("SELECT DISTINCT region FROM $tableName ORDER BY region");

  ob_start(); ?>
  <style>
    #meta-yookassa-form {
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
        <?php foreach ($districtOptions as $option) { echo "<option value='$option'>$option</option>"; } ?>
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

function display_form() {
  return form_html();
}