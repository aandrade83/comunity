<?php
session_start();

$filial  = $_SESSION['filial'] ?? '';
$id_user = $_SESSION['user'] ?? 0;

$comision = false;
if (isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 2) {
  $comision = true;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/VV/utilities/includes.php");

$Categorias = get_all_categorias();
$total      = get_total_categoria();

$rutahead = "https://lab.lacallecr.com/VV/utilities/tema";

// Detectar en cuál módulo estamos (para tabs + mostrar/ocultar búsqueda y crear tema)
$uri = $_SERVER['REQUEST_URI'] ?? '';
$isForum    = (strpos($uri, '/VV/apps/Forum/') !== false);
$isServices = (strpos($uri, '/VV/apps/Services/') !== false);
$isServicesNew = (strpos($uri, '/Services/new_topic.php') !== false);


// Fallback: si no calza nada, asumimos Foro
if (!$isForum && !$isServices) $isForum = true;
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

  <link rel="stylesheet" href="<?php echo $rutahead; ?>/libs/select2/select2.min.css">
 <script src="<?php echo $rutahead; ?>/libs/select2/select2.min.js"></script>


  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- Dropzone -->
  <link rel="stylesheet" href="<?php echo $rutahead; ?>/libs/dropzone/dropzone.min.css">
  <script src="<?php echo $rutahead; ?>/libs/dropzone/dropzone.min.js"></script>

  <!-- App JS -->
  <script type="text/javascript" src="js/functions.js"></script>

  <!-- Magnific Popup -->
  <link rel="stylesheet" href="<?php echo $rutahead; ?>/libs/magnific-popup/magnific-popup.css" />
  <script src="<?php echo $rutahead; ?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Preview adjuntos (NO toca functions.js) -->
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

      <!-- ROW 1: Tabs (izq) + Usuario (der) -->
      <div class="vv-topbar">
        <div class="vv-top-left">
          <ul class="nav nav-pills vv-tabs">
            <li role="presentation" class="<?php echo ($isForum ? 'active' : ''); ?>">
              <a href="https://lab.lacallecr.com/VV/apps/Forum/index.php?c=1">FORO</a>
            </li>
            <li role="presentation" class="<?php echo ($isServices ? 'active' : ''); ?>">
              <a href="https://lab.lacallecr.com/VV/apps/Services/index.php?c=1">PROVEEDORES</a>
            </li>
             <li role="presentation" class="<?php echo ($isServices ? 'active' : ''); ?>">
              <a href="https://lab.lacallecr.com/VV/apps/Services/index.php?c=1">ENCUESTAS</a>
            </li>
          </ul>
        </div>

        <div class="vv-top-right vv-userbox">
          <div class="avatar vv-avatar">
            <div class="circle"><?php echo htmlspecialchars((string)$filial); ?></div>
          </div>
          <button type="button" class="btn btn-default vv-logout" onclick="logout();">Salir</button>
        </div>
      </div>

      <!-- ROW 2: Logo + (Solo Foro) Buscar + Crear -->
      <div class="vv-mainbar">
        <div class="vv-logo">
          <a href="index.php"><img class="logoVV" src="images/Logo_VV.png" alt="" /></a>
        </div>
<?php if ($isServices) { 
  $categories = get_all_categorias_servicios();
?>
 
  <? if(!$isServicesNew) { ?>
  <div class="form-group" style="margin:10px 0 0;">
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
      <label for="service_category" style="margin:0; font-weight:600;">Categorías:</label>

      <select id="service_category" name="service_category" class="form-control"
              style="min-width:260px; max-width:420px;">
        <option value="">— Seleccione Una Categoria</option>
        <?php foreach ($categories as $cat) { ?>
          <option value="<?php echo (int)$cat->vars['id']; ?>">
            <?php echo htmlspecialchars($cat->vars['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
          </option>
        <?php } ?>
      </select>

      
    </div>

  </div>
        <?/*
          <div class="vv-search">
            <div class="vv-searchbox">
              <input type="text" id="buscar" class="form-control" placeholder="Buscar Tema">
              <button class="btn btn-default" onclick="SearchTema()" type="button"><i class="fa fa-search"></i></button>
            </div>
          </div>*/?>

          <div class="vv-actions">
            <form action="new_topic.php" method="post" class="form" style="margin:0;">
              <button class="btn btn-primary" type="submit">Crear Nuevo Servicio</button>
            </form>
          </div>

   <? } ?>
        <?php } else { ?>
          <div class="vv-spacer"></div>
        <?php } ?>
      </div>

    </div>
  </div>
