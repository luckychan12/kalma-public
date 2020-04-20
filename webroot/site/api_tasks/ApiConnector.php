<?php
/**
 * Create and dispatch requests to the Kalma API, and handle the responses
 *
 * @author Georgia Perrins (georgiadmperrins@btinternet.com)
 * @author Fergus Bentley (fergusbentley@gmail.com)
 * @category Kalma
 * @package  Web
 */

require '../vendor/autoload.php';

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

/**
 * Class ApiConnect
 */
class ApiConnector
{

    private Client $client;

    private static ApiConnector $instance;

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
            'verify' => false,
        ]);
    }

    /**
     * Return an instance of ApiConnect, using an existing instance if available, else constructing a new one
     * @return ApiConnector
     */
    public static function getConnection() : ApiConnector
    {
        return static::$instance ?? new ApiConnector();
    }

    /**
     * Make a request to the API
     *
     * @param string $method     An HTTP method corresponding to one of the CRUD actions
     * @param string $uri        The API resource identifier (excluding the server's address)
     * @param array|null $params The request body. Converted to JSON if method != "GET", else URL encoded
     * @param bool $private      Whether the endpoint requires an Authorization
     * @param array $headers     Additional headers to supply with the request
     * @return object|null
     */
    public function request(string $method, string $uri, ?array $params = null, bool $private = false, array $headers = []) : ?object
    {
        $options = array(
            'headers' => $headers,
        );

        // Provide an up-to-date Bearer token for endpoints requiring authentication
        if ($private) {
            if($this->ensureValidAccess()) {
                $access_token = $_SESSION['auth']->access_token;
                $options['headers']['Authorization'] = "Bearer $access_token";
            }
            else {
                session_unset();
                header('Location: https://kalma.club/login');
                exit();
            }
        }

        // Pass request parameters as either query parameters or a JSON request body, depending on the method
        if ($params !== null) {
            if (strcasecmp($method, 'GET') === 0) {
                $options['query'] = $params;
            }
            else {
                $options['json'] = $params;
            }
        }

        // Make the request
        $res = $this->client->request($method, $uri, $options);

        $response_body = $res->getBody()->getContents();
        $data = json_decode($response_body);

        return $data;
    }

    /**
     * Ensure that the $_SESSION variable contains a valid access token.
     * If an access_token exists, check it's expiry. If necessary, refresh the token.
     * Return true if a valid access_token exists by the end of execution, else false.
     *
     * @return bool
     */
    private function ensureValidAccess() : bool
    {
        if(!isset($_SESSION['auth'])) {
            return false;
        }

        $access_expiry = $_SESSION['auth']->access_expiry;
        $now = new DateTime("now");
        if ($access_expiry <= $now) {
            return $this->refreshSession();
        }

        return isset($_SESSION['auth']['access_token']);
    }

    /**
     * Attempt to get a new set of auth tokens to replace those in the $_SESSION variable
     * Return false if there is no refresh token exists, either in $_COOKIE or the $_SESSION
     *
     * @return bool
     */
    private function refreshSession() : bool
    {
        if (!isset($_COOKIE['refresh']) && !isset($_SESSION['auth'])) {
            return false;
        }

        $refresh_token = $_COOKIE['refresh'] ?? $_SESSION['auth']->refresh_token;

        $request_body = array(
            'refresh_token' => $refresh_token,
            'client_fingerprint' => $_SESSION['fingerprint'],
        );

        $response = $this->request('POST', 'api/user/refresh', $request_body);
        if ($response !== null) {

        }

        return false;
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
            $_SESSION['status'] = $response->status;
            $_SESSION['error'] = $response->error;
            $_SESSION['error_message'] = $response->message . " ($response->uri)";
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

    function addPeriodicData($link, $startTime, $stopTime, $extraLabel,$extraData){
        try {
            $res = $this->client->request('POST', $link, ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'json' => ['periods' => [['start_time' => $startTime, 'stop_time' => $stopTime, $extraLabel => $extraData]]]]);
            $messageBody = $res->getBody()->getContents();
            $data= json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
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
  
    function editData($link,$data){
        try{
            $res = $this->client->request('PUT',$link, ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'body' => json_encode($data)]);
            $messageBody = $res->getBody()->getContents();
            $data= json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
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
    function deleteData($link,$data){
        try {
            $res = $this->client->request('DELETE',$link, ['headers' => ["Authorization" => 'bearer ' . $_SESSION['access_token']],'body' => json_encode($data)]);
            $messageBody = $res->getBody()->getContents();
            $data= json_decode($messageBody);
            return $data;
        }
        catch (ClientException $e){
            $response = json_decode($e->getResponse()->getBody()->getContents());
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
}

?>




