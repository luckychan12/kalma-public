<?php
session_start();

require_once '../api_tasks/ApiConnector.php';

if(isset($_POST['signup'])){
    $dob = (new DateTime($_POST['dob']))->format(DATE_ISO8601);
    $api = new ApiConnector();
    $result = $api->request('POST', 'api/user/signup', array(
            'first_name' => $_POST['firstName'],
            'last_name' => $_POST['lastName'],
            'password' => $_POST['password'],
            'email_address' => $_POST['email'],
            'date_of_birth' => $dob,
        )
    );

    if (!isset($result->error)){
        $_SESSION['confirmationLink'] = $result->confirmation_url;
        header('Location: ./signup-success.php');
        exit();
    }
    else {
        header("Location: ./error.php?code=$result->error&message=$result->message");
        exit();
    }
}





