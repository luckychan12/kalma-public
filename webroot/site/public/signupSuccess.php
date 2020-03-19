<?php
include_once "header.php";

?>
<!doctype html>
<html lang='en'>
<body>
<div>
    <!--<button onclick="window.location.href = 'loginAndSignup.php'" style="background-color: var(--c-secondary);margin: 20px; padding-right: 10px;padding-left: 10px">Login</button> -->
</div>
<h1 style="padding-top: 20px;text-align: center">You have successfully created your account.<br>
    Wait 10 seconds and then
    <br>
    click the button below to activate your account.</h1>
<?php $url = $_SESSION['confirmationLink'];
        $parsedUrl = parse_url($url);
        $confirmationData = $parsedUrl['query'];
?>
<div style="text-align: center;">
<button style="background-color: var(--c-secondary);margin: 20px; padding:10px" onclick=location.href="confirmationPage.php?<?php echo $confirmationData ?>">Click here to activate your account</button>
</div>
</body>