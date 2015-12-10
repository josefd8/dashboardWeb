<?php

/**
 * Esta clase representa la informacion de una sola cola en el dashboard.
 * Guarda parametros como nombre de la cola, llamadas, llamadas abandonadas
 * y parametros de agentes (cuantos conectados, disponibles, pausados, etc)
 * @author Jose Fernandez
 *
 */
class elementosCola {
	private $nombreCola;
	private $llamadasRecibidas;
	private $llamadasContestadas;
	private $llamadasAbandonadas;
	private $asa;
	private $contestadasEnNS;
	private $ns;
	private $porcentajeAbandonadas;
	private $espera;
	private $aht;
	private $agentes = array ();
	public function __get($key) {
		if (isset ( $this->$key )) {
			return $this->$key;
		}
	}
	public function __set($key, $val) {
		$this->$key = $val;
	}
	public function incluirAgente($agente) {
		array_push ( $this->agentes, $agente );
	}
	
	/**
	 * Retorna la cantidad de agentes cuyo estado sea ocupado (status 2 - Device in use o status 3
	 * device busy)
	 */
	public function agentesOcupados() {
		$ocupados = 0;
		
		foreach ( $this->agentes as $agente ) {
			if (($agente->__get ( 'estatus' ) == 2) || ($agente->__get ( 'estatus' ) == 3)) {
				$ocupados ++;
			}
		}
		return $ocupados;
	}
	
	/**
	 * Retorna la cantidad de agentes disponibles.
	 * Los agentes disponbles se calculan
	 * como la cantidad de agentes con estatus 1 (Not in use), o status 6 (ringing) y
	 * que ademas no estan pausados (paused = false)
	 */
	public function agentesDisponibles() {
		$disponibles = 0;
		
		foreach ( $this->agentes as $agente ) {
			if (($agente->__get ( 'estatus' ) == '1') || ($agente->__get ( 'estatus' ) == '6')) {
				if ($agente->__get ( 'pausado' ) == '0') {
					$disponibles ++;
				}
			}
		}
		return $disponibles;
	}
	
	/**
	 * Obtiene la cantidad de agentes no disponibles.
	 * Un agente no disponible es aquel que tiene un status
	 * de 5 o se encuentra pausado.
	 */
	public function agentesNoDisponibles() {
		$noDisponibles = 0;
		foreach ( $this->agentes as $agente ) {
			if (($agente->__get ( 'pausado' ) == '1') || ($agente->__get ( 'estatus' ) == '5')) {
				$noDisponibles ++;
			}
		}
		return $noDisponibles;
	}
}

?>