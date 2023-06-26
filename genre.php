<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Genre;

require_once "inc/auth.php";
require_once "inc/header.php";

$pagination = new PicoPagination($cfg->getResultPerPage()); 
$subquery = new PicoDatabaseQueryBuilder($database);
$queryBuilder = new PicoDatabaseQueryBuilder($database);

$order = $pagination->createOrder(array(
), array(
  'genre_id',
  'name',
  'time_create'
), 
'name'
);

$sql = $queryBuilder->newQuery()
  ->select("genre.*
  ")
  ->from("genre")
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
      <th scope="col">Active</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($data as $row)
    {
      $no++;
      $genre = new Genre($row);
      $linkEdit = basename($_SERVER['PHP_SELF'])."?genre_id=".$genre->getGenreId();
    ?>
    <tr data-id="<?php echo $genre->getGenreId();?>">
      <th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkEdit;?>"><?php echo $genre->getName();?></a></td>
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