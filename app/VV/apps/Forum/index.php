<?
 include('../ui/main_head.php');
 ?>

     <!-- ROW 2: Logo + (Solo Foro) Buscar + Crear -->
    <!-- Toolbar: Buscar (izq/col-8) + Crear (der/col-4) -->
<?php if ($isForum) { ?>
  <div class="container vv-toolbar">
    <div class="row">
      <div class="col-12">
        <div class="vv-toolbar-row">
          
          <!-- IZQUIERDA: Buscar (input + lupa en un solo renglón) -->
          <div class="vv-toolbar-left">
            <div class="input-group vv-searchgroup">
              <input type="text" id="buscar" class="form-control" placeholder="Buscar Tema">
              <button class="btn btn-outline-secondary" onclick="SearchTema()" type="button">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>

          <!-- DERECHA: Botón pegado a la derecha (sobre la X) -->
          <div class="vv-toolbar-right">
            <form action="new_topic.php" method="post" style="margin:0;">
              <button class="btn btn-success vv-btn-create" type="submit">
                <i class="fa fa-plus"></i>
                <span class="vv-btn-label"> NUEVO TEMA</span>
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
<?php } ?>


 <?
 $s = param('s');
 $c = param('c');
$st = param('st');
 if($st == 2){
        $temas = get_close_topics($s,$c);
 } else {
  $temas = get_active_topics($s,$c);
 }

 //echo "<pre>";
 //print_r($temas); exit;
