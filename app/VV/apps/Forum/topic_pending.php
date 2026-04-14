<?
 include('../ui/main_head.php');

 $tema = get_topic(param('t'));
 $adjuntos =  get_adjuntos_tema(param('t'));

$adj = false;
 if(!empty($adjuntos)){
    $adj = true;
 } 
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

                            <!-- POST -->
                            <div class="post">
                                <div class="topwrap">
                                    <div class="userinfo pull-left">
                                           <div class="circle3"><? echo $tema->vars['info']->vars['filial']?></div>
                                          <? echo $tema->vars['info']->vars['nombre']?>
                                        <div class="icons">
                                            <img src="images/icon3.jpg" alt="" /><img src="images/icon4.jpg" alt="" /><img src="images/icon5.jpg" alt="" /><img src="images/icon6.jpg" alt="" />
                                        </div>
                                    </div>
                                    <div class="posttext pull-left">
                                        <h2><a href="topic_pending.php?t=<? echo $tema->vars['id'] ?>"><? echo $tema->vars['titulo'] ?></a></h2>
                                        <p><? echo $tema->vars['detalle'] ?></p>
                                           <div class="row">
                                                <div class="col-lg-6 col-md-6">
                                                <input id="topic" type="hidden" value="<? echo $tema->vars['id'] ?>">
                                                 <select name="revision" id="revision"  class="form-control" >
                                                        <option value="" disabled selected>Seleccione Revisión</option>
                                                        <option value="1">Aprobado</option>
                                                        <option value="3">Rechazado</option>
                                                    </select>
                                    </div>

                                    <? if ($adj){ ?>
  <div class="vv-adjuntos-wrap" style="margin-top:10px;"><BR><BR>
    <div style="font-weight:600; margin-bottom:6px;">Adjuntos del tema</div>

    <div class="vv-adjuntos-grid" style="display:flex; flex-wrap:wrap; gap:10px;">
      <?
        foreach ($adjuntos as $a) {

          $file = (string)($a->vars['nombre_archivo'] ?? '');
          if ($file === '') continue;

          $orig = (string)($a->vars['nombre_original'] ?? $file);
          $mime = (string)($a->vars['mime_type'] ?? '');

          // URL pública (topic_pending.php está en /VV/apps/Forum/)
          $url  = "uploads/adjuntos/" . $file;

          $isImg = false;
          if ($mime && strpos($mime, 'image/') === 0) $isImg = true;
          if (!$isImg && preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) $isImg = true;

          $isPdf = preg_match('/\.pdf$/i', $file) || $mime === 'application/pdf';

          if ($isImg) {
      ?>
            <a class="vv-attach vv-attach-img"
               href="<? echo $url; ?>"
               title="<? echo htmlspecialchars($orig); ?>"
               data-download="<? echo $url; ?>"
               style="display:inline-block; width:92px; text-align:center; text-decoration:none;">
              <img src="<? echo $url; ?>"
                   alt="<? echo htmlspecialchars($orig); ?>"
                   style="width:92px; height:92px; object-fit:cover; border-radius:10px; border:1px solid #ddd;">
              <div style="font-size:11px; color:#666; margin-top:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <? echo htmlspecialchars($orig); ?>
              </div>
            </a>
      <?
          } else {
            // PDF -> preview iframe; otros -> solo descarga/nueva pestaña
            $cls = $isPdf ? "vv-attach-pdf" : "vv-attach-file";
      ?>
            <a class="vv-attach <? echo $cls; ?>"
               href="<? echo $url; ?>"
               target="_blank"
               title="<? echo htmlspecialchars($orig); ?>"
               data-download="<? echo $url; ?>"
               style="display:inline-block; padding:10px 12px; border:1px solid #ddd; border-radius:10px; text-decoration:none; max-width:260px;">
              <span style="font-weight:600;"><? echo $isPdf ? "PDF" : "Archivo"; ?></span>
              <div style="font-size:12px; color:#666; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <? echo htmlspecialchars($orig); ?>
              </div>
              <div style="font-size:12px; margin-top:6px; color:#0d6efd;">
                Ver / Descargar
              </div>
            </a>
      <?
          }
        }
      ?>
    </div>
  </div>
<? } ?>

                                   </div></div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="postinfobot">
                                    <div class="clearfix"></div>
                                </div>
                            </div><!-- POST -->

                            <!-- POST (Respuesta + adjuntos) -->
                            <div class="post">
                                <form id="frm_pending" action="#" class="form" method="post">
                                    <div class="topwrap">
                                        <div class="userinfo pull-left">
                                              <div class="circle"><? echo $filial ?></div>
                                        <div class="icons">
                                                <img src="images/icon3.jpg" alt="" /><img src="images/icon4.jpg" alt="" /><img src="images/icon5.jpg" alt="" /><img src="images/icon6.jpg" alt="" />
                                            </div>
                                        </div>
                                       
                                        <div class="posttext pull-left">
                                            <div class="textwraper">
                                                <div class="postreply">Agregar Comentario</div>
                                                <textarea name="reply" id="reply" placeholder="Digite su Mensaje acá"></textarea>
 <? /*
                                                <!-- ✅ Dropzone para adjuntos en respuesta -->
                                                <div style="margin-top:12px;">
                                                    <div style="font-weight:600; margin-bottom:6px;">Adjuntar archivos</div>
                                                    <div id="dzPendingAdjuntos" class="dropzone"></div>
                                                    <small class="text-muted">
                                                        Puede subir múltiples archivos: imágenes, PDF, Word, Excel, TXT.
                                                    </small>
                                                </div>
                                                */?>
                                            </div>
                                        </div> 

                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="postinfobot">

                                        <div class="notechbox pull-left">
                                           <!--  <input type="checkbox" name="note" id="note" class="form-control" /> -->
                                        </div>

                                        <div class="pull-left">
                                           <!-- <label for="note"> Email me when some one post a reply</label> -->
                                        </div>

                                        <div class="pull-right postreply">
                                            <div class="pull-left">
                                                <button id="btn_pending_save" type="submit" class="btn btn-primary">Guardar</button>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>

                                        <div class="clearfix"></div>
                                    </div>
                                </form>
                            </div><!-- POST -->

                        </div>

<?
 include('../ui/partials/side.php'); 
?>

                    </div>
                </div>

                <div class="container">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="pull-left"><a href="#" class="prevnext"><i class="fa fa-angle-left"></i></a></div>
                            <div class="pull-left">

                            </div>
                            <div class="pull-left"><a href="#" class="prevnext last"><i class="fa fa-angle-right"></i></a></div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

            </section>

<?
 include('../ui/partials/footer.php'); ?>
?>
