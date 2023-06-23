<?php

use Pico\Data\Entity\Song;
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;

require_once "inc/auth.php";
require_once "inc/header.php";
?>
<link rel="stylesheet" href="lib/css.css">
<script src="lib/dubbing.js"></script>
<script src="lib/ajax.js"></script>
<link rel="stylesheet" href="lib/icon.css">

<?php
$song_id = @$_GET['song_id'];
if($song_id != '')
{
?>
<div class="srt-editor editor1">
    <div class="row">
        <div class="col col-7">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Timeline</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Raw</button>
                </li>
            </ul>
            <div class="tab-content" id="srt-tab-content">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <!-- list begin -->
                    <div class="srt-list-wrapper">
                        <div class="srt-list-container">

                        </div>
                    </div>
                    <!-- list end -->

                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="srt-raw">
                        <textarea class="srt-text-raw" spellcheck="false"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-5">
            <div class="player">
                <div class="text-display-container">
                    <div class="text-display">
                        <div class="text-display-inner d-flex align-items-center justify-content-center"></div>
                    </div>
                </div>
                <div class="srt-zoom-control-wrapper">
                <input type="range" class="srt-zoom-control" min="0" max="8" step="1" list="input-markers">
                <datalist id="input-markers" style="--list-length: 9;">
                    <option value="0">0.125x</option><option value="1">0.25x</option><option value="2">0.5x</option><option value="3">0.75x</option><option value="4">1x</option><option value="5">1.25x</option><option value="6">1.5x</option><option value="7">1.75x</option><option value="8">2x</option>
                </datalist>
                </div>
                <div class="player-controller">
                    <button class="btn btn-dark button-play-master">Play</button>
                    <button class="btn btn-dark button-pause-master">Pause</button>
                    <button class="btn btn-dark button-scroll-master">Scroll</button>
                    <button class="btn btn-dark button-record-master">Record</button>
                    <button class="btn btn-dark button-stop-master">Stop</button>
                    <button class="btn btn-dark button-save-master">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- controller drag begin -->
    <div class="srt-map">
        <div class="srt-map-first-layer">

            <div class="srt-time-position">
                <div class="srt-time-position-inner">
                    <div class="srt-time-position-pointer" data-toggle="tooltip" data-placement="top" title="00:00:00"></div>
                </div>
            </div>

            <div class="srt-timestamp">
                <canvas class="srt-timeline-canvas"></canvas>
            </div>

            <div class="srt-edit-area">
                <div class="srt-waveform">
                    <canvas class="srt-timeline-canvas-edit" height="64" width="100%"></canvas>
                </div>
                <div class="srt-map-srt-container">
                </div>
            </div>
        </div>
    </div>
    <!-- controller drag end -->
</div>

<!-- Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">Delete Text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this one?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger delete">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php

try
{
    $song = new Song(array('song_id'=>@$_GET['song_id']), $database);
    $song->select();
    if($song != null)
    {
        $lyric = $song->getLyric();
        if(strlen(trim($lyric)) == 0)
        {
            $lyric = "{type here}";
        }
        if(stripos($lyric, "-->") === false)
        {
            $lyric = "00:00:00,000 --> 00:00:01,000\r\n".$lyric;
        }
?>
<script>
    let song_id = '<?php echo $song->getSongId(); ?>';
    let path = '<?php echo $cfg->getSongBaseUrl();?>/<?php echo $song->getFileName(); ?>';
    let jsonData = <?php echo json_encode(array('lyric'=>$lyric)); ?>;
    let rawData = jsonData.lyric;
</script>
<script>
    let srt;
    
    $(document).ready(function(evt)
    {      
        srt = new Dubbing('.editor1', rawData, path);
        

        document.querySelector('.button-play-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.play();
        });

        document.querySelector('.button-scroll-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.toggleScroll();
        });

        document.querySelector('.button-pause-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.pause(true);
        });

        document.querySelector('.button-save-master').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
        });
        document.querySelector('.button-record-master').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();       
            audioChunks = [];
            rec.start();
        });

        document.querySelector('.button-stop-master').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();          
            rec.stop();
        });

        document.onkeydown = function(e) {
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                e.stopPropagation();
                saveAudio();
            }
        };
    });
    
    function saveAudio()
    {

    }


    let rec;
    let audioChunks;
    navigator.mediaDevices.getUserMedia({audio:true})
      .then(stream => {handlerFunction(stream)})


            function handlerFunction(stream) {
            rec = new MediaRecorder(stream);
            rec.ondataavailable = e => {
              audioChunks.push(e.data);
              if (rec.state == "inactive"){
                let blob = new Blob(audioChunks,{type:'audio/mpeg-3'});
                recordedAudio.src = URL.createObjectURL(blob);
                recordedAudio.controls=true;
                recordedAudio.autoplay=true;
                sendData(blob)
              }
            }
          }
            function sendData(data) {

            }


       
    
    
</script>

<audio src="" id="recordedAudio"></audio>

<?php
}
}
catch(Exception $e)
{
    // do nothing
}
}
else
{
    $pagination = new PicoPagination($cfg->getResultPerPage()); 
    $subquery = new PicoDatabaseQueryBuilder($database);
    $queryBuilder = new PicoDatabaseQueryBuilder($database);

    $order = $pagination->getOrder(array(
        'time_create'=>'song.time_create',
        'title'=>'song.title',
        'duration'=>'song.duration'
      ), array(
        'time_create',
        'title',
        'duration'
      ), 
      'time_create'
    );

    $sql = $queryBuilder->newQuery()
    ->select("song.*, 
        (select artist.name from artist where artist.artist_id = song.artist_vocal) as artist_vocal_name,
        (select artist.name from artist where artist.artist_id = song.artist_composer) as artist_composer_name,
        (select artist.name from artist where artist.artist_id = song.artist_arranger) as artist_arranger_name,
        (select genre.name from genre where genre.genre_id = song.genre_id) as genre_name,
        (select album.name from album where album.album_id = album.album_id) as album_name
        ")
    ->from("song")
    ->where("song.active = ? ", true)
    ->orderBy($order)
    ->limit($pagination->getLimit())
    ->offset($pagination->getOffset());
    try
    {
    $data = $database->fetchAll($sql, PDO::FETCH_OBJ);
    if($data != null && !empty($data))
    {
    ?>

    <table class="table">
    <thead>
        <tr>
        <th scope="col" width="20">#</th>
        <th scope="col">Title</th>
        <th scope="col">Artist</th>
        <th scope="col">Album</th>
        <th scope="col">Duration</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = $pagination->getOffset();
        foreach($data as $row)
        {
        $no++;
        $song = new Song($row);
        ?>
        <tr>
        <th class="text-right" scope="row"><?php echo $no;?></th>
        <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?song_id=<?php echo $song->getSongId();?>"><?php echo $song->getTitle();?></a></td>
        <td><?php echo $song->getArtistVocalName();?></td>
        <td><?php echo $song->getAlbumName();?></td>
        <td><?php echo $song->getDuration();?></td>
        </tr>
        <?php
        }
        ?>
        
    </tbody>
    </table>

    <?php
    }
    }
    catch(Exception $e)
    {
    ?>
    <div class="alert alert-warning">
    <?php
    
    ?>
    </div>
    <?php
    }
}
require_once "inc/footer.php";
?>