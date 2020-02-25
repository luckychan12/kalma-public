<?php
if (session_status() != 2){
    session_start();
}

include_once "../api_tasks/apiConnect.php";
//TODO work on security
function validateLogin($password, $email){
    $api = new ApiConnect();
    $data = $api->requestLogin($password, $email, "123456789");
    if ($data->success) {
        $_SESSION['access_token'] = $data->access_token;
        echo '<script> location.href = "../public/dashboard"</script>';
    }
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







