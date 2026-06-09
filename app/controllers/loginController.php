<?php

	namespace app\controllers;
	use app\models\mainModel;

	class loginController extends mainModel{

	private function sanitizeInput($value){
		return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function validarFormato($filtro,$cadena){
		return preg_match('/^'.$filtro.'$/', $cadena) === 1;
	}

		/*----------  Controlador iniciar sesion  ----------*/
	public function iniciarSesionControlador(){

		$email=$this->sanitizeInput($_POST['login_email'] ?? '');
		$clave=$this->sanitizeInput($_POST['login_clave'] ?? '');

		# Verificando campos obligatorios #
		if($email=="" or $clave==""){
			echo '<article class="message is-danger">
			  <div class="message-body">
			    <strong>Ocurrió un error inesperado</strong><br>
			    No has llenado todos los campos que son obligatorios
			  </div>
			</article>';
		}else{

			# Verificando integridad de los datos #
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				echo '<article class="message is-danger">
				  <div class="message-body">
				    <strong>Ocurrió un error inesperado</strong><br>
				    El EMAIL no tiene un formato válido
				  </div>
				</article>';
			}else{

				# Verificando integridad de los datos #
				if(!$this->validarFormato("[a-zA-Z0-9$@.-]{7,100}",$clave)){
					echo '<article class="message is-danger">
					  <div class="message-body">
					    <strong>Ocurrió un error inesperado</strong><br>
					    La CLAVE no coincide con el formato solicitado
					  </div>
					</article>';
				}else{

					# Verificando usuario #
					$check_usuario=$this->conectar()->prepare("SELECT u.*, c.cliente_id, c.tipo_cliente FROM usuario u LEFT JOIN cliente c ON c.usuario_id = u.usuario_id WHERE u.usuario_email = :email LIMIT 1");
					$check_usuario->bindParam(':email', $email);
					$check_usuario->execute();

					if($check_usuario->rowCount()==1){

						$check_usuario=$check_usuario->fetch();
						$passwordMatch = password_verify($clave, $check_usuario['usuario_clave']) or $check_usuario['usuario_clave'] === $clave;

						if($check_usuario['usuario_email']==$email and $passwordMatch){
							if($check_usuario['email_verificado'] == 0){
								echo '<article class="message is-warning">
								  <div class="message-body">
								    <strong>Email no verificado</strong><br>
								    Debes verificar tu email antes de iniciar sesion.<br>
								    <a href="'.APP_URL.'reenviar-verificacion.php" style="color:#4ade80;text-decoration:underline">Reenviar email de verificación</a>
								  </div>
								</article>';
								return;
							}

							$_SESSION['id']=$check_usuario['usuario_id'];
							$_SESSION['nombre']=$check_usuario['usuario_nombre'];
							$_SESSION['apellido']=$check_usuario['usuario_apellido'];
							$_SESSION['usuario']=$check_usuario['usuario_usuario'];
							$_SESSION['foto']=$check_usuario['usuario_foto'];
							$_SESSION['caja']=$check_usuario['caja_id'];
							$_SESSION['rol']=$check_usuario['rol'];

							if(!empty($check_usuario['cliente_id'])){
								$_SESSION['cliente_id']=$check_usuario['cliente_id'];
								$_SESSION['tipo_cliente']=$check_usuario['tipo_cliente'] ?: 'minorista';
							}

$rol = $check_usuario['rol'];
								if($rol === 'admin' || $rol === 'vendedor'){
									$redirectUrl = APP_URL.'dashboard/';
								} else {
									$redirectUrl = APP_URL.'app/ecommerce/index.php';
								}

							if(headers_sent()){
								echo "<script> window.location.href='".$redirectUrl."'; </script>";
							}else{
								header("Location: ".$redirectUrl);
							}
						} else {
							echo '<article class="message is-danger">
							  <div class="message-body">
							    <strong>Ocurrió un error inesperado</strong><br>
							    Usuario o clave incorrectos
							  </div>
							</article>';
						}

					}else{
						echo '<article class="message is-danger">
						  <div class="message-body">
						    <strong>Ocurrió un error inesperado</strong><br>
						    Usuario o clave incorrectos
						  </div>
						</article>';
					}
				}
			}
		}
	}
/*----------  Controlador cerrar sesion  ----------*/
		public function cerrarSesionControlador(){

			session_destroy();

		    if(headers_sent()){
                echo "<script> window.location.href='".APP_URL."login/'; </script>";
            }else{
                header("Location: ".APP_URL."login/");
            }
		}

		/*----------  Controlador registrar usuario publico  ----------*/
		public function registrarUsuarioPublicoControlador(){

			$nombre=$this->sanitizeInput($_POST['usuario_nombre'] ?? '');
	    	$apellido=$this->sanitizeInput($_POST['usuario_apellido'] ?? '');
	    	$usuario=$this->sanitizeInput($_POST['usuario_usuario'] ?? '');
	    	$email=$this->sanitizeInput($_POST['usuario_email'] ?? '');
	    	$clave1=$this->sanitizeInput($_POST['usuario_clave_1'] ?? '');
	    	$clave2=$this->sanitizeInput($_POST['usuario_clave_2'] ?? '');

			# Verificando campos obligatorios #
			if($nombre=="" || $apellido=="" || $email=="" || $clave1=="" || $clave2==""){
					echo '<article class="message is-danger">
					  <div class="message-body">
					    <strong>Ocurrió un error inesperado</strong><br>
					    No has llenado todos los campos que son obligatorios
					  </div>
					</article>';

				return;
			}

		    # Verificando formato del email #
		    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				echo '<article class="message is-danger">
				  <div class="message-body">
				    <strong>Ocurrió un error inesperado</strong><br>
				    El EMAIL no tiene un formato válido
				  </div>
				</article>';
				return;
		    }

		    # Verificando formato de las claves #
		    if(!$this->validarFormato("[a-zA-Z0-9$@.-]{7,100}",$clave1) || !$this->validarFormato("[a-zA-Z0-9$@.-]{7,100}",$clave2)){
				echo '<article class="message is-danger">
				  <div class="message-body">
				    <strong>Ocurrió un error inesperado</strong><br>
				    Las CLAVES no coinciden con el formato solicitado
				  </div>
				</article>';
				return;
		    }

		    # Verificando que las claves coincidan #
		    if($clave1!=$clave2){
		    	echo '<article class="message is-danger">
				  <div class="message-body">
				    <strong>Ocurrió un error inesperado</strong><br>
				    Las CLAVES que acaba de ingresar no coinciden, por favor verifique e intente nuevamente
				  </div>
				</article>';
				return;
		    }

		    # Verificando email duplicado #
		    $check_email=$this->conectar()->prepare("SELECT usuario_email FROM usuario WHERE usuario_email = :email");
		    $check_email->bindParam(':email', $email);
		    $check_email->execute();
			if($check_email->rowCount()>0){
				echo '<article class="message is-danger">
				  <div class="message-body">
				    <strong>Ocurrió un error inesperado</strong><br>
				    El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente
				  </div>
				</article>';
				return;
			}

		    # Si no se proporcionó usuario, usar el email como usuario #
		    if($usuario==""){
		    	$usuario = $email;
		    }

		    	    # Preparando datos para registro #
	    $clave = password_hash($clave1, PASSWORD_DEFAULT);
	    $token = bin2hex(random_bytes(32));
	    $token_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

	    $conexion = $this->conectar();
	    $conexion->beginTransaction();

	    try {
	        $stmt = $conexion->prepare("INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_usuario, usuario_email, usuario_clave, usuario_foto, email_verificado, token_verificacion, token_expiracion) VALUES (:Nombre, :Apellido, :Usuario, :Email, :Clave, :Foto, 0, :Token, :Expiracion)");
	        $stmt->execute([
	            ':Nombre'      => $nombre,
	            ':Apellido'    => $apellido,
	            ':Usuario'     => $usuario,
	            ':Email'       => $email,
	            ':Clave'       => $clave,
	            ':Foto'        => '',
	            ':Token'       => $token,
	            ':Expiracion'  => $token_expiracion
	        ]);

	        $usuarioId = $conexion->lastInsertId();
	        $stmtCliente = $conexion->prepare("INSERT INTO cliente (usuario_id, tipo_cliente) VALUES (:UsuarioId, 'minorista')");
	        $stmtCliente->execute([':UsuarioId' => $usuarioId]);

	        require_once __DIR__ . '/../helpers/EmailHelper.php';
	        $emailHelper = new \EmailHelper();
	        $nombreCompleto = $nombre . ' ' . $apellido;
	        $emailEnviado = $emailHelper->enviarEmailVerificacion($email, $nombreCompleto, $token);

	        $conexion->commit();

	        if($emailEnviado){
	            echo '<article class="message is-success">
	              <div class="message-body">
	                <strong>¡Cuenta creada exitosamente!</strong><br>
	                Se envió un email de verificación a <strong>'.htmlspecialchars($email).'</strong>. Revisa tu bandeja e ingresa al enlace.
	              </div>
	            </article>';
	        } else {
	            echo '<article class="message is-warning">
	              <div class="message-body">
	                <strong>Cuenta creada, pero no se pudo enviar el email de verificación.</strong><br>
	                Intenta reenviar el token desde <a href="'.APP_URL.'reenviar-verificacion.php" style="color:#4ade80;text-decoration:underline">aquí</a>.
	              </div>
	            </article>';
	        }
	    } catch (\Exception $e) {
	        $conexion->rollBack();
	        echo '<article class="message is-danger">
	          <div class="message-body">
	            <strong>Ocurrió un error inesperado</strong><br>
	            No se pudo crear la cuenta, por favor intente nuevamente
	          </div>
	        </article>';
	    }
	}

	}