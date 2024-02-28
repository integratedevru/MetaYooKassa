<?php

function metayookassa_uninstall() {
    global $wpdb;

    $payment_types_table_name = $wpdb->prefix . 'metayookassa_payment_types';
    $invoice_table_name = $wpdb->prefix . 'metayookassa_invoice';
    $counter_value_table_name = $wpdb->prefix . 'metayookassa_counter_value';

    $sql_payment_types = "DROP TABLE IF EXISTS $payment_types_table_name;";
    $sql_invoice = "DROP TABLE IF EXISTS $invoice_table_name;";
    $sql_counter_value = "DROP TABLE IF EXISTS $counter_value_table_name;";

    $wpdb->query($sql_payment_types);
    $wpdb->query($sql_invoice);
    $wpdb->query($sql_counter_value);
}