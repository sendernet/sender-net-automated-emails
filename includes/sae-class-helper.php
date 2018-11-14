<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Automated Emails Helper Class
 * 
 * 
 */
class Sender_Automated_Emails_Helper extends Sender_Automated_Emails_Settings {
    
    /**
     * Enables debug mode
     * 
     * @var boolean true when enabled
     */
    public $debug = false;


    /**
     * Generate authentication URL
     * 
     * @return string
     */
    public function getAuthUrl() {
        $query = http_build_query(array(
            'return'        => get_site_url() . '/wp-admin/options-general.php?page=sender-net-automated-emails&action=authenticate&response_key=API_KEY',
            'return_cancel' => $this->getBaseUrl(),
            'store_baseurl' => get_site_url(),
            'store_currency' => get_option('woocommerce_currency')
        ));
    
        $url = $this->getBaseUrl() . '/commerce/auth/?' . $query;
        return $url;
    }
    
    /**
     * Save api key to database
     * 
     * @param type $apiKey
     * @return boolean
     */
    public function authenticate($apiKey) {
        $api = new Sender_Automated_Emails_Api();
        $api->setApiKey($apiKey);

        if(!$api->checkApiKey()) {
            echo $this->makeNotice('Could not authenticate!');
            delete_option('sender_automated_emails_api_key');
            update_option('sender_automated_emails_plugin_active', false);
            return true;
        } else {
            update_option('sender_automated_emails_api_key', $apiKey);
            update_option('sender_automated_emails_plugin_active', true);
            
            $lists = $api->getAllLists();
            
            $forms = $api->getAllForms();
            
            if(isset($lists[0]->id) ) {
                update_option('sender_automated_emails_customers_list', 
                    array('id' => (int) $lists[0]->id, 'title' => sanitize_text_field($lists[0]->title)));
            } else {
                update_option('sender_automated_emails_allow_guest_track', 0);
            }
            
            if(isset($lists[0]->id)) {
                update_option('sender_automated_emails_registration_list', 
                    array('id' => (int) $lists[0]->id, 'title' => sanitize_text_field($lists[0]->title)));
            } else {
                update_option('sender_automated_emails_registration_track', 0);
            }
            
            if(isset($forms->error) && get_option('sender_automated_emails_allow_forms')) {
                update_option( 'sender_automated_emails_allow_forms', 0 );
            }
            
        }
        
    }
    
    /**
     * Delete options on disconnect
     */
    public function disconnect() {
        delete_option('sender_automated_emails_api_key');
        delete_option( 'sender_automated_emails_customers_list' );
    }
    
