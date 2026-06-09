<?php
require_once __DIR__ . '/../config/session.php';
if (!isLoggedIn()) redirect(APP_URL . 'app/ecommerce/index.php');

$pedidoId = $_SESSION['last_pedido_id'] ?? null;
$total    = $_SESSION['last_pedido_total'] ?? 0;
unset($_SESSION['last_pedido_id'], $_SESSION['last_pedido_total']);

if (!$pedidoId) redirect(APP_URL . 'app/ecommerce/index.php');

$pageTitle = 'BRITECH — Pedido confirmado';
include __DIR__ . '/partials/header.php';
?>

<main>
<div class="page-wrap">
  <div class="success-wrap">
    <div class="success-icon">✅</div>
    <div class="success-title">¡Pedido confirmado!</div>
    <div class="success-sub">
      Tu pedido <strong style="color:var(--white);font-family:var(--font-mono)">#<?= $pedidoId ?></strong>
      fue recibido y está siendo procesado.<br>
      Total: <strong style="font-family:var(--font-mono)"><?= '$' . number_format((float)$total, 0, ',', '.') ?></strong>
    </div>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="<?php echo APP_URL . 'app/ecommerce/views/pedidos.php'; ?>" class="btn btn-primary btn-lg">Ver mis pedidos</a>
      <a href="<?php echo APP_URL . 'app/ecommerce/index.php'; ?>" class="btn btn-outline btn-lg">Seguir comprando</a>
    </div>
  </div>
</div>
</main>

<script>
// Clear cart after successful checkout
sessionStorage.removeItem('nx_cart');
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
