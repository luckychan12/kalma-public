<?php
include_once 'header.php';
include_once "../api_tasks/apiConnect.php";

if (isset($_GET['confirmation'])){
    $api = new ApiConnect();
    $data = $api->confirmAccount($_GET['confirmation']);

}
?>

<!doctype html>
<html lang='en'>
<body>
<div>
    <button onclick="window.location.href = 'loginAndSignup.php'" style="background-color: var(--c-secondary);margin: 20px; padding-right: 10px;padding-left: 10px">Login</button>
</div>
<div style="text-align: center">
    <?php
    if(!isset($data->error)){
        echo $data->message;
    }
    else {
        echo '<script>location.href = "../public/errorPage.php" </script>';
    }
    ?>
</div>
</body>
