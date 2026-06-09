<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/PHPMailer.php';
require_once __DIR__ . '/../../vendor/SMTP.php';
require_once __DIR__ . '/../../vendor/Exception.php';
require_once __DIR__ . '/../../config/email_config.php';

class EmailHelper {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host       = SMTP_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = SMTP_USERNAME;
        $this->mail->Password   = SMTP_PASSWORD;
        $this->mail->SMTPSecure = SMTP_SECURE;
        $this->mail->Port       = SMTP_PORT;
        $this->mail->CharSet    = 'UTF-8';

        $this->mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    }

    public function enviarEmailVerificacion($email, $nombre, $token) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $nombre);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verifica tu cuenta - Britech';

            $urlVerificacion = BASE_URL . 'verificar.php?token=' . $token;

            $this->mail->Body    = $this->getTemplateVerificacion($nombre, $urlVerificacion);
            $this->mail->AltBody = "Hola $nombre,\n\nPara verificar tu cuenta, copia este enlace:\n$urlVerificacion\n\nExpira en 1 hora.\n\n- Britech";

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            error_log('EmailHelper error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  RECUPERACIÓN DE CONTRASEÑA
    // ─────────────────────────────────────────────────────────────────

    public function enviarEmailRecuperacion($email, $nombre, $token) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $nombre);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Restablecer contraseña - Britech';

            $urlReset = BASE_URL . 'restablecer-contrasena.php?token=' . $token;

            $this->mail->Body    = $this->getTemplateRecuperacion($nombre, $urlReset);
            $this->mail->AltBody = "Hola $nombre,\n\nRecibimos una solicitud para restablecer tu contraseña.\n\nCopiá este enlace en tu navegador:\n$urlReset\n\nExpira en 1 hora. Si no solicitaste este cambio, ignorá este email.\n\n- Britech";

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            error_log('EmailHelper [recuperacion] error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    private function getTemplateRecuperacion($nombre, $urlReset) {
        $nombreSafe = htmlspecialchars($nombre,   ENT_QUOTES, 'UTF-8');
        $linkSafe   = htmlspecialchars($urlReset, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Restablecer contraseña - Britech</title>
</head>
<body style="margin:0;padding:0;background-color:#F0EFE9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#1a1a1a;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0EFE9;padding:40px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        <!-- HEADER -->
        <tr>
          <td style="background-color:#111111;border-radius:16px 16px 0 0;padding:32px 48px;text-align:center;">
            <span style="font-family:Georgia,'Times New Roman',serif;font-size:28px;font-weight:700;color:#ffffff;letter-spacing:2px;">BRITECH</span>
          </td>
        </tr>

        <!-- BODY -->
        <tr>
          <td style="background-color:#ffffff;padding:48px 48px 40px;">

            <div style="margin-bottom:24px;">
              <span style="display:inline-block;background-color:#111111;color:#ffffff;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;padding:5px 16px;border-radius:100px;">
                Recuperar contraseña
              </span>
            </div>

            <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:30px;font-weight:700;color:#111111;line-height:1.2;margin:0 0 20px;letter-spacing:-0.5px;">
              Restablecé tu<br/>contraseña
            </h1>

            <p style="font-size:15px;color:#555555;line-height:1.8;margin:0 0 36px;">
              Hola, <strong style="color:#111111;font-weight:600;">{$nombreSafe}</strong>.<br/><br/>
              Recibimos una solicitud para restablecer la contraseña de tu cuenta en Britech.
              Hacé clic en el botón para crear una nueva contraseña.
            </p>

            <!-- Boton CTA -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr><td align="center">
                <a href="{$linkSafe}" style="display:inline-block;background-color:#111111;color:#ffffff;text-decoration:none;font-family:Georgia,'Times New Roman',serif;font-size:15px;font-weight:700;padding:16px 48px;border-radius:100px;">
                  Restablecer contraseña
                </a>
              </td></tr>
            </table>

            <!-- Link fallback -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr>
                <td style="background-color:#F7F7F5;border:1px solid #E3E3E3;border-radius:10px;padding:16px 20px;">
                  <p style="font-size:11px;color:#999999;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.8px;">O copiá este enlace en tu navegador</p>
                  <a href="{$linkSafe}" style="font-size:12px;color:#111111;word-break:break-all;text-decoration:none;">{$linkSafe}</a>
                </td>
              </tr>
            </table>

            <!-- Info box -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:36px;">
              <tr>
                <td style="background-color:#F7F7F5;border-left:3px solid #111111;border-radius:0 8px 8px 0;padding:16px 20px;">
                  <p style="font-size:13px;color:#666666;line-height:1.65;margin:0;">
                    Este enlace <strong style="color:#111111;font-weight:600;">expira en 1 hora</strong>.
                    Si no solicitaste restablecer tu contraseña, podés ignorar este email.
                    Tu contraseña no cambiará hasta que hagas clic en el enlace.
                  </p>
                </td>
              </tr>
            </table>

            <!-- Divider -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr><td style="border-top:1px solid #EBEBEB;font-size:0;">&nbsp;</td></tr>
            </table>

            <p style="font-size:13px;color:#999999;text-align:center;line-height:1.7;margin:0;">
              ¿Algún inconveniente? Escribinos a<br/>
              <a href="mailto:britechfsa@gmail.com" style="color:#111111;font-weight:600;text-decoration:none;">britechfsa@gmail.com</a>
            </p>

          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background-color:#111111;border-radius:0 0 16px 16px;padding:26px 48px;text-align:center;">
            <p style="font-family:Georgia,'Times New Roman',serif;font-size:14px;font-weight:700;color:#ffffff;margin:0 0 10px;letter-spacing:2px;">BRITECH</p>
            <p style="font-size:11px;color:#888888;line-height:1.7;margin:0;">
              Mensaje automático, por favor no respondas este email.<br/>
              <a href="#" style="color:#cccccc;text-decoration:none;">Politica de privacidad</a>
              &nbsp;&middot;&nbsp;
              <a href="#" style="color:#cccccc;text-decoration:none;">Terminos de uso</a>
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>

</body>
</html>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────

    private function getTemplateVerificacion($nombre, $urlVerificacion) {
        $nombreSafe = htmlspecialchars($nombre,          ENT_QUOTES, 'UTF-8');
        $linkSafe   = htmlspecialchars($urlVerificacion, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verifica tu cuenta - Britech</title>
</head>
<body style="margin:0;padding:0;background-color:#F0EFE9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#1a1a1a;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0EFE9;padding:40px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        <!-- HEADER -->
        <tr>
          <td style="background-color:#111111;border-radius:16px 16px 0 0;padding:32px 48px;text-align:center;">
            <span style="font-family:Georgia,'Times New Roman',serif;font-size:28px;font-weight:700;color:#ffffff;letter-spacing:2px;">BRITECH</span>
          </td>
        </tr>

        <!-- BODY -->
        <tr>
          <td style="background-color:#ffffff;padding:48px 48px 40px;">

            <div style="margin-bottom:24px;">
              <span style="display:inline-block;background-color:#111111;color:#ffffff;font-size:10px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;padding:5px 16px;border-radius:100px;">
                Verificacion de cuenta
              </span>
            </div>

            <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:30px;font-weight:700;color:#111111;line-height:1.2;margin:0 0 20px;letter-spacing:-0.5px;">
              Confirma tu<br/>direccion de email
            </h1>

            <p style="font-size:15px;color:#555555;line-height:1.8;margin:0 0 36px;">
              Hola, <strong style="color:#111111;font-weight:600;">{$nombreSafe}</strong>. Gracias por registrarte en Britech.<br/><br/>
              Para activar tu cuenta y comenzar a usar todos nuestros servicios,
              necesitamos confirmar que este email te pertenece.
            </p>

            <!-- Boton CTA -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr><td align="center">
                <a href="{$linkSafe}" style="display:inline-block;background-color:#111111;color:#ffffff;text-decoration:none;font-family:Georgia,'Times New Roman',serif;font-size:15px;font-weight:700;padding:16px 48px;border-radius:100px;">
                  Verificar mi cuenta
                </a>
              </td></tr>
            </table>

            <!-- Link fallback -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr>
                <td style="background-color:#F7F7F5;border:1px solid #E3E3E3;border-radius:10px;padding:16px 20px;">
                  <p style="font-size:11px;color:#999999;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.8px;">O copia este enlace en tu navegador</p>
                  <a href="{$linkSafe}" style="font-size:12px;color:#111111;word-break:break-all;text-decoration:none;">{$linkSafe}</a>
                </td>
              </tr>
            </table>

            <!-- Info box -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:36px;">
              <tr>
                <td style="background-color:#F7F7F5;border-left:3px solid #111111;border-radius:0 8px 8px 0;padding:16px 20px;">
                  <p style="font-size:13px;color:#666666;line-height:1.65;margin:0;">
                    Este enlace <strong style="color:#111111;font-weight:600;">expira en 1 hora</strong> desde que recibiste este mensaje.
                    Si no solicitaste esta cuenta, puedes ignorar este email.
                  </p>
                </td>
              </tr>
            </table>

            <!-- Divider -->
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:32px;">
              <tr><td style="border-top:1px solid #EBEBEB;font-size:0;">&nbsp;</td></tr>
            </table>

            <p style="font-size:13px;color:#999999;text-align:center;line-height:1.7;margin:0;">
              Algun inconveniente? Escribinos a<br/>
              <a href="mailto:britechfsa@gmail.com" style="color:#111111;font-weight:600;text-decoration:none;">britechfsa@gmail.com</a>
            </p>

          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background-color:#111111;border-radius:0 0 16px 16px;padding:26px 48px;text-align:center;">
            <p style="font-family:Georgia,'Times New Roman',serif;font-size:14px;font-weight:700;color:#ffffff;margin:0 0 10px;letter-spacing:2px;">BRITECH</p>
            <p style="font-size:11px;color:#888888;line-height:1.7;margin:0;">
              Mensaje automatico, por favor no respondas este email.<br/>
              <a href="#" style="color:#cccccc;text-decoration:none;">Politica de privacidad</a>
              &nbsp;&middot;&nbsp;
              <a href="#" style="color:#cccccc;text-decoration:none;">Terminos de uso</a>
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>

</body>
</html>
HTML;
    }
}