<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/stylesecurity.css">
<div class="bg-grid"></div>

<div class="shell">
  <div class="layout">

    <!-- ═══ LEFT PANEL — SLIDESHOW ═══ -->
    <div class="left-panel">
      <div class="lp-shine"></div>

      <div class="lp-brand">
        <img src="<?php echo APP_URL; ?>app/views/img/logo.png" alt="VENTAS" style="width:50px;height:50px;object-fit:contain;filter:invert(1) brightness(.85);border-radius:10px">
        <div>
          <div class="lp-name">BRITECH</div>
          <div class="lp-tagline">Sistema de ventas</div>
        </div>
      </div>

      <div class="slideshow">

        <!-- SLIDE 1: Ventas -->
        <div class="slide active" id="slide-0">
          <div class="slide-tag"><div class="slide-tag-dot"></div>Módulo 01</div>
          <div class="slide-title">Gestión de ventas y  ecommerce</div>
          <div class="slide-desc">Registra pedidos, emite comprobantes y haz seguimiento de cada venta en tiempo real.</div>
          <div class="mockup">
            <div class="mk-stat-row">
              <div class="mk-stat"><div class="mk-stat-lbl">Hoy</div><div class="mk-stat-val">$0</div></div>
              <div class="mk-stat"><div class="mk-stat-lbl">Pedidos</div><div class="mk-stat-val">0</div></div>
            </div>
            <div class="mk-row"><span class="mk-label">Cliente 1</span><span class="mk-val">$0</span></div>
            <div class="mk-row"><span class="mk-label">Cliente 2</span><span class="mk-val">$0</span></div>
            <div class="mk-row"><span class="mk-label">Cliente 3</span><span class="mk-val up">$0 ↑</span></div>
          </div>
        </div>

        <!-- SLIDE 2: Inventario -->
        <div class="slide" id="slide-1">
          <div class="slide-tag"><div class="slide-tag-dot"></div>Módulo 02</div>
          <div class="slide-title">Control de inventario</div>
          <div class="slide-desc">Visualiza el stock disponible, recibe alertas y administra productos fácilmente.</div>
          <div class="mockup">
            <div class="mk-item"><div class="mk-item-bar" style="background:#888"></div><span class="mk-item-name">Producto 1</span><span class="mk-badge b-ok">0 u.</span></div>
            <div class="mk-item"><div class="mk-item-bar" style="background:#555"></div><span class="mk-item-name">Producto 2</span><span class="mk-badge b-low">0 u. ⚠</span></div>
            <div class="mk-item"><div class="mk-item-bar" style="background:#aaa"></div><span class="mk-item-name">Producto 3</span><span class="mk-badge b-ok">0 u.</span></div>
          </div>
        </div>

        <!-- SLIDE 3: Clientes -->
        <div class="slide" id="slide-2">
          <div class="slide-tag"><div class="slide-tag-dot"></div>Módulo 03</div>
          <div class="slide-title">Gestión de clientes</div>
          <div class="slide-desc">Administra clientes, consulta historial y asigna listas de precio diferenciadas.</div>
          <div class="mockup">
            <div class="mk-client"><div class="mk-av">C1</div><div style="flex:1"><div class="mk-cname">Cliente 1</div><div class="mk-csub">0 pedidos · $0</div></div><span class="mk-cbadge">Tipo</span></div>
            <div class="mk-client"><div class="mk-av">C2</div><div style="flex:1"><div class="mk-cname">Cliente 2</div><div class="mk-csub">0 pedidos · $0</div></div><span class="mk-cbadge">Tipo</span></div>
          </div>
        </div>

        <!-- SLIDE 4: Caja -->
        <div class="slide" id="slide-3">
          <div class="slide-tag"><div class="slide-tag-dot"></div>Módulo 04</div>
          <div class="slide-title">Caja registradora</div>
          <div class="slide-desc">Gestiona la caja, registra ventas y controla el flujo de dinero.</div>
          <div class="mockup">
            <div class="mk-buscar">
              <svg class="mk-buscar-ico" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5"/><path d="M11 11l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span class="mk-buscar-txt">Buscar producto…</span>
            </div>
            <div class="mk-cart-lbl">Carrito</div>
            <div class="mk-citem"><div class="mk-cdot" style="background:#888"></div><span class="mk-cname2">Producto · x0</span><span class="mk-cprice">$0</span></div>
            <div class="mk-total"><span class="mk-total-lbl">Total</span><span class="mk-total-val">$0</span></div>
            <div class="mk-confirm">Confirmar venta →</div>
          </div>
        </div>

        <div class="slide-nav">
          <div class="dot on" onclick="gS(0);rA()"></div>
          <div class="dot" onclick="gS(1);rA()"></div>
          <div class="dot" onclick="gS(2);rA()"></div>
          <div class="dot" onclick="gS(3);rA()"></div>
          <div class="slide-arrows">
            <button class="arr-btn" onclick="pS();rA()">←</button>
            <button class="arr-btn" onclick="nS();rA()">→</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ RIGHT PANEL ═══ -->
    <div class="right-panel">

      <!-- LOGIN VIEW -->
      <div id="v-login" class="view active">
        <div class="rp-title">Bienvenido</div>
        <div class="rp-sub">Ingresa tus credenciales para continuar</div>

        <form method="POST" autocomplete="off">
          <div class="err-box" id="login-err" style="display:none"><span>⚠</span><span id="login-err-txt"></span></div>
          <?php
            if(isset($_POST['login_email']) && isset($_POST['login_clave'])){
                $insLogin->iniciarSesionControlador();
            }
          ?>
          <div class="field">
            <label class="lbl">Email</label>
            <div class="iw">
              <span class="iico">📧</span>
              <input class="inp" type="email" name="login_email" maxlength="100" required placeholder="tu@email.com">
            </div>
          </div>
          <div class="field">
            <label class="lbl">Contraseña</label>
            <div class="iw">
              <span class="iico">🔒</span>
              <input class="inp" type="password" name="login_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required placeholder="••••••••">
              <button class="eye-btn" type="button" onclick="tEye('login_clave',this)">👁</button>
            </div>
          </div>
          
          <button class="btn-main" type="submit">Iniciar sesión →</button>
          <div class="divider"><span>o</span></div>
          <p style="text-align:center;font-size:.76rem;color:var(--s500)">¿No tienes cuenta? <a href="<?php echo APP_URL; ?>register/" style="color:var(--s300);text-decoration:underline">Crear una cuenta</a></p>
        </form>
      </div>
      <p style="text-align:center;font-size:.76rem;color:var(--s500)">
  <a href="<?php echo APP_URL; ?>olvide-contrasena.php" 
     style="color:var(--s300);text-decoration:underline">
    ¿Olvidaste tu contraseña?
  </a>
</p>

    </div>
  </div>

  <div class="footer">VENTAS &copy; 2024 — Sistema de ventas</div>
</div>

<script>
/* ─── SLIDESHOW ─── */
function gS(n) {
  document.querySelectorAll('.slide').forEach((s, i) => s.classList.toggle('active', i === n));
  document.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('on', i === n));
  cur = n;
}
function nS() { gS((cur + 1) % 4); }
function pS() { gS((cur - 1 + 4) % 4); }
function rA() { clearInterval(aT); aT = setInterval(nS, 4200); }
let cur = 0, aT = setInterval(nS, 4200);

/* ─── HELPERS ─── */
function tEye(id, b) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  b.textContent = i.type === 'password' ? '👁' : '🙈';
}
</script>
