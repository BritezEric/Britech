<?php
require_once 'config/server.php';
require_once 'config/app.php';
require_once 'config/email_config.php';
require_once 'app/helpers/EmailHelper.php';

$conexion = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$mensaje     = '';
$tipo_mensaje = '';
$enviado     = false;

if ($conexion->connect_error) {
    $mensaje      = 'Error de conexion a la base de datos.';
    $tipo_mensaje = 'error';

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje      = 'El email ingresado no tiene un formato valido.';
        $tipo_mensaje = 'error';
    } else {
        // Buscamos el usuario (solo los que ya verificaron su email)
        $consulta = $conexion->prepare(
            "SELECT usuario_id, usuario_nombre, usuario_apellido
               FROM usuario
              WHERE usuario_email = ? AND email_verificado = 1
              LIMIT 1"
        );

        if ($consulta) {
            $consulta->bind_param('s', $email);
            $consulta->execute();
            $resultado = $consulta->get_result();

            // Por seguridad mostramos el mismo mensaje exista o no el usuario
            if ($resultado && $resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                $consulta->close();

                // Generar token seguro y expiración de 1 hora
                $token             = bin2hex(random_bytes(32));
                $token_expiracion  = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $actualizar = $conexion->prepare(
                    "UPDATE usuario
                        SET token_reset = ?, token_reset_expiracion = ?
                      WHERE usuario_id = ?"
                );

                if ($actualizar) {
                    $actualizar->bind_param('ssi', $token, $token_expiracion, $usuario['usuario_id']);
                    $actualizar->execute();
                    $actualizar->close();

                    $emailHelper     = new EmailHelper();
                    $nombre_completo = $usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido'];

                    $emailHelper->enviarEmailRecuperacion($email, $nombre_completo, $token);
                }
            } else {
                $consulta->close();
            }

            // Siempre mostramos este mensaje (no revelamos si el email existe)
            $enviado      = true;
            $mensaje      = 'Si el email esta registrado y verificado, recibiras un enlace para restablecer tu contrasena.';
            $tipo_mensaje = 'success';

        } else {
            $mensaje      = 'Error interno. Intentalo mas tarde.';
            $tipo_mensaje = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Olvidé mi contraseña — Britech</title>
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

    /* CARD */
    .card{
      background:var(--s950);border:1px solid var(--s750);border-radius:20px;
      overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.7);
    }

    /* HEADER */
    .card-header{
      background:linear-gradient(155deg,#0e0e0e 0%,#161616 50%,#0c0c0c 100%);
      border-bottom:1px solid var(--s750);padding:1.6rem 2rem;
      display:flex;align-items:center;gap:.75rem;position:relative;overflow:hidden;
    }
    .card-header::after{
      content:'';position:absolute;top:0;left:15%;right:15%;height:1px;
      background:linear-gradient(90deg,transparent,rgba(200,200,200,.1),transparent);
    }
    .brand-name{
      font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;
      color:var(--s50);letter-spacing:.1em;
    }
    .brand-tag{font-size:.58rem;color:var(--s500);letter-spacing:.18em;text-transform:uppercase;margin-top:.15rem}

    /* BODY */
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

    /* ALERT BOXES */
    .alert{
      display:flex;align-items:flex-start;gap:.5rem;padding:.65rem .85rem;
      border-radius:9px;font-size:.76rem;line-height:1.55;margin-bottom:1.1rem;border:1px solid;
    }
    .alert-icon{font-size:.8rem;flex-shrink:0;margin-top:.05rem}
    .alert.error{background:rgba(80,80,80,.1);border-color:rgba(120,120,120,.2);color:var(--s400)}
    .alert.success{background:rgba(100,100,100,.08);border-color:rgba(150,150,150,.18);color:var(--s300)}

    /* FORM */
    .field{margin-bottom:.9rem}
    .lbl{
      display:block;font-size:.62rem;font-weight:600;letter-spacing:.08em;
      text-transform:uppercase;color:var(--s500);margin-bottom:.38rem;
    }
    .iw{position:relative}
    .iico{
      position:absolute;left:.8rem;top:50%;transform:translateY(-50%);
      font-size:.75rem;color:var(--s600);pointer-events:none;
    }
    .inp{
      width:100%;padding:.65rem .8rem .65rem 2.2rem;
      background:var(--s850);border:1px solid var(--s750);border-radius:9px;
      font-family:'Inter',sans-serif;font-size:.85rem;color:var(--s100);
      outline:none;transition:border-color .18s,background .18s;
    }
    .inp::placeholder{color:var(--s600)}
    .inp:focus{border-color:var(--s400);background:var(--s800)}

    .btn-main{
      width:100%;padding:.75rem;border:none;border-radius:10px;
      font-family:'Inter',sans-serif;font-size:.88rem;font-weight:600;cursor:pointer;
      background:var(--s100);color:var(--s900);
      transition:opacity .15s,transform .13s;margin-top:.25rem;
    }
    .btn-main:hover{opacity:.88}
    .btn-main:active{transform:scale(.99)}
    .btn-main:disabled{opacity:.4;cursor:not-allowed}

    /* SENT STATE */
    .sent-state{text-align:center;padding:.5rem 0}
    .sent-icon{font-size:2rem;margin-bottom:.8rem;display:block}
    .sent-title{font-size:.95rem;font-weight:600;color:var(--s100);margin-bottom:.4rem}
    .sent-desc{font-size:.76rem;color:var(--s500);line-height:1.65}

    /* FOOTER LINKS */
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
    a.link-btn{display:inline;text-decoration:underline;text-decoration-color:var(--s700)}
    .sep{color:var(--s700);font-size:.65rem}

    /* CARD FOOTER */
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

    <!-- HEADER -->
    <div class="card-header">
      <div>
        <div class="brand-name">BRITECH</div>
        <div class="brand-tag">Sistema de ventas</div>
      </div>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <?php if ($enviado): ?>

        <!-- ESTADO: EMAIL ENVIADO -->
        <div class="sent-state">
          <span class="sent-icon">📬</span>
          <div class="sent-title">Revisá tu bandeja</div>
          <p class="sent-desc">
            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?><br><br>
            El enlace expira en <strong style="color:var(--s300)">1 hora</strong>.
            Si no ves el email, revisá tu carpeta de spam.
          </p>
        </div>

      <?php else: ?>

        <div class="section-tag">
          <div class="section-tag-dot"></div>
          Recuperar acceso
        </div>

        <div class="card-title">¿Olvidaste tu contraseña?</div>
        <p class="card-sub">Ingresá tu email y te enviamos un enlace para crear una nueva contraseña.</p>

        <?php if ($mensaje && !$enviado): ?>
          <div class="alert <?php echo $tipo_mensaje; ?>">
            <span class="alert-icon"><?php echo $tipo_mensaje === 'error' ? '⚠' : '✓'; ?></span>
            <span><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
          <div class="field">
            <label class="lbl">Email</label>
            <div class="iw">
              <span class="iico">📧</span>
              <input
                class="inp"
                type="email"
                name="email"
                required
                placeholder="tu@email.com"
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>"
              >
            </div>
          </div>

          <button class="btn-main" type="submit">Enviar enlace →</button>
        </form>

      <?php endif; ?>

      <div class="links-row">
        <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>" class="link-btn">← Volver al inicio</a>
        <span class="sep">·</span>
        <a href="<?php echo defined('APP_URL') ? APP_URL : '/'; ?>register/" class="link-btn">Crear cuenta</a>
      </div>

    </div><!-- /card-body -->

    <!-- CARD FOOTER -->
    <div class="card-footer">
      <p>Por seguridad, el enlace expira en 1 hora desde que fue generado.</p>
    </div>

  </div><!-- /card -->

  <div class="footer-outer">BRITECH &copy; <?php echo date('Y'); ?> — Sistema de ventas</div>
</div><!-- /shell -->

</body>
</html>