<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modernize Free</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <style>
    body{
      font-family:"Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }
    .icon-size-20{
      font-size: 20px;
    }
    .main-page{
      padding:100px 20px 0px 20px;
      box-sizing: border-box;
      position: relative;
      min-height: calc(100vh - 75px);
  }

  .modal-body .table-borderless td
  {
    padding: 5px 5px;
    vertical-align: middle;
  }

  .modal table td{
    position: relative;
  }

  .button-add-list {
    position: absolute;
    box-sizing: border-box;
    width: 28px;
    height: 28px;
    right: 11px;
    top: 10px;
    background-color: #FFFFFF;
    border: 1px solid #DDDDDD;
    border-radius: 50%;
  }

  .file-uploader[data-status="1"] .file-upload-zone{
    display: block;
  }
  .file-uploader[data-status="1"] .song-info{
    display: none;
  }
  .file-uploader[data-status="2"] .file-upload-zone{
    display: none;
  }
  .file-uploader[data-status="2"] .song-info{
    display: block;
  }

  .dialog-table{
    width: 100%;
  }
  .dialog-table tr td:first-child{
    width: 35%;
  }
  .dialog-table tr td{
    padding: 2px 0;
  }



  

  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <?php require_once "inc/sidebar.php";?>
    
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <?php
        require_once "header-inner.php";
      ?>
      <div class="main-page">