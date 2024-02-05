<?php

use Pico\Data\Entity\Song;
use Pico\Request\PicoRequest;

require_once "inc/auth-with-login-form.php";

$song = new Song(null, $database);

$inputGet = new PicoRequest(INPUT_GET);
$delayStr = $inputGet->getDelay();
if($delayStr == null || empty($delayStr))
{
    $delay = 0;
}
else
{
    $delay = intval($delayStr);
}
$lyric = array('lyric' => '', 'start'=>0, 'duration'=>0, 'song_id'=>'');
if($inputGet->getSongId() != null)
{
    $song->findOneBySongId($inputGet->getSongId());
    $lyric['lyric'] = $song->getLyric();
    $lyric['duration'] = $song->getDuration() * 1000;
    $lyric['start'] = (time() * 1000) + $delay;
    $lyric['song_id'] = $song->getSongId();
}


require_once "inc/header.php";
?>

<div class="filter-container">
  <form action="" method="get">
  <div class="filter-group">
      <span>Song</span>
      <?php
      $sql = "select song.song_id, song.title, album.album_id, album.name as album_name
      from song 
      inner join(album) on(album.album_id = song.album_id)
      where song.active = true and album.active = true and album.as_draft = false
      order by album.sort_order asc, song.track_number asc
      ";
      $album_list = array();
      try
      {
      $res = $database->executeQuery($sql);
      $rows = $res->fetchAll(PDO::FETCH_ASSOC);
      foreach($rows as $row)
      {
        if(!isset($album_list[$row['album_id']]))
        {
            $album_list[$row['album_id']] = array();
        }
        $album_list[$row['album_id']][] = $row;
      }
      $arr1 = array();
      foreach($album_list as $albumItem)
      {
        $arr2 = array();
        if(!empty($albumItem))
        {
            $arr2[] = '<optgroup label="'.$albumItem[0]['album_name'].'">';
            foreach($albumItem as $songItem)
            {
                $arr2[] = '<option value="'.$songItem['song_id'].'">'.$songItem['title'].'</option>';
            }
            $arr2[] = '</optgroup>';
        }
        $arr1[] = implode("\r\n", $arr2);
      }
      
      }
      catch(Exception $e)
      {
        echo $e->getMessage();   
      }
      ?>
      <select class="form-control" name="song_id" id="song_id">
        <?php
        echo implode("\r\n", $arr1);
        
        ?>
      </select>
      
  </div>
  
  <input class="btn btn-primary open" type="submit" name="play" value="Play">
`  
  </form>
</div>

    <script src="karaoke-script.js"></script>
<style> 
    .teleprompter
    {
        position: relative;
        width: calc(100% + 40px);
        height: calc(100vh - 160px);
        background-color: white;
        overflow: hidden;
        margin: 0px -20px;
        white-space: nowrap;
        text-transform: uppercase;
    }
    @media screen and (min-width: 1200px) {
        .main .teleprompter{
            margin: 0;
            width: 100%;
        }
        
    }
    
    .teleprompter-container{
        position: relative;
        width: 100%;
    }
    .teleprompter-container > div{
        position: absolute;
        text-align: center;
        width: 100%;
        border-top: 1px solid #fafafa;
        padding-top: 5px;
        box-sizing: border-box;
    }
    .marked{
        background-color: #cdff43c4;
        color: #222222;
    }
</style>

<div class="teleprompter">
    <div class="teleprompter-container"></div>
</div>
<script>
    let karaoke = null;
    if(typeof data.lyric != 'undefined' && data.lyric != '')
    {
        karaoke = new Karaoke(data, '.teleprompter-container');      
        animate();
    }
    function animate()
    {
        karaoke.animate();
        requestAnimationFrame(animate);
    }
    </script>

<?php
require_once "inc/footer.php";
?>