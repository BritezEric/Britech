<?php

	namespace app\controllers;
	use app\models\mainModel;

	class productController extends mainModel{

	private function sanitizeInput($value){
		return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

		/*----------  Controlador registrar producto  ----------*/
		public function registrarProductoControlador(){

			# Almacenando datos#
		    $codigo=$this->sanitizeInput($_POST['producto_codigo'] ?? '');
		    $nombre=$this->sanitizeInput($_POST['producto_nombre'] ?? '');

		    $precio_compra=$this->sanitizeInput($_POST['producto_precio_compra'] ?? '');
		    $precio_venta=$this->sanitizeInput($_POST['producto_precio_venta'] ?? '');
		    $stock=$this->sanitizeInput($_POST['producto_stock'] ?? '');

		    $marca=$this->sanitizeInput($_POST['producto_marca'] ?? '');
		    $modelo=$this->sanitizeInput($_POST['producto_modelo'] ?? '');
		    $unidad=$this->sanitizeInput($_POST['producto_unidad'] ?? '');
		    $categoria=$this->sanitizeInput($_POST['producto_categoria'] ?? '');

		    # Verificando campos obligatorios #
            if($codigo=="" || $nombre=="" || $precio_compra=="" || $precio_venta=="" || $stock==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9- ]{1,77}",$codigo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El CODIGO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,100}",$nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9.]{1,25}",$precio_compra)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE COMPRA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9.]{1,25}",$precio_venta)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE VENTA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9]{1,22}",$stock)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El STOCK O EXISTENCIAS no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($marca!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$marca)){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La MARCA no coincide con el formato solicitado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }

		    if($modelo!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$modelo)){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El MODELO no coincide con el formato solicitado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }

		    # Comprobando presentacion del producto #
			if(!in_array($unidad, PRODUCTO_UNIDAD)){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La PRESENTACION DEL PRODUCTO no es correcta o no la ha seleccionado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Verificando categoria #
		    $sql = $this->conectar()->prepare("SELECT categoria_id FROM categoria WHERE categoria_id=:categoria");
		    $sql->bindValue(':categoria',$categoria);
		    $sql->execute();
		    $check_categoria = $sql;
		    if($check_categoria->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La categoría seleccionada no existe en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    # Verificando stock total o existencias #
            if($stock<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No puedes registrar un producto con stock o existencias en 0, debes de agregar al menos una unidad",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Comprobando precio de compra del producto #
            $precio_compra=number_format($precio_compra,MONEDA_DECIMALES,'.','');
            if($precio_compra<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE COMPRA no puede ser menor o igual a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Comprobando precio de venta del producto #
            $precio_venta=number_format($precio_venta,MONEDA_DECIMALES,'.','');
            if($precio_venta<=0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE VENTA no puede ser menor o igual a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Comprobando precio de compra y venta del producto #
			if($precio_compra>$precio_venta){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El precio de compra del producto no puede ser mayor al precio de venta",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Comprobando codigo de producto #
		    $sql = $this->conectar()->prepare("SELECT codigo FROM producto WHERE codigo=:codigo");
		    $sql->bindValue(':codigo',$codigo);
		    $sql->execute();
		    $check_codigo = $sql;
		    if($check_codigo->rowCount()>=1){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El código de producto que ha ingresado ya se encuentra registrado en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    # Comprobando nombre de producto #
		    $sql = $this->conectar()->prepare("SELECT nombre FROM producto WHERE codigo=:codigo AND nombre=:nombre");
		    $sql->bindValue(':codigo',$codigo);
		    $sql->bindValue(':nombre',$nombre);
		    $sql->execute();
		    $check_nombre = $sql;
		    if($check_nombre->rowCount()>=1){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"Ya existe un producto registrado con el mismo nombre y código de barras",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    # Directorios de imagenes #
			$img_dir='../views/productos/';

			# Comprobar si se selecciono una imagen #
    		if($_FILES['producto_foto']['name']!="" && $_FILES['producto_foto']['size']>0){

    			# Creando directorio #
		        if(!file_exists($img_dir)){
		            if(!mkdir($img_dir,0777)){
		            	$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error inesperado",
							"texto"=>"Error al crear el directorio",
							"icono"=>"error"
						];
						return json_encode($alerta);
		                exit();
		            } 
		        }

		        # Verificando formato de imagenes #
		        if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png"){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            exit();
		        }

		        # Verificando peso de imagen #
		        if(($_FILES['producto_foto']['size']/1024)>5120){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            exit();
		        }

		        # Nombre de la foto #
		        $foto=$codigo."_".rand(0,100);

		        # Extension de la imagen #
		        switch(mime_content_type($_FILES['producto_foto']['tmp_name'])){
		            case 'image/jpeg':
		                $foto=$foto.".jpg";
		            break;
		            case 'image/png':
		                $foto=$foto.".png";
		            break;
		        }

		        chmod($img_dir,0777);

		        # Moviendo imagen al directorio #
		        if(!move_uploaded_file($_FILES['producto_foto']['tmp_name'],$img_dir.$foto)){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"No podemos subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
		            exit();
		        }

    		}else{
    			$foto="";
    		}

$conexion=$this->conectar();

			try{
				$conexion->beginTransaction();

				$insert_producto=$conexion->prepare("INSERT INTO producto (codigo, nombre, tipo, marca, modelo, estado, foto, precio_base, categoria_id) VALUES (:Codigo, :Nombre, :Unidad, :Marca, :Modelo, :Estado, :Foto, :PrecioCompra, :Categoria)");
				$insert_producto->bindValue(':Codigo',$codigo);
				$insert_producto->bindValue(':Nombre',$nombre);
				$insert_producto->bindValue(':Unidad',$unidad);
				$insert_producto->bindValue(':Marca',$marca);
				$insert_producto->bindValue(':Modelo',$modelo);
				$insert_producto->bindValue(':Estado','Habilitado');
				$insert_producto->bindValue(':Foto',$foto);
				$insert_producto->bindValue(':PrecioCompra',$precio_compra);
				$insert_producto->bindValue(':Categoria',$categoria);
				$insert_producto->execute();

				$producto_id=$conexion->lastInsertId();

				$insert_inventario=$conexion->prepare("INSERT INTO inventario (producto_id, stock_actual) VALUES (:ProductoId, :Stock)");
				$insert_inventario->bindValue(':ProductoId',$producto_id);
				$insert_inventario->bindValue(':Stock',$stock);
				$insert_inventario->execute();

				$insert_precio_minorista=$conexion->prepare("INSERT INTO precio (producto_id, tipo_precio, cantidad_minima, precio) VALUES (:ProductoId, 'minorista', 1, :PrecioVenta)");
				$insert_precio_minorista->bindValue(':ProductoId',$producto_id);
				$insert_precio_minorista->bindValue(':PrecioVenta',$precio_venta);
				$insert_precio_minorista->execute();

				$insert_precio_mayorista=$conexion->prepare("INSERT INTO precio (producto_id, tipo_precio, cantidad_minima, precio) VALUES (:ProductoId, 'mayorista', 1, :PrecioVenta)");
				$insert_precio_mayorista->bindValue(':ProductoId',$producto_id);
				$insert_precio_mayorista->bindValue(':PrecioVenta',$precio_venta);
				$insert_precio_mayorista->execute();

				$conexion->commit();

				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Producto registrado",
					"texto"=>"El producto ".$nombre." se registro con exito",
					"icono"=>"success"
				];
			}catch(\Exception $e){
				if($conexion->inTransaction()){
					$conexion->rollBack();
				}

				if(is_file($img_dir.$foto)){
					chmod($img_dir.$foto,0777);
					unlink($img_dir.$foto);
				}

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar el producto, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador listar producto  ----------*/
		public function listarProductoControlador($pagina,$registros,$url,$busqueda,$categoria){

			$pagina=$this->sanitizeInput($pagina);
			$registros=$this->sanitizeInput($registros);
			$categoria=$this->sanitizeInput($categoria);

			$url=$this->sanitizeInput($url);
			if($categoria>0){
				$url=APP_URL.$url."/".$categoria."/";
			}else{
				$url=APP_URL.$url."/";
			}

			$busqueda=$this->sanitizeInput($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$campos="producto.producto_id,producto.codigo as producto_codigo,producto.nombre as producto_nombre,inventario.stock_actual as producto_stock_total,precio.precio as producto_precio_venta,producto.foto as producto_foto,categoria.nombre as categoria_nombre";

			if(isset($busqueda) && $busqueda!=""){

				$consulta_datos="SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN inventario ON producto.producto_id=inventario.producto_id INNER JOIN precio ON producto.producto_id=precio.producto_id AND precio.tipo_precio='minorista' AND precio.cantidad_minima=1 WHERE producto.codigo LIKE :busqueda OR producto.nombre LIKE :busqueda2 OR producto.marca LIKE :busqueda3 OR producto.modelo LIKE :busqueda4 ORDER BY producto.nombre ASC LIMIT $inicio,$registros";

				$consulta_total="SELECT COUNT(producto.producto_id) FROM producto INNER JOIN inventario ON producto.producto_id=inventario.producto_id WHERE producto.codigo LIKE :busqueda OR producto.nombre LIKE :busqueda2 OR producto.marca LIKE :busqueda3 OR producto.modelo LIKE :busqueda4";

				$stmtDatos = $this->conectar()->prepare($consulta_datos);
				$searchTerm = '%'.$busqueda.'%';
				$stmtDatos->bindValue(':busqueda',$searchTerm);
				$stmtDatos->bindValue(':busqueda2',$searchTerm);
				$stmtDatos->bindValue(':busqueda3',$searchTerm);
				$stmtDatos->bindValue(':busqueda4',$searchTerm);
				$stmtDatos->execute();
				$datos = $stmtDatos->fetchAll();

				$stmtTotal = $this->conectar()->prepare($consulta_total);
				$stmtTotal->bindValue(':busqueda',$searchTerm);
				$stmtTotal->bindValue(':busqueda2',$searchTerm);
				$stmtTotal->bindValue(':busqueda3',$searchTerm);
				$stmtTotal->bindValue(':busqueda4',$searchTerm);
				$stmtTotal->execute();
				$total = (int) $stmtTotal->fetchColumn();

			}elseif($categoria>0){

		        $consulta_datos="SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN inventario ON producto.producto_id=inventario.producto_id INNER JOIN precio ON producto.producto_id=precio.producto_id AND precio.tipo_precio='minorista' AND precio.cantidad_minima=1 WHERE producto.categoria_id=:categoria ORDER BY producto.nombre ASC LIMIT $inicio,$registros";

		        $consulta_total="SELECT COUNT(producto.producto_id) FROM producto INNER JOIN inventario ON producto.producto_id=inventario.producto_id WHERE categoria_id=:categoria";

		        $stmtDatos = $this->conectar()->prepare($consulta_datos);
		        $stmtDatos->bindValue(':categoria',$categoria);
		        $stmtDatos->execute();
		        $datos = $stmtDatos->fetchAll();

		        $stmtTotal = $this->conectar()->prepare($consulta_total);
		        $stmtTotal->bindValue(':categoria',$categoria);
		        $stmtTotal->execute();
		        $total = (int) $stmtTotal->fetchColumn();

		    }else{

				$consulta_datos="SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN inventario ON producto.producto_id=inventario.producto_id INNER JOIN precio ON producto.producto_id=precio.producto_id AND precio.tipo_precio='minorista' AND precio.cantidad_minima=1 ORDER BY producto.nombre ASC LIMIT $inicio,$registros";

				$stmtDatos = $this->conectar()->prepare($consulta_datos);
				$stmtDatos->execute();
				$datos = $stmtDatos->fetchAll();

				$stmtTotal = $this->conectar()->query("SELECT COUNT(producto.producto_id) FROM producto INNER JOIN inventario ON producto.producto_id=inventario.producto_id");
				$total = (int) $stmtTotal->fetchColumn();

			}


			$numeroPaginas =ceil($total/$registros);

		    if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
		            <article class="media pb-3 pt-3">
		                <figure class="media-left">
		                    <p class="image is-64x64">';
		                        if(is_file("./app/views/productos/".$rows['producto_foto'])){
		                            $tabla.='<img src="'.APP_URL.'app/views/productos/'.$rows['producto_foto'].'">';
		                        }else{
		                            $tabla.='<img src="'.APP_URL.'app/views/productos/default.png">';
		                        }
		            $tabla.='</p>
		                </figure>
		                <div class="media-content">
		                    <div class="content">
		                        <p>
		                            <strong>'.$contador.' - '.$rows['producto_nombre'].'</strong><br>
		                            <strong>CODIGO:</strong> '.$rows['producto_codigo'].', 
		                            <strong>PRECIO:</strong> $'.$rows['producto_precio_venta'].', 
		                            <strong>STOCK:</strong> '.$rows['producto_stock_total'].', 
		                            <strong>CATEGORIA:</strong> '.$rows['categoria_nombre'].'
		                        </p>
		                    </div>
		                    <div class="has-text-right">
		                        <a href="'.APP_URL.'productPhoto/'.$rows['producto_id'].'/" class="button is-info is-rounded is-small">
			                    	<i class="far fa-image fa-fw"></i>
			                    </a>

		                        <a href="'.APP_URL.'productUpdate/'.$rows['producto_id'].'/" class="button is-success is-rounded is-small">
		                        	<i class="fas fa-sync fa-fw"></i>
		                        </a>

		                        <form class="FormularioAjax is-inline-block" action="'.APP_URL.'app/ajax/productoAjax.php" method="POST" autocomplete="off" >

			                		<input type="hidden" name="modulo_producto" value="eliminar">
			                		<input type="hidden" name="producto_id" value="'.$rows['producto_id'].'">

			                    	<button type="submit" class="button is-danger is-rounded is-small">
			                    		<i class="far fa-trash-alt fa-fw"></i>
			                    	</button>
			                    </form>
		                    </div>
		                </div>
		            </article>


		            <hr>
		            ';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<p class="has-text-centered pb-6"><i class="far fa-hand-point-down fa-5x"></i></p>
			            <p class="has-text-centered">
			                <a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
			                    Haga clic acá para recargar el listado
			                </a>
			            </p>
					';
				}else{
					$tabla.='
						<p class="has-text-centered pb-6"><i class="far fa-grin-beam-sweat fa-5x"></i></p>
						<p class="has-text-centered">No hay productos registrados en esta categoría</p>
					';
				}
			}

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando productos <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}


		/*----------  Controlador eliminar producto  ----------*/
		public function eliminarProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $sql = $this->conectar()->prepare("SELECT * FROM producto WHERE producto_id=:id");
		    $sql->bindValue(':id',$id);
		    $sql->execute();
		    $datos = $sql;
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando ventas #
		    $sql = $this->conectar()->prepare("SELECT producto_id FROM venta_detalle WHERE producto_id=:id LIMIT 1");
		    $sql->bindValue(':id',$id);
		    $sql->execute();
		    $check_ventas = $sql;
		    if($check_ventas->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el producto del sistema ya que tiene ventas asociadas",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    $eliminarProducto=$this->eliminarRegistro("producto","producto_id",$id);

		    if($eliminarProducto->rowCount()==1){

		    	if(is_file("../views/productos/".$datos['producto_foto'])){
		            chmod("../views/productos/".$datos['producto_foto'],0777);
		            unlink("../views/productos/".$datos['producto_foto']);
		        }

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Producto eliminado",
					"texto"=>"El producto '".$datos['producto_nombre']."' ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar el producto '".$datos['producto_nombre']."' del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar producto  ----------*/
		public function actualizarProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $sql = $this->conectar()->prepare("SELECT * FROM producto WHERE producto_id=:id");
		    $sql->bindValue(':id',$id);
		    $sql->execute();
		    $datos = $sql;
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $codigo=$this->sanitizeInput($_POST['producto_codigo'] ?? '');
		    $nombre=$this->sanitizeInput($_POST['producto_nombre'] ?? '');

		    $precio_compra=$this->sanitizeInput($_POST['producto_precio_compra'] ?? '');
		    $precio_venta=$this->sanitizeInput($_POST['producto_precio_venta'] ?? '');
		    $stock=$this->sanitizeInput($_POST['producto_stock'] ?? '');

		    $marca=$this->sanitizeInput($_POST['producto_marca'] ?? '');
		    $modelo=$this->sanitizeInput($_POST['producto_modelo'] ?? '');
		    $unidad=$this->sanitizeInput($_POST['producto_unidad'] ?? '');
		    $categoria=$this->sanitizeInput($_POST['producto_categoria'] ?? '');

		    # Verificando campos obligatorios #
            if($codigo=="" || $nombre=="" || $precio_compra=="" || $precio_venta=="" || $stock==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9- ]{1,77}",$codigo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El CODIGO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,100}",$nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9.]{1,25}",$precio_compra)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE COMPRA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9.]{1,25}",$precio_venta)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE VENTA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[0-9]{1,22}",$stock)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El STOCK O EXISTENCIAS no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($marca!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$marca)){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La MARCA no coincide con el formato solicitado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }

		    if($modelo!=""){
		    	if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,30}",$modelo)){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El MODELO no coincide con el formato solicitado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }

		    # Comprobando presentacion del producto #
			if(!in_array($unidad, PRODUCTO_UNIDAD)){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La PRESENTACION DEL PRODUCTO no es correcta o no la ha seleccionado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Verificando categoria #
			if($datos['categoria_id']!=$categoria){
			    $sql = $this->conectar()->prepare("SELECT categoria_id FROM categoria WHERE categoria_id=:categoria");
			    $sql->bindValue(':categoria',$categoria);
			    $sql->execute();
			    $check_categoria = $sql;
			    if($check_categoria->rowCount()<=0){
			        $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La categoría seleccionada no existe en el sistema",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
			}

		    # Verificando stock total o existencias #
            if($stock<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No puedes registrar un producto con stock o existencias en 0, debes de agregar al menos una unidad",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Comprobando precio de compra del producto #
            $precio_compra=number_format($precio_compra,MONEDA_DECIMALES,'.','');
            if($precio_compra<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE COMPRA no puede ser menor o igual a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Comprobando precio de venta del producto #
            $precio_venta=number_format($precio_venta,MONEDA_DECIMALES,'.','');
            if($precio_venta<=0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE VENTA no puede ser menor o igual a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Comprobando precio de compra y venta del producto #
			if($precio_compra>$precio_venta){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El precio de compra del producto no puede ser mayor al precio de venta",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Comprobando codigo de producto #
			if($datos['producto_codigo']!=$codigo){
			    $sql = $this->conectar()->prepare("SELECT producto_codigo FROM producto WHERE producto_codigo=:codigo");
			    $sql->bindValue(':codigo',$codigo);
			    $sql->execute();
			    $check_codigo = $sql;
			    if($check_codigo->rowCount()>=1){
			        $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El código de producto que ha ingresado ya se encuentra registrado en el sistema",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
			}

		    # Comprobando nombre de producto #
		    if($datos['producto_nombre']!=$nombre){
			    $sql = $this->conectar()->prepare("SELECT producto_nombre FROM producto WHERE producto_codigo=:codigo AND producto_nombre=:nombre");
			    $sql->bindValue(':codigo',$codigo);
			    $sql->bindValue(':nombre',$nombre);
			    $sql->execute();
			    $check_nombre = $sql;
			    if($check_nombre->rowCount()>=1){
			        $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Ya existe un producto registrado con el mismo nombre y código de barras",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }


		    $producto_datos_up=[
				[
					"campo_nombre"=>"producto_codigo",
					"campo_marcador"=>":Codigo",
					"campo_valor"=>$codigo
				],
				[
					"campo_nombre"=>"producto_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$nombre
				],
				[
					"campo_nombre"=>"producto_stock_total",
					"campo_marcador"=>":Stock",
					"campo_valor"=>$stock
				],
				[
					"campo_nombre"=>"producto_tipo_unidad",
					"campo_marcador"=>":Unidad",
					"campo_valor"=>$unidad
				],
				[
					"campo_nombre"=>"producto_precio_compra",
					"campo_marcador"=>":PrecioCompra",
					"campo_valor"=>$precio_compra
				],
				[
					"campo_nombre"=>"producto_precio_venta",
					"campo_marcador"=>":PrecioVenta",
					"campo_valor"=>$precio_venta
				],
				[
					"campo_nombre"=>"producto_marca",
					"campo_marcador"=>":Marca",
					"campo_valor"=>$marca
				],
				[
					"campo_nombre"=>"producto_modelo",
					"campo_marcador"=>":Modelo",
					"campo_valor"=>$modelo
				],
				[
					"campo_nombre"=>"categoria_id",
					"campo_marcador"=>":Categoria",
					"campo_valor"=>$categoria
				]
			];

			$condicion=[
				"condicion_campo"=>"producto_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("producto",$producto_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Producto actualizado",
					"texto"=>"Los datos del producto '".$datos['producto_nombre']."' se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos del producto '".$datos['producto_nombre']."', por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador eliminar foto producto  ----------*/
		public function eliminarFotoProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $sql = $this->conectar()->prepare("SELECT * FROM producto WHERE producto_id=:id");
		    $sql->bindValue(':id',$id);
		    $sql->execute();
		    $datos = $sql;
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/productos/";

    		chmod($img_dir,0777);

    		if(is_file($img_dir.$datos['producto_foto'])){

		        chmod($img_dir.$datos['producto_foto'],0777);

		        if(!unlink($img_dir.$datos['producto_foto'])){
		            $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Error al intentar eliminar la foto del producto, por favor intente nuevamente",
						"icono"=>"error"
					];
					return json_encode($alerta);
		        	exit();
		        }
		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la foto del producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    $producto_datos_up=[
				[
					"campo_nombre"=>"producto_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>""
				]
			];

			$condicion=[
				"condicion_campo"=>"producto_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("producto",$producto_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"La foto del producto '".$datos['producto_nombre']."' se elimino correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"No hemos podido actualizar algunos datos del producto '".$datos['producto_nombre']."', sin embargo la foto ha sido eliminada correctamente",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador actualizar foto producto  ----------*/
		public function actualizarFotoProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $sql = $this->conectar()->prepare("SELECT * FROM producto WHERE producto_id=:id");
		    $sql->bindValue(':id',$id);
		    $sql->execute();
		    $datos = $sql;
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/productos/";

    		# Comprobar si se selecciono una imagen #
    		if($_FILES['producto_foto']['name']=="" && $_FILES['producto_foto']['size']<=0){
    			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No ha seleccionado una foto para el producto",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
    		}

    		# Creando directorio #
	        if(!file_exists($img_dir)){
	            if(!mkdir($img_dir,0777)){
	                $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Error al crear el directorio",
						"icono"=>"error"
					];
					return json_encode($alerta);
	                exit();
	            } 
	        }

	        # Verificando formato de imagenes #
	        if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png"){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Verificando peso de imagen #
	        if(($_FILES['producto_foto']['size']/1024)>5120){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado supera el peso permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Nombre de la foto #
	        if($datos['producto_foto']!=""){
		        $foto=explode(".", $datos['producto_foto']);
		        $foto=$foto[0];
	        }else{
	        	$foto=$datos['producto_codigo']."_".rand(0,100);
	        }
	        

	        # Extension de la imagen #
	        switch(mime_content_type($_FILES['producto_foto']['tmp_name'])){
	            case 'image/jpeg':
	                $foto=$foto.".jpg";
	            break;
	            case 'image/png':
	                $foto=$foto.".png";
	            break;
	        }

	        chmod($img_dir,0777);

	        # Moviendo imagen al directorio #
	        if(!move_uploaded_file($_FILES['producto_foto']['tmp_name'],$img_dir.$foto)){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos subir la imagen al sistema en este momento",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Eliminando imagen anterior #
	        if(is_file($img_dir.$datos['producto_foto']) && $datos['producto_foto']!=$foto){
		        chmod($img_dir.$datos['producto_foto'], 0777);
		        unlink($img_dir.$datos['producto_foto']);
		    }

		    $producto_datos_up=[
				[
					"campo_nombre"=>"producto_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				]
			];

			$condicion=[
				"condicion_campo"=>"producto_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("producto",$producto_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"La foto del producto '".$datos['producto_nombre']."' se actualizo correctamente",
					"icono"=>"success"
				];
			}else{

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"No hemos podido actualizar algunos datos del producto '".$datos['producto_nombre']."', sin embargo la foto ha sido actualizada",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}
	}