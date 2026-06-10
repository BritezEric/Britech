# 🖥️ Britech — Sistema de Ventas e Inventario

Sistema de punto de venta (POS), facturación e inventario desarrollado en **PHP + MySQL** siguiendo el patrón **MVC**, con interfaz construida sobre **Bulma CSS**.

---

## ✨ Características principales

- **POS / Facturación** — Registrá ventas al mostrador de forma ágil con búsqueda de productos en tiempo real.
- **Inventario** — Administrá productos, categorías, stock y alertas de bajo inventario.
- **Gestión de clientes** — Alta, baja, modificación y búsqueda de clientes con historial de compras.
- **Gestión de usuarios** — Roles y permisos para administradores y cajeros.
- **Módulo E-commerce** — Tienda online integrada para ventas al público (`app/ecommerce/`).
- **Módulo Mayorista** — Portal de pedidos para clientes mayoristas (`app/mayorista/`).
- **Caja** — Control de apertura y cierre de caja con resumen de movimientos.
- **Notificaciones** — Sistema de alertas internas y notificaciones por email (SMTP).
- **Reportes PDF** — Generación de comprobantes y reportes con código de barras (Code 128).
- **Búsqueda AJAX** — Búsqueda instantánea de productos y clientes sin recargar la página.

---

## 🛠️ Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.1+ |
| Base de datos | MySQL 8.0+ / MariaDB |
| ORM / Acceso a datos | PDO |
| Frontend | Bulma CSS, JavaScript (AJAX) |
| Servidor local | Laragon (Apache + MySQL) |
| Arquitectura | MVC (Model-View-Controller) |

---

## 🚀 Instalación rápida

Consultá el archivo **[INSTALLATION.md](INSTALLATION.md)** para la guía completa paso a paso.

**Resumen:**
1. Copiar la carpeta del proyecto a `C:\laragon\www\Britech\`
2. Crear la base de datos `britechjr` en phpMyAdmin 6
3. Importar `DB/britechjr.sql`
4. Configurar `config/server.php` y `config/app.php`
5. Acceder a `http://localhost/Britech/`

---

## 🔐 Acceso por defecto

| Campo | Valor |
|-------|-------|
| Usuario | `Administrador` |
| Contraseña | `Administrador` |

> ⚠️ Cambiá las credenciales por defecto después del primer acceso.

---

## 📁 Estructura del proyecto

```
Britech/
├── app/
│   ├── ajax/             # Endpoints AJAX (productos, clientes, ventas…)
│   ├── controllers/      # Controladores MVC
│   ├── ecommerce/        # Módulo tienda online
│   │   ├── config/
│   │   ├── controllers/
│   │   ├── models/
│   │   └── views/
│   ├── helpers/          # EmailHelper y utilidades
│   ├── mayorista/        # Módulo portal mayorista
│   ├── models/           # Modelos de datos principales
│   ├── pdf/              # Generación de PDFs y código de barras
│   └── views/            # Vistas del panel de administración
├── config/
│   ├── app.php           # URL base, nombre de la app, moneda, zona horaria
│   ├── server.php        # Credenciales de base de datos
│   └── email_config.php  # Configuración SMTP
├── DB/
│   └── britechjr.sql     # Dump de la base de datos
├── .htaccess             # Rewrite rules para URLs amigables
└── index.php             # Punto de entrada de la aplicación
```

---

## ⚙️ Configuración

### Base de datos (`config/server.php`)
```php
define('DB_SERVER', 'localhost');
define('DB_NAME',   'britechjr');
define('DB_USER',   'root');
define('DB_PASS',   '');
```

### Aplicación (`config/app.php`)
```php
const APP_URL  = "http://localhost/Britech/";
const APP_NAME = "Britech";
const MONEDA_SIMBOLO = "$";
const MONEDA_NOMBRE  = "ARS";
date_default_timezone_set("America/Argentina/Buenos_Aires");
```

---

## 📧 Configuración de email (opcional)

El sistema incluye notificaciones por email usando SMTP. Configurá `config/email_config.php` con tus credenciales de Gmail u otro proveedor SMTP. Para Gmail se requiere una **App Password** (contraseña de aplicación), no la contraseña normal de la cuenta.

---

## 📋 Requisitos del servidor

- PHP **8.1** o superior con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`
- MySQL **8.0** / MariaDB **10.4** o superior
- Apache con **mod_rewrite** habilitado
- Laragon Full (incluye todo lo necesario para desarrollo local)

---

## 📄 Licencia

Este proyecto es de uso libre para fines educativos y comerciales. Cualquier redistribución debe mantener la atribución original.
