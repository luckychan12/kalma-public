<?php
include_once '../api_tasks/apiConnect.php';
?>

<script>
    window.onload = function() {
       let dat =  getData(sessionStorage.getItem('jwt'), "/api/user/" + sessionStorage.getItem('id') + "/account");
    }
    function handleDashboard(result){
        document.getElementById('welcome').append(result.user.first_name);
    }

</script>


