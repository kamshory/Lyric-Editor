</div>
<div class="py-6 px-6 text-center">
    <p class="mb-0 fs-4">Design and Developed by <a href="https://adminmart.com/" target="_blank" class="pe-1 text-primary text-decoration-underline">AdminMart.com</a> Distributed by <a href="https://themewagon.com">ThemeWagon</a></p>
</div>


<style>
    body {
    margin: 24px;
    }


    .upload-drop-zone {
    color: #0f3c4b;
    background-color: var(--colorPrimaryPale, #c8dadf);
    outline: 2px dashed var(--colorPrimaryHalf, #c1ddef);
    outline-offset: -12px;
    transition:
        outline-offset 0.2s ease-out,
        outline-color 0.3s ease-in-out,
        background-color 0.2s ease-out;
    }
    .upload-drop-zone.highlight {
    outline-offset: -4px;
    outline-color: var(--colorPrimaryNormal, #0576bd);
    background-color: var(--colorPrimaryEighth, #c8dadf);
    }
    .upload_svg {
    fill: var(--colorPrimaryNormal, #0576bd);
    }
    
    .upload_img {
    width: calc(33.333% - (2rem / 3));
    object-fit: contain;
    }
</style>
<script src="lib/upload-song.js">
    

</script>
<!-- Modal -->



<div class="modal fade" id="uploadFile" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadFileLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadFileLabel">Upload Song</h5>
                <button type="button" class="btn-primary btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            
                <div class="file-uploader">
                    <fieldset class="file-upload-zone upload-drop-zone text-center mb-3 p-4">
                        <legend class="visually-hidden">Song Pploader</legend>
                        <svg class="upload_svg" width="60" height="60" aria-hidden="true">
                            <use href="#icon-imageUpload"></use>
                        </svg>
                        <p class="small my-2">Drag &amp; drop song into this region<br><i>or</i></p>
                        <input id="upload_image_background" data-post-name="image_background" class="position-absolute invisible" type="file" accept="audio/mp3" />
                        <label class="btn btn-primary mb-3" for="upload_image_background">Choose File</label>
                        <div class="upload_gallery d-flex flex-wrap justify-content-center gap-3 mb-0"></div>
                    </fieldset>

                    <fieldset class="song-info">
                        <legend class="visually-hidden">Song Information</legend>
                        <form>
                
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>Title</td>
                                    <td>
                                        <input type="text" class="form-control" name="title">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Album</td>
                                    <td>
                                        <select class="form-control" name="album_id" data-ajax="true" data-source="lib.ajax/album-list.php">
                                            <option value="">- select -</option>
                                        </select>
                                        <button class="button-add-list button-add-album">+</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Genre</td>
                                    <td>
                                        <select class="form-control" name="genre_id" data-ajax="true" data-source="lib.ajax/genre-list.php">
                                            <option value="">- select -</option>
                                        </select>
                                        <button class="button-add-list button-add-genre">+</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Vocal</td>
                                    <td>
                                        <select class="form-control" name="artist_vocal" data-ajax="true" data-source="lib.ajax/artist-list.php">
                                            <option value="">- select -</option>
                                        </select>
                                        <button class="button-add-list button-add-artist">+</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Composer</td>
                                    <td>
                                        <select class="form-control" name="artist_composer" data-ajax="true" data-source="lib.ajax/artist-list.php">
                                            <option value="">- select -</option>
                                        </select>
                                        <button class="button-add-list button-add-artist">+</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Arranger</td>
                                    <td>
                                        <select class="form-control" name="artist_arranger" data-ajax="true" data-source="lib.ajax/artist-list.php">
                                            <option value="">- select -</option>
                                        </select>
                                        <button class="button-add-list button-add-artist">+</button>                                      
                                    </td>
                                </tr>
                                <input type="hidden" name="random_song_id" value="">
                            </tbody>
                        </table>
                        <div class="progress-upload">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div class="loader-icon">&nbsp;</div>
                        </form>
                    </fieldset>

                </div>
            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success save-song">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="addAlbumDialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addAlbumDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAlbumDialogLabel">Add Album</h5>
                <button type="button" class="btn-primary btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" name="album_name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success save-album">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="addGenreDialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addGenreDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGenreDialogLabel">Add Genre</h5>
                <button type="button" class="btn-primary btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" name="genre_name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success save-genre">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="addArtistDialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addArtistDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addArtistDialogLabel">Add Artist</h5>
                <button type="button" class="btn-primary btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" name="artist_name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success save-artist">OK</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


</div>
</div>
<script>
    $(document).ready(function(e){
        
    })
</script>
</body>
</html>