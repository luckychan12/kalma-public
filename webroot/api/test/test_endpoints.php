<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    use Kalma\Api\Core\Auth;

    $jwt = Auth::generateJWT(10, 1234);
    sleep(1); // Wait for auth key to become valid
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Test</title>

    <link rel="stylesheet"
          href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.18.0/build/styles/default.min.css">
    <script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.18.0/build/highlight.min.js"></script>

    <style>
        #json_wrapper {
            border: solid 1px darkred;
            border-radius: 5px;
            overflow: hidden;
        }

        #text {
            border: solid 1px black;
            padding: 5px;
        }
    </style>
</head>
<body>
    Text: <br />
    <pre id="text">

    </pre>
    <br />
    JSON: <br />
    <pre id="json_wrapper"><code class="json" id="json"></code></pre>

    <script src="https://code.jquery.com/jquery-3.4.1.js"
            integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
            crossorigin="anonymous"></script>

    <script>
        $(function(){
            testLogin();
        });

        function logResponse(res) {
            console.log(res);
            $("#text").append("<p>" + res.responseText + "</p>");
            if (res.hasOwnProperty("responseJSON")) {
                let $json = $("#json");
                $json.append("\n\n" + JSON.stringify(res.responseJSON, null, 4));
                hljs.highlightBlock($json[0]);
            }
        }

        function testLogin() {
            $.ajax({
                method: "POST",
                url: "http://localhost/api/user/login",
                crossDomain: true,
                xhrFields: {
                    withCredentials: false
                },
                accepts: {
                    json: "application/json"
                },
                dataType: "json",
                data: {
                    "email_address": "dummy@example.com",
                    "password": "Password123!",
                    "client_fingerprint": 123456789
                },
                complete: function(res) {
                    logResponse(res);
                    if (res.hasOwnProperty('responseJSON')) {
                        let data = res.responseJSON;
                        if (data.success)
                            testRead(data.jwt, data.links.account);
                    }
                },
            });
        }

        function testRead(jwt, readLink) {
            $.ajax({
                method: "GET",
                url: "http://localhost" + readLink,
                crossDomain: true,
                xhrFields: {
                    withCredentials: false
                },
                accepts: {
                    json: "application/json"
                },
                dataType: "json",
                headers : {
                    'Authorization' : jwt
                },
                complete: function(res) {
                    logResponse(res);
                    if (res.hasOwnProperty("responseJSON")) {
                        let data = res.responseJSON;
                        if (data.success)
                            testLogout(jwt, data.links.logout);
                    }
                },
            });
        }

        function testLogout(jwt, logoutLink) {
            $.ajax({
                method: "POST",
                url: "http://localhost" + logoutLink,
                crossDomain: true,
                xhrFields: {
                    withCredentials: false
                },
                accepts: {
                    json: "application/json"
                },
                dataType: "json",
                headers : {
                    'Authorization' : jwt
                },
                complete: function(res) {
                    logResponse(res);
                },
            });
        }

    </script>

</body>
</html>