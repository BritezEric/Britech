<?php
require_once __DIR__ . '/../config/session.php';
if (!isLoggedIn()) {
    redirect(APP_URL . 'app/ecommerce/views/login.php');
}

$error   = $_SESSION['checkout_error'] ?? '';
$success = $_SESSION['checkout_success'] ?? '';
unset($_SESSION['checkout_error'], $_SESSION['checkout_success']);

$pageTitle = 'BRITECH — Checkout';
$tipoCliente = getTipoCliente();
$isMayorista = isMayorista();

include __DIR__ . '/partials/header.php';
?>

<main>
<div class="page-wrap">
  <div class="page-title">Finalizar compra</div>
  <div class="page-subtitle">Revisá tu pedido y completá los datos de envío</div>

  <?php if ($error): ?>
    <div class="notice notice-red"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="checkout-grid">
    <!-- SHIPPING FORM -->
    <div>
      <div class="card mb2">
        <div class="card-head"><h3>📦 Datos de envío</h3></div>
        <div class="card-body">
          <form method="POST" action="<?php echo APP_URL . 'app/ecommerce/controllers/checkout.php'; ?>" id="checkoutForm">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">
            <input type="hidden" name="cart_json" id="cartJsonInput" value="">
            <input type="hidden" name="tipo_precio" value="<?= $tipoCliente ?>">

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-input" required
                       value="<?= htmlspecialchars(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="tel" name="telefono" class="form-input" placeholder="+54 11 1234-5678">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Provincia</label>
                <input type="text" name="provincia" class="form-input" required placeholder="Buenos Aires">
              </div>
              <div class="form-group">
                <label class="form-label">Ciudad</label>
                <input type="text" name="ciudad" class="form-input" required placeholder="CABA">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-input" required placeholder="Av. Corrientes 1234, Piso 3">
            </div>

            <div class="form-group">
              <label class="form-label">Notas adicionales</label>
              <input type="text" name="notas" class="form-input" placeholder="Instrucciones especiales (opcional)">
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>💳 Método de pago</h3></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.7rem">
          <?php
          $metodos = [
            'Transferencia bancaria' => '🏦',
            'MercadoPago'            => '💙',
            'Efectivo contra entrega'=> '💵',
          ];
          foreach ($metodos as $metodo => $icon): ?>
            <label style="display:flex;align-items:center;gap:.8rem;padding:.8rem 1rem;border:1px solid var(--border);border-radius:var(--r2);cursor:pointer;transition:border-color var(--t)"
                   onmouseenter="this.style.borderColor='var(--border2)'" onmouseleave="this.style.borderColor='var(--border)'">
              <input type="radio" name="metodo_pago" form="checkoutForm"
                     value="<?= $metodo ?>" <?= $metodo === 'Transferencia bancaria' ? 'checked' : '' ?>
                     style="accent-color:var(--white)">
              <span style="font-size:1.1rem"><?= $icon ?></span>
              <span style="font-size:.88rem;color:var(--white2)"><?= $metodo ?></span>
            </label>
          <?php endforeach; ?>

          <?php if ($isMayorista): ?>
            <div class="notice notice-gold" style="margin:0">★ Como mayorista, podés solicitar factura A.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ORDER SUMMARY -->
    <div>
      <div class="card" style="position:sticky;top:88px">
        <div class="card-head">
          <h3>🧾 Resumen del pedido</h3>
          <?php if ($isMayorista): ?>
            <div class="badge badge-gold" style="margin-top:.3rem">★ Precios mayoristas</div>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div id="orderSummary" style="margin-bottom:1rem">
            <p style="color:var(--white4);font-size:.85rem;text-align:center;padding:1rem 0">Cargando carrito...</p>
          </div>
          <div style="border-top:1px solid var(--border);padding-top:0.8rem">
            <div class="total-line"><span>Subtotal</span><span id="summarySubtotal">—</span></div>
            <div class="total-line"><span>Envío</span><span style="color:var(--green)">A calcular</span></div>
            <div class="total-line grand"><span>Total</span><span id="summaryTotal" style="font-family:var(--font-mono)">—</span></div>
          </div>
          <button type="submit" form="checkoutForm" class="btn btn-primary btn-full btn-lg mt2" id="submitBtn" disabled>
            Confirmar pedido →
          </button>
          <a href="<?php echo APP_URL . 'app/ecommerce/index.php'; ?>" class="btn btn-outline btn-full mt1">← Seguir comprando</a>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const cart = JSON.parse(sessionStorage.getItem('nx_cart') || '[]');
  const summary = document.getElementById('orderSummary');
  const submitBtn = document.getElementById('submitBtn');
  const cartInput = document.getElementById('cartJsonInput');

  if (!cart.length) {
    summary.innerHTML = '<p style="color:var(--red);text-align:center;font-size:.85rem">Tu carrito está vacío</p>';
    return;
  }

  cartInput.value = JSON.stringify(cart);
  submitBtn.disabled = false;

  let html = '', total = 0;
  cart.forEach(item => {
    const sub = item.precio * item.qty;
    total += sub;
    html += `
      <div class="order-item">
        <div class="order-item-name">${item.nombre}</div>
        <div class="order-item-qty">×${item.qty}</div>
        <div class="order-item-price">${fmt(sub)}</div>
      </div>`;
  });
  summary.innerHTML = html;
  document.getElementById('summarySubtotal').textContent = fmt(total);
  document.getElementById('summaryTotal').textContent = fmt(total);
});

function fmt(n) {
  return '$' + Math.round(n).toLocaleString('es-AR', {maximumFractionDigits: 0});
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
