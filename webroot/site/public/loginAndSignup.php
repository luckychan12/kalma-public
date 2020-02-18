<?php
include_once "header.php";
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
</style>

<body >

<div class="container-fluid" >
<div class="row" style="margin-top: 20px">
    <div class="col-md-1"></div>
    <div class="col-lg-5" style="padding:10px;background-color: var(--c-primary-dark);font:var(--f-normal) ;color: var(--c-text-on-primary);border-top-left-radius:20px; border-bottom-left-radius: 20px">
        <h2 style="text-align: center">Login</h2>
        <form style="text-align: center">
            <input type="email" placeholder="Email" id="logEmail">
            <br>
            <input type="password" placeholder="Password" id="logPassword">
            <br>
            <input style="width: 100px;background-color: var(--c-secondary); color:var(--c-text-on-secondary)" type="submit" name="login" value="Submit">
        </form>
    </div>
    <div class="col-lg-5" style="padding:10px;background-color: var(--c-primary-dark);font:var(--f-normal) ;color: var(--c-text-on-primary);border-top-right-radius:20px; border-bottom-right-radius: 20px">
        <h2 style="text-align: center; ">Sign up</h2>
        <form action="dashboard.php" method="post" style="text-align: center">
            <input type="email" placeholder="Email" id="email">
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
