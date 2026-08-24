<?php
class Cita {
    private $id;
    private $cliente_id;
    private $abogado_id;
    private $fecha;
    private $hora;
    private $tipo_de_caso_id;
    private $mensaje;
    private $estado;

    // Getters y Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getClienteId() {
        return $this->cliente_id;
    }

    public function setClienteId($cliente_id) {
        $this->cliente_id = $cliente_id;
    }

    public function getAbogadoId() {
        return $this->abogado_id;
    }

    public function setAbogadoId($abogado_id) {
        $this->abogado_id = $abogado_id;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getHora() { 
        return $this->hora;
    }

    public function setHora($hora) { 
        $this->hora = $hora;
    }

    public function getTipoDeCasoId() {
        return $this->tipo_de_caso_id;
    }

    public function setTipoDeCasoId($tipo_de_caso_id) {
        $this->tipo_de_caso_id = $tipo_de_caso_id;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }
}
?>
