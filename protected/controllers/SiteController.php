<?php
class SiteController extends Controller {
	/**
	 * Declares class-based actions.
	 */
	public function actions() {
		return array (
				
				// captcha action renders the CAPTCHA image displayed on the contact page
				'captcha' => array (
						'class' => 'CCaptchaAction',
						'backColor' => 0xFFFFFF 
				),
				
				// page action renders "static" pages stored under 'protected/views/site/pages'
				// They can be accessed via: index.php?r=site/page&view=FileName
				'page' => array (
						'class' => 'CViewAction' 
				) 
		);
	}
	public function filters() {
		return array (
				array (
						'booster.filters.BoosterFilter - delete' 
				) 
		);
	}
	public function actionIndex() {
		$this->render ( 'index' );
	}
	
	/**
	 * Esta funcion es llamada via AJAX cada vez que un usuario modifica el texto del cintillo en el dashboard.
	 * Guarda el valor del nuevo texto introducido por el usuario en la base de datos.
	 */
	public function actionCambiarTextoCintillo() {
		try {
			
			$nuevo_texto = $_POST ['texto'];
			
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'texto_cintillo'";
			$s = Settings::model ()->find ( $criteria );
			$s->valor = $nuevo_texto;
			$s->save ();
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	public function enviarTextoCintillo($s) {
		try {
			
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'texto_cintillo'";
			$s = Settings::model ()->find ( $criteria );
			$texto = $s->valor;
			
			echo "event: cintillo\n";
			
			echo 'data: ' . $texto;
			echo "\n\n";
			
			ob_flush ();
			flush ();
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	public function enviarFoto($s) {
		try {
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'direccion_foto'";
			$s = Settings::model ()->find ( $criteria );
			$foto = $s->valor;
			
			echo "event: foto\n";
			echo 'data: ' . $foto;
			echo "\n\n";
			ob_flush ();
			flush ();
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	public function actionActualizarFoto() {
		try {
			$nuevaFoto = $_POST ['nombre'];
			$this->renderPartial ( 'logo', array (
					'nombreArchivo' => $nuevaFoto 
			) );
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	public function actionActualizarColasBD() {
		try {
			$colas = $_POST ['salida'];
			
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'colas_visibles'";
			$s = Settings::model ()->find ( $criteria );
			$s->valor = json_encode ( $colas );
			$s->save ( false );
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	public function enviarColas($s) {
		try {
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'colas_visibles'";
			$s = Settings::model ()->find ( $criteria );
			$colas = $s->valor;
			
			echo "event: colas\n";
			echo 'data: ' . $colas;
			echo "\n\n";
			ob_flush ();
			flush ();
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
	}
	
	/**
	 * Esta funcion es llamada al momento de iniciarse el dashboard.
	 * Lo que hace es crear la conexion con AMI,
	 * y generar consultas cada 3 segundos sobre las estadisticas de la cola. Una vez que se obtiene respuesta de
	 * estas estadisticas, se las envia al browser que inicio la consulta utilizando el metodo SSE o "Server Sent Events"
	 */
	public function actionSSERequest() {
		
		try {
			$a = new AMIClass ();
			$a->iniciarConexionAMI ();
			
			//En algunas versiones de asterisk, la respuesta al comando QueueStatus tiene un formato diferente
			//que solo se arregla si se envia un "queue show" primero. Para eso se realiza primero esta linea.
			$a->recargarColas();
			
			$s = new Settings ();
			
			date_default_timezone_set ( "America/New_York" );
			header ( "Content-Type: text/event-stream\n\n" );
						
		while ( 3 ) {
			
			// En el caso de que exista algun error registrado (lo que hice mediante el uso de los flash de Yii)
			// Entra en esta rutina y envia la causa del error al browser.
			// Esto es principalmente para la clase de AMI, que se programo desde un principio para registrar los errores
			// que ocurran de esta forma
			if (Yii::app ()->user->hasFlash ( 'error' )) {
				break;
			} else {
				
				$this->obtenerInfoColas ( $a,$s );
				$this->enviarTextoCintillo ( $s );
				$this->enviarFoto ( $s );
				session_write_close ();
				sleep ( 3 );
			}
		}
		
		// Si por alguna razon se sale de la rutina, cierra la conexion que tenia abierta con AMI
		$a->finalizarConexionAMI ();
		
		} catch (Exception $e) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
		}
		
		if (Yii::app ()->user->hasFlash ( 'error' )) {
			$this->mostrarError ();
		}
		
	}
	
	/**
	 * Realiza la consulta via AMI de los parametros de las colas
	 * 
	 * @param AMIClass $a        	
	 */
	public function obtenerInfoColas(AMIClass $a, Settings $s) {
		
		try {
			
		echo "event: ping\n";
		
		// Se obtienen los parametros de las colas del servidor y se guardan en una variable
		$parametrosColas = $a->GetQueueInfo ();
		
		//Se obtienen las colas de la BD (para asi saber cuales pintar y cuales no)
		$criteria = new CDbCriteria ();
		$criteria->condition = "clave = 'colas_visibles'";
		$s = Settings::model ()->find ( $criteria );
		$colas = json_decode($s->valor);
				
		// Se crea un arreglo llamado tabla que sera una matriz de 2 dimensiones, que contendra la informacion
		// y estadisticas de las colas
		$tabla = array ();
		
		foreach ( $parametrosColas as $pc ) {
			$temp = array ();			
			array_push ( $temp, $pc->__get ( 'nombreCola' ) );
			array_push ( $temp, $pc->__get ( 'llamadasRecibidas' ) );
			array_push ( $temp, $pc->__get ( 'llamadasContestadas' ) );
			array_push ( $temp, $pc->__get ( 'llamadasAbandonadas' ) );
			array_push ( $temp, $pc->__get ( 'asa' ) );
			array_push ( $temp, $pc->__get ( 'contestadasEnNS' ) );
			array_push ( $temp, $pc->__get ( 'ns' ) );
			array_push ( $temp, $pc->__get ( 'porcentajeAbandonadas' ) );
			array_push ( $temp, $pc->__get ( 'espera' ) );
			array_push ( $temp, $pc->__get ( 'aht' ) );
			array_push ( $temp, sizeof ( $pc->__get ( 'agentes' ) ) );
			array_push ( $temp, $pc->agentesOcupados () );
			array_push ( $temp, $pc->agentesDisponibles () );
			array_push ( $temp, $pc->agentesNoDisponibles () );
			array_push ( $temp, $this->seEncuentraEnBD($pc->__get ('nombreCola' ),$colas));			
			array_push ( $tabla, $temp );		
		}
		
		// Se pasa el arreglo de 2 dimensiones completo hacia el browser para que mediante javascript,
		// lo pinte
		echo 'data: ' . json_encode ( $tabla );
		echo "\n\n";
		
		ob_flush ();
		flush ();
		
		} catch (Exception $e) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );			
		}
		
	}
	
	/**
	 * Verifica que $cola exista en el arreglo colas. Si existe y esta como true, retorna true, si esta como
	 * false, retorna false. Si no se encuentra en el arreglo, retorna true
	 * @param unknown $cola cola a verificar
	 * @param unknown $colas arreglo de colas tomada de la BD
	 * @return boolean true si debe ser visible en el dashboard, false de lo contrario.
	 */
	public function seEncuentraEnBD($cola, $colas){
		
		try {
			
			if (sizeof($colas) == 0) {				
				return true;
			}
				
		$bandera = false;
	
		foreach ($colas as $c) {
					
			if ($c[0] == $cola) {
			
				if ($c[1] == "true") {
					return true;
				} else {
					return false;
				}
			
				$bandera = true;
			}
		}
				
		//no se encontro en el arreglo
		if ($bandera == false){
			return true;
		}
		
		} catch (Exception $e) {
			Yii::app ()->user->setFlash ( 'error', "Se encontro un error inesperado: " . $e->getMessage () );
			return false;
		}
		
	}
	
	public function mostrarError() {
		$error = "";
		foreach ( Yii::app ()->user->getFlashes () as $key => $message ) {
			$error = $message;
		}
		
		echo "event: error\n";
		echo 'data: ' . $error;
		echo "\n\n";
		ob_flush ();
		flush ();
	}
	
	public function actionguardarFoto() {
		$pic = $_FILES ['archivo'];
		$data = array (
				'success' => false 
		);
		
		// Validamos si la copio correctamente
		if (copy ( $pic ['tmp_name'], 'images/' . $pic ['name'] )) {
			$this->renderPartial ( 'logo', array (
					'nombreArchivo' => $pic ['name'] 
			) );
			
			// Guardamos el nuevo valor de la direccion de la foto en BD
			$criteria = new CDbCriteria ();
			$criteria->condition = "clave = 'direccion_foto'";
			$s = Settings::model ()->find ( $criteria );
			$s->valor = $pic ['name'];
			$s->save ();
		}
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		if ($error = Yii::app ()->errorHandler->error) {
			if (Yii::app ()->request->isAjaxRequest)
				echo $error ['message'];
			else
				$this->render ( 'error', $error );
		}
	}
	
	/**
	 * Displays the contact page
	 */
	public function actionContact() {
		$model = new ContactForm ();
		if (isset ( $_POST ['ContactForm'] )) {
			$model->attributes = $_POST ['ContactForm'];
			if ($model->validate ()) {
				$name = '=?UTF-8?B?' . base64_encode ( $model->name ) . '?=';
				$subject = '=?UTF-8?B?' . base64_encode ( $model->subject ) . '?=';
				$headers = "From: $name <{$model->email}>\r\n" . "Reply-To: {$model->email}\r\n" . "MIME-Version: 1.0\r\n" . "Content-Type: text/plain; charset=UTF-8";
				
				mail ( Yii::app ()->params ['adminEmail'], $subject, $model->body, $headers );
				Yii::app ()->user->setFlash ( 'contact', 'Thank you for contacting us. We will respond to you as soon as possible.' );
				$this->refresh ();
			}
		}
		$this->render ( 'contact', array (
				'model' => $model 
		) );
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin() {
		$model = new LoginForm ();
		
		// if it is ajax validation request
		if (isset ( $_POST ['ajax'] ) && $_POST ['ajax'] === 'login-form') {
			echo CActiveForm::validate ( $model );
			Yii::app ()->end ();
		}
		
		// collect user input data
		if (isset ( $_POST ['LoginForm'] )) {
			$model->attributes = $_POST ['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate () && $model->login ())
				$this->redirect ( Yii::app ()->user->returnUrl );
		}
		// display the login form
		$this->render ( 'login', array (
				'model' => $model 
		) );
	}
	
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app ()->user->logout ();
		$this->redirect ( Yii::app ()->homeUrl );
	}
}