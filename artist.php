<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Artist;
use Pico\Exceptions\NoRecordFoundException;
use Pico\Request\PicoRequest;

require_once "inc/auth.php";
require_once "inc/header.php";

$inputGet = new PicoRequest(INPUT_GET);
if($inputGet->equalsAction(PicoRequest::ACTION_DETAIL) && $inputGet->getArtistId() != null)
{
  $artist = new Artist(null, $database);
  try
  {
  $artist->findOneByArtistId($inputGet->getArtistId());
  ?>
  <table class="table table-responsive">
    <tbody>
      <tr>
        <td>Artist ID</td>
        <td><?php echo $artist->getArtistId();?></td>
      </tr>
      <tr>
        <td>Name</td>
        <td><?php echo $artist->getName();?></td>
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
  'artist_id',
  'name',
  'time_create'
), 
'name'
);

$sql = $queryBuilder->newQuery()
  ->select("artist.*
  ")
  ->from("artist")
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
      <th scope="col">Real Name</th>
      <th scope="col">Stage Name</th>
      <th scope="col">Gender</th>
      <th scope="col">Active</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($data as $row)
    {
      $no++;
      $artist = new Artist($row);
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&artist_id=".$artist->getArtistId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&artist_id=".$artist->getArtistId();
    ?>
    <tr data-id="<?php echo $artist->getArtistId();?>">
      <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $artist->getName();?></a></td>
      <td class="text-data text-data-stage_name"><?php echo $artist->getStageName();?></td>
      <td class="text-data text-data-gender"><?php echo $artist->getGender() == 'M' ? 'Man' : 'Woman';?></td>
      <td class="text-data text-data-active"><?php echo $artist->getActive() ? 'Yes' : 'No';?></td>
    </tr>
    <?php
    }
    ?>
    
  </tbody>
</table>

<div class="lazy-dom modal-container" data-url="lib.ajax/artist-update-dialog.php"></div>

<script>
  let editArtistModal;
  $(document).ready(function(e){
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let artistId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-container');
      dialogSelector.load(dialogSelector.attr('data-url')+'?artist_id='+artistId, function(data){
        let editArtistModalElem = document.querySelector('#editArtistDialog');
        editArtistModal = new bootstrap.Modal(editArtistModalElem, {
          keyboard: false
        });
        editArtistModal.show();
      })
    });

    $(document).on('click', '.save-edit-artist', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/artist-update.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          editArtistModal.hide();
          let formData = getFormData(dataSet);
          let dataId = formData.artist_id;
          let name = formData.name;
          let stage_name = formData.stage_name;
          let gender = formData.gender;
          let active = $('[name="active"]')[0].checked;
          $('[data-id="'+dataId+'"] .text-data.text-data-name').text(name);
          $('[data-id="'+dataId+'"] .text-data.text-data-stage_name').text(stage_name);
          $('[data-id="'+dataId+'"] .text-data.text-data-gender').text(gender=='M'?'Man':'Woman');
          $('[data-id="'+dataId+'"] .text-data.text-data-active').text(active?'Yes':'No');
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