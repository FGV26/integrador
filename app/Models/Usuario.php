<?php
class Usuario {
    private $id;
    private $nombre;
    private $apellido_paterno;
    private $apellido_materno;
    private $correo;
    private $telefono;
    private $usuario;
    private $contrasena;
    private $rol;
    private $imagen;
    private $is_active = 1;
    private $creado_en;
    private $last_login_at;
    private $password_change_required = 0;
    private $estado_cliente = 'activo';
    

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

    public function getContrasena() {
        return $this->contrasena;
    }

    public function setContrasena($contrasena) {
        $this->contrasena = $contrasena;
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

    public function getIsActive() {
        return (bool) $this->is_active;
    }

    public function setIsActive($isActive) {
        $this->is_active = (int) ((bool) $isActive);
    }

    public function getCreadoEn() {
        return $this->creado_en;
    }

    public function setCreadoEn($creadoEn) {
        $this->creado_en = $creadoEn;
    }

    public function getLastLoginAt() {
        return $this->last_login_at;
    }

    public function setLastLoginAt($lastLoginAt) {
        $this->last_login_at = $lastLoginAt;
    }

    public function getPasswordChangeRequired() {
        return (bool) $this->password_change_required;
    }

    public function setPasswordChangeRequired($passwordChangeRequired) {
        $this->password_change_required = (int) ((bool) $passwordChangeRequired);
    }

    public function getEstadoCliente() {
        return $this->estado_cliente;
    }

    public function setEstadoCliente($estadoCliente) {
        $this->estado_cliente = $estadoCliente ?: 'activo';
    }
}
?>
