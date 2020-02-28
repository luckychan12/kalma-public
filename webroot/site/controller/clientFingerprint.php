<body>
<?php
/*
 * gets the client fingerprint and sends it to the required page
 * depends on the option you select
 * refresh or logout
 */
    function getFingerprint($job)
    {
        echo '<script>getFingerprint("'.$job.'")</script>';
    }


?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ClientJS/0.1.11/client.min.js"></script>
<script>
    function getFingerprint(job){
        let client = new ClientJS();
        let fingerprint = client.getFingerprint();
        if ( job === "refresh"){
            window.location.href = "refreshCheck.php?clientFingerprint=" + fingerprint;
        }
        else if ( job === "logout"){
            window.location.href = "../controller/logoutController.php?clientFingerprint=" + fingerprint;
        }
    }
</script>
</body>

