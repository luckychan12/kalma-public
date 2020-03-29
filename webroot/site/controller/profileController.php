<?php
include_once '../api_tasks/apiConnect.php';

session_start();

$api = new ApiConnect();
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



