<?php
/**
 * survey_store.php  — AJAX endpoint to create/update surveys
 * Accepts: POST with JSON body
 * Returns: JSON {ok:bool, survey_id?:int, error?:string}
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Encuesta/db.php';

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

function json_out(array $data, int $code = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Commission only
if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] < 2) {
    json_out(['ok' => false, 'error' => 'Sin permiso'], 403);
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || !isset($body['title'])) {
    json_out(['ok' => false, 'error' => 'Payload inválido'], 400);
}

$survey_id   = isset($body['survey_id']) ? (int)$body['survey_id'] : 0;
$title       = trim($body['title'] ?? '');
$description = trim($body['description'] ?? '');
$questions   = $body['questions'] ?? [];
$filial      = $_SESSION['filial'] ?? '';

if ($title === '') {
    json_out(['ok' => false, 'error' => 'Título requerido']);
}
if (empty($questions)) {
    json_out(['ok' => false, 'error' => 'Al menos una pregunta requerida']);
}

// If editing, check survey exists and has no responses
if ($survey_id > 0) {
    $existing = enc_row('SELECT id FROM surveys WHERE id = ?', 'i', $survey_id);
    if (!$existing) {
        json_out(['ok' => false, 'error' => 'Encuesta no encontrada']);
    }
    if (enc_response_count($survey_id) > 0) {
        json_out(['ok' => false, 'error' => 'No se puede editar: ya tiene respuestas']);
    }
    // Delete existing questions + options
    $old_questions = enc_rows('SELECT id FROM survey_questions WHERE survey_id = ?', 'i', $survey_id);
    foreach ($old_questions as $oq) {
        enc_exec('DELETE FROM survey_options WHERE question_id = ?', 'i', (int)$oq['id']);
    }
    enc_exec('DELETE FROM survey_questions WHERE survey_id = ?', 'i', $survey_id);
    // Update survey header
    enc_exec(
        'UPDATE surveys SET title = ?, description = ? WHERE id = ?',
        'ssi', $title, $description, $survey_id
    );
} else {
    // Insert new survey
    $now       = date('Y-m-d H:i:s');
    $survey_id = enc_exec(
        'INSERT INTO surveys (title, description, status, show_results, created_by, created_at) VALUES (?,?,1,0,?,?)',
        'ssss', $title, $description, $filial, $now
    );
    if (!$survey_id) {
        json_out(['ok' => false, 'error' => 'Error al crear encuesta']);
    }
    $is_new_survey = true;
}

// Insert questions + options
$valid_types = ['text', 'textarea', 'radio', 'checkbox', 'dropdown', 'scale'];

foreach ($questions as $sort => $q) {
    $qtext    = trim($q['text'] ?? '');
    $qtype    = in_array($q['type'] ?? '', $valid_types) ? $q['type'] : 'text';
    $required = isset($q['required']) ? (int)(bool)$q['required'] : 1;
    $order    = isset($q['sort_order']) ? (int)$q['sort_order'] : $sort;

    if ($qtext === '') continue;

    $qid = enc_exec(
        'INSERT INTO survey_questions (survey_id, question, type, required, position) VALUES (?,?,?,?,?)',
        'issii', $survey_id, $qtext, $qtype, $required, $order
    );

    if (!$qid) continue;

    // Options for radio/checkbox/dropdown
    if (in_array($qtype, ['radio', 'checkbox', 'dropdown']) && !empty($q['options'])) {
        foreach ($q['options'] as $oi => $opt) {
            $otext = trim($opt['text'] ?? '');
            if ($otext === '') continue;
            $oorder = isset($opt['sort_order']) ? (int)$opt['sort_order'] : $oi;
            enc_exec(
                'INSERT INTO survey_options (question_id, option_text, position) VALUES (?,?,?)',
                'isi', $qid, $otext, $oorder
            );
        }
    }
}

// Notificar solo si es encuesta nueva (no edición)
if (!empty($is_new_survey)) {
    try {
        require_once ROOT_PATH . '/utilities/mail/notificaciones.php';
        vv_notificar('Encuesta', $title);
    } catch (\Throwable $_e) { /* no cortar el flujo */ }
}

json_out(['ok' => true, 'survey_id' => $survey_id]);
