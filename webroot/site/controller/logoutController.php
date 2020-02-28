<?php
/*
 * Controls what the functionality for logging out
 */

session_start();
include_once '../api_tasks/apiConnect.php';
echo $_GET['clientFingerprint'];
$api= new ApiConnect();
$data = $api->signOut($_GET['clientFingerprint']);
if($data->success){
    echo '<script> location.href = "../public/loginAndSignup.php"</script>';
}
