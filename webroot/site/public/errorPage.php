<?php
include_once "header.php";

?>
<!doctype html>
<html lang='en'>
<body style="">
<button onclick="window.location.href = 'loginAndSignup.php'" style="background-color: var(--c-secondary);margin: 20px; padding-right: 10px;padding-left: 10px">Back</button>
    <div style="padding-top: 20px;text-align: center; font-size: xx-large; font:var(--f-normal)">
        Status: <?php echo $_SESSION['status'];?>
        <br>
        Error: <?php echo $_SESSION['error'] ?>
        <br>
        Message: <?php echo $_SESSION['error_message'] ?>
    </div>
</body>
