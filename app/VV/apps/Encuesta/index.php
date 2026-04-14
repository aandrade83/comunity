<?
 include('../ui/main_head.php');
?>

<style>

/* ===== OCULTAR SIDE EN MOBILE ===== */
@media (max-width: 768px) {
    #side,
    .side,
    .sidebar,
    .rightcol,
    .col-right {
        display: none !important;
    }
}


</style>


<?
 $encuestas = get_encuestas(1);
?>

<style>
    /* ====== ESTILOS ENCUESTAS (mover luego a tu CSS) ====== */
    .encuestas-title-page {
        margin-top: 20px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .encuesta-box {
        background: #ffffff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px 20px;
        margin-bottom: 15px;
    }

    .encuesta-box h4 {
        margin-top: 0;
        margin-bottom: 12px; /* espacio entre título y slider */
        font-size: 16px;
        font-weight: 600;
    }

    .encuesta-detalle {
        margin-bottom: 8px;
        color: #666;
        font-size: 13px;
    }

    .encuesta-slider-row {
        display: flex;
        justify-content: center;
        margin: 8px 0 10px 0;
    }

    .encuesta-slider-inner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 50%; /* barra a la mitad del ancho */
        min-width: 260px;
    }

    .encuesta-slider {
        flex: 1;
    }

    .slider-value {
        min-width: 45px;
        text-align: right;
        font-weight: 600;
        font-size: 13px;
    }

    .encuesta-btn-row {
        text-align: center;
        margin-bottom: 5px;
    }
</style>

<section class="content">

    <!-- BREADCRUMB -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="https://lab.lacallecr.com/VV/apps/Forum/index.php">Inicio</a>
                <span class="diviver"></span>
                ENCUESTAS
            </div>
        </div>
    </div>

    <!-- LISTA DE ENCUESTAS -->
    <div class="container" id="encuestasContainer"
         data-user-id="<? echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>">
        <div class="row">

            <div class="col-lg-8 col-md-8 col-xs-12">

                <h3 class="encuestas-title-page">ENCUESTAS</h3>

                 <div class="alert alert-info" style="margin-top:15px;">
        <strong>Encuesta del Desarrollador</strong><br>
        Favor llenar la siguiente encuesta.
        <br><br>
        <a href="https://docs.google.com/forms/d/e/1FAIpQLSc5lhgvoGU_Ro3mZwHrnn8nsfySSMr22MfBeEid7vQnf10FYA/viewform?usp=publish-editor" 
           target="_blank" 
           class="btn btn-primary">
           Ir a la Encuesta
        </a>
    </div>
    
                <p>Su respuesta es anónima y será utilizada únicamente con fines de mejora continua.</p>

                <? if (!empty($encuestas)) { ?>

                    <? foreach ($encuestas as $enc) { 

                        $done = false;

                        $user_resp = get_resp_encuesta_user($filial,$enc->vars['id']);
                        $resp = get_resp_encuesta($enc->vars['id']);

                         if (is_array($user_resp) && array_key_exists('id_user', $user_resp)) {
                            if ((int)$user_resp['id_user'] === (int)$filial) {
                                $done = true;
                            }
                        }



                        if (!empty($resp) && is_array($resp)) {

                            $result['total'] = count($resp);

                            $suma = 0;

                    foreach ($resp as $r) {
                        if (isset($r['detalle'])) {
                        $suma += (float)$r['detalle'];
                        }
                    }

                    if ($result['total'] > 0) {
                        $result['avg'] = round($suma / $result['total'], 2);
                    }
                  }

                        // print_r($user_resp);
                         //print_r($resp);

                        ?>

                        <div class="post encuesta-box"
                             data-encuesta-id="<? echo $enc->vars['id']; ?>">

                            <h4><? echo $enc->vars['titulo']; ?></h4>

                            <? /* if (!empty($enc->vars['detalle'])) { ?>
                                <p class="encuesta-detalle">
                                    <? echo $enc->vars['detalle']; ?>
                                </p>
                            <? } */ ?>

                      <!-- SLIDER + PORCENTAJE EN UNA SOLA LÍNEA -->
<div class="encuesta-slider-row">
    <div class="encuesta-slider-inner">

        <input type="range"
               min="0"
               max="100"
               value="<? echo $done ? (int)$result['avg'] : 50; ?>"
               class="encuesta-slider"
               <? if ($done) echo 'disabled'; ?>
               oninput="updateSliderValue(this)">

        <span class="slider-value">
            <? if ($done) { ?>
                <? echo (int)$result['avg']; ?>%
                (<? echo (int)$result['total']; ?> filiales)
            <? } else { ?>
                50%
            <? } ?>
        </span>

    </div>
</div>

<!-- BOTÓN GUARDAR (solo si NO está done) -->
<? if (!$done) { ?>
    <div class="encuesta-btn-row">
        <button type="button"
                class="btn btn-success btn-guardar-encuesta"
                onclick="confirmGuardarEncuesta(this)">
            Guardar
        </button>
    </div>
<? } ?>

                        </div><!-- /.encuesta-box -->

                    <? } ?>

                <? } else { ?>

                    <div class="post encuesta-box">
                        <h4>No hay encuestas disponibles</h4>
                        <p class="encuesta-detalle">
                            En este momento no hay encuestas activas.
                        </p>
                    </div>

                <? } ?>

            </div>

            <?
            include('../ui/partials/side.php');
            ?>

        </div>
    </div>

</section>


<script>
    /**
     * Actualiza el porcentaje al mover la barra
     */
    function updateSliderValue(slider) {
        const value = slider.value;
        const valueLabel = slider.parentElement.querySelector('.slider-value');
        if (valueLabel) {
            valueLabel.innerText = value + '%';
        }
    }

    /**
     * Obtiene el user desde la sesión embebida en el contenedor
     * (solo para debug visual)
     */
    function getCurrentUserId() {
        const container = document.getElementById('encuestasContainer');
        if (!container) return null;
        return container.getAttribute('data-user-id');
    }

    /**
     * Confirmar y enviar encuesta
     */
    function confirmGuardarEncuesta(button) {

        const encuestaBox = button.closest('.encuesta-box');
        if (!encuestaBox) return;

        const slider = encuestaBox.querySelector('.encuesta-slider');
        if (!slider) return;

        const encuestaId = encuestaBox.getAttribute('data-encuesta-id');
        const valor = slider.value;
        const userId = getCurrentUserId(); // solo debug

        // 🔍 DEBUG ANTES DE ENVIAR
        console.log('PREPARANDO ENVÍO ENCUESTA', {
            ac: 'enc',
            encuestaId: encuestaId,
            valor: valor,
            userId: userId
        });

        Swal.fire({
            title: '¿Está seguro?',
            text: 'Una vez guardada la respuesta no podrá ser editada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (!result.isConfirmed) return;

            // 🚀 AJAX
            $.ajax({
                url: 'actions/actions.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    ac: 'enc',
                    encuestaId: encuestaId,
                    valor: valor
                },
                success: function (resp) {

                    console.log('RESPUESTA BACKEND', resp);

                    if (resp.control == '1') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Gracias',
                            text: 'Su respuesta fue guardada correctamente'
                        }).then(() => {
                            // 🔄 REFRESCAR PÁGINA
                            location.reload();
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resp.error || 'No se pudo guardar la encuesta'
                        });
                    }
                },
                error: function (xhr, status, error) {

                    console.error('ERROR AJAX', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de comunicación con el servidor'
                    });
                }
            });

        });
    }
</script>

<?
 include('../ui/partials/footer.php');
?>

