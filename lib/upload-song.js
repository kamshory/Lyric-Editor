
let selectName = '';
$(document).ready(function (e) {

    let uploadModalSelector = document.querySelector('#uploadFile');
    let uploadModal = new bootstrap.Modal(uploadModalSelector, {
        keyboard: false
    });

    let albumModalSelector = document.querySelector('#addAlbumDialog');
    let albumModal = new bootstrap.Modal(albumModalSelector, {
        keyboard: false
    });

    let genreModalSelector = document.querySelector('#addGenreDialog');
    let genreModal = new bootstrap.Modal(genreModalSelector, {
        keyboard: false
    });

    let artistModalSelector = document.querySelector('#addArtistDialog');
    let artistModal = new bootstrap.Modal(artistModalSelector, {
        keyboard: false
    });


    $(document).on('change', '.upload-drop-zone input[type="file"]', function(e)
    {
        let file = e.target.files[0];
        document.querySelector('[name="title"]').value = file.name;
        let formData = new FormData();
        formData.append('file', file);
        formData.append('random_song_id', document.querySelector('[name="random_song_id"]').value);

        $.ajax({
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        let val = parseInt(((evt.loaded / evt.total) * 100));    
                        let pb = $(".progress-upload .progress-bar");  
                        pb.css('width', val+'%');
                        pb.attr('aria-valuenow', val);
                        pb.html(val + '%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: 'lib.ajax/song-upload.php',
            data: formData,
            dataType:'json',
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                let pb = $(".progress-upload .progress-bar");
                let val = 0;
                pb.css('width', val+'%');
                pb.attr('aria-valuenow', val);
                pb.html(val + '%');
                $('.loader-icon').show();
            },
            error:function(){
                $('.loader-icon').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
            },
            success: function(response){
                console.log(response)
                if(response){
                    $('.loader-icon').html('<p style="color:#28A74B;">File has uploaded successfully!</p>');
                    
                }else if(resp == 'err'){
                    $('.loader-icon').html('<p style="color:#EA4335;">Please select a valid file to upload.</p>');
                }
            }
        });
        $('.file-uploader').attr('data-status', '2');
    });


    // Show upload file modal
    $(document).on('click', '.button-upload-file', function (e1) {
        e1.preventDefault();
        e1.stopPropagation();
        loadForm();  
        document.querySelector('[name="random_song_id"]').value = generateRandom(20);
        document.querySelector('.file-uploader').setAttribute('data-status', '1');
        uploadModal.show();
    });

    $(document).on('click', '.save-song', function(e1){
        let dataRequest = $(uploadModalSelector).find('form').serializeArray();
        $.ajax({
            url: 'lib.ajax/song-add.php',
            type: 'POST',
            dataType: 'json',
            data: dataRequest,
            success: function (data) {
                uploadModal.hide();
            }
        });
    });

    // Show adbum modal
    $(document).on('click', '.button-add-album', function (e1) {
        e1.preventDefault();
        e1.stopPropagation();
        selectName = e1.target.parentNode.querySelector('select').getAttribute('name');        
        albumModal.show();
        setTimeout(function () {
            albumModalSelector.querySelector('[name="album_name"]').select();
        }, 1000);
    });

    // Save album
    $(document).on('click', '.save-album', function (e2) {
        let textElement = e2.target.parentNode.parentNode.parentNode.querySelector('input[type="text"]');
        e2.preventDefault();
        $.ajax({
            url: 'lib.ajax/album-add.php',
            type: 'POST',
            dataType: 'json',
            data: { name: textElement.value },
            success: function (data) {
                if (data && data.id && data.value) {
                    let opt = document.createElement('option');
                    opt.setAttribute('value', data.id);
                    opt.setAttribute('selected', 'selected');
                    opt.innerHTML = data.value;
                    let selectElement = document.querySelector('select[name="'+selectName+'"]');
                    selectElement.append(opt);
                    selectElement.value = data.id;
                }
            }
        });
        albumModal.hide();
    });


    $(document).on('click', '.button-add-genre', function (e1) {
        e1.preventDefault();
        e1.stopPropagation();
        selectName = e1.target.parentNode.querySelector('select').getAttribute('name');
        genreModalSelector.querySelector('[name="genre_name"]').value = '';
        genreModal.show();
        setTimeout(function () {
            genreModalSelector.querySelector('[name="genre_name"]').select();
        }, 1000);

    });


    $(document).on('click', '.save-genre', function (e2) {
        let textElement = e2.target.parentNode.parentNode.parentNode.querySelector('input[type="text"]');
        e2.preventDefault();
        $.ajax({
            url: 'lib.ajax/genre-add.php',
            type: 'POST',
            dataType: 'json',
            data: { name: textElement.value },
            success: function (data) {
                if (data && data.id && data.value) {
                    let opt = document.createElement('option');
                    opt.setAttribute('value', data.id);
                    opt.setAttribute('selected', 'selected');
                    opt.innerHTML = data.value;
                    let selectElement = document.querySelector('select[name="'+selectName+'"]');
                    selectElement.append(opt);
                    selectElement.value = data.id;
                }
            }
        });
        genreModal.hide();
    });

    $(document).on('click', '.button-add-artist', function (e1) {
        e1.preventDefault();
        e1.stopPropagation();
        selectName = e1.target.parentNode.querySelector('select').getAttribute('name');
        artistModalSelector.querySelector('[name="artist_name"]').value = '';
        artistModal.show();
        setTimeout(function () {
            artistModalSelector.querySelector('[name="artist_name"]').select();
        }, 1000);

    });


    $(document).on('click', '.save-artist', function (e2) {
        let textElement = e2.target.parentNode.parentNode.parentNode.querySelector('input[type="text"]');
        e2.preventDefault();
        $.ajax({
            url: 'lib.ajax/artist-add.php',
            type: 'POST',
            dataType: 'json',
            data: { name: textElement.value },
            success: function (data) {
                if (data && data.id && data.value) {
                    let opt = document.createElement('option');
                    opt.setAttribute('value', data.id);
                    opt.setAttribute('selected', 'selected');
                    opt.innerHTML = data.value;
                    let selectElement = document.querySelector('select[name="'+selectName+'"]');
                    selectElement.append(opt);
                    selectElement.value = data.id;
                }
            }
        });
        artistModal.hide();
    });
});


function loadForm() {
    $('select[data-ajax="true"]').each(function (index) {
        let element = $(this);
        let current_value = $(this).find('option:selected').val() || '';
        let path = $(this).attr('data-source');
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: path,
            data: { current_value: current_value },
            success: function (data) {
                if (data && data.length) {
                    element.empty();
                    let opt = $('<option>- select -</option>');
                    opt.attr('value', '');
                    element.append(opt);
                    for (let i = 0; i < data.length; i++) {
                        let opt = $('<option />');
                        opt.text(data[i].value);
                        opt.attr('value', data[i].id);
                        if (typeof data[i].selected != 'undefined' && data[i].selected) {
                            opt.attr('selected', 'selected');
                        }
                        element.append(opt);
                    }
                }
            },
            error: function (err) {
                console.log(path);
                console.log(err)
            }
        })
    })
}

function generateRandom(length) 
{
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let counter = 0;
    while (counter < length) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
      counter += 1;
    }
    return result;
}