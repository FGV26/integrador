<?php
require_once dirname(__DIR__) . '/conexiones/conexion.php';
require_once dirname(__DIR__) . '/model/Cita.php';

class CitaDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function crear($cita)
    {
        $sql = "INSERT INTO citas (cliente_id, abogado_id, fecha, hora, tipo_de_caso_id, mensaje, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            echo "Error en la preparación de la declaración: " . $this->conn->error;
            return false;
        }

        $clienteId = $cita->getClienteId();
        $abogadoId = $cita->getAbogadoId();
        $fecha = $cita->getFecha();
        $hora = $cita->getHora();
        $tipoDeCasoId = $cita->getTipoDeCasoId();
        $mensaje = $cita->getMensaje();
        $estado = $cita->getEstado();

        $stmt->bind_param(
            'iisssss',
            $clienteId,
            $abogadoId,
            $fecha,
            $hora,
            $tipoDeCasoId,
            $mensaje,
            $estado
        );

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error en la ejecución de la declaración: " . $stmt->error;
            return false;
        }
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM citas";
        $result = $this->conn->query($sql);
        $citas = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cita = new Cita();
                $cita->setId($row['id']);
                $cita->setClienteId($row['cliente_id']);
                $cita->setAbogadoId($row['abogado_id']);
                $cita->setFecha($row['fecha']);
                $cita->setHora($row['hora']);
                $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
                $cita->setMensaje($row['mensaje']);
                $cita->setEstado($row['estado']);
                $citas[] = $cita;
            }
        }

        return $citas;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM citas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            return $cita;
        }

        return null;
    }

    public function actualizar($cita)
    {
        $sql = "UPDATE citas SET cliente_id = ?, abogado_id = ?, fecha = ?, hora = ?, tipo_de_caso_id = ?, mensaje = ?, estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iisssssi', $cita->getClienteId(), $cita->getAbogadoId(), $cita->getFecha(), $cita->getHora(), $cita->getTipoDeCasoId(), $cita->getMensaje(), $cita->getEstado(), $cita->getId());
        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM citas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function obtenerAbogados()
    {
        $sql = "SELECT * FROM usuarios WHERE rol = 'abogado'";
        $result = $this->conn->query($sql);
        $abogados = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $abogado = new Usuario();
                $abogado->setId($row['id']);
                $abogado->setNombre($row['nombre']);
                $abogado->setApellidoPaterno($row['apellido_paterno']);
                $abogado->setApellidoMaterno($row['apellido_materno']);
                $abogados[] = $abogado;
            }
        }

        return $abogados;
    }

    public function obtenerCitasPorFecha($fecha)
    {
        $sql = "SELECT hora FROM citas WHERE fecha = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $horas = [];
        while ($row = $result->fetch_assoc()) {
            $horas[] = $row['hora'];
        }

        return $horas;
    }

    public function obtenerCitasPorCliente($clienteId)
    {
        $sql = "SELECT * FROM citas WHERE cliente_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $clienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $citas = [];

        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }

    public function obtenerCitasPorAbogado($abogadoId)
    {
        $sql = "SELECT * FROM citas WHERE abogado_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $abogadoId);
        $stmt->execute();
        $result = $stmt->get_result();

        $citas = [];
        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }
}
