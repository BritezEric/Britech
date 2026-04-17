<?php
// ╔══════════════════════════════════════════════════════════╗
// ║  BRITECH — api/storage.php                              ║
// ║  Almacenamiento genérico JSON en MySQL                  ║
// ╚══════════════════════════════════════════════════════════╝

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$entity = $_GET['entity'] ?? null;

function resp($data, $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$entity) resp(['ok' => false, 'error' => 'Entidad no especificada'], 400);
$entity = preg_replace('/[^a-zA-Z0-9_-]/', '', $entity);

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT payload FROM storage WHERE entity = ?');
    $stmt->execute([$entity]);
    $row = $stmt->fetch();
    if (!$row) resp(['ok' => true, 'data' => []]);
    $payload = json_decode($row['payload'], true) ?? [];
    resp(['ok' => true, 'data' => $payload]);
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if ($body === null || !array_key_exists('data', $body))
        resp(['ok' => false, 'error' => 'JSON inválido o falta propiedad data'], 400);

    $payload = json_encode($body['data'], JSON_UNESCAPED_UNICODE);
    $pdo->prepare('INSERT INTO storage (entity, payload) VALUES (?, ?) ON DUPLICATE KEY UPDATE payload=VALUES(payload), updated_at=CURRENT_TIMESTAMP')
        ->execute([$entity, $payload]);
    resp(['ok' => true, 'saved' => true]);
}

resp(['ok' => false, 'error' => 'Método no permitido'], 405);
