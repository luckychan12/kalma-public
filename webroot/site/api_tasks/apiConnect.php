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
    //TODO add a client fingerprint
    function requestLogin($inPassword,$inEmail,$inClientFingerprint){
        $res = $this->client->request('POST', 'api/user/login', ['json' => ["email_address" => $inEmail, "password"=> $inPassword, "client_fingerprint" => "123456789"]]);
        $messageBody = $res->getBody()->read(2048);
        $data = json_decode($messageBody);
        return $data;
    }

    function getData($link){
        $res = $this->client->request('GET', $link, ['headers' => ["Authorization" => 'bearer '.$_SESSION['access_token']]]);
        $messageBody = $res->getBody()->read(2048);
        $data = json_decode($messageBody);
        return $data;
    }
}

?>

