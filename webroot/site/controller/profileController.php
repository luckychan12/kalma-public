<?php
include_once '../api_tasks/apiConnect.php';
$api = new ApiConnect();
$data = $api->getData($_SESSION['account_link']);
if (isset($data->error)){
    echo'<script>location.href = "../public/errorPage.php" </script>';
}
?>
<form>
    <label for="firstName">First Name: </label> <br><input type="text" style="padding:5px;width: 350px; height:33px" id="firstName" name="firstName" value="<?php echo $data->user->first_name?>" readonly>
    <br>
    <label for="lastName">Last Name: </label> <br><input type="text" style="padding:5px;width: 350px; height:33px" id="lastName" name="lastName" value="<?php echo $data->user->last_name?>" readonly>
    <br>
    <label for="dob">Date Of Birth: </label> <br><input type="date" style="padding:5px;width: 350px; height:33px" id="dob" name="dob" value="<?php echo $data->user->date_of_birth?>" readonly>
    <br>
    <label for="email">Email: </label> <br><input type="email" style="padding:5px;width: 350px; height:33px" id="email" name="email" value="<?php echo $data->user->email_address?>" readonly>
</form>



