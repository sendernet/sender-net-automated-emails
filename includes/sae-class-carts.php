<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Automated Emails Carts Class
 * 
 * Handles carts
 */

class Sender_Automated_Emails_Carts extends Sender_Automated_Emails_Settings {
    
    var $sender_api;
    var $sender_helper;
    var $woo_cart_column;
    var $cart_recovered = 0;
    
/**
 * Constructor function
 * 
 */
    public function __construct() {

        $this->sender_api = new Sender_Automated_Emails_Api();
        $this->sender_helper = new Sender_Automated_Emails_Helper();
        
        
    }
    
    /**
     * Check for woo persistent cart column name
     * 
     * @global type $woocommerce
     */
    public function checkWooVersion() {
        
        global $woocommerce;
        
        if(version_compare( $woocommerce->version, '3.1.0', ">=" )) {
            $this->woo_cart_column = '_woocommerce_persistent_cart_' . get_current_blog_id();
        } else {
            $this->woo_cart_column = '_woocommerce_persistent_cart';
        }
        
    }
    
    /**
     * Handle cart update data
     * 
     */
    public function handleCarts() {
        
        if( session_id() === '' ){
            session_start();
        } 
      
        $this->checkWooVersion();
        
        if ( is_user_logged_in() ) {
            
            $this->handleLoggedInUsers();
            
        } else { 
            if (get_option('sender_automated_emails_allow_guest_track')) {
                $this->handleGuestUsers();
            }
        }
        

    }
    
    /**
     * Get sender cart
     * 
     * @global type $wpdb
     * @param type $userId
     * @param type $userType
     * @return type
     */
    public function getSenderCart($userId, $userType = 'GUEST') {
        
        if(!$userId) {
            return [];
        }



        
        global $wpdb;
        
        $query   = "SELECT * FROM `".$wpdb->prefix."sender_automated_emails_carts`
                        WHERE user_id = %d
                        AND user_type = %s
                        AND cart_recovered = %d
                        AND cart_status = '0'";
        
        $results = $wpdb->get_results($wpdb->prepare( $query, $userId, $userType, $this->cart_recovered ) );
        
        return $results;
        
    }
    
    /**
     * Retrieve sender cart by session
     * 
     * @global type $wpdb
     * @param type $session
     * @return type
     */
    public function getSenderCartBySession($session) {
        
        if(!$session) {
            return [];
        }
        
        global $wpdb;
        
        $query   = "SELECT * FROM `".$wpdb->prefix."sender_automated_emails_carts`
                        WHERE session = %s
                        AND user_type = 'GUEST'
                        AND cart_recovered = %d
                        AND cart_status = '0' ";
        
        $results = $wpdb->get_results($wpdb->prepare( $query, $session, $this->cart_recovered ) );
        
        return $results;
        
    }
    
