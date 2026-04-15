<?php
include('../ui/main_head.php');

$servicio = get_servicio(param('t'));
$adjuntos = get_adjuntos_servicio(param('t'), "tema");

// ---- Likes del servicio ----
$svc_likes   = get_total_servicio_likes($servicio->vars['id']);
$svc_unlikes = get_total_servicio_unlikes($servicio->vars['id']);

if ($comision) {
  $svc_likes_list   = get_users_list_servicio_likes($servicio->vars['id'], 1);
  $svc_unlikes_list = get_users_list_servicio_likes($servicio->vars['id'], 0);
  $svc_like_users   = ['usuarios' => $svc_likes_list['usuarios'] . ',' . $svc_unlikes_list['usuarios']];
} else {
  $svc_like_users = get_users_list_servicio_likes($servicio->vars['id']);
}
$svc_liked = in_array($id_user, explode(',', $svc_like_users['usuarios'] ?? ''));

function esc($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function adj_url_svc($nombre_archivo) {
  return "/VV/apps/Services/uploads/adjuntos/" . rawurlencode((string)$nombre_archivo);
}
function is_img_mime($mime) {
  return (is_string($mime) && strpos($mime, 'image/') === 0);
}
function render_adj_svc($adjuntosList, $title = 'Adjuntos') {
  if (empty($adjuntosList)) return;
  echo '<div class="adjuntos-preview-block" style="margin-top:10px;">';
  echo '<div style="font-weight:600; margin-bottom:6px;">' . esc($title) . '</div>';
  echo '<div class="adjuntos-grid" style="display:flex; flex-wrap:wrap; gap:10px;">';
  foreach ($adjuntosList as $a) {
    $vars = isset($a->vars) ? $a->vars : [];
    $orig = $vars['nombre_original'] ?? 'archivo';
    $file = $vars['nombre_archivo'] ?? '';
    $mime = $vars['mime_type'] ?? '';
    $ext  = strtolower((string)($vars['extension'] ?? ''));
    if (!$file) continue;
    $url = adj_url_svc($file);
    echo '<div class="adj-item" style="width:130px;">';
    if (is_img_mime($mime)) {
      echo '<a href="'.esc($url).'" class="vv-attach-img" title="'.esc($orig).'" style="text-decoration:none; display:block;">';
      echo '<img src="'.esc($url).'" alt="'.esc($orig).'" style="width:130px; height:90px; object-fit:cover; border-radius:8px; border:1px solid #e6e6e6;">';
      echo '</a>';
    } elseif ($mime === 'application/pdf' || $ext === 'pdf') {
      echo '<div style="border:1px solid #e6e6e6; border-radius:8px; padding:10px; height:90px; display:flex; align-items:center; justify-content:center; background:#fafafa;">';
      echo '<a href="'.esc($url).'" class="vv-attach-pdf" title="'.esc($orig).'" style="text-decoration:none; font-size:12px; text-align:center;">PDF</a>';
      echo '</div>';
    } else {
      echo '<div style="border:1px solid #e6e6e6; border-radius:8px; padding:10px; height:90px; display:flex; align-items:center; justify-content:center; background:#fafafa;">';
      echo '<a href="'.esc($url).'" class="vv-attach-file" title="'.esc($orig).'" style="text-decoration:none; font-size:12px; text-align:center;">';
      echo esc($ext ? strtoupper($ext) : 'FILE');
      echo '</a></div>';
    }
    echo '<div style="font-size:12px; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="'.esc($orig).'">'.esc($orig).'</div>';
    echo '<div style="margin-top:4px;"><a href="'.esc($url).'" download style="font-size:12px;">Descargar</a></div>';
    echo '</div>';
  }
  echo '</div></div>';
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

        <?php
          $fecha_obj = new DateTime($servicio->vars['fecha']);
          $fecha_fmt = $fecha_obj->format('d M Y @ h:ia');
          $tipoLabel = ($servicio->vars['tipo'] === 'ext') ? 'Local' : 'Vecinos';
          $tipoClass = ($servicio->vars['tipo'] === 'ext') ? 'label-warning' : 'label-default';
        ?>

        <!-- SERVICIO -->
        <div class="post" style="position:relative;">
          <?php if ($id_user == $servicio->vars['creador'] || $_SESSION['rol'] > 1): ?>
            <a href="edit_topic.php?t=<?php echo (int)$servicio->vars['id']; ?>"
               title="Editar servicio"
               style="position:absolute; top:10px; right:12px; color:#aaa; font-size:16px; text-decoration:none; z-index:1;"
               onmouseover="this.style.color='#428bca'" onmouseout="this.style.color='#aaa'">
              <i class="fa fa-pencil"></i>
            </a>
          <?php endif; ?>

          <div class="topwrap">
            <div class="userinfo pull-left">
              <div class="circle"><?php echo esc($servicio->vars['info']->vars['filial']); ?></div>
              <?php echo esc($servicio->vars['info']->vars['nombre']); ?>
              <div class="icons">
                <span class="label <?php echo $tipoClass; ?>"><?php echo $tipoLabel; ?></span>
              </div>
            </div>

            <div class="posttext pull-left">
              <input id="topic" type="hidden" value="<?php echo (int)$servicio->vars['id']; ?>">
              <input id="user"  type="hidden" value="<?php echo (int)$id_user; ?>">

              <h2><a href="#"><?php echo esc($servicio->vars['titulo']); ?></a></h2>
              <p><?php echo nl2br(esc($servicio->vars['detalle'])); ?></p>

              <?php if (!empty($servicio->vars['telefono'])): ?>
                <?php $wa_num = preg_replace('/\D/', '', $servicio->vars['telefono']); ?>
                <div style="margin:10px 0 6px;">
                  <a href="https://wa.me/506<?php echo $wa_num; ?>" target="_blank" rel="noopener"
                     style="display:inline-flex; align-items:center; gap:7px; color:#25D366;
                            font-size:15px; font-weight:600; text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" style="flex-shrink:0;">
                      <path fill="#25D366" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <?php echo esc($servicio->vars['telefono']); ?>
                  </a>
                </div>
              <?php endif; ?>

              <?php render_adj_svc($adjuntos, 'Adjuntos del servicio'); ?>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="postinfobot">
            <?php
              $t_likes   = (int)($svc_likes['total']   ?? 0);
              $t_unlikes = (int)($svc_unlikes['total'] ?? 0);
            ?>
            <div class="likeblock pull-left">
              <i <?php if ($comision): ?>title="<?php echo esc($svc_likes_list['usuarios'] ?? ''); ?>"<?php endif; ?>
                 onclick="likes(1);" id="lu_<?php echo (int)$servicio->vars['id']; ?>"
                 class="fa fa-thumbs-o-up up hover-link<?php if ($svc_liked): ?> disabled-link<?php endif; ?>"></i>
              <span class="up" id="slu_<?php echo (int)$servicio->vars['id']; ?>"><?php echo $t_likes; ?></span>

              <i <?php if ($comision): ?>title="<?php echo esc($svc_unlikes_list['usuarios'] ?? ''); ?>"<?php endif; ?>
                 onclick="likes(0);" id="ld_<?php echo (int)$servicio->vars['id']; ?>"
                 class="fa fa-thumbs-o-down down hover-link<?php if ($svc_liked): ?> disabled-link<?php endif; ?>"></i>
              <span class="down" id="sld_<?php echo (int)$servicio->vars['id']; ?>"><?php echo $t_unlikes; ?></span>

              <?php if ($comision): ?>
                <ul id="dynamic-res-<?php echo (int)$servicio->vars['id']; ?>" class="number-list"></ul>
              <?php endif; ?>
            </div>

            <div class="prev pull-left"></div>
            <div class="posted pull-left"><i class="fa fa-clock-o"></i> <?php echo $fecha_fmt; ?></div>
            <div class="clearfix"></div>
          </div>
        </div><!-- /POST -->


        <!-- RESPUESTAS -->
        <?php if (!empty($servicio->vars['respuestas'])): ?>
          <?php foreach ($servicio->vars['respuestas'] as $r): ?>
            <?php
              $adjResp = get_adjuntos_servicio($r->vars['id'], "respuesta");
              $rFecha  = new DateTime($r->vars['fecha']);
              $rFmt    = $rFecha->format('d M Y @ h:ia');

              $r_likes   = get_total_respuesta_servicio_likes($r->vars['id']);
              $r_unlikes = get_total_respuesta_servicio_unlikes($r->vars['id']);

              if ($comision) {
                $r_likes_list   = get_users_list_respuesta_servicio_likes($r->vars['id'], 1);
                $r_unlikes_list = get_users_list_respuesta_servicio_likes($r->vars['id'], 0);
                $r_like_users   = ['usuarios' => $r_likes_list['usuarios'] . ',' . $r_unlikes_list['usuarios']];
              } else {
                $r_like_users = get_users_list_respuesta_servicio_likes($r->vars['id']);
              }
              $r_liked = in_array($id_user, explode(',', $r_like_users['usuarios'] ?? ''));

              $rt_likes   = (int)($r_likes['total']   ?? 0);
              $rt_unlikes = (int)($r_unlikes['total'] ?? 0);
            ?>
            <div class="post">
              <div class="topwrap">
                <div class="userinfo pull-left">
                  <div class="circle2"><?php echo esc($r->vars['info']->vars['filial']); ?></div>
                  <?php echo esc($r->vars['info']->vars['nombre']); ?>
                </div>
                <div class="posttext pull-left">
                  <p><?php echo nl2br(esc($r->vars['detalle'])); ?></p>
                  <?php render_adj_svc($adjResp, 'Adjuntos de la respuesta'); ?>
                </div>
                <div class="clearfix"></div>
              </div>
              <div class="postinfobot">
                <div class="likeblock pull-left">
                  <i <?php if ($comision): ?>title="<?php echo esc($r_likes_list['usuarios'] ?? ''); ?>"<?php endif; ?>
                     onclick="Rlikes(1, <?php echo (int)$r->vars['id']; ?>);" id="rlu_<?php echo (int)$r->vars['id']; ?>"
                     class="fa fa-thumbs-o-up up hover-linkR<?php if ($r_liked): ?> disabled-link<?php endif; ?>"></i>
                  <span class="up" id="rslu_<?php echo (int)$r->vars['id']; ?>"><?php echo $rt_likes; ?></span>

                  <i <?php if ($comision): ?>title="<?php echo esc($r_unlikes_list['usuarios'] ?? ''); ?>"<?php endif; ?>
                     onclick="Rlikes(0, <?php echo (int)$r->vars['id']; ?>);" id="rld_<?php echo (int)$r->vars['id']; ?>"
                     class="fa fa-thumbs-o-down down hover-linkR<?php if ($r_liked): ?> disabled-link<?php endif; ?>"></i>
                  <span class="down" id="rsld_<?php echo (int)$r->vars['id']; ?>"><?php echo $rt_unlikes; ?></span>

                  <?php if ($comision): ?>
                    <ul id="dynamic-res_<?php echo (int)$r->vars['id']; ?>" class="number-list"></ul>
                  <?php endif; ?>
                </div>
                <div class="prev pull-left"></div>
                <div class="posted pull-left"><i class="fa fa-clock-o"></i> <?php echo $rFmt; ?></div>
                <div class="clearfix"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>


        <!-- FORM RESPONDER -->
        <div class="post">
          <form id="frm_topic" action="#" class="form" method="post">
            <div class="topwrap">
              <div class="userinfo pull-left">
                <div class="circle"><?php echo esc($filial); ?></div>
                <div class="icons"></div>
              </div>
              <div class="posttext pull-left">
                <div class="textwraper">
                  <div class="postreply">Agregue su comentario</div>
                  <textarea name="reply" id="reply" placeholder="Digite su mensaje acá"></textarea>
                  <div style="margin-top:12px;">
                    <div style="font-weight:600; margin-bottom:6px;">Adjuntar archivos</div>
                    <div id="dzReplyAdjuntos" class="dropzone"></div>
                    <small class="text-muted">Puede subir imágenes, PDF, Word, Excel, TXT.</small>
                  </div>
                </div>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="postinfobot">
              <?php if ($servicio->vars['estado'] > 1): ?>
                <div class="pull-left closed">
                  <p class="closeTopic closed">SERVICIO CERRADO</p>
                </div>
              <?php else: ?>
                <div class="pull-right postreply">
                  <p style="display:none" class="closeTopic closed">SERVICIO CERRADO</p>
                  <div class="pull-left active">
                    <button type="submit" id="btn_post" class="btn btn-primary">Guardar</button>
                  </div>
                  <?php if ($_SESSION['rol'] > 1 || $_SESSION['user'] == $servicio->vars['creador']): ?>
                    <div class="pull-left active">
                      <button style="background-color:#e13f28; margin-left:15px;" type="button"
                              onclick="closeTopicControl();" class="btn btn-primary">Cerrar</button>
                    </div>
                  <?php endif; ?>
                  <div class="clearfix"></div>
                </div>
              <?php endif; ?>
              <div class="clearfix"></div>
            </div>
          </form>
        </div><!-- /POST -->

      </div>

      <?php include('../ui/partials/side.php'); ?>

    </div>
  </div>
</section>

<?php include('../ui/partials/footer.php'); ?>
