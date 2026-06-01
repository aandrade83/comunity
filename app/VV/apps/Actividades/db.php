<?php
/**
 * Actividades module — DB helpers
 */

function act_db(): mysqli {
    global $conn_db;
    db_connect('master');
    return $conn_db->mysqli_connector;
}

function act_row(string $sql, string $types = '', ...$params): ?array {
    $db   = act_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return null;
    if ($types !== '' && count($params) > 0) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function act_rows(string $sql, string $types = '', ...$params): array {
    $db   = act_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return [];
    if ($types !== '' && count($params) > 0) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res  = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    return $rows;
}

function act_exec(string $sql, string $types, ...$params): int {
    $db   = act_db();
    $stmt = $db->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $ret = (int)($stmt->insert_id ?: $stmt->affected_rows);
    $stmt->close();
    return $ret;
}

// ── Participantes ────────────────────────────────────────────────────────────

function act_get_participante(int $actividad_id, int $user_id): ?array {
    return act_row(
        'SELECT * FROM actividad_participantes WHERE actividad_id = ? AND user_id = ?',
        'ii', $actividad_id, $user_id
    );
}

function act_count_si(int $actividad_id): int {
    $row = act_row(
        'SELECT COUNT(*) AS n FROM actividad_participantes WHERE actividad_id = ? AND estado = "si"',
        'i', $actividad_id
    );
    return (int)($row['n'] ?? 0);
}

// ── Adjuntos ─────────────────────────────────────────────────────────────────

/** Adjuntos de una actividad */
function act_adjuntos(int $actividad_id): array {
    return act_rows(
        'SELECT * FROM actividad_adjuntos WHERE entidad = "actividad" AND entidad_id = ? ORDER BY id',
        'i', $actividad_id
    );
}

/** Adjuntos de un comentario */
function act_adjuntos_comment(int $comentario_id): array {
    return act_rows(
        'SELECT * FROM actividad_adjuntos WHERE entidad = "comment" AND entidad_id = ? ORDER BY id',
        'i', $comentario_id
    );
}

function act_upload_dir(): string {
    return ROOT_PATH . '/apps/Actividades/uploads/adjuntos/';
}

function act_upload_url(): string {
    return BASE_URL . '/apps/Actividades/uploads/adjuntos/';
}

/**
 * Process uploaded files and insert records.
 * $entidad: 'actividad' or 'comment'
 * $entidad_id: the activity or comment ID
 */
function act_process_uploads(int $entidad_id, string $entidad = 'actividad'): void {
    if (empty($_FILES['adjuntos']['name'][0])) return;

    $allowed = ['jpg' => 'imagen', 'jpeg' => 'imagen', 'png' => 'imagen', 'pdf' => 'pdf'];
    $dir     = act_upload_dir();

    foreach ($_FILES['adjuntos']['name'] as $i => $name) {
        if ($_FILES['adjuntos']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) continue;
        if ($_FILES['adjuntos']['size'][$i] > 8 * 1024 * 1024) continue; // 8 MB max

        $filename = time() . '_' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['adjuntos']['tmp_name'][$i], $dir . $filename)) continue;

        act_exec(
            'INSERT INTO actividad_adjuntos (entidad, entidad_id, archivo, tipo) VALUES (?,?,?,?)',
            'siss', $entidad, $entidad_id, $filename, $allowed[$ext]
        );
    }
}

// ── Comentarios ──────────────────────────────────────────────────────────────

/** Comentarios de una actividad, con sus adjuntos cargados */
/*function act_comentarios(int $actividad_id): array {
    $rows = act_rows(
        'SELECT * FROM actividad_comentarios WHERE actividad_id = ? ORDER BY created_at ASC',
        'i', $actividad_id
    );
    foreach ($rows as &$c) {
        $c['adjuntos'] = act_adjuntos_comment((int)$c['id']);
    }
    unset($c);
    return $rows;
}*/
function act_comentarios(int $actividad_id): array {

    try {
        $rows = act_rows(
            'SELECT * FROM actividad_comentarios WHERE actividad_id = ? ORDER BY created_at ASC',
            'i',
            $actividad_id
        );
    } catch (Throwable $e) {
        // 🔥 aquí atrapamos el error real (query, tabla, etc)
        error_log("ERROR act_comentarios: " . $e->getMessage());
        return [];
    }

    if (!is_array($rows) || empty($rows)) {
        return [];
    }

    foreach ($rows as &$c) {
        $c['adjuntos'] = act_adjuntos_comment((int)$c['id']);
    }

    unset($c);

    return $rows;
}
