/* ─── CART ──────────────────────────────────────────────────── */
let cart = JSON.parse(sessionStorage.getItem('nx_cart') || '[]');

function saveCart() {
  sessionStorage.setItem('nx_cart', JSON.stringify(cart));
  updateCartBadge();
}

function updateCartBadge() {
  const n = cart.reduce((s, i) => s + i.qty, 0);
  document.querySelectorAll('.cart-badge').forEach(el => {
    el.textContent = n;
    el.classList.toggle('show', n > 0);
  });
}

function addToCart(productoId, nombre, precio, stock, tipo, e) {
  if (e) e.stopPropagation();
  if (stock === 0 && tipo !== 'pedido') { toast('Sin stock disponible', 'error'); return; }
  const ex = cart.find(i => i.id === productoId);
  if (ex) {
    if (tipo !== 'pedido' && ex.qty >= stock) { toast('Stock máximo alcanzado', 'error'); return; }
    ex.qty++;
  } else {
    cart.push({ id: productoId, nombre, precio, stock, tipo, qty: 1 });
  }
  saveCart();
  toast(nombre.split(' ').slice(0, 3).join(' ') + ' agregado', 'success');
  renderCartDrawer();
  refreshAddBtns();
}

function removeFromCart(id) {
  cart = cart.filter(i => i.id !== id);
  saveCart(); renderCartDrawer(); refreshAddBtns();
}

function changeQty(id, d) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  const nq = item.qty + d;
  if (nq <= 0) { removeFromCart(id); return; }
  if (item.tipo !== 'pedido' && nq > item.stock) { toast('Stock insuficiente', 'error'); return; }
  item.qty = nq;
  saveCart(); renderCartDrawer();
}

function openCart() {
  renderCartDrawer();
  document.getElementById('cartDrawer').classList.add('open');
  document.getElementById('cartOverlay').classList.add('open');
}
function closeCart() {
  document.getElementById('cartDrawer').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('open');
}

function renderCartDrawer() {
  const container = document.getElementById('cartItems');
  const footer    = document.getElementById('cartFooter');
  if (!cart.length) {
    container.innerHTML = `<div class="cart-empty"><div class="cart-empty-icon">🛍</div><p>Tu carrito está vacío</p></div>`;
    if (footer) footer.style.display = 'none';
    return;
  }
  if (footer) footer.style.display = 'block';
  let html = '', total = 0;
  cart.forEach(item => {
    const sub = item.precio * item.qty;
    total += sub;
    html += `
      <div class="cart-item">
        <div class="cart-thumb">🛍</div>
        <div class="cart-item-info">
          <div class="cart-item-name">${item.nombre}</div>
          <div class="cart-item-price">${fmt(item.precio)} × ${item.qty} = ${fmt(sub)}</div>
          ${item.tipo === 'pedido' ? '<span class="badge badge-blue" style="margin-top:4px;font-size:.58rem">Bajo pedido</span>' : ''}
          <div class="cart-item-controls">
            <button class="qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
            <span class="qty-val">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
          </div>
        </div>
        <button class="cart-remove" onclick="removeFromCart(${item.id})" title="Eliminar">✕</button>
      </div>`;
  });
  container.innerHTML = html;
  const grandEl = document.getElementById('cartTotal');
  if (grandEl) grandEl.textContent = fmt(total);
}

function refreshAddBtns() {
  document.querySelectorAll('[data-add-btn]').forEach(btn => {
    const id = parseInt(btn.dataset.addBtn);
    const inCart = cart.some(i => i.id === id);
    btn.classList.toggle('in-cart', inCart);
    btn.textContent = inCart ? '✓' : '+';
  });
}

function getCartTotal() {
  return cart.reduce((s, i) => s + i.precio * i.qty, 0);
}

function clearCart() {
  cart = [];
  saveCart(); renderCartDrawer(); refreshAddBtns();
}

/* ─── MODAL ──────────────────────────────────────────────── */
let modalQty = 1;
let modalData = null;

