<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../config/session.php';

// Only mayoristas
if (!isLoggedIn() or getTipoCliente() !== 'mayorista') {
    header('Location: ' . APP_URL . 'login/');
    exit;
}

$usuario_id = getUsuarioId();
require_once __DIR__ . '/../../ecommerce/models/ClienteModel.php';
require_once __DIR__ . '/../../ecommerce/models/PedidoModel.php';

// Get cart from session
$cart = $_SESSION['carrito'] ?? [];
$envio = $_SESSION['envio'] ?? ['metodo' => 'recoger', 'costo' => 0];
$usuariodata = ClienteModel::getUsuario($usuario_id);

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$total = $subtotal + ($envio['costo'] ?? 0);

$pageTitle = 'Checkout — BRITECH Mayorista';
include __DIR__ . '/../views/partials/header.php';
?>

<style>
.checkout-section {
  margin: 2rem 0;
  padding: 1.5rem;
  border: 1px solid var(--border);
  border-radius: var(--r2);
  background: var(--black2);
}
.checkout-section h3 {
  font-family: var(--font-serif);
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: var(--white);
}
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 500;
  font-size: 0.85rem;
  color: var(--white);
}
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid var(--border);
  border-radius: var(--r);
  background: var(--black3);
  color: var(--white);
  font-family: var(--font-sans);
  font-size: 0.85rem;
}
.form-group textarea {
  resize: vertical;
  min-height: 80px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.order-summary {
  background: var(--black2);
  border: 1px solid var(--border);
  border-radius: var(--r2);
  padding: 1.5rem;
  max-width: 400px;
  margin: 2rem auto;
}
.order-summary h3 {
  font-family: var(--font-serif);
  font-size: 1.1rem;
  margin-bottom: 1rem;
}
.order-item {
  display: flex;
  justify-content: space-between;
  padding: 0.6rem 0;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem;
}
.order-item-last {
  border: none;
}
.order-totals {
  padding-top: 1rem;
  border-top: 1px solid var(--border);
  margin-top: 1rem;
}
.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}
.total-row.grand {
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--gold);
  border-top: 1px solid var(--border);
  padding-top: 0.5rem;
  margin-top: 0.5rem;
}
</style>

<main>
<div class="container">
  <h1 style="text-align:center;margin-bottom:2rem;font-family:var(--font-serif);font-size:2.2rem">Checkout Mayorista</h1>

  <?php if (empty($cart)): ?>
    <div class="notice notice-gold" style="text-align:center">
      Tu carrito está vacío. <a href="<?= APP_URL ?>app/mayorista/index.php" style="color:var(--white)">Volver al catálogo</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;max-width:1200px;margin:0 auto">
      <div>
        <!-- DATOS DE ENVÍO -->
        <div class="checkout-section">
          <h3>📍 Datos de envío</h3>
          <form id="checkoutForm" method="post" action="<?= APP_URL ?>app/ecommerce/controllers/auth.php">
            <input type="hidden" name="accion" value="procesar_pago_mayorista">
            <?= csrf() ?>

            <div class="form-grid">
              <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($usuariodata['nombre'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($usuariodata['email'] ?? '') ?>" required>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($usuariodata['telefonos'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label>Empresa/Razón social</label>
                <input type="text" name="empresa" value="<?= htmlspecialchars($usuariodata['razon_social'] ?? '') ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label>Dirección</label>
              <input type="text" name="direccion" value="<?= htmlspecialchars($usuariodata['direccion'] ?? '') ?>" required>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Ciudad</label>
                <input type="text" name="ciudad" value="<?= htmlspecialchars($usuariodata['ciudad'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label>Provincia</label>
                <input type="text" name="provincia" value="<?= htmlspecialchars($usuariodata['provincia'] ?? '') ?>">
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Código postal</label>
                <input type="text" name="cp" value="<?= htmlspecialchars($usuariodata['codigo_postal'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>País</label>
                <input type="text" name="pais" value="Argentina" required>
              </div>
            </div>

            <!-- MÉTODO ENVÍO -->
            <div class="checkout-section" style="margin-top:1.5rem">
              <h3>🚚 Método de envío</h3>
              <div style="display:flex;flex-direction:column;gap:.8rem">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                  <input type="radio" name="envio" value="recoger" checked onchange="updateShipping()">
                  <span>Retiro en local</span>
                  <span style="color:var(--gold);font-weight:600">Gratis</span>
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                  <input type="radio" name="envio" value="envio" onchange="updateShipping()">
                  <span>Envío a domicilio</span>
                  <span style="color:var(--white3);font-size:.8rem">(consultar costo)</span>
                </label>
              </div>
            </div>

            <!-- NOTAS -->
            <div style="margin-top:1.5rem">
              <div class="form-group">
                <label>Notas especiales (opcional)</label>
                <textarea name="notas" placeholder="Ej: Solicito factura A, entregar después de las 14hs..."></textarea>
              </div>
            </div>

            <div style="display:flex;gap:1rem;justify-content:center;margin-top:2rem">
              <a href="<?= APP_URL ?>app/mayorista/index.php" class="btn btn-outline btn-lg">← Volver</a>
              <button type="submit" class="btn btn-primary btn-lg" style="width:200px">Confirmar pedido</button>
            </div>
          </form>
        </div>
      </div>

      <!-- RESUMEN -->
      <div>
        <div class="order-summary">
          <h3>Resumen del pedido</h3>
          <div style="max-height:300px;overflow-y:auto;margin-bottom:1rem">
            <?php foreach ($cart as $item): ?>
              <div class="order-item">
                <div style="flex:1">
                  <div style="font-weight:500"><?= htmlspecialchars(substr($item['nombre'], 0, 30)) ?></div>
                  <div style="color:var(--white4);font-size:.75rem"><?= $item['cantidad'] ?> × <?= number_format($item['precio'], 0, ',', '.') ?></div>
                </div>
                <div style="text-align:right;min-width:100px">$<?= number_format($item['cantidad'] * $item['precio'], 0, ',', '.') ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-totals">
            <div class="total-row">
              <span>Subtotal:</span>
              <span>$<?= number_format($subtotal, 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
              <span>Envío:</span>
              <span id="envioDisplay">Gratis</span>
            </div>
            <div class="total-row grand">
              <span>TOTAL:</span>
              <span id="totalDisplay">$<?= number_format($total, 0, ',', '.') ?></span>
            </div>
          </div>

          <div class="mayorista-note" style="margin-top:1rem">
            ★ Precios mayoristas ya aplicados. Reclama tu factura en notas especiales.
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
</main>

<script>
function updateShipping() {
  const method = document.querySelector('input[name="envio"]:checked').value;
  const display = document.getElementById('envioDisplay');
  const total = document.getElementById('totalDisplay');
  let cost = 0;
  if (method === 'envio') {
    display.textContent = 'Consultar con vendedor';
  } else {
    display.textContent = 'Gratis';
  }
  // Recalculate if needed
}
</script>

<?php include __DIR__ . '/../views/partials/footer.php'; ?>
