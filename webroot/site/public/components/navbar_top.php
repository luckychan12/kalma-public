<?php require_once __DIR__ . '/fingerprinter.php'; ?>

<nav class="navbar navbar-dark navbar-expand-lg navbar-top">
    <a class="navbar-brand" href="#">Kalma</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTop">
        <ul class="navbar-nav <?= isset($_SESSION['links']) ? "show" : "hide" ?>">
            <li class="nav-item">
                <a class="nav-link" href="./dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    My Data
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <a class="dropdown-item" href="./sleep.php">Sleep</a>
                    <a class="dropdown-item" href="./calm.php">Mindful Minutes</a>
                    <a class="dropdown-item" href="#">Daily Steps</a>
                    <a class="dropdown-item" href="#">Weight</a>
                    <a class="dropdown-item" href="#">Height</a>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <a class="nav-link <?= isset($_SESSION['account_link']) ? "show" : "hide" ?>" href="./profile.php"><i class="fa fa-fw fa-user"></i><span>Profile</span></a>
            <a class="nav-link <?= isset($_SESSION['logout_link']) ? "show" : "hide" ?>" href="?logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            <a class="nav-link <?= !isset($_SESSION['account_link']) ? "show" : "hide" ?>" href="./loginAndSignup.php"><i class="fas fa-sign-in-alt"></i><span>Login/Signup</span></a>
        </ul>
    </div>
</nav>