function openModal(data) {
  modalData = data;
  modalQty = 1;
  const o = document.getElementById('productModal');
  o.querySelector('#mName').textContent = data.nombre;
  o.querySelector('#mCat').textContent = data.categoria;
  o.querySelector('#mBrand').textContent = data.marca ? `${data.marca} ${data.modelo || ''}` : '';
  o.querySelector('#mDesc').textContent = data.descripcion || 'Sin descripción disponible.';
  o.querySelector('#mQtyVal').textContent = 1;

  // Price
  const priceEl = o.querySelector('#mPrice');
  priceEl.textContent = fmt(data.precio);

  // Stock
  const stockEl = o.querySelector('#mStock');
  if (data.tipo === 'pedido') {
    stockEl.innerHTML = '<span class="badge badge-blue">📦 Bajo pedido</span>';
  } else if (data.stock === 0) {
    stockEl.innerHTML = '<span class="badge badge-red">Sin stock</span>';
  } else if (data.stock <= 5) {
    stockEl.innerHTML = `<span class="badge badge-gold">⚠ Últimas ${data.stock} unidades</span>`;
  } else {
    stockEl.innerHTML = '<span class="badge badge-green">✓ Disponible</span>';
  }

  // Price tiers (mayorista)
  const tiersEl = o.querySelector('#mTiers');
  if (data.tiers && data.tiers.length) {
    let tHtml = '<div class="price-tiers"><div class="price-tiers-title">Precios por volumen</div>';
    data.tiers.forEach(t => {
      const active = modalQty >= t.cantidad_minima;
      tHtml += `<div class="price-tier ${active ? 'active' : ''}"><span class="qty">×${t.cantidad_minima}+</span><span class="price">${fmt(t.precio)}</span></div>`;
    });
    tHtml += '</div>';
    tiersEl.innerHTML = tHtml;
  } else {
    tiersEl.innerHTML = '';
  }

  // Image
  const imgEl = o.querySelector('#mImg');
  imgEl.innerHTML = data.foto
    ? `<img src="${data.foto}" alt="${data.nombre}" onerror="this.parentElement.innerHTML='🛍'">`
    : '🛍';

  // Add btn
  const addBtn = o.querySelector('#mAddBtn');
  const noStock = data.stock === 0 && data.tipo !== 'pedido';
  addBtn.disabled = noStock;
  addBtn.textContent = noStock ? 'Sin stock' : '🛒 Agregar al carrito';
  addBtn.className = noStock ? 'btn btn-outline btn-lg' : 'btn btn-primary btn-lg';

  o.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('productModal').classList.remove('open');
  document.body.style.overflow = '';
  modalData = null;
}

function changeModalQty(d) {
  if (!modalData) return;
  const nq = modalQty + d;
  const maxStock = modalData.tipo === 'pedido' ? 9999 : modalData.stock;
  if (nq < 1 || nq > maxStock) return;
  modalQty = nq;
  document.getElementById('mQtyVal').textContent = modalQty;

  // Update price for mayorista tiers
  if (modalData.tiers && modalData.tiers.length) {
    let best = modalData.tiers[0].precio;
    modalData.tiers.forEach(t => {
      if (modalQty >= t.cantidad_minima) best = t.precio;
    });
    document.getElementById('mPrice').textContent = fmt(best);
    // Refresh tiers highlight
    document.querySelectorAll('.price-tier').forEach((el, i) => {
      const t = modalData.tiers[i];
      if (t) el.classList.toggle('active', modalQty >= t.cantidad_minima);
    });
  }
}

function addFromModal() {
  if (!modalData) return;
  addToCart(modalData.id, modalData.nombre, parseFloat(document.getElementById('mPrice').textContent.replace(/\D/g,'')), modalData.stock, modalData.tipo, null);
  if (modalQty > 1) {
    const item = cart.find(i => i.id === modalData.id);
    if (item) { item.qty = modalQty; saveCart(); }
  }
  closeModal();
}

/* ─── SEARCH + FILTER ────────────────────────────────────── */
function initSearch() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(card => {
      const text = card.dataset.search || '';
      card.style.display = text.includes(q) ? '' : 'none';
    });
    updateResultsCount();
  });
}

function updateResultsCount() {
  const el = document.getElementById('resultsCount');
  if (!el) return;
  const visible = document.querySelectorAll('.product-card:not([style*="display: none"])').length;
  el.textContent = `${visible} resultado${visible !== 1 ? 's' : ''}`;
}

/* ─── TOAST ───────────────────────────────────────────────── */
function toast(msg, type = 'info') {
  let c = document.getElementById('toastContainer');
  if (!c) { c = document.createElement('div'); c.id = 'toastContainer'; c.className = 'toast-container'; document.body.appendChild(c); }
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = msg;
  c.appendChild(el);
  setTimeout(() => { el.style.cssText = 'opacity:0;transform:translateX(16px);transition:all .3s'; setTimeout(() => el.remove(), 300); }, 2800);
}

/* ─── UTILS ──────────────────────────────────────────────── */
function fmt(n) {
  return '$' + Math.round(n).toLocaleString('es-AR', { maximumFractionDigits: 0 });
}

/* ─── INIT ───────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();
  renderCartDrawer();
  refreshAddBtns();
  initSearch();

  // Close modal on overlay click
  const mo = document.getElementById('productModal');
  if (mo) mo.addEventListener('click', e => { if (e.target === mo) closeModal(); });
});
