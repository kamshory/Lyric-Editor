<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Song;
use Pico\Exceptions\NoRecordFoundException;
use Pico\Request\PicoRequest;

require_once "inc/auth.php";
require_once "inc/header.php";

$inputGet = new PicoRequest(INPUT_GET);

if($inputGet->equalsAction(PicoRequest::ACTION_DETAIL) && $inputGet->getSongId() != null)
{
  $queryBuilder = new PicoDatabaseQueryBuilder($database);
  $sql = $queryBuilder->newQuery()
  ->select("song.*, 
    (select artist.name from artist where artist.artist_id = song.artist_composer) as artist_composer_name,
    (select artist.name from artist where artist.artist_id = song.artist_arranger) as artist_arranger_name,
    artist.name as artist_vocal_name,
    genre.name as genre_name,
    album.name as album_name
    ")
  ->from("song")
  ->leftJoin("artist")->on("artist.artist_id = song.artist_vocal")
  ->leftJoin("genre")->on("genre.genre_id = song.genre_id")
  ->leftJoin("album")->on("album.album_id = song.album_id")
  ->where("song.song_id = ? ", $inputGet->getSongId());
  try
  {
    $row = $database->fetch($sql, PDO::FETCH_OBJ);
    if(!empty($row))
    {
      $song = new Song($row);
      ?>
      <table class="table table-responsive">
        <tbody>
          <tr>
            <td>Song ID</td>
            <td><?php echo $song->getSongId();?></td>
          </tr>
          <tr>
            <td>Title</td>
            <td><?php echo $song->getTitle();?></td>
          </tr>
          <tr>
            <td>Duration</td>
            <td><?php echo $song->getDuration();?></td>
          </tr>
          <tr>
            <td>Genre</td>
            <td><?php echo $song->getGenreName();?></td>
          </tr>
          <tr>
            <td>Album</td>
            <td><?php echo $song->getAlbumName();?></td>
          </tr>
          <tr>
            <td>Vocal</td>
            <td><?php echo $song->getArtistVocalName();?></td>
          </tr>
          <tr>
            <td>Composer</td>
            <td><?php echo $song->getArtistComposerName();?></td>
          </tr>
          <tr>
            <td>Arranger</td>
            <td><?php echo $song->getArtistArrangerName();?></td>
          </tr>
        </tbody>
      </table>
      
      <?php
    }
    else
    {
      ?>
      <div class="alert alert-warning"><?php echo $e->getMessage();?></div>
      <?php
    }
  }
  catch(Exception $e)
  {

  }
}
else
{
$pagination = new PicoPagination($cfg->getResultPerPage()); 
$subquery = new PicoDatabaseQueryBuilder($database);
$queryBuilder = new PicoDatabaseQueryBuilder($database);

$order = $pagination->createOrder(array(
  'time_create'=>'song.time_create',
  'title'=>'song.title',
  'duration'=>'song.duration',
  'artist_vocal'=>'artist.name',
  'genre'=>'genre.name',
  'album'=>'album.name'
), array(
  'time_create',
  'title',
  'duration',
  'artist_vocal',
  'genre',
  'album'
), 
'time_create'
);

$sql = $queryBuilder->newQuery()
->select("song.*, 
  (select artist.name from artist where artist.artist_id = song.artist_composer) as artist_composer_name,
  (select artist.name from artist where artist.artist_id = song.artist_arranger) as artist_arranger_name,
  artist.name as artist_vocal_name,
  genre.name as genre_name,
  album.name as album_name
  ")
->from("song")
->leftJoin("artist")->on("artist.artist_id = song.artist_vocal")
->leftJoin("genre")->on("genre.genre_id = song.genre_id")
->leftJoin("album")->on("album.album_id = song.album_id")
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
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&song_id=".$song->getSongId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&song_id=".$song->getSongId();
    ?>
    <tr>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>"><?php echo $song->getTitle();?></a></td>
      <td><?php echo $song->getArtisName();?></td>
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
   echo $e->getMessage();
   ?>
 </div>
 <?php
}
}

require_once "inc/footer.php";
?>