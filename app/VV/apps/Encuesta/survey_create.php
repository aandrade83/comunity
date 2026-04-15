<?php
include('../ui/main_head.php');
require_once ROOT_PATH . '/apps/Encuesta/db.php';

// Commission only
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 2) {
    header('Location: index.php');
    exit;
}

$edit_id = (int)(param('id') ?? 0);
$survey  = null;

if ($edit_id > 0) {
    $survey = enc_load_survey($edit_id);
    if (!$survey) {
        header('Location: index.php');
        exit;
    }
    // Can't edit if responses exist
    if (enc_response_count($edit_id) > 0) {
        header('Location: index.php?err=has_responses');
        exit;
    }
}

$survey_json = $survey ? json_encode($survey, JSON_UNESCAPED_UNICODE) : 'null';
?>

<style>
@media (max-width: 768px) {
    #side, .side, .sidebar, .rightcol, .col-right { display: none !important; }
}
#builder { max-width: 780px; }
.q-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 14px 16px;
    margin-bottom: 12px;
    position: relative;
}
.q-card .q-header {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    flex-wrap: wrap;
}
.q-card .q-text-wrap { flex: 1; min-width: 200px; }
.q-card .q-type-wrap { min-width: 160px; }
.q-card .q-req-wrap  { display: flex; align-items: center; gap: 4px; font-size: 13px; white-space: nowrap; }
.q-card .btn-q-del   {
    background: none; border: none; color: #c00;
    font-size: 20px; line-height: 1; cursor: pointer; padding: 0 4px;
}
.q-card .q-order-btns { display: flex; flex-direction: column; gap: 2px; }
.q-card .q-order-btns button {
    background: none; border: 1px solid #ccc; border-radius: 3px;
    font-size: 10px; line-height: 1.4; cursor: pointer; padding: 1px 4px;
}
.q-options-section { margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; }
.option-row { display: flex; gap: 6px; align-items: center; margin-bottom: 6px; }
.option-row input { flex: 1; }
.option-row .btn-opt-del {
    background: none; border: none; color: #c00; font-size: 18px; cursor: pointer; line-height: 1;
}
.btn-add-opt { font-size: 12px; }
.scale-preview { font-size: 12px; color: #888; margin-top: 6px; }
</style>

<section class="content">

    <div class="container">
        <div class="row">
            <div class="col-lg-8 breadcrumbf">
                <a href="index.php">Encuestas</a>
                <span class="diviver"></span>
                <?= $edit_id > 0 ? 'Editar Encuesta' : 'Nueva Encuesta' ?>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-xs-12">

                <div id="builder">

                    <div class="post" style="padding:20px;">
                        <h3 style="margin-top:0;"><?= $edit_id > 0 ? 'Editar Encuesta' : 'Nueva Encuesta' ?></h3>

                        <div class="form-group">
                            <label>Título <span style="color:red">*</span></label>
                            <input type="text" id="survey-title" class="form-control" maxlength="255" placeholder="Título de la encuesta">
                        </div>

                        <div class="form-group">
                            <label>Descripción <small>(opcional)</small></label>
                            <textarea id="survey-desc" class="form-control" rows="2" placeholder="Descripción breve..."></textarea>
                        </div>
                    </div>

                    <div id="questions-list" style="margin-top:12px;"></div>

                    <button type="button" id="btn-add-q" class="btn btn-default" style="margin-top:4px;" onclick="encAddQ()">
                        <i class="fa fa-plus"></i> Agregar Pregunta
                    </button>

                    <div class="text-right" style="margin-top:20px; margin-bottom:40px;">
                        <a href="index.php" class="btn btn-default" style="margin-right:8px;">Cancelar</a>
                        <button type="button" id="btn-save" class="btn btn-success btn-lg" onclick="encSave()">
                            <i class="fa fa-save"></i> Guardar Encuesta
                        </button>
                    </div>

                </div>

            </div>

<script>
// ── Global state ─────────────────────────────────────────────────────────
var encQuestions = [];
var encSurveyData = <?= $survey_json ?>;
var encSurveyId   = <?= $edit_id ?: 'null' ?>;
var encApiUrl     = '<?= BASE_URL ?>/apps/Encuesta/survey_store.php';

var ENC_TYPES = [
    {v:'text',     l:'Texto corto'},
    {v:'textarea', l:'Texto largo'},
    {v:'radio',    l:'Opcion unica'},
    {v:'checkbox', l:'Seleccion multiple'},
    {v:'dropdown', l:'Lista desplegable'},
    {v:'scale',    l:'Escala (0-100)'}
];

function encHasOptions(t) { return t==='radio'||t==='checkbox'||t==='dropdown'; }

function encEsc(s) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(s||''));
    return d.innerHTML;
}

