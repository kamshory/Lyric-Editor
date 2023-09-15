<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\EntitySong;
use Pico\Database\PicoPagable;
use Pico\Database\PicoPage;
use Pico\Database\PicoSortable;
use Pico\Exceptions\NoRecordFoundException;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Utility\SpecificationUtil;

require_once "inc/auth.php";
require_once "inc/header.php";

$inputGet = new PicoRequest(INPUT_GET);
if($inputGet->equalsAction(PicoRequest::ACTION_DETAIL) && $inputGet->getAlbumId() != null)
{
  $album = new Album(null, $database);
  try
  {
  $album->findOneByAlbumId($inputGet->getAlbumId());
  ?>
  
  <table class="table table-responsive">
    <tbody>
      <tr>
        <td>Album ID</td>
        <td><?php echo $album->getAlbumId();?></td>
      </tr>
      <tr>
        <td>Name</td>
        <td><?php echo $album->getName();?></td>
      </tr>
      <tr>
        <td>Release Date</td>
        <td><?php echo $album->getReleaseDate();?></td>
      </tr>
      <tr>
        <td>Number of Song</td>
        <td><?php echo $album->getNumberOfSong();?></td>
      </tr>
      <tr>
        <td>Duration</td>
        <td><?php echo $album->getDuration();?></td>
      </tr>
    </tbody>
  </table>
  
  <?php
  }
  catch(NoRecordFoundException $e)
  {
    ?>
    <div class="alert alert-warning"><?php echo $e->getMessage();?></div>
    <?php
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
      <span>Name</span>
      <input class="form-control" type="text" name="name" id="name" autocomplete="off" value="<?php echo $inputGet->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
  </div>
  
  <input class="btn btn-success" type="submit" value="Show">
  <input class="btn btn-primary add-data" type="button" value="Add">
  
  </form>
</div>
<?php
$orderMap = array(
  'name'=>'name', 
  'albumId'=>'albumId', 
  'album'=>'albumId'
);
$orderDefault = 'name';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createAlbumSpecification($inputGet);
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $orderDefault), $pagination->getOrderType());
$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$albumEntity = new Album(null, $database);
$rowData = $albumEntity->findAll($spesification, $pagable, $sortable, true);

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
      <th scope="col">Name</th>
      <th scope="col">Duration</th>
      <th scope="col">Song</th>
      <th scope="col">Active</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($result as $album)
    {
      $no++;
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&album_id=".$album->getAlbumId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&album_id=".$album->getAlbumId();
    ?>
    <tr data-id="<?php echo $album->getAlbumId();?>">
      <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $album->getName();?></a></td>
      <td><?php echo $album->getDuration();?></td>
      <td><?php echo $album->getNumberOfSong();?></td>
      <td class="text-data text-data-active"><?php echo $album->getActive() ? 'Yes' : 'No';?></td>
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


<div class="lazy-dom modal-container modal-add-data" data-url="lib.ajax/album-add-dialog.php"></div>
<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/album-update-dialog.php"></div>

<script>
  let addAlbumModal;
  let updateAlbumModal;
  
  $(document).ready(function(e){
    
    $(document).on('click', '.add-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let dialogSelector = $('.modal-add-data');
      dialogSelector.load(dialogSelector.attr('data-url'), function(data){
        let addAlbumModalElem = document.querySelector('#addAlbumDialog');
        addAlbumModal = new bootstrap.Modal(addAlbumModalElem, {
          keyboard: false
        });
        addAlbumModal.show();
      })
    });
    
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let albumId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-update-data');
      dialogSelector.load(dialogSelector.attr('data-url')+'?album_id='+albumId, function(data){
        let updateAlbumModalElem = document.querySelector('#updateAlbumDialog');
        updateAlbumModal = new bootstrap.Modal(updateAlbumModalElem, {
          keyboard: false
        });
        updateAlbumModal.show();
      })
    });
    
    $(document).on('click', '.save-add-album', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/album-add.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          addAlbumModal.hide();
          window.location.reload();
        }
      })
    });

    $(document).on('click', '.save-edit-album', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/album-update.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          updateAlbumModal.hide();
          let formData = getFormData(dataSet);
          let dataId = formData.album_id;
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