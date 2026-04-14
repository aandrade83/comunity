<?php
include('head.php');
?>

<section class="content">
  <div class="container">

    <!-- Breadcrumb -->
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver">&nbs;</span> Crear Servicio
      </div>
    </div>

    <div class="row">
      <!-- Main -->
      <div class="col-lg-8 col-md-8">

        <div class="post">
          <form id="frm_new" action="#" method="post" class="form newtopic" autocomplete="off">

            <div class="topwrap">
              <div class="userinfo pull-left">
                <div class="avatar">
                  <div class="circle"><?php echo htmlspecialchars($filial ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="status green">&nbsp;</div>
                </div>
                <div class="icons">
                  <img src="images/icon1.png" alt="" />
                  <img src="images/icon2.png" alt="" />
                  <img src="images/icon3.png" alt="" />
                  <img src="images/icon4.png" alt="" />
                </div>
              </div>

              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Crear Tema</div>

                  <div class="form-group" style="margin-top:10px;">
                    <input type="text" name="title" id="title" class="form-control" placeholder="Digite el Título" />
                  </div>

                  <?php if (!empty($isServices)) { ?>
                    <div class="form-group" style="margin-top:6px;">
                      <label style="font-weight:600; margin-bottom:6px; display:block;">
                        Tipo <span class="text-muted" style="font-weight:400;">(Interno = un servicio del Condominio, Local = un servicio cerca de la zona)</span>
                      </label>
                      <label style="margin-right:12px; font-weight:400;">
                        <input type="radio" name="tipo" value="int" checked> Interno
                      </label>
                      <label style="font-weight:400;">
                        <input type="radio" name="tipo" value="ext"> Local
                      </label>
                    </div>
                  <?php } ?>

                  <div class="form-group" style="margin-top:10px;">
                    <select name="category" id="category" class="form-control">
                      <option value="">Seleccione Categoría</option>
                      <?php if (!empty($categories)) { foreach ($categories as $c) { ?>
                        <option value="<?php echo (int)($c->vars['id'] ?? 0); ?>">
                          <?php echo htmlspecialchars($c->vars['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                      <?php } } ?>
                    </select>
                    <?php if (!empty($isServices)) { ?>
                      <small class="text-muted">Si no está tu categoría, escribila y presioná Enter para crearla.</small>
                    <?php } ?>
                  </div>

                  <div class="form-group" style="margin-top:10px;">
                    <textarea name="desc" id="desc" class="form-control" rows="10" placeholder="Detalle"></textarea>
                  </div>

                  <div style="margin-top:18px;">
                    <div style="font-weight:600; margin-bottom:6px;">Adjuntar archivos</div>
                    <div id="dzAdjuntos" class="dropzone"></div>
                    <small class="text-muted">Puede subir múltiples archivos: imágenes, PDF, Word, Excel, TXT.</small>
                  </div>

                </div>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="postinfobot">
              <div class="pull-right postreply">
                <button type="submit" class="btn btn-primary">Publicar</button>
              </div>
              <div class="clearfix"></div>
            </div>

          </form>
        </div>

      </div>

      <?php include('side.php'); ?>

    </div>

  </div>
</section>

<?php include('footer.php'); ?>
