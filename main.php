<?php

require "vendor/autoload.php"; 

use GuzzleHttp\Client;

class Request
{
    private $base_uri;
    private $timeout;

    private $client_id;
    private $client_secret;
    private $grant_type;

    public $access_token;

    public function __construct(){
        $this->base_url = 'http://localhost/shop/public/api/';
        $this->timeout = 2.0;

        $this->client_id = 'SWIARMFNUXJCSKDFCES5WUDEBA';
        $this->client_secret = "ZzdSOE9qUU1UOVpob0JYVnJzako0VlczbVNQY2JXU2praWlMNTc";
        $this->grant_type = "client_credentials";

        $this->login();
    }

    protected function init_request(){
        return new Client([
            'base_uri' => $this->base_url,
            'timeout'  => $this->timeout,
            'headers' => [
                'Authorization' => $this->access_token ?? '',
                'Content-Type' => 'application/json',
                'Accept' => '*/*'
            ]
        ]);
    }

    public function post($url, $data){
        $client = $this->init_request();

        return $client->request('POST', $url, [
            'json' => $data
        ]);
    }

    public function get($url){
        $client = $this->init_request();

        return $client->request('GET', $url);
    }

    public function login(){
        $res = $this->post('oauth/token', [
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "grant_type" => $this->grant_type,
        ]);

        $res = json_decode($res->getBody());
        
        $this->access_token = $res->token_type . ' ' . $res->access_token;
    }
}
