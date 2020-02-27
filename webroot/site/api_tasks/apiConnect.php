<?php
require '../vendor/autoload.php';

use GuzzleHttp\Client;

/**
 * Class ApiConnect
 */
class ApiConnect
{
    /**
     * @var Client
     */
    private $client;

    /**
     * ApiConnect constructor.
     */
    function __construct()
    {
        $this->client =  new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://localhost/',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }

    /**
     * Request to login
     * @param $inPassword
     * @param $inEmail
     * @param $inClientFingerprint
     * @return mixed
     */
    function requestLogin($inPassword, $inEmail, $inClientFingerprint){
        try {
            $res = $this->client->request('POST', 'api/user/login', ['json' => ["email_address" => $inEmail, "password" => $inPassword, "client_fingerprint" => $inClientFingerprint]]);
            $messageBody = $res->getBody()->read(2048);
            $data = json_decode($messageBody);
        }
        catch (\GuzzleHttp\Exception\ClientException $e){
            return "error";

        }
        return $data;

    }

    /**
     * Get data from the api
     * @param $link
     * @return mixed|string
     */
    function getData($link){
        try {
            $res = $this->client->request('GET', $link, ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']]]);
            $messageBody = $res->getBody()->read(2048);
            $data = json_decode($messageBody);
        }
        catch (\GuzzleHttp\Exception\ClientException $e){
            return "error";
        }
        return $data;
    }

    /**
     * Get new tokens
     *
     * Work in progress
     * @param $clientFingerprint
     */
    function refreshToken($clientFingerprint){
        $res = $this->client->request('POST', 'api/user/refresh', ['json' => ["refresh_token" => $_SESSION["refresh_token"], "client_fingerprint" => $clientFingerprint]]);
        $messageBody = $res->getBody()->read(2048);
        $data= json_decode($messageBody);
        if ($data->success){
            $_SESSION['access_token']= $data->access_token;
            $_SESSION['refresh_token'] = $data->refresh_token;
        }
        else{
            //session_destroy();
            echo'<script>location.href = "../public/loginAndSignup.php" </script>';
        }
    }

    /**
     * Request to signout using the api
     * @param $clientFingerprint
     * @return mixed
     */
    function signOut($clientFingerprint){
        $res = $this->client->request('POST', 'api/user/logout', ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'json' => ['client_fingerprint' => $clientFingerprint]]);
        session_destroy();
        $messageBody = $res->getBody()->read(2048);
        $data= json_decode($messageBody);
        return $data;

    }
}

?>

