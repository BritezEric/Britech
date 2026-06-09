<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/models/ProductoModel.php';

$catId      = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$sortBy     = $_GET['sort'] ?? 'nombre';
$priceRange = $_GET['price'] ?? 'all';
$stockOnly  = isset($_GET['stock']);

$productos  = ProductoModel::getCatalogo($catId);
$tipoCliente = getTipoCliente();
$loggedIn    = isLoggedIn();

// Sort
usort($productos, function($a, $b) use ($sortBy) {
    return match($sortBy) {
        'price-asc'  => ($a['precio_minorista'] ?? 0) <=> ($b['precio_minorista'] ?? 0),
        'price-desc' => ($b['precio_minorista'] ?? 0) <=> ($a['precio_minorista'] ?? 0),
        'stock-desc' => $b['stock'] <=> $a['stock'],
        default      => strcmp($a['nombre'], $b['nombre']),
    };
});

// Price filter
if ($priceRange !== 'all') {
    [$min, $max] = match($priceRange) {
        '0-50000'        => [0, 50000],
        '50000-200000'   => [50000, 200000],
        '200000-500000'  => [200000, 500000],
        '500000+'        => [500000, PHP_INT_MAX],
        default          => [0, PHP_INT_MAX],
    };
    $productos = array_filter($productos, fn($p) => ($p['precio_minorista'] ?? 0) >= $min && ($p['precio_minorista'] ?? 0) <= $max);
}

if ($stockOnly) {
    $productos = array_filter($productos, fn($p) => $p['stock'] > 0 || $p['tipo'] === 'pedido');
}

// Fetch display price + tiers for each product
$productosFull = [];
foreach ($productos as $p) {
    $precioDisplay = ProductoModel::getPrecioOptimo($p['producto_id'], $tipoCliente);
    $precioMinorista = ProductoModel::getPrecioOptimo($p['producto_id'], 'minorista');
    $tiers = ($tipoCliente === 'mayorista') ? ProductoModel::getPreciosMayorista($p['producto_id']) : [];
    $p['precio_display'] = $precioDisplay;
    $p['precio_minorista'] = $precioMinorista;
    $p['tiers'] = $tiers;
    $productosFull[] = $p;
}

$pageTitle = 'BRITECH — Catálogo';
include __DIR__ . '/views/partials/header.php';

function fmtPrice(float $n): string {
    return '$' . number_format($n, 0, ',', '.');
}
?>

