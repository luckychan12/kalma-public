<?php
session_start();
include_once "../api_tasks/apiConnect.php";

if(isset($_POST['login'])) {
    $api = new ApiConnect();
    $data = $api->requestLogin($_POST['logPassword'], $_POST['logEmail'], $_POST['fingerprint']);

    if ($data->success) {
        $_SESSION['access_token'] = $data->access_token;
        $_SESSION['account_link'] = $data->links->account;
        $_SESSION['logout_link'] = $data->links->logout;
        echo '<script> location.href = "../public/dashboard"</script>';
    }
}



function signOut(){



}
?>
<script>



</script>
<script>
    function requestSignout(){
        requestLogout(sessionStorage.getItem('jwt'), "/api/user/" + sessionStorage.getItem('id') + "/logout")
    }
    function signout(result){
        sessionStorage.clear();
        location.href = "../public/loginAndSignup.php";
    }
</script>







