# 🛠️ GUÍA DE INSTALACIÓN — Britech (Sistema de Ventas e Inventario)

> **Entorno recomendado:** Laragon + phpMyAdmin 6 · PHP 8.4 · MySQL 8.4

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|---------------|
| Laragon | 6.x (Full) |
| PHP | 8.1 o superior |
| MySQL | 8.0 o superior |
| phpMyAdmin | 6.x (incluido en Laragon Full) |

---

## Paso 1 — Mover el proyecto a la carpeta `www` de Laragon

1. Descomprimí el archivo `Britech-main.zip` donde prefieras.
2. Renombrá la carpeta extraída de `Britech-main` a `Britech` (o el nombre que quieras usar en la URL).
3. Mové (o copiá) esa carpeta a:

```
C:\laragon\www\Britech
```

La estructura final debe quedar así:

```
C:\laragon\www\
└── Britech\
    ├── app\
    ├── config\
    ├── DB\
    │   └── britechjr.sql
    ├── public\
    ├── .htaccess
    └── index.php
```

---

## Paso 2 — Crear la base de datos en phpMyAdmin 6

1. Abrí Laragon y hacé clic en **Start All** para iniciar Apache y MySQL.
2. En el panel de Laragon, hacé clic en **Database** (o entrá desde el menú → **phpMyAdmin**).  
   La URL por defecto es: `http://localhost/phpmyadmin`

3. En phpMyAdmin, en el panel izquierdo hacé clic en **Nueva** (New).

4. En el campo **Nombre de la base de datos** escribí:
   ```
   britechjr
   ```
5. En el selector de cotejamiento (collation) elegí:
   ```
   utf8mb4_general_ci
   ```
6. Hacé clic en **Crear**.

---

## Paso 3 — Importar la base de datos

1. Con la base de datos `britechjr` seleccionada en el panel izquierdo, hacé clic en la pestaña **Importar**.
2. En la sección **Archivo a importar**, hacé clic en **Seleccionar archivo** y navegá hasta:
   ```
   C:\laragon\www\Britech\DB\britechjr.sql
   ```
3. Asegurate de que el formato sea **SQL** (se detecta automáticamente).
4. Hacé clic en el botón **Importar** al final de la página.
5. phpMyAdmin mostrará el mensaje: *Importación realizada correctamente* ✅

---

## Paso 4 — Configurar la conexión a la base de datos

Abrí el archivo `config/server.php` con tu editor de código favorito y verificá que los valores coincidan con tu instalación de Laragon:

```php
<?php

if(!defined('DB_SERVER')){
    define('DB_SERVER', 'localhost');   // Host de MySQL en Laragon
}

if(!defined('DB_NAME')){
    define('DB_NAME', 'britechjr');    // Nombre de la base de datos que creaste
}

if(!defined('DB_USER')){
    define('DB_USER', 'root');         // Usuario por defecto de Laragon
}

if(!defined('DB_PASS')){
    define('DB_PASS', '');             // Laragon no tiene contraseña por defecto
}
```

> 💡 Si cambiaste la contraseña de MySQL en Laragon, actualizá `DB_PASS` con la tuya.

---

## Paso 5 — Configurar la URL y datos de la aplicación

Abrí el archivo `config/app.php` y editá los siguientes valores:

```php
<?php

const APP_URL  = "http://localhost/Britech/";   // Debe coincidir con el nombre de tu carpeta en www
const APP_NAME = "Britech";                     // Nombre de tu empresa u organización

// Configuración de moneda
const MONEDA_SIMBOLO   = "$";
const MONEDA_NOMBRE    = "ARS";
const MONEDA_DECIMALES = "2";

// Zona horaria
date_default_timezone_set("America/Argentina/Buenos_Aires");
```

> ⚠️ **Importante:** `APP_URL` debe terminar con `/` y debe incluir el protocolo (`http://` o `https://`).

---

## Paso 6 — Configurar el correo electrónico (opcional)

Si querés usar el sistema de notificaciones por email, abrí `config/email_config.php` y configurá tus datos SMTP:

```php
define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_SECURE',     'tls');
define('SMTP_USERNAME',   'tu-correo@gmail.com');   // Tu correo de Gmail
define('SMTP_PASSWORD',   'xxxx xxxx xxxx xxxx');   // App Password de Gmail (no la contraseña normal)
define('SMTP_FROM_EMAIL', 'tu-correo@gmail.com');
define('SMTP_FROM_NAME',  'Britech');

define('BASE_URL', 'http://localhost/Britech/');
```

> 💡 Para generar un **App Password** en Gmail: Cuenta de Google → Seguridad → Verificación en 2 pasos → Contraseñas de aplicaciones.

---

## Paso 7 — Acceder al sistema

1. Asegurate de que Laragon esté corriendo (Apache + MySQL activos).
2. Abrí tu navegador y entrá a:
   ```
   http://localhost/Britech/
   ```

### Credenciales por defecto

| Campo    | Valor           |
|----------|-----------------|
| Usuario  | `Administrador` |
| Contraseña | `Administrador` |

> ⚠️ **Cambiá la contraseña por defecto** después del primer inicio de sesión.

---

## Solución de problemas comunes

| Problema | Solución |
|----------|----------|
| Página en blanco / error 500 | Verificá que `APP_URL` en `app.php` coincida exactamente con la ruta de tu carpeta en `www` |
| Error de conexión a BD | Revisá los datos en `config/server.php`, confirmá que MySQL esté activo en Laragon |
| Error al importar `.sql` | Asegurate de tener seleccionada la base de datos `britechjr` antes de importar |
| Módulo `mod_rewrite` desactivado | En Laragon → menú → Apache → `mod_rewrite` debe estar habilitado |
| Imágenes no cargan | Verificá que `APP_URL` tenga la barra `/` al final |

---

## Estructura de archivos relevantes

```
Britech/
├── config/
│   ├── app.php           ← URL y nombre de la aplicación
│   ├── server.php        ← Credenciales de base de datos
│   └── email_config.php  ← Configuración SMTP
├── DB/
│   └── britechjr.sql     ← Archivo de base de datos a importar
├── app/
│   ├── controllers/      ← Lógica de negocio
│   ├── models/           ← Modelos de datos
│   ├── views/            ← Vistas PHP
│   ├── ecommerce/        ← Módulo e-commerce
│   └── mayorista/        ← Módulo mayorista
└── .htaccess             ← Configuración de rutas (mod_rewrite)
```
