<?php
include('../ui/main_head.php');

$servicio_id = (int)param('t');
$servicio    = get_servicio($servicio_id);

// Solo el creador o comisión puede editar
if (empty($servicio->vars['id']) || ($id_user != $servicio->vars['creador'] && $_SESSION['rol'] <= 1)) {
  header("Location: index.php");
  exit;
}

$categorias_servicios = get_all_categorias_servicios();
$adjuntos_actuales    = get_adjuntos_servicio($servicio_id, "tema");

function esc_e($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function adj_url_edit($f) {
  return "/VV/apps/Services/uploads/adjuntos/" . rawurlencode((string)$f);
}
?>
<section class="content">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver"></span>
        <a href="topic.php?t=<?php echo $servicio_id; ?>">Servicio</a> <span class="diviver"></span> Editar
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 col-md-8">
        <div class="post">
          <form id="frm_edit" action="#" class="form newtopic" method="post" autocomplete="off">
            <input type="hidden" id="topic_id" value="<?php echo $servicio_id; ?>">

            <div class="topwrap">
              <div class="userinfo pull-left">
                <div class="avatar">
                  <div class="circle"><?php echo esc_e($filial); ?></div>
                </div>
                <div class="icons"></div>
              </div>

              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Editar Servicio / Proveedor</div>

                  <div class="form-group" style="margin-top:10px;">
                    <input type="text" id="title" class="form-control"
                           placeholder="Título del servicio"
                           value="<?php echo esc_e($servicio->vars['titulo']); ?>" />
                  </div>

                  <div class="form-group" style="margin-top:8px;">
                    <label style="font-weight:600; margin-bottom:4px; display:block;">Tipo</label>
                    <select id="tipo" name="tipo" class="form-control">
                      <option value="int" <?php if ($servicio->vars['tipo'] === 'int') echo 'selected'; ?>>Vecinos (servicio de un vecino)</option>
                      <option value="ext" <?php if ($servicio->vars['tipo'] === 'ext') echo 'selected'; ?>>Local (negocio / proveedor de la zona)</option>
                    </select>
                  </div>

                  <div class="form-group" style="margin-top:8px;">
                    <label style="font-weight:600; margin-bottom:4px; display:block;">Categoría</label>
                    <select id="category" name="category" class="form-control">
                      <option value="">Seleccione Categoría</option>
                      <?php foreach ($categorias_servicios as $cat): ?>
                        <option value="<?php echo (int)$cat->vars['id']; ?>"
                          <?php if ((int)$cat->vars['id'] === (int)$servicio->vars['id_categoria']) echo 'selected'; ?>>
                          <?php echo esc_e($cat->vars['nombre']); ?>
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
                           placeholder="Ej: 88881234" maxlength="15"
                           value="<?php echo esc_e($servicio->vars['telefono'] ?? ''); ?>" />
                  </div>

                  <div class="form-group" style="margin-top:10px;">
                    <textarea id="desc" class="form-control" rows="8"
                              placeholder="Describa el servicio o proveedor"><?php echo esc_e($servicio->vars['detalle']); ?></textarea>
                  </div>

                  <!-- ============================
                       ADJUNTOS ACTUALES
                  ============================= -->
                  <div class="form-group" style="margin-top:18px;">
                    <label style="font-weight:600; margin-bottom:8px; display:block;">Archivos adjuntos</label>

                    <div id="adj-grid" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                      <?php foreach ($adjuntos_actuales as $a):
                        $file = (string)($a->vars['nombre_archivo'] ?? '');
                        if (!$file) continue;
                        $orig = (string)($a->vars['nombre_original'] ?? $file);
                        $mime = (string)($a->vars['mime_type'] ?? '');
                        $ext  = (string)($a->vars['extension'] ?? '');
                        $adjId = (int)($a->vars['id'] ?? 0);
                        $url  = adj_url_edit($file);
                        $isImg = $mime && strpos($mime, 'image/') === 0;
                      ?>
                        <div class="adj-edit-item" id="adj-item-<?php echo $adjId; ?>"
                             style="width:120px; position:relative;">
                          <?php if ($isImg): ?>
                            <img src="<?php echo esc_e($url); ?>" alt="<?php echo esc_e($orig); ?>"
                                 style="width:120px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #ddd; display:block;">
                          <?php else: ?>
                            <div style="width:120px; height:90px; border:1px solid #ddd; border-radius:6px;
                                        display:flex; align-items:center; justify-content:center;
                                        background:#f7f7f7; font-size:13px; font-weight:600; color:#666;">
                              <?php echo esc_e($ext ? strtoupper($ext) : 'FILE'); ?>
                            </div>
                          <?php endif; ?>
                          <div style="font-size:11px; margin-top:4px; white-space:nowrap; overflow:hidden;
                                      text-overflow:ellipsis; color:#555;" title="<?php echo esc_e($orig); ?>">
                            <?php echo esc_e($orig); ?>
                          </div>
                          <!-- Botón eliminar -->
                          <button type="button"
                                  class="btn-del-adj"
                                  data-adj-id="<?php echo $adjId; ?>"
                                  title="Eliminar"
                                  style="position:absolute; top:2px; right:2px;
                                         background:rgba(0,0,0,0.55); border:none; border-radius:50%;
                                         width:22px; height:22px; line-height:20px; text-align:center;
                                         color:#fff; font-size:13px; cursor:pointer; padding:0;">
                            &times;
                          </button>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <!-- Dropzone para nuevos archivos -->
                    <div id="dzEditAdjuntos" class="dropzone"
                         style="min-height:80px; border:2px dashed #ccc; border-radius:6px; padding:10px;"></div>
                    <small class="text-muted">Puede subir imágenes, PDF, Word, Excel, TXT (máx. 16 MB por archivo).</small>
                  </div>

                </div>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="postinfobot">
              <div class="pull-left">
                <a href="topic.php?t=<?php echo $servicio_id; ?>" class="btn btn-default">Cancelar</a>
              </div>
              <div class="pull-right postreply">
                <button id="btn_edit" type="submit" class="btn btn-primary">Guardar cambios</button>
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

<script>
jQuery(function($){
  var API    = BASE_URL + '/apps/Services/actions/actions.php';
  var TOPIC  = <?php echo $servicio_id; ?>;

  // ==========================================
  // ELIMINAR ADJUNTO EXISTENTE
  // ==========================================
  $(document).on('click', '.btn-del-adj', function(){
    var adjId = $(this).data('adj-id');
    var $item = $('#adj-item-' + adjId);

    Swal.fire({
      title: '¿Eliminar archivo?',
      text: 'Esta acción no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#e13f28',
    }).then(async function(result){
      if (!result.isConfirmed) return;
      try {
        var fd = new FormData();
        fd.append('adj_id', adjId);
        var r    = await fetch(API + '?ac=deleteAdjunto', {method:'POST', body:fd});
        var text = await r.text();
        var data;
        try { data = JSON.parse(text); } catch(e){ throw new Error('Respuesta no es JSON'); }
        if (String(data.control) === '1') {
          $item.fadeOut(200, function(){ $(this).remove(); });
        } else {
          swalError(data.error || 'No se pudo eliminar');
        }
      } catch(err) {
        swalError('Error al eliminar el archivo');
      }
    });
  });

  // ==========================================
  // DROPZONE — subir nuevos archivos
  // ==========================================
  if (typeof window.Dropzone !== 'undefined') {
    var dz = new Dropzone('#dzEditAdjuntos', {
      url: API + '?ac=addAdjuntos',
      method: 'post',
      autoProcessQueue: true,
      uploadMultiple: false,
      parallelUploads: 3,
      addRemoveLinks: true,
      timeout: 120000,
      paramName: 'adjuntos',
      maxFilesize: 16,
      acceptedFiles: 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt',
      dictDefaultMessage: 'Arrastrá archivos aquí o hacé click para agregar',
      dictRemoveFile: 'Quitar',
      sending: function(file, xhr, formData){
        formData.append('t', TOPIC);
      },
      success: function(file, resp){
        var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
        if (!data || String(data.control) !== '1') {
          swalError((data && data.error) || 'Error al subir');
          return;
        }
        // Agregar miniatura al grid por cada adjunto devuelto
        var adjs = data.adjuntos || [];
        adjs.forEach(function(a){
          var url  = '/VV/apps/Services/uploads/adjuntos/' + encodeURIComponent(a.nombre_archivo);
          var isImg = a.mime_type && a.mime_type.indexOf('image/') === 0;
          var thumb = isImg
            ? '<img src="'+url+'" style="width:120px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #ddd;display:block;">'
            : '<div style="width:120px;height:90px;border:1px solid #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#f7f7f7;font-size:13px;font-weight:600;color:#666;">'+(a.extension?a.extension.toUpperCase():'FILE')+'</div>';
          var label = '<div style="font-size:11px;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#555;" title="'+a.nombre_original+'">'+a.nombre_original+'</div>';
          var btn   = '<button type="button" class="btn-del-adj" data-adj-id="'+a.id+'" title="Eliminar" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:22px;height:22px;line-height:20px;text-align:center;color:#fff;font-size:13px;cursor:pointer;padding:0;">&times;</button>';
          var html  = '<div class="adj-edit-item" id="adj-item-'+a.id+'" style="width:120px;position:relative;">'+thumb+label+btn+'</div>';
          $('#adj-grid').append(html);
        });
        // Limpiar el archivo del dropzone UI
        dz.removeFile(file);
      },
      error: function(file, message){
        var data = (typeof message === 'object') ? message : null;
        swalError((data && data.error) || (typeof message === 'string' ? message : 'Error al subir'));
        dz.removeFile(file);
      }
    });
  }

  // ==========================================
  // SELECT2 — categoría editable
  // ==========================================
  if ($.fn.select2) {
    var $sel = $('#category');

    function existsByText(txt) {
      var t = (txt||'').trim().toLowerCase();
      var found = false;
      $sel.find('option').each(function(){
        if (($(this).text()||'').trim().toLowerCase() === t) found = true;
      });
      return found;
    }

    async function createCategory(nombre) {
      var fd = new FormData();
      fd.append('nombre', nombre);
      var r    = await fetch(API + '?ac=newCat', {method:'POST', body:fd});
      var text = await r.text();
      var data;
      try { data = JSON.parse(text); } catch(e){ throw new Error('Respuesta NO es JSON: '+text); }
      var id = data.id || data.ID || data.Id;
      if (id) return id;
      throw new Error(data.error || 'No se pudo crear la categoría');
    }

    $sel.select2({
      placeholder: 'Seleccione Categoría',
      width: '100%',
      tags: true,
      createTag: function(params){
        var term = (params.term||'').trim();
        if (!term || existsByText(term)) return null;
        return {id:'__new__:'+term, text:'Crear: "'+term+'"', newTag:true, term:term};
      },
      templateSelection: function(d){ return d && d.newTag ? d.term : d.text; }
    });

    $sel.on('select2:select', async function(e){
      var d = e.params.data;
      if (!d || !d.newTag) return;
      var nombre = (d.term||'').trim();
      if (!nombre) return;
      try {
        var newId = await createCategory(nombre);
        $sel.find('option[value="'+d.id.replace(/"/g,'\\"')+'"]').remove();
        $sel.append(new Option(nombre, newId, true, true)).trigger('change');
        swalOk('Listo', 'Categoría creada');
      } catch(err) {
        $sel.find('option[value="'+d.id.replace(/"/g,'\\"')+'"]').remove();
        $sel.val(null).trigger('change');
        swalError(err.message || 'No se pudo crear la categoría');
      }
    });
  }

  // ==========================================
  // GUARDAR CAMBIOS (campos de texto)
  // ==========================================
  $('#frm_edit').off('submit').on('submit', async function(e){
    e.preventDefault();

    var title    = $.trim($('#title').val());
    var cat      = $.trim($('#category').val());
    var desc     = $.trim($('#desc').val());
    var tipo     = $.trim($('#tipo').val());
    var telefono = $.trim($('#telefono').val());
    var id       = $.trim($('#topic_id').val());

    if (!title || !cat || !desc) {
      swalError('Todos los campos son obligatorios');
      return;
    }

    try {
      var fd = new FormData();
      fd.append('t',        id);
      fd.append('title',    title);
      fd.append('c',        cat);
      fd.append('desc',     desc);
      fd.append('tipo',     tipo);
      fd.append('telefono', telefono);

      var r    = await fetch(API + '?ac=editTopic', {method:'POST', body:fd});
      var text = await r.text();
      var data;
      try { data = JSON.parse(text); } catch(ex){ throw new Error('Respuesta NO es JSON: '+text); }

      if (String(data.control) === '1') {
        $('#btn_edit').prop('disabled', true);
        swalOk('¡Listo!', 'El servicio fue actualizado.');
        setTimeout(function(){ window.location.href = 'topic.php?t=' + id; }, 1500);
      } else {
        swalError(data.error || 'Hubo un error');
      }
    } catch(err) {
      swalError('A system error was detected');
    }
  });
});
</script>

<?php include('../ui/partials/footer.php'); ?>
