<?php
include('../ui/main_head.php');

if (!$comision) {
    header('Location: index.php');
    exit;
}

$error = param('err') ?? '';
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
#act-form { max-width: 760px; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Actividades</a>
                <span class="diviver"></span>
                Nueva Actividad
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-xs-12">

                <div id="act-form">
                    <div class="post" style="padding:20px;">
                        <h3 style="margin-top:0;">Nueva Actividad</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <form method="POST" action="store.php" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Título <span style="color:red">*</span></label>
                                <input type="text" name="titulo" class="form-control" maxlength="255" required>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Fecha <span style="color:red">*</span></label>
                                        <input type="date" name="fecha" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Hora <span style="color:red">*</span></label>
                                        <input type="time" name="hora" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Lugar</label>
                                <input type="text" name="lugar" class="form-control" maxlength="255">
                            </div>

                            <div class="form-group">
                                <label>Colaboración</label>
                                <input type="text" name="colaboracion" class="form-control" maxlength="100" placeholder="Ej: Traer silla, comida, etc.">
                            </div>

                            <div class="form-group">
                                <label>Detalle</label>
                                <textarea name="detalle" class="form-control" rows="5" placeholder="Descripción de la actividad..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Adjuntos <small class="text-muted">(JPG, PNG, PDF — múltiples)</small></label>
                                <input type="file" name="adjuntos[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <div style="margin-top:20px; margin-bottom:40px; text-align:right;">
                                <a href="index.php" class="btn btn-default" style="margin-right:8px;">Cancelar</a>
                                <button type="submit" id="btn-create-act" class="btn btn-success btn-lg">
                                    <i class="fa fa-save"></i> Guardar Actividad
                                </button>
                            </div>

                        </form>
<script>
document.querySelector('form').addEventListener('submit', function() {
    var btn = document.getElementById('btn-create-act');
    if (btn) btn.disabled = true;
});
</script>
                    </div>
                </div>

            </div>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
