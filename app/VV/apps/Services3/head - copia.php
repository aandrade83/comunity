<?php
 session_start();
 $filial = $_SESSION['filial'];
 $id_user = $_SESSION['user'];
 $comision = false;
 if($_SESSION['rol'] == 2){ $comision = true;}
 require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");
 
 $Categorias = get_all_categorias();
 $total = get_total_categoria();


$rutahead="https://lab.lacallecr.com/VV/utilities/tema";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Condominio:: Valle Verde</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom -->
        <link href="css/custom.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
          <![endif]-->

        <!-- fonts -->
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="font-awesome-4.0.3/css/font-awesome.min.css">

        <!-- CSS STYLE-->
        <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />

        <!-- SLIDER REVOLUTION 4.x CSS SETTINGS -->
        <link rel="stylesheet" type="text/css" href="rs-plugin/css/settings.css" media="screen" />
         <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.js"></script>
         
         <script src="js/bootstrap.min.js"></script>
        <!-- SweetAlert2 CSS -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10"> -->
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
           <link rel="stylesheet" href="<?php echo $rutahead;?>/libs/dropzone/dropzone.min.css">
          <script src="<?php echo $rutahead;?>/libs/dropzone/dropzone.min.js"></script>


        <script type="text/javascript" src="js/functions.js"></script>

        <link rel="stylesheet" href="<?php echo $rutahead;?>/libs/magnific-popup/magnific-popup.css" />
        <script src="<?php echo $rutahead;?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>

        <script src="js/adjuntos_preview.js"></script>

         


    </head>
    <body>

        <div class="container-fluid">
          <div class="row">
            <div class="image-container">
                <img src="images/slide.jpg" alt="slidebg1" data-bgrepeat="no-repeat">
            </div>
         </div>

            <div class="headernav">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-1 col-xs-3 col-sm-2 col-md-2 logo "><a href="index.php"><img class="logoVV" src="images/Logo_VV.png" alt=""  /></a></div>
                        <div class="col-lg-3 col-xs-9 col-sm-5 col-md-3 selecttopic">
                            <div class="dropdown">
                            
                            </div>
                        </div>
                        <div class="col-lg-4 search hidden-xs hidden-sm col-md-3">
                            <div class="wrap">
                                <form action="#" method="post" class="form">
                                    <div class="pull-left txt"><input type="text" id="buscar" class="form-control" placeholder="Buscar Tema"></div>
                                    <div class="pull-right"><button class="btn btn-default" onclick="SearchTema()" type="button"><i class="fa fa-search"></i></button></div>
                                    <div class="clearfix"></div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-4 col-xs-12 col-sm-5 col-md-4 avt">
                            <div class="stnt pull-left">                            
                                <form action="new_topic.php" method="post" class="form">
                                    <button class="btn btn-primary">Crear Nuevo Tema</button>
                                </form>
                            </div>
                            
                             <div class="env pull-left"> -- <!-- <i class="fa fa-envelope"></i>--></div>

                            <div class="avatar pull-left dropdown">
                                <a data-toggle="dropdown" href="#">

                                    <!--<img src="images/avatar.png" alt="" /> -->
                                    <div class="circle"><? echo $filial ?></div>

                                </a> <b class="caret-custom"></b>
                                <div class="status green">&nbsp;</div>
                                <ul class="dropdown-menu" role="menu">
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#" onclick="logout();">Salir</a></li>
                                    
                                </ul>
                            </div>
                            
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>

