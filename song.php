<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Song;

require_once "inc/auth.php";
require_once "inc/header.php";

$pagination = new PicoPagination($cfg->getResultPerPage()); 
$subquery = new PicoDatabaseQueryBuilder($database);
$queryBuilder = new PicoDatabaseQueryBuilder($database);

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
  ->orderBy("song.time_create desc")
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
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?song_id=<?php echo $song->getSongId();?>"><?php echo $song->getTitle();?></a></td>
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

require_once "inc/footer.php";
?>