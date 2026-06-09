<?php

require_once __DIR__ . '/config/server.php';

$conexion = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$mensaje = 'Token invalido o expirado';
$tipo_mensaje = 'error';

if ($conexion->connect_error) {
    $mensaje = 'Error de conexion a la base de datos.';
    $tipo_mensaje = 'error';
} elseif (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $consulta = $conexion->prepare(
        "SELECT usuario_id FROM usuario WHERE token_verificacion = ? AND token_expiracion > NOW() AND email_verificado = 0"
    );

    if ($consulta) {
        $consulta->bind_param('s', $token);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado && $resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            $consulta->close();

            $actualizar = $conexion->prepare(
                "UPDATE usuario SET email_verificado = 1, token_verificacion = NULL, token_expiracion = NULL WHERE usuario_id = ?"
            );

            if ($actualizar) {
                $actualizar->bind_param('i', $usuario['usuario_id']);
                $actualizar->execute();

                if ($actualizar->affected_rows === 1) {
                    $mensaje = 'Tu email fue verificado correctamente. Ya podes iniciar sesion.';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Token invalido o expirado. Por favor solicita uno nuevo.';
                    $tipo_mensaje = 'error';
                }

                $actualizar->close();
            } else {
                $mensaje = 'Error interno en la actualizacion.';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Token invalido o expirado. Por favor solicita uno nuevo.';
            $tipo_mensaje = 'error';
            $consulta->close();
        }
    } else {
        $mensaje = 'Error interno en la consulta de verificacion.';
        $tipo_mensaje = 'error';
    }
} else {
    $mensaje = 'No se recibio ningun token de verificacion.';
    $tipo_mensaje = 'error';
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verificacion de Email - Britech</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap');

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DM Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background-color: #F0EFE9;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }

    .card {
      background: #ffffff;
      border-radius: 20px;
      overflow: hidden;
      max-width: 480px;
      width: 100%;
      box-shadow: 0 4px 40px rgba(0,0,0,0.08);
    }

    /* ── Header ── */
    .card-header {
      background-color: #111111;
      padding: 32px 48px;
      text-align: center;
    }

    .brand {
      font-family: 'Syne', Georgia, serif;
      font-size: 26px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: 2px;
    }

    /* ── Body ── */
    .card-body {
      padding: 44px 48px 40px;
      text-align: center;
    }

    /* Icono circulo */
    .icon-wrap {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
    }
    .icon-wrap.success { background-color: #EAFAF1; }
    .icon-wrap.error   { background-color: #FDF0F0; }

    .icon-wrap svg { width: 34px; height: 34px; }

    /* Tag */
    .tag {
      display: inline-block;
      background-color: #111111;
      color: #ffffff;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 5px 16px;
      border-radius: 100px;
      margin-bottom: 20px;
    }

    /* Titulo */
    .card-body h1 {
      font-family: 'Syne', Georgia, serif;
      font-size: 26px;
      font-weight: 700;
      color: #111111;
      line-height: 1.2;
      margin-bottom: 16px;
      letter-spacing: -0.5px;
    }

    /* Mensaje */
    .msg-box {
      border-radius: 10px;
      padding: 16px 20px;
      font-size: 14px;
      line-height: 1.7;
      margin-bottom: 32px;
      text-align: left;
    }
    .msg-box.success {
      background-color: #EAFAF1;
      border-left: 3px solid #27AE60;
      color: #1a6b3c;
    }
    .msg-box.error {
      background-color: #FDF0F0;
      border-left: 3px solid #E74C3C;
      color: #922b21;
    }

    /* Boton */
    .btn {
      display: inline-block;
      background-color: #111111;
      color: #ffffff;
      text-decoration: none;
      font-family: 'Syne', Georgia, serif;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.3px;
      padding: 14px 40px;
      border-radius: 100px;
      transition: opacity 0.2s ease;
    }
    .btn:hover { opacity: 0.82; }

    /* ── Footer ── */
    .card-footer {
      background-color: #111111;
      padding: 20px 48px;
      text-align: center;
    }
    .card-footer p {
      font-size: 11px;
      color: #888888;
      line-height: 1.6;
    }
    .card-footer a {
      color: #cccccc;
      text-decoration: none;
    }
  </style>
</head>
<body>

  <div class="card">

    <!-- HEADER -->
    <div class="card-header">
      <span class="brand">BRITECH</span>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <?php if ($tipo_mensaje === 'success'): ?>

        <div class="icon-wrap success">
          <svg viewBox="0 0 24 24" fill="none" stroke="#27AE60" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 6L9 17l-5-5"/>
          </svg>
        </div>
        <span class="tag">Verificacion exitosa</span>
        <h1>Email confirmado</h1>

      <?php else: ?>

        <div class="icon-wrap error">
          <svg viewBox="0 0 24 24" fill="none" stroke="#E74C3C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <span class="tag">Verificacion fallida</span>
        <h1>Algo salio mal</h1>

      <?php endif; ?>

      <div class="msg-box <?php echo $tipo_mensaje; ?>">
        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
      </div>

      <?php if ($tipo_mensaje === 'success'): ?>
        <a href="login/" class="btn">Iniciar sesion</a>
      <?php else: ?>
        <a href="register/" class="btn">Volver al registro</a>
      <?php endif; ?>

    </div>

    <!-- FOOTER -->
    <div class="card-footer">
      <p>
        Mensaje automatico &mdash; no respondas este email.<br/>
        <a href="#">Politica de privacidad</a> &nbsp;&middot;&nbsp; <a href="#">Terminos de uso</a>
      </p>
    </div>

  </div>

</body>
</html>