?>

            <section class="content">

                
                <div class="container">
                    <div class="row">
                        
                        <div class="col-lg-8 breadcrumbf" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                         <? if ($s != "") { ?>
                            <a href="<?= BASE_URL ?>/apps/Forum/index.php">Inicio</a> <span class="diviver"></span>
                        <? } ?>

                         <a href="<?= BASE_URL ?>/apps/Forum/index.php"
                            style="display:inline-block;padding:5px 16px;border-radius:20px;font-weight:600;font-size:13px;text-decoration:none;
                                   <?= ($st != 2) ? 'background:#337ab7;color:#fff;border:1px solid #337ab7;' : 'background:#fff;color:#555;border:1px solid #ccc;' ?>">
                           <i class="fa fa-folder-open-o"></i> Activos
                         </a>
                         <a href="<?= BASE_URL ?>/apps/Forum/index.php?st=2"
                            style="display:inline-block;padding:5px 16px;border-radius:20px;font-weight:600;font-size:13px;text-decoration:none;
                                   <?= ($st == 2) ? 'background:#d9534f;color:#fff;border:1px solid #d9534f;' : 'background:#fff;color:#555;border:1px solid #ccc;' ?>">
                           <i class="fa fa-lock"></i> Cerrados
                         </a>

                        </div>
                        
                    </div>
                </div>
              


                <div class="container">
                    <div class="row">
                        
                        <div class="col-lg-8 col-xs-12 col-md-8">
                            <!--
                            <div class="pull-left"><a href="#" class="prevnext"><i class="fa fa-angle-left"></i></a></div>
                            <div class="pull-left">
                                

                                <ul class="paginationforum">
                                    <li class="hidden-xs"><a href="#">1</a></li>
                                    <li class="hidden-xs"><a href="#">2</a></li>
                                    <li class="hidden-xs"><a href="#">3</a></li>
                                    <li class="hidden-xs"><a href="#">4</a></li>
                                    <li><a href="#">5</a></li>
                                    <li><a href="#">6</a></li>
                                    <li><a href="#" class="active">7</a></li>
                                    <li><a href="#">8</a></li>
                                    <li class="hidden-xs"><a href="#">9</a></li>
                                    <li class="hidden-xs"><a href="#">10</a></li>
                                    <li class="hidden-xs hidden-md"><a href="#">11</a></li>
                                    <li class="hidden-xs hidden-md"><a href="#">12</a></li>
                                    <li class="hidden-xs hidden-sm hidden-md"><a href="#">13</a></li>
                                    <li><a href="#">1586</a></li>
                                </ul>
                               
                            </div>
                            <div class="pull-left"><a href="#" class="prevnext last"><i class="fa fa-angle-right"></i></a></div>
                             -->
                            <div class="clearfix"></div>
                        </div>
                    
                    </div>
                </div>


                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-8">
                            <!-- POST -->
                            <h3> TEMAS ACTIVOS</h3>
                            
                             <? if(!empty($temas)) { ?>
                              
                             <? foreach($temas as $tema) {  ?>
                              <div class="post">
                                <div class="wrap-ut pull-left">
                                    <div class="userinfo pull-left">
                                       <!-- <div class="avatar">
                                            <img src="images/avatar.jpg" alt="" />
                                            <div class="status green">&nbsp;</div>
                                        </div>
                                    -->
                                    <div class="circle"><? echo $tema->vars['info']->vars['filial']?></div>
                                     <? echo $tema->vars['info']->vars['nombre']?>

                                        <div class="icons">
                                           <span class="label label-info"><? echo $Categorias[$tema->vars['id_categoria']]->vars['nombre']?></span> 
                                        </div> 
                                    </div>
                                    <div class="posttext pull-left">
                                         <h2>
                                           <a href="topic.php?t=<? echo $tema->vars['id'] ?>"><? echo $tema->vars['titulo'] ?></a>
                                           <a href="topic.php?t=<? echo $tema->vars['id'] ?>"
                                              title="Ver más"
                                              style="margin-left:8px; color:#aaa; font-size:16px; vertical-align:middle; text-decoration:none;"
                                              onmouseover="this.style.color='#428bca'" onmouseout="this.style.color='#aaa'">
                                             <i class="fa fa-eye"></i>
                                           </a>
                                         </h2>
                                        <?
                                         $vv_cut = false;
                                         $vv_preview = vv_text_excerpt($tema->vars['detalle'], 500, $vv_cut);
                                         // También hay "más" si el extracto excede las 7 líneas visibles
                                         $vv_more = $vv_cut || substr_count($vv_preview, "\n") >= 7;
                                        ?>
                                        <p class="vv-preview"><?= htmlspecialchars($vv_preview, ENT_QUOTES, 'UTF-8') ?></p>
                                        <a href="topic.php?t=<? echo $tema->vars['id'] ?>" class="vv-preview-more"<?= $vv_more ? '' : ' hidden' ?>>Ver más...</a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="postinfo pull-left">
                                    <div class="comments">
                                        <div class="commentbg">
                                            <? echo $tema->vars['responses']['responses'] ?>
                                            <div class="mark"></div>
                                        </div>

                                    </div>
                                    <div class="views"><i class="fa fa-eye"></i> <? echo $tema->vars['views']['views'] ?></div>
                                    <div class="time"><i class="fa fa-clock-o"></i> <? echo $tema->vars['last_update'] ?></div>                                    
                                </div>
                                <div class="clearfix"></div>
                            </div><!-- POST -->
                            <? } ?>

                           <? } else { ?>

                            <div class="post">
                                <div class="wrap-ut pull-left">
                                    
                                    <div class="posttext pull-left">
                                        <h2>    No hay temas disponibles</h2>
                                        
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                               
                                <div class="clearfix"></div>
                            </div><!-- POST -->



                           <? } ?>


                           

                        </div>
                        <?
                        include('../ui/partials/side.php');
                        ?>
                        

                    </div>
                </div>



                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-xs-12">
                            <?/*
                            <div class="pull-left"><a href="#" class="prevnext"><i class="fa fa-angle-left"></i></a></div>
                            
                            <div class="pull-left">
                                
                                <ul class="paginationforum">
                                    <li class="hidden-xs"><a href="#">1</a></li>
                                    <li class="hidden-xs"><a href="#">2</a></li>
                                    <li class="hidden-xs"><a href="#">3</a></li>
                                    <li class="hidden-xs"><a href="#">4</a></li>
                                    <li><a href="#">5</a></li>
                                    <li><a href="#">6</a></li>
                                    <li><a href="#" class="active">7</a></li>
                                    <li><a href="#">8</a></li>
                                    <li class="hidden-xs"><a href="#">9</a></li>
                                    <li class="hidden-xs"><a href="#">10</a></li>
                                    <li class="hidden-xs hidden-md"><a href="#">11</a></li>
                                    <li class="hidden-xs hidden-md"><a href="#">12</a></li>
                                    <li class="hidden-xs hidden-sm hidden-md"><a href="#">13</a></li>
                                    <li><a href="#">1586</a></li>
                                </ul>
                               
                            </div>
                            <div class="pull-left"><a href="#" class="prevnext last"><i class="fa fa-angle-right"></i></a></div>
                            */ ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>


            </section>

  <script>
   // Muestra "Ver más..." si el CSS recortó el extracto (pantallas angostas).
   (function () {
     function vvCheckPreviews() {
       var items = document.querySelectorAll('.vv-preview');
       for (var i = 0; i < items.length; i++) {
         var more = items[i].nextElementSibling;
         if (more && more.classList.contains('vv-preview-more') && items[i].scrollHeight > items[i].clientHeight + 1) {
           more.hidden = false;
         }
       }
     }
     if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', vvCheckPreviews); } else { vvCheckPreviews(); }
     window.addEventListener('resize', vvCheckPreviews);
   })();
  </script>

  <?
   include('../ui/partials/footer.php');
  ?>