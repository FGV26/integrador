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
    private $hora_inicio_at;
    private $hora_fin_at;
    private $observacion_final;
    private $requiere_nueva_cita = 0;
    private $requiere_cambio_especialidad = 0;

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

    public function getHoraInicioAt() {
        return $this->hora_inicio_at;
    }

    public function setHoraInicioAt($hora_inicio_at) {
        $this->hora_inicio_at = $hora_inicio_at;
    }

    public function getHoraFinAt() {
        return $this->hora_fin_at;
    }

    public function setHoraFinAt($hora_fin_at) {
        $this->hora_fin_at = $hora_fin_at;
    }

    public function getObservacionFinal() {
        return $this->observacion_final;
    }

    public function setObservacionFinal($observacion_final) {
        $this->observacion_final = $observacion_final;
    }

    public function getRequiereNuevaCita() {
        return $this->requiere_nueva_cita;
    }

    public function setRequiereNuevaCita($requiere_nueva_cita) {
        $this->requiere_nueva_cita = (int) $requiere_nueva_cita;
    }

    public function getRequiereCambioEspecialidad() {
        return $this->requiere_cambio_especialidad;
    }

    public function setRequiereCambioEspecialidad($requiere_cambio_especialidad) {
        $this->requiere_cambio_especialidad = (int) $requiere_cambio_especialidad;
    }
}
?>
