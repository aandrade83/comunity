<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Encuesta/db.php';

$filial   = $_SESSION['filial'] ?? '';
$comision = isset($_SESSION['rol']) && (int)$_SESSION['rol'] >= 2;

// Load surveys: commission sees all, filials see only active
if ($comision) {
    $surveys = enc_rows('SELECT * FROM surveys ORDER BY id DESC');
} else {
    $surveys = enc_rows('SELECT * FROM surveys WHERE status = 1 ORDER BY id DESC');
}

// For each survey, attach response count and whether this filial answered
foreach ($surveys as &$sv) {
    $sv['response_count'] = enc_response_count((int)$sv['id']);
    $sv['user_answered']  = ($filial !== '') ? (enc_get_response((int)$sv['id'], $filial) !== null) : false;
}
unset($sv);
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
.enc-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 6px;
}
.enc-badge-active   { background:#dff0d8; color:#3c763d; }
.enc-badge-inactive { background:#f2dede; color:#a94442; }
.enc-badge-answered { background:#d9edf7; color:#31708f; }
.enc-badge-results  { background:#fcf8e3; color:#8a6d3b; }

.encuesta-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 14px 18px;
    margin-bottom: 14px;
}
.encuesta-box h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}
.encuesta-box .meta {
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
}
.encuesta-box .btn-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}
</style>

<section class="content">

    <!-- BREADCRUMB -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="<?= BASE_URL ?>/apps/Forum/index.php">Inicio</a>
                <span class="diviver"></span>
                ENCUESTAS
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-md-8 col-xs-12">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0;">ENCUESTAS</h3>
                    <?php if ($comision): ?>
                        <a href="survey_create.php" class="btn btn-success">
                            <i class="fa fa-plus"></i> Nueva Encuesta
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($surveys)): ?>
                    <div class="encuesta-box">
                        <h4>No hay encuestas disponibles</h4>
                        <p class="meta">En este momento no hay encuestas activas.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($surveys as $sv): ?>
                    <div class="encuesta-box">
                        <h4>
                            <?= htmlspecialchars($sv['title'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($comision): ?>
                                <span class="enc-badge <?= $sv['status'] ? 'enc-badge-active' : 'enc-badge-inactive' ?>">
                                    <?= $sv['status'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                                <?php if ($sv['show_results']): ?>
                                    <span class="enc-badge enc-badge-results">Resultados visibles</span>
                                <?php endif; ?>
                                <?php if ($sv['user_answered']): ?>
                                    <span class="enc-badge enc-badge-answered">Ya respondió</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($sv['user_answered']): ?>
                                    <span class="enc-badge enc-badge-answered">Ya respondió</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </h4>

                        <?php if (!empty($sv['description'])): ?>
                            <p class="meta"><?= htmlspecialchars($sv['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

                        <?php if ($comision): ?>
                            <p class="meta"><?= $sv['response_count'] ?> respuesta(s) &nbsp;|&nbsp; Creada: <?= htmlspecialchars($sv['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

                        <div class="btn-row">
                            <?php if ($comision): ?>
                                <!-- Edit only if no responses yet -->
                                <?php if ($sv['response_count'] == 0): ?>
                                    <a href="survey_create.php?id=<?= (int)$sv['id'] ?>" class="btn btn-xs btn-default">
                                        <i class="fa fa-pencil"></i> Editar
                                    </a>
                                <?php endif; ?>

                                <!-- Activate / Deactivate -->
                                <?php if ($sv['status']): ?>
                                    <button class="btn btn-xs btn-warning btn-enc-action"
                                            data-id="<?= (int)$sv['id'] ?>" data-ac="deactivate">
                                        <i class="fa fa-pause"></i> Desactivar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-xs btn-success btn-enc-action"
                                            data-id="<?= (int)$sv['id'] ?>" data-ac="activate">
                                        <i class="fa fa-play"></i> Activar
                                    </button>
                                <?php endif; ?>

                                <!-- Toggle results visibility -->
                                <button class="btn btn-xs btn-<?= $sv['show_results'] ? 'info' : 'default' ?> btn-enc-action"
                                        data-id="<?= (int)$sv['id'] ?>" data-ac="toggle_results"
                                        title="<?= $sv['show_results'] ? 'Ocultar resultados a filiales' : 'Mostrar resultados a filiales' ?>">
                                    <i class="fa fa-<?= $sv['show_results'] ? 'eye' : 'eye-slash' ?>"></i>
                                    <?= $sv['show_results'] ? 'Ocultar resultados' : 'Publicar resultados' ?>
                                </button>

                                <!-- View results -->
                                <?php if ($sv['response_count'] > 0): ?>
                                    <a href="survey_results.php?id=<?= (int)$sv['id'] ?>" class="btn btn-xs btn-primary">
                                        <i class="fa fa-bar-chart"></i> Ver resultados
                                    </a>
                                <?php endif; ?>

                                <!-- Responder (comisión también puede participar en encuestas activas) -->
                                <?php if ($sv['status'] && !$sv['user_answered']): ?>
                                    <a href="survey_answer.php?id=<?= (int)$sv['id'] ?>" class="btn btn-xs btn-success">
                                        <i class="fa fa-pencil-square-o"></i> Responder
                                    </a>
                                <?php endif; ?>

                                <!-- Delete -->
                                <button class="btn btn-xs btn-danger btn-enc-delete"
                                        data-id="<?= (int)$sv['id'] ?>"
                                        data-responses="<?= (int)$sv['response_count'] ?>">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>

                            <?php else: ?>
                                <!-- Regular filial -->
                                <?php if (!$sv['user_answered']): ?>
                                    <a href="survey_answer.php?id=<?= (int)$sv['id'] ?>" class="btn btn-xs btn-success">
                                        <i class="fa fa-pencil-square-o"></i> Responder
                                    </a>
                                <?php elseif ($sv['show_results']): ?>
                                    <a href="survey_results.php?id=<?= (int)$sv['id'] ?>" class="btn btn-xs btn-primary">
                                        <i class="fa fa-bar-chart"></i> Ver resultados
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

<script>
// Action buttons (activate/deactivate/toggle_results)
document.querySelectorAll('.btn-enc-action').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var ac = this.dataset.ac;
        fetch('actions/actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'ac=' + encodeURIComponent(ac) + '&id=' + encodeURIComponent(id)
        })
        .then(function(r){ return r.json(); })
        .then(function(resp) {
            if (resp.ok) {
                location.reload();
            } else {
                alert(resp.error || 'Error');
            }
        });
    });
});

// Delete buttons
document.querySelectorAll('.btn-enc-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id        = this.dataset.id;
        var responses = parseInt(this.dataset.responses, 10);

        var opts = {
            title: responses > 0
                ? 'Esta encuesta tiene ' + responses + ' respuesta(s). ¿Eliminar de todas formas?'
                : '¿Eliminar esta encuesta?',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        };
        opts[_swalIconKey] = 'warning';

        Swal.fire(opts).then(function(result) {
            if (!result) return;
            fetch('actions/actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ac=delete&id=' + encodeURIComponent(id)
            })
            .then(function(r){ return r.json(); })
            .then(function(resp) {
                if (resp.ok) {
                    location.reload();
                } else {
                    alert(resp.error || 'Error al eliminar');
                }
            });
        });
    });
});
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
