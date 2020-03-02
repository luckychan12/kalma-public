<?php
/*
 * Controls what the functionality for logging out
 */

session_start();
include_once '../api_tasks/apiConnect.php';
$api= new ApiConnect();
$data = $api->signOut($_GET['clientFingerprint']);
if(!isset($data->error)){
    echo '<script> location.href = "../public/loginAndSignup.php"</script>';
}
else{
    echo'<script>location.href = "../public/errorPage.php" </script>';
}
