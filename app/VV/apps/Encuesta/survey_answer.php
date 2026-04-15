<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Encuesta/db.php';

function renderQuestion(array $q, bool $preview): string {
    $qid  = (int)$q['id'];
    $name = 'q_' . $qid;
    $req  = (int)$q['required'];
    $text = htmlspecialchars($q['question'], ENT_QUOTES, 'UTF-8');
    $mark = $req ? '<span class="q-required-mark">*</span>' : '';

    $html = '<div class="question-block" data-qid="' . $qid . '" data-required="' . $req . '" data-type="' . $q['type'] . '">';
    $html .= '<span class="q-label">' . $text . $mark . '</span>';

    switch ($q['type']) {
        case 'text':
            $html .= '<input type="text" class="form-control q-answer" name="' . $name . '" ' . ($preview ? 'disabled' : '') . ' maxlength="500" placeholder="Su respuesta...">';
            break;

        case 'textarea':
            $html .= '<textarea class="form-control q-answer" name="' . $name . '" rows="3" ' . ($preview ? 'disabled' : '') . ' maxlength="2000" placeholder="Su respuesta..."></textarea>';
            break;

        case 'radio':
            foreach ($q['options'] as $opt) {
                $oval = htmlspecialchars($opt['option_text'], ENT_QUOTES, 'UTF-8');
                $html .= '<div class="radio" style="margin:4px 0;">'
                       . '<label><input type="radio" class="q-answer" name="' . $name . '" value="' . $oval . '" ' . ($preview ? 'disabled' : '') . '> ' . $oval . '</label>'
                       . '</div>';
            }
            break;

        case 'checkbox':
            foreach ($q['options'] as $opt) {
                $oval = htmlspecialchars($opt['option_text'], ENT_QUOTES, 'UTF-8');
                $html .= '<div class="checkbox" style="margin:4px 0;">'
                       . '<label><input type="checkbox" class="q-answer q-check" name="' . $name . '[]" value="' . $oval . '" ' . ($preview ? 'disabled' : '') . '> ' . $oval . '</label>'
                       . '</div>';
            }
            break;

        case 'dropdown':
            $html .= '<select class="form-control q-answer" name="' . $name . '" ' . ($preview ? 'disabled' : '') . '>';
            $html .= '<option value="">-- Seleccione --</option>';
            foreach ($q['options'] as $opt) {
                $oval = htmlspecialchars($opt['option_text'], ENT_QUOTES, 'UTF-8');
                $html .= '<option value="' . $oval . '">' . $oval . '</option>';
            }
            $html .= '</select>';
            break;

        case 'scale':
            $html .= '<div class="scale-row">'
                   . '<span>0</span>'
                   . '<input type="range" min="0" max="100" value="50" class="q-answer q-scale" name="' . $name . '" ' . ($preview ? 'disabled' : '') . ' oninput="this.nextElementSibling.textContent=this.value">'
                   . '<span class="scale-val">50</span>'
                   . '<span>100</span>'
                   . '</div>';
            break;
    }

    $html .= '</div>';
    return $html;
}

$survey_id = (int)(param('id') ?? 0);
$filial    = $_SESSION['filial'] ?? '';
$comision  = isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 2;

if (!$survey_id) {
    header('Location: index.php');
    exit;
}

$survey = enc_load_survey($survey_id);

if (!$survey || (!$comision && !(int)$survey['status'])) {
    header('Location: index.php');
    exit;
}

