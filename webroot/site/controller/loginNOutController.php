<?php
include_once "../api_tasks/apiConnect.php";
//TODO work on security
function validateLogin($password, $email){
    requestLogin($password, $email, "123456789");
}

?>

<script>
    function requestSignout(){
        requestLogout(sessionStorage.getItem('jwt'), "/api/user/" + sessionStorage.getItem('id') + "/logout")
    }
    function signout(result){
        sessionStorage.clear();
        location.href = "../public/loginAndSignup.php";
    }
</script>