// ── Button handlers (called via onclick attributes) ───────────────────────
function encAddQ() {
    encQuestions.push({id:null, text:'', type:'radio', required:true, options:[]});
    encRender();
    var cards = document.querySelectorAll('.q-card');
    if (cards.length) cards[cards.length-1].scrollIntoView({behavior:'smooth', block:'center'});
}

function encDelQ(idx) { encQuestions.splice(idx, 1); encRender(); }

function encUpQ(idx) {
    if (idx === 0) return;
    var t = encQuestions[idx-1]; encQuestions[idx-1] = encQuestions[idx]; encQuestions[idx] = t;
    encRender();
}

function encDnQ(idx) {
    if (idx >= encQuestions.length-1) return;
    var t = encQuestions[idx+1]; encQuestions[idx+1] = encQuestions[idx]; encQuestions[idx] = t;
    encRender();
}

function encAddOpt(idx) { encQuestions[idx].options.push({id:null, text:''}); encRender(); }
function encDelOpt(idx, oi) { encQuestions[idx].options.splice(oi, 1); encRender(); }

function encChangeType(idx, val) {
    encQuestions[idx].type = val;
    if (!encHasOptions(val)) encQuestions[idx].options = [];
    encRender();
}

function encChangeText(idx, val)      { encQuestions[idx].text = val; }
function encChangeOptText(idx, oi, val) { encQuestions[idx].options[oi].text = val; }
function encChangeReq(idx, checked)   { encQuestions[idx].required = checked; }

function encSave() {
    var title = document.getElementById('survey-title').value.trim();
    var desc  = document.getElementById('survey-desc').value.trim();
    if (!title) { alert('El titulo es obligatorio.'); return; }
    if (!encQuestions.length) { alert('Agregue al menos una pregunta.'); return; }
    for (var i = 0; i < encQuestions.length; i++) {
        var q = encQuestions[i];
        if (!q.text.trim()) { alert('La pregunta '+(i+1)+' no tiene texto.'); return; }
        if (encHasOptions(q.type) && !q.options.length) { alert('La pregunta '+(i+1)+' necesita al menos una opcion.'); return; }
        for (var j = 0; j < q.options.length; j++) {
            if (!q.options[j].text.trim()) { alert('La pregunta '+(i+1)+', opcion '+(j+1)+' esta vacia.'); return; }
        }
    }
    var payload = {survey_id:encSurveyId, title:title, description:desc, questions:[]};
    for (var i = 0; i < encQuestions.length; i++) {
        var q = encQuestions[i];
        var opts = [];
        for (var j = 0; j < q.options.length; j++) opts.push({id:q.options[j].id, text:q.options[j].text, sort_order:j});
        payload.questions.push({id:q.id, text:q.text, type:q.type, required:q.required?1:0, sort_order:i, options:opts});
    }
    var btn = document.getElementById('btn-save');
    btn.disabled = true; btn.textContent = 'Guardando...';
    fetch(encApiUrl, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)})
    .then(function(r){return r.json();})
    .then(function(resp){
        if (resp.ok) { window.location.href = 'index.php'; }
        else { alert(resp.error||'Error al guardar'); btn.disabled=false; btn.textContent='Guardar Encuesta'; }
    })
    .catch(function(){ alert('Error de comunicacion'); btn.disabled=false; btn.textContent='Guardar Encuesta'; });
}

