<?php
ini_set ( 'include_path', implode ( PATH_SEPARATOR, array (
		implode ( DIRECTORY_SEPARATOR, array (
				'..',
				'..',
				'..',
				'src',
				'mg' 
		) ),
		ini_get ( 'include_path' ) 
) ) );
require_once ('log4php/Logger.php');
require_once 'PAMI/Autoloader/Autoloader.php'; // Include PAMI autoloader.
\PAMI\Autoloader\Autoloader::register (); // Call autoloader register for PAMI autoloader.
                                          // use PAMI\Client\Impl\ClientImpl;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Listener\IEventListener;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Action\ListCommandsAction;
use PAMI\Message\Action\ListCategoriesAction;
use PAMI\Message\Action\CoreShowChannelsAction;
use PAMI\Message\Action\CoreSettingsAction;
use PAMI\Message\Action\CoreStatusAction;
use PAMI\Message\Action\StatusAction;
use PAMI\Message\Action\ReloadAction;
use PAMI\Message\Action\CommandAction;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\LogoffAction;
use PAMI\Message\Action\AbsoluteTimeoutAction;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\BridgeAction;
use PAMI\Message\Action\CreateConfigAction;
use PAMI\Message\Action\GetConfigAction;
use PAMI\Message\Action\GetConfigJSONAction;
use PAMI\Message\Action\AttendedTransferAction;
use PAMI\Message\Action\RedirectAction;
use PAMI\Message\Action\DAHDIShowChannelsAction;
use PAMI\Message\Action\DAHDIHangupAction;
use PAMI\Message\Action\DAHDIRestartAction;
use PAMI\Message\Action\DAHDIDialOffHookAction;
use PAMI\Message\Action\DAHDIDNDOnAction;
use PAMI\Message\Action\DAHDIDNDOffAction;
use PAMI\Message\Action\AgentsAction;
use PAMI\Message\Action\AgentLogoffAction;
use PAMI\Message\Action\MailboxStatusAction;
use PAMI\Message\Action\MailboxCountAction;
use PAMI\Message\Action\VoicemailUsersListAction;
use PAMI\Message\Action\PlayDTMFAction;
use PAMI\Message\Action\DBGetAction;
use PAMI\Message\Action\DBPutAction;
use PAMI\Message\Action\DBDelAction;
use PAMI\Message\Action\DBDelTreeAction;
use PAMI\Message\Action\GetVarAction;
use PAMI\Message\Action\SetVarAction;
use PAMI\Message\Action\PingAction;
use PAMI\Message\Action\ParkedCallsAction;
use PAMI\Message\Action\SIPQualifyPeerAction;
use PAMI\Message\Action\SIPShowPeerAction;
use PAMI\Message\Action\SIPPeersAction;
use PAMI\Message\Action\SIPShowRegistryAction;
use PAMI\Message\Action\SIPNotifyAction;
use PAMI\Message\Action\QueuesAction;
use PAMI\Message\Action\QueueStatusAction;
use PAMI\Message\Action\QueueSummaryAction;
use PAMI\Message\Action\QueuePauseAction;
use PAMI\Message\Action\QueueRemoveAction;
use PAMI\Message\Action\QueueUnpauseAction;
use PAMI\Message\Action\QueueLogAction;
use PAMI\Message\Action\QueuePenaltyAction;
use PAMI\Message\Action\QueueReloadAction;
use PAMI\Message\Action\QueueResetAction;
use PAMI\Message\Action\QueueRuleAction;
use PAMI\Message\Action\MonitorAction;
use PAMI\Message\Action\PauseMonitorAction;
use PAMI\Message\Action\UnpauseMonitorAction;
use PAMI\Message\Action\StopMonitorAction;
use PAMI\Message\Action\ExtensionStateAction;
use PAMI\Message\Action\JabberSendAction;
use PAMI\Message\Action\LocalOptimizeAwayAction;
use PAMI\Message\Action\ModuleCheckAction;
use PAMI\Message\Action\ModuleLoadAction;
use PAMI\Message\Action\ModuleUnloadAction;
use PAMI\Message\Action\ModuleReloadAction;
use PAMI\Message\Action\ShowDialPlanAction;
use PAMI\Message\Action\ParkAction;
use PAMI\Message\Action\MeetmeListAction;
use PAMI\Message\Action\MeetmeMuteAction;
use PAMI\Message\Action\MeetmeUnmuteAction;
use PAMI\Message\Action\EventsAction;
use PAMI\Message\Action\VGMSMSTxAction;
use PAMI\Message\Action\DongleSendSMSAction;
use PAMI\Message\Action\DongleShowDevicesAction;
use PAMI\Message\Action\DongleReloadAction;
use PAMI\Message\Action\DongleStartAction;
use PAMI\Message\Action\DongleRestartAction;
use PAMI\Message\Action\DongleStopAction;
use PAMI\Message\Action\DongleResetAction;
use PAMI\Message\Action\DongleSendUSSDAction;
use PAMI\Message\Action\DongleSendPDUAction;
use PAMI\Message\Response\ResponseMessage;
use PAMI\Message\Event\QueueParamsEvent;
use PAMI\Message\Event\QueueMemberEvent;
class AMIClass {
	private $a;
	private $familias = array (
			"DND",
			"CFNA",
			"CFIM",
			"CFB",
			"LMNT",
			"CFOLLOW1",
			"CFOLLOW2",
			"CFOLLOW3",
			"LKP" 
	);
	
