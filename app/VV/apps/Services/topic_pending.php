<?php
include('../ui/main_head.php');

if ($_SESSION['rol'] <= 1) {
  header("Location: index.php");
  exit;
}

$servicio = get_servicio(param('t'));
$adjuntos = get_adjuntos_servicio(param('t'), "tema");
?>

<section class="content">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver"> - </span>
        <a href="pending.php">Pendientes</a> <span class="diviver"></span>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-md-8">

        <!-- SERVICIO -->
        <div class="post">
          <div class="topwrap">
            <div class="userinfo pull-left">
              <div class="circle3"><?php echo htmlspecialchars($servicio->vars['info']->vars['filial'], ENT_QUOTES, 'UTF-8'); ?></div>
              <?php echo htmlspecialchars($servicio->vars['info']->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="posttext pull-left">
              <h2><?php echo htmlspecialchars($servicio->vars['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <p><?php echo nl2br(htmlspecialchars($servicio->vars['detalle'], ENT_QUOTES, 'UTF-8')); ?></p>

              <div class="row" style="margin-top:10px;">
                <div class="col-lg-6 col-md-6">
                  <input id="topic" type="hidden" value="<?php echo (int)$servicio->vars['id']; ?>">
                  <select name="revision" id="revision" class="form-control">
                    <option value="" disabled selected>Seleccione Revisión</option>
                    <option value="1">Aprobado</option>
                    <option value="3">Rechazado</option>
                  </select>
                </div>
              </div>

              <?php if (!empty($adjuntos)): ?>
                <div style="margin-top:12px;">
                  <div style="font-weight:600; margin-bottom:6px;">Adjuntos del servicio</div>
                  <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php foreach ($adjuntos as $a):
                      $file = (string)($a->vars['nombre_archivo'] ?? '');
                      if (!$file) continue;
                      $orig = (string)($a->vars['nombre_original'] ?? $file);
                      $mime = (string)($a->vars['mime_type'] ?? '');
                      $url  = "/VV/apps/Services/uploads/adjuntos/" . rawurlencode($file);
                      $isImg = $mime && strpos($mime, 'image/') === 0;
                    ?>
                      <?php if ($isImg): ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" style="display:inline-block;">
                          <img src="<?php echo htmlspecialchars($url); ?>" alt="<?php echo htmlspecialchars($orig); ?>"
                               style="width:92px; height:92px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                        </a>
                      <?php else: ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank"
                           style="display:inline-block; padding:10px; border:1px solid #ddd; border-radius:8px; text-decoration:none;">
                          <?php echo htmlspecialchars($orig); ?>
                        </a>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

            </div>
            <div class="clearfix"></div>
          </div>
          <div class="postinfobot">
            <div class="clearfix"></div>
          </div>
        </div>

        <!-- FORM REVISIÓN -->
        <div class="post">
          <form id="frm_pending" action="#" class="form" method="post">
            <div class="topwrap">
              <div class="userinfo pull-left">
                <div class="circle"><?php echo htmlspecialchars($filial, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="icons"></div>
              </div>
              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Agregar Comentario</div>
                  <textarea name="reply" id="reply" placeholder="Digite su mensaje acá"></textarea>
                </div>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="postinfobot">
              <div class="pull-right postreply">
                <button id="btn_pending_save" type="submit" class="btn btn-primary">Guardar</button>
              </div>
              <div class="clearfix"></div>
            </div>
          </form>
        </div>

      </div>

      <?php include('../ui/partials/side.php'); ?>
    </div>
  </div>
</section>

<?php include('../ui/partials/footer.php'); ?>
