<?php
session_start();
require_once '../controller/clientFingerprint.php';

//calls to initiate logout sequence
//starts by getting the clients fingerprint
if(isset($_POST['logout'])){
    getFingerprint('logout');
}

?>



<!doctype html>
<html lang='en'>
<head>
    <title>kalma</title>
    <!-- Meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel="stylesheet" href="../assets/kalma-theme.css">
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ClientJS/0.1.11/client.min.js"></script>
    <style>
        ul {
            position: sticky;
            top: 0;
            list-style-type: none;
            margin: 0;
            padding: 0;
            background:  var(--c-primary);
            text-align: center;
            height: 52px;

        }

        li {
            display: inline-block;
        }
        li a {
            display: block;
            color: var(--c-text-on-primary);
            font: var(--f-normal);
            text-decoration: none;

        }
        li a:hover {
            font: var(--f-normal);
            color: #c3c8ce;
            text-decoration: none;
        }

        .menu-content {
            display: none;
            position: absolute;
            background-color: var(--c-primary);
            min-width: 160px;

            overflow: auto;
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
            z-index: 1;
        }
        .menu-content a {
            color: var(--c-text-on-primary);
            font: var(--f-normal);
            font-size: large;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            width:250px;

            z-index: 1;
        }
        .menu-content a:hover {
            background-color: var(--c-primary-light);
        }
        .menu {
            position: relative;
            float: left;
            z-index: 2;
            height:100%
        }
        .btn {
            background-color: transparent;
            color: var(--c-text-on-primary);
            border:none;
            padding: 18px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        body {
            background-color: var(--c-bg);
            color: var(--c-text-on-bg);
            font: var(--f-normal);
        }

        .show {display: block;}
        .hide {display: none;}
    </style>

    <!--Displays the side menu with all the pages-->
    <script>

        function openSideBar() {
            document.getElementById("sideMenu").classList.toggle("show");
        }
        window.onclick = function(event) {
            if (!event.target.matches('.btn')) {
                var sideMenu = document.getElementsByClassName("menu-content");
                var i;
                for (i = 0; i < sideMenu.length; i++) {
                    var openSideMenu = sideMenu[i];
                    if (openSideMenu.classList.contains('show')) {
                        openSideMenu.classList.remove('show');

                    }
                }
            }
        }

    </script>

</head>


    <div class="container-fluid " style="width:100%;position: relative;padding:0; z-index: 10">
        <ul>
            <form action="" method="post">
            <div class="menu" style="z-index=2">
                <button type="button" title="Side Menu" id="sideButton" class="btn fas fa-bars" onclick="openSideBar()"></button>
                <div id="sideMenu" class="menu-content">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="#mindful" >Mindful Minutes</a>
                    <a href="#step" >Step Tracker</a>
                    <a href="#sleep" >Sleep Tracker</a>
                    <a href="#weight" >Weight Tracker</a>
                    <a href="#height" >Height Tracker</a>
                    <a href="profile.php" >Profile</a>
                </div>
            </div>
            <li style="text-align: center">
                <a style="font: var(--f-brand); font-size: 35px;  padding:5px"  href="#">kalma</a>
            </li>
                <button type="submit" name="logout" style="float:right;" id="signOut" class="btn fas fa-sign-out-alt" title="Logout" value="">
            </form>
        </ul>
    </div>
<?php

//hides the menu buttons that aren't accessible on some of the pages
$filepath = strtok($_SERVER['REQUEST_URI'], '?');
if ($filepath == "/Kalma/webroot/site/public/loginAndSignup.php"){
    echo '<script>document.getElementById("signOut").classList.add("hide");
          document.getElementById("sideButton").classList.add("hide")</script>';
}
else if ($filepath == "/Kalma/webroot/site/public/errorPage.php"){
    echo '<script>document.getElementById("signOut").classList.add("hide");
          document.getElementById("sideButton").classList.add("hide")</script>';
}
else if($filepath == "/Kalma/webroot/site/public/logoutSuccess.php"){
    echo '<script>document.getElementById("signOut").classList.add("hide");
          document.getElementById("sideButton").classList.add("hide")</script>';
}
else if($filepath == "/Kalma/webroot/site/public/signupSuccess.php"){
    echo '<script>document.getElementById("signOut").classList.add("hide");
          document.getElementById("sideButton").classList.add("hide")</script>';
}
else if($filepath == "/Kalma/webroot/site/public/confirmationPage.php"){
    echo '<script>document.getElementById("signOut").classList.add("hide");
          document.getElementById("sideButton").classList.add("hide")</script>';
}
else{
    echo '<script>document.getElementById("signOut").classList.remove("hide");
          document.getElementById("sideButton").classList.remove("hide")</script>';
}

