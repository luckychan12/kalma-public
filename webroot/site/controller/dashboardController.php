<?php
include_once '../api_tasks/apiConnect.php';
//include_once 'clientFingerprint.php';


    $api = new ApiConnect();
    $data = $api->getData($_SESSION['account_link']);
    //if ($data == "error"){
    //    $fingerprint = new fingerprint();
    //    $fingerprint->getFingerprint('refresh');
    //}
    ///else {
    //    $_SESSION['id'] = $data->user->user_id;
        echo '<h1>Welcome '.$data->user->first_name.' </h1>';
    //}

?>




