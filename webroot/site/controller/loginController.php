<?php
/*
 * Sends the request to log in and stores the returned results
 * Sends to the dashboard page or invalid the login page
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "../controller/ensureFingerprint.php";
require_once "../api_tasks/ApiConnector.php";

if(isset($_POST['login'])) {
    $api = new ApiConnector();
    $data = $api->request('POST', 'api/user/login', array(
        'email_address' => $_POST['logEmail'],
        'password' => $_POST['logPassword'],
        'client_fingerprint' => $_SESSION['fingerprint'],
    ));
    if (!isset($data->error)) {
        $_SESSION['links'] = $data->links;
        $_SESSION['auth'] = (object)[
            'access_token' => $data->access_token,
            'access_expiry' => $data->access_expiry,
            'refresh_token' => $data->refresh_token,
            'refresh_expiry' => $data->refresh_expiry,
        ];

        // Update the refresh cookie, if it already exists
        if(isset($_POST['remember'])) {
            setcookie('refresh', $data->refresh_token);
        }

        header('Location: ./dashboard.php');
    }
    else {
        $_SESSION['login_message'] = "{$data->message} ({$data->error})";
    }
}



?>








