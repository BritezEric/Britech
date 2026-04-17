// ╔══════════════════════════════════════════════════════════╗
// ║  BRITECH — js/api.js                                    ║
// ║  Capa de acceso a la API PHP/MySQL                      ║
// ║  Importar en: views/admin.html y views/tienda.html      ║
// ╚══════════════════════════════════════════════════════════╝

// Rutas relativas desde cualquier archivo dentro de /views/
const API_PRODUCTOS = '../api/productos.php';
const API_STORAGE   = '../api/storage.php';

// ─── Storage sincrónico (XHR) ────────────────────────────────
function storageGet(entity) {
    try {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `${API_STORAGE}?entity=${encodeURIComponent(entity)}`, false);
        xhr.send();
        if (xhr.status === 200 && xhr.responseText) {
            const res = JSON.parse(xhr.responseText);
            if (res.ok && Array.isArray(res.data)) return res.data;
        }
    } catch (e) { console.error('[storageGet]', e); }
    return [];
}

function storageSet(entity, data) {
    try {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', API_STORAGE, false);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({ entity, data }));
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.ok) return data;
        }
    } catch (e) { console.error('[storageSet]', e); }
    return data;
}

// ─── Fetch asíncrono con manejo centralizado de errores ──────
async function apiFetch(endpoint, method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    try {
        const res  = await fetch(endpoint, opts);
        const data = await res.json();
        if (!data.ok) {
            if (typeof toast === 'function') toast(data.error || 'Error en el servidor', 'error');
            return null;
        }
        return data;
    } catch (e) {
        if (typeof toast === 'function') toast('Error de conexión con el servidor', 'error');
        console.error('[apiFetch]', e);
        return null;
    }
}

// ─── Cache en memoria ────────────────────────────────────────
window._productos = null;

async function getProductosCache() {
    if (window._productos) return window._productos;
    const res = await apiFetch(API_PRODUCTOS);
    window._productos = res ? res.data : [];
    return window._productos;
}

function invalidateProductCache() { window._productos = null; }

// ─── CRUD Productos ──────────────────────────────────────────
async function renderInventario() {
    const res = await apiFetch(API_PRODUCTOS);
    if (!res) return;
    window._productos = res.data;
    if (typeof renderInvStats  === 'function') renderInvStats(res.data);
    if (typeof renderInvTable  === 'function') renderInvTable(res.data);
    if (typeof renderInvGrid   === 'function') renderInvGrid(res.data);
}

async function saveProduct() {
    const id        = document.getElementById('editProductId').value;
    const nombre    = document.getElementById('pName').value.trim();
    const compra    = Math.round(parseFloat(document.getElementById('pCompra').value) || 0);
    const stock     = parseInt(document.getElementById('pStock').value) || 0;
    const minorista = Math.round(parseFloat(document.getElementById('pMinorista').value) || 0);
    const mayorista = Math.round(parseFloat(document.getElementById('pMayorista').value) || 0);
    const desc      = document.getElementById('pDesc').value.trim();

    if (!nombre)              { toast('El nombre es requerido', 'error'); return; }
    if (!minorista || !mayorista) { toast('Los precios son requeridos', 'error'); return; }

    const payload = { nombre, descripcion: desc, stock, compra,
                      precio_minorista: minorista, precio_mayorista: mayorista,
                      imagenes: typeof pendingImages !== 'undefined' ? pendingImages : [] };
    let res;
    if (id) { payload.id = parseInt(id); res = await apiFetch(API_PRODUCTOS, 'PUT', payload); }
    else    { res = await apiFetch(API_PRODUCTOS, 'POST', payload); }
    if (!res) return;

    toast(id ? 'Producto actualizado' : 'Producto agregado');
    if (typeof closeModal       === 'function') closeModal('productModal');
    if (typeof resetProductModal=== 'function') resetProductModal();
    invalidateProductCache();
    renderInventario();
}

async function editProduct(id) {
    const res = await apiFetch(`${API_PRODUCTOS}?id=${id}`);
    if (!res) return;
    const p = res.data;
    document.getElementById('editProductId').value             = p.id;
    document.getElementById('productModalTitle').textContent   = 'Editar Producto';
    document.getElementById('pName').value                     = p.nombre;
    document.getElementById('pCompra').value                   = p.compra || '';
    document.getElementById('pStock').value                    = p.stock;
    document.getElementById('pMinorista').value                = p.precio_minorista || '';
    document.getElementById('pMayorista').value                = p.precio_mayorista || '';
    document.getElementById('pDesc').value                     = p.descripcion || '';
    if (typeof pendingImages !== 'undefined') pendingImages    = [...(p.imagenes || [])];
    if (typeof renderImgPreviews === 'function') renderImgPreviews('pImgPreviews');
    if (typeof openModal === 'function') openModal('productModal');
}

