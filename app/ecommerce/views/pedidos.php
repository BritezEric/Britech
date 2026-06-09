<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/PedidoModel.php';
if (!isLoggedIn()) redirect(APP_URL . 'app/ecommerce/views/login.php');

$usuarioId = getUsuarioId();
$pedidos   = PedidoModel::getByUsuario($usuarioId);

// Single order detail
$detalle = null;
if (isset($_GET['id'])) {
    $detalle = PedidoModel::getDetalle((int)$_GET['id'], $usuarioId);
}

$pageTitle = 'BRITECH — Mis pedidos';
include __DIR__ . '/partials/header.php';

$estadoColors = [
    'pendiente'  => 'badge-gray',
    'pagado'     => 'badge-green',
    'enviado'    => 'badge-blue',
    'entregado'  => 'badge-green',
    'cancelado'  => 'badge-red',
];
?>

<main>
<div class="page-wrap">
  <?php if ($detalle): ?>
    <!-- DETAIL VIEW -->
    <a href="<?php echo APP_URL . 'app/ecommerce/views/pedidos.php'; ?>" class="btn btn-outline btn-sm" style="margin-bottom:1.5rem">← Volver a mis pedidos</a>
    <div class="page-title">Pedido #<?= $detalle['pedido_id'] ?></div>
    <div class="page-subtitle"><?= date('d/m/Y H:i', strtotime($detalle['fecha'])) ?></div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">
      <div class="card">
        <div class="card-head">
          <h3>Productos</h3>
          <span class="badge <?= $estadoColors[$detalle['estado']] ?? 'badge-gray' ?>" style="margin-top:.3rem">
            <?= ucfirst($detalle['estado']) ?>
          </span>
        </div>
        <div class="card-body">
          <?php foreach ($detalle['items'] as $item): ?>
            <div class="order-item">
              <div class="order-item-name"><?= htmlspecialchars($item['nombre']) ?></div>
              <div class="order-item-qty">×<?= $item['cantidad'] ?></div>
              <div class="order-item-price" style="font-family:var(--font-mono)">
                $<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <div class="card-head"><h3>Total</h3></div>
        <div class="card-body">
          <div class="total-line grand" style="border:none;padding:0">
            <span>Total pagado</span>
            <span style="font-family:var(--font-mono)">$<?= number_format($detalle['total'], 0, ',', '.') ?></span>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- LIST VIEW -->
    <div class="page-title">Mis pedidos</div>
    <div class="page-subtitle">Historial de compras de tu cuenta</div>

    <?php if (empty($pedidos)): ?>
      <div style="text-align:center;padding:4rem 2rem;color:var(--white4)">
        <div style="font-size:3rem;margin-bottom:1rem;opacity:.3">🛍</div>
        <p style="font-size:.95rem;margin-bottom:1.5rem">Todavía no realizaste ningún pedido</p>
        <a href="<?php echo APP_URL . 'app/ecommerce/index.php'; ?>" class="btn btn-primary">Ver catálogo</a>
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($pedidos as $p): ?>
          <a href="?id=<?= $p['pedido_id'] ?>" class="order-row" style="text-decoration:none">
            <div>
              <div class="order-id">Pedido #<?= $p['pedido_id'] ?></div>
              <div class="order-date"><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></div>
            </div>
            <div>
              <span class="badge <?= $estadoColors[$p['estado']] ?? 'badge-gray' ?>">
                <?= ucfirst($p['estado']) ?>
              </span>
            </div>
            <div style="font-family:var(--font-mono);font-size:.75rem;color:var(--white4)">
              <?= $p['items'] ?> ítem<?= $p['items'] != 1 ? 's' : '' ?>
            </div>
            <div class="order-total">
              $<?= number_format($p['total'], 0, ',', '.') ?>
            </div>
            <div style="color:var(--white4);font-size:.85rem">→</div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
