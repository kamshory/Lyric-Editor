<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Album;
use Pico\Data\Entity\Genre;
use Pico\Database\PicoPagable;
use Pico\Database\PicoPage;
use Pico\Database\PicoSortable;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Utility\SpecificationUtil;

require_once "inc/auth.php";
require_once "inc/header.php";

$inputGet = new PicoRequest(INPUT_GET);


  ?>
  <div class="filter-container">
  <form action="" method="get">
  <div class="filter-group">
      <span>Name</span>
      <input class="form-control" type="text" name="name" id="name" autocomplete="off" value="<?php echo $inputGet->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
  </div>
  
  <input class="btn btn-success" type="submit" value="Show">
  
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

$spesification = SpecificationUtil::createGenreSpecification($inputGet);
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $orderDefault), $pagination->getOrderType());
$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$genreEntity = new Genre(null, $database);
$rowData = $genreEntity->findAll($spesification, $pagable, $sortable, true);

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
      <th scope="col">Active</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($result as $genre)
    {
      $no++;
      $linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&genre_id=".$genre->getGenreId();
      $linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&genre_id=".$genre->getGenreId();
    ?>
    <tr data-id="<?php echo $genre->getGenreId();?>">
      <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkDetail;?>"><?php echo $genre->getName();?></a></td>
      <td><?php echo $genre->getActive() ? 'Yes' : 'No';?></td>
    </tr>
    <?php
    }
    ?>
    
  </tbody>
</table>


<div class="lazy-dom modal-container" data-url="lib.ajax/genre-edit-dialog.php"></div>

<script>
  let editGenreModal;
  $(document).ready(function(e){
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let genreId = $(this).closest('tr').attr('data-id') || '';
      let dialogSelector = $('.modal-container');
      dialogSelector.load(dialogSelector.attr('data-url')+'?genre_id='+genreId, function(data){
        let editGenreModalElem = document.querySelector('#editGenreDialog');
        editGenreModal = new bootstrap.Modal(editGenreModalElem, {
          keyboard: false
        });
        editGenreModal.show();
      })
    });

    $(document).on('click', '.save-edit-genre', function(){
      let dataSet = $(this).closest('form').serializeArray();
      $.ajax({
        type:'POST',
        url:'lib.ajax/genre-update.php',
        data:dataSet, 
        dataType:'html',
        success: function(data)
        {
          console.log(data)
          editGenreModal.hide();
        }
      })
    });
  });
</script>

<?php
}
require_once "inc/footer.php";
?>