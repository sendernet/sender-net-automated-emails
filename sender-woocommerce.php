<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Automated Emails
 *
 *
 * @link              https://sender.net
 * @since             1.0.0
 * @package           sender-woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Sender.net Automated Emails
 * Plugin URI:        https://help.sender.net/knowledgebase/the-documentation-of-our-woocommerce-plugin/
 * Description:       The email marketing automation tool that helps you reach your customers with ease.
 * Version:           1.0.0
 * Author:            Sender.net
 * Author URI:        https://sender.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sender-woocommerce
 */

// Load dependencies
require_once( "includes/sw-class-settings.php" );
require_once( "includes/sw-class-helper.php" );
require_once( "includes/sw-class-api.php" );
require_once( "includes/sw-class-carts.php" );
require_once( "includes/sw-class-widget.php" );


if( !class_exists( 'Sender_Woocommerce' ) ) { // Check if class exists
    
/**
 * Sender Woocommerce Class
 * 
 * 
 */
    class Sender_Woocommerce {

    /**
     * 
     * Constructor function
     * 
     * Initialize all hooks and variables
     * 
     */
        public function __construct() {

            $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
            $plugin_version = $plugin_data['Version'];

            define ( 'SENDERWOO_CURRENT_VERSION', $plugin_version );

            register_activation_hook( __FILE__, array(&$this, 'sw_activate'));

            add_action('admin_init', array(&$this, 'sw_check_for_woo'));

            add_action('admin_menu', array(&$this, 'sw_add_sender_settings_menu'));
            
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'sw_add_plugin_action_links') );

            add_action('admin_enqueue_scripts', array(&$this, 'sw_enqueue_scripts_css'));

            add_action('admin_enqueue_scripts', array(&$this, 'sw_enqueue_scripts_js'));
            
            if(get_option('sender_woocommerce_plugin_active')) {
                
                $wooEnabled = get_option('sender_woocommerce_has_woocommerce');
                $autoSubscribeNewUsers = get_option('sender_woocommerce_registration_track');
                $allowPush = get_option('sender_woocommerce_allow_push');
                $allowForms = get_option('sender_woocommerce_allow_forms');


                if($autoSubscribeNewUsers) {
                    add_action('user_register', array(&$this, 'sw_subscribe_new_user'), 10, 1);
                }

                if($allowPush) {
                    add_action('wp_footer', array(&$this, 'sw_add_push_script'));
                }

                if($allowForms) {
                    add_action('admin_init', array(&$this, 'sw_update_form_versions'));
                }

                // Add woocommerce related hooks
                if($wooEnabled) {
                    
                    add_action( 'init',  array( &$this, 'emailCapturingHandler'), 10, 2 ); 

                    $senderWoocommerceCarts = new Sender_Woocommerce_Carts();	
                    // Redirect after login if user comes from hash url
                    if(isset($_COOKIE['sender_woocommerce_h'])) {
                        add_filter( 'woocommerce_login_redirect', array(&$this, 'sw_after_login_redirect'));
                    }
                    add_filter('template_include', array(&$senderWoocommerceCarts, 'assignCart'), 99, 1);

                    if(get_option('sender_woocommerce_high_acc')) {
                        add_action('woocommerce_cart_updated', array(&$senderWoocommerceCarts, 'handleCarts'));
                    } else {
                        add_action('woocommerce_update_cart_action_cart_updated', array(&$senderWoocommerceCarts, 'handleCarts'));
                        add_action('woocommerce_add_to_cart', array(&$senderWoocommerceCarts, 'handleCarts'));
                        add_action('woocommerce_cart_item_removed', array(&$senderWoocommerceCarts, 'handleCarts'));
                    }

                    add_action( 'woocommerce_checkout_order_processed', array( &$senderWoocommerceCarts,  'convertCart' ), 10 , 1 );

                    add_action( 'woocommerce_single_product_summary',  array( &$this, 'sw_add_sender_product_import'), 10, 2 ); 
                    
                    add_action( 'woocommerce_after_checkout_billing_form',  array( &$this, 'guestEmailCatchJs'), 10, 2 ); 
                    
                }
                
            }
            
            

        }
        
    /**
     * Add actions and filters for guest tracking
     */
        function emailCapturingHandler() {
            
            if ( ! is_user_logged_in() ) {
                add_action( 'wp_ajax_nopriv_save_data',  array( &$this, 'saveCapturedCustomerData'), 10, 2 );
            }
            
        }
        
    /**
     * Handle guest data
     * Create/Update guest, when entered email
     * 
     * @global type $wpdb
     * @global type $woocommerce
     */
        function saveCapturedCustomerData() {
                
            if(session_id() === '') { // No session
                session_start();
            }        

            global $wpdb, $woocommerce;
            
            $sender_carts = new Sender_Woocommerce_Carts();
            $current_time = current_time( 'timestamp' );
            
            // Check for submitted form data
            if ( isset($_POST['first_name']) && $_POST['first_name'] != '' ){
                $_SESSION['first_name'] = $_POST['first_name'];
            }            
            if ( isset($_POST['last_name']) && $_POST['last_name'] != '' ) {
                $_SESSION['last_name'] = $_POST['last_name'];
            }       
            if ( isset($_POST['email']) && $_POST['email'] != '' ) {
                $_SESSION['email'] = $_POST['email'];
            }            
           
            // Insert record in guest table
            if ( isset( $_SESSION['first_name'] ) ) {
                $billing_first_name = $_SESSION['first_name'];                
            } else {
                $billing_first_name = '';
            }

            if ( isset( $_SESSION['last_name'] ) ) {
                $billing_last_name = $_SESSION['last_name'];
            } else {
                $billing_last_name = '';
            }

            if ( isset( $_SESSION['email'] ) ) {
                $user_email = $_SESSION['email'];
            } else {
                $user_email = '';
            }
            
            $results_guest = $sender_carts->getSenderUserByEmail($user_email);
            
            $get_cookie   = WC()->session->get_session_cookie();
            
            $results_guest_cart = $sender_carts->getSenderCartBySession($get_cookie[0]);
            
            
            if ( $results_guest ) { // Guest already exists
                
                $sqlQuery = "UPDATE `".$wpdb->prefix."sender_woocommerce_users`
                            SET updated = %d
                            WHERE id = %d ";

                $wpdb->query( $wpdb->prepare($sqlQuery, $current_time, $results_guest[0]->id));
                
                if($results_guest_cart) {
                    
                    $sqlQuery = "UPDATE `".$wpdb->prefix."sender_woocommerce_carts`
                            SET user_id = %d,
                                updated = %d
                            WHERE id = %d ";

                    $wpdb->query( $wpdb->prepare($sqlQuery, $results_guest[0]->id, $current_time, $results_guest_cart[0]->id));

                }
                
                $user_id = $results_guest[0]->id;
                
            } elseif ( $results_guest_cart ) { // Guest has cart
                
                $user_id = $results_guest_cart[0]->user_id;
                
                
                if($user_id == 0) { // Create guest user if there is none
                    
                    $sqlQuery = "INSERT INTO `".$wpdb->prefix."sender_woocommerce_users`
                             ( first_name, last_name, email, created, updated )
                             VALUES ( %s, %s, %s, %d, %d )";

                    $wpdb->query($wpdb->prepare($sqlQuery, $billing_first_name, $billing_last_name, $user_email, $current_time, $current_time));

                    $user_id = $wpdb->insert_id; 
                    
                } else { // Update guest user
                    
                    $sqlQuery = "UPDATE `".$wpdb->prefix."sender_woocommerce_users`
                                SET email =   %s,
                                    updated = %d
                                WHERE id = %d ";

                    $wpdb->query( $wpdb->prepare($sqlQuery, $user_email, $current_time, $user_id));
        
                }
                
                $sqlQuery = "UPDATE `".$wpdb->prefix."sender_woocommerce_carts`
                                SET user_id = %d,
                                    updated = %d
                                WHERE id = %d ";

                $wpdb->query( $wpdb->prepare($sqlQuery, $user_id, $current_time, $results_guest_cart[0]->id));
                
                
            } else { // Create new guest user
                
                $sqlQuery = "INSERT INTO `".$wpdb->prefix."sender_woocommerce_users`
                             ( first_name, last_name, email, created, updated )
                             VALUES ( %s, %s, %s, %d, %d )";

                $wpdb->query($wpdb->prepare($sqlQuery, $billing_first_name, $billing_last_name, $user_email, $current_time, $current_time));
                
                $user_id = $wpdb->insert_id;
                
            }
            
            $results = $sender_carts->getSenderCart($user_id);
            
            $_SESSION['user_id'] = $user_id;
            
            $api = new Sender_Woocommerce_Api();
            $mailinglist = get_option('sender_woocommerce_customers_list');
            $api->addToList($user_email, $mailinglist['id'], $billing_first_name, $billing_last_name);

            $cart    = array();

            if ( function_exists('WC') ) {
                $cart['cart'] = WC()->session->cart;
            } else {
                $cart['cart'] = $woocommerce->session->cart;
            }
            
            $updated_cart_info = json_encode($cart);

            if ( count($results) > 0 ) { // Guest has cart
                
                    $sender_carts->updateSenderCart($user_id, $updated_cart_info);

                    $userR = $sender_carts->getSenderUser($user_id);

                    if(isset($userR[0]->email)) { // Sync cart only if has email
                        $sender_carts->prepareForApiCall($results[0]->id, $userR[0]->email);
                    }
                    
                    $_SESSION['sender_woocommerce_cart_id'] = $results[0]->id;
                    
            } else {
                   
                if ($get_cookie[0] != '') {   
                    
                    $results = $sender_carts->getSenderCartBySession($get_cookie[0]);
                    
                    if ( count( $results ) == 0 ) { // Create new cart for unknown user    
                        
                        $cart_info        = $updated_cart_info;
                        
                        if ( !empty($cart['cart']) ) {
                            
                            $cartId = $sender_carts->saveSenderCart(0, 'GUEST', $cart_info, $get_cookie[0]);
                            
                            // Do not prepare if no email given
    //                        $sender_carts->prepareForApiCall($cartId);
                            
                            $_SESSION['sender_woocommerce_cart_id'] = $cartId;
                           
                        }   
                        
                    } else {              
                        
                        if ( !empty($cart['cart']) ) {  
                            
                             $sqlQuery = "UPDATE `".$wpdb->prefix."sender_woocommerce_carts`
                                        SET cart_data = %s,
                                            updated = %d
                                        WHERE id = %d AND
                                              session = %s AND
                                              user_type = 'GUEST' AND
                                              cart_recovered = %d";

                            $wpdb->query( $wpdb->prepare($sqlQuery, $updated_cart_info, $current_time, $results[0]->id, $get_cookie[0], $sender_carts->cart_recovered));
                            
                            $guestCart = $sender_carts->getSenderCartBySession($get_cookie[0]);
                            
                            $email = $sender_carts->getSenderUser($guestCart[0]->user_id);
                            $sender_carts->prepareForApiCall($results[0]->id,$email);
                             
                            $_SESSION['sender_woocommerce_cart_id'] = $results[0]->id;
                            
                        }
                        
                    }
                }
            }
            
        }
        
    /**
     * Adds script to product page, to allow sender product import
     * 
     * @global type $product
     */
        function sw_add_sender_product_import() {
            
            if(get_option('sender_woocommerce_allow_import')) {
                global $product;
                $id = $product->get_id();

                $pName = $product->get_name();
                $pImage = get_the_post_thumbnail_url($id);
                $pDescription = str_replace("\"", '\\"', $product->get_description());
                $pPrice = $product->get_price();
                $pSalePrice = $product->get_sale_price();
                $pCurrency = get_option('woocommerce_currency');
                $pQty = $product->get_stock_quantity();
                $pRating = $product->get_average_rating();

                if(empty($pSalePrice)) {
                    $pSalePrice = $pPrice;
                }

                 echo '<script type="application/sender+json">
                        {
                          "name": "' . $pName . '",
                          "image": "' . $pImage . '",
                          "description": "' . $pDescription . '",
                          "price": "' . $pPrice . '",
                          "special_price": "' . $pSalePrice . '",
                          "currency": "' . $pCurrency . '",
                          "quantity": "' . $pQty . '",
                          "rating": "' . $pRating . '"
                        }
                    </script>';
            }
           
        }
        
    /**
     * 
     * @param type $user_id
     */
        function sw_subscribe_new_user($user_id) {
            $sender_api = new Sender_Woocommerce_Api();
            $userObj = get_userdata($user_id);
            $listId = get_option('sender_woocommerce_registration_list');
            $sender_api->addToList($userObj->user_email, $listId['id']);
        }
        
    /**
     * Add settings action link
     * 
     * @param type $links
     * @return type
     */
        function sw_add_plugin_action_links($links) {
           $mylinks = array(
            '<a href="' . admin_url( 'options-general.php?page=sender-woocommerce' ) . '">Settings</a>',
            );
           return array_merge( $links, $mylinks );
        }
        
    /**
     * 
     */
        function sw_update_form_versions() {
            
            $sender_api = new Sender_Woocommerce_Api();
            $forms = $sender_api->getAllForms();
            
            if(!isset($forms->error)) {
                
                $formOptions = array();
                
                foreach($forms as $form) {
                    $formOptions[$form->id] = $form->script_url; 
                }

                update_option('sender_woocommerce_forms_list', $formOptions);
                
            }
            
            
        }
        
        
    /**
     * Generate push notification script
     */
        function sw_add_push_script() {
            $sender_api = new Sender_Woocommerce_Api();
            ?>
                <script type="text/javascript">
                (function(p,u,s,h){
                    p._spq=p._spq||[];
                    p._spq.push(['_currentTime',Date.now()]);
                    s=u.createElement('script');
                    s.type='text/javascript';
                    s.async=true;
                    s.src="<?php echo $sender_api->getPushProject(); ?>";
                    h=u.getElementsByTagName('script')[0];
                    h.parentNode.insertBefore(s,h);
                })(window,document);
                </script>
            <?php
        }
        
    /**
     * Generate inputs data catching javascript
     */
        function guestEmailCatchJs() {
            if (! is_user_logged_in()) {
                ?>
                <script type="text/javascript">
                    jQuery( 'input#billing_email' ).on( 'blur', function() {
                        
                        var data = {
                            first_name	: jQuery('#billing_first_name').val(),
                            last_name	: jQuery('#billing_last_name').val(),
                            email		: jQuery('#billing_email').val(),
                            action: 'save_data'
                        };	

                        var adminUrl = "<?php echo get_admin_url();?>admin-ajax.php";

                        jQuery.post( adminUrl, data);		
                    });
                </script>			
                <?php
            }
        }

      /**
       * Redirects user if 
       * has cart hash
       * 
       * @param string $redirect
       * @return string
       */
        public function sw_after_login_redirect($redirect) {
            
            $redirect = get_site_url() . '/?hash=' . $_COOKIE['sender_woocommerce_h'];
            return $redirect;
            
        }
        
    /**
     * Check if woocommerce plugin is installed!
     * 
     * @return boolean
     */
        public static function sw_has_woocommerce() {

            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && class_exists( 'WooCommerce' ) ) {
                return true;
            } else {
                return false;
            }
        }
        
        public function sw_check_for_woo() {
            if(!self::sw_has_woocommerce() && is_plugin_active(plugin_basename(__FILE__))) {
               update_option('sender_woocommerce_has_woocommerce', false);
            } else {
               update_option('sender_woocommerce_has_woocommerce', true);
            }
        }
        
    /**
     * Add Sender.net plugin settings link under system settings
     * 
     */
        function sw_add_sender_settings_menu() {
            
            add_options_page(
                'Sender.net Automated Emails settings',
                'Sender.net Settings', 'manage_options',
                'sender-woocommerce',
                array($this, 'sw_display_sender_settings'));
            
        }
        
    /**
     * Display Sender.net settings page.
     * 
     */
        public function sw_display_sender_settings() {
            include_once( 'views/sw-admin-settings.php' ); 
        }
        
    /**
     * 
     * Called on plugin activation
     * Creates database tables if not existent
     * Sets default settings if not existent
     * 
     * @global type $wpdb
     */                            
        function sw_activate() {
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            global $wpdb;
            
            $wcap_collate = '';
            
            if($wpdb->has_cap('collation')) {
                $wcap_collate = $wpdb->get_charset_collate();
            }
            
            $sender_carts = $wpdb->prefix . 'sender_woocommerce_carts';
            $cartsSql = "CREATE TABLE IF NOT EXISTS $sender_carts (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `user_id` int(11) NOT NULL,
                             `user_type` varchar(15),
                             `session` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                             `cart_data` text COLLATE utf8_unicode_ci NOT NULL,
                             `cart_recovered` int(11) NOT NULL,
                             `cart_status` int(11) NOT NULL,
                             `created` int(11) NOT NULL,
                             `updated` int(11) NOT NULL,
                             PRIMARY KEY (`id`)
                             ) $wcap_collate";
            // Create table
            dbDelta($cartsSql, true);
            
            
            
            $sender_users = $wpdb->prefix."sender_woocommerce_users" ;
            
            $usersSql = "CREATE TABLE IF NOT EXISTS $sender_users (
            `id` int(15) NOT NULL AUTO_INCREMENT,
            `first_name` text,
            `last_name` text,
            `email` text,
            `created` int(11) NOT NULL,
            `updated` int(11) NOT NULL,
            PRIMARY KEY (`id`)
            ) $wcap_collate";
            $wpdb->query( $usersSql );
            
            // Setup options
            if( !get_option( 'sender_woocommerce_api_key' ) ) {
                add_option( 'sender_woocommerce_api_key', 'api_key' );
            }
            
            if( !get_option( 'sender_woocommerce_allow_guest_track' ) ) {
                add_option( 'sender_woocommerce_allow_guest_track', false );
            }
       
            if( !get_option( 'sender_woocommerce_allow_import' ) ) {
                add_option( 'sender_woocommerce_allow_import', 1 );
            }
            
            if( !get_option( 'sender_woocommerce_allow_forms' ) ) {
                add_option( 'sender_woocommerce_allow_forms', false );
            }
            
            if( !get_option( 'sender_woocommerce_customers_list' ) ) {
                add_option( 'sender_woocommerce_customers_list', array('id' => false, 'title' => ' ') );
            }
            
            if( !get_option( 'sender_woocommerce_registration_list' ) ) {
                add_option( 'sender_woocommerce_registration_list', array('id' => false, 'title' => ' ') );
            }
            
            if( !get_option( 'sender_woocommerce_registration_track' ) ) {
                add_option( 'sender_woocommerce_registration_track', 1 );
            }
           
            if( !get_option( 'sender_woocommerce_cart_period' ) ) {
                add_option( 'sender_woocommerce_cart_period', 'today' );
            }
            
            if( !get_option( 'sender_woocommerce_has_woocommerce' ) ) {
                add_option( 'sender_woocommerce_has_woocommerce', false );
            }
            
            if( !get_option( 'sender_woocommerce_high_acc' ) ) {
                add_option( 'sender_woocommerce_high_acc', true );
            }
            
            if( !get_option( 'sender_woocommerce_allow_push' ) ) {
                add_option( 'sender_woocommerce_allow_push', false );
            }
            
            if( !get_option( 'sender_woocommerce_forms_list' ) ) {
                add_option( 'sender_woocommerce_forms_list', false );
            }
            
            if( !get_option( 'sender_woocommerce_plugin_active' ) ) {
                add_option( 'sender_woocommerce_plugin_active', false );
            }
            
    }
    
    /**
     * Load JS scripts
     * 
     * @param type $hook
     */  
        function sw_enqueue_scripts_js( $hook ) {
                wp_enqueue_script( 'sender_scripts', plugin_dir_url( __FILE__ ) . 'assets/js/sw-sender-scripts.js' ); 
                wp_enqueue_script( 'sw_table_sorter', plugin_dir_url( __FILE__ ) . 'assets/js/sw-vendor-tablesorter.js' ); 
        }
    
    /**
     * Load Stylesheets
     * 
     * @param type $hook
     */
        function sw_enqueue_scripts_css( $hook ) { 
                wp_enqueue_style( 'sender_styles', plugin_dir_url( __FILE__ ) . 'assets/css/sw-sender-style.css' );
                wp_enqueue_style( 'sw_material_icons', plugin_dir_url( __FILE__ ) . 'assets/css/sw-vendor-material-design-iconic-font.css' );
        }       
                
    }   
}   
// Init class
$senderWoocommerce = new Sender_Woocommerce();

?>