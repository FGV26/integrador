<?php

class ValidadorGestionUsuario
{
    public static function validar(array $datos, UsuarioDAO $usuarioDAO, int $idActual = 0, bool $contrasenaObligatoria = true): array
    {
        $errores = [];
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidoPaterno = trim((string) ($datos['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string) ($datos['apellido_materno'] ?? ''));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $correo = trim((string) ($datos['correo'] ?? ''));
        $usuario = trim((string) ($datos['usuario'] ?? ''));
        $contrasena = (string) ($datos['contrasena'] ?? '');

        foreach ([
            'nombre' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
        ] as $campo => $valor) {
            if ($campo !== 'apellido_materno' && $valor === '') {
                $errores[$campo] = 'Este campo es obligatorio.';
                continue;
            }

            if ($valor !== '' && strlen($valor) > 40) {
                $errores[$campo] = 'Maximo 40 caracteres.';
            }
        }

        if (!preg_match('/^\d{9}$/', $telefono)) {
            $errores['telefono'] = 'Debe tener 9 digitos.';
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Ingresa un correo valido.';
        } elseif ($usuarioDAO->existeCorreoEnOtroId($correo, $idActual)) {
            $errores['correo'] = 'Este correo ya esta registrado.';
        }

        if ($usuario === '') {
            $errores['usuario'] = 'El usuario es obligatorio.';
        } elseif ($usuarioDAO->existeUsuarioEnOtroId($usuario, $idActual)) {
            $errores['usuario'] = 'Este usuario ya existe.';
        }

        if ($contrasenaObligatoria || $contrasena !== '') {
            if (strlen($contrasena) < 8 || !preg_match('/\d/', $contrasena)) {
                $errores['contrasena'] = 'Minimo 8 caracteres y al menos un numero.';
            }
        }

        return $errores;
    }
}
