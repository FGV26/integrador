<?php
class Usuario {
    private $id;
    private $nombre;
    private $apellido_paterno;
    private $apellido_materno;
    private $correo;
    private $telefono;
    private $usuario;
    private $contraseña;
    private $rol;
    private $imagen;
    

    // Getters y Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getApellidoPaterno() {
        return $this->apellido_paterno;
    }

    public function setApellidoPaterno($apellido_paterno) {
        $this->apellido_paterno = $apellido_paterno;
    }

    public function getApellidoMaterno() {
        return $this->apellido_materno;
    }

    public function setApellidoMaterno($apellido_materno) {
        $this->apellido_materno = $apellido_materno;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function getContraseña() {
        return $this->contraseña;
    }

    public function setContraseña($contraseña) {
        $this->contraseña = $contraseña;
    }

    public function getRol() {
        return $this->rol;
    }

    public function setRol($rol) {
        $this->rol = $rol;
    }

    public function getImagen() {
        return $this->imagen;
    }

    public function setImagen($imagen) {
        $this->imagen = $imagen;
    }
}
?>
