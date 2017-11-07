<?php
/**
 * 百度鹰眼 Web API
 *
 * @author Paris<zhouhuikd@gmail.com>
 * @version 1.0
 * 2017.11.06
 */
class BaiduMapTrace {

    private $api_key = 'your ak';
    private $api_endpoint = 'http://yingyan.baidu.com/api/v3';
    private $service_id = 11111; //your service id

    const TIMEOUT = 10;

    // SSL Verification
    public $verify_ssl = false;

    private static $instance;


    private function __construct() {}

    
    //Create a new instance
    public function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }


    /**
     * Make an HTTP POST request
     * @param   string $method URL of the API request method
     * @param   array $args Assoc array of arguments (usually your data)
     * @param   int $timeout Timeout limit for request in seconds
     * @return  array|false   Assoc array of API response, decoded from JSON
     *
     */
    public function post($method, $args = array(), $timeout = self::TIMEOUT) {
        return $this->makeRequest('post', $method, $args, $timeout);
    }


    /**
     * Performs the underlying HTTP request. Not very exciting.
     * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
     * @param  string $method The API method to be called
     * @param  array $args Assoc array of parameters to be passed
     * @param int $timeout
     * @return array|false Assoc array of decoded result
     * @throws \Exception
     */
    private function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT) {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception('cURL support is required, but can not be found.');
        }

        $url = $this->api_endpoint.'/'.$method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json'
        ));*/
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //curl_setopt($ch, CURLOPT_ENCODING, '');

        switch ($http_verb) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                $this->attachRequestPayload($ch, $args);
                break;
            case 'get':
                $args['service_id'] = $this->service_id;
                $args['ak'] = $this->api_key;
                $query = http_build_query($args, '', '&');
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($ch, $args);
                break;
            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($ch, $args);
                break;
        }

        $responseContent = curl_exec($ch);

        return json_decode($responseContent);
    }


    /**
     * Encode the data and attach it to the request
     * @param   resource $ch cURL session handle, used by reference
     * @param   array $data Assoc array of data to attach
     */
    private function attachRequestPayload(&$ch, $data)
    {
        $data['ak'] = $this->api_key;
        $data['service_id'] = $this->service_id;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
   
}
