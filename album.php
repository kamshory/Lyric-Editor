<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Album;

require_once "inc/auth.php";
require_once "inc/header.php";

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
  ->select("album.*
  ")
  ->from("album")
  ->where("album.active = ? ", true)
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
      <th scope="col" width="20">&nbsp;</th>
      <th scope="col" width="20">#</th>
      <th scope="col">Name</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($data as $row)
    {
      $no++;
      $album = new Album($row);
    ?>
    <tr data-id="<?php echo $album->getAlbumId();?>">
      <th scope="row"><a href="#" class="edit-data">Edit</a></th>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?album_id=<?php echo $album->getAlbumId();?>"><?php echo $album->getName();?></a></td>
    </tr>
    <?php
    }
    ?>
    
  </tbody>
</table>

<script>
  $(document).ready(function(e){
    $(document).on('click', '.edit-data', function(e2){
      e2.preventDefault();
      e2.stopPropagation();
      let albumId = $(this).closest('tr').attr('data-id') || '';
      console.log(albumId)
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