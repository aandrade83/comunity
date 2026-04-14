<?
include('../ui/main_head.php');
?>
<section class="content">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Inicio</a> <span class="diviver"></span> </div>
            </div>
        </div>


        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8">



                    <!-- POST -->
                    <div class="post">

                        <form id="frm_new" action="#" class="form newtopic" method="post" enctype="multipart/form-data">

                            <div class="topwrap">
                                <div class="userinfo pull-left">
                                    <div class="avatar">
                                       <div class="circle"><? echo $filial ?></div>
                                   </div>

                                   <div class="icons">
                                    <img src="images/icon3.jpg" alt="" /><img src="images/icon4.jpg" alt="" /><img src="images/icon5.jpg" alt="" /><img src="images/icon6.jpg" alt="" />
                                </div>
                            </div>
                            <div class="posttext pull-left">

                                <div>
                                    <input type="text" id="title" placeholder="Digite el Titulo" class="form-control" />
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <select name="category" id="category"  class="form-control" >
                                            <option value="" disabled selected>Seleccione Categoria</option>
                                            <? foreach($Categorias as $c) { ?>
                                                <option value="<? echo $c->vars['id'] ?>"><? echo $c->vars['nombre'] ?></option>
                                            <? } ?>

                                        </select>
                                    </div>

                                </div>

                                <div>
                                    <textarea name="desc" id="desc" placeholder="Detalle"  class="form-control" ></textarea>
                                </div>
                                <div class="form-group">
  <label>Adjuntar archivos</label>
  <div id="dzAdjuntos" class="dropzone"></div>


                                  <small class="text-muted">
                                    Puede subir múltiples archivos: imágenes, PDF, Word, Excel, TXT.
                                </small>
                            </div>
                            <div class="row newtopcheckbox">

                            </div>


                        </div>
                        <div class="clearfix"></div>
                    </div>                              
                    <div class="postinfobot">

                        <div class="notechbox pull-left">
                         <!--  <input type="checkbox" name="note" id="note" class="form-control" /> -->
                     </div>

                     <div class="pull-left">
                                           <!--
                                            <label for="note"> Email me when some one post a reply</label>
                        -->
                    </div>

                    <div class="pull-right postreply">
                                            <!--
                                            <div class="pull-left smile"><a href="#"><i class="fa fa-smile-o"></i></a></div>
                       -->
                       <div class="pull-left"><button  id="btn_post" type="submit" class="btn btn-primary">Post</button></div>
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

    </div>
</div>


</section>

<?
include('../ui/partials/footer.php');
?>
