<?php
include_once "../api_tasks/ApiConnector.php";
$api = new ApiConnector();

$token = $_GET['confirmation'] ?? "";
$data = $api->confirmAccount($token);
?>

<!doctype html>
<html lang='en'>

<head>
    <?php include_once "./components/global_head_inner.php"; ?>
</head>

<body>
    <?php include_once "./components/navbar_top.php" ?>

    <div>
        <a class="btn btn-primary" href="./loginAndSignup.php">Login</a>
    </div>

    <div>
        <?php
        if(!isset($data->error)) {
            echo "{$data->message} ({$data->error})";
        }
        else {
            header('Location: ./errorPage.php');
            exit();
        }
        ?>
    </div>
</body>

</html>