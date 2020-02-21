<?php
?>
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous">
</script>

<?php
//Sends password to the api and checks it and saves a jwt
function requestLogin($inPassword,$inEmail,$inClientFingerprint){
    ?>
    <script>

        var details = {"email_address":"<?php echo $inEmail ?>","password":"<?php echo $inPassword ?>","client_fingerprint":"<?php echo $inClientFingerprint ?>"};
        $.ajax({
            method: "POST",
            url: "http://localhost/api/user/login",
            dataType: "json",
            data: details,
            complete: function(result){
                if (result.hasOwnProperty('responseJSON')) {
                    let res = result.responseJSON;
                    if(res.success) {
                        sessionStorage.setItem('jwt', res.jwt);
                        sessionStorage.setItem('id', res.user_id);
                        location.href ="../public/dashboard.php";
                    }


                }
            }
        });
    </script>
<?php
    }
//Retrieves data from the api

    ?>


<script>
    function getData(jwt,route){
        $.ajax({
            method: "GET",
            url: "http://localhost" + route,
            headers : {'Authorization' : jwt},
            dataType: "json",
            success: function(result) {
                    handleDashboard(result);
                }

        });
    }

</script>

<script>
    function requestLogout(jwt, route){
        $.ajax({
            method:"POST",
            url: "http://localhost" + route,
            headers : {'Authorization' : jwt},
            dataType: "json",
            complete: function(result){
                signout(result);
            }
        })
    }
</script>