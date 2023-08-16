<?php

use Pico\Data\Entity\Album;
use Pico\Exception\NoRecordFoundException;
use Pico\Request\PicoRequest;

require_once dirname(__DIR__)."/inc/auth.php";
$inputGet = new PicoRequest(INPUT_GET);
$album = new Album(null, $database);
?>
<form action="">
    <div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="editAlbumDialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editAlbumDialogLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAlbumDialogLabel">Edit Album</h5>
                    <button type="button" class="btn-primary btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <?php
                    try
                    {                     
                    $album->findOneByAlbumId($inputGet->getAlbumId());
                    ?>
                    <table class="dialog-table">
                        <tbody>
                            <tr>
                                <td>Album Name</td>
                                <td><input type="text" class="form-control" name="name" value="<?php echo $album->getName();?>"></td>
                            </tr>
                            <tr>
                                <td>Release Date</td>
                                <td><input type="date" class="form-control" name="release_date" value="<?php echo $album->getReleaseDate();?>"></td>
                            </tr>
                            <tr>
                                <td>Active</td>
                                <td><label></label><input type="checkbox" name="active" value="1" <?php echo $album->getActive() == 1 ?' checked':'';?>> Active</label></td>
                            </tr>
                        </tbody>
                        <input type="hidden" name="album_id" value="<?php echo $album->getAlbumId();?>">
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
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success save-edit-album">OK</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</form>