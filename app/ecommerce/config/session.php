<?php
require_once __DIR__ . '/../../../config/app.php';

// Usar la misma sesión que VENTAS-main
if (session_status() === PHP_SESSION_NONE) {
    // El nombre de sesión debe ser el mismo que usa el gestor
    session_name(APP_SESSION_NAME); // 'POS'
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['cliente_id']);
}

function getTipoCliente(): ?string {
    return $_SESSION['tipo_cliente'] ?? null;
}

function setSession(array $data): void {
    foreach ($data as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function isMayorista(): bool {
    return getTipoCliente() === 'mayorista';
}

function getUsuarioId(): ?int {
    return isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
}

function csrf(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}