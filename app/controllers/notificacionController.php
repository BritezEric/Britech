<?php

	namespace app\controllers;

	use app\models\mainModel;
	use \PDO;

	class notificacionController extends mainModel{

		private function validarTipo($tipo){
			$tiposPermitidos = ['info', 'advertencia', 'error', 'exito'];
			return in_array($tipo, $tiposPermitidos, true) ? $tipo : 'info';
		}

		public function crearNotificacion($usuario_id, $titulo, $mensaje, $tipo = 'info'){
			$usuario_id = (int) $usuario_id;
			$titulo = trim((string) $titulo);
			$mensaje = trim((string) $mensaje);
			$tipo = $this->validarTipo((string) $tipo);

			$sql = $this->conectar()->prepare("INSERT INTO notificacion (usuario_id, titulo, mensaje, tipo, leida) VALUES (:usuario_id, :titulo, :mensaje, :tipo, 0)");
			$sql->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
			$sql->bindValue(':titulo', $titulo);
			$sql->bindValue(':mensaje', $mensaje);
			$sql->bindValue(':tipo', $tipo);
			$sql->execute();

			return $sql->rowCount() > 0;
		}

		public function obtenerNotificaciones($usuario_id){
			$usuario_id = (int) $usuario_id;

			$sql = $this->conectar()->prepare("SELECT * FROM notificacion WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC LIMIT 10");
			$sql->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
			$sql->execute();

			return $sql->fetchAll(PDO::FETCH_ASSOC);
		}

		public function contarNoLeidas($usuario_id){
			$usuario_id = (int) $usuario_id;

			$sql = $this->conectar()->prepare("SELECT COUNT(*) FROM notificacion WHERE usuario_id = :usuario_id AND leida = 0");
			$sql->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
			$sql->execute();

			return (int) $sql->fetchColumn();
		}

		public function marcarLeida($notificacion_id, $usuario_id){
			$notificacion_id = (int) $notificacion_id;
			$usuario_id = (int) $usuario_id;

			$sql = $this->conectar()->prepare("UPDATE notificacion SET leida = 1 WHERE notificacion_id = :notificacion_id AND usuario_id = :usuario_id");
			$sql->bindValue(':notificacion_id', $notificacion_id, PDO::PARAM_INT);
			$sql->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
			$sql->execute();

			return $sql->rowCount() > 0;
		}

		public function marcarTodasLeidas($usuario_id){
			$usuario_id = (int) $usuario_id;

			$sql = $this->conectar()->prepare("UPDATE notificacion SET leida = 1 WHERE usuario_id = :usuario_id AND leida = 0");
			$sql->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
			$sql->execute();

			return $sql->rowCount();
		}
	}
