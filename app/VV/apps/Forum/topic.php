<?

 include('../ui/main_head.php');

$tema = get_topic(param('t'));

$likes = get_total_tema_likes($tema->vars['id']);
$unlikes = get_total_tema_unlikes($tema->vars['id']);
$like_users = get_users_tema_likes($tema->vars['id']);

$hide = false;
$hide_resp = "";

// Parche para esconder el tema del porton , solo visible para las 22 personas que ya lo vieron
if ($tema->vars['id'] == 23) {

    $hide_resp = 57;  

    $id_users = [ 
        33, 45, 16, 46, 54, 43, 53, 1, 65, 86, 99,
        59, 88, 85, 61, 36, 83, 79, 70, 37, 26, 39
    ];

    if (in_array($id_user, $id_users)) {
        $hide = true;
    }
}

if($comision){
  $users_likes_list = get_users_list_tema_likes($tema->vars['id'],1);
  $users_unlikes_list = get_users_list_tema_likes($tema->vars['id'],0);
  $users_like['usuarios'] = $users_likes_list['usuarios'].",".$users_unlikes_list['usuarios'];
} else {
  $users_like = get_users_list_tema_likes($tema->vars['id']);
}
$usuarios_array = explode(',', $users_like['usuarios']);

$liked = false;
if (in_array($id_user, $usuarios_array)) {
  $liked = true;
}

$adjuntos = get_adjuntos_tema(param('t'),"tema");
$adj = !empty($adjuntos);