// ── Render uses inline onXxx so dynamic elements work without delegation ──
function encRender() {
    var html = '';
    for (var idx = 0; idx < encQuestions.length; idx++) {
        var q = encQuestions[idx];
        var typeOpts = '';
        for (var ti = 0; ti < ENC_TYPES.length; ti++) {
            typeOpts += '<option value="'+ENC_TYPES[ti].v+'"'+(q.type===ENC_TYPES[ti].v?' selected':'')+'>'+ENC_TYPES[ti].l+'</option>';
        }
        var optHtml = '';
        if (encHasOptions(q.type)) {
            var rows = '';
            for (var oi = 0; oi < q.options.length; oi++) {
                rows += '<div class="option-row">'
                      + '<input type="text" class="form-control" value="'+encEsc(q.options[oi].text)+'" placeholder="Opcion '+(oi+1)+'"'
                      + ' oninput="encChangeOptText('+idx+','+oi+',this.value)">'
                      + '<button type="button" class="btn-opt-del" onclick="encDelOpt('+idx+','+oi+')">&#215;</button>'
                      + '</div>';
            }
            optHtml = '<div class="q-options-section"><div class="options-list">'+rows+'</div>'
                    + '<button type="button" class="btn btn-xs btn-default btn-add-opt" onclick="encAddOpt('+idx+')">+ Agregar opcion</button></div>';
        } else if (q.type === 'scale') {
            optHtml = '<div class="scale-preview">Escala deslizable de 0 a 100</div>';
        }
        html += '<div class="q-card">'
              + '<div class="q-header">'
              + '<div class="q-order-btns">'
              + '<button type="button" class="btn-q-up" onclick="encUpQ('+idx+')">&#9650;</button>'
              + '<button type="button" class="btn-q-dn" onclick="encDnQ('+idx+')">&#9660;</button>'
              + '</div>'
              + '<div class="q-text-wrap"><input type="text" class="form-control q-text" value="'+encEsc(q.text)+'" placeholder="Pregunta '+(idx+1)+'..."'
              + ' oninput="encChangeText('+idx+',this.value)"></div>'
              + '<div class="q-type-wrap"><select class="form-control q-type" onchange="encChangeType('+idx+',this.value)">'+typeOpts+'</select></div>'
              + '<label class="q-req-wrap"><input type="checkbox" class="q-req"'+(q.required?' checked':'')
              + ' onchange="encChangeReq('+idx+',this.checked)">Requerida</label>'
              + '<button type="button" class="btn-q-del" onclick="encDelQ('+idx+')">&#215;</button>'
              + '</div>'+optHtml+'</div>';
    }
    document.getElementById('questions-list').innerHTML = html;
}

// ── Init ──────────────────────────────────────────────────────────────────
if (encSurveyData) {
    document.getElementById('survey-title').value = encSurveyData.title || '';
    document.getElementById('survey-desc').value  = encSurveyData.description || '';
    for (var _i = 0; _i < (encSurveyData.questions||[]).length; _i++) {
        var _q = encSurveyData.questions[_i];
        var _opts = [];
        for (var _j = 0; _j < (_q.options||[]).length; _j++) {
            _opts.push({id:_q.options[_j].id, text:_q.options[_j].option_text||''});
        }
        encQuestions.push({id:_q.id, text:_q.question||'', type:_q.type, required:!!parseInt(_q.required,10), options:_opts});
    }
} else {
    encQuestions.push({id:null, text:'', type:'radio', required:true, options:[]});
}
encRender();
</script>

            <?php try { include('../ui/partials/side.php'); } catch (\Throwable $e) { /* sidebar no crítico */ } ?>
        </div>
    </div>

</section>

<?php include('../ui/partials/footer.php'); ?>
