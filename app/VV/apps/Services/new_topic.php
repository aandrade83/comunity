<?php
include('../ui/main_head.php');
$categorias_servicios = get_all_categorias_servicios();
?>
<section class="content">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver"></span> Crear Servicio
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 col-md-8">
        <div class="post">
          <form id="frm_new" action="#" class="form newtopic" method="post" autocomplete="off">
            <div class="topwrap">
              <div class="userinfo pull-left">
                <div class="avatar">
                  <div class="circle"><?php echo htmlspecialchars((string)$filial, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="icons"></div>
              </div>

              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Nuevo Servicio / Proveedor</div>

                  <div class="form-group" style="margin-top:10px;">
                    <input type="text" id="title" class="form-control" placeholder="Título del servicio" />
                  </div>

                  <div class="form-group" style="margin-top:8px;">
                    <label style="font-weight:600; margin-bottom:4px; display:block;">Tipo</label>
                    <select id="tipo" name="tipo" class="form-control">
                      <option value="int">Vecinos (servicio de un vecino)</option>
                      <option value="ext">Local (negocio / proveedor de la zona)</option>
                    </select>
                  </div>

                  <div class="form-group" style="margin-top:8px;">
                    <label style="font-weight:600; margin-bottom:4px; display:block;">Categoría</label>
                    <select id="category" name="category" class="form-control">
                      <option value="">Seleccione Categoría</option>
                      <?php foreach ($categorias_servicios as $cat): ?>
                        <option value="<?php echo (int)$cat->vars['id']; ?>">
                          <?php echo htmlspecialchars($cat->vars['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Si la categoría no existe, escríbela y presioná Enter para crearla.</small>
                  </div>

                  <div class="form-group" style="margin-top:10px;">
                    <label style="font-weight:600; margin-bottom:4px; display:block;">
                      Teléfono / WhatsApp <small class="text-muted">(opcional)</small>
                    </label>
                    <input type="tel" id="telefono" class="form-control"
                           placeholder="Ej: 88881234" maxlength="15" />
                  </div>

                  <div class="form-group" style="margin-top:10px;">
                    <textarea id="desc" class="form-control" rows="8" placeholder="Describa el servicio o proveedor"></textarea>
                  </div>

                  <div class="form-group" style="margin-top:14px;">
                    <label style="font-weight:600; margin-bottom:6px; display:block;">Adjuntar archivos</label>
                    <div id="dzAdjuntos" class="dropzone"></div>
                    <small class="text-muted">Puede subir imágenes, PDF, Word, Excel, TXT.</small>
                  </div>

                </div>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="postinfobot">
              <div class="pull-right postreply">
                <button id="btn_post" type="submit" class="btn btn-primary">Publicar</button>
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
