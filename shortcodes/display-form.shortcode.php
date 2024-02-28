<?php

function form_html() {
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

function display_form() {
  return form_html();
}