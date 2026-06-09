<?php
require_once 'config/server.php';
require_once 'config/app.php';

$conexion = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$mensaje      = '';
$tipo_mensaje = '';
$token_valido = false;
$token        = '';
$usuario_id   = null;
$cambiado     = false;

if ($conexion->connect_error) {
    $mensaje      = 'Error de conexion a la base de datos.';
    $tipo_mensaje = 'error';

} elseif (isset($_GET['token']) && !empty($_GET['token'])) {

    $token = trim($_GET['token']);

    // Validar que el token exista y no haya expirado
    $consulta = $conexion->prepare(
        "SELECT usuario_id, usuario_nombre
           FROM usuario
          WHERE token_reset = ? AND token_reset_expiracion > NOW()
          LIMIT 1"
    );

    if ($consulta) {
        $consulta->bind_param('s', $token);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado && $resultado->num_rows === 1) {
            $usuario      = $resultado->fetch_assoc();
            $token_valido = true;
            $usuario_id   = $usuario['usuario_id'];
        } else {
            $mensaje      = 'El enlace es invalido o ya expiro. Solicitá uno nuevo.';
            $tipo_mensaje = 'error';
        }
        $consulta->close();
    }

    // Procesar el formulario de nueva contraseña
    if ($token_valido && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $clave1 = $_POST['clave1'] ?? '';
        $clave2 = $_POST['clave2'] ?? '';

        if (strlen($clave1) < 7 || strlen($clave1) > 100) {
            $mensaje      = 'La contraseña debe tener entre 7 y 100 caracteres.';
            $tipo_mensaje = 'error';
        } elseif (!preg_match('/^[a-zA-Z0-9$@.\-]+$/', $clave1)) {
            $mensaje      = 'La contraseña contiene caracteres no permitidos.';
            $tipo_mensaje = 'error';
        } elseif ($clave1 !== $clave2) {
            $mensaje      = 'Las contraseñas no coinciden.';
            $tipo_mensaje = 'error';
        } else {
            $hash = password_hash($clave1, PASSWORD_BCRYPT, ['cost' => 10]);

            $actualizar = $conexion->prepare(
                "UPDATE usuario
                    SET usuario_clave = ?,
                        token_reset = NULL,
                        token_reset_expiracion = NULL
                  WHERE usuario_id = ?"
            );

            if ($actualizar) {
                $actualizar->bind_param('si', $hash, $usuario_id);
                $actualizar->execute();

                if ($actualizar->affected_rows === 1) {
                    $cambiado     = true;
                    $token_valido = false;
                    $mensaje      = '¡Contraseña actualizada correctamente! Ya podés iniciar sesion.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje      = 'Error al actualizar la contraseña. Intentalo de nuevo.';
                    $tipo_mensaje = 'error';
                }
                $actualizar->close();
            }
        }
    }

} else {
    // Redirigir si no hay token en la URL
    header('Location: ' . (defined('APP_URL') ? APP_URL : '/') . 'olvide-contrasena.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Restablecer contraseña — Britech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    :root{
      --s950:#080808;--s900:#111111;--s850:#181818;--s800:#202020;--s750:#2a2a2a;
      --s700:#333333;--s600:#444444;--s500:#666666;--s400:#888888;--s300:#aaaaaa;
      --s200:#cccccc;--s100:#e0e0e0;--s50:#f5f5f5;
    }
    body{
      font-family:'Inter',sans-serif;background:var(--s900);color:var(--s100);
      min-height:100vh;display:flex;align-items:center;justify-content:center;
      padding:1.5rem 1rem;position:relative;overflow-x:hidden;
    }
    .bg-grid{
      position:fixed;inset:0;
      background-image:linear-gradient(var(--s800) 1px,transparent 1px),linear-gradient(90deg,var(--s800) 1px,transparent 1px);
      background-size:36px 36px;opacity:.35;pointer-events:none;z-index:0;
    }
    .shell{position:relative;z-index:1;width:100%;max-width:420px;animation:fadeUp .42s ease both}
    @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

    .card{
      background:var(--s950);border:1px solid var(--s750);border-radius:20px;
      overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.7);
    }

    .card-header{
      background:linear-gradient(155deg,#0e0e0e 0%,#161616 50%,#0c0c0c 100%);
      border-bottom:1px solid var(--s750);padding:1.6rem 2rem;
      display:flex;align-items:center;gap:.75rem;position:relative;overflow:hidden;
    }
    .card-header::after{
      content:'';position:absolute;top:0;left:15%;right:15%;height:1px;
      background:linear-gradient(90deg,transparent,rgba(200,200,200,.1),transparent);
    }
    .brand-name{font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--s50);letter-spacing:.1em}
    .brand-tag{font-size:.58rem;color:var(--s500);letter-spacing:.18em;text-transform:uppercase;margin-top:.15rem}

    .card-body{padding:2rem 2rem 1.8rem}

    .section-tag{
      display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .65rem;
      background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);
      border-radius:20px;font-size:.6rem;font-weight:600;color:var(--s300);
      letter-spacing:.08em;text-transform:uppercase;margin-bottom:.9rem;
    }
    .section-tag-dot{width:5px;height:5px;border-radius:50%;background:var(--s400)}

    .card-title{font-size:1rem;font-weight:600;color:var(--s50);margin-bottom:.3rem}
    .card-sub{font-size:.76rem;color:var(--s500);line-height:1.6;margin-bottom:1.4rem}

    .alert{
      display:flex;align-items:flex-start;gap:.5rem;padding:.65rem .85rem;
      border-radius:9px;font-size:.76rem;line-height:1.55;margin-bottom:1.1rem;border:1px solid;
    }
    .alert-icon{font-size:.8rem;flex-shrink:0;margin-top:.05rem}
    .alert.error{background:rgba(80,80,80,.1);border-color:rgba(120,120,120,.2);color:var(--s400)}
    .alert.success{background:rgba(100,100,100,.08);border-color:rgba(150,150,150,.18);color:var(--s300)}

    .field{margin-bottom:.9rem}
    .lbl{display:block;font-size:.62rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--s500);margin-bottom:.38rem}
    .iw{position:relative}
    .iico{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:var(--s600);pointer-events:none}
    .inp{
      width:100%;padding:.65rem .8rem .65rem 2.2rem;
      background:var(--s850);border:1px solid var(--s750);border-radius:9px;
      font-family:'Inter',sans-serif;font-size:.85rem;color:var(--s100);
      outline:none;transition:border-color .18s,background .18s;
    }
    .inp::placeholder{color:var(--s600)}
    .inp:focus{border-color:var(--s400);background:var(--s800)}
    .eye-btn{
      position:absolute;right:.7rem;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;font-size:.75rem;
      color:var(--s500);padding:0;transition:color .14s;
    }
    .eye-btn:hover{color:var(--s200)}

    /* Barra de fuerza de contraseña */
    .strength-bar{height:3px;border-radius:2px;background:var(--s800);margin-top:.45rem;overflow:hidden}
    .strength-fill{height:100%;width:0%;border-radius:2px;transition:width .25s,background .25s}
    .strength-label{font-size:.6rem;color:var(--s600);margin-top:.3rem;min-height:.8rem}

    .btn-main{
      width:100%;padding:.75rem;border:none;border-radius:10px;
      font-family:'Inter',sans-serif;font-size:.88rem;font-weight:600;cursor:pointer;
      background:var(--s100);color:var(--s900);
      transition:opacity .15s,transform .13s;margin-top:.25rem;
    }
    .btn-main:hover{opacity:.88}
    .btn-main:active{transform:scale(.99)}

    .success-state{text-align:center;padding:.5rem 0}
    .success-icon{font-size:2rem;margin-bottom:.8rem;display:block}
    .success-title{font-size:.95rem;font-weight:600;color:var(--s100);margin-bottom:.4rem}
    .success-desc{font-size:.76rem;color:var(--s500);line-height:1.65}

    .expired-state{text-align:center;padding:.5rem 0}
    .expired-icon{font-size:2rem;margin-bottom:.8rem;display:block}
    .expired-title{font-size:.95rem;font-weight:600;color:var(--s400);margin-bottom:.4rem}
    .expired-desc{font-size:.76rem;color:var(--s500);line-height:1.65}

    .links-row{
      display:flex;align-items:center;justify-content:center;gap:.5rem;
      margin-top:1.3rem;padding-top:1.1rem;border-top:1px solid var(--s800);
    }
    .link-btn{
      background:none;border:none;font-family:'Inter',sans-serif;
      font-size:.72rem;color:var(--s500);cursor:pointer;
      text-decoration:underline;text-decoration-color:var(--s700);
      padding:0;transition:color .14s;
    }
    .link-btn:hover{color:var(--s200)}
    a.link-btn{display:inline}
    .sep{color:var(--s700);font-size:.65rem}

    .card-footer{
      background:var(--s850);border-top:1px solid var(--s750);
      padding:.9rem 2rem;text-align:center;
    }
    .card-footer p{font-size:.62rem;color:var(--s600);line-height:1.6}
    .footer-outer{text-align:center;margin-top:1rem;font-size:.64rem;color:var(--s700);letter-spacing:.04em}

    @media(max-width:480px){
      .card-body{padding:1.6rem 1.4rem 1.5rem}
      .card-header{padding:1.3rem 1.4rem}
    }
  </style>
