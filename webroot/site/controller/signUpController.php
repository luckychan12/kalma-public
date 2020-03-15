<?php
include_once '../api_tasks/apiConnect.php';
session_start();
if(isset($_POST['signup'])){
    try {
        $dob = new DateTime($_POST['dob']);
        $dob =  $dob->getTimestamp();


    } catch (Exception $e) {
    }
    $api = new ApiConnect();
    $result = $api->requestSignup($_POST['firstName'],$_POST['lastName'],$_POST['password'],$_POST['email'],$dob);
    if (!isset($result->error)){
        echo '<script>location.href = "../public/signupSuccess.php"</script>';
    }
    else {
        echo '<script>location.href = "../public/errorPage.php" </script>';
    }
}





