<?php
/*
 * Controls the data flow in and out of the page
 */

include_once '../api_tasks/apiConnect.php';

    $api = new ApiConnect();
    $data = $api->getData($_SESSION['account_link']);
    if (!isset($data->error)){
        echo '<h1>Welcome '.$data->user->first_name.' </h1>';
    }
    else {
        echo'<script>location.href = "../public/errorPage.php" </script>';
    }

?>




