<?php
// Configuración SMTP para envío de emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls o ssl
define('SMTP_USERNAME', 'britechfsa@gmail.com'); // Cambiar por tu correo
define('SMTP_PASSWORD', 'pmyp pfir ssky cmss'); // App Password de Gmail, NO la contraseña normal
define('SMTP_FROM_EMAIL', 'britechfsa@gmail.com');
define('SMTP_FROM_NAME', 'Britech');

// URL base para los links de verificación (cambiar según tu dominio)
define('BASE_URL', 'http://localhost/VENTAS-main/'); // Cambiar por tu URL real si usas dominio

// Configuración de la base de datos
// Estas constantes ya se definen en config/server.php, por eso no se vuelven a definir aquí.
?>
