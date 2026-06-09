<?php
require_once __DIR__ . '/../config/session.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'logout':
        redirect(APP_URL . 'logout/');
        break;
    case 'login':
    case 'register':
    default:
        redirect(APP_URL . ($action === 'register' ? 'register/' : 'login/'));
        break;
}
