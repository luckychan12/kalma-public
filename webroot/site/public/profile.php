<?php include_once '../controller/profileController.php'?>

<!doctype html>
<html lang='en'>

<head>
    <?php include_once './components/global_head_inner.php'?>
</head>

<body>

    <?php include_once './components/navbar_top.php' ?>

    <div class="container">
        <div class="row" style="margin-top: 20px">
            <div class="offset-md-2">
                <h1>Profile</h1>
                <form>
                    <label for="firstName">First Name: </label> <br>
                    <input type="text" style="padding:5px;width: 350px; height:33px" id="firstName" name="firstName" value="<?= $data->user->first_name ?? "Unknown" ?>" readonly>
                    <br>
                    <label for="lastName">Last Name: </label> <br>
                    <input type="text" style="padding:5px;width: 350px; height:33px" id="lastName" name="lastName" value="<?= $data->user->last_name ?? "Unknown" ?>" readonly>
                    <br>
                    <label for="dob">Date Of Birth: </label> <br>
                    <input type="date" style="padding:5px;width: 350px; height:33px" id="dob" name="dob" value="<?= $data->user->date_of_birth ?? "00/00/0000" ?>" readonly>
                    <br>
                    <label for="email">Email: </label> <br>
                    <input type="email" style="padding:5px;width: 350px; height:33px" id="email" name="email" value="<?= $data->user->email_address ?? "Unknown" ?>" readonly>
                </form>
            </div>
        </div>
    </div>
</body>

