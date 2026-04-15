<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Actividades/db.php';

$act_id = (int)(param('id') ?? 0);
if (!$act_id) { header('Location: index.php'); exit; }

$act = act_row('SELECT * FROM actividades WHERE id = ?', 'i', $act_id);
if (!$act) { header('Location: index.php'); exit; }

$adjuntos    = act_adjuntos($act_id);
$user_id     = (int)($id_user ?? 0);
$participante = $user_id ? act_get_participante($act_id, $user_id) : null;
$total_si    = act_count_si($act_id);
$comentarios = act_comentarios($act_id);
$today       = date('Y-m-d');
$can_part    = ($act['fecha'] > $today); // solo eventos futuros
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
.act-detail { background:#fff; border:1px solid #ddd; border-radius:4px; padding:20px 22px; margin-bottom:14px; }
.act-detail h3 { margin-top:0; }
.act-meta-row { font-size:13px; color:#666; margin-bottom:14px; }
.act-meta-row span { display:inline-block; margin-right:20px; margin-bottom:4px; }
.act-meta-row i { margin-right:5px; color:#888; }
.adj-grid { display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; }
.adj-img  { width:120px; height:90px; object-fit:cover; border-radius:4px; border:1px solid #ddd; cursor:pointer; transition:opacity .2s; }
.adj-img:hover { opacity:.85; }
.adj-pdf  { display:flex; align-items:center; gap:6px; padding:8px 12px; border:1px solid #ddd; border-radius:4px; font-size:13px; color:#337ab7; text-decoration:none; background:#f9f9f9; }
.adj-pdf:hover { background:#eef; }

/* Participation */
.part-box { background:#fff; border:1px solid #ddd; border-radius:4px; padding:16px 20px; margin-bottom:14px; }
.part-box h4 { margin-top:0; }
.badge-si  { background:#5cb85c; color:#fff; padding:3px 10px; border-radius:10px; font-size:13px; }
.badge-no  { background:#d9534f; color:#fff; padding:3px 10px; border-radius:10px; font-size:13px; }
.part-locked { font-size:13px; color:#999; margin-top:6px; }

/* Comments */
.comments-section { margin-bottom:14px; }
.comment-card { background:#fff; border:1px solid #e8e8e8; border-radius:4px; padding:12px 16px; margin-bottom:10px; }
.comment-card .c-header { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.comment-card .c-circle { width:32px; height:32px; border-radius:50%; background:#337ab7; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; }
.comment-card .c-meta { font-size:12px; color:#888; }
.comment-card .c-text { font-size:14px; color:#333; white-space:pre-wrap; word-break:break-word; margin-bottom:8px; }
.comment-card .adj-grid { margin-top:6px; }
.comment-card .adj-img  { width:90px; height:68px; }
.comment-form { background:#fff; border:1px solid #ddd; border-radius:4px; padding:16px 20px; }
.comment-form h4 { margin-top:0; }
.adj-preview { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
.adj-preview img { width:80px; height:60px; object-fit:cover; border-radius:3px; border:1px solid #ddd; }

/* Participant list (commission) */
.part-list table { width:100%; border-collapse:collapse; font-size:13px; }
.part-list td, .part-list th { padding:5px 8px; border-bottom:1px solid #f0f0f0; }
.part-list th { font-weight:600; color:#888; font-size:12px; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Actividades</a>
                <span class="diviver"></span>
                <?= htmlspecialchars($act['titulo'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-xs-12">

                <!-- ── Activity detail ───────────────────────────────────── -->
                <div class="act-detail">
                    <h3><?= htmlspecialchars($act['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>

                    <div class="act-meta-row">
                        <span><i class="fa fa-calendar"></i><?= htmlspecialchars($act['fecha'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span><i class="fa fa-clock-o"></i><?= substr($act['hora'], 0, 5) ?></span>
                        <?php if ($act['lugar']): ?>
                            <span><i class="fa fa-map-marker"></i><?= htmlspecialchars($act['lugar'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <?php if ($act['colaboracion']): ?>
                            <span><i class="fa fa-info-circle"></i><?= htmlspecialchars($act['colaboracion'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <span><i class="fa fa-users"></i><strong><?= $total_si ?></strong> confirmado(s)</span>
                    </div>

                    <?php if ($act['detalle']): ?>
                        <p style="color:#444; white-space:pre-wrap; font-size:14px; margin-bottom:14px;"><?= htmlspecialchars($act['detalle'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if (!empty($adjuntos)): ?>
                        <h5 style="margin-bottom:8px;"><i class="fa fa-paperclip"></i> Adjuntos</h5>
                        <div class="adj-grid">
                            <?php foreach ($adjuntos as $adj): ?>
                                <?php $url = act_upload_url() . htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if ($adj['tipo'] === 'imagen'): ?>
                                    <a href="<?= $url ?>" class="vv-attach-img" title="<?= htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?>" data-download="<?= $url ?>">
                                        <img src="<?= $url ?>" class="adj-img" alt="imagen">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $url ?>" class="vv-attach-pdf adj-pdf">
                                        <i class="fa fa-file-pdf-o fa-2x" style="color:#c0392b;"></i>
                                        <?= htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($comision): ?>
                        <div style="margin-top:16px; display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="edit.php?id=<?= $act_id ?>" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i> Editar</a>
                            <button class="btn btn-xs btn-danger" id="btn-del-act"><i class="fa fa-trash"></i> Eliminar</button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── Participation ─────────────────────────────────────── -->
                <div class="part-box">
                    <h4><i class="fa fa-check-square-o"></i> Participación</h4>

                    <?php if (!$can_part): ?>
                        <p class="part-locked"><i class="fa fa-lock"></i>
                            <?= $act['fecha'] < $today ? 'Este evento ya pasó.' : 'El evento es hoy, no se puede modificar la participación.' ?>
                        </p>
                        <?php if ($participante): ?>
                            <p style="margin-top:8px; font-size:13px;">
                                Su respuesta registrada:
                                <?= $participante['estado'] === 'si' ? '<span class="badge-si">Sí asistiré</span>' : '<span class="badge-no">No asistiré</span>' ?>
                            </p>
                        <?php endif; ?>

                    <?php elseif ($participante): ?>
                        <p style="font-size:14px; margin-bottom:10px;">
                            Su respuesta:
                            <?= $participante['estado'] === 'si' ? '<span class="badge-si">Sí asistiré</span>' : '<span class="badge-no">No asistiré</span>' ?>
                        </p>
                        <div style="display:flex; gap:8px;">
                            <button class="btn btn-sm btn-success btn-part" data-estado="si" <?= $participante['estado']==='si'?'disabled':'' ?>><i class="fa fa-check"></i> Sí asistiré</button>
                            <button class="btn btn-sm btn-danger  btn-part" data-estado="no" <?= $participante['estado']==='no'?'disabled':'' ?>><i class="fa fa-times"></i> No asistiré</button>
                        </div>

                    <?php else: ?>
                        <p style="font-size:13px; color:#555; margin-bottom:10px;">¿Participará en esta actividad?</p>
                        <div style="display:flex; gap:8px;">
                            <button class="btn btn-sm btn-success btn-part" data-estado="si"><i class="fa fa-check"></i> Sí asistiré</button>
                            <button class="btn btn-sm btn-danger  btn-part" data-estado="no"><i class="fa fa-times"></i> No asistiré</button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($comision): ?>
                <!-- ── Participant list (commission only) ─────────────────── -->
                <?php $lista = act_rows('SELECT * FROM actividad_participantes WHERE actividad_id = ? ORDER BY updated_at DESC', 'i', $act_id); ?>
                <?php if (!empty($lista)): ?>
                <div class="part-box">
                    <h4><i class="fa fa-list"></i> Participantes (<?= $total_si ?> confirmados)</h4>
                    <div class="part-list">
                        <table>
                            <tr><th>Usuario</th><th>Filial</th><th>Estado</th><th>Actualizado</th></tr>
                            <?php foreach ($lista as $p): ?>
                            <tr>
                                <td><?= (int)$p['user_id'] ?></td>
                                <td><?= (int)$p['filial_id'] ?></td>
                                <td><?= $p['estado']==='si' ? '<span style="color:#3c763d;font-weight:600;">Sí</span>' : '<span style="color:#a94442;">No</span>' ?></td>
                                <td><?= htmlspecialchars($p['updated_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- ── Comments ──────────────────────────────────────────── -->
                <div class="comments-section">
                    <h4 style="margin-bottom:12px;">
                        <i class="fa fa-comments-o"></i>
                        Comentarios <span style="font-size:13px; color:#888; font-weight:normal;">(<?= count($comentarios) ?>)</span>
                    </h4>

                    <?php if (empty($comentarios)): ?>
                        <p style="color:#aaa; font-size:13px;">Aún no hay comentarios. ¡Sé el primero!</p>
                    <?php else: ?>
                        <?php foreach ($comentarios as $c): ?>
                        <div class="comment-card">
                            <div class="c-header">
                                <div class="c-circle"><?= (int)$c['filial_id'] ?: (int)$c['user_id'] ?></div>
                                <div class="c-meta">
                                    Usuario <?= (int)$c['user_id'] ?> &nbsp;·&nbsp;
                                    <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <div class="c-text"><?= htmlspecialchars($c['detalle'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if (!empty($c['adjuntos'])): ?>
                                <div class="adj-grid">
                                    <?php foreach ($c['adjuntos'] as $adj): ?>
                                        <?php $url = act_upload_url() . htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($adj['tipo'] === 'imagen'): ?>
                                            <a href="<?= $url ?>" class="vv-attach-img" title="<?= htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?>" data-download="<?= $url ?>">
                                                <img src="<?= $url ?>" class="adj-img" alt="imagen">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= $url ?>" class="vv-attach-pdf adj-pdf">
                                                <i class="fa fa-file-pdf-o"></i>
                                                <?= htmlspecialchars($adj['archivo'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- ── Comment form ──────────────────────────────────────── -->
                <div class="comment-form" style="margin-bottom:40px;">
                    <h4><i class="fa fa-pencil"></i> Agregar comentario</h4>
                    <div class="form-group">
                        <textarea id="c-detalle" class="form-control" rows="3" placeholder="Escribe tu comentario... (también puedes subir fotos del evento)" maxlength="2000"></textarea>
                    </div>
                    <div class="form-group">
                        <label style="font-size:13px; color:#666;"><i class="fa fa-camera"></i> Adjuntar imágenes o PDF <small>(JPG, PNG, PDF — máx 8 MB c/u)</small></label>
                        <input type="file" id="c-adjuntos" multiple accept=".jpg,.jpeg,.png,.pdf" style="display:block; margin-top:4px;">
                        <div class="adj-preview" id="c-preview"></div>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn-comentar">
                        <i class="fa fa-paper-plane"></i> Publicar
                    </button>
                </div>

                <div style="margin-bottom:20px;">
                    <a href="index.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver</a>
                </div>

            </div>

<script>
(function() {
    var actId = <?= $act_id ?>;

    // ── Participation ──────────────────────────────────────────────────────
    document.querySelectorAll('.btn-part').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var estado = this.dataset.estado;
            document.querySelectorAll('.btn-part').forEach(function(b){ b.disabled = true; });
            fetch('participar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'actividad_id=' + actId + '&estado=' + encodeURIComponent(estado)
            })
            .then(function(r){ return r.json(); })
            .then(function(resp) {
                if (resp.ok) { location.reload(); }
                else {
                    alert(resp.error || 'Error');
                    document.querySelectorAll('.btn-part').forEach(function(b){ b.disabled = false; });
                }
            })
            .catch(function() {
                alert('Error de comunicación');
                document.querySelectorAll('.btn-part').forEach(function(b){ b.disabled = false; });
            });
        });
    });

    // ── Delete activity ────────────────────────────────────────────────────
    var btnDel = document.getElementById('btn-del-act');
    if (btnDel) {
        btnDel.addEventListener('click', function() {
            var opts = { title: '¿Eliminar esta actividad?', text: 'Se eliminarán adjuntos, comentarios y participaciones.', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' };
            opts[_swalIconKey] = 'warning';
            Swal.fire(opts).then(function(result) {
                if (!(result === true || (result && result.isConfirmed))) return;
                fetch('delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + actId
                })
                .then(function(r){ return r.json(); })
                .then(function(resp) {
                    if (resp.ok) window.location.href = 'index.php';
                    else alert(resp.error || 'Error al eliminar');
                });
            });
        });
    }

    // ── Image preview ──────────────────────────────────────────────────────
    document.getElementById('c-adjuntos').addEventListener('change', function() {
        var preview = document.getElementById('c-preview');
        preview.innerHTML = '';
        var allowed = ['jpg','jpeg','png','pdf'];
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            var ext  = file.name.split('.').pop().toLowerCase();
            if (allowed.indexOf(ext) === -1) continue;
            if (ext !== 'pdf') {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            } else {
                var span = document.createElement('span');
                span.style.cssText = 'font-size:12px; color:#c0392b; display:flex; align-items:center; gap:4px;';
                span.innerHTML = '<i class="fa fa-file-pdf-o"></i>' + file.name;
                preview.appendChild(span);
            }
        }
    });

    // ── Submit comment ─────────────────────────────────────────────────────
    document.getElementById('btn-comentar').addEventListener('click', function() {
        var detalle = document.getElementById('c-detalle').value.trim();
        if (!detalle) { alert('El comentario no puede estar vacío'); return; }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Publicando...';

        var fd = new FormData();
        fd.append('actividad_id', actId);
        fd.append('detalle', detalle);
        var files = document.getElementById('c-adjuntos').files;
        for (var i = 0; i < files.length; i++) fd.append('adjuntos[]', files[i]);

        fetch('comentar.php', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(resp) {
            if (resp.ok) {
                location.reload();
            } else {
                alert(resp.error || 'Error al publicar');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-paper-plane"></i> Publicar';
            }
        })
        .catch(function() {
            alert('Error de comunicación');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Publicar';
        });
    });
})();
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
