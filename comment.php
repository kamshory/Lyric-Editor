<?php

use Pico\Data\Entity\Album;
use Pico\Data\Entity\Artist;
use Pico\Data\Entity\EntitySong;
use Pico\Data\Entity\Genre;
use Pico\Data\Entity\Song;
use Pico\Data\Tools\SelectOption;
use Pico\Database\PicoPagable;
use Pico\Database\PicoPage;
use Pico\Database\PicoSortable;
use Pico\Pagination\PicoPagination;
use Pico\Request\PicoFilterConstant;
use Pico\Request\PicoRequest;
use Pico\Utility\SpecificationUtil;

require_once "inc/auth.php";
require_once "inc/header.php";
?>
<link rel="stylesheet" href="lib/lyric-editor.css">
<script src="lib/script.js"></script>
<script src="lib/ajax.js"></script>
<link rel="stylesheet" href="lib/icon.css">

<?php
$inputGet = new PicoRequest(INPUT_GET);
if($inputGet->equalsAction(PicoRequest::ACTION_EDIT) && $inputGet->getSongId() != null)
{
?>
<div class="srt-editor editor1">
    <div class="row">
        <div class="col col-7">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Timeline</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Raw</button>
                </li>
            </ul>
            <div class="tab-content" id="srt-tab-content">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <!-- list begin -->
                    <div class="srt-list-wrapper">
                        <div class="srt-list-container">

                        </div>
                    </div>
                    <!-- list end -->

                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="srt-raw">
                        <textarea class="srt-text-raw" spellcheck="false"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col col-5">
            <div class="player">
                <div class="text-display-container">
                    <div class="text-display">
                        <div class="text-display-inner d-flex align-items-center justify-content-center"></div>
                    </div>
                </div>
                <div class="srt-zoom-control-wrapper">
                <input type="range" class="srt-zoom-control" min="0" max="8" step="1" list="input-markers">
                <datalist id="input-markers" style="--list-length: 9;">
                    <option value="0">0.125x</option><option value="1">0.25x</option><option value="2">0.5x</option><option value="3">0.75x</option><option value="4">1x</option><option value="5">1.25x</option><option value="6">1.5x</option><option value="7">1.75x</option><option value="8">2x</option>
                </datalist>
                </div>
                <div class="player-controller">
                    <button class="btn btn-dark button-play-master">Play</button>
                    <button class="btn btn-dark button-pause-master">Pause</button>
                    <button class="btn btn-dark button-scroll-master">Scroll</button>
                    <button class="btn btn-dark button-reset-master">Reset</button>
                    <button class="btn btn-dark button-save-master">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- controller drag begin -->
    <div class="srt-map">
        <div class="srt-map-first-layer">

            <div class="srt-time-position">
                <div class="srt-time-position-inner">
                    <div class="srt-time-position-pointer" data-toggle="tooltip" data-placement="top" title="00:00:00"></div>
                </div>
            </div>

            <div class="srt-timestamp">
                <canvas class="srt-timeline-canvas"></canvas>
            </div>

            <div class="srt-edit-area">
                <div class="srt-waveform">
                    <canvas class="srt-timeline-canvas-edit" height="64" width="100%"></canvas>
                </div>
                <div class="srt-map-srt-container">
                </div>
            </div>
        </div>
    </div>
    <!-- controller drag end -->
</div>

<!-- Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">Delete Text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this one?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger delete">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php

try
{
$song = new Song(array('song_id'=>@$_GET['song_id']), $database);
$song->select();
if($song != null)
{
    $comment = $song->getComment();
    if(strlen(trim($comment)) == 0)
    {
        $comment = "{type here}";
    }
    if(stripos($comment, "-->") === false)
    {
        $comment = "00:00:00,000 --> 00:00:01,000\r\n".$comment;
    }
?>
<script>
    let song_id = '<?php echo $song->getSongId(); ?>';
    let path = '<?php echo $cfg->getSongBaseUrl();?>/<?php echo $song->getFileName(); ?>';
    let jsonData = <?php echo json_encode(array('comment'=>$comment)); ?>;
    let rawData = jsonData.comment;
</script>
<script>
    let srt;
    $(document).ready(function(evt)
    {      
        srt = new SrtGenerator('.editor1', rawData, path);
        srt.onDeleteData = function(index, countData) {
            if (countData > 1) {
                idToDelete = index;
                let myModal = new bootstrap.Modal(document.querySelector('#deleteItem'), {
                    keyboard: false
                });
                myModal.show();
                document.querySelector('#deleteItem .delete').addEventListener('click', function(e) {
                    srt.deleteData(idToDelete);
                    idToDelete = -1;
                    myModal.hide();
                });
            }
        };

        document.querySelector('.button-play-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.play();
        });

        document.querySelector('.button-scroll-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.toggleScroll();
        });

        document.querySelector('.button-pause-master').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            srt.pause(true);
        });

        document.querySelector('.button-save-master').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            saveComment();
        });
        document.querySelector('.button-reset-master').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetComment();
        });

        document.onkeydown = function(e) {
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                e.stopPropagation();
                saveComment();
            }
        };
    });
    function resetComment()
    {
         $.ajax({
            type:'GET',
            url:'lib.ajax/comment-load.php',
            data:{song_id:song_id},
            dataType:'json',
            success:function(data)
            {
                rawData:data.comment;
                srt.initData(rawData, path)
            }
        });
    }
    function saveComment()
    {
        if(srt.zoomLevelIndex < srt.zoomLevelIndexOriginal)
        {
            srt.resetZoom();
        }
        srt.updateData();
        let duration = srt.duration;
        rawData = srt.getFinalResult();
        ajax.post('lib.ajax/comment-save.php', {
            song_id: song_id,
            comment: rawData,
            duration: duration
        }, function(response, status) {
        });
    }
</script>

<?php
}
}
catch(Exception $e)
{
    // do nothing
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
$defaultOrderBy = 'title';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet, array('active'=>true));;
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType());
$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new EntitySong(null, $database);
$rowData = $songEntity->findAll($spesification, $pagable, $sortable, true);

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
<?php
}
}
require_once "inc/footer.php";
?>