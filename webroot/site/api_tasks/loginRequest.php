<?php
include_once "apiConnect.php";
//TODO Add validation to make sure emails/passwords are correct
function validateLogin($password, $email){
    requestLogin($password, $email, "123456789");
}
?>








