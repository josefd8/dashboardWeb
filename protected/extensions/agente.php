<?php

/**
 * Esta clase guarda informacion de un agente (Nombre, llamadas recibidas
 * estatus y pausado)
 * @author Jose Fernandez
 *
 */
class agente {
	private $nombre;
	private $llamadasRecibidas;
	private $estatus;
	private $pausado;
	public function __get($key) {
		if (isset ( $this->$key )) {
			return $this->$key;
		}
	}
	public function __set($key, $val) {
		$this->$key = $val;
	}
}

?>