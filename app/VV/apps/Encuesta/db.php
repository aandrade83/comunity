<?php
/**
 * Encuesta module — prepared-statement helpers
 * Reuses the existing $conn_db mysqli connection from utilities/db/manager.php
 * Include AFTER utilities/includes.php
 */

function enc_db(): mysqli {
    global $conn_db;
    db_connect('master');
    return $conn_db->mysqli_connector;
}

/** Fetch a single row or null */
function enc_row(string $sql, string $types = '', ...$params): ?array {
    $db   = enc_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return null;
    if ($types !== '' && count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/** Fetch all rows */
function enc_rows(string $sql, string $types = '', ...$params): array {
    $db   = enc_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return [];
    if ($types !== '' && count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res  = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

/** Execute INSERT/UPDATE/DELETE; returns insert_id for INSERT, affected_rows otherwise */
function enc_exec(string $sql, string $types, ...$params): int {
    $db   = enc_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $ret = (int)($stmt->insert_id ?: $stmt->affected_rows);
    $stmt->close();
    return $ret;
}

/** Count responses for a survey */
function enc_response_count(int $survey_id): int {
    $row = enc_row('SELECT COUNT(*) AS n FROM survey_responses WHERE survey_id = ?', 'i', $survey_id);
    return (int)($row['n'] ?? 0);
}

/** Get the response row for a filial on a given survey, or null */
function enc_get_response(int $survey_id, string $filial_id): ?array {
    return enc_row(
        'SELECT * FROM survey_responses WHERE survey_id = ? AND filial_id = ?',
        'is', $survey_id, $filial_id
    );
}

/** Load a full survey with its questions and options */
function enc_load_survey(int $survey_id): ?array {
    $survey = enc_row('SELECT * FROM surveys WHERE id = ?', 'i', $survey_id);
    if (!$survey) return null;

    $questions = enc_rows(
        'SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY position, id',
        'i', $survey_id
    );

    foreach ($questions as &$q) {
        $q['options'] = [];
        if (in_array($q['type'], ['radio', 'checkbox', 'dropdown'])) {
            $q['options'] = enc_rows(
                'SELECT * FROM survey_options WHERE question_id = ? ORDER BY position, id',
                'i', (int)$q['id']
            );
        }
    }
    unset($q);

    $survey['questions'] = $questions;
    return $survey;
}
