<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Midi;

require_once "inc/auth.php";
require_once "inc/header.php";

$pagination = new PicoPagination($cfg->getResultPerPage()); 
$subquery = new PicoDatabaseQueryBuilder($database);
$queryBuilder = new PicoDatabaseQueryBuilder($database);

$order = $pagination->createOrder(array(
  'time_create'=>'midi.time_create',
  'title'=>'midi.title',
  'duration'=>'midi.duration',
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
->select("midi.*, 
  (select artist.name from artist where artist.artist_id = midi.artist_composer) as artist_composer_name,
  (select artist.name from artist where artist.artist_id = midi.artist_arranger) as artist_arranger_name,
  artist.name as artist_vocal_name,
  genre.name as genre_name,
  album.name as album_name
  ")
->from("midi")
->leftJoin("artist")->on("artist.artist_id = midi.artist_vocal")
->leftJoin("genre")->on("genre.genre_id = midi.genre_id")
->leftJoin("album")->on("album.album_id = midi.album_id")
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
      $midi = new Midi($row);
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&midi_id=".$midi->getMidiId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&midi_id=".$midi->getMidiId();
    ?>
    <tr>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>"><?php echo $midi->getTitle();?></a></td>
      <td><?php echo $midi->getArtisName();?></td>
      <td><?php echo $midi->getAlbumName();?></td>
      <td><?php echo $midi->getDuration();?></td>
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