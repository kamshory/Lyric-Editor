<?php

use Pico\Data\Entity\Song;
use Pico\Request\PicoRequest;

require_once "inc/auth-with-login-form.php";

$song = new Song(null, $database);

$inputGet = new PicoRequest(INPUT_GET);
$lyric = array('lyric' => '', 'start'=>0, 'duration'=>0);
if($inputGet->getSongId() != null)
{
    $song->findOneBySongId($inputGet->getSongId());
    $lyric['lyric'] = $song->getLyric();
    $lyric['duration'] = $song->getDuration() * 1000;
    $lyric['start'] = time() * 1000;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karaoke</title>
    <script>
        let url = 'ws://localhost:8889/';
        if (typeof wsReconnectInterval == "undefined") {
            var wsReconnectInterval = 10000;
        }

        function connect() {
            let ws = new WebSocket(url);
            ws.onopen = function () {
                // subscribe to some channels
                console.log('Connected');
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
    let data = <?php echo json_encode($lyric);?>;

    </script>
    <script src="kar.js"></script>
    
</head>
<body>

<div class="all">
    <div id="container"></div>
</div>

<style>
    body{
        margin: 0;
        padding: 0;
        position: relative;
        height: 100vh;
    }
    .all
    {
        position: relative;
        width: 100%;
        height: 100%;
    }
    #container{
        position: relative;
        width: 100%;
    }
    #container > div{
        position: absolute;
        text-align: center;
        width: 100%;
        border-top: 1px solid #EEEEEE;
        padding-top: 5px;
        box-sizing: border-box;
    }
    .marked{
        background-color: yellow;
    }
</style>

<script>
    let karaoke = null;
    if(typeof data.lyric != 'undefined' && data.lyric != '')
    {
        karaoke = new Karaoke(data);
        
        animate();
    }
    function animate()
    {
        karaoke.animate();
        requestAnimationFrame(animate);
    }
    </script>
</body>
</html>