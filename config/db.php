<?php
// ╔══════════════════════════════════════════════════════════╗
// ║  BRITECH — config/db.php                                ║
// ║  Conexión PDO única para toda la aplicación             ║
// ╚══════════════════════════════════════════════════════════╝

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=brit;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Error de conexión: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
