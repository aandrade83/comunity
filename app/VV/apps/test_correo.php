<?php
/**
 * test_correo.php — Envía la plantilla "test" a todos los condominos con email_flag = 1.
 *
 * Acceso: solo rol 3 (superadmin).
 * URL:    /VV/apps/test_correo.php
 */

include('./ui/main_head.php');

if (!$superadmin) {
    header('Location: ' . BASE_URL . '/apps/Forum/index.php');
    exit;
}

require_once ROOT_PATH . '/utilities/mail/Mailer.php';
require_once ROOT_PATH . '/apps/plantillas/test.php';

// ── Obtener destinatarios ──────────────────────────────────────────────────
db_connect('master');
$db    = $GLOBALS['conn_db']->mysqli_connector;
$res   = $db->query('SELECT id, nombre, apellido, email FROM condominos WHERE email_flag = 1');
$destinatarios = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $destinatarios[] = $row;
    }
}

//print_r($destinatarios); exit;
// ── Enviar ─────────────────────────────────────────────────────────────────
$mailer  = new \VV\Mail\Mailer();
$asunto  = 'Correo prueba';
$enviados = 0;
$errores  = 0;
$log      = [];

foreach ($destinatarios as $c) {
    $html = plantilla_test($c);
    try {
        $mailer->send($c['email'], $asunto, $html, trim($c['nombre'] . ' ' . $c['apellido']));
        $enviados++;
        $log[] = ['ok' => true, 'email' => $c['email'], 'nombre' => $c['nombre'] . ' ' . $c['apellido']];
    } catch (\RuntimeException $e) {
        $errores++;
        $log[] = ['ok' => false, 'email' => $c['email'], 'nombre' => $c['nombre'] . ' ' . $c['apellido'], 'error' => $e->getMessage()];
    }
}
?>

<style>
.correo-wrap { max-width:720px; margin:30px auto; font-family:'Segoe UI',Arial,sans-serif; }
.correo-wrap h2 { font-size:20px; margin-bottom:16px; }
.stats { display:flex; gap:16px; margin-bottom:24px; }
.stat-box { padding:14px 22px; border-radius:6px; font-size:15px; font-weight:600; }
.stat-ok  { background:#dff0d8; color:#2e7d32; }
.stat-err { background:#f2dede; color:#a94442; }
.stat-tot { background:#d9edf7; color:#31708f; }
.log-table { width:100%; border-collapse:collapse; font-size:13px; }
.log-table th { background:#f5f5f5; padding:8px 12px; text-align:left; border:1px solid #ddd; }
.log-table td { padding:7px 12px; border:1px solid #ddd; }
.row-ok  { background:#f6fff6; }
.row-err { background:#fff6f6; }
.badge-ok  { color:#27ae60; font-weight:700; }
.badge-err { color:#e74c3c; font-weight:700; }
</style>

<section class="content">
<div class="container">
<div class="correo-wrap">

    <h2><i class="fa fa-envelope"></i> Resultado de envío</h2>

    <div class="stats">
        <div class="stat-box stat-tot">Total: <?= count($destinatarios) ?></div>
        <div class="stat-box stat-ok" >Enviados: <?= $enviados ?></div>
        <div class="stat-box stat-err">Errores: <?= $errores ?></div>
    </div>

    <?php if (empty($destinatarios)): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i>
            No hay condominos con <code>email_flag = 1</code>.
        </div>
    <?php else: ?>
        <table class="log-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($log as $i => $entry): ?>
                <tr class="<?= $entry['ok'] ? 'row-ok' : 'row-err' ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($entry['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($entry['email'],  ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($entry['ok']): ?>
                            <span class="badge-ok">✓ Enviado</span>
                        <?php else: ?>
                            <span class="badge-err">✗ Error</span>
                            <br><small style="color:#999;"><?= htmlspecialchars($entry['error'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top:20px;">
        <a href="<?= BASE_URL ?>/apps/Forum/index.php" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

</div>
</div>
</section>

<?php include('./ui/partials/footer.php'); ?>
