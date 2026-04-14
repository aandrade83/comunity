<?
 include('head.php');
 $t = param('t');
 $c = param('c');
 $temas = get_servicios_special($c,$t);
 //echo "<pre>";
 //print_r($temas); exit;
?>

            <section class="content">

                
                <div class="container">
                    <div class="row">
                        
                        <div class="col-lg-8 breadcrumbf">
                         
                            <a href="https://lab.lacallecr.com/VV/apps/Services/index.php">Inicio</a> <span class="diviver"></span> 
                        
                        
                        </div>
                        
                    </div>
                </div>
              


                <div class="container">
                    <div class="row">
                        
                        <div class="col-lg-8 col-xs-12 col-md-8">
                            <!--
                            <div class="pull-left"><a href="#" class="prevnext"><i class="fa fa-angle-left"></i></a></div>
                            <div class="pull-left">
                                

                                <ul class="paginationServices">
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
                            <h3> SERVICIOS / PROVEEDORES</h3>
                            
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
                                         <h2><a href="topic.php?t=<? echo $tema->vars['id'] ?>"><? echo $tema->vars['titulo'] ?></a></h2>
                                        <p><? echo $tema->vars['detalle'] ?></p>
                                    </div>
                                    <div class="clearfix"></div>
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
                        include('side.php');
                        ?>
                        

                    </div>
                </div>



                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-xs-12">
                            <?/*
                            <div class="pull-left"><a href="#" class="prevnext"><i class="fa fa-angle-left"></i></a></div>
                            
                            <div class="pull-left">
                                
                                <ul class="paginationServices">
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

  <?
   include('footer.php');
  ?>