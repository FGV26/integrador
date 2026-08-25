<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/CitaDAO.php';
require_once dirname(__DIR__) . '/Repositories/IngresoDAO.php';
require_once dirname(__DIR__) . '/Repositories/PerdidaDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() != 'abogado') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$citaDAO = new CitaDAO();
$ingresoDAO = new IngresoDAO();
$perdidaDAO = new PerdidaDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citaId = isset($_POST['cita_id']) ? (int) $_POST['cita_id'] : 0;
    $abogadoId = (int) $_SESSION['usuario']->getId();
    $fechaActual = date('Y-m-d');
    $citaActual = $citaDAO->obtenerPorId($citaId);

    if (!$citaActual || (int) $citaActual->getAbogadoId() !== $abogadoId) {
        $_SESSION['mensaje'] = 'No tienes permiso para modificar esta cita.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: ../Abogado/Citas.php');
        exit();
    }

    if (isset($_POST['aceptar'])) {
        $resultado = $citaDAO->confirmarCita($citaId);
        $_SESSION['mensaje'] = $resultado ? 'La cita fue aceptada correctamente.' : 'No se pudo aceptar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    } elseif (isset($_POST['iniciar'])) {
        $resultado = $citaDAO->iniciarAtencion($citaId, $abogadoId);
        $_SESSION['mensaje'] = $resultado ? 'La atencion fue iniciada. El tiempo estimado es de 40 minutos.' : 'No se pudo iniciar la atencion.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/" . ($resultado ? "AtencionCita.php" : "DetalleCita.php") . "?id=$citaId");
        exit();
    } elseif (isset($_POST['guardar_nota'])) {
        $nota = trim((string) ($_POST['nota_abogado'] ?? ''));
        $resultado = $citaDAO->agregarNotaAbogado($citaId, $abogadoId, $nota);
        $_SESSION['mensaje'] = $resultado ? 'La nota previa fue guardada correctamente.' : 'Escribe una nota valida antes de guardar.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/AtencionCita.php?id=$citaId");
        exit();
    } elseif (isset($_POST['finalizar_atencion'])) {
        $observacion = trim((string) ($_POST['observacion_final'] ?? ''));
        $requiereNuevaCita = isset($_POST['requiere_nueva_cita']);
        $requiereCambioEspecialidad = isset($_POST['requiere_cambio_especialidad']);
        $resultado = $citaDAO->finalizarAtencion($citaId, $abogadoId, $observacion, $requiereNuevaCita, $requiereCambioEspecialidad);
        if ($resultado) {
            $ingreso = new Ingreso();
            $ingreso->setCitaId($citaId);
            $montoIngreso = rand(100, 250);
            $ingreso->setMonto($montoIngreso);
            $ingreso->setFecha($fechaActual);
            $ingresoDAO->crear($ingreso);
        }
        $_SESSION['mensaje'] = $resultado ? 'La atencion fue finalizada correctamente.' : 'No se pudo finalizar la atencion.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/" . ($resultado ? "Citas.php" : "AtencionCita.php?id=$citaId"));
        exit();
    } elseif (isset($_POST['cancelar'])) {
        $motivo = trim((string) ($_POST['motivo_cancelacion'] ?? 'Cancelacion desde el panel del abogado'));
        $contrasenaConfirmacion = (string) ($_POST['contrasena_confirmacion'] ?? '');
        if ($motivo === '') {
            $_SESSION['mensaje'] = 'Ingresa el motivo para rechazar o cancelar la cita.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        if (!password_verify($contrasenaConfirmacion, $_SESSION['usuario']->getContrasena())) {
            $_SESSION['mensaje'] = 'La contrasena de validacion es incorrecta.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        $resultado = $citaDAO->cancelarCitaPorAbogado($citaId, $motivo);
        if ($resultado) {
            $citaDAO->crearAvisoCliente(
                (int) $citaActual->getClienteId(),
                $citaId,
                'cancelacion',
                'Tu cita fue cancelada',
                'Motivo: ' . $motivo
            );
            $perdida = new Perdida();
            $perdida->setCitaId($citaId);
            $montoPerdida = rand(100, 250);
            $perdida->setMonto($montoPerdida);
            $perdida->setFecha($fechaActual); 
            $perdidaDAO->crear($perdida);
        }
        $_SESSION['mensaje'] = $resultado ? 'La cita fue cancelada correctamente.' : 'No se pudo cancelar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    } elseif (isset($_POST['reajustar'])) {
        $fecha = trim((string) ($_POST['nueva_fecha'] ?? ''));
        $hora = trim((string) ($_POST['nueva_hora'] ?? ''));
        $motivo = trim((string) ($_POST['motivo_reajuste'] ?? ''));
        $contrasenaConfirmacion = (string) ($_POST['contrasena_confirmacion'] ?? '');
        $horasPermitidas = ['09:30', '10:30', '11:30', '12:30', '14:00', '15:00', '16:00', '17:00'];
        $fechasPermitidas = [];
        for ($i = 1; $i <= 3; $i++) {
            $fechasPermitidas[] = (new DateTimeImmutable('+' . $i . ' day'))->format('Y-m-d');
        }

        if ($motivo === '') {
            $_SESSION['mensaje'] = 'Ingresa el motivo del reajuste.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !in_array($fecha, $fechasPermitidas, true)) {
            $_SESSION['mensaje'] = 'Selecciona uno de los tres dias disponibles para reajustar.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        if (!in_array($hora, $horasPermitidas, true)) {
            $_SESSION['mensaje'] = 'Selecciona un horario valido.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        if (!password_verify($contrasenaConfirmacion, $_SESSION['usuario']->getContrasena())) {
            $_SESSION['mensaje'] = 'La contrasena de validacion es incorrecta.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }
        if (!$citaDAO->horarioDisponible($fecha, $hora, $citaId)) {
            $_SESSION['mensaje'] = 'Ese horario ya esta ocupado. Selecciona otro.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header("Location: ../Abogado/DetalleCita.php?id=$citaId");
            exit();
        }

        $resultado = $citaDAO->reajustarCitaPorAbogado($citaId, $abogadoId, $fecha, $hora);
        if ($resultado) {
            $citaDAO->crearAvisoCliente(
                (int) $citaActual->getClienteId(),
                $citaId,
                'reajuste',
                'Tu cita fue reajustada',
                'Nueva fecha: ' . $fecha . ' a las ' . $hora . '. Motivo: ' . $motivo
            );
        }
        $_SESSION['mensaje'] = $resultado ? 'La cita fue reajustada correctamente.' : 'No se pudo reajustar la cita.';
        $_SESSION['mensaje_tipo'] = $resultado ? 'success' : 'danger';
        header("Location: ../Abogado/DetalleCita.php?id=$citaId");
        exit();
    }
}
?>
