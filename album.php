<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Dto\AlbumDto;
use Pico\Data\Entity\Album;
use Pico\Exception\NoRecordFoundException;
use Pico\Request\PicoRequest;

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
$pagination = new PicoPagination($cfg->getResultPerPage()); 
$subquery = new PicoDatabaseQueryBuilder($database);
$queryBuilder = new PicoDatabaseQueryBuilder($database);

$order = $pagination->createOrder(array(
), array(
  'album_id',
  'name',
  'time_create'
), 
'name'
);

$sql = $queryBuilder->newQuery()
  ->select("album.*")
  ->from("album")
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
    foreach($data as $row)
    {
      $no++;
      $album = AlbumDto::valueOf(new Album($row));
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&album_id=".$album->getAlbumId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&album_id=".$album->getAlbumId();
    ?>
    <tr data-id="<?php echo $album->getAlbumId();?>">
      <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $album->getName();?></a></td>
      <td><?php echo $album->getDuration();?></td>
      <td><?php echo $album->getNumberOfSong();?></td>
      <td><?php echo $album->getActive() ? 'Yes' : 'No';?></td>
    </tr>
    <?php
    }
    ?>
    
  </tbody>
</table>

<div class="lazy-dom modal-container" data-url="lib.ajax/album-edit-dialog.php"></div>

<script>
  let editAlbumModal;
  $(document).ready(function(e){
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let albumId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-container');
      dialogSelector.load(dialogSelector.attr('data-url')+'?album_id='+albumId, function(data){
        let editAlbumModalElem = document.querySelector('#editAlbumDialog');
        editAlbumModal = new bootstrap.Modal(editAlbumModalElem, {
          keyboard: false
        });
        editAlbumModal.show();
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
          editAlbumModal.hide();
          let formData = getFormData(dataSet);
          console.log(formData)
          let name = formData.name;
          $('.text-data.text-data-name').text(name);
        }
      })
    });
  });
  
  
  
</script>

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