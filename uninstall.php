<?php
/**
 * Uninstall handler
 * 
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
// drop a custom database table
global $wpdb;

// Delete carts table
$carts_table = $wpdb->prefix . "sender_woocommerce_carts";
$carts_delete = "DROP TABLE " . $carts_table ;
$wpdb->query( $carts_delete );

/// Delete users table
$users_table = $wpdb->prefix . "sender_woocommerce_users";
$users_delete = "DROP TABLE " . $users_table ;
$wpdb->query( $users_delete );

// Delete options
delete_option( 'sender_woocommerce_api_key' );
delete_option( 'sender_woocommerce_plugin_active' );
delete_option( 'sender_woocommerce_allow_guest_track' );
delete_option( 'sender_woocommerce_allow_import' );
delete_option( 'sender_woocommerce_allow_forms' );
delete_option( 'sender_woocommerce_customers_list' );
delete_option( 'sender_woocommerce_has_woocommerce' );
delete_option( 'sender_woocommerce_high_acc' );
delete_option( 'sender_woocommerce_registration_list' );
delete_option( 'sender_woocommerce_registration_track' );
delete_option( 'sender_woocommerce_allow_push' );
delete_option( 'sender_woocommerce_cart_period' );
delete_option( 'sender_woocommerce_forms_list' );