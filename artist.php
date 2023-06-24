<?php
use Pico\Database\PicoDatabaseQueryBuilder;
use Pico\Pagination\PicoPagination;
use \PDO as PDO;
use Pico\Data\Entity\Artist;

require_once "inc/auth.php";
require_once "inc/header.php";

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
      <th scope="col">Name</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = $pagination->getOffset();
    foreach($data as $row)
    {
      $no++;
      $artist = new Artist($row);
      $linkEdit = basename($_SERVER['PHP_SELF'])."?artist_id=".$album->getArtistId();
    ?>
    <tr>
      <th scope="row"><?php echo $no;?></th>
      <td><a href="<?php echo $linkEdit;?>"><?php echo $artist->getName();?></a></td>
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