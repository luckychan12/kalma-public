<nav class="navbar navbar-dark navbar-expand-lg navbar-top">
    <a class="navbar-brand" href="#">Kalma</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTop">
        <ul class="navbar-nav <?= isset($_SESSION['auth']) ? "show" : "hide" ?>">
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
                    <a class="dropdown-item">Daily Steps <small>(Coming Soon!)</small></a>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <a class="nav-link <?= isset($_SESSION['auth']) ? "show" : "hide" ?>" href="./profile.php"><i class="fa fa-fw fa-user"></i><span>Profile</span></a>
            <a class="nav-link <?= isset($_SESSION['auth']) ? "show" : "hide" ?>" href="./logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            <a class="nav-link <?= isset($_SESSION['auth']) ? "hide" : "show" ?>" href="./login-and-signup.php"><i class="fas fa-sign-in-alt"></i><span>Login/Signup</span></a>
        </ul>
    </div>
</nav>