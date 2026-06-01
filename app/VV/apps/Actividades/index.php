<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Actividades/db.php';

$actividades = act_rows(
    'SELECT a.*, (SELECT COUNT(*) FROM actividad_participantes p WHERE p.actividad_id = a.id AND p.estado = "si") AS total_si
     FROM actividades a ORDER BY a.fecha DESC, a.hora DESC'
);
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
.act-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 14px 18px;
    margin-bottom: 14px;
}
.act-card h4 { margin: 0 0 6px 0; font-size: 16px; font-weight: 600; }
.act-card .act-meta { font-size: 12px; color: #888; margin-bottom: 10px; }
.act-card .act-meta span { margin-right: 14px; }
.act-card .act-meta i { margin-right: 4px; }
.act-card .btn-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.act-badge-si { background:#dff0d8; color:#3c763d; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="<?= BASE_URL ?>/apps/Forum/index.php">Inicio</a>
                <span class="diviver"></span>
                ACTIVIDADES
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-xs-12">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0;">ACTIVIDADES</h3>
                    <?php if ($comision): ?>
                        <a href="create.php" class="btn btn-success">
                            <i class="fa fa-plus"></i> Nueva Actividad
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($actividades)): ?>
                    <div class="act-card" style="text-align:center; padding:30px 20px; color:#888;">
                        <i class="fa fa-calendar-o fa-3x" style="margin-bottom:12px; display:block;"></i>
                        <h4 style="color:#888;">Aún no hay ninguna actividad programada</h4>
                        <?php if ($comision): ?>
                            <p style="margin-top:8px;">
                                <a href="create.php" class="btn btn-success btn-sm">
                                    <i class="fa fa-plus"></i> Crear primera actividad
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($actividades as $act): ?>
                    <div class="act-card">
                        <h4>
                            <a href="view.php?id=<?= (int)$act['id'] ?>">
                                <?= htmlspecialchars($act['titulo'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h4>
                        <div class="act-meta">
                            <span><i class="fa fa-calendar"></i><?= htmlspecialchars($act['fecha'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="fa fa-clock-o"></i><?= substr($act['hora'], 0, 5) ?></span>
                            <?php if ($act['lugar']): ?>
                                <span><i class="fa fa-map-marker"></i><?= htmlspecialchars($act['lugar'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="act-badge-si"><i class="fa fa-users"></i> <?= (int)$act['total_si'] ?> participante(s)</span>
                        </div>
                        <?php if ($act['detalle']): ?>
                            <p style="margin:0 0 6px 0; color:#555; font-size:13px;">
                                <?= htmlspecialchars(mb_substr($act['detalle'], 0, 120), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($act['detalle']) > 120 ? '…' : '' ?>
                            </p>
                        <?php endif; ?>
                        <div class="btn-row">
                            <a href="view.php?id=<?= (int)$act['id'] ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-eye"></i> Ver detalle
                            </a>
                            <?php if ($comision): ?>
                                <a href="edit.php?id=<?= (int)$act['id'] ?>" class="btn btn-xs btn-default">
                                    <i class="fa fa-pencil"></i> Editar
                                </a>
                                <button class="btn btn-xs btn-danger btn-act-delete" data-id="<?= (int)$act['id'] ?>">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

<script>
document.querySelectorAll('.btn-act-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = btn.dataset.id;
        var opts = { title: '¿Eliminar esta actividad?', text: 'Se eliminarán también los adjuntos y participaciones.', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' };
        opts[_swalIconKey] = 'warning';
        Swal.fire(opts).then(function(result) {
            if (!(result === true || (result && (result.isConfirmed || result.value)))) return;
            fetch('delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
            .then(function(r) { return r.text(); })
            .then(function(text) {
                var resp;
                try { resp = JSON.parse(text); } catch(e) { alert('Error al eliminar (respuesta inválida): ' + text.substring(0, 200)); return; }
                if (resp.ok) location.reload();
                else alert(resp.error || 'Error al eliminar');
            })
            .catch(function(err) { alert('Error de red: ' + err); });
        });
    });
});
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
