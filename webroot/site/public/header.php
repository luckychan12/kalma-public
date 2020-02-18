<?php
?>

<!doctype html>
<html lang='en'>
<head>
    <!-- Meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel="stylesheet" href="../assets/kalma-theme.css">
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
            padding-right: 50px;
            
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

        .show {display: block;}
    </style>

    //Displays the side menu with all the pages
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
            <div class="menu" style="z-index=2">
                <button class="btn fas fa-bars"  onclick="openSideBar()"></button>
                <div id="sideMenu" class="menu-content">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="#mindful">Mindful Minutes</a>
                    <a href="#step">Step Tracker</a>
                    <a href="#sleep">Sleep Tracker</a>
                    <a href="#weight">Weight Tracker</a>
                    <a href="#height">Height Tracker</a>
                    <a href="#profile">Profile</a>
                </div>
            </div>
            <li style="text-align: center">
                <a style="font: var(--f-brand); font-size: 35px;  margin:0" href="#">kalma</a>
            </li>

        </ul>
    </div>


