<?php
/*
Plugin Name: Meta YooKassa
Plugin URI: https://github.com/integratedevru/MetaYooKassa
Description: Form for payments via YooKassa and download data of successful payments.
Version: 1.1
Author: MetaSystems (for PKS)
*/

register_activation_hook(__FILE__, 'metayookassa_activation');
include_once(plugin_dir_path(__FILE__) . 'hooks/activation.hook.php');

register_deactivation_hook(__FILE__, 'metayookassa_deactivation');
include_once(plugin_dir_path(__FILE__) . 'hooks/deactivation.hook.php');

register_uninstall_hook(__FILE__, 'metayookassa_uninstall');
include_once(plugin_dir_path(__FILE__) . 'hooks/uninstall.hook.php');

add_action('init', 'handle_form_submission');
include_once(plugin_dir_path(__FILE__) . 'actions/handle-form-submission.action.php');

add_shortcode('meta_yookassa_form', 'display_form');
include_once(plugin_dir_path(__FILE__) . 'shortcodes/display-form.shortcode.php');

add_action('admin_menu', 'add_plugin_menu');
include_once(plugin_dir_path(__FILE__) . 'actions/add-plugin-menu.action.php');

add_action('admin_init', 'metayookassa_register_settings');
include_once(plugin_dir_path(__FILE__) . 'actions/register-settings.action.php');

// add_action('wp_ajax_yookassa_download_data', 'yookassa_download_data');
// include_once(plugin_dir_path(__FILE__) . 'actions/yookassa-download-data.action.php');

add_action('wp_ajax_get_payment_data', 'get_payment_data');
add_action('wp_ajax_nopriv_get_payment_data', 'get_payment_data');
include_once(plugin_dir_path(__FILE__) . 'actions/get-payment-data.action.php');

add_action('yookassa_send_data_event', 'yookassa_download_data');
include_once(plugin_dir_path(__FILE__) . 'actions/yookassa-download-data.action.php');
