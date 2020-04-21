<?php
include_once '../api_tasks/ApiConnector.php';

session_start();

$api = new ApiConnector();
if (isset($_SESSION['account_link'])) {
    $data = $api->getData($_SESSION['account_link']);
}
else {
    header('Location: ../public/loginAndSignup.php');
}

if (isset($data->error)) {
    header('Location: ../public/errorPage.php');
}

?>



