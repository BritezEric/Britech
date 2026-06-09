<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/PedidoModel.php';

if (!isLoggedIn()) {
    redirect(APP_URL . 'login/');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . 'app/ecommerce/views/checkout.php');
}

$cartJson   = $_POST['cart_json'] ?? '';
$tipoPrecio = $_POST['tipo_precio'] ?? 'minorista';
$usuarioId  = getUsuarioId();

// Parse cart
$items = json_decode($cartJson, true);
if (!$items || !is_array($items) || empty($items)) {
    $_SESSION['checkout_error'] = 'El carrito está vacío.';
    redirect(APP_URL . 'app/ecommerce/views/checkout.php');
}

// Build items for model
$pedidoItems = array_map(fn($i) => [
    'producto_id' => (int)($i['id'] ?? 0),
    'cantidad'    => (int)($i['qty'] ?? 1),
], $items);

$envioData = [
    'direccion'    => trim($_POST['direccion'] ?? ''),
    'provincia'    => trim($_POST['provincia'] ?? ''),
    'ciudad'       => trim($_POST['ciudad'] ?? ''),
    'metodo_pago'  => $_POST['metodo_pago'] ?? 'Transferencia bancaria',
];

if (!$envioData['direccion'] || !$envioData['provincia'] || !$envioData['ciudad']) {
    $_SESSION['checkout_error'] = 'Completá todos los datos de envío.';
    redirect(APP_URL . 'app/ecommerce/views/checkout.php');
}

$result = PedidoModel::crear($usuarioId, $pedidoItems, $envioData, $tipoPrecio);

if (!$result['ok']) {
    $_SESSION['checkout_error'] = $result['msg'];
    redirect(APP_URL . 'app/ecommerce/views/checkout.php');
}

// Clear cart via JS on success page
$_SESSION['last_pedido_id'] = $result['pedido_id'];
$_SESSION['last_pedido_total'] = $result['total'];
redirect(APP_URL . 'app/ecommerce/views/success.php');
