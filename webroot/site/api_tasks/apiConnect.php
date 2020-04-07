<?php
require '../vendor/autoload.php';
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
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
            'base_uri' => 'https://kalma.club',
            // You can set any number of default request options.
            'timeout'  => 50.0,

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
            $messageBody = $res->getBody()->getContents();
            $data = json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['login_message'] = $response->message;
            return $response;
        }
        catch (RequestException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['login_message'] = $response->message;
            return $response;
        }

    }

    /**
     * Get data from the api
     * @param $link
     * @return mixed|string
     */
    function getData($link){
        try {
            $res = $this->client->request('GET', $link, ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']]]);
            $messageBody = $res->getBody()->getContents();
            $data = json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }


    }

    /**
     * Get new tokens
     *
     * Work in progress
     * @param $clientFingerprint
     * @return mixed
     */
    function refreshToken($clientFingerprint){
        try {
            $res = $this->client->request('POST', 'api/user/refresh', ['json' => ["refresh_token" => $_SESSION["refresh_token"], "client_fingerprint" => $clientFingerprint]]);
            $messageBody = $res->getBody()->getContents();
            $data = json_decode($messageBody);
            $_SESSION['access_token']= $data->access_token;
            $_SESSION['refresh_token'] = $data->refresh_token;
            return $data;
        }
        catch (ClientException $e)
        {
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }




    }

    /**
     * Request to signout using the api
     * @param $clientFingerprint
     * @return mixed
     */
    function signOut($clientFingerprint){
        try{
            $res = $this->client->request('POST', 'api/user/logout', ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'json' => ['client_fingerprint' => $clientFingerprint]]);
            session_destroy();
            $messageBody = $res->getBody()->getContents();
            $data= json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }



    }
    function requestSignup($inFirstName,$inLastName,$inPassword, $inEmail, $inDOB){
        try {
            $res = $this->client->request('POST', 'api/user/signup', ['json' => ["email_address" => $inEmail, "password" => $inPassword, "first_name" => $inFirstName, "last_name" => $inLastName, "date_of_birth" =>$inDOB]]);
            $messageBody = $res->getBody()->getContents();
            $data = json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
    }

    function confirmAccount($confirmation){
        try {
            $res = $this->client->request('POST', 'api/user/confirm', ['json' => ["confirmation_token" => $confirmation]]);
            $messageBody = $res->getBody()->getContents();
            $data = json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody());
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
    }

    function addSleepData($startTime, $stopTime, $sleepQuality ){
        try{
            $res = $this->client->request('POST',"api/user/".$_SESSION['user_id']."/sleep", ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'json' => ['start_time' => $startTime, 'stop_time' => $stopTime, 'sleep_quality' => $sleepQuality]]);
            $messageBody = $res->getBody()->read(2048);
            $data= json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }
        catch (RequestException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
            session_unset();
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message;
            if(isset($response->detail)) {
                $_SESSION['detail'] = $response->detail;
            }
            return $response;
        }

    }
}

?>





