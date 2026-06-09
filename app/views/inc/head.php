<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo APP_NAME; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/bulma.min.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/all.css">
<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/estilos.css">

<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/sweetalert2.min.css">
<?php if (strpos($_SERVER['REQUEST_URI'], '/login') !== false || strpos($_SERVER['REQUEST_URI'], '/register') !== false): ?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>app/views/css/stylesecurity.css">
<?php endif; ?>
<script src="<?php echo APP_URL; ?>app/views/js/sweetalert2.all.min.js" ></script>