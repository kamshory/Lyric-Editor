<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Database\PicoSortable;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\Artist;
use Pico\Data\Entity\EntityReference;
use Pico\Data\Entity\Genre;
use Pico\Data\Entity\Reference;
use Pico\Data\Tools\SelectOption;
use Pico\Database\PicoPagable;
use Pico\Database\PicoPage;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Utility\SpecificationUtil;

require_once "inc/auth-with-login-form.php";
require_once "inc/header.php";

$inputGet = new PicoRequest(INPUT_GET);
if($inputGet->equalsAction(PicoRequest::ACTION_DETAIL) && $inputGet->getReferenceId() != null)
{
  $queryBuilder = new PicoDatabaseQueryBuilder($database);
  $sql = $queryBuilder->newQuery()
  ->select("reference.*, 
    (select artist.name from artist where artist.artist_id = reference.artist_composer) as artist_composer_name,
    (select artist.name from artist where artist.artist_id = reference.artist_arranger) as artist_arranger_name,
    artist.name as artist_vocal_name,
    genre.name as genre_name,
    album.name as album_name
    ")
  ->from("reference")
  ->leftJoin("artist")->on("artist.artist_id = reference.artist_vocal")
  ->leftJoin("genre")->on("genre.genre_id = reference.genre_id")
  ->leftJoin("album")->on("album.album_id = reference.album_id")
  ->where("reference.reference_id = ? ", $inputGet->getReferenceId());
  try
  {
    $row = $database->fetch($sql, PDO::FETCH_OBJ);
    if(!empty($row))
    {
      $reference = new Reference($row);
      ?>
      <table class="table table-responsive">
        <tbody>
          <tr>
            <td>Reference ID</td>
            <td><?php echo $reference->getReferenceId();?></td>
          </tr>
          <tr>
            <td>Title</td>
            <td><?php echo $reference->getTitle();?></td>
          </tr>
          <tr>
            <td>Duration</td>
            <td><?php echo $reference->getDuration();?></td>
          </tr>
          <tr>
            <td>Genre</td>
            <td><?php echo $reference->getGenreName();?></td>
          </tr>
          <tr>
            <td>Album</td>
            <td><?php echo $reference->getAlbumName();?></td>
          </tr>
          <tr>
            <td>Vocal</td>
            <td><?php echo $reference->getArtistVocalName();?></td>
          </tr>
          <tr>
            <td>Composer</td>
            <td><?php echo $reference->getArtistComposerName();?></td>
          </tr>
          <tr>
            <td>Arranger</td>
            <td><?php echo $reference->getArtistArrangerName();?></td>
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
        <span>Title</span>
        <input class="form-control" type="text" name="title" id="title" autocomplete="off" value="<?php echo $inputGet->getTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
    </div>
    <div class="filter-group">
        <span>Artist</span>
        <select class="form-control" name="artist_vocal_id" id="artist_vocal_id">
            <option value="">- All -</option>
            <?php echo new SelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getArtistId()); ?>
        </select>
    </div>
    <div class="filter-group">
        <span>Album</span>
        <input class="form-control" type="text" name="album" id="album" autocomplete="off" value="<?php echo $inputGet->getAlbum(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
    </div>
    <div class="filter-group">
        <span>Year</span>
        <input class="form-control" type="number" name="year" id="year" autocomplete="off" value="<?php echo $inputGet->getYear(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
    </div>

    <div class="filter-group">
        <span>Complete</span>
        <select class="form-control" name="active" id="active">
            <option value="">- All -</option>
            <option value="1"<?php echo $inputGet->getLyricComplete() == '1'?' selected':'';?>>Yes</option>
            <option value="0"<?php echo $inputGet->getLyricComplete() == '0'?' selected':'';?>>No</option>
        </select>
    </div>

    <input class="btn btn-success" type="submit" value="Show">
    <input class="btn btn-primary add-data" type="button" value="Add">
    
    </form>
</div>
<?php
$orderMap = array(
    'title'=>'title', 
    'album'=>'albumId', 
    'genreId'=>'genreId', 
    'genre'=>'genreId',
    'artistId'=>'artistId'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createReferenceSpecification($inputGet);;
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$referenceEntity = new EntityReference(null, $database);
$rowData = $referenceEntity->findAll($spesification, $pagable, $sortable, true);

$result = $rowData->getResult();

?>

<script>
    $(document).ready(function(e){
        let pg = new Pagination('.pagination', '.page-selector', 'data-page-number', 'page');
        pg.init();
        $(document).on('change', '.filter-container form select', function(e2){
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
        <th scope="col" width="20"><i class="ti ti-trash"></i></th>
        <th scope="col" width="20">#</th>
        <th scope="col">Title</th>
        <th scope="col">Album</th>
        <th scope="col">Genre</th>
        <th scope="col">Artist</th>
        <th scope="col">Duration</th>
        <th scope="col">Active</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = $pagination->getOffset();
        foreach($result as $reference)
        {
        $no++;
        $referenceId = $reference->getReferenceId();
        $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&reference_id=".$referenceId;
        $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&reference_id=".$referenceId;
        $linkDelete = basename($_SERVER['PHP_SELF'])."?action=delete&reference_id=".$referenceId;
        ?>
        <tr data-id="<?php echo $referenceId;?>">
        <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
        <th scope="row"><a href="<?php echo $linkDelete;?>" class="delete-data"><i class="ti ti-trash"></i></a></th>
        <th class="text-right" scope="row"><?php echo $no;?></th>
        <td><a href="<?php echo $linkDetail;?>" class="text-data text-data-title"><?php echo $reference->getTitle();?></a></td>
        <td class="text-data text-data-album-name"><?php echo $reference->hasValueAlbum() ? $reference->getAlbum()->getName() : "";?></td>
        <td class="text-data text-data-genre-name"><?php echo $reference->hasValueGenre() ? $reference->getGenre()->getName() : "";?></td>
        <td class="text-data text-data-artist-name"><?php echo $reference->hasValueArtistVocal() ? $reference->getArtistVocal()->getName() : "";?></td>
        <td class="text-data text-data-duration"><?php echo $reference->getDuration();?></td>
        <td class="text-data text-data-active"><?php echo $reference->isActive() ? 'Yes' : 'No';?></td>
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

<?php
}
?>

<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/reference-update-dialog.php"></div>
<div class="lazy-dom modal-container modal-add-data" data-url="lib.ajax/reference-add-dialog.php"></div>

<script>
  let addReferenceModal;
  let updateReferenceModal;

  $(document).ready(function(e){
    console.log('aaa')

    $(document).on('click', '.add-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let dialogSelector = $('.modal-add-data');
      dialogSelector.load(dialogSelector.attr('data-url'), function(data){
        let addReferenceModalElem = document.querySelector('#addReferenceDialog');
        addReferenceModal = new bootstrap.Modal(addReferenceModalElem, {
          keyboard: false
        });
        addReferenceModal.show();
      })
    });

    $(document).on('click', '.save-add-reference', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/reference-add.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          addReferenceModal.hide();
          window.location.reload();
        }
      })
    });   
  
    
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      
      let referenceId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-update-data');
      dialogSelector.load(dialogSelector.attr('data-url')+'?reference_id='+referenceId, function(data){
        
        let updateReferenceModalElem = document.querySelector('#updateReferenceDialog');
        updateReferenceModal = new bootstrap.Modal(updateReferenceModalElem, {
          keyboard: false
        });
        updateReferenceModal.show();
        downloadForm('.lazy-dom-container', function(){
          if(!allDownloaded)
          {
              initModal2();
              console.log('loaded')
              allDownloaded = true;
          }
          loadForm();
      });
      })
    });

    $(document).on('click', '.save-update-reference', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/reference-update.php',
        data:dataSet, 
        dataType:'json',
        success: function(data)
        {
          updateReferenceModal.hide();
          let formData = getFormData(dataSet);
          let dataId = data.reference_id;
          let title = data.title;
          let active = data.active;
          $('[data-id="'+dataId+'"] .text-data.text-data-title').text(data.title);
          $('[data-id="'+dataId+'"] .text-data.text-data-artist-name').text(data.artist_vocal_name);
          $('[data-id="'+dataId+'"] .text-data.text-data-album-name').text(data.album_name);
          $('[data-id="'+dataId+'"] .text-data.text-data-genre-name').text(data.genre_name);
          $('[data-id="'+dataId+'"] .text-data.text-data-active').text(data.active?'Yes':'No');
          $('[data-id="'+dataId+'"] .text-data.text-data-duration').text(data.duration);

        }
      })
    });
  });
</script>
<?php
}
require_once "inc/footer.php";
?>