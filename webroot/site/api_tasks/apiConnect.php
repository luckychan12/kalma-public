<?php
require '../vendor/autoload.php';

use GuzzleHttp\Client;

class ApiConnect
{
    private $client;
    function __construct()
    {
        $this->client =  new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://localhost/',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }

    function requestLogin($inPassword,$inEmail,$inClientFingerprint){
        $res = $this->client->request('POST', 'api/user/login', ['json' => ["email_address" => "dummy@example.com", "password"=> "Password123!", "client_fingerprint" => "123456789"]]);
        $messageBody = $res->getBody()->read(2048);
        $json = json_decode($messageBody);
        //echo '<p>'.$json->access_token.')</p>';
        return $json;
    }
}

?>

