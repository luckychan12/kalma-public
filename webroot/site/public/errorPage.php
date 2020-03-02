<?php
include_once "header.php";

?>
<!doctype html>
<html lang='en'>
<body style="">
    <div style="padding-top: 20px;text-align: center; font-size: xx-large; font:var(--f-normal)">
        Status: <?php echo $_SESSION['status'];?>
        <br>
        Error: <?php echo $_SESSION['error'] ?>
        <br>
        Message: <?php echo $_SESSION['error_message'] ?>
    </div>
</body>