</head>
<body>

<div class="bg-grid"></div>

<div class="shell">
  <div class="card">

    <div class="card-header">
      <div>
        <div class="brand-name">BRITECH</div>
        <div class="brand-tag">Sistema de ventas</div>
      </div>
    </div>

    <div class="card-body">

      <?php if ($cambiado): ?>
        <!-- CONTRASEÑA CAMBIADA OK -->
        <div class="success-state">
          <span class="success-icon">✅</span>
          <div class="success-title">¡Contraseña actualizada!</div>
          <p class="success-desc">
            Tu contraseña fue cambiada correctamente.<br>
            Ya podés iniciar sesion con tu nueva contraseña.
          </p>
        </div>
        <div class="links-row">
          <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>" class="link-btn">← Ir al inicio de sesion</a>
        </div>

      <?php elseif (!$token_valido): ?>
        <!-- TOKEN INVÁLIDO O EXPIRADO -->
        <div class="expired-state">
          <span class="expired-icon">⏰</span>
          <div class="expired-title">Enlace invalido o expirado</div>
          <p class="expired-desc">
            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
          </p>
        </div>
        <div class="links-row">
          <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>olvide-contrasena.php" class="link-btn">Solicitar nuevo enlace</a>
          <span class="sep">·</span>
          <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>" class="link-btn">Iniciar sesion</a>
        </div>

      <?php else: ?>
        <!-- FORMULARIO NUEVA CONTRASEÑA -->
        <div class="section-tag">
          <div class="section-tag-dot"></div>
          Nueva contraseña
        </div>

        <div class="card-title">Creá tu nueva contraseña</div>
        <p class="card-sub">Ingresá la nueva contraseña. Debe tener al menos 7 caracteres.</p>

        <?php if ($mensaje): ?>
          <div class="alert <?php echo $tipo_mensaje; ?>">
            <span class="alert-icon">⚠</span>
            <span><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="restablecer-contrasena.php?token=<?php echo urlencode($token); ?>" autocomplete="off">

          <div class="field">
            <label class="lbl">Nueva contraseña</label>
            <div class="iw">
              <span class="iico">🔒</span>
              <input class="inp" type="password" id="clave1" name="clave1"
                     pattern="[a-zA-Z0-9$@.\-]{7,100}" minlength="7" maxlength="100"
                     required placeholder="••••••••"
                     oninput="checkStrength(this.value)">
              <button class="eye-btn" type="button" onclick="tEye('clave1',this)">👁</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="sbar"></div></div>
            <div class="strength-label" id="slabel"></div>
          </div>

          <div class="field">
            <label class="lbl">Confirmá la contraseña</label>
            <div class="iw">
              <span class="iico">🔒</span>
              <input class="inp" type="password" id="clave2" name="clave2"
                     minlength="7" maxlength="100"
                     required placeholder="••••••••">
              <button class="eye-btn" type="button" onclick="tEye('clave2',this)">👁</button>
            </div>
          </div>

          <button class="btn-main" type="submit">Guardar contraseña →</button>
        </form>

        <div class="links-row">
          <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>" class="link-btn">← Volver al inicio</a>
        </div>

      <?php endif; ?>

    </div><!-- /card-body -->

    <div class="card-footer">
      <p>Por seguridad, no compartas este enlace con nadie.</p>
    </div>

  </div><!-- /card -->

  <div class="footer-outer">BRITECH &copy; <?php echo date('Y'); ?> — Sistema de ventas</div>
</div>

<script>
function tEye(id, b) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  b.textContent = i.type === 'password' ? '👁' : '🙈';
}

function checkStrength(val) {
  const bar   = document.getElementById('sbar');
  const label = document.getElementById('slabel');
  let score = 0;
  if (val.length >= 7)  score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[$@.\-]/.test(val)) score++;

  const levels = [
    { pct:'20%', bg:'#555555', txt:'Muy débil'  },
    { pct:'40%', bg:'#777777', txt:'Débil'       },
    { pct:'60%', bg:'#999999', txt:'Regular'     },
    { pct:'80%', bg:'#bbbbbb', txt:'Fuerte'      },
    { pct:'100%',bg:'#e0e0e0', txt:'Muy fuerte'  },
  ];

  const lvl = levels[Math.min(score, 4)];
  bar.style.width      = lvl.pct;
  bar.style.background = lvl.bg;
  label.style.color    = lvl.bg;
  label.textContent    = val.length ? lvl.txt : '';
}
</script>

</body>
</html>