    /**
     * Save sender cart to database
     * 
     * 
     * @global type $wpdb
     * @param type $userId
     * @param type $userType
     * @param type $cartData
     * @param type $session
     * @return type
     */
    public function saveSenderCart($userId, $userType, $cartData, $session = null) {
        
        global $wpdb;
        $currentTime = current_time('timestamp');
        
        
        $sqlQuery = "INSERT INTO `".$wpdb->prefix."sender_automated_emails_carts`
                         ( user_id, user_type, cart_data, session, created, updated )
                         VALUES ( %d, %s, %s, %s, %d, %d )";

        $wpdb->query($wpdb->prepare($sqlQuery, $userId, $userType, $cartData, $session, $currentTime, $currentTime));
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update existing sender cart
     * 
     * @global type $wpdb
     * @param type $userId
     * @param type $cartData
     * @return boolean
     */
    public function updateSenderCart($userId, $cartData) {
        
        if($cartData === '{"cart":[]}') {
            return false;
        }
        
        global $wpdb;
        $sqlQuery = "UPDATE `".$wpdb->prefix."sender_automated_emails_carts`
                             SET cart_data = %s,
                                 updated = %d
                             WHERE user_id = %d 
                             AND cart_recovered = %d
                             AND cart_status = '0' ";

        $wpdb->query( $wpdb->prepare($sqlQuery, $cartData, current_time('timestamp'), $userId, $this->cart_recovered));
        
    }
    
    /**
     * Mark sender cart as recovered
     * 
     * @global type $wpdb
     * @param type $userId
     * @param type $cartId
     * 
     */
    public function recoverSenderCart($userId, $cartId) {
        
        global $wpdb;
        $sqlQuery = "UPDATE `".$wpdb->prefix."sender_automated_emails_carts`
                            SET user_id = %d,
                                cart_recovered = '1',
                            WHERE id = %d";

        $wpdb->query( $wpdb->prepare($sqlQuery, $userId, $cartId) );
        
    }
    
    /**
     * Mark sender cart as converted
     * 
     * @global type $wpdb
     * @param type $cartId
     */
    public function convertSenderCart($cartId) {
        
        global $wpdb;
        $sqlQuery = "UPDATE `".$wpdb->prefix."sender_automated_emails_carts`
                            SET cart_status = '2',
                                session = ''
                            WHERE id = %d";

        $wpdb->query( $wpdb->prepare($sqlQuery, $cartId) );
        
    }

    /**
     * Gather data for cart_track API call
     * 
     * @global type $woocommerce
     * @param type $cartId
     * @param type $email
     */
    public function prepareForApiCall($cartId, $email = '') {
        
        global $woocommerce;
        
        $items = $woocommerce->cart->get_cart();
        $total = $woocommerce->cart->total;
        
        $data = array(
            "email" => $email,
            "external_id" => $cartId,
            "url" => 'null',
            "currency" => 'EUR',
            "grand_total" =>  $total,
            "products" => array()
        );

        foreach($items as $item => $values) {

            $_product =  wc_get_product( $values['data']->get_id() );
            $discount = round(100-(get_post_meta($values['product_id'] , '_sale_price', true) / get_post_meta($values['product_id'] , '_regular_price', true) * 100));
            $prod = array(
                'sku' => $values['data']->get_sku(),
                'name' => $_product->get_title(),
                'price' => get_post_meta($values['product_id'] , '_regular_price', true),
                'price_display' => get_post_meta($values['product_id'] , '_sale_price', true),
                'discount' => (string)$discount,
                'qty' =>  $values['quantity'],
                'image' => get_the_post_thumbnail_url($values['data']->get_id())
            );


            $data['products'][] = $prod;
        }
        
        $data['grand_total'] = $woocommerce->cart->total;

        if(count($data['products']) >= 1) {
            $this->sender_api->cartTrack($data);
        } else {
            $this->sender_api->cartDelete($cartId);
        }
        
    }
    
    /**
     * Retrieve sender user from database
     * by ID
     * 
     * @global type $wpdb
     * @param type $userId
     * @return type
     */
    public function getSenderUser($userId) {
        
        global $wpdb;
        $sqlQuery = "SELECT * FROM `".$wpdb->prefix."sender_automated_emails_users` WHERE id = %d AND id != '0'";


        $result = $wpdb->get_results( $wpdb->prepare( $sqlQuery, $userId ) );
        
        return $result;

    }
    
    /**
     * Retrieve sender user from database
     * by EMAIL
     * 
     * @global type $wpdb
     * @param type $email
     * @return type
     */
    public function getSenderUserByEmail($email) {
        
        global $wpdb;
        $sqlQuery = "SELECT * FROM `".$wpdb->prefix."sender_automated_emails_users` WHERE email = %s AND id != '0'";


        $result = $wpdb->get_results( $wpdb->prepare( $sqlQuery, $email ) );
        
        return $result;

    }
    
    /**
     * Logged in user cart tracking
     * 
     * @global type $woocommerce
     * @global type $wpdb
     */
    public function handleLoggedInUsers() {
        
            global $woocommerce;
            global $wpdb;
        

            $user_id = get_current_user_id();

            $results = $this->getSenderCart($user_id, 'REGISTERED');
            
            
            $_SESSION['sender_automated_emails_user_id'] = $user_id;
            
            

            if ( count($results) == 0 ) {
                
                $cart = get_user_meta( $user_id, $this->woo_cart_column, true );
                
                if( !empty($cart['cart']) ) {
                    
                    $cart_info = json_encode( $cart );

                    $abandoned_cart_id = $this->saveSenderCart($user_id, 'REGISTERED', $cart_info);

                    $_SESSION['sender_automated_emails_cart_id'] = $abandoned_cart_id;
                }

            } else {     
                
                $cart = get_user_meta( $user_id, $this->woo_cart_column, true );
                
                if( !empty($cart['cart']) ) {
                    
                    $updated_cart_info = json_encode( get_user_meta( $user_id, $this->woo_cart_column, true ) );
                    
                    
                    $this->updateSenderCart($user_id, $updated_cart_info);

                    $cartUp = $this->getSenderCart($user_id, 'REGISTERED');

                    if ( count( $cartUp ) > 0 ) {
                        $abandoned_cart_id   = $cartUp[0]->id;
                        $_SESSION['sender_automated_emails_cart_id'] = $abandoned_cart_id;

                    }

                    $this->prepareForApiCall($abandoned_cart_id, wp_get_current_user()->user_email);
                    
                }
                
                

            }
    }
    
    /**
     * Guest users cart tracking
     * if enabled
     * 
     * @global type $woocommerce
     * @global type $wpdb
     */
    public function handleGuestUsers() {
        
        global $woocommerce;
        global $wpdb;


        if ( isset( $_SESSION['user_id'] ) ) { 
            
            $user_id = $_SESSION['user_id'];
            $_SESSION['sender_automated_emails_user_id'] = $user_id;
           
        } else {
            
            $user_id = "";
            
        }
        

        $results = $this->getSenderCart($user_id);
        
        $cart    = array();
        
        $current_time = current_time('timestamp');

        $get_cookie = WC()->session->get_session_cookie();

        if ( function_exists('WC') ) {
            
            $cart['cart'] = WC()->session->cart;
            
        } else {
            
            $cart['cart'] = $woocommerce->session->cart;
            
        }
        
        $updated_cart_info = json_encode($cart);

        if ( !empty($results) ) { // Guest has cart
            
            $this->updateSenderCart($user_id, $updated_cart_info);

            if($user_id != 0) { // Sync only if there is user

                $userR = $this->getSenderUser($user_id);
                $this->sender_helper->logError('Updating sender cart with email: ' . $userR[0]->email);
                $this->prepareForApiCall($results[0]->id, $userR[0]->email);

            }

            $_SESSION['sender_automated_emails_cart_id'] = $results[0]->id;
                
        } else {    
               
            if ($get_cookie[0] != '') {   
                
                $results = $this->getSenderCartBySession($get_cookie[0]);
                
                if ( count( $results ) == 0 ) {    
                    
                    $cart_info        = $updated_cart_info;
                   
                    if ( !empty($cart['cart']) ) {
                        
                        $cartId = $this->saveSenderCart(0, 'GUEST', $cart_info, $get_cookie[0]);
                      
                        
                        $_SESSION['sender_automated_emails_cart_id'] = $cartId;
                       
                    }   
                    
                } else {              
                    
                    if ( !empty($cart['cart']) ) {   
                        
                        $sqlQuery = "UPDATE `".$wpdb->prefix."sender_automated_emails_carts`
                             SET cart_data = %s,
                                 updated = %d
                             WHERE id = %d 
                             AND session = %s
                             AND user_type = 'GUEST'
                             AND cart_recovered = %d";

                        $wpdb->query( $wpdb->prepare($sqlQuery, $updated_cart_info, $current_time, $results[0]->id, $get_cookie[0], $this->cart_recovered));
                        $guestCart = $this->getSenderCartBySession($get_cookie[0]);
                        
                        if($guestCart[0]->user_id != 0) {
                            $get_user = $this->getSenderUser($guestCart[0]->user_id);
                            $this->prepareForApiCall($results[0]->id, $get_user[0]->email);
                        }
                         
                        $_SESSION['sender_automated_emails_cart_id'] = $results[0]->id;
                        
                    }
                    
                }
            }
        }     
    }
    
    /**
     * Checks cart hash and assigns it to user
     * 
     * 
     * @global type $woocommerce
     * @global type $wpdb
     * @global type $wpdb
     * @param type $template
     * @return type
     */
    public function assignCart($template) {
        global $woocommerce;
        global $wpdb;
   
        
        // Here we update user id for sender cart if user logs in
        if(isset($_SESSION['sender_automated_emails_cart_id']) && is_user_logged_in()) {
            
            $sqlQuery = "UPDATE `".$wpdb->prefix."sender_automated_emails_carts`
                        SET user_id = %d,
                            updated = %d,
                            user_type = 'REGISTERED'
                        WHERE id = %d";

            $wpdb->query( $wpdb->prepare($sqlQuery, get_current_user_id(), current_time('timestamp'), $_SESSION['sender_automated_emails_cart_id']) );

        }

        $cartId = '';

        $url = wc_get_cart_url();

        if(isset($_GET['hash'])) {
            $cartHash = sanitize_text_field($_GET['hash']);
            $sCart = $this->sender_api->cartGet($cartHash);
            $cartId = (int) $sCart->cart_id;
            setcookie( 'sender_automated_emails_h', $cartHash, time() + 900000 );
        }

        $this->sender_helper->logError('Assign cart id: ' . $cartId);
        
        if ( !empty($cartId) ) {
            
            if( session_id() === '' ) {
                //session has not started
                session_start();
            }   

            global $wpdb;

            $get_user_results  = array();

            $get_user_id_query = "SELECT * FROM `".$wpdb->prefix."sender_automated_emails_carts` WHERE id = %d AND cart_recovered = '0' AND cart_status = '0'";
            $get_user_results  = $wpdb->get_results( $wpdb->prepare( $get_user_id_query, $cartId ) );
            $user_id           = 0;
            $user_type         = false;
            
            if ( isset( $get_user_results ) && count( $get_user_results ) > 0 ) { 
                $user_id = $get_user_results[0]->user_id;
                $user_type = $get_user_results[0]->user_type;
            }  

            if ( $user_id == 0 ) {
                wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                exit;
            } 
            
            setcookie ("sender_automated_emails_u", base64_encode($user_id), time() + 900000);
            
            
            if ( $user_type == 'GUEST' ) {
                
                $query_guest   = "SELECT * from `". $wpdb->prefix."sender_automated_emails_users` WHERE id = %d";
                $results_guest = $wpdb->get_results( $wpdb->prepare( $query_guest, $user_id ) );
                
                $query_cart    = "SELECT cart_recovered FROM `".$wpdb->prefix."sender_automated_emails_carts` WHERE id = %d";
                $results       = $wpdb->get_results( $wpdb->prepare( $query_cart, $cartId ) );  
                

                if ( $results_guest  && $results[0]->cart_recovered == '0' ) {
                    $_SESSION['guest_first_name'] = $results_guest[0]->first_name;
                    $_SESSION['guest_last_name']  = $results_guest[0]->last_name;
                    $_SESSION['guest_email']      = $results_guest[0]->email;
                    $_SESSION['user_id']          = $user_id;
                } else {
                    wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                }
            }

            if ( $user_type == 'REGISTERED' ) {
                
                if(is_user_logged_in()) {
                    $user_login = $user->data->user_login;
                    $my_temp    = woocommerce_load_persistent_cart( $user_login, $user );
                   
                } else {
                    wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')));
                    exit;
                    
                }
             
            } else  {
                
                $my_temp = $this->loadGuestCart( $user_id );
                
            }
                header( "Location: $url" );

        } else {
            
            return $template;
            
        }
    }
    
    /**
     * Create new cart and new session
     * 
     * @global type $woocommerce
     */
    public function loadGuestCart() {

        if (isset($_SESSION['user_id']) 
            && !empty($_SESSION['user_id'])) {
            
            global $woocommerce;
            
            $senderCart = $this->getSenderCart($_SESSION['user_id']);
             
            $decodedCart = json_decode($senderCart[0]->cart_data,true );

            if (!count($decodedCart['cart'])) {
                return;
            }

            $Cart = new WC_Cart();

            foreach ($decodedCart['cart'] as $product) {
                $Cart->add_to_cart(
                    (int) $product['product_id'],
                    (int) $product['quantity'],
                    (int) $product['variation_id'], 
                    $product['variation']
                );
            }

            $CartSession = new WC_Cart_Session($Cart);

        }
    }
    
    /**
     * Handles cart purchase, marks cart as converted and checks if it was
     * recovered via email
     * 
     * @global type $wpdb
     * @param type $order
     * @return type
     */
    public function convertCart($order) {
        
        global $wpdb;
        
        if(isset($_COOKIE['sender_automated_emails_h']) && isset($_COOKIE['sender_automated_emails_u'])) {
            
            $tmpCartId = $this->sender_api->cartGet($_COOKIE['sender_automated_emails_h']);
            $cartId = $tmpCartId->cart_id;
            
            $userId = base64_decode($_COOKIE['sender_automated_emails_u']);
            
            $resp = $this->sender_api->cartConvert($cartId);
            
            // Debug
            $this->sender_helper->logError('Converting cart COOKIE_H_U' . $cartId);
        
            if(is_user_logged_in() && get_current_user_id() > 0) {

                $wpdb->delete($wpdb->prefix . "sender_automated_emails_carts", array('id' => $userId), array('%d'));

                $userId = get_current_user_id();
                
                $this->recoverSenderCart($userId, $cartId);
                

            }
            
            $this->convertSenderCart($cartId);

            setcookie ("sender_automated_emails_u",$_COOKIE['sender_automated_emails_u'], time() - 9000000);
            setcookie ("sender_automated_emails_h",$_COOKIE['sender_automated_emails_h'], time() - 9000000);

            return $order;
            
            
        } elseif (isset($_SESSION['sender_automated_emails_cart_id']) && isset($_SESSION['sender_automated_emails_user_id'])) {
            
            if(isset($_SESSION['sender_automated_emails_cart_id'])) {
                $cartId = $_SESSION['sender_automated_emails_cart_id'];
            } else {
                $cartId = '';
            }

            if(isset($_SESSION['sender_automated_emails_user_id'])) {
                $userId = $_SESSION['sender_automated_emails_user_id'];
            } else {
                $userId = '';
            }
            
            // Debug
            $this->sender_helper->logError('Converting cart SESSION' . $cartId);

            $resp = $this->sender_api->cartConvert($cartId);
            
            if(is_user_logged_in() && get_current_user_id() > 0) {

                $wpdb->delete($wpdb->prefix . "sender_automated_emails_carts", array('id' => $userId), array('%d'));

                $userId = get_current_user_id();
                
                $this->recoverSenderCart($userId, $cartId);

                delete_user_meta($userId, '_woocommerce_persistent_cart');
            }
            
            $this->convertSenderCart($cartId);
            

            return $order;
           
        }
        
    }
    
}				
