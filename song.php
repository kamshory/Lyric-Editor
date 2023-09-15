<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Database\PicoSortable;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\Artist;
use Pico\Data\Entity\EntitySong;
use Pico\Data\Entity\Genre;
use Pico\Data\Entity\Song;
use Pico\Data\Tools\SelectOption;
use Pico\Database\PicoPagable;
use Pico\Database\PicoPage;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Utility\SpecificationUtil;

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
    ?>
    <div class="filter-container">
    <form action="" method="get">
    <div class="filter-group">
        <span>Genre</span>
        <select class="form-control" name="genre_id" id="genre_id">
            <option value="">- All -</option>
            <?php echo new SelectOption(new Genre(null, $database), array('value'=>'genreId', 'label'=>'name'), $inputGet->getGenreId()); ?>
        </select>
    </div>
    <div class="filter-group">
        <span>Album</span>
        <select class="form-control" name="album_id" id="album_id">
            <option value="">- All -</option>
            <?php echo new SelectOption(new Album(null, $database), array('value'=>'albumId', 'label'=>'name'), $inputGet->getAlbumId()); ?>
        </select>
    </div>
    <div class="filter-group">
        <span>Artist Vocal</span>
        <select class="form-control" name="artist_vocal_id" id="artist_vocal_id">
            <option value="">- All -</option>
            <?php echo new SelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getArtistVocalId()); ?>
        </select>
    </div>
    <div class="filter-group">
        <span>Title</span>
        <input class="form-control" type="text" name="title" id="title" autocomplete="off" value="<?php echo $inputGet->getTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
    </div>
    
    <input class="btn btn-success" type="submit" value="Show">
    
    </form>
</div>
<?php
$orderMap = array(
    'title'=>'title', 
    'albumId'=>'albumId', 
    'album'=>'albumId', 
    'genreId'=>'genreId', 
    'genre'=>'genreId',
    'artistVocalId'=>'artistVocalId',
    'artistVocal'=>'artistVocalId',
    'artistComposerId'=>'artistComposerId',
    'artistComposer'=>'artistComposerId'
);
$orderDefault = 'title';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createMidiSpecification($inputGet);
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $orderDefault), $pagination->getOrderType());
$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new EntitySong(null, $database);
$rowData = $songEntity->findAll($spesification, $pagable, $sortable, true);

$result = $rowData->getResult();

?>

<script>
    $(document).ready(function(e){
        let pg = new Pagination('.pagination', '.page-selector', 'data-page-number', 'page');
        pg.init();
        $(document).on('change', 'select', function(e2){
            $(this).closest('form').submit();
        });
    });
</script>

<?php
if(!empty($result))
{
?>
<div class="pagination">
    <div class="pagination-number">
    <?php
    foreach($rowData->getPagination() as $pg)
    {
        ?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
    }
    ?>
    </div>
</div>
<table class="table">
    <thead>
        <tr>
        <th scope="col" width="20"><i class="ti ti-edit"></i></th>
        <th scope="col" width="20">#</th>
        <th scope="col">Title</th>
        <th scope="col">Album</th>
        <th scope="col">Genre</th>
        <th scope="col">Vocalist</th>
        <th scope="col">Composer</th>
        <th scope="col">Duration</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = $pagination->getOffset();
        foreach($result as $song)
        {
        $no++;
        $songId = $song->getSongId();
        $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&song_id=".$songId;
        $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&song_id=".$songId;
        ?>
        <tr>
        <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
        <th class="text-right" scope="row"><?php echo $no;?></th>
        <td><a href="<?php echo $linkDetail;?>"><?php echo $song->getTitle();?></a></td>
        <td><?php echo $song->hasValueAlbum() ? $song->getAlbum()->getName() : "";?></td>
        <td><?php echo $song->hasValueGenre() ? $song->getGenre()->getName() : "";?></td>
        <td><?php echo $song->hasValueArtistVocal() ? $song->getArtistVocal()->getName() : "";?></td>
        <td><?php echo $song->hasValueArtistComposer() ? $song->getArtistComposer()->getName() : "";?></td>
        <td><?php echo $song->getDuration();?></td>
        </tr>
        <?php
        }
        ?>
        
    </tbody>
    </table>


    <div class="pagination">
    <div class="pagination-number">
    <?php
    foreach($rowData->getPagination() as $pg)
    {
        ?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
    }
    ?>
    </div>
</div>


<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/song-update-dialog.php"></div>

<script>
  let updateSongModal;
  
  $(document).ready(function(e){
    
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let songId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-update-data');
      dialogSelector.load(dialogSelector.attr('data-url')+'?song_id='+songId, function(data){
        let updateSongModalElem = document.querySelector('#updateSongDialog');
        updateSongModal = new bootstrap.Modal(updateSongModalElem, {
          keyboard: false
        });
        updateSongModal.show();
      })
    });

    $(document).on('click', '.save-edit-song', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/song-update.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          updateSongModal.hide();
          let formData = getFormData(dataSet);
          let dataId = formData.song_id;
          let name = formData.name;
          let active = $('[name="active"]')[0].checked;
          $('[data-id="'+dataId+'"] .text-data.text-data-name').text(name);
          $('[data-id="'+dataId+'"] .text-data.text-data-active').text(active?'Yes':'No');
        }
      })
    });
  });
  
  
  
</script>
<?php
}
}
require_once "inc/footer.php";
?>