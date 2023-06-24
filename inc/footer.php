</div>
<div class="px-6 text-center">
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





<div class="lazy-dom-container">
    <div class="lazy-dom song-upload-dialog" data-url="lib.ajax/song-upload-dialog.php"></div>
    <div class="lazy-dom album-add-dialog" data-url="lib.ajax/album-add-dialog.php"></div>
    <div class="lazy-dom genre-add-dialog" data-url="lib.ajax/genre-add-dialog.php"></div>
    <div class="lazy-dom artist-add-dialog" data-url="lib.ajax/artist-add-dialog.php"></div>
</div>
</div>
</div>
<script>
    $(document).ready(function(e){
        
    })
</script>
</body>
</html>