$already_answered = $filial !== '' ? (enc_get_response($survey_id, $filial) !== null) : false;
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
.answer-form { max-width: 720px; }
.question-block {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 16px 18px;
    margin-bottom: 14px;
}
.question-block .q-label {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 10px;
    display: block;
}
.question-block .q-required-mark { color: #c00; margin-left: 3px; }
.scale-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 4px;
}
.scale-row input[type=range] { flex: 1; }
.scale-val { min-width: 40px; font-weight: 700; font-size: 15px; }
.answered-box {
    background: #dff0d8;
    border: 1px solid #d6e9c6;
    border-radius: 4px;
    padding: 16px 20px;
    margin-bottom: 16px;
}
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Encuestas</a>
                <span class="diviver"></span>
                <?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-md-8 col-xs-12 answer-form">

                <div class="post" style="padding:20px 22px; margin-bottom:14px;">
                    <h3 style="margin-top:0;"><?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!empty($survey['description'])): ?>
                        <p style="color:#555; margin-bottom:0;"><?= htmlspecialchars($survey['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($already_answered): ?>

                    <div class="answered-box">
                        <strong><i class="fa fa-check-circle"></i> Ya registró su respuesta.</strong>
                        <?php if ((int)$survey['show_results'] || $comision): ?>
                            <p style="margin:8px 0 0 0;">
                                <a href="survey_results.php?id=<?= $survey_id ?>" class="btn btn-sm btn-primary">
                                    <i class="fa fa-bar-chart"></i> Ver resultados
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>

                <?php elseif ($comision): ?>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Modo vista previa (comisión). Su respuesta no será registrada.
                    </div>

                    <?php foreach ($survey['questions'] as $q): ?>
                        <?= renderQuestion($q, true) ?>
                    <?php endforeach; ?>

                <?php else: ?>

                    <p style="color:#555; font-size:13px; margin-bottom:16px;">
                        Su respuesta es anónima y será utilizada únicamente con fines de mejora continua.
                    </p>

                    <form id="answer-form" novalidate>
                        <input type="hidden" name="survey_id" value="<?= $survey_id ?>">

                        <?php foreach ($survey['questions'] as $q): ?>
                            <?= renderQuestion($q, false) ?>
                        <?php endforeach; ?>

                        <div style="text-align:center; margin: 20px 0 40px 0;">
                            <button type="submit" class="btn btn-success btn-lg" id="btn-submit">
                                <i class="fa fa-paper-plane"></i> Enviar Respuesta
                            </button>
                        </div>
                    </form>

                <?php endif; ?>

            </div>

<script>
document.getElementById('answer-form') && document.getElementById('answer-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Client-side required validation
    var blocks = document.querySelectorAll('.question-block');
    for (var i = 0; i < blocks.length; i++) {
        var block = blocks[i];
        if (parseInt(block.dataset.required, 10) !== 1) continue;
        var type  = block.dataset.type;
        var valid = false;

        if (type === 'radio') {
            valid = !!block.querySelector('.q-answer:checked');
        } else if (type === 'checkbox') {
            valid = !!block.querySelector('.q-check:checked');
        } else if (type === 'scale') {
            valid = true; // always has a value
        } else {
            var inp = block.querySelector('.q-answer');
            valid = inp && inp.value.trim() !== '';
        }

        if (!valid) {
            var label = block.querySelector('.q-label');
            alert('Por favor complete: ' + (label ? label.textContent.trim() : 'Pregunta ' + (i + 1)));
            block.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }

    // Build answers array
    var answers = [];
    blocks.forEach(function(block) {
        var qid  = parseInt(block.dataset.qid, 10);
        var type = block.dataset.type;

        if (type === 'checkbox') {
            var checked = block.querySelectorAll('.q-check:checked');
            var vals = [];
            checked.forEach(function(cb) { vals.push(cb.value); });
            answers.push({ question_id: qid, answer: vals.join('||') });
        } else {
            var inp = block.querySelector('.q-answer');
            answers.push({ question_id: qid, answer: inp ? inp.value : '' });
        }
    });

    var btn = document.getElementById('btn-submit');
    btn.disabled    = true;
    btn.textContent = 'Enviando...';

    fetch('actions/actions.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
            ac:        'submit_survey',
            survey_id: <?= $survey_id ?>,
            answers:   answers
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
        if (resp.ok) {
            var opts = { title: 'Gracias', text: 'Su respuesta fue registrada correctamente.', showCancelButton: false };
            opts[_swalIconKey] = 'success';
            Swal.fire(opts).then(function() {
                window.location.href = 'index.php';
            });
        } else {
            alert(resp.error || 'Error al enviar');
            btn.disabled    = false;
            btn.textContent = 'Enviar Respuesta';
        }
    })
    .catch(function() {
        alert('Error de comunicación');
        btn.disabled    = false;
        btn.textContent = 'Enviar Respuesta';
    });
});
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
