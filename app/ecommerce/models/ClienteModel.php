<?php
require_once __DIR__ . '/../config/db.php';

class ClienteModel {

    public static function registrar(array $data): array {
        $db = getDB();
        // Check email unique
        $chk = $db->prepare("SELECT usuario_id FROM usuario WHERE usuario_email = :email");
        $chk->execute([':email' => $data['email']]);
        if ($chk->fetch()) {
            return ['ok' => false, 'msg' => 'El email ya está registrado.'];
        }

        $db->beginTransaction();
        try {
            // Insert usuario
            $stmt = $db->prepare("
                INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_email, usuario_usuario, usuario_clave, rol, email_verificado)
                VALUES (:nombre, :apellido, :email, :usuario, :clave, 'vendedor', 1)
            ");
            $stmt->execute([
                ':nombre'   => $data['nombre'],
                ':apellido' => $data['apellido'],
                ':email'    => $data['email'],
                ':usuario'  => $data['email'],
                ':clave'    => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);
            $usuarioId = (int)$db->lastInsertId();

            // Insert cliente
            $stmt2 = $db->prepare("
                INSERT INTO cliente (usuario_id, tipo_cliente, tipo_documento, numero_documento, provincia, ciudad, direccion, telefono)
                VALUES (:uid, :tipo, :tdoc, :ndoc, :prov, :ciudad, :dir, :tel)
            ");
            $stmt2->execute([
                ':uid'    => $usuarioId,
                ':tipo'   => $data['tipo_cliente'] ?? 'minorista',
                ':tdoc'   => $data['tipo_documento'] ?? 'DNI',
                ':ndoc'   => $data['numero_documento'] ?? '',
                ':prov'   => $data['provincia'] ?? '',
                ':ciudad' => $data['ciudad'] ?? '',
                ':dir'    => $data['direccion'] ?? '',
                ':tel'    => $data['telefono'] ?? '',
            ]);
            $clienteId = (int)$db->lastInsertId();

            $db->commit();
            return ['ok' => true, 'usuario_id' => $usuarioId, 'cliente_id' => $clienteId];
        } catch (Exception $e) {
            $db->rollBack();
            return ['ok' => false, 'msg' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    public static function login(string $email, string $password): array {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.usuario_id, u.usuario_nombre, u.usuario_apellido, u.usuario_email, u.usuario_clave,
                   c.cliente_id, c.tipo_cliente
            FROM usuario u
            LEFT JOIN cliente c ON c.usuario_id = u.usuario_id
            WHERE u.usuario_email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['ok' => false, 'msg' => 'Email o contraseña incorrectos.'];
        }

        // Support both hashed and plain (legacy) passwords
        $valid = password_verify($password, $row['usuario_clave'])
                 || $row['usuario_clave'] === $password;

        if (!$valid) {
            return ['ok' => false, 'msg' => 'Email o contraseña incorrectos.'];
        }

        return [
            'ok'           => true,
            'usuario_id'   => $row['usuario_id'],
            'cliente_id'   => $row['cliente_id'],
            'nombre'       => $row['usuario_nombre'],
            'apellido'     => $row['usuario_apellido'],
            'email'        => $row['usuario_email'],
            'tipo_cliente' => $row['tipo_cliente'] ?? 'minorista',
        ];
    }

    public static function getByUsuarioId(int $usuarioId): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM cliente WHERE usuario_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetch() ?: null;
    }
}
