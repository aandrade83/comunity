<?php
session_start();

$filial  = $_SESSION['filial'] ?? '';
$id_user = $_SESSION['user'] ?? 0;

$comision = false;
if (isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 2) {
  $comision = true;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/VV/utilities/includes.php");
// BASE_URL y ASSETS_URL quedan disponibles desde config.php (cargado por includes.php)

$Categorias = get_all_categorias();
$total      = get_total_categoria();

// Detectar módulo activo (para highlight de tabs)
$uri        = $_SERVER['REQUEST_URI'] ?? '';
$isForum    = (strpos($uri, '/VV/apps/Forum/')    !== false);
$isServices = (strpos($uri, '/VV/apps/Services/') !== false);
if (!$isForum && !$isServices) $isForum = true; // fallback
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Condominio:: Valle Verde</title>

  <!-- Bootstrap -->
  <link href="<?= BASE_URL ?>/apps/ui/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom -->
  <link href="<?= BASE_URL ?>/apps/ui/css/custom.css" rel="stylesheet">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Fonts -->
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="<?= BASE_URL ?>/apps/ui/font-awesome-4.0.3/css/font-awesome.min.css">

  <!-- CSS STYLE -->
  <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/apps/ui/css/style.css" media="screen" />

  <!-- Slider Revolution CSS -->
  <link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>/apps/ui/rs-plugin/css/settings.css" media="screen" />

  <!-- Contexto de ambiente — disponible en todo el JS como BASE_URL -->
  <script>var BASE_URL = "<?= BASE_URL ?>";</script>

  <script type="text/javascript" src="<?= BASE_URL ?>/utilities/includes/js/jquery.js"></script>
  <script src="<?= BASE_URL ?>/apps/ui/js/bootstrap.min.js"></script>

  <!-- SweetAlert2 (local) -->
  <script src="<?= ASSETS_URL ?>/libs/sweetalert2/sweetalert2.min.js"></script>
  <link href="<?= ASSETS_URL ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">

  <!-- Dropzone (local) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/dropzone/dropzone.min.css">
  <script src="<?= ASSETS_URL ?>/libs/dropzone/dropzone.min.js"></script>

  <!-- App JS -->
  <script type="text/javascript" src="<?= BASE_URL ?>/apps/ui/js/functions.js"></script>

  <!-- Magnific Popup (local) -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/magnific-popup/magnific-popup.css" />
  <script src="<?= ASSETS_URL ?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Preview adjuntos -->
  <script src="<?= BASE_URL ?>/apps/ui/js/adjuntos_preview.js"></script>
</head>

<body>

<div class="container-fluid">
  <div class="row">
    <div class="image-container">
      <img src="<?= BASE_URL ?>/apps/ui/images/slide.jpg" alt="slidebg1" data-bgrepeat="no-repeat">
    </div>
  </div>

  <div class="headernav">
    <div class="container">

      <!-- ROW 1: Tabs (izq) + Usuario (der) -->
      <div class="vv-topbar">
        <div class="vv-top-left">
          <ul class="nav nav-pills vv-tabs">
            <li role="presentation" class="<?php echo ($isForum ? 'active' : ''); ?>">
              <a href="<?= BASE_URL ?>/apps/Forum/index.php">FORO</a>
            </li>
            <li role="presentation" class="<?php echo ($isServices ? 'active' : ''); ?>">
              <a href="<?= BASE_URL ?>/apps/Services/index.php">PROVEEDORES</a>
            </li>
          </ul>
        </div>

        <div class="vv-top-right vv-userbox">
          <div class="avatar vv-avatar">
            <div class="circle"><?php echo htmlspecialchars((string)$filial); ?></div>
          </div>
          <a href="<?= BASE_URL ?>/apps/login/logout.php" class="btn btn-default vv-logout">Salir</a>
        </div>
      </div>

    </div>
  </div>