// ===== Helpers adjuntos =====
function esc($s){
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function adj_url($nombre_archivo){
  // relativo para que funcione en cualquier dominio/entorno
  return "/VV/apps/Forum/uploads/adjuntos/".rawurlencode((string)$nombre_archivo);
}
function is_img_mime($mime){
  return (is_string($mime) && strpos($mime, 'image/') === 0);
}
function is_pdf_mime($mime){
  return ($mime === 'application/pdf');
}
function render_adjuntos_block($adjuntosList, $title = 'Adjuntos'){
  if (empty($adjuntosList)) return;

  echo '<div class="adjuntos-preview-block" style="margin-top:10px;">';
  echo '<div style="font-weight:600; margin-bottom:6px;">'.esc($title).'</div>';
  echo '<div class="adjuntos-grid" style="display:flex; flex-wrap:wrap; gap:10px;">';

  foreach($adjuntosList as $a){
    $vars = isset($a->vars) ? $a->vars : [];
    $orig = $vars['nombre_original'] ?? ($vars['nombre'] ?? 'archivo');
    $file = $vars['nombre_archivo'] ?? ($vars['archivo'] ?? '');
    $mime = $vars['mime_type'] ?? ($vars['mime'] ?? '');
    $ext  = $vars['extension'] ?? '';

    if (!$file) continue;

    $url = adj_url($file);

    $dataMime = esc($mime);
    $dataExt  = esc($ext);
    $dataName = esc($orig);
    $dataUrl  = esc($url);

    echo '<div class="adj-item" style="width:130px;">';

    // Imagen => miniatura clickeable
    if (is_img_mime($mime)) {
      echo '<a href="javascript:void(0)" class="adj-open" data-url="'.$dataUrl.'" data-mime="'.$dataMime.'" data-name="'.$dataName.'" data-ext="'.$dataExt.'" style="text-decoration:none;">';
      echo '<img src="'.$dataUrl.'" alt="'.$dataName.'" style="width:130px; height:90px; object-fit:cover; border-radius:8px; border:1px solid #e6e6e6;">';
      echo '</a>';
      echo '<div style="font-size:12px; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="'.$dataName.'">'.$dataName.'</div>';
    } else {
      // No imagen => tarjeta simple (abre modal igual)
      echo '<div style="border:1px solid #e6e6e6; border-radius:8px; padding:10px; height:90px; display:flex; align-items:center; justify-content:center; background:#fafafa;">';
      echo '<a href="javascript:void(0)" class="adj-open" data-url="'.$dataUrl.'" data-mime="'.$dataMime.'" data-name="'.$dataName.'" data-ext="'.$dataExt.'" style="text-decoration:none; font-size:12px; text-align:center;">';
      echo esc($ext ? strtoupper($ext) : 'FILE');
      echo '</a>';
      echo '</div>';
      echo '<div style="font-size:12px; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="'.$dataName.'">'.$dataName.'</div>';
    }

    // Link descarga directo
    echo '<div style="margin-top:4px;">';
    echo '<a href="'.$dataUrl.'" download style="font-size:12px;">Descargar</a>';
    echo '</div>';

    echo '</div>';
  }

  echo '</div>';
  echo '</div>';
}
?>

<section class="content">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 breadcrumbf">
        <a href="index.php">Inicio</a> <span class="diviver"></span>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-md-8">
        <?
          $fecha_hora = $tema->vars['fecha'];
          $fecha_hora_obj = new DateTime($fecha_hora);
          $fecha_formateada = $fecha_hora_obj->format('d M Y @ h:ia');
        ?>

        <!-- POST -->
        <div class="post">
          <div class="topwrap">
            <div class="userinfo pull-left">
              <div class="circle"><? echo $tema->vars['info']->vars['filial']?></div>
              <? echo $tema->vars['info']->vars['nombre']?>
            </div>

            <div class="posttext pull-left">
              <input id="topic" type="hidden" value="<? echo $tema->vars['id'] ?>">
              <input id="user" type="hidden" value="<? echo $id_user ?>">

              <h2><a href="#"><? echo $tema->vars['titulo'] ?></a></h2>
              <p><? echo $tema->vars['detalle'] ?></p>

              <?
                // ✅ Adjuntos del tema
                render_adjuntos_block($adjuntos, 'Adjuntos del tema');
              ?>
            </div>

            <div class="clearfix"></div>
          </div>

          <div class="postinfobot">
            <?
              $t_likes = 0; $t_unlikes = 0;
              if(isset($likes['total'])){ $t_likes = $likes['total'];}
              if(isset($unlikes['total'])){ $t_unlikes = $unlikes['total'];}
            ?>

            <div class="likeblock pull-left">
              <i <? if($comision) { ?>title="<? echo $users_likes_list['usuarios']?>"; <? } ?>
                 onclick="likes(1);" id="lu_<? echo $tema->vars['id'] ?>"
                 class="fa fa-thumbs-o-up up hover-link <? if($liked) {?> disabled-link <? } ?>"></i>
              <span class="up" id="slu_<? echo $tema->vars['id'] ?>"><? echo $t_likes ?></span>

              <i <? if($comision) { ?>title="<? echo $users_unlikes_list['usuarios']?>"; <? } ?>
                 onclick="likes(0);" id="ld_<? echo $tema->vars['id'] ?>"
                 class="fa fa-thumbs-o-down down hover-link <? if($liked) {?> disabled-link <? } ?>"></i>
              <span class="down" id="sld_<? echo $tema->vars['id'] ?>"><? echo $t_unlikes ?></span>

              <? if($comision) { ?>
                 <ul id="dynamic-res-<?php echo (int)$tema->vars['id']; ?>" class="number-list"></ul>
              <? } ?>
            </div>

            <div class="prev pull-left"></div>
            <div class="posted pull-left"><i class="fa fa-clock-o"></i><? echo $fecha_formateada ?></div>
            <div class="clearfix"></div>
          </div>
        </div><!-- POST -->

        <!-- RESPUESTAS -->
        <? if(count($tema->vars['respuestas']) >= 1){ ?>
          <? foreach ($tema->vars['respuestas'] as $r) { ?>
              <? if($r->vars['id'] != $hide_resp || $hide == true ) { ?>  
            <?
              // ✅ Adjuntos de la respuesta (como ya lo tenías)
              $adjuntosResp = get_adjuntos_tema($r->vars['id'],"respuesta");
              $adjresp = !empty($adjuntosResp);

              $likes = get_total_respuesta_likes($r->vars['id']);
              $unlikes = get_total_respuesta_unlikes($r->vars['id']);

              if($comision){
                $users_likes_list = get_users_list_respuesta_likes($r->vars['id'],1);
                $users_unlikes_list = get_users_list_respuesta_likes($r->vars['id'],0);
                $users_like['usuarios'] = $users_likes_list['usuarios'].",".$users_unlikes_list['usuarios'];
              } else {
                $users_like = get_users_list_respuesta_likes($r->vars['id']);
              }
              $usuarios_array = explode(',', $users_like['usuarios']);

              $liked = false;
              if (in_array($id_user, $usuarios_array)) $liked = true;

              $t_likes = 0; $t_unlikes = 0;
              if(isset($likes['total'])){ $t_likes = $likes['total'];}
              if(isset($unlikes['total'])){ $t_unlikes = $unlikes['total'];}

              $fecha_hora = $r->vars['fecha'];
              $fecha_hora_obj = new DateTime($fecha_hora);
              $fecha_formateada = $fecha_hora_obj->format('d M Y @ h:ia');
            ?>

            <div class="post">
              <div class="topwrap">
                <div class="userinfo pull-left">
                  <div class="circle2"><? echo $r->vars['info']->vars['filial']?></div>
                  <? echo $r->vars['info']->vars['nombre']?>
                </div>

                <div class="posttext pull-left">
                  <p><? echo nl2br($r->vars['detalle']) ?></p>

                  <?
                    // ✅ Adjuntos de la respuesta
                    render_adjuntos_block($adjuntosResp, 'Adjuntos de la respuesta');
                  ?>
                </div>

                <div class="clearfix"></div>
              </div>

              <div class="postinfobot">
                <div class="likeblock pull-left">
                  <i <? if($comision) { ?>title="<? echo $users_likes_list['usuarios']?>"; <? } ?>
                     onclick="Rlikes(1,<? echo $r->vars['id'] ?>);" id="rlu_<? echo $r->vars['id'] ?>"
                     class="fa fa-thumbs-o-up up hover-linkR <? if($liked) {?> disabled-link <? } ?>"></i>
                  <span class="up" id="rslu_<? echo $r->vars['id'] ?>"><? echo $t_likes ?></span>

                  <i <? if($comision) { ?>title="<? echo $users_unlikes_list['usuarios']?>"; <? } ?>
                     onclick="Rlikes(0,<? echo $r->vars['id'] ?>);" id="rld_<? echo $r->vars['id'] ?>"
                     class="fa fa-thumbs-o-down down hover-linkR <? if($liked) {?> disabled-link <? } ?>"></i>
                  <span class="down" id="rsld_<? echo $r->vars['id'] ?>"><? echo $t_unlikes ?></span>

                  <? if($comision) { ?>
                    <ul id="dynamic-res" class="number-list"></ul>
                  <? } ?>
                </div>

                <div class="prev pull-left"></div>
                <div class="posted pull-left"><i class="fa fa-clock-o"></i><? echo $fecha_formateada ?></div>
                <div class="clearfix"></div>
              </div>
            </div>
          <? } ?>
          <? } ?>
        <? } ?>

        <!-- FORM RESPONDER -->
        <div class="post">
          <form id="frm_topic" action="#" class="form" method="post">
            <div class="topwrap">
              <div class="userinfo pull-left">
                <? if(isset($r->vars['id'])) { ?>
                  <input id="Rtopic" type="hidden" value="<? echo $r->vars['id'] ?>">
                <? } ?>
                <div class="circle"><? echo $filial ?></div>
                <div class="icons"></div>
              </div>

              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Agregue su comentario </div>
                  <textarea name="reply" id="reply" placeholder="Digite su Mensaje acá"></textarea>

                  <!-- Si ya tenés Dropzone para reply, este div lo aprovecha functions.js -->
                  <div style="margin-top:12px;">
                    <div style="font-weight:600; margin-bottom:6px;">Adjuntar archivos</div>
                    <div id="dzReplyAdjuntos" class="dropzone"></div>
                    <small class="text-muted">Puede subir múltiples archivos: imágenes, PDF, Word, Excel, TXT.</small>
                  </div>

                </div>
              </div>

              <div class="clearfix"></div>
            </div>

            <div class="postinfobot">
              <? if($tema->vars['estado'] > 1){ ?>
                <div class="pull-left closed">
                  <p class="closeTopic closed"> TEMA CERRADO </p>
                </div>
              <? } else { ?>
                <div class="pull-right postreply">
                  <p style="display:none" class="closeTopic closed"> TEMA CERRADO </p>
                  <div class="pull-left active">
                    <button type="submit" id="btn_post" class="btn btn-primary">Guardar</button>
                  </div>

                  <? if($_SESSION['rol'] > 1 || $_SESSION['user'] == $tema->vars['creador'] ) { ?>
                    <div class="pull-left active">
                      <button style="background-color:#e13f28;margin-left:15px;" type="button" onclick="closeTopicControl();" class="btn btn-primary">Cerrar Tema</button>
                    </div>
                  <? } ?>

                  <div class="clearfix"></div>
                </div>
              <? } ?>

              <div class="clearfix"></div>
            </div>
          </form>
        </div><!-- POST -->

      </div>

      <? include('../ui/partials/side.php'); ?>

    </div>
  </div>
</section>

<!-- ✅ Modal Preview (Bootstrap) -->
<div class="modal fade" id="adjPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adjPreviewTitle" style="margin:0;">Archivo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body" style="min-height:300px;">
        <img id="adjPreviewImg" src="" alt="" style="max-width:100%; display:none; margin:auto;">
        <iframe id="adjPreviewFrame" src="" style="width:100%; height:65vh; border:0; display:none;"></iframe>
        <div id="adjPreviewGeneric" style="display:none;">
          <p>Este archivo no tiene vista previa.</p>
        </div>
      </div>

      <div class="modal-footer">
        <a id="adjPreviewDownload" class="btn btn-primary" href="#" download>Descargar</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>viewControl()</script>

<script>
// Preview sin tocar functions.js (vanilla)
(function(){
  function showModal(name, url, mime, ext){
    document.getElementById('adjPreviewTitle').textContent = name || 'Archivo';
    var img = document.getElementById('adjPreviewImg');
    var frame = document.getElementById('adjPreviewFrame');
    var generic = document.getElementById('adjPreviewGeneric');
    var dl = document.getElementById('adjPreviewDownload');

    img.style.display = 'none';
    frame.style.display = 'none';
    generic.style.display = 'none';

    dl.href = url;

    // Imagen => <img>
    if (mime && mime.indexOf('image/') === 0) {
      img.src = url;
      img.style.display = 'block';
    }
    // PDF => iframe
    else if (mime === 'application/pdf' || (ext && String(ext).toLowerCase() === 'pdf')) {
      frame.src = url;
      frame.style.display = 'block';
    }
    // Otros => sin preview
    else {
      generic.style.display = 'block';
    }

    // Bootstrap modal
    if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
      jQuery('#adjPreviewModal').modal('show');
    } else {
      // fallback: abrir en nueva pestaña si no hay modal
      window.open(url, '_blank');
    }
  }

  document.addEventListener('click', function(e){
    var a = e.target.closest('.adj-open');
    if (!a) return;
    e.preventDefault();
    showModal(
      a.getAttribute('data-name'),
      a.getAttribute('data-url'),
      a.getAttribute('data-mime'),
      a.getAttribute('data-ext')
    );
  });
})();
</script>

<? include('../ui/partials/footer.php'); ?>
