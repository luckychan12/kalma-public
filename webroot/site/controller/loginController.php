<?php
/*
 * Sends the request to log in and stores the returned results
 * Sends to the dashboard page or invalid the login page
 */

session_start();
include_once "../api_tasks/apiConnect.php";

if(isset($_POST['login'])) {
    $api = new ApiConnect();
    $data = $api->requestLogin($_POST['logPassword'], $_POST['logEmail'], $_POST['fingerprint']);
    if (!isset($data->error)) {
        $_SESSION['access_token'] = $data->access_token;
        $_SESSION['account_link'] = $data->links->account;
        $_SESSION['refresh_token'] = $data->refresh_token;
        $_SESSION['logout_link'] = $data->links->logout;
        echo $data;
       echo '<script> location.href = "../public/dashboard.php"</script>';
    }
    else
    {
        echo '<script>location.href = "../public/errorPage.php?"</script>';
    }
}



?>








