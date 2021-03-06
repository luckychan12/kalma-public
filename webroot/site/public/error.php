<?php
session_start();
?>

<!doctype html>
<html lang='en'>

<head>
    <?php include_once './components/global_head_inner.php' ?>
</head>

<body>
    <?php include_once './components/navbar_top.php' ?>

    <div class="text-center">
        <b>Status:</b> <?= $_SESSION['status'] ?? "Unknown" ?>
        <br>
        <b>Error Code:</b> <?= $_GET['code'] ?? $_SESSION['error'] ?? "Unknown" ?>
        <br>
        <b>Message:</b> <?= $_GET['message'] ?? $_SESSION['error_message'] ?? "Unknown" ?>
        <br>
        <b>Detail:</b> <?= $_SESSION['detail'] ?? "Unknown" ?>
        <br>
        <br>
        <a class="btn btn-primary" href="./login-and-signup.php">Back to login</a>
    </div>

</body>

</html>