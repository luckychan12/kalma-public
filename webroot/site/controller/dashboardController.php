<?php
/*
 * Controls the data flow in and out of the page
 */
session_start();

include_once '../api_tasks/apiConnect.php';

$api = new ApiConnect();

if (isset($_SESSION['account_link'])) {
    $data = $api->getData($_SESSION['account_link']);
}
else {
    header('Location: ./loginAndSignup.php');
}

if (isset($data->error)) {
    header('Location: ./errorPage.php');
}
else{
    $_SESSION['user_id'] = $data->user->user_id;
}
