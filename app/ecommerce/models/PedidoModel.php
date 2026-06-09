<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ProductoModel.php';

class PedidoModel {

    /**
     * Create pedido + detalles + optional envio
     * Returns ['ok'=>bool, 'pedido_id'=>int, 'msg'=>string]
     */
    public static function crear(int $usuarioId, array $items, array $envioData, string $tipoPrecio): array {
        $db = getDB();
        $db->beginTransaction();
        try {
            $total = 0;

            // Pre-validate and compute totals
            $lineas = [];
            foreach ($items as $item) {
                $prod = ProductoModel::getById((int)$item['producto_id']);
                if (!$prod) throw new Exception("Producto {$item['producto_id']} no encontrado.");
                $qty = (int)$item['cantidad'];
                if ($qty < 1) continue;

                // Check stock for 'stock' type
                if ($prod['tipo'] === 'stock' && $prod['stock'] < $qty) {
                    throw new Exception("Stock insuficiente para {$prod['nombre']}.");
                }

                $precio = ProductoModel::getPrecioOptimo($prod['producto_id'], $tipoPrecio, $qty);
                $subtotal = $precio * $qty;
                $total += $subtotal;
                $lineas[] = [
                    'producto_id' => $prod['producto_id'],
                    'tipo'        => $prod['tipo'],
                    'nombre'      => $prod['nombre'],
                    'cantidad'    => $qty,
                    'precio'      => $precio,
                ];
            }

            if (empty($lineas)) throw new Exception("El carrito está vacío.");

            // Insert pedido
            $stmt = $db->prepare("
                INSERT INTO pedido (usuario_id, estado, total, fecha)
                VALUES (:uid, 'pendiente', :total, NOW())
            ");
            $stmt->execute([':uid' => $usuarioId, ':total' => $total]);
            $pedidoId = (int)$db->lastInsertId();

            // Insert detalles + discount stock
            $stmtD = $db->prepare("
                INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio)
                VALUES (:pid, :prod, :qty, :precio)
            ");
            foreach ($lineas as $linea) {
                $stmtD->execute([
                    ':pid'    => $pedidoId,
                    ':prod'   => $linea['producto_id'],
                    ':qty'    => $linea['cantidad'],
                    ':precio' => $linea['precio'],
                ]);
                if ($linea['tipo'] === 'stock') {
                    ProductoModel::descontarStock($linea['producto_id'], $linea['cantidad']);
                }
            }

            // Insert envio
            $stmtE = $db->prepare("
                INSERT INTO envio (pedido_id, direccion, empresa, estado)
                VALUES (:pid, :dir, 'A definir', 'pendiente')
            ");
            $stmtE->execute([
                ':pid' => $pedidoId,
                ':dir' => trim("{$envioData['direccion']}, {$envioData['ciudad']}, {$envioData['provincia']}"),
            ]);

            // Insert pago simulado
            $stmtP = $db->prepare("
                INSERT INTO pago (pedido_id, metodo, estado, fecha)
                VALUES (:pid, :metodo, 'aprobado', NOW())
            ");
            $stmtP->execute([':pid' => $pedidoId, ':metodo' => $envioData['metodo_pago'] ?? 'Transferencia']);

            // Update pedido to pagado
            $db->prepare("UPDATE pedido SET estado='pagado' WHERE pedido_id=:pid")
               ->execute([':pid' => $pedidoId]);

            $db->commit();
            return ['ok' => true, 'pedido_id' => $pedidoId, 'total' => $total];
        } catch (Exception $e) {
            $db->rollBack();
            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    public static function getByUsuario(int $usuarioId): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, COUNT(pd.pedido_detalle_id) AS items
            FROM pedido p
            LEFT JOIN pedido_detalle pd ON pd.pedido_id = p.pedido_id
            WHERE p.usuario_id = :uid
            GROUP BY p.pedido_id
            ORDER BY p.fecha DESC
        ");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public static function getDetalle(int $pedidoId, int $usuarioId): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pedido WHERE pedido_id=:pid AND usuario_id=:uid");
        $stmt->execute([':pid' => $pedidoId, ':uid' => $usuarioId]);
        $pedido = $stmt->fetch();
        if (!$pedido) return null;
        $stmt2 = $db->prepare("
            SELECT pd.*, p.nombre, p.foto FROM pedido_detalle pd
            JOIN producto p ON p.producto_id = pd.producto_id
            WHERE pd.pedido_id = :pid
        ");
        $stmt2->execute([':pid' => $pedidoId]);
        $pedido['items'] = $stmt2->fetchAll();
        return $pedido;
    }
}