	/**
	 * Obtiene los valores faltantes para el modelo extensions via una conexion AMI al servidor Asterisk
	 */
	public function getAMI() {
		$this->iniciarConexionAMI ();
		
		// Obtenemos las extensiones que se encuentran en el modelo extensions
		$dataProvider = new CActiveDataProvider ( 'Extensions', array (
				'pagination' => false 
		) );
		foreach ( $dataProvider->getData () as $model ) {
			
			$dispositivo = trim ( $model->numero );
			
			foreach ( $this->familias as $familia ) {
				
				$value = $this->databaseGet ( $familia, $dispositivo );
				
				// Como se trata de variables booleanas, si $value = YES, se debe guardar 1 en
				// base de datos, si no 0
				if (($familia == "DND") or ($familia == "LMNT") or ($familia == "LKP")) {
					
					if ($value == "YES") {
						$model->$familia = 1;
					} else {
						$model->$familia = 0;
					}
				} 

				else { // para el resto de las variables no booleanas
					
					if (strlen ( $value ) > 0) {
						// Se almacena el valor
						$model->$familia = $value;
					} else {
						// Se almacena "No definico"
						$model->$familia = "";
					}
				}
			}
			if (! $model->save ()) {
				Yii::app ()->user->setFlash ( 'error', "Se encontro un error a la hora de salvar unos de los valores en la BD: " . $model->getErrors () );
			}
		}
		$this->finalizarConexionAMI ();
	}
	
	/**
	 * En algunas versiones de asterisk, se presenta el problema de que si el servicio de asterisk esta
	 * recien iniciado, la respuesta a un comando de QueueStatusAction no es valida, y por esto el dashboard
	 * no funciona. El problema se arregla al enviar un comando de queue show.
	 * Se creo esta funcion para ejecutar ese comando justo antes de iniciar el dashboard y arreglar el problema.
	 */
	public function recargarColas(){
		$respuesta = $this->a->send ( new CommandAction('queue show'));
	}
	
