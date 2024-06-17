<?php
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function crear($usuario)
    {
        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, correo, telefono, usuario, contraseña, rol, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'sssssssss',
            $usuario->getNombre(),
            $usuario->getApellidoPaterno(),
            $usuario->getApellidoMaterno(),
            $usuario->getCorreo(),
            $usuario->getTelefono(),
            $usuario->getUsuario(),
            $usuario->getContraseña(),
            $usuario->getRol(),
            $usuario->getImagen()
        );
        return $stmt->execute();
    }

    public function actualizar($usuario)
    {
        $sql = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, correo = ?, telefono = ?, usuario = ?, contraseña = ?, rol = ?, imagen = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        $nombre = $usuario->getNombre();
        $apellidoPaterno = $usuario->getApellidoPaterno();
        $apellidoMaterno = $usuario->getApellidoMaterno();
        $correo = $usuario->getCorreo();
        $telefono = $usuario->getTelefono();
        $usuarioNombre = $usuario->getUsuario();
        $contraseña = $usuario->getContraseña();
        $rol = $usuario->getRol();
        $imagen = $usuario->getImagen();
        $id = $usuario->getId();

        $stmt->bind_param(
            'sssssssssi',
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $correo,
            $telefono,
            $usuarioNombre,
            $contraseña,
            $rol,
            $imagen,
            $id
        );

        return $stmt->execute();
    }

    public function obtenerPorRol($rol)
    {
        $sql = "SELECT * FROM usuarios WHERE rol = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $rol);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuario = new Usuario();
                $usuario->setId($row['id']);
                $usuario->setNombre($row['nombre']);
                $usuario->setApellidoPaterno($row['apellido_paterno']);
                $usuario->setApellidoMaterno($row['apellido_materno']);
                $usuario->setCorreo($row['correo']);
                $usuario->setTelefono($row['telefono']);
                $usuario->setUsuario($row['usuario']);
                $usuario->setContraseña($row['contraseña']);
                $usuario->setRol($row['rol']);
                $usuario->setImagen($row['imagen']);
                $usuarios[] = $usuario;
            }
        }

        return $usuarios;
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuarios WHERE rol = 'abogado'";
        $result = $this->conn->query($sql);
        $usuarios = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuario = new Usuario();
                $usuario->setId($row['id']);
                $usuario->setNombre($row['nombre']);
                $usuario->setApellidoPaterno($row['apellido_paterno']);
                $usuario->setApellidoMaterno($row['apellido_materno']);
                $usuario->setCorreo($row['correo']);
                $usuario->setTelefono($row['telefono']);
                $usuario->setUsuario($row['usuario']);
                $usuario->setContraseña($row['contraseña']);
                $usuario->setRol($row['rol']);
                $usuario->setImagen($row['imagen']);
                $usuarios[] = $usuario;
            }
        }

        return $usuarios;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $usuario = new Usuario();
            $usuario->setId($row['id']);
            $usuario->setNombre($row['nombre']);
            $usuario->setApellidoPaterno($row['apellido_paterno']);
            $usuario->setApellidoMaterno($row['apellido_materno']);
            $usuario->setCorreo($row['correo']);
            $usuario->setTelefono($row['telefono']);
            $usuario->setUsuario($row['usuario']);
            $usuario->setContraseña($row['contraseña']);
            $usuario->setRol($row['rol']);
            $usuario->setImagen($row['imagen']);
            return $usuario;
        }

        return null;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function autenticar($usuario, $contraseña)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($contraseña, $row['contraseña'])) {
                $usuario = new Usuario();
                $usuario->setId($row['id']);
                $usuario->setNombre($row['nombre']);
                $usuario->setApellidoPaterno($row['apellido_paterno']);
                $usuario->setApellidoMaterno($row['apellido_materno']);
                $usuario->setCorreo($row['correo']);
                $usuario->setTelefono($row['telefono']);
                $usuario->setUsuario($row['usuario']);
                $usuario->setContraseña($row['contraseña']);
                $usuario->setRol($row['rol']);
                $usuario->setImagen($row['imagen']);
                return $usuario;
            }
        }

        return null;
    }

    public function existeUsuario($usuario)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function existeCorreo($correo)
    {
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
?>
