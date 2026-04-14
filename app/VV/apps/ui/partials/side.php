        
<div class="col-lg-4 col-md-4">

    <!-- -->
    <div class="sidebarblock">
        <div class="side-topic-cat categorias">
        <h3>Categorias</h3>
        </div>
        <div class="divline"></div>
        <div class="blocktxt">
            <ul class="cats">
                 <? foreach($Categorias as $c) { ?>
                    <? $t = 0 ;
                       if(isset($total[$c->vars['id']]['total'])){
                        $t = $total[$c->vars['id']]['total'];
                       }
                    ?>
                <li><a href="https://lab.lacallecr.com/VV/apps/Forum/index.php?c=<? echo $c->vars['id']?>"><? echo $c->vars['nombre']?><span class="badge pull-right"><? echo $t ?></span></a></li>
                <? } ?>
            </ul>
        </div>
    </div>


    <!-- -->


<!-- -->
<div class="sidebarblock">
    <div class="side-topic-active mis-temas">
    <h3>Mis temas activos (Top 10) </h3>
    </div>
    <? $actives = get_active_user_temas($id_user); ?>
    <?
    
    if(!empty($actives)){ ?>
     <? foreach ($actives   as $t) { ?>
    <div class="divline"></div>
    <div class="blocktxt">
          <a href="https://lab.lacallecr.com/VV/apps/Forum/topic.php?t=<? echo $t['id']?>"><? echo $t['titulo']?></a>
    </div>
     <? }?> 
     <? } else { ?>

    <div class="divline"></div>
    <div class="blocktxt">
        NO HAY TEMA ABIERTO EN EL QUE HAYAS PARTICIPADO
    </div>
    <? } ?>
    
</div>
<?
$encuestas = get_encuestas(1);
?>


    <!-- -->
    <div class="sidebarblock">
       <div class="side-topic-enc encuesta"> 
        <h3>Encuesta</h3>
        </div>
        <div class="divline"></div>
         <div class="divline"></div>
          <? if(empty($encuestas)) { ?>
          <div class="blocktxt">
           NO HAY ENCUESTA ABIERTA
         </div>
          <? } else { ?>
            <div class="blocktxt">
             <a href="https://lab.lacallecr.com/VV/apps/Encuesta/index.php" target="_self" >Ver Encuestas</a>
         </div>

          <? } ?>  
        <? /*
        <div class="blocktxt">
            <p>Titulo de la Encuesta</p>
            <form action="#" method="post" class="form">
                <table class="poll">
                    <tr>
                        <td>
                            <div class="progress">
                                <div class="progress-bar color1" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 90%">
                                 Opcion 1
                             </div>
                         </div>
                     </td>
                     <td class="chbox">
                        <input id="opt1" type="radio" name="opt" value="1">  
                        <label for="opt1"></label>  
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="progress">
                            <div class="progress-bar color2" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 63%">
                                Opcion 2
                            </div>
                        </div>
                    </td>
                    <td class="chbox">
                        <input id="opt2" type="radio" name="opt" value="2" checked>  
                        <label for="opt2"></label>  
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="progress">
                            <div class="progress-bar color3" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 75%">
                                Opcion 3
                            </div>
                        </div>
                    </td>
                    <td class="chbox">
                        <input id="opt3" type="radio" name="opt" value="3">  
                        <label for="opt3"></label>  
                    </td>
                </tr>
            </table>
        </form>
        <p class="smal">Voting ends on 19th of October</p>

    </div>
    */?>
</div>

<!-- -->
<? if($comision){ ?>
<div class="sidebarblock">
    <div class="side-topic-rec temas-rechazados">
    <h3>Temas Rechazados (Top 5)</h3>
    </div>
    <? $rechazados = get_declined_topics(); ?>
    <?
    
    if(!empty($rechazados)){ ?>
     <? foreach ($rechazados   as $t) { ?>
    <div class="divline"></div>
    <div class="blocktxt">
          <a href="https://lab.lacallecr.com/VV/apps/Forum/topic.php?t=<? echo $t['id']?>"><? echo $t['titulo']?></a>
    </div>
     <? }?> 
     <? } else { ?>

    <div class="divline"></div>
    <div class="blocktxt">
        NO HAY TEMAS RECHAZADOS
    </div>
    <? } ?>
    
</div>
<? } ?>

</div>
