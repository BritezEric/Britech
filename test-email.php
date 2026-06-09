<?php
/**
 * Script de prueba para verificar el envío de emails
 * Ejecutar desde navegador: http://localhost/VENTAS-main/test-email.php
 */

require_once 'app/helpers/EmailHelper.php';

$emailHelper = new EmailHelper();

$test_email = 'britechfsa@gmail.com';
$test_name = 'Usuario de Prueba';

// Token de prueba (simula verificación)
$test_token = bin2hex(random_bytes(16));

echo "<h1>Prueba de Envío de Email</h1>";
echo "<p>Enviando email de verificación a: <strong>$test_email</strong></p>";

$resultado = $emailHelper->enviarEmailVerificacion($test_email, $test_name, $test_token);

if ($resultado) {
    echo "<p style='color: green; font-weight: bold;'>✅ Email enviado exitosamente</p>";
    echo "<p>Revisa tu bandeja de entrada y spam.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Error al enviar email</p>";
    echo "<p>Revisa la configuración SMTP y los logs de error.</p>";
}

echo "<hr>";
echo "<h3>Configuración actual:</h3>";
echo "<ul>";
echo "<li>SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'No definido') . "</li>";
echo "<li>SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'No definido') . "</li>";
echo "<li>SMTP User: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'No definido') . "</li>";
echo "<li>From Email: " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'No definido') . "</li>";
echo "<li>Base URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "</li>";
echo "</ul>";

echo "<p><a href='" . (defined('APP_URL') ? APP_URL : '#') . "'>← Volver al sistema</a></p>";
?>