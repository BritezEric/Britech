from pathlib import Path

path = Path(r'C:\laragon\www\VENTAS-main\app\controllers\loginController.php')
text = path.read_text(encoding='utf-8')
start_marker = '/*----------  Controlador iniciar sesion  ----------*/'
end_marker = '/*----------  Controlador cerrar sesion  ----------*/'
start = text.index(start_marker)
end = text.index(end_marker)
new_func = '''/*----------  Controlador iniciar sesion  ----------*/
	public function iniciarSesionControlador(){

		$email=$this->limpiarCadena($_POST['login_email']);
		$clave=$this->limpiarCadena($_POST['login_clave']);

		# Verificando campos obligatorios #
		if($email=="" or $clave==""){
			echo '<article class="message is-danger">\n			  <div class="message-body">\n			    <strong>Ocurrió un error inesperado</strong><br>\n			    No has llenado todos los campos que son obligatorios\n			  </div>\n			</article>';
		}else{

			# Verificando integridad de los datos #
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				echo '<article class="message is-danger">\n				  <div class="message-body">\n				    <strong>Ocurrió un error inesperado</strong><br>\n				    El EMAIL no tiene un formato válido\n				  </div>\n				</article>';
			}else{

				# Verificando integridad de los datos #
				if($this->verificarDatos("[a-zA-Z0-9$@.-]{7,100}",$clave)){
					echo '<article class="message is-danger">\n					  <div class="message-body">\n					    <strong>Ocurrió un error inesperado</strong><br>\n					    La CLAVE no coincide con el formato solicitado\n					  </div>\n					</article>';
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
								echo '<article class="message is-warning">\n								  <div class="message-body">\n								    <strong>Email no verificado</strong><br>\n								    Debes verificar tu email antes de iniciar sesión.<br>\n								    <a href="'.APP_URL.'reenviar-verificacion.php" style="color:#4ade80;text-decoration:underline">Reenviar email de verificación</a>\n								  </div>\n								</article>';
								return;
							}

							$_SESSION['id']=$check_usuario['usuario_id'];
							$_SESSION['nombre']=$check_usuario['usuario_nombre'];
							$_SESSION['apellido']=$check_usuario['usuario_apellido'];
							$_SESSION['usuario']=$check_usuario['usuario_usuario'];
							$_SESSION['foto']=$check_usuario['usuario_foto'];
							$_SESSION['caja']=$check_usuario['caja_id'];

							if(!empty($check_usuario['cliente_id'])){
								$_SESSION['cliente_id']=$check_usuario['cliente_id'];
								$_SESSION['tipo_cliente']=$check_usuario['tipo_cliente'] ?: 'minorista';
							}

							$redirectUrl = !empty($check_usuario['cliente_id']) ? APP_URL.'app/ecommerce/index.php' : APP_URL.'dashboard/';

							if(headers_sent()){
								echo "<script> window.location.href='".$redirectUrl."'; </script>";
							}else{
								header("Location: ".$redirectUrl);
							}
						} else {
							echo '<article class="message is-danger">\n							  <div class="message-body">\n							    <strong>Ocurrió un error inesperado</strong><br>\n							    Usuario o clave incorrectos\n							  </div>\n							</article>';
						}

					}else{
						echo '<article class="message is-danger">\n						  <div class="message-body">\n						    <strong>Ocurrió un error inesperado</strong><br>\n						    Usuario o clave incorrectos\n						  </div>\n						</article>';
					}
				}
			}
		}
	}
'''
new_text = text[:start] + new_func + text[end:]
path.write_text(new_text, encoding='utf-8')
print('patched')