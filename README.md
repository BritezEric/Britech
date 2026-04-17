# BRITECH — Sistema de Gestión + Tienda

## Estructura de carpetas

```
britech/
├── index.php            ← Entrada principal (redirige a tienda)
├── patch.sql            ← Ejecutar UNA VEZ en MySQL
├── .htaccess            ← Seguridad y URLs limpias
│
├── config/
│   └── db.php           ← Conexión PDO (editar usuario/pass/DB aquí)
│
├── api/
│   ├── productos.php    ← CRUD productos (GET/POST/PUT/DELETE)
│   └── storage.php      ← Almacenamiento JSON genérico
│
├── js/
│   └── api.js           ← Funciones JS compartidas (opcional incluir en vistas)
│
├── css/
│   └── style.css        ← Estilos globales compartidos
│
└── views/
    ├── tienda.html      ← Tienda pública (carga productos desde API)
    └── admin.html       ← Panel de gestión (inventario, ventas, envíos)
```

## Instalación

1. Copiar la carpeta `britech/` dentro de `htdocs/` (XAMPP) o `www/` (WAMP)
2. Crear la base de datos `brit` en MySQL (phpMyAdmin o consola)
3. Ejecutar `patch.sql` sobre la base de datos `brit`
4. Editar `config/db.php` si el usuario/contraseña no son `root`/`''`
5. Acceder a `http://localhost/britech/`

## Endpoints de la API

| Método | URL | Acción |
|--------|-----|--------|
| GET | `/api/productos.php` | Listar todos los productos |
| GET | `/api/productos.php?id=5` | Obtener un producto |
| POST | `/api/productos.php` | Crear producto |
| PUT | `/api/productos.php` | Editar producto |
| DELETE | `/api/productos.php` | Eliminar (soft delete) |
| GET | `/api/storage.php?entity=ventas` | Leer entidad de storage |
| POST | `/api/storage.php?entity=ventas` | Guardar entidad de storage |

## Notas

- La **tienda** (`views/tienda.html`) carga productos automáticamente desde la API al abrir.
- El **panel admin** (`views/admin.html`) gestiona inventario, ventas, envíos y combos.
- El carrito y favoritos de la tienda se persisten en MySQL via `storage.php`.
- Los precios de la tienda usan `precio_minorista` de cada producto.
