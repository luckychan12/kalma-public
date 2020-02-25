<?php
include_once "header.php";
include_once "../controller/loginNOutController.php";
if(isset($_POST['login'])) {
    validateLogin($_POST['logPassword'],$_POST['logEmail']);
}
?>


<!doctype html>

<html lang='en'>
<style>
    input {
        padding:5px;
        margin:10px;
        width:350px;
        font: var(--f-normal);
    }
    .hide {display: none;}

</style>
<script>

    window.onload = function() {
        var today = new Date();
        var day = today.getDate();
        var month = today.getMonth() + 1;
        var year = today.getFullYear();

        if (day < 10)
            day = '0' + day;
        if (month < 10)
            month = '0' + month;
        var now = year+ '-' + month + '-' + day;
        document.getElementById('dob').setAttribute("max", now);
    }
</script>
<body >

<div class="container-fluid" >
<div class="row" style="margin-top: 20px">
    <div class="col-md-1"></div>
    <div class="col-lg-5" style="padding:10px;background-color: var(--c-primary-dark);font:var(--f-normal) ;color: var(--c-text-on-primary);border-top-left-radius:20px; border-bottom-left-radius: 20px">
        <h2 id="loginHeader" style="text-align: center">Login</h2>
        <form action="loginAndSignup.php" method="post" style="text-align: center">
            <input type="email" placeholder="Email" name="logEmail">
            <br>
            <input type="password" placeholder="Password" name="logPassword">
            <br>
            <input style="width: 100px;background-color: var(--c-secondary); color:var(--c-text-on-secondary)" type="submit" name="login" value="Submit">
        </form>
    </div>
    <div class="col-lg-5" style="padding:10px;background-color: var(--c-primary-dark);font:var(--f-normal) ;color: var(--c-text-on-primary);border-top-right-radius:20px; border-bottom-right-radius: 20px">
        <h2 style="text-align: center; ">Sign up</h2>
        <form action="loginAndSignup.php" method="post" style="text-align: center">
            <input type="email" placeholder="Email" id="email">
            <br>
            <input type="text" placeholder="First Name" id="firstName">
            <br>
            <input type="text" placeholder="Last Name" id="lastName">
            <br>
            <input type="password" placeholder="Password" id="password">
            <br>
            <input type="date"  id="dob" min="1900-01-01">
            <br>
            <input style="width: 100px;background-color: var(--c-secondary); color:var(--c-text-on-secondary)" type="submit" name="signup" value="Submit">
        </form>
    </div>
</div>
</div>
</body>
