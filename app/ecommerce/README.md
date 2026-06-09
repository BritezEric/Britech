# BRITECH E-Commerce — Guía de instalación

## Requisitos
- PHP 8.0+
- MySQL 8.0+
- Servidor web (Apache/Nginx) con soporte para PHP
- XAMPP / WAMP / Laragon para desarrollo local

---

## Estructura del proyecto

```
/ecommerce
  /assets
    /css        → style.css (diseño completo)
    /js         → app.js (carrito, modal, búsqueda)
  /config
    db.php      → Conexión PDO a MySQL
    session.php → Helpers de sesión y autenticación
  /controllers
    auth.php    → Login / Registro / Logout
    checkout.php → Procesar pedido
  /models
    ProductoModel.php → Catálogo, precios, stock
    ClienteModel.php  → Registro, login
    PedidoModel.php   → Crear pedido, historial
  /views
    /partials
      header.php → Header reutilizable + carrito drawer
      footer.php → Footer
    login.php     → Formulario de login
    register.php  → Formulario de registro
    checkout.php  → Finalizar compra
    success.php   → Confirmación de pedido
    pedidos.php   → Historial de pedidos
  index.php       → Catálogo principal
  README.md       → Este archivo
```

---

## Configuración

### 1. Base de datos
Importar `britech.sql` en tu servidor MySQL:
```sql
-- En phpMyAdmin o terminal:
mysql -u root -p < britech.sql
```

### 2. Conexión
Editar `/ecommerce/config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'britech');
define('DB_USER', 'root');   // Tu usuario MySQL
define('DB_PASS', '');        // Tu contraseña MySQL
```

### 3. Servidor web
Colocar la carpeta `ecommerce/` dentro de `htdocs/` (XAMPP) o `www/` (WAMP).

Acceder desde: `http://localhost/ecommerce/`

### 4. Apache - URL paths
Si usás Apache, asegurate de que `mod_rewrite` esté activo.
El proyecto usa paths absolutos comenzando con `/ecommerce/`.

---

## Funcionalidades implementadas

### 👤 Autenticación
- Registro con tipo de cliente (minorista / mayorista)
- Login con `password_verify()` + soporte legacy (plain text)
- Logout
- CSRF token en formularios

### 💰 Precios dinámicos
- **Visitante / Minorista** → precio minorista base
- **Mayorista** → precio mayorista con descuentos por volumen
- Los precios por volumen se actualizan en tiempo real en el modal según la cantidad

### 📦 Catálogo
- Filtro por categoría (desde navbar)
- Filtro por rango de precio
- Filtro "solo disponibles"
- Ordenamiento (nombre, precio, stock)
- Búsqueda en tiempo real
- Distinción visual: productos en stock vs bajo pedido

### 🛒 Carrito
- Persistencia en `sessionStorage`
- Control de stock para productos tipo 'stock'
- Productos tipo 'pedido' sin límite de stock
- Precios mayoristas correctamente aplicados

### 🚀 Checkout
- Formulario de dirección de envío
- Selección de método de pago
- Pago simulado (sin pasarela real)
- Descuento de stock para productos tipo 'stock'
- Generación de pedido + pedido_detalle + envio + pago en una transacción

### 📋 Pedidos
- Historial con estado visual
- Vista de detalle por pedido

---

## Lógica de precios (detalle)

```sql
-- Ejemplo para un mayorista comprando 8 unidades:
SELECT precio FROM precio
WHERE producto_id = 1
  AND tipo_precio = 'mayorista'
  AND cantidad_minima <= 8
ORDER BY cantidad_minima DESC
LIMIT 1;
-- Devuelve: 17000 (la fila con cantidad_minima=1, ya que 8 < 10)

-- Si compra 12 unidades:
-- Devuelve: 15000 (fila cantidad_minima=10, mejor precio)
```

---

## Seguridad implementada
- Consultas preparadas (PDO) en todos los modelos
- CSRF tokens en formularios
- `password_hash()` / `password_verify()` para contraseñas
- `htmlspecialchars()` en todas las salidas
- Verificación de sesión en rutas protegidas
- Transacciones DB para operaciones críticas
