<?php
include_once "../controller/dashboardController.php";
?>
<!doctype html>
<html lang='en'>
<head>
    <?php include_once './components/global_head_inner.php' ?>
</head>
<body>
    <?php include_once './components/navbar_top.php'; ?>

    <h1>Welcome, <?= $data->user->first_name ?>!</h1>
</body>