<main>
<div class="container">
  <!-- HERO -->
  <div class="hero">
    <div class="hero-left">
      <div class="hero-eyebrow">Catálogo <?= date('Y') ?></div>
      <h1 class="hero-title">Tecnología que <em>importa</em></h1>
      <p class="hero-desc">Productos seleccionados, precios según tu perfil de cliente. Stock real. Entrega garantizada.</p>
      <div style="display:flex;gap:.8rem;flex-wrap:wrap">
        <button class="btn btn-primary btn-lg" onclick="document.getElementById('catalogo').scrollIntoView({behavior:'smooth'})">Ver catálogo</button>
        <?php if (!$loggedIn): ?>
          <a href="<?php echo APP_URL . 'login/'; ?>" class="btn btn-outline">Acceder →</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hero-right">💻</div>
  </div>

  <?php if (!$loggedIn): ?>
    <div class="notice notice-info">
      💡 <strong>¿Sos mayorista?</strong> <a href="<?php echo APP_URL . 'login/'; ?>" style="color:var(--blue);text-decoration:underline">Iniciá sesión</a> para ver tus precios exclusivos y descuentos por volumen.
    </div>
  <?php elseif ($tipoCliente === 'mayorista'): ?>
    <div class="notice notice-gold">
      ★ Estás viendo <strong>precios mayoristas</strong>. Los descuentos por volumen se aplican automáticamente.
    </div>
  <?php endif; ?>

  <!-- FILTERS -->
  <section id="catalogo">
    <div class="section-head">
      <div class="section-title">
        <small>Catálogo completo</small>
        Todos los productos
      </div>
      <span style="font-family:var(--font-mono);font-size:.72rem;color:var(--white4)"><?= count($productosFull) ?> productos</span>
    </div>

    <form method="get" class="filter-bar" id="filterForm">
      <?php if ($catId): ?>
        <input type="hidden" name="cat" value="<?= $catId ?>">
      <?php endif; ?>

      <span class="filter-label">Ordenar</span>
      <select name="sort" class="filter-select" onchange="this.form.submit()">
        <option value="nombre"     <?= $sortBy === 'nombre'     ? 'selected' : '' ?>>Nombre A-Z</option>
        <option value="price-asc"  <?= $sortBy === 'price-asc'  ? 'selected' : '' ?>>Precio ↑</option>
        <option value="price-desc" <?= $sortBy === 'price-desc' ? 'selected' : '' ?>>Precio ↓</option>
        <option value="stock-desc" <?= $sortBy === 'stock-desc' ? 'selected' : '' ?>>Mayor stock</option>
      </select>

      <span class="filter-label" style="margin-left:.5rem">Precio</span>
      <select name="price" class="filter-select" onchange="this.form.submit()">
        <option value="all"        <?= $priceRange === 'all'        ? 'selected' : '' ?>>Todos</option>
        <option value="0-50000"    <?= $priceRange === '0-50000'    ? 'selected' : '' ?>>Hasta $50.000</option>
        <option value="50000-200000"  <?= $priceRange === '50000-200000'   ? 'selected' : '' ?>>$50k – $200k</option>
        <option value="200000-500000" <?= $priceRange === '200000-500000'  ? 'selected' : '' ?>>$200k – $500k</option>
        <option value="500000+"    <?= $priceRange === '500000+'    ? 'selected' : '' ?>>Más de $500k</option>
      </select>

      <label class="filter-check">
        <input type="checkbox" name="stock" <?= $stockOnly ? 'checked' : '' ?> onchange="this.form.submit()">
        <span>Solo disponibles</span>
      </label>

      <span class="results-info" id="resultsCount"><?= count($productosFull) ?> resultados</span>
    </form>

    <!-- GRID -->
    <div class="product-grid">
      <?php if (empty($productosFull)): ?>
        <div class="no-results">
          <div class="no-results-icon">🔍</div>
          <h3>Sin resultados</h3>
          <p>Probá otros filtros o <a href="<?php echo APP_URL . 'app/ecommerce/index.php'; ?>" style="color:var(--white2)">ver todos</a></p>
        </div>
      <?php else: ?>
        <?php foreach ($productosFull as $p):
          $precio = $p['precio_display'];
          $pMinorista = $p['precio_minorista'];
          $stock = (int)$p['stock'];
          $tipo  = $p['tipo'];
          $tiersJson = json_encode($p['tiers']);
          $dataModal = json_encode([
            'id'          => $p['producto_id'],
            'nombre'      => $p['nombre'],
            'categoria'   => $p['categoria'] ?? '',
            'marca'       => $p['marca'] ?? '',
            'modelo'      => $p['modelo'] ?? '',
            'descripcion' => '',
            'precio'      => $precio,
            'stock'       => $stock,
            'tipo'        => $tipo,
            'foto'        => $p['foto'] ?? '',
            'tiers'       => $p['tiers'],
          ]);
          $searchData = strtolower($p['nombre'] . ' ' . ($p['categoria'] ?? '') . ' ' . ($p['marca'] ?? '') . ' ' . ($p['modelo'] ?? ''));
        ?>
          <div class="product-card fade-in" data-search="<?= htmlspecialchars($searchData) ?>"
               onclick="openModal(<?= htmlspecialchars($dataModal, ENT_QUOTES) ?>)">
            <div class="product-card-img">
              <?php if ($p['foto']): ?>
                <img src="<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>"
                     onerror="this.parentElement.innerHTML='<span style=font-size:3.5rem>🛍</span>'">
              <?php else: ?>
                <span style="font-size:3.5rem">🛍</span>
              <?php endif; ?>
              <div class="card-badges">
                <?php if ($tipo === 'pedido'): ?>
                  <span class="badge badge-blue">📦 Pedido</span>
                <?php elseif ($stock === 0): ?>
                  <span class="badge badge-red">Sin stock</span>
                <?php elseif ($stock <= 5): ?>
                  <span class="badge badge-gold">⚠ Últimas <?= $stock ?></span>
                <?php else: ?>
                  <span class="badge badge-green">✓ Disponible</span>
                <?php endif; ?>
                <?php if ($tipoCliente === 'mayorista'): ?>
                  <span class="badge badge-gold" style="font-size:.55rem">★ MAY</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="product-card-body">
              <div class="product-cat"><?= htmlspecialchars($p['categoria'] ?? '') ?></div>
              <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
              <?php if ($p['marca']): ?>
                <div class="product-brand"><?= htmlspecialchars($p['marca']) ?> <?= htmlspecialchars($p['modelo'] ?? '') ?></div>
              <?php endif; ?>
              <div class="product-card-foot">
                <div class="product-price">
                  <?php if ($tipoCliente === 'mayorista' && $pMinorista && $pMinorista > $precio): ?>
                    <span class="old"><?= fmtPrice($pMinorista) ?></span>
                  <?php endif; ?>
                  <?php if (!$loggedIn): ?>
                    <?= fmtPrice($precio) ?>
                  <?php elseif ($precio > 0): ?>
                    <?= fmtPrice($precio) ?>
                  <?php else: ?>
                    <span style="color:var(--white4);font-size:.75rem">Consultar</span>
                  <?php endif; ?>
                </div>
                <button class="add-btn" data-add-btn="<?= $p['producto_id'] ?>"
                  <?= ($stock === 0 && $tipo !== 'pedido') ? 'disabled' : '' ?>
                  onclick="event.stopPropagation();addToCart(
                    <?= $p['producto_id'] ?>,
                    <?= json_encode($p['nombre']) ?>,
                    <?= $precio ?>,
                    <?= $stock ?>,
                    <?= json_encode($tipo) ?>,
                    event)">+</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>
</main>

<?php include __DIR__ . '/views/partials/footer.php'; ?>
