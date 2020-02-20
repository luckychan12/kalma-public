<?php

?>


<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous">
</script>
<script>
    function request(inPassword,inEmail){
        var details = {"email_address":inEmail,"password":inPassword,"client_fingerprint":123456789};
        $.ajax({
            method: "POST",
            url: "http://localhost/api/user/login",
            dataType: "json",
            data: details,
            complete: function(result){
                if (result.hasOwnProperty('responseJSON')) {
                    let res = result.responseJSON;

                if(res.success)
                    return getData(res.jwt, res.links.account);
                else
                    return -1;
                }
            }

        });
    }
    function getData(jwt,account){
        $.ajax({
            method: "GET",
            url: "http://localhost" + account,
            headers : {'Authorization' : jwt},
            dataType: "json",
            complete: function(result) {
                if (result.hasOwnProperty('responseJSON')) {
                    let res = result.responseJSON;
                    if (res.success)
                        location.href ='../public/dashboard.php';
                    else
                        location.href = '../public/loginAndSignup.php';
                    return res.user;
                }
            }
        });


    }

</script>


