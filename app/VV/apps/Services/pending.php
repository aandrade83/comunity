<?php 

 include('head.php');
 $temas = get_pending_topics();
?>

            <section class="content">


          <div class="container">
                    <div class="row">
                        <div class="col-lg-8 breadcrumbf">
                            <a href="index.php">Inicio</a> <span class="diviver"> - </span> 
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-8">
                            <!-- POST -->
                            <h3> TEMAS PENDIENTES DE REVISION</h3>
                           
                            <? foreach ($temas as $tema) { ?>
                              
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
                                            <img src="images/icon1.jpg" alt="" /><img src="images/icon4.jpg" alt="" />
                                        </div>
                                    </div>
                                    <div class="posttext pull-left">
                                        <h2><a href="topic_pending.php?t=<? echo $tema->vars['id'] ?>"><? echo $tema->vars['titulo'] ?></a></h2>
                                        <p><? echo $tema->vars['detalle'] ?></p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="pull-left"><a href="topic_pending.php?t=<? echo $tema->vars['id'] ?>"><button type="button" style="margin-top: 50px" class="btn btn-primary">Revisar</button></a></div>
                                <!--
                                <div class="postinfo pull-left">
                                    <div class="comments">
                                        <div class="commentbg">
                                            560
                                            <div class="mark"></div>
                                        </div>

                                    </div>
                                    <div class="views"><i class="fa fa-eye"></i> 1,568</div>
                                    <div class="time"><i class="fa fa-clock-o"></i> 24 min</div>                                    
                                </div>
                               -->
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
                            */  ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>


            </section>

  <?
   include('footer.php');
   ?>