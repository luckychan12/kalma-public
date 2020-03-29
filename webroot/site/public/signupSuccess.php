<?php
session_start();
if (isset($_SESSION['confirmationLink'])) {
    $url = $_SESSION['confirmationLink'];
    $parsedUrl = parse_url($url);
    $token = $parsedUrl['query'];
}
?>

<!doctype html>
<html lang='en'>

<head>
    <?php include_once './components/global_head_inner.php' ?>
</head>

<body>
    <?php include_once './components/navbar_top.php' ?>

    <div class="container-fluid text-center <?= isset($token) ? "show" : "hide" ?>">
        <h1>You have successfully created your account.</h1>
        <p class="lead">
            Wait 10 seconds and then <br>
            click the button below to activate your account.
        </p>
        <div>
            <a class="btn btn-primary" href="./confirmationPage.php?<?= $token ?? '' ?>">Click here to activate your account</a>
        </div>
    </div>

    <div class="container-fluid text-center <?= isset($token) ? "hide" : "show" ?>">
        <h1>Something went wrong creating your account.</h1>
        <div>
            <a class="btn btn-primary" href="./loginAndSignup.php">Back to login / signup</a>
        </div>
    </div>
</body>

</html>