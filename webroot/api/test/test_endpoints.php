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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ClientJS/0.1.11/client.min.js"></script>

    <script>

        let client_fingerprint;

        $(function(){

            let client = new ClientJS();
            let browser = client.getBrowser() + " " + client.getBrowserVersion();
            let os = client.getOS() + " " + client.getOSVersion();
            let device = client.getDeviceType();
            client_fingerprint = `${client.getFingerprint()}`;

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
                data: JSON.stringify({
                    "email_address": "dummy@example.com",
                    "password": "Password123!",
                    "client_fingerprint": client_fingerprint
                }),
                complete: function(res) {
                    logResponse(res);
                    if (res.hasOwnProperty('responseJSON')) {
                        let data = res.responseJSON;
                        if (!data.error)
                            testRead(data.access_token, data.links.account);
                    }
                },
            });
        }

        function testRead(access_token, readLink) {
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
                    'Authorization' : `Bearer ${access_token}`
                },
                complete: function(res) {
                    logResponse(res);
                    if (res.hasOwnProperty("responseJSON")) {
                        let data = res.responseJSON;
                        if (!data.error)
                            testLogout(access_token, data.links.logout);
                    }
                },
            });
        }

        function testLogout(access_token, logoutLink) {
            setTimeout(function(){
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
                    data : JSON.stringify({"client_fingerprint" : client_fingerprint}),
                    headers : {
                        'Authorization' : `Bearer ${access_token}`
                    },
                    complete: function(res) {
                        logResponse(res);
                    },
                });
            }, 100);
        }

    </script>

</body>
</html>