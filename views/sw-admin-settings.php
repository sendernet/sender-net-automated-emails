<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
    
    // Get action
    if ( isset( $_GET['action'] ) ) {
        $action = $_GET['action'];
    } else {
        $action = "";
    }
    
    // Init helper class
    $sender_helper = new Sender_Woocommerce_Helper();
    
    // Delete api key
    if($action === 'disconnect') {
        $sender_helper->disconnect();
    }
    
    // Update cart period
    if($action === 'change_period' &&  isset( $_GET['tp'] )) {
        update_option( 'sender_woocommerce_cart_period', strtolower(trim($_GET['tp'])) );
        wp_redirect( admin_url( 'options-general.php?page=sender-woocommerce#!dashboard' ) );
    }
    
    // Authenticate user via returned API key
    if($action === 'authenticate' && isset( $_GET['response_key'] )) {
        $sender_helper->authenticate($_GET['response_key']);
    }
    
    // Init API Class
    $sender_api = new Sender_Woocommerce_Api();
 
    // Show connection view if no api key found!
    if(!$sender_api->checkApiKey()) {
        include_once 'pages/sw-connect.php';

    } else {
        include_once 'pages/sw-dashboard.php';
    }