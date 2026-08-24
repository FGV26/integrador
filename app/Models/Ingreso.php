<?php
class Ingreso {
    private $id;
    private $cita_id;
    private $monto;
    private $fecha;

    // Getters y Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCitaId() {
        return $this->cita_id;
    }

    public function setCitaId($cita_id) {
        $this->cita_id = $cita_id;
    }

    public function getMonto() {
        return $this->monto;
    }

    public function setMonto($monto) {
        $this->monto = $monto;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }
}
?>
