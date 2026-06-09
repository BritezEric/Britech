<?php
require_once 'config/server.php';
require_once 'app/helpers/EmailHelper.php';

$conexion = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$mensaje = '';
$tipo_mensaje = '';

if ($conexion->connect_error) {
    $mensaje = 'Error de conexion a la base de datos.';
    $tipo_mensaje = 'error';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Email invalido.';
        $tipo_mensaje = 'error';
    } else {
        $consulta = $conexion->prepare(
            "SELECT usuario_id, usuario_nombre, usuario_apellido FROM usuario WHERE usuario_email = ? AND email_verificado = 0"
        );

        if ($consulta) {
            $consulta->bind_param('s', $email);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if ($resultado && $resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                $consulta->close();

                $token = bin2hex(random_bytes(32));
                $token_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $actualizar = $conexion->prepare(
                    "UPDATE usuario SET token_verificacion = ?, token_expiracion = ? WHERE usuario_id = ?"
                );

                if ($actualizar) {
                    $actualizar->bind_param('ssi', $token, $token_expiracion, $usuario['usuario_id']);
                    $actualizar->execute();

                    if ($actualizar->affected_rows === 1) {
                        $emailHelper = new EmailHelper();
                        $nombre_completo = $usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido'];

                        if ($emailHelper->enviarEmailVerificacion($email, $nombre_completo, $token)) {
                            $mensaje = 'Email de verificacion reenviado exitosamente. Revisa tu bandeja.';
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = 'Error al enviar el email. Intentalo mas tarde.';
                            $tipo_mensaje = 'error';
                        }
                    } else {
                        $mensaje = 'Error al generar el token de verificacion.';
                        $tipo_mensaje = 'error';
                    }

                    $actualizar->close();
                } else {
                    $mensaje = 'Error interno en la actualizacion.';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = 'No se encontro una cuenta no verificada con ese email.';
                $tipo_mensaje = 'error';
                $consulta->close();
            }
        } else {
            $mensaje = 'Error interno en la consulta de usuario.';
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
  <title>Reenviar Verificacion - Britech</title>
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
      max-width: 440px;
      width: 100%;
      box-shadow: 0 4px 40px rgba(0,0,0,0.08);
    }

    /* Header */
    .card-header {
      background-color: #111111;
      padding: 28px 48px;
      text-align: center;
    }
    .brand {
      font-family: 'Syne', Georgia, serif;
      font-size: 24px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: 2px;
    }

    /* Body */
    .card-body {
      padding: 40px 44px 36px;
    }

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

    .card-body h1 {
      font-family: 'Syne', Georgia, serif;
      font-size: 26px;
      font-weight: 700;
      color: #111111;
      line-height: 1.2;
      margin-bottom: 10px;
      letter-spacing: -0.5px;
    }

    .subtitle {
      font-size: 14px;
      color: #777777;
      line-height: 1.65;
      margin-bottom: 28px;
    }

    /* Mensaje */
    .msg-box {
      border-radius: 0 8px 8px 0;
      padding: 14px 18px;
      font-size: 13px;
      line-height: 1.65;
      margin-bottom: 24px;
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

    /* Formulario */
    .form-group { margin-bottom: 20px; }

    label {
      display: block;
      font-size: 12px;
      font-weight: 500;
      color: #444444;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    input[type="email"] {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid #E3E3E3;
      border-radius: 10px;
      font-size: 15px;
      font-family: 'DM Sans', sans-serif;
      color: #111111;
      background: #FAFAFA;
      transition: border-color 0.2s;
      outline: none;
    }
    input[type="email"]:focus {
      border-color: #111111;
      background: #ffffff;
    }
    input[type="email"]::placeholder { color: #BBBBBB; }

    /* Boton */
    button[type="submit"] {
      width: 100%;
      padding: 14px;
      background-color: #111111;
      color: #ffffff;
      border: none;
      border-radius: 100px;
      font-family: 'Syne', Georgia, serif;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.3px;
      cursor: pointer;
      transition: opacity 0.2s;
      margin-top: 4px;
    }
    button[type="submit"]:hover { opacity: 0.82; }

    /* Links */
    .links {
      text-align: center;
      margin-top: 24px;
      display: flex;
      justify-content: center;
      gap: 4px;
    }
    .links a {
      font-size: 13px;
      color: #888888;
      text-decoration: none;
      padding: 0 10px;
      border-right: 1px solid #DDDDDD;
      line-height: 1;
    }
    .links a:last-child { border-right: none; }
    .links a:hover { color: #111111; }

    /* Footer */
    .card-footer {
      background-color: #111111;
      padding: 18px 44px;
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

      <span class="tag">Verificacion de cuenta</span>
      <h1>Reenviar enlace</h1>
      <p class="subtitle">Ingresa tu email y te enviamos un nuevo enlace de verificacion.</p>

      <?php if ($mensaje): ?>
        <div class="msg-box <?php echo $tipo_mensaje; ?>">
          <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required
                 placeholder="tu@email.com"
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>"/>
        </div>
        <button type="submit">Reenviar email</button>
      </form>

      <div class="links">
        <a href="login/">Iniciar sesion</a>
        <a href="register/">Registrarse</a>
      </div>

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