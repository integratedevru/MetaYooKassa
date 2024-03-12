<?php

function metayookassa_deactivation() {
  wp_clear_scheduled_hook('yookassa_send_data_event');
}
