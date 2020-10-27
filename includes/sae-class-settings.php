<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Automated Emails Settings class
 * 
 * 
 */
class Sender_Automated_Emails_Settings {

    private $baseUrl = 'https://api.sender.net';
    
    public function __construct() {
        add_action( 'wp_ajax_change_customer_list', 'change_customer_list');
        add_action( 'wp_ajax_change_register_list', 'change_register_list');
        add_action( 'wp_ajax_toggle_cart_track', 'toggle_cart_track');
        add_action( 'wp_ajax_toggle_form_widget', 'toggle_form_widget');
        add_action( 'wp_ajax_toggle_allow_import', 'toggle_allow_import');
        add_action( 'wp_ajax_toggle_allow_push', 'toggle_allow_push');
        add_action( 'wp_ajax_toggle_allow_high_acc', 'toggle_allow_high_acc');
        add_action( 'wp_ajax_toggle_registration_track', 'toggle_registration_track');
    }

    /**
     * Generate WP notice
     * 
     * @param type $message
     * @param type $type
     */
    public static function makeNotice($message, $type = 'error') {

        $classes = 'notice is-dismissible';
        $classes .= ' notice-' . $type; 

        printf( '<div class="%1$s" style="margin: 15px 15px 15px 0px !important;"><p>%2$s</p></div>', $classes, $message );

    }
    
    /**
     * Return base URL
     * 
     * @return type
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }
    
}

    /**
     * Update customer mailinglist
     * 
     */
    function change_customer_list() {

        if(isset($_POST['listId']) && isset($_POST['title'])) {
            $array = array(
                'id'    => (int) $_POST['listId'],
                'title' => sanitize_text_field($_POST['title'])
            );
            update_option('sender_automated_emails_customers_list', $array);
            echo json_encode(array('success' => true));
            wp_die();
        }
        
    }
    
    /**
     * Update new users mailinglist
     * 
     */
    function change_register_list() {

        if(isset($_POST['listId']) && isset($_POST['title'])) {
            $array = array(
                'id'    => (int) $_POST['listId'],
                'title' => sanitize_text_field($_POST['title'])
            );
            update_option('sender_automated_emails_registration_list', $array);
            echo json_encode(array('success' => true));
            wp_die();
        }
        
    }
    
    /**
     * Toggle cart track setting
     */
    function toggle_cart_track() {
        
        $trackStatus = get_option('sender_automated_emails_allow_guest_track');
        
        update_option('sender_automated_emails_allow_guest_track', (int)!$trackStatus);
        
        echo (int)!$trackStatus;
        wp_die();
        
    }
    
    /**
     * Toggle new users auto subscribe
     */
    function toggle_registration_track() {
        
        $trackStatus = get_option('sender_automated_emails_registration_track');
        
        update_option('sender_automated_emails_registration_track', (int)!$trackStatus);
        
        echo (int)!$trackStatus;
        wp_die();
        
    }

    /**
     * Toggle allow product import
     */
    function toggle_allow_import() {
        
        $importStatus = get_option('sender_automated_emails_allow_import');
        
        update_option('sender_automated_emails_allow_import', (int)!$importStatus);
        
        echo (int)!$importStatus;
        wp_die();
        
    }

    /**
     * Toggle allow product import
     */
    function toggle_allow_push() {
        
        $pushStatus = get_option('sender_automated_emails_allow_push');
        
        update_option('sender_automated_emails_allow_push', (int)!$pushStatus);
        
        echo (int)!$pushStatus;
        wp_die();
        
    }
    
    /**
     * Toggle allow high accuracy
     */
    function toggle_allow_high_acc() {
        
        $allowHighAcc = get_option('sender_automated_emails_high_acc');
        
        update_option('sender_automated_emails_high_acc', (int)!$allowHighAcc);
        
        echo (int)!$allowHighAcc;
        wp_die();
        
    }

    /**
     * Toggle form widget
     */
    function toggle_form_widget() {
        
        $importStatus = get_option('sender_automated_emails_allow_forms');
        
        update_option('sender_automated_emails_allow_forms', (int)!$importStatus);
        
        echo (int)!$importStatus;
        wp_die();
        
    }
    
// Init class
$senderSettings = new Sender_Automated_Emails_Settings();