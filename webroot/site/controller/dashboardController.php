<?php
include_once '../api_tasks/apiConnect.php';
    $api = new ApiConnect();
    $data = $api->getData($_SESSION['account_link']);

?>




