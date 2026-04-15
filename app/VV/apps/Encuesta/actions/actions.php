<?php
ob_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/VV/utilities/includes.php';
require_once ROOT_PATH . '/apps/Encuesta/db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');

function json_exit(array $data, int $code = 200): void {
    if (ob_get_length()) ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['user'])) {
    json_exit(['ok' => false, 'error' => 'Sesión expirada'], 401);
}

$filial   = $_SESSION['filial'] ?? '';
$comision = isset($_SESSION['rol']) && (int)$_SESSION['rol'] === 2;

// Detect action from form-encoded POST or JSON body
$ac = $_POST['ac'] ?? null;

// Support JSON body (from fetch with Content-Type: application/json)
$raw  = file_get_contents('php://input');
$body = null;
if ($raw && !$ac) {
    $body = json_decode($raw, true);
    $ac   = $body['ac'] ?? null;
}

switch ($ac) {

    // ── Submit survey answer ────────────────────────────────────────────────
    case 'submit_survey':
        $survey_id = (int)($body['survey_id'] ?? 0);
        $answers   = $body['answers']   ?? [];

        if (!$survey_id) json_exit(['ok' => false, 'error' => 'Encuesta inválida']);
        if ($filial === '') json_exit(['ok' => false, 'error' => 'Sesión inválida']);

        // Check survey exists and is active
        $survey = enc_row('SELECT * FROM surveys WHERE id = ? AND status = 1', 'i', $survey_id);
        if (!$survey) json_exit(['ok' => false, 'error' => 'Encuesta no disponible']);

        // Check not already answered (UNIQUE constraint would catch it too)
        if (enc_get_response($survey_id, $filial)) {
            json_exit(['ok' => false, 'error' => 'Ya registró su respuesta']);
        }

        // Load questions for required validation
        $questions = enc_rows(
            'SELECT id, required, type FROM survey_questions WHERE survey_id = ? ORDER BY position, id',
            'i', $survey_id
        );

        // Validate required questions
        $answer_map = [];
        if (is_array($answers)) {
            foreach ($answers as $a) {
                $answer_map[(int)($a['question_id'] ?? 0)] = $a['answer'] ?? '';
            }
        }
        foreach ($questions as $q) {
            if (!(int)$q['required']) continue;
            $val = trim($answer_map[(int)$q['id']] ?? '');
            if ($val === '') {
                json_exit(['ok' => false, 'error' => 'Por favor complete todas las preguntas requeridas']);
            }
        }

        // Insert response row
        $now         = date('Y-m-d H:i:s');
        $response_id = enc_exec(
            'INSERT INTO survey_responses (survey_id, filial_id, created_at) VALUES (?,?,?)',
            'iss', $survey_id, $filial, $now
        );

        if (!$response_id) {
            json_exit(['ok' => false, 'error' => 'Error al registrar respuesta (posiblemente ya existe)']);
        }

        // Insert answers
        foreach ($questions as $q) {
            $qid = (int)$q['id'];
            $val = $answer_map[$qid] ?? '';
            enc_exec(
                'INSERT INTO survey_answers (response_id, question_id, answer) VALUES (?,?,?)',
                'iis', $response_id, $qid, (string)$val
            );
        }

        json_exit(['ok' => true]);
        break;

    // ── Activate survey ─────────────────────────────────────────────────────
    case 'activate':
        if (!$comision) json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_exit(['ok' => false, 'error' => 'ID inválido']);
        enc_exec('UPDATE surveys SET status = 1 WHERE id = ?', 'i', $id);
        json_exit(['ok' => true]);
        break;

    // ── Deactivate survey ───────────────────────────────────────────────────
    case 'deactivate':
        if (!$comision) json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_exit(['ok' => false, 'error' => 'ID inválido']);
        enc_exec('UPDATE surveys SET status = 0 WHERE id = ?', 'i', $id);
        json_exit(['ok' => true]);
        break;

    // ── Toggle results visibility ────────────────────────────────────────────
    case 'toggle_results':
        if (!$comision) json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_exit(['ok' => false, 'error' => 'ID inválido']);
        enc_exec('UPDATE surveys SET show_results = 1 - show_results WHERE id = ?', 'i', $id);
        json_exit(['ok' => true]);
        break;

    // ── Delete survey ────────────────────────────────────────────────────────
    case 'delete':
        if (!$comision) json_exit(['ok' => false, 'error' => 'Sin permiso'], 403);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_exit(['ok' => false, 'error' => 'ID inválido']);

        // Delete answers → responses → options → questions → survey
        $responses = enc_rows('SELECT id FROM survey_responses WHERE survey_id = ?', 'i', $id);
        foreach ($responses as $r) {
            enc_exec('DELETE FROM survey_answers WHERE response_id = ?', 'i', (int)$r['id']);
        }
        enc_exec('DELETE FROM survey_responses WHERE survey_id = ?', 'i', $id);

        $questions_del = enc_rows('SELECT id FROM survey_questions WHERE survey_id = ?', 'i', $id);
        foreach ($questions_del as $qd) {
            enc_exec('DELETE FROM survey_options WHERE question_id = ?', 'i', (int)$qd['id']);
        }
        enc_exec('DELETE FROM survey_questions WHERE survey_id = ?', 'i', $id);
        enc_exec('DELETE FROM surveys WHERE id = ?', 'i', $id);

        json_exit(['ok' => true]);
        break;

    default:
        json_exit(['ok' => false, 'error' => 'Acción desconocida'], 400);
}
