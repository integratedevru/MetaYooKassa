<?php

function form_html() {
  global $wpdb;

  $tableName = $wpdb->prefix . 'metayookassa_payment_types';
  $districtOptions = $wpdb->get_col("SELECT DISTINCT region FROM $tableName ORDER BY region");

  ob_start(); ?>
  <style>
    #meta-yookassa-form .hidden {
      display: none;
    }
    #meta-yookassa-form {
      max-width: 800px;
      min-width: 600px;
      margin: 0 auto;
      font-family: Arial, sans-serif;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 15px;
    }
    #meta-yookassa-form .form-label {
      display: block;
      margin-top: 10px;
    }
    #meta-yookassa-form .form-input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    #meta-yookassa-form .form-input[readonly] {
      background-color: #f0f0f0;
      pointer-events: none;
    }
    #meta-yookassa-form .form-submit {
      min-width: 140px;
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
    #meta-yookassa-form .form-submit:disabled {
      background-color: #EEEEEE;
      color: #ABABAB;
      cursor: default;
    }
    #meta-yookassa-form .form-cancel {
      min-width: 140px;
      background-color: #AD4F4C;
      color: white;
      cursor: pointer;
      margin-top: 10px;
      padding: 10px;
      border: none;
      border-radius: 5px;
      font-weight: 900;
    }
    #meta-yookassa-form .form-cancel:hover {
      background-color: #9E4C45;
    }
    #meta-yookassa-form .accept-popup {
      position: fixed;
      background-color: ghostwhite;
      padding: 20px;
      top: 50%;
      left: 50%;
      border-radius: 20px;
      border: black 2px solid;
      transform: translateX(-50%) translateY(-50%);
    }
    #meta-yookassa-form .button-group {
      display: flex;
      justify-content: space-between;
    }
  </style>

  <form id="meta-yookassa-form" method="post">
    <div id="first-part">
      <label class="form-label" for="district">Район:</label>
      <select class="form-input" style="width: 100%;" id="district" name="district" required>
        <option value="" disabled selected>Выберите район</option>
        <?php foreach ($districtOptions as $option) { echo "<option value='$option'>$option</option>"; } ?>
      </select>
      <label class="form-label" for="type_of_payment">Тип платежа:</label>
      <input class="form-input" type="text" pattern="\d+" id="type_of_payment" name="type_of_payment" title="Трёхзначный код из квитацнии" required>
      <label class="form-label" for="account_number">Лицевой счёт:</label>
      <input class="form-input" type="text" pattern="\d+" id="account_number" name="account_number" required>
      <button class="form-submit" type="button" id="switch-button" onclick="switchToSecondPart()">Продолжить</button>
      <p style="color: red" class="hidden" id="error-message"></p>
    </div>
    <div id="second-part" class="hidden">
      <label class="form-label" for="address">Адрес:</label>
      <input class="form-input" type="text" id="address" name="address" readonly>
      <p>Показатели счётчиков:</p>
      <table><tbody id="counters"></tbody></table>
      <label class="form-label" for="amount">Сумма платежа (руб.):</label>
      <input class="form-input" type="text" id="amount" name="amount" oninput="calculateCommission()">
      <br>
      <!-- <p>Коммиссия ЮКассы (0,9%): <span id="commission"></span></p>
      <p><b>Итого к оплате: <span id="total"></span></b></p> -->
      <button class="form-submit" type="button" id="next-button" onclick="switchToThirdPart()">Оплатить с помощью ЮКассы и передать показания счётчиков</button>
      <input class="form-cancel" style="margin-top: unset;" type="button" id="back-button" onclick="switchToFirstPart()" value="Назад">
    </div>
    <div id="third-part" class="hidden accept-popup">
      <p><b>Подтверждаете корректность введённых данных?</b></p>
      <div class="button-group">
        <input class="form-submit" type="submit" value="Да">
        <input class="form-cancel" type="button" id="second-back-button" onclick="switchToFirstPart()" value="Нет">
      </div>
    </div>
  </form>

  <script>
    function calculateCommission() {
        // var amount = isNaN(document.getElementById("amount").value)
        //   ? 0
        //   : document.getElementById("amount").value;
        // var total = isNaN(document.getElementById("amount").value)
        //   ? 0.01
        //   : (parseFloat(amount) / (1 - (0.009))).toFixed(2);
        // var commission = (parseFloat(total) - parseFloat(amount)).toFixed(2);
        // document.getElementById("commission").innerText = commission;
        // document.getElementById("total").innerText = total;
    }
    function switchToFirstPart() {
      document.getElementById('switch-button').classList.remove('hidden');
      document.getElementById('second-part').classList.add('hidden');
      document.getElementById('third-part').classList.add('hidden');
      document.getElementById('district').removeAttribute('readonly');
      document.getElementById('type_of_payment').removeAttribute('readonly');
      document.getElementById('account_number').removeAttribute('readonly');
      document.getElementById('next-button').removeAttribute('disabled');
      document.getElementById('back-button').classList.remove('hidden');
    }
    function switchToThirdPart() {
      document.getElementById('switch-button').classList.add('hidden');
      document.getElementById('third-part').classList.remove('hidden');
      document.getElementById('next-button').setAttribute('disabled', 'disabled');
      document.getElementById('back-button').classList.add('hidden');
    }
    function switchToSecondPart() {
      document.getElementById('switch-button').classList.add('hidden');
      var region = document.getElementsByName('district')[0].value;
      var typeOfPayment = document.getElementsByName('type_of_payment')[0].value;
      var invoiceNumber = document.getElementsByName('account_number')[0].value;
      jQuery.ajax({
        type: 'POST',
        url: '<?php echo home_url('/wp-admin/admin-ajax.php'); ?>',
        data: {
          action: 'get_payment_data',
          region: region,
          type_of_payment: typeOfPayment,
          invoice_number: invoiceNumber,
        },
        success: function(response) {
          var data = JSON.parse(response);
          console.log(data);
          if (data.error) {
            document.getElementById('error-message').classList.remove('hidden');
            document.getElementById('error-message').innerHTML = data.error;
            document.getElementById('switch-button').classList.remove('hidden');
            return;
          } else {
            document.getElementById('error-message').classList.add('hidden');
          }
          document.getElementById('district').setAttribute('readonly', 'readonly');
          document.getElementById('type_of_payment').setAttribute('readonly', 'readonly');
          document.getElementById('account_number').setAttribute('readonly', 'readonly');
          document.getElementById('second-part').classList.remove('hidden');
          document.getElementById('address').value = data.address;
          document.getElementById('amount').value = data.amount;
          calculateCommission();
          var tbody = document.getElementById('counters');
          tbody.innerHTML = '';
          if (data.counters.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.innerHTML = '---';
            tr.appendChild(td);
            tbody.appendChild(tr);
          } else {
            var tr = document.createElement('tr');
            var th1 = document.createElement('th');
            var th2 = document.createElement('th');
            var th3 = document.createElement('th');
            var th4 = document.createElement('th');
            th1.innerHTML = 'Название услуги';
            th2.innerHTML = 'Номер счётчика';
            th3.innerHTML = 'Предыдущий показатель';
            th4.innerHTML = 'Новый показатель';
            tr.appendChild(th1);
            tr.appendChild(th2);
            tr.appendChild(th3);
            tr.appendChild(th4);
            tbody.appendChild(tr);
          }
          for (var i = 0; i < data.counters.length; i++) {
            var tr = document.createElement('tr');
            var td1 = document.createElement('td');
            var td2 = document.createElement('td');
            var td3 = document.createElement('td');
            var td4 = document.createElement('td');
            td1.innerHTML = `<input type="text" class="form-input" value="${data.counters[i].service_name}" name="counter${i}ServiceName" readonly>`;
            td2.innerHTML = `<input type="text" class="form-input" value="${data.counters[i].meter_number}" name="counter${i}MeterNumber" readonly>`;
            td3.innerHTML = `<input type="text" class="form-input" pattern="\\d+" value="${data.counters[i].old_reading}" name="counter${i}OldReading" readonly>`;
            td4.innerHTML = `<input type="text" class="form-input" pattern="\\d+" name="counter${i}NewReading">`;
            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);
            tr.appendChild(td4);
            tbody.appendChild(tr);
          }
        },
        error: function(error) {
          console.error('AJAX error:', error);
        }
      });
    }
  </script>
  <?php
  return ob_get_clean();
}

function display_form() {
  return form_html();
}