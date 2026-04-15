<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Actividades/db.php';

if (!$comision) { header('Location: index.php'); exit; }

$act_id = (int)(param('id') ?? 0);
if (!$act_id) { header('Location: index.php'); exit; }

$act = act_row('SELECT * FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) { header('Location: index.php'); exit; }

$adjuntos = act_adjuntos($act_id);
$error    = param('err') ?? '';
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
#act-form { max-width: 760px; }
.adj-thumb { width:80px; height:60px; object-fit:cover; border-radius:3px; border:1px solid #ddd; vertical-align:middle; margin-right:8px; }
.adj-row { display:flex; align-items:center; gap:10px; padding:6px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Actividades</a>
                <span class="diviver"></span>
                <a href="view.php?id=<?= $act_id ?>">Ver</a>
                <span class="diviver"></span>
                Editar
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-xs-12">

                <div id="act-form">
                    <div class="post" style="padding:20px;">
                        <h3 style="margin-top:0;">Editar Actividad</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>

                        <form method="POST" action="update.php" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $act_id ?>">

                            <div class="form-group">
                                <label>Título <span style="color:red">*</span></label>
                                <input type="text" name="titulo" class="form-control" maxlength="255" required
                                    value="<?= htmlspecialchars($act['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Fecha <span style="color:red">*</span></label>
                                        <input type="date" name="fecha" class="form-control" required
                                            value="<?= htmlspecialchars($act['fecha'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Hora <span style="color:red">*</span></label>
                                        <input type="time" name="hora" class="form-control" required
                                            value="<?= substr($act['hora'], 0, 5) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Lugar</label>
                                <input type="text" name="lugar" class="form-control" maxlength="255"
                                    value="<?= htmlspecialchars($act['lugar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="form-group">
                                <label>Colaboración</label>
                                <input type="text" name="colaboracion" class="form-control" maxlength="100"
                                    value="<?= htmlspecialchars($act['colaboracion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="form-group">
                                <label>Detalle</label>
                                <textarea name="detalle" class="form-control" rows="5"><?= htmlspecialchars($act['detalle'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <?php if (!empty($adjuntos)): ?>
                            <div class="form-group">
                                <label>Adjuntos actuales</label>
                                <?php foreach ($adjuntos as $adj): ?>
                                <div class="adj-row">
                                    <?php if ($adj['tipo'] === 'imagen'): ?>
                                        <img src="<?= act_upload_url() . htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?>" class="adj-thumb">
                                    <?php else: ?>
                                        <i class="fa fa-file-pdf-o fa-2x" style="color:#c0392b;"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <label style="margin:0; font-weight:normal; color:#c00;">
                                        <input type="checkbox" name="del_adj[]" value="<?= (int)$adj['id'] ?>"> Eliminar
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Agregar adjuntos <small class="text-muted">(JPG, PNG, PDF)</small></label>
                                <input type="file" name="adjuntos[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <div style="margin-top:20px; margin-bottom:40px; text-align:right;">
                                <a href="view.php?id=<?= $act_id ?>" class="btn btn-default" style="margin-right:8px;">Cancelar</a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
