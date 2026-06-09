<?php

	require_once "../../autoload.php";

	use app\controllers\notificacionController;

	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		header('Content-Type: application/json');
		echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
		exit();
	}

	$accion = $_POST['accion'] ?? '';
	$controller = new notificacionController();

	header('Content-Type: application/json');

	switch($accion){
		case 'crear':
			$usuario_id = $_POST['usuario_id'] ?? 0;
			$titulo = $_POST['titulo'] ?? '';
			$mensaje = $_POST['mensaje'] ?? '';
			$tipo = $_POST['tipo'] ?? 'info';

			echo json_encode([
				'ok' => $controller->crearNotificacion($usuario_id, $titulo, $mensaje, $tipo),
				'data' => [
					'usuario_id' => (int) $usuario_id,
					'titulo' => $titulo,
					'mensaje' => $mensaje,
					'tipo' => $tipo
				]
			]);
			break;

		case 'obtener':
			$usuario_id = $_POST['usuario_id'] ?? 0;
			echo json_encode([
				'ok' => true,
				'data' => $controller->obtenerNotificaciones($usuario_id)
			]);
			break;

		case 'contar':
			$usuario_id = $_POST['usuario_id'] ?? 0;
			echo json_encode([
				'ok' => true,
				'data' => $controller->contarNoLeidas($usuario_id)
			]);
			break;

		case 'marcar':
			$notificacion_id = $_POST['notificacion_id'] ?? 0;
			$usuario_id = $_POST['usuario_id'] ?? 0;
			echo json_encode([
				'ok' => $controller->marcarLeida($notificacion_id, $usuario_id),
				'data' => [
					'notificacion_id' => (int) $notificacion_id,
					'usuario_id' => (int) $usuario_id
				]
			]);
			break;

		case 'marcar_todas':
			$usuario_id = $_POST['usuario_id'] ?? 0;
			echo json_encode([
				'ok' => true,
				'data' => [
					'usuario_id' => (int) $usuario_id,
					'actualizadas' => $controller->marcarTodasLeidas($usuario_id)
				]
			]);
			break;

		default:
			echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
			break;
	}