	/**
	 * Obtiene los parametros de las colas y los agentes en el servidor Asterisk
	 * Esta funcion fue creada especificamente para el DashboardWeb, ya que permite
	 * obtener todos los parametros de las colas y agentes de la misma forma como lo
	 * hace la aplicacion Dashboard de escritorio.
	 *
	 * @return array donde cada elemento corresponde a una cola. Cada cola posee ademas
	 *         un array interno que indica los agentes de esa cola
	 */
	public function GetQueueInfo() {
		
			$parametrosCola = array ();
		
		/**
		 * La respuesta a un mensaje de QueueStatusAction retorna un array de
		 * objetos QueueParams y QueueMember.
		 * Con los objetos QueueParams
		 * podemos obtener las estadisticas de la cola, con el objeto QueueMember
		 * el estado de los agentes
		 */
		$respuesta = $this->a->send ( new QueueStatusAction () );
		
		if ($respuesta instanceof ResponseMessage) {
			
			if ($respuesta->isSuccess ()) {
				
				$event = $respuesta->getEvents ();
							
				foreach ( $event as $obj ) {
					
					// Es informacion de una cola
					if ($obj instanceof QueueParamsEvent) {
						$e = new elementosCola ();
						$e->__set ( 'nombreCola', $obj->getQueue () );
						$e->__set ( 'llamadasContestadas', $obj->getCompleted () );
						$e->__set ( 'llamadasAbandonadas', $obj->getAbandoned () );
						$e->__set ( 'llamadasRecibidas', $obj->getCompleted () + $obj->getAbandoned () );
						$e->__set ( 'asa', $obj->getHoldTime () );
						$e->__set ( 'contestadasEnNS', $obj->getKey ( 'completedinsl' ) );
						$e->__set ( 'ns', $obj->getServiceLevelPerf () );
						
						if ($e->__get ( 'llamadasRecibidas' ) == 0) {
							$e->__set ( 'porcentajeAbandonadas', 0 );
						} else {
							$pabandonadas = ($e->__get ( 'llamadasAbandonadas' ) * 100) / $e->__get ( 'llamadasRecibidas' );
							$e->__set ( 'porcentajeAbandonadas', round ( $pabandonadas, 2 ) );
						}
						
						$e->__set ( 'espera', $obj->getCalls () );
						$e->__set ( 'aht', gmdate ( "H:i:s", $obj->getKey ( 'talktime' ) ) );
						array_push ( $parametrosCola, $e );
						
					//Es informacion de un agente de una cola
					} elseif ($obj instanceof QueueMemberEvent)  {
						// Es informacion de un miembro perteneciente a una cola
						
						// Creamos un nuevo elemento agente
						$a = new agente ();
						
						// Lo cargamos con la informacion que retorna el objeto QueueMember
						$a->__set ( 'nombre', $obj->getKey ( 'name' ) );
						$a->__set ( 'llamadasRecibidas', $obj->getKey ( 'callstaken' ) );
						$a->__set ( 'estatus', $obj->getKey ( 'status' ) );
						$a->__set ( 'pausado', $obj->getKey ( 'paused' ) );
						
						// Este agente pertenece a una cola, por eso obtenemos el nombre
						// de la cola a la que pertenece y lo agregamos a ella
						$perteneceACola = $obj->getKey ( 'queue' );
						
						foreach ( $parametrosCola as $p ) {
							
							if ($p->__get ( 'nombreCola' ) == $perteneceACola) {
								
								$p->incluirAgente ( $a );
							}
						}
					} 
				}
			} else {
				Yii::app ()->user->setFlash ( 'error', "La respuesta a la consulta de QueueStatus no fue exitosa. Consulte al administrador del sistema para mas detalles" );
			}
		}
		
		return $parametrosCola;
	}
	
