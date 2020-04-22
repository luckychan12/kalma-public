<?php
/*
 * Controls what the functionality for logging out
 */

session_start();

require_once '../api_tasks/ApiConnector.php';
require_once '../controller/ensureFingerprint.php';

$api = ApiConnector::getConnection();
$data = $api->request('POST', $_SESSION['links']->logout, ['client_fingerprint' => $_SESSION['fingerprint']]);
session_unset();

if(!isset($data->error)){
    header('Location: ./login-and-signup.php');
}
else {
    header("Location: ./error.php?code=$data->error&message=$data->message");
}