async function deleteProduct(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    const res = await apiFetch(API_PRODUCTOS, 'DELETE', { id });
    if (!res) return;
    toast('Producto eliminado', 'info');
    invalidateProductCache();
    renderInventario();
}

async function showProductInfo(id) {
    const res = await apiFetch(`${API_PRODUCTOS}?id=${id}`);
    if (!res) return;
    const p    = res.data;
    const min  = p.precio_minorista || 0;
    const may  = p.precio_mayorista || 0;
    const cmp  = p.compra || 0;
    const pMin = cmp > 0 ? (((min - cmp) / cmp) * 100).toFixed(1) : '—';
    const pMay = cmp > 0 ? (((may - cmp) / cmp) * 100).toFixed(1) : '—';
    const imgs = (p.imagenes || []).slice(0, 3)
        .map(b => `<img src="${b}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">`)
        .join('');
    document.getElementById('productInfoBody').innerHTML = `
        <div style="display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap">${imgs || '<span style="color:var(--text3);font-size:.85rem">Sin imágenes</span>'}</div>
        <h3 style="font-family:var(--font-d);margin-bottom:.8rem">${p.nombre}</h3>
        ${p.descripcion ? `<p style="color:var(--text2);font-size:.88rem;margin-bottom:1rem">${p.descripcion}</p>` : ''}
        <div class="totals-box">
            <div class="totals-row"><span>Stock actual</span><span style="color:${p.stock===0?'var(--red)':'var(--green)'}">${p.stock} unidades</span></div>
            <div class="totals-row"><span>Precio compra 🔒</span><span style="color:var(--text3)">${fmt(cmp)}</span></div>
            <div class="totals-row"><span>Minorista</span><span>${fmt(min)} <small style="color:var(--green);font-size:.75rem">(+${pMin}%)</small></span></div>
            <div class="totals-row"><span>Mayorista</span><span>${fmt(may)} <small style="color:var(--green);font-size:.75rem">(+${pMay}%)</small></span></div>
        </div>
        <div class="mt-2 flex gap-1">
            <button class="btn btn-ghost btn-sm" onclick="closeModal('productInfoModal');editProduct(${p.id})">Editar</button>
            <button class="btn btn-danger btn-sm" onclick="closeModal('productInfoModal');deleteProduct(${p.id})">Eliminar</button>
        </div>`;
    if (typeof openModal === 'function') openModal('productInfoModal');
}

// ─── Poblar selects de productos (Ventas / Envíos / Inversión) ─
async function refreshSaleProductList() {
    const type     = document.getElementById('saleClientType')?.value;
    const products = await getProductosCache();
    const combos   = storageGet('combos');
    const sel      = document.getElementById('saleProductSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Seleccioná —</option>';
    products.forEach(p => {
        const price = type === 'mayorista' ? p.precio_mayorista : p.precio_minorista;
        sel.innerHTML += `<option value="p_${p.id}" data-price="${price}" data-name="${p.nombre}" data-stock="${p.stock}">${p.nombre} — ${fmt(price)} (stock: ${p.stock})</option>`;
    });
    combos.forEach(c => {
        const price = type === 'mayorista' ? c.mayorista : c.minorista;
        sel.innerHTML += `<option value="c_${c.id}" data-price="${price}" data-name="${c.name}" data-stock="9999">⭐ ${c.name} — ${fmt(price)}</option>`;
    });
}

async function refreshShipProductList() {
    const type     = document.getElementById('shClientType')?.value;
    const products = await getProductosCache();
    const combos   = storageGet('combos');
    const sel      = document.getElementById('shProductSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Seleccioná —</option>';
    products.forEach(p => {
        const price = type === 'mayorista' ? p.precio_mayorista : p.precio_minorista;
        sel.innerHTML += `<option value="p_${p.id}" data-price="${price}" data-name="${p.nombre}" data-stock="${p.stock}">${p.nombre} — ${fmt(price)}</option>`;
    });
    combos.forEach(c => {
        const price = type === 'mayorista' ? c.mayorista : c.minorista;
        sel.innerHTML += `<option value="c_${c.id}" data-price="${price}" data-name="${c.name}" data-stock="9999">⭐ ${c.name} — ${fmt(price)}</option>`;
    });
}

async function fillInversionProductSel() {
    const products = await getProductosCache();
    const sel = document.getElementById('invProductSel');
    if (!sel) return;
    sel.innerHTML = '<option value="">No actualizar stock</option>';
    products.forEach(p => {
        sel.innerHTML += `<option value="${p.id}">${p.nombre} (stock: ${p.stock})</option>`;
    });
}
