<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karaoke</title>
    <script>
        let url = 'ws://localhost:8888/';
        if (typeof wsReconnectInterval == "undefined") {
            var wsReconnectInterval = 10000;
        }

        function connect() {
            let ws = new WebSocket(url);
            ws.onopen = function () {
                // subscribe to some channels
            };

            ws.onmessage = function (e) {
                processIncommingData(e.data);
            };

            ws.onclose = function (e) {
                console.log("Socket is closed. Reconnect will be attempted in " + wsReconnectInterval + " millisecond.", e.reason);
                setTimeout(function () {
                    // create new connection onclose
                    connect();
                }, wsReconnectInterval);
            };

            ws.onerror = function (err) {
                console.error("Socket encountered error: ", err.message, "Closing socket");
                ws.close();
            };
        }
        if (typeof wsEndpoint != "undefined") {
            connect();
        }

        function processIncommingData(message) {
            console.log(message);
        }

        function getUrl(originalUrl, tag)
        {
            // construct URL here
            return originalUrl;
        }

        

        window.onload = function()
        {
            console.log('connecting');
            connect();
        }

    </script>
</head>
<body>
    
</body>
</html>