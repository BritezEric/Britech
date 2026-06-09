<?php
require_once __DIR__ . '/../../config/session.php';
$tipoCliente = getTipoCliente();
$isMayorista = isMayorista();
$loggedIn    = isLoggedIn();
$nombre      = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'BRITECH — Tecnología Premium' ?></title>
  <link rel="stylesheet" href="<?php echo APP_URL . 'app/ecommerce/assets/css/style.css'; ?>">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <a href="<?php echo APP_URL . 'app/ecommerce/index.php'; ?>" class="logo">
      BRITECH<span class="logo-dot">·</span>
      <span>
        <span class="logo-sub">Tecnología premium</span>
      </span>
    </a>

    <div class="search-wrap">
      <input type="text" class="search-input" id="searchInput" placeholder="Buscar productos...">
      <button class="search-btn">⌕</button>
    </div>

    <div class="header-actions">
      <?php if ($loggedIn): ?>
        <span class="user-chip <?= $tipoCliente ?>">
          <?= $isMayorista ? '★ Mayorista' : '· Minorista' ?>
        </span>
        <span style="font-size:.82rem;color:var(--white3)"><?= htmlspecialchars($nombre) ?></span>
        <a href="<?php echo APP_URL . 'logout/'; ?>" class="h-link btn-sm">Salir</a>
      <?php else: ?>
        <a href="<?php echo APP_URL . 'login/'; ?>" class="h-link">Ingresar</a>
        <a href="<?php echo APP_URL . 'register/'; ?>" class="h-link" style="border-color:var(--border3);color:var(--white)">Registrarse</a>
      <?php endif; ?>

      <button class="h-btn" onclick="openCart()" title="Carrito">
        🛒<span class="badge cart-badge" id="cartBadge">0</span>
      </button>

      <?php if ($loggedIn): ?>
        <a href="<?php echo APP_URL . 'app/ecommerce/views/pedidos.php'; ?>" class="h-link">Mis pedidos</a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="cat-nav">
    <div class="cat-nav-inner" id="catNavInner">
      <button class="cat-btn <?= (!isset($_GET['cat']) ? 'active' : '') ?>"
              onclick="filterCat(0)">Todos</button>
      <?php
      require_once __DIR__ . '/../../models/ProductoModel.php';
      $cats = ProductoModel::getCategorias();
      foreach ($cats as $c): ?>
        <button class="cat-btn <?= (isset($_GET['cat']) && $_GET['cat'] == $c['categoria_id'] ? 'active' : '') ?>"
                onclick="filterCat(<?= $c['categoria_id'] ?>)">
          <?= htmlspecialchars($c['nombre']) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </nav>
</header>

<!-- CART DRAWER -->
<div class="overlay" id="cartOverlay" onclick="closeCart()"></div>
<aside class="cart-drawer" id="cartDrawer">
  <div class="drawer-head">
    <span class="drawer-title">🛒 Tu carrito</span>
    <button class="close-btn" onclick="closeCart()">✕</button>
  </div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-footer" id="cartFooter" style="display:none">
    <?php if ($isMayorista): ?>
      <div class="mayorista-note">★ Precios mayoristas aplicados</div>
    <?php elseif (!$loggedIn): ?>
      <div class="mayorista-note" style="color:var(--white3);background:rgba(255,255,255,.04);border-color:var(--border)">
        💡 <a href="<?php echo APP_URL . 'login/'; ?>" style="color:var(--white2);text-decoration:underline">Iniciá sesión</a> para ver precios mayoristas
      </div>
    <?php endif; ?>
    <div class="total-line"><span>Subtotal</span><span id="cartTotal">$0</span></div>
    <div class="total-line"><span>Envío</span><span style="color:var(--green)">A calcular</span></div>
    <div class="total-line grand"><span>Total estimado</span><span id="cartTotal2" style="font-family:var(--font-mono)">$0</span></div>
    <div class="mt2">
      <?php if ($loggedIn): ?>
        <a href="<?php echo APP_URL . 'app/ecommerce/views/checkout.php'; ?>" class="btn btn-primary btn-full btn-lg">Finalizar compra →</a>
      <?php else: ?>
        <a href="<?php echo APP_URL . 'login/'; ?>" class="btn btn-primary btn-full btn-lg">Iniciar sesión para comprar</a>
      <?php endif; ?>
      <button class="btn btn-outline btn-full mt1" onclick="closeCart()">Seguir comprando</button>
    </div>
  </div>
</aside>

<!-- PRODUCT MODAL -->
<div class="modal-overlay" id="productModal">
  <div class="modal">
    <div class="modal-head">
      <h3>Detalle del producto</h3>
      <button class="close-btn" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-grid">
        <div class="modal-img" id="mImg">🛍</div>
        <div class="modal-info">
          <div class="modal-cat" id="mCat"></div>
          <div class="modal-name" id="mName"></div>
          <div style="font-size:.8rem;color:var(--white4)" id="mBrand"></div>
          <div class="modal-price" id="mPrice"></div>
          <div id="mStock"></div>
          <div class="modal-desc" id="mDesc"></div>
          <div id="mTiers"></div>
          <div class="qty-control">
            <button class="qty-ctrl-btn" onclick="changeModalQty(-1)">−</button>
            <span class="qty-ctrl-val" id="mQtyVal">1</span>
            <button class="qty-ctrl-btn" onclick="changeModalQty(1)">+</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary btn-lg" id="mAddBtn" onclick="addFromModal()">🛒 Agregar</button>
      <button class="btn btn-outline" onclick="closeModal()">Cerrar</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<script src="<?php echo APP_URL . 'app/ecommerce/assets/js/app.js'; ?>"></script>
<script>
// Category filter — reloads page with ?cat=X
function filterCat(catId) {
  const url = new URL(window.location.href);
  if (catId === 0) url.searchParams.delete('cat');
  else url.searchParams.set('cat', catId);
  window.location.href = url.toString();
}

// Sync cart total in drawer
const _origRender = renderCartDrawer;
function renderCartDrawer() {
  _origRender();
  const total = getCartTotal();
  const el2 = document.getElementById('cartTotal2');
  if (el2) el2.textContent = fmt(total);
}
</script>
