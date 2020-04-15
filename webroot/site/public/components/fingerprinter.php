<script src="https://cdnjs.cloudflare.com/ajax/libs/ClientJS/0.1.11/client.min.js"></script>
<script>
    function getFingerprint(job){
        let client = new ClientJS();
        let fingerprint = client.getFingerprint();

        if(!job) return fingerprint;

        if (job === "refresh"){
            window.location.href = "refreshCheck.php?clientFingerprint=" + fingerprint;
        }
        else if ( job === "logout"){
            window.location.href = "../controller/logoutController.php?clientFingerprint=" + fingerprint;
        }
    }

    $(function(){
        let client = new ClientJS();
        let $hiddenFingerprint = $("#hiddenFingerprint");
        if ($hiddenFingerprint.length)
            $hiddenFingerprint.val(client.getFingerprint());
    })
</script>

<?php
if(isset($_GET['logout'])) {
    echo "<script>getFingerprint('logout')</script>";
}
?>