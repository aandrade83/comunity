<?php

session_start();

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '0');


$filial  = $_SESSION['filial'] ?? '';
$id_user = $_SESSION['user'] ?? 0;

if (empty($id_user)) {
    header("Location: /VV/");
    exit;
}

$comision    = false;
$superadmin  = false;
if (isset($_SESSION['rol']) && (int)$_SESSION['rol'] >= 2) {
  $comision = true;
}
if (isset($_SESSION['rol']) && (int)$_SESSION['rol'] >= 3) {
  $superadmin = true;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/VV/utilities/includes.php");

$Categorias = get_all_categorias();
$total      = get_total_categoria();

$rutahead = ASSETS_URL; // definido en config.php, auto-detecta el ambiente

// Detectar en cuál módulo estamos (para tabs + mostrar/ocultar búsqueda y crear tema)
$uri = $_SERVER['REQUEST_URI'] ?? '';
$isForum       = (strpos($uri, '/VV/apps/Forum/') !== false);
$isServices    = (strpos($uri, '/VV/apps/Services/') !== false);
$isEncuesta    = (strpos($uri, '/VV/apps/Encuesta/') !== false);
$isActividades  = (strpos($uri, '/VV/apps/Actividades/') !== false);
$isCondominios  = (strpos($uri, '/VV/apps/Condominios/') !== false);

// Fallback: si no calza nada, asumimos Foro
if (!$isForum && !$isServices && !$isEncuesta && !$isActividades && !$isCondominios) $isForum = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Condominio:: Valle Verde</title>

  <!-- Bootstrap -->
  <link href="../ui/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom -->
  <link href="../ui/css/custom.css?v=<?= filemtime(ROOT_PATH . '/apps/ui/css/custom.css') ?>" rel="stylesheet">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- fonts -->
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="../ui/font-awesome-4.0.3/css/font-awesome.min.css">

  <!-- CSS STYLE-->
  <link rel="stylesheet" type="text/css" href="../ui/css/style.css" media="screen" />

  <!-- SLIDER REVOLUTION 4.x CSS SETTINGS -->
  <link rel="stylesheet" type="text/css" href="../ui/rs-plugin/css/settings.css" media="screen" />

  <!-- Contexto de ambiente — disponible en todo el JS como BASE_URL -->
  <script>var BASE_URL = "<?= BASE_URL ?>";</script>
  <script type="text/javascript" src="<?= BASE_URL ?>/utilities/includes/js/jquery.js"></script>
  <script src="../ui/js/bootstrap.min.js"></script>

  <!-- SweetAlert2 (local) -->
  <script src="<?= ASSETS_URL ?>/libs/sweetalert2/sweetalert2.min.js"></script>
  <link href="<?= ASSETS_URL ?>/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">

  <!-- Dropzone -->
  <link rel="stylesheet" href="<?php echo $rutahead; ?>/libs/dropzone/dropzone.min.css">
  <script src="<?php echo $rutahead; ?>/libs/dropzone/dropzone.min.js"></script>

  <!-- App JS -->
  <script type="text/javascript" src="<?= BASE_URL ?>/apps/ui/js/functions.js?v=<?= filemtime(ROOT_PATH . '/apps/ui/js/functions.js') ?>"></script>

  <!-- Magnific Popup -->
  <link rel="stylesheet" href="<?php echo $rutahead; ?>/libs/magnific-popup/magnific-popup.css" />
  <script src="<?php echo $rutahead; ?>/libs/magnific-popup/jquery.magnific-popup.min.js"></script>

  <!-- Preview adjuntos (NO toca functions.js) -->
  <script src="../ui/js/adjuntos_preview.js"></script>
  <!-- Lightbox con galería y flechas -->
  <script src="../ui/js/vv-lightbox.js"></script>

  <!-- Select2 (solo para Services) -->
  <?php if ($isServices): ?>
  <link href="<?= ASSETS_URL ?>/libs/select2/select2.min.css" rel="stylesheet">
  <script src="<?= ASSETS_URL ?>/libs/select2/select2.min.js"></script>
  <?php endif; ?>
</head>

<body>

<div class="container-fluid">
  <div class="row">
    <div class="image-container">
      <img src="../ui/images/slide.jpg" alt="slidebg1" data-bgrepeat="no-repeat">
    </div>
  </div>
<? /*
  <div class="headernav">
    <div class="container">
    <!-- ROW 1: Tabs (izq) + Usuario (der) -->
      <div class="vv-topbar">
        <div class="vv-logo">
          <a href="index.php"><img class="logoVV" src="../ui/images/Logo_VV.png" alt="" /></a>
        </div>
        <div class="vv-top-left">
          <ul class="nav nav-pills vv-tabs">
            <li role="presentation" class="<?php echo ($isForum ? 'active' : ''); ?>">
              <a href="<?= BASE_URL ?>/apps/Forum/index.php?c=1">FORO</a>
            </li>
            <li role="presentation" class="<?php echo ($isServices ? 'active' : ''); ?>">
              <a href="<?= BASE_URL ?>/apps/Services/index.php?c=1">PROVEEDORES</a>
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

  */ ?>
  <div class="headernav">
  <div class="container">

    <div class="vv-topbar">

      <!-- IZQUIERDA: Logo + botón hamburguesa (móvil) -->
      <div class="vv-left">
        <div class="vv-logo">
          <a href="index.php">
            <img class="logoVV" src="../ui/images/Logo_VV.png" alt="" />
          </a>
        </div>

        <!-- Hamburguesa — solo visible en móvil -->
        <button class="vv-hamburger" id="vv-hamburger" type="button" aria-label="Menú" aria-expanded="false" aria-controls="vv-nav-menu">
          <span></span><span></span><span></span>
        </button>

        <ul class="nav nav-pills vv-tabs" id="vv-nav-menu">
          <li role="presentation" class="<?php echo ($isForum ? 'active' : ''); ?>">
            <a href="<?= BASE_URL ?>/apps/Forum/index.php">FORO</a>
          </li>
          <li role="presentation" class="<?php echo ($isServices ? 'active' : ''); ?>">
            <a href="<?= BASE_URL ?>/apps/Services/index.php">SERVICIOS</a>
          </li>
          <li role="presentation" class="<?php echo ($isEncuesta ? 'active' : ''); ?>">
            <a href="<?= BASE_URL ?>/apps/Encuesta/index.php?c=1">ENCUESTAS</a>
          </li>
          <li role="presentation" class="<?php echo ($isActividades ? 'active' : ''); ?>">
            <a href="<?= BASE_URL ?>/apps/Actividades/index.php">ACTIVIDADES</a>
          </li>
          <?php if ($superadmin): ?>
          <li role="presentation" class="<?php echo ($isCondominios ? 'active' : ''); ?>">
            <a href="<?= BASE_URL ?>/apps/Condominios/index.php">CONDOMINIOS</a>
          </li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- DERECHA: Usuario + Salir -->
      <div class="vv-top-right vv-userbox">
        <div class="avatar vv-avatar">
          <div class="circle"><?php echo htmlspecialchars((string)$filial); ?></div>
        </div>
        <button type="button" class="btn btn-default vv-logout" onclick="logout();">Salir</button>
      </div>

    </div>

  </div>

  <!-- POPUP REGLAS -->
<script>
(function() {
  var btn = document.getElementById('vv-hamburger');
  var menu = document.getElementById('vv-nav-menu');
  if (!btn || !menu) return;
  btn.addEventListener('click', function() {
    var open = menu.classList.toggle('vv-nav-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.classList.toggle('vv-hamburger--open', open);
  });
  // Cerrar al navegar
  menu.querySelectorAll('a').forEach(function(a) {
    a.addEventListener('click', function() {
      menu.classList.remove('vv-nav-open');
      btn.classList.remove('vv-hamburger--open');
      btn.setAttribute('aria-expanded', 'false');
    });
  });
})();
</script>

<div id="reglasModal" class="vv-modal-overlay" style="display:none;">
    <div class="vv-modal-box">
        <div class="vv-modal-header">
            <span>Normas Comunitarias del Foro</span>
            <button onclick="closeReglasPopup()">×</button>
        </div>

        <div class="vv-modal-body">
            <iframe src="../ui/reglas.html" frameborder="0"></iframe>
        </div>
    </div>
</div>


</div>

