<div class="bg-grid"></div>

<div class="shell">
  <div class="layout">

    <!-- ═══ LEFT PANEL — SLIDESHOW ═══ -->
    <div class="left-panel">
      <div class="lp-shine"></div>

      <div class="lp-brand">
        <img src="<?php echo APP_URL; ?>app/views/img/logo.png" alt="VENTAS" style="width:50px;height:50px;object-fit:contain;filter:invert(1) brightness(.85);border-radius:10px">
        <div>
          <div class="lp-name">Britech</div>
          <div class="lp-tagline">Sistema de gestión y ventas</div>
        </div>
      </div>

      <div class="slideshow">
        <div class="slide active" id="slide-0">
          <div class="slide-tag"><div class="slide-tag-dot"></div>Módulo 01</div>
          <div class="slide-title">Gestión de ventas</div>
          <div class="slide-desc">Registra pedidos, emite comprobantes y haz seguimiento de cada venta en tiempo real.</div>
        </div>
      </div>
    </div>

    <!-- ═══ RIGHT PANEL ═══ -->
    <div class="right-panel">
      <div id="v-register" class="view active">

        <div class="rp-title">Crear cuenta</div>
        <div class="rp-sub">Completa tus datos para registrarte</div>

        <form method="POST" autocomplete="off">
          <div class="err-box" id="reg-err" style="display:none"><span>⚠</span><span id="reg-err-txt"></span></div>

          <?php
            if(isset($_POST['usuario_email']) && isset($_POST['usuario_clave_1'])){
                $insLogin->registrarUsuarioPublicoControlador();
            }
          ?>

          <div class="field">
            <label class="lbl">Nombre <span class="req">*</span></label>
            <div class="iw"><span class="iico">👤</span><input class="inp" type="text" name="usuario_nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required placeholder="Juan"></div>
          </div>

          <div class="field">
            <label class="lbl">Apellido <span class="req">*</span></label>
            <div class="iw"><span class="iico">👤</span><input class="inp" type="text" name="usuario_apellido" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required placeholder="García"></div>
          </div>

          <div class="field">
            <label class="lbl">Email <span class="req">*</span></label>
            <div class="iw"><span class="iico">✉</span><input class="inp" type="email" name="usuario_email" maxlength="100" required placeholder="tu@correo.com"></div>
          </div>

          <div class="field">
            <label class="lbl">Contraseña <span class="req">*</span></label>
            <div class="iw">
              <span class="iico">🔒</span>
              <input class="inp" type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required placeholder="Mín. 7 caracteres">
              <button class="eye-btn" type="button" onclick="tEye('usuario_clave_1',this)">👁</button>
            </div>
          </div>

          <div class="field">
            <label class="lbl">Confirmar contraseña <span class="req">*</span></label>
            <div class="iw">
              <span class="iico">🔒</span>
              <input class="inp" type="password" name="usuario_clave_2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required placeholder="Repetí tu contraseña">
              <button class="eye-btn" type="button" onclick="tEye('usuario_clave_2',this)">👁</button>
            </div>
          </div>

          <div class="field">
            <label class="lbl">Tipo de cuenta <span class="req">*</span></label>
            <div class="iw">
              <span class="iico">🎯</span>
              <select class="inp" name="tipo_cuenta" required style="cursor:pointer">
                <option value="">-- Seleccionar --</option>
                <option value="minorista">Minorista (Cliente)</option>
                <option value="mayorista">Mayorista (Cliente Wholesale)</option>
              </select>
            </div>
          </div>

          <button class="btn-main" type="submit">Crear cuenta ✓</button>
          <div class="divider"><span>o</span></div>
          <p style="text-align:center;font-size:.76rem;color:var(--s500)">¿Ya tienes cuenta? <a href="<?php echo APP_URL; ?>login/" style="color:var(--s300);text-decoration:underline">Inicia sesión</a></p>
        </form>
      </div>
    </div>
  </div>

  <div class="footer">VENTAS &copy; 2024 — Sistema de ventas</div>
</div>

<style>
/* ── BASIC STYLES ── */
.bg-grid {position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at 25% 25%, rgba(255,255,255,.02) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,.02) 0%, transparent 50%);background-size:50px 50px;z-index:-1}
.shell {min-height:100vh;display:flex;flex-direction:column}
.layout {flex:1;display:grid;grid-template-columns:1fr 1fr;min-height:100vh}
.left-panel {background:linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);padding:2rem;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;overflow:hidden}
.right-panel {background:#111;padding:2rem;display:flex;align-items:center;justify-content:center}
.footer {background:#000;color:#666;text-align:center;padding:1rem;font-size:.8rem}

/* ── BRAND ── */
.lp-brand {display:flex;align-items:center;gap:1rem;margin-bottom:2rem;z-index:2}
.lp-name {font-size:1.5rem;font-weight:700;color:#fff;margin:0}
.lp-tagline {font-size:.8rem;color:#999;margin:0}

/* ── FORM ── */
.rp-title {font-size:1.8rem;font-weight:700;color:#fff;margin-bottom:.5rem}
.rp-sub {color:#999;margin-bottom:1.5rem}
.field {margin-bottom:1rem}
.lbl {display:block;font-size:.85rem;font-weight:600;color:#ccc;margin-bottom:.3rem}
.iw {position:relative;display:flex;align-items:center}
.iico {position:absolute;left:.8rem;color:#666;font-size:1rem;z-index:1}
.inp {width:100%;padding:.8rem .8rem .8rem 2.5rem;border:1px solid #333;border-radius:8px;background:#1a1a1a;color:#fff;font-size:.9rem}
.inp:focus {outline:none;border-color:#555}
.btn-main {width:100%;padding:.9rem;border:none;border-radius:8px;background:linear-gradient(135deg, #4ade80 0%, #22c55e 100%);color:#000;font-weight:600;font-size:.9rem;cursor:pointer;margin-top:.5rem}
.btn-main:hover {background:linear-gradient(135deg, #22c55e 0%, #16a34a 100%)}
.divider {text-align:center;margin:1rem 0;position:relative}
.divider span {background:#111;padding:0 .5rem;color:#666;font-size:.8rem}

/* ── ERRORS ── */
.err-box {display:flex;gap:.5rem;align-items:flex-start;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#ef4444}
.req {color:#ef4444}

/* ── EYE BUTTON ── */
.eye-btn {position:absolute;right:.8rem;border:none;background:none;color:#666;cursor:pointer;font-size:1rem;padding:.2rem}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .layout {grid-template-columns:1fr;grid-template-rows:1fr auto}
  .left-panel {display:none}
  .right-panel {padding:1rem}
}
</style>

<script>
/* ─── EYE ─── */
function tEye(id,b){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';b.textContent=i.type==='password'?'👁':'🙈';}
</script>