<?php

function metayookassa_activation() {
    $payment_types_sql = get_payment_types_sql();
    $invoice_sql = get_invoice_sql();
    $counter_value_sql = get_counter_value_sql();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($payment_types_sql);
    dbDelta($invoice_sql);
    dbDelta($counter_value_sql);

    $tomorrow_at_1am = strtotime('tomorrow 1:00 AM - 3 hours');
    wp_clear_scheduled_hook('yookassa_send_data_event');
    wp_schedule_event($tomorrow_at_1am, 'meta_daily', 'yookassa_send_data_event');
}

function get_payment_types_sql() {
    global $wpdb;
    $table_name = get_payment_types_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        region VARCHAR(50) NOT NULL,
        reester_number VARCHAR(50) NOT NULL,
        type_of_payment VARCHAR(3) NOT NULL,
        receipt_name VARCHAR(50) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    return $sql;
}

function get_invoice_sql() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = get_invoice_table_name();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        region VARCHAR(50) NOT NULL,
        invoice_number VARCHAR(50) NOT NULL,
        reester_number VARCHAR(50) NOT NULL,
        receipt_name VARCHAR(50) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        type_of_payment VARCHAR(3) NOT NULL,
        address TEXT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        unified_number VARCHAR(50) NOT NULL,
        PRIMARY KEY  (id),
        INDEX region_type_invoice (region, type_of_payment, invoice_number)
    ) $charset_collate;";
    return $sql;
}

function get_counter_value_sql() {
    global $wpdb;
    $invoice_table_name = get_invoice_table_name();
    $table_name = get_counter_value_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        invoice_id mediumint(9) NOT NULL,
        service_name VARCHAR(100) NOT NULL,
        meter_number VARCHAR(50) NOT NULL,
        old_reading INT NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (invoice_id) REFERENCES $invoice_table_name(id)
    ) $charset_collate;";
    return $sql;
}

function get_payment_types_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'metayookassa_payment_types';
    return $table_name;
}

function get_invoice_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'metayookassa_invoice';
    return $table_name;
}

function get_counter_value_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'metayookassa_counter_value';
    return $table_name;
}