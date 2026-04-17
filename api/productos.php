<?php
// ╔══════════════════════════════════════════════════════════╗
// ║  BRITECH — api/productos.php                            ║
// ║  GET / POST / PUT / DELETE  →  tabla productos + precios║
// ╚══════════════════════════════════════════════════════════╝

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

function resp($data, $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function err($msg, $code = 400): void {
    resp(['ok' => false, 'error' => $msg], $code);
}

// ─── GET ────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $sql = "
        SELECT p.*,
               MAX(CASE WHEN pr.id_rol = 1 THEN pr.precio END) AS precio_minorista,
               MAX(CASE WHEN pr.id_rol = 2 THEN pr.precio END) AS precio_mayorista
        FROM productos p
        LEFT JOIN precios pr ON pr.id_producto = p.id_producto
        WHERE p.activo = 1
    ";
    if ($id) {
        $stmt = $pdo->prepare($sql . " AND p.id_producto = ? GROUP BY p.id_producto");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) err('Producto no encontrado', 404);
        resp(['ok' => true, 'data' => formatProduct($row)]);
    }
    $stmt = $pdo->query($sql . " GROUP BY p.id_producto ORDER BY p.nombre ASC");
    resp(['ok' => true, 'data' => array_map('formatProduct', $stmt->fetchAll())]);
}

// ─── POST ───────────────────────────────────────────────────
if ($method === 'POST') {
    $nombre    = trim($input['nombre'] ?? '');
    $desc      = trim($input['descripcion'] ?? '');
    $stock     = (int)($input['stock'] ?? 0);
    $compra    = (float)($input['compra'] ?? 0);
    $minorista = (float)($input['precio_minorista'] ?? 0);
    $mayorista = (float)($input['precio_mayorista'] ?? 0);
    $imagenes  = $input['imagenes'] ?? [];

    if (!$nombre)             err('El nombre es requerido');
    if (!$minorista || !$mayorista) err('Los precios son requeridos');

    $pdo->beginTransaction();
    try {
        $extra = json_encode(['compra' => $compra, 'imagenes' => $imagenes]);
        $pdo->prepare("INSERT INTO productos (nombre, descripcion, stock, activo) VALUES (?, ?, ?, 1)")
            ->execute([$nombre, $desc . '||' . $extra, $stock]);
        $id = $pdo->lastInsertId();

        $stmtP = $pdo->prepare("INSERT INTO precios (id_producto, id_rol, precio) VALUES (?, ?, ?)");
        $stmtP->execute([$id, 1, $minorista]);
        $stmtP->execute([$id, 2, $mayorista]);

        if ($stock > 0) {
            $pdo->prepare("INSERT INTO movimientos (id_producto, tipo, cantidad, descripcion) VALUES (?, 'ingreso', ?, 'Stock inicial')")
                ->execute([$id, $stock]);
        }

        $pdo->commit();
        resp(['ok' => true, 'id' => $id, 'message' => 'Producto creado'], 201);
    } catch (Exception $e) {
        $pdo->rollBack();
        err('Error al crear producto: ' . $e->getMessage(), 500);
    }
}

// ─── PUT ────────────────────────────────────────────────────
if ($method === 'PUT') {
    $id        = (int)($input['id'] ?? 0);
    $nombre    = trim($input['nombre'] ?? '');
    $desc      = trim($input['descripcion'] ?? '');
    $stock     = isset($input['stock']) ? (int)$input['stock'] : null;
    $compra    = (float)($input['compra'] ?? 0);
    $minorista = (float)($input['precio_minorista'] ?? 0);
    $mayorista = (float)($input['precio_mayorista'] ?? 0);
    $imagenes  = $input['imagenes'] ?? null;

    if (!$id)     err('ID requerido');
    if (!$nombre) err('El nombre es requerido');

    $stmtGet = $pdo->prepare("SELECT descripcion, stock FROM productos WHERE id_producto = ?");
    $stmtGet->execute([$id]);
    $current = $stmtGet->fetch();
    if (!$current) err('Producto no encontrado', 404);

    $parts        = explode('||', $current['descripcion'], 2);
    $currentExtra = isset($parts[1]) ? json_decode($parts[1], true) : [];
    $newImages    = $imagenes !== null ? $imagenes : ($currentExtra['imagenes'] ?? []);
    $extra        = json_encode(['compra' => $compra, 'imagenes' => $newImages]);

    $pdo->beginTransaction();
    try {
        if ($stock !== null) {
            $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, stock=? WHERE id_producto=?")
                ->execute([$nombre, $desc . '||' . $extra, $stock, $id]);
        } else {
            $pdo->prepare("UPDATE productos SET nombre=?, descripcion=? WHERE id_producto=?")
                ->execute([$nombre, $desc . '||' . $extra, $id]);
        }

        $stmtP = $pdo->prepare("INSERT INTO precios (id_producto, id_rol, precio) VALUES (?,?,?) ON DUPLICATE KEY UPDATE precio=VALUES(precio)");
        $stmtP->execute([$id, 1, $minorista]);
        $stmtP->execute([$id, 2, $mayorista]);

        if ($stock !== null && $stock != $current['stock']) {
            $diff = $stock - $current['stock'];
            $pdo->prepare("INSERT INTO movimientos (id_producto, tipo, cantidad, descripcion) VALUES (?,?,?,'Ajuste manual de stock')")
                ->execute([$id, $diff > 0 ? 'ingreso' : 'egreso', abs($diff)]);
        }

        $pdo->commit();
        resp(['ok' => true, 'message' => 'Producto actualizado']);
    } catch (Exception $e) {
        $pdo->rollBack();
        err('Error al actualizar: ' . $e->getMessage(), 500);
    }
}

// ─── DELETE ─────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
    if (!$id) err('ID requerido');
    $stmt = $pdo->prepare("UPDATE productos SET activo=0 WHERE id_producto=?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) err('Producto no encontrado', 404);
    resp(['ok' => true, 'message' => 'Producto eliminado']);
}

// ─── Helper ─────────────────────────────────────────────────
function formatProduct(array $row): array {
    $parts = explode('||', $row['descripcion'] ?? '', 2);
    $extra = isset($parts[1]) ? json_decode($parts[1], true) : [];
    return [
        'id'               => (int)$row['id_producto'],
        'nombre'           => $row['nombre'],
        'descripcion'      => $parts[0],
        'stock'            => (int)$row['stock'],
        'compra'           => (float)($extra['compra'] ?? 0),
        'precio_minorista' => (float)($row['precio_minorista'] ?? 0),
        'precio_mayorista' => (float)($row['precio_mayorista'] ?? 0),
        'imagenes'         => $extra['imagenes'] ?? [],
        'activo'           => (bool)$row['activo'],
    ];
}
