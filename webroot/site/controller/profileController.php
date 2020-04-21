<?php
session_start();

require_once '../controller/ensureFingerprint.php';
require_once '../api_tasks/ApiConnector.php';

$api = new ApiConnector();
if (isset($_SESSION['links'])) {
    $data = $api->getData($_SESSION['links']->account);
}
else {
    header('Location: ./loginAndSignup');
}

if (isset($data->error)) {
    header('Location: ./errorPage');
}
?>