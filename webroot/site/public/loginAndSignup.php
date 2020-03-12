<?php
include_once "header.php";

?>


<!doctype html>

<html lang='en'>
<style>
label {
    margin-top: 5px;
    margin-bottom: 0;
    text-align: left;
    width: 350px;
}
    input {
        padding:5px;
        margin:5px;
        width:350px;
        font: var(--f-normal);
    }

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
        <div style="text-align: center">
         <?php
         //Displays a message if there was an issue logging in
         if (isset($_SESSION['login_message']))
         {
             echo $_SESSION['login_message'];
         }
         ?>
        </div>


        <form action="../controller/loginController.php" method="post" style="text-align: center">
            <label for="logEmail">Email Address:</label>
            <input type="email" placeholder="Email" name="logEmail" id="logEmail" required>
            <br>
            <label for="logPassword">Password:</label>
            <input type="password" placeholder="Password" name="logPassword" id="logPassword" required>
            <br>
            <input type="hidden" name="fingerprint" id="hiddenFingerprint">
            <input style="width: 100px;background-color: var(--c-secondary); color:var(--c-text-on-secondary)" type="submit" name="login" value="Submit">
        </form>
    </div>
    <div class="col-lg-5" style="padding:10px;background-color: var(--c-primary-dark);font:var(--f-normal) ;color: var(--c-text-on-primary);border-top-right-radius:20px; border-bottom-right-radius: 20px">
        <h2 style="text-align: center; ">Sign up</h2>
        <form action="../controller/signUpController.php" method="post" style="text-align: center">
            <label for="email">Email Address:</label>
            <input type="email" placeholder="Email" name="email" id="email" required>
            <br>
            <label for="firstName">First Name:</label>
            <input type="text" placeholder="First Name" name="firstName" id="firstName" required>
            <br>
            <label for="lastName">Last Name:</label>
            <input type="text" placeholder="Last Name" name="lastName" id="lastName" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" placeholder="Password" name="password" id="password" required>
            <br>
            <label for="dob">Date of Birth:</label>
            <input type="date"  name="dob" min="1900-01-01" placeholder="Date Of Birth" id="dob" required>
            <br>
            <input style="width: 100px;background-color: var(--c-secondary); color:var(--c-text-on-secondary)" type="submit" name="signup" value="Submit">
        </form>
    </div>
</div>
</div>
</body>

<script>
    let client = new ClientJS();
    let fingerprint = client.getFingerprint();
    document.getElementById("hiddenFingerprint").value = fingerprint;
</script>