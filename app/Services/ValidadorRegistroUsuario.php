<?php

class ValidadorRegistroUsuario
{
    public static function validar(array $datos): array
    {
        $errores = [];

        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidoPaterno = trim((string) ($datos['apellido_paterno'] ?? ''));
        $correo = trim((string) ($datos['correo'] ?? ''));
        $usuario = trim((string) ($datos['usuario'] ?? ''));
        $contrasena = (string) ($datos['contrasena'] ?? '');

        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }

        if ($apellidoPaterno === '') {
            $errores['apellido_paterno'] = 'El apellido paterno es obligatorio.';
        }

        if ($correo === '') {
            $errores['correo'] = 'El correo es obligatorio.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'El correo no tiene un formato valido.';
        }

        if ($usuario === '') {
            $errores['usuario'] = 'El usuario es obligatorio.';
        }

        if ($contrasena === '') {
            $errores['contrasena'] = 'La contrasena es obligatoria.';
        }

        return $errores;
    }
}
