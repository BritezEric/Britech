<?php
require_once __DIR__ . '/../config/db.php';

class ProductoModel {

    /**
     * Get all active products with their base retail price and stock
     */
    public static function getCatalogo(?int $categoriaId = null): array {
        $db = getDB();
        $where = "p.estado = 'activo'";
        $params = [];
        if ($categoriaId) {
            $where .= " AND p.categoria_id = :cat";
            $params[':cat'] = $categoriaId;
        }
        $sql = "
            SELECT p.producto_id, p.codigo, p.nombre, p.tipo, p.marca, p.modelo, p.foto,
                   c.nombre AS categoria, c.categoria_id,
                   COALESCE(i.stock_actual, 0) AS stock,
                   (SELECT pr.precio FROM precio pr
                    WHERE pr.producto_id = p.producto_id
                      AND pr.tipo_precio = 'minorista'
                    ORDER BY pr.cantidad_minima ASC LIMIT 1) AS precio_minorista
            FROM producto p
            LEFT JOIN categoria c ON c.categoria_id = p.categoria_id
            LEFT JOIN inventario i ON i.producto_id = p.producto_id
            WHERE $where
            ORDER BY p.nombre ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single product detail
     */
    public static function getById(int $id): ?array {
        $db = getDB();
        $sql = "
            SELECT p.*, c.nombre AS categoria,
                   COALESCE(i.stock_actual, 0) AS stock
            FROM producto p
            LEFT JOIN categoria c ON c.categoria_id = p.categoria_id
            LEFT JOIN inventario i ON i.producto_id = p.producto_id
            WHERE p.producto_id = :id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all prices for a product
     */
    public static function getPrecios(int $productoId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM precio
            WHERE producto_id = :id
            ORDER BY tipo_precio ASC, cantidad_minima ASC
        ");
        $stmt->execute([':id' => $productoId]);
        return $stmt->fetchAll();
    }

    /**
     * Get best price for a given tipo and quantity
     */
    public static function getPrecioOptimo(int $productoId, string $tipo, int $cantidad = 1): float {
        $db = getDB();
        // Best price = highest cantidad_minima that is <= $cantidad
        $stmt = $db->prepare("
            SELECT precio FROM precio
            WHERE producto_id = :id
              AND tipo_precio = :tipo
              AND cantidad_minima <= :qty
            ORDER BY cantidad_minima DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $productoId, ':tipo' => $tipo, ':qty' => $cantidad]);
        $row = $stmt->fetch();
        if ($row) return (float)$row['precio'];
        // Fallback: minimum price for that type
        $stmt2 = $db->prepare("
            SELECT precio FROM precio
            WHERE producto_id = :id AND tipo_precio = :tipo
            ORDER BY cantidad_minima ASC LIMIT 1
        ");
        $stmt2->execute([':id' => $productoId, ':tipo' => $tipo]);
        $row2 = $stmt2->fetch();
        return $row2 ? (float)$row2['precio'] : 0.0;
    }

    /**
     * Get all mayorista price tiers for a product
     */
    public static function getPreciosMayorista(int $productoId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT cantidad_minima, precio FROM precio
            WHERE producto_id = :id AND tipo_precio = 'mayorista'
            ORDER BY cantidad_minima ASC
        ");
        $stmt->execute([':id' => $productoId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all categories that have active products
     */
    public static function getCategorias(): array {
        $db = getDB();
        $stmt = $db->query("
            SELECT c.categoria_id, c.nombre, COUNT(p.producto_id) AS total
            FROM categoria c
            LEFT JOIN producto p ON p.categoria_id = c.categoria_id AND p.estado = 'activo'
            GROUP BY c.categoria_id, c.nombre
            HAVING total > 0
            ORDER BY c.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Decrease stock (for 'stock' type products)
     */
    public static function descontarStock(int $productoId, int $cantidad): bool {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE inventario SET stock_actual = stock_actual - :qty
            WHERE producto_id = :id AND stock_actual >= :qty
        ");
        $stmt->execute([':qty' => $cantidad, ':id' => $productoId]);
        return $stmt->rowCount() > 0;
    }
}
