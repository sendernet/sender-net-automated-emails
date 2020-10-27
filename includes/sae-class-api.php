<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Automated Emails Api Class
 * Handles communication with sender
 */
class Sender_Automated_Emails_Api extends Sender_Automated_Emails_Settings
{

    private $apiKey;
    private $apiEndpoint;
    private $commerceEndpoint;

    public function __construct()
    {

        $this->apiKey = get_option('sender_automated_emails_api_key');
        $this->apiEndpoint = $this->getBaseUrl() . '/v1';
        $this->commerceEndpoint = $this->getBaseUrl() . '/commerce/v1';

    }

    /**
     *
     * @return type
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     *
     * @param null $key
     * @return bool
     */
    public function setApiKey($key = null)
    {
        if (!$key) {
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
    public function checkApiKey()
    {
        // Try
        $response = $this->ping();
        //|| !$this->getApiKey()
        if (!isset($response->pong)) { // Wrong api key
            delete_option('sender_automated_emails_api_key');
            update_option('sender_automated_emails_plugin_active', false);
            return false;
        }

        return $response;
    }

    /**
     * Ping server to check API key
     * @return array
     */
    public function ping()
    {
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
     * @return array|mixed
     */
    public function getAllLists()
    {
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
     * @return array|mixed
     */
    public function getAllForms()
    {
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
     * @return array|mixed
     */
    public function getPushProject()
    {
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
     * @param int $id
     * @return array
     */
    public function getFormById($id)
    {
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
     * @param string $email
     * @param int $listId
     * @param string $fname
     * @param string $lname
     * @return array
     */
    public function addToList($email, $listId, $fname = '', $lname = '')
    {

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
     * @param array $params
     * @return array|mixed
     */
    public function cartTrack($params)
    {

        $params['api_key'] = $this->apiKey;
        $params['url'] = get_site_url() . '/?hash={$cart_hash}';

        return $this->makeCommerceRequest($params, 'cart_track');

    }

    /**
     * Get cart from sender
     * @param string $cartHash
     * @return array|mixed
     */
    public function cartGet($cartHash)
    {

        $params = array(
            "api_key" => $this->apiKey,
            "cart_hash" => $cartHash,
        );

        return $this->makeCommerceRequest($params, 'cart_get');

    }

    /**
     * Convert cart
     * @param array $cartId
     * @return array|mixed
     */
    public function cartConvert($cartId)
    {

        $params = array(
            "api_key" => $this->apiKey,
            "external_id" => $cartId,
        );

        return $this->makeCommerceRequest($params, 'cart_convert');

    }

    /**
     * Delete cart
     * @param int $cartId
     * @return array|mixed
     */
    public function cartDelete($cartId)
    {

        $params = array(
            "api_key" => $this->apiKey,
            "external_id" => $cartId,
        );

        return $this->makeCommerceRequest($params, 'cart_delete');
    }

    /**
     * Setup commerce request
     * @param array $params
     * @param string $method
     * @return array
     */
    private function makeCommerceRequest($params, $method)
    {
        $params['api_key'] = $this->getApiKey();
        if (function_exists('curl_version')) {
            return $this->makeCurlRequest(http_build_query(array('data' => $params)), $this->commerceEndpoint . '/' . $method);
        }
        return $this->makeHttpRequest($params, $this->commerceEndpoint . '/' . $method);
    }

    /**
     * Setup api request
     * @param array $params
     * @return array
     */
    private function makeApiRequest($params)
    {
        if (function_exists('curl_version')) {
            return $this->makeCurlRequest(http_build_query(array('data' => json_encode($params))), $this->apiEndpoint);
        }
        return $this->makeHttpRequest($params, $this->apiEndpoint);
    }

    /**
     * Make HTTP request through file_get_contents
     * @param $data
     * @param $endpoint
     * @return array|mixed|object
     */
    private function makeHttpRequest($data, $endpoint)
    {
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(array('data' => $data))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($endpoint, false, $context);
        return json_decode($result);
    }

    /**
     * Make api request through CURL
     * @param array|string $data payload
     * @param string $endpoint
     * @return array Server response
     */
    private function makeCurlRequest($data, $endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        return json_decode($server_output);
    }
}
