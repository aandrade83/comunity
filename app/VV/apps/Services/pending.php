<?php
include('../ui/main_head.php');

if ($_SESSION['rol'] <= 1) {
  header("Location: index.php");
  exit;
}

$servicios = get_pending_servicios();
?>

<section class="content">

  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver"> - </span>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-md-8">
        <h3>SERVICIOS PENDIENTES DE REVISIÓN</h3>

        <?php if (!empty($servicios)): ?>
          <?php foreach ($servicios as $servicio): ?>
            <div class="post">
              <div class="wrap-ut pull-left">
                <div class="userinfo pull-left">
                  <div class="circle"><?php echo htmlspecialchars($servicio->vars['info']->vars['filial'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <?php echo htmlspecialchars($servicio->vars['info']->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="posttext pull-left">
                  <h2><a href="topic_pending.php?t=<?php echo (int)$servicio->vars['id']; ?>"><?php echo htmlspecialchars($servicio->vars['titulo'], ENT_QUOTES, 'UTF-8'); ?></a></h2>
                  <p><?php echo htmlspecialchars(mb_substr($servicio->vars['detalle'], 0, 200), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="clearfix"></div>
              </div>
              <div class="pull-left">
                <a href="topic_pending.php?t=<?php echo (int)$servicio->vars['id']; ?>">
                  <button type="button" style="margin-top:50px;" class="btn btn-primary">Revisar</button>
                </a>
              </div>
              <div class="clearfix"></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="post">
            <div class="wrap-ut pull-left">
              <div class="posttext pull-left">
                <h2>No hay servicios pendientes</h2>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
          </div>
        <?php endif; ?>
      </div>

      <?php include('../ui/partials/side.php'); ?>
    </div>
  </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
