<?php
    session_start();
?>

<!doctype html>
<html lang='en'>
<head>
    <?php include_once "./components/global_head_inner.php"; ?>
    <link rel="stylesheet" href="assets/stylesheets/loginAndSignup.css">
</head>

<body>

    <?php include_once "./components/navbar_top.php"; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1"></div>

            <div class="col-lg-5">
                <h2> Login </h2>
                <div class="alert alert-danger alert-dismissible fade text-center <?= isset($_SESSION['login_message']) ? "show" : "hide"?>" role="alert">
                    <?= $_SESSION['login_message'] ?? ""; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form class="form-login" action="../controller/loginController.php" method="post">
                    <div class="form-group">
                        <label for="logEmail">Email Address:</label><br>
                        <input class="form-control" type="email" placeholder="Email" name="logEmail" id="logEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="logPassword">Password:</label><br>
                        <input class="form-control" type="password" placeholder="Password" name="logPassword" id="logPassword" required>
                    </div>
                    <input type="hidden" name="fingerprint" id="hiddenFingerprint">
                    <input class="btn btn-primary" type="submit" name="login" value="Submit">
                </form>
            </div>

            <div class="col-lg-5">
                <hr class="d-lg-none">
                <h2>Sign up</h2>
                <form class="form-signup"action="../controller/signUpController.php" method="post">
                    <div class="form-group">
                        <label for="email">Email Address:</label><br>
                        <input class="form-control" type="email" placeholder="Email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="firstName">First Name:</label><br>
                        <input class="form-control" type="text" placeholder="First Name" name="firstName" id="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name:</label><br>
                        <input class="form-control" type="text" placeholder="Last Name" name="lastName" id="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label><br>
                        <input class="form-control" type="password" placeholder="Password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth:</label><br>
                        <input class="form-control" type="date" name="dob" min="1900-01-01" placeholder="Date Of Birth" id="dob" required>
                    </div>
                    <input class="btn btn-primary" type="submit" name="signup" value="Submit">
                </form>
            </div>

            <div class="col-md-1"></div>
        </div>
    </div>
</body>

</html>