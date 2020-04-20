<?php
/*
 * Controls what the functionality for logging out
 */

session_start();

require_once '../api_tasks/ApiConnector.php';
require_once '../controller/ensureFingerprint.php';

$api = ApiConnector::getConnection();
$data = $api->signOut($_SESSION['fingerprint']);

if(!isset($data->error)){
    session_unset();
    header('Location: ./loginAndSignup');
}
else {
    header('Location: ./errorPage');
}