	/**
	 * Dado un conjunto de parametros los salva en el servidor AMI
	 */
	public function saveAMI($variables) {
		$departamento = $variables ['departamento'];
		
		// Se obtienen todas las extensiones pertenecientes al departamento
		// que viene especificado en $variables
		$extensiones = Extensions::model ()->findAll ( array (
				'condition' => "DEP = '$departamento'" 
		) );
		
		$this->iniciarConexionAMI ();
		
		$this->borrar_todo ( $extensiones );
		
		foreach ( $variables as $key => $value ) {
			
			if (in_array ( $key, $this->familias )) { // Si es una familia valida
				
				if (($key == "DND") or ($key == "LKP") or ($key == "LMNT")) { // Si es una de las booleanas
					foreach ( $value as $id ) {
						$extensiones = Extensions::model ()->findByPk ( $id );
						$this->databaseSet ( $key, trim ( $extensiones->numero ), "YES" ); // Colocamos en true los elementos
					}
				} else { // Si no es una de las booleanas
					foreach ( $value as $id => $numero ) {
						if (is_numeric ( $numero )) {
							$extensiones = Extensions::model ()->findByPk ( $id );
							$this->databaseSet ( $key, trim ( $extensiones->numero ), $numero );
						}
						
						if ((! is_numeric ( $numero )) && (! $numero == "")) {
							Yii::app ()->user->setFlash ( 'error', "Uno de los valores introducidos no tiene formato correcto: $numero" );
						}
					}
				}
			}
		}
		
		$this->finalizarConexionAMI ();
	}
	public function iniciarConexionAMI() {
		$options = array (
				'log4php.properties' => realpath ( __DIR__ ) . DIRECTORY_SEPARATOR . 'log4php.properties',
				'host' => Yii::app ()->params ['host'],
				'port' => Yii::app ()->params ['port'],
				'username' => Yii::app ()->params ['username'],
				'secret' => Yii::app ()->params ['secret'],
				'connect_timeout' => Yii::app ()->params ['connect_timeout'],
				'read_timeout' => Yii::app ()->params ['read_timeout'],
				'scheme' => Yii::app ()->params ['scheme'] 
		) // try tls://
;
		$this->a = new ClientImpl ( $options );
		
		try {
			$this->a->open ();
		} catch ( Exception $e ) {
			Yii::app ()->user->setFlash ( 'error', "Se detecto un problema al iniciar la conexion con el servidor: " . $e->getMessage () );
		}
	}
	public function finalizarConexionAMI() {
		$this->a->close ();
	}
	
	/**
	 * Dado un conjunto de extensiones, borra todas las familias
	 */
	private function borrar_todo($extensiones) {
		foreach ( $this->familias as $familia ) {
			foreach ( $extensiones as $model ) {
				$this->databaseErase ( $familia, $model->numero );
			}
		}
	}
	
	/**
	 * Dada una familia y llave, retorna el valor asociado.
	 * Equivalente al comando database get.
	 * 
	 * @param unknown $familia        	
	 * @param unknown $dispositivo        	
	 * @return Ambigous <string, NULL>
	 */
	private function databaseGet($familia, $dispositivo) {
		$respuesta = $this->a->send ( new DBGetAction ( $familia, $dispositivo ) );
		if ($respuesta instanceof ResponseMessage) {
			if ($respuesta->isSuccess ()) {
				$event = $respuesta->getEvents ();
				foreach ( $event as $value ) {
					if ($value instanceof EventMessage) {
						return $value->getKey ( 'val' );
					}
				}
			}
		}
	}
	
	/**
	 * Dada una familia y un dispositivo, borra la entrada del servidor asterisk.
	 * Equivalente al
	 * comando DB erase
	 * 
	 * @param unknown $familia        	
	 * @param unknown $dispositivo        	
	 */
	private function databaseErase($familia, $dispositivo) {
		$respuesta = $this->a->send ( new DBDelAction ( $familia, $dispositivo ) );
		if ($respuesta instanceof ResponseMessage) {
			if ($respuesta->isSuccess ()) {
				if ($respuesta->getMessage () == "Key deleted successfully") {
					return true;
				} else {
					Yii::app ()->user->setFlash ( 'error', "Ha ocurrido un problema al borrar un valor en el servidor: " . $respuesta->getMessage () );
				}
			}
		}
	}
	
	/**
	 * Dado un dispositivo una llave y su valor, los guarda en la BD de asterisk.
	 * Similar al comando
	 * Database put
	 * 
	 * @param unknown $familia        	
	 * @param unknown $dispositivo        	
	 * @param unknown $valor        	
	 * @throws Exception
	 * @return boolean
	 */
	private function databaseSet($familia, $dispositivo, $valor) {
		$respuesta = $this->a->send ( new DBPutAction ( $familia, $dispositivo, $valor ) );
		if ($respuesta instanceof ResponseMessage) {
			if ($respuesta->isSuccess ()) {
				if ($respuesta->getMessage () == "Updated database successfully") {
					return true;
				} else {
					Yii::app ()->user->setFlash ( 'error', "Ha ocurrido un problema al insertar un valor en el servidor: " . $respuesta->getMessage () );
				}
			}
		}
	}
}
?>