    /**
     * Generate carts table content in dashboard
     * 
     * @global type $wpdb
     */
    public function getSenderCarts() {
            global $wpdb;
            
            $usersTable = $wpdb->prefix."sender_automated_emails_users";
            $cartsTable = $wpdb->prefix."sender_automated_emails_carts";
            $cartPeriod = get_option('sender_automated_emails_cart_period');
            
            switch ($cartPeriod) {
                case 'hour':
                    $cartPeriod = '1 HOUR';
                    break;
                case 'today':
                    $cartPeriod = '0 DAY';
                    break;
                case 'week':
                    $cartPeriod = '7 DAY';
                    break;
                case 'month':
                    $cartPeriod = '30 DAY';
                    break;
                case 'alltime':
                    $cartPeriod = '100 YEAR';
                    break;
                default:
                    break;
            }
            
            $items_per_page = 5;
            $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
            $offset = ( $page * $items_per_page ) - $items_per_page;
            
            $query   = "
                SELECT {$cartsTable}.*, {$usersTable}.email
                FROM `{$cartsTable}`
                LEFT JOIN `{$usersTable}`
                ON {$cartsTable}.user_id = {$usersTable}.id
                WHERE {$cartsTable}.updated >= unix_timestamp(CURRENT_DATE - INTERVAL {$cartPeriod})
                AND {$cartsTable}.updated <= unix_timestamp(CURRENT_DATE + INTERVAL 1 DAY)
                AND cart_status = 0
            ";
                
            $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
            $total = $wpdb->get_var( $total_query );
            

            $query   = "
                SELECT {$cartsTable}.*, {$usersTable}.email
                FROM `{$cartsTable}`
                LEFT JOIN `{$usersTable}`
                ON {$cartsTable}.user_id = {$usersTable}.id
                WHERE {$cartsTable}.updated >= unix_timestamp(CURRENT_DATE - INTERVAL {$cartPeriod})
                AND {$cartsTable}.updated <= unix_timestamp(CURRENT_DATE + INTERVAL 1 DAY)
                AND cart_status = 0
                ORDER BY {$cartsTable}.created DESC
                LIMIT {$offset}, {$items_per_page} 
            ";
                
            $results = $wpdb->get_results($query);
            
            $links = paginate_links( array(
                                    'base' => add_query_arg( 'cpage', '%#%&#!dashboard' ),
                                    'format' => '',
                                    'prev_text' => __('&laquo;'),
                                    'next_text' => __('&raquo;'),
                                    'total' => ceil($total / $items_per_page),
                                    'type' => 'array',
                                    'current' => $page
                                )); 
            
            echo '        
                <table id="table"  class="table table-hover tablesorter">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Recovered</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                <tbody>
            ';
            
            $i = 1;
            foreach($results as $result) {
                
                $date = gmdate("Y-m-d H:i:s ", $result->updated);
                $cs = 'ACTIVE';
                $color = '#a46497';
                
                if((int)$result->updated < ((int)current_time('timestamp') - (60 * 15))) {
                    $cs = 'ABANDONED';
                    $color = 'red';
                }

                if($result->cart_status == 2) {
                    $cs = 'CONVERTED';
                    $color = 'green';
                }

                if($result->user_type == 'REGISTERED') {
                    $result->email = get_userdata($result->user_id)->user_email;

                }

                $cart = json_decode($result->cart_data);

                if(isset($cart->cart)) {
                    $cart_details = $cart->cart;
                } else {
                    $cart_details = [];
                }


                $line_total = 0;

                if( count( $cart_details ) > 0 ) {    		
                    foreach( $cart_details as $k => $v ) {    		     
                        if( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                            $line_total = $line_total + $v->line_total + $v->line_subtotal_tax;
                        } else {
                            $line_total = $line_total + $v->line_total;
                        }
                    }
                }

                $line_total     = wc_price( $line_total );
                $quantity_total = 0;

                if ( count( $cart_details ) > 0) {    		         
                    foreach( $cart_details as $k => $v ) {
                        $quantity_total = $quantity_total + $v->quantity;
                    }
                }

                printf('
                    <tr>
                        <td>%d</td>
                        <td>%s</td>
                        <td style="color: %s;"><i class="zmdi zmdi-shopping-cart"></i> %s</td>

                        <td>%s</td>
                        <td>%s</td>

                        <td>%s</td>
                        <td>%s</td>
                    </tr>', (int )$i, esc_html($result->email) ? esc_html($result->email) : 'Not provided', $color, $cs, (bool) $result->cart_recovered ? 'Yes' : 'No', (int )$quantity_total, (int) $line_total, $date );

                $i++;
                
            }
            
            echo '</tbody>
        </table>';
            
            if(isset($links)) {
                echo '<div style="text-align: center;">';
                foreach ($links as $link) {
                    $link = str_replace('current', 'sae-current-link', $link);
                    echo '<span class="" style="margin-left:2px; background-color: #fff; color: #000 !important; text-decoration: none !important;">' . str_replace('page-numbers', 'sender-net-automated-emails-button', $link) . '</span>';
                }
                echo "</div>";
            }
    
    }
    
    /**
     * Generate converted carts table content in dashboard
     * 
     * @global type $wpdb
     */
    public function getSenderConvertedCarts() {
            global $wpdb;
            
            $usersTable = $wpdb->prefix."sender_automated_emails_users";
            $cartsTable = $wpdb->prefix."sender_automated_emails_carts";
            $cartPeriod = get_option('sender_automated_emails_cart_period');
            
            switch ($cartPeriod) {
                case 'hour':
                    $cartPeriod = '1 HOUR';
                    break;
                case 'today':
                    $cartPeriod = '0 DAY';
                    break;
                case 'week':
                    $cartPeriod = '7 DAY';
                    break;
                case 'month':
                    $cartPeriod = '30 DAY';
                    break;
                case 'alltime':
                    $cartPeriod = '100 YEAR';
                    break;
                default:
                    break;
            }
            
            $items_per_page = 5;
            $page = isset( $_GET['ccpage'] ) ? abs( (int) $_GET['ccpage'] ) : 1;
            $offset = ( $page * $items_per_page ) - $items_per_page;
            
            $query   = "
                SELECT {$cartsTable}.*, {$usersTable}.email
                FROM `{$cartsTable}`
                LEFT JOIN `{$usersTable}`
                ON {$cartsTable}.user_id = {$usersTable}.id
                WHERE {$cartsTable}.updated >= unix_timestamp(CURRENT_DATE - INTERVAL {$cartPeriod})
                AND {$cartsTable}.updated <= unix_timestamp(CURRENT_DATE + INTERVAL 1 DAY)
                AND cart_status = 2
            ";
                
            $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
            $total = $wpdb->get_var( $total_query );
            

            $query   = "
                SELECT {$cartsTable}.*, {$usersTable}.email
                FROM `{$cartsTable}`
                LEFT JOIN `{$usersTable}`
                ON {$cartsTable}.user_id = {$usersTable}.id
                WHERE {$cartsTable}.updated >= unix_timestamp(CURRENT_DATE - INTERVAL {$cartPeriod})
                AND {$cartsTable}.updated <= unix_timestamp(CURRENT_DATE + INTERVAL 1 DAY)
                AND cart_status = 2
                ORDER BY {$cartsTable}.created DESC
                LIMIT {$offset}, {$items_per_page} 
            ";
                
            $results = $wpdb->get_results($query);
            
            $links = paginate_links( array(
                                    'base' => add_query_arg( 'ccpage', '%#%&#!converted' ),
                                    'format' => '',
                                    'prev_text' => __('&laquo;'),
                                    'next_text' => __('&raquo;'),
                                    'total' => ceil($total / $items_per_page),
                                    'type' => 'array',
                                    'current' => $page
                                )); 
            
            $i = 1;
            $total_recovered = 0;
            
            echo '        
                <table id="table"  class="table table-hover tablesorter">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Recovered</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                <tbody>
            ';
            
            foreach($results as $result) {
                
                $date = gmdate("Y-m-d H:i:s ", $result->updated);
               
                $cs = 'CONVERTED';
                $color = 'green';
               

                if($result->user_type == 'REGISTERED') {
                    $result->email = get_userdata($result->user_id)->user_email;
                }

                $cart = json_decode($result->cart_data);

                if(isset($cart->cart)) {
                    $cart_details = $cart->cart;
                } else {
                    $cart_details = [];
                }


                $line_total = 0;

                if( count( $cart_details ) > 0 ) {    		
                    foreach( $cart_details as $k => $v ) {    		     
                        if( $v->line_subtotal_tax != 0 && $v->line_subtotal_tax > 0 ) {
                            $line_total = $line_total + $v->line_total + $v->line_subtotal_tax;
                        } else {
                            $line_total = $line_total + $v->line_total;
                        }
                    }
                }

                if($result->cart_recovered == 1) {
                   $total_recovered += $line_total;
                }
                
                $line_total     = wc_price( $line_total );
                $quantity_total = 0;

                if ( count( $cart_details ) > 0) {    		         
                    foreach( $cart_details as $k => $v ) {
                        $quantity_total = $quantity_total + $v->quantity;
                    }
                }
                

                printf('
                    <tr>
                        <td>%d</td>
                        <td>%s</td>
                        <td style="color: %s;"><i class="zmdi zmdi-shopping-cart"></i> %s</td>

                        <td>%s</td>
                        <td>%s</td>

                        <td>%s</td>
                        <td>%s</td>
                    </tr>', (int) $i, esc_html($result->email) ? esc_html($result->email) : 'Not provided', $color, $cs, (bool) $result->cart_recovered ? 'Yes' : 'No', (float) $quantity_total, (float) $line_total, $date );

                $i++;
                
            }
            
            echo '</tbody>
        </table>';
            
//            if($total_recovered > 1) {
//                echo '<i class="zmdi zmdi-money"></i> These carts have recovered: <strong>' . $total_recovered . '€</strong> of otherwise lost income!';
//            }
            
            if(isset($links)) {
                echo '<div style="text-align: center;">';
                foreach ($links as $link) {
                    $link = str_replace('current', 'sae-current-link', $link);
                    echo '<span class="" style="margin-left:2px; background-color: #fff; color: #000 !important; text-decoration: none !important;">' . str_replace('page-numbers', 'sender-net-automated-emails-button', $link) . '</span>';
                }
                echo "</div>";
            }
    
    }
    
    /**
     * Error logging function
     * 
     * @param String $error
     */
    public function logError($error) {
        if($this->debug) {
            error_log($error);
        }
    }

    /**
     * Returns notice HTML
     * 
     * @param string $text
     * @param string $type | 'success' | 'error' | 'info' | 'warning'
     * @return string
     */
    public function showNotice($text, $type) {
        
        $notice =   '<div class="sender-net-automated-emails-alert sender-net-automated-emails-'
                    . $type . '">' . $text . '</div>';
        
        return $notice;
    }
    
}