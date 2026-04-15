<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Encuesta/db.php';

$survey_id = (int)(param('id') ?? 0);
$filial    = $_SESSION['filial'] ?? '';
$comision  = isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 2;

if (!$survey_id) {
    header('Location: index.php');
    exit;
}

$survey = enc_load_survey($survey_id);
if (!$survey) {
    header('Location: index.php');
    exit;
}

// Access control
if (!$comision) {
    $my_response = enc_get_response($survey_id, $filial);
    if (!$my_response || !(int)$survey['show_results']) {
        header('Location: index.php');
        exit;
    }
}

$total_responses = enc_response_count($survey_id);

// Aggregate answers per question
$results = []; // question_id => aggregated data
foreach ($survey['questions'] as $q) {
    $qid  = (int)$q['id'];
    $type = $q['type'];

    $raw_answers = enc_rows(
        'SELECT sa.answer FROM survey_answers sa
         JOIN survey_responses sr ON sr.id = sa.response_id
         WHERE sa.question_id = ? AND sr.survey_id = ?',
        'ii', $qid, $survey_id
    );

    $data = [
        'question' => $q['question'],
        'type'     => $type,
        'count'    => count($raw_answers),
        'options'  => $q['options'],
    ];

    if (in_array($type, ['radio', 'dropdown'])) {
        // Count per option
        $counts = [];
        foreach ($q['options'] as $opt) {
            $counts[$opt['option_text']] = 0;
        }
        foreach ($raw_answers as $a) {
            $val = $a['answer'];
            if (isset($counts[$val])) $counts[$val]++;
            else $counts[$val] = 1;
        }
        $data['counts'] = $counts;

    } elseif ($type === 'checkbox') {
        // Answers stored as "opt1||opt2"
        $counts = [];
        foreach ($q['options'] as $opt) {
            $counts[$opt['option_text']] = 0;
        }
        foreach ($raw_answers as $a) {
            $parts = explode('||', $a['answer'] ?? '');
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') {
                    if (isset($counts[$p])) $counts[$p]++;
                    else $counts[$p] = 1;
                }
            }
        }
        $data['counts'] = $counts;

    } elseif ($type === 'scale') {
        $vals = array_filter(array_map(function($a) {
            return is_numeric($a['answer']) ? (float)$a['answer'] : null;
        }, $raw_answers));
        $data['average'] = count($vals) ? round(array_sum($vals) / count($vals), 1) : 0;
        // Distribution buckets 0-19,20-39,40-59,60-79,80-100
        $buckets = ['0–19'=>0,'20–39'=>0,'40–59'=>0,'60–79'=>0,'80–100'=>0];
        foreach ($vals as $v) {
            if ($v <= 19)      $buckets['0–19']++;
            elseif ($v <= 39)  $buckets['20–39']++;
            elseif ($v <= 59)  $buckets['40–59']++;
            elseif ($v <= 79)  $buckets['60–79']++;
            else               $buckets['80–100']++;
        }
        $data['buckets'] = $buckets;

    } else {
        // text / textarea — list of responses
        $texts = array_filter(array_map(function($a) {
            return trim($a['answer'] ?? '');
        }, $raw_answers));
        $data['texts'] = array_values($texts);
    }

    $results[$qid] = $data;
}
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
.result-block {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 16px 18px;
    margin-bottom: 16px;
}
.result-block .q-title {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 12px;
}
.result-block .chart-wrap {
    max-width: 420px;
    margin: 0 auto;
}
.result-block .text-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 260px;
    overflow-y: auto;
}
.result-block .text-list li {
    padding: 5px 8px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
    color: #333;
}
.result-block .text-list li:last-child { border-bottom: none; }
.scale-avg { font-size: 28px; font-weight: 700; color: #337ab7; text-align: center; margin-bottom: 8px; }
.scale-avg small { font-size: 13px; font-weight: normal; color: #888; display:block; }
.bar-simple { margin-bottom: 6px; }
.bar-simple .bar-label { font-size: 12px; margin-bottom: 2px; display: flex; justify-content: space-between; }
.bar-simple .bar-track { background: #e9e9e9; border-radius: 3px; height: 14px; overflow: hidden; }
.bar-simple .bar-fill  { background: #337ab7; height: 100%; border-radius: 3px; transition: width 0.6s ease; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Encuestas</a>
                <span class="diviver"></span>
                Resultados: <?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-md-8 col-xs-12">

                <div class="post" style="padding:16px 20px; margin-bottom:14px;">
                    <h3 style="margin-top:0;"><?= htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!empty($survey['description'])): ?>
                        <p style="color:#555; margin:0 0 6px 0;"><?= htmlspecialchars($survey['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p style="font-size:13px; color:#888; margin:0;">
                        <strong><?= $total_responses ?></strong> respuesta(s) registrada(s)
                    </p>
                </div>

                <?php if ($total_responses === 0): ?>
                    <div class="result-block">
                        <p style="text-align:center; color:#888;">Aún no hay respuestas para esta encuesta.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($survey['questions'] as $idx => $q):
                        $qid  = (int)$q['id'];
                        $data = $results[$qid] ?? null;
                        if (!$data) continue;
                        $type = $data['type'];
                    ?>
                    <div class="result-block">
                        <div class="q-title"><?= ($idx + 1) ?>. <?= htmlspecialchars($data['question'], ENT_QUOTES, 'UTF-8') ?></div>
                        <p style="font-size:12px; color:#aaa; margin-bottom:10px;">
                            <?= $data['count'] ?> respuesta(s) &nbsp;·&nbsp;
                            <?= ucfirst(str_replace(['radio','checkbox','dropdown','text','textarea','scale'],
                                                    ['Opción única','Selección múltiple','Desplegable','Texto corto','Texto largo','Escala'],
                                                    $type)) ?>
                        </p>

                        <?php if (in_array($type, ['radio', 'dropdown'])): ?>
                            <div class="chart-wrap">
                                <canvas id="chart-<?= $qid ?>" height="200"></canvas>
                            </div>

                        <?php elseif ($type === 'checkbox'): ?>
                            <div class="chart-wrap" style="max-width:560px;">
                                <canvas id="chart-<?= $qid ?>" height="<?= max(80, count($data['counts']) * 36) ?>"></canvas>
                            </div>

                        <?php elseif ($type === 'scale'): ?>
                            <div class="scale-avg">
                                <?= $data['average'] ?>
                                <small>promedio de <?= $data['count'] ?> respuesta(s)</small>
                            </div>
                            <?php foreach ($data['buckets'] as $label => $cnt):
                                $pct = $data['count'] > 0 ? round($cnt / $data['count'] * 100) : 0;
                            ?>
                            <div class="bar-simple">
                                <div class="bar-label">
                                    <span><?= $label ?></span>
                                    <span><?= $cnt ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <?php if (empty($data['texts'])): ?>
                                <p style="color:#aaa; font-size:13px;">Sin respuestas de texto.</p>
                            <?php else: ?>
                                <ul class="text-list">
                                    <?php foreach ($data['texts'] as $txt): ?>
                                        <li><?= htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>

                <div style="margin-bottom:40px;">
                    <a href="index.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver</a>
                </div>

            </div>

<!-- Chart.js -->
<script src="<?= ASSETS_URL ?>/libs/chart-js/Chart.bundle.min.js"></script>

<script>
// Chart colors palette
var COLORS = [
    '#337ab7','#5cb85c','#f0ad4e','#d9534f','#9b59b6',
    '#1abc9c','#e67e22','#2980b9','#27ae60','#c0392b',
    '#8e44ad','#16a085','#f39c12','#2c3e50','#7f8c8d'
];

<?php foreach ($survey['questions'] as $q):
    $qid  = (int)$q['id'];
    $data = $results[$qid] ?? null;
    if (!$data) continue;
    $type = $data['type'];

    if (in_array($type, ['radio', 'dropdown'])):
        $labels = json_encode(array_keys($data['counts']), JSON_UNESCAPED_UNICODE);
        $values = json_encode(array_values($data['counts']));
?>
(function() {
    var ctx = document.getElementById('chart-<?= $qid ?>');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?= $labels ?>,
            datasets: [{ data: <?= $values ?>, backgroundColor: COLORS.slice(0, <?= count($data['counts']) ?>) }]
        },
        options: {
            legend: { position: 'bottom' },
            tooltips: {
                callbacks: {
                    label: function(item, d) {
                        var val   = d.datasets[0].data[item.index];
                        var total = d.datasets[0].data.reduce(function(a,b){return a+b;},0);
                        var pct   = total ? Math.round(val/total*100) : 0;
                        return d.labels[item.index] + ': ' + val + ' (' + pct + '%)';
                    }
                }
            }
        }
    });
})();

    <?php elseif ($type === 'checkbox'):
        $labels = json_encode(array_keys($data['counts']), JSON_UNESCAPED_UNICODE);
        $values = json_encode(array_values($data['counts']));
        $n      = $data['count'] ?: 1;
    ?>
(function() {
    var ctx = document.getElementById('chart-<?= $qid ?>');
    if (!ctx) return;
    var vals = <?= $values ?>;
    var n    = <?= $n ?>;
    new Chart(ctx.getContext('2d'), {
        type: 'horizontalBar',
        data: {
            labels: <?= $labels ?>,
            datasets: [{
                label: 'Respuestas',
                data: vals,
                backgroundColor: '#337ab7'
            }]
        },
        options: {
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }]
            },
            legend: { display: false },
            tooltips: {
                callbacks: {
                    label: function(item) {
                        var pct = Math.round(item.xLabel / n * 100);
                        return item.xLabel + ' (' + pct + '%)';
                    }
                }
            }
        }
    });
})();

    <?php endif; ?>
<?php endforeach; ?>
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
