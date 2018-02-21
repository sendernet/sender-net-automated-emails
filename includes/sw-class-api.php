<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Integration Api Class
 * 
 * Handles communication with sender
 */
class Sender_Woocommerce_Api extends Sender_Woocommerce_Settings {
    
    private $apiKey;
    private $apiEndpoint;
    private $commerceEndpoint;

    public function __construct() {
        
        $this->apiKey = get_option('sender_woocommerce_api_key');
        $this->apiEndpoint = $this->getBaseUrl() . '/api';
        $this->commerceEndpoint = $this->getBaseUrl() . '/commerce/v1';
        
    }
    
    /**
     * 
     * @return type
     */
    public function getApiKey() {
        return $this->apiKey;
    }
    
    /**
     * 
     * @param type $key
     * @return boolean
     */
    public function setApiKey($key = null) {
        
        if(!$key) {
            return false;
        }
        
        $this->apiKey = $key;
        
        return true;
    }

    /**
     * Try to make api call to check whether
     * the api key is valid
     *
     *
     * @return boolean | true if valid key
     */
    public function checkApiKey() {
        // Try
        $response = $this->ping();
        
        if (!isset($response->pong) || !$this->getApiKey()) { // Wrong api key
            delete_option('sender_woocommerce_api_key');
            update_option('sender_woocommerce_plugin_active', false);
            return false;
        }
        
        return $response;
    }

    public function ping() {
        $data = array(
            "method" => "ping",
            "params" => array(
                "api_key" => $this->apiKey,
 
            )
        );
        
        return $this->makeApiRequest($data);
    }


    /**
     * Retrieve all mailinglists
     * 
     * @return type
     */
    public function getAllLists() {
        $data = array(
            "method" => "listGetAllLists", 
            "params" => array(
                "api_key" => $this->apiKey,
 
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve all forms
     * 
     * @return type
     */
    public function getAllForms() {
        $data = array(
            "method" => "formGetAll", 
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve push project script url
     * 
     * @return type
     */
    public function getPushProject() {
        $data = array(
            "method" => "pushGetProject", 
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve specific form via ID
     * 
     * @param type $id
     * @return type
     */
    public function getFormById($id) {
        $data = array(
            "method" => "formGetById", 
            "params" => array(
                "form_id" => $id,
                "api_key" => $this->apiKey,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Add user or info to mailinglist
     * 
     * @param type $email
     * @param type $listId
     * @param type $fname
     * @param type $lname
     * @return type
     */
    public function addToList($email, $listId, $fname = '', $lname = '') {
        
        $data = array(
            "method" => "listSubscribe", 
            "params" => array(
                "api_key" => $this->apiKey,
                "list_id" => $listId,
                "emails" => array(
                    'email' => $email,
                    'firstname' => $fname,
                    'lastname' => $lname)
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Start cart tracking
     * 
     * @param type $params
     * @return type
     */
    public function cartTrack($params) {
        
        $params['api_key'] = $this->apiKey;
        $params['url'] = get_site_url() . '/?hash={$cart_hash}';
        
        return $this->makeCommerceRequest($params, 'cart_track');

    }

    /**
     * Get cart from sender
     * 
     * @param type $cartHash
     * @return type
     */
    public function cartGet($cartHash) {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "cart_hash" => $cartHash,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_get');

    }
    
    /**
     * Convert cart
     * 
     * @param type $cartId
     * @return type
     */
    public function cartConvert($cartId) {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_convert');
        
    }
    
    /**
     * 
     * @param type $cartId
     * @return type
     */
    public function cartDelete($cartId) {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_delete');
    }

    /**
     * 
     * @param type $params
     * @param type $method
     * @return type
     */
    private function makeCommerceRequest($params, $method) {
        ini_set('display_errors', 'Off');
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($params)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->commerceEndpoint . '/' . $method, false, $context);
        $response = json_decode($result);
        return $response;
    }

    /**
     * 
     * @param type $params
     * @return type
     */
    private function makeApiRequest($params) {
        ini_set('display_errors', 'Off');

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(array('data' => json_encode($params)))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->apiEndpoint, false, $context);
        $response = json_decode($result);
        return $response;
        
    }
}
