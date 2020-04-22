<?php

if (isset($_GET['fingerprint'])) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['fingerprint'] = $_GET['fingerprint'];
    if (isset($_GET['redirect']) && $_GET['redirect'] !== '') {
        header('Location: ' . $_GET['redirect']);
    }
    else {
        header('Location: ./loginAndSignup');
    }
}

?>
<html lang="en">
<head>
    <title>Redirecting You... | Kalma</title>
</head>
<body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ClientJS/0.1.11/client.min.js"></script>
    <script type="text/javascript">
        const client = new ClientJS();
        window.location.replace(`?fingerprint=${client.getFingerprint()}<?= $_GET['redirect'] ? ('&redirect= ' . urlencode($_GET['redirect'])) : ''; ?>`);
    </script>
</body>
</html>