<?php

require_once dirname(__DIR__) . '/app/Helpers/Conexion.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este importador solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

$files = array_slice($argv, 1);
if (!$files) {
    fwrite(STDERR, "Uso: php tools/importar_citas_historicas.php archivo1.csv [archivo2.csv ...]\n");
    exit(1);
}

$connection = (new Conexion())->getConnection();
$connection->set_charset('utf8mb4');
$timezone = new DateTimeZone('America/Lima');

$sourceTypes = [
    1 => 'Derecho Civil',
    2 => 'Derecho Familiar',
    3 => 'Derecho Penal',
    4 => 'Derecho Notarial',
];

$statusMap = [
    'atendida' => 'terminado',
    'cancelada' => 'cancelada',
    'confirmada' => 'confirmada',
    'pendiente' => 'pendiente',
    'solicitada' => 'pendiente',
];

function normalizeDate(?string $value, DateTimeZone $timezone): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    return (new DateTimeImmutable($value))->setTimezone($timezone)->format('Y-m-d H:i:s');
}

function getCaseTypeIds(mysqli $connection): array
{
    $result = $connection->query('SELECT id, tipo FROM tipos_de_caso');
    $types = [];
    while ($row = $result->fetch_assoc()) {
        $types[$row['tipo']] = (int) $row['id'];
    }
    return $types;
}

function getOrCreateHistoricalUser(mysqli $connection, string $role, int $sourceId): array
{
    $username = 'legacy_' . $role . '_' . $sourceId;
    $find = $connection->prepare('SELECT id, usuario FROM usuarios WHERE usuario = ? LIMIT 1');
    $find->bind_param('s', $username);
    $find->execute();
    $existing = $find->get_result()->fetch_assoc();
    if ($existing) {
        return ['id' => (int) $existing['id'], 'created' => false];
    }

    $name = $role === 'cliente' ? 'Cliente' : 'Abogado';
    $lastName = 'Historico ' . $sourceId;
    $email = 'legacy.' . $role . '.' . $sourceId . '@import.ortiz.local';
    $phone = '';
    $password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $image = 'default.png';
    $isActive = 0;
    $insert = $connection->prepare(
        'INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, correo, telefono, usuario, contrasena, rol, imagen, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $empty = '';
    $insert->bind_param('sssssssssi', $name, $lastName, $empty, $email, $phone, $username, $password, $role, $image, $isActive);
    $insert->execute();

    return ['id' => (int) $connection->insert_id, 'created' => true];
}

$caseTypeIds = getCaseTypeIds($connection);
$createdUsers = 0;
$insertedAppointments = 0;
$skippedAppointments = 0;
$processedRows = 0;
$clientCache = [];
$lawyerCache = [];

$connection->begin_transaction();

try {
    $duplicateCheck = $connection->prepare('SELECT id FROM citas WHERE referencia_externa = ? LIMIT 1');
    $insertAppointment = $connection->prepare(
        'INSERT INTO citas (
            cliente_id, abogado_id, fecha, hora, tipo_de_caso_id, mensaje, estado,
            fecha_solicitud, fecha_confirmacion, fecha_atencion, fecha_cancelacion,
            motivo_cancelacion, modalidad, canal_solicitud, es_primera_consulta,
            estado_origen, origen_datos, referencia_externa, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($files as $file) {
        if (!is_readable($file)) {
            throw new RuntimeException('No se puede leer el archivo: ' . $file);
        }

        $handle = fopen($file, 'rb');
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new RuntimeException('El CSV no contiene encabezados: ' . $file);
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                throw new RuntimeException('Fila con columnas invalidas en ' . $file);
            }

            $row = array_combine($headers, $values);
            $processedRows++;
            $sourceClientId = (int) $row['cliente_id'];
            $sourceLawyerId = (int) $row['abogado_id'];
            $sourceTypeId = (int) $row['tipo_de_caso_id'];
            $scheduledAt = normalizeDate($row['fecha_programada'], $timezone);
            $requestedAt = normalizeDate($row['fecha_solicitud'], $timezone);

            if (!$scheduledAt || !$requestedAt || !isset($sourceTypes[$sourceTypeId], $caseTypeIds[$sourceTypes[$sourceTypeId]])) {
                throw new RuntimeException('Fila sin fecha o especialidad valida en ' . $file);
            }

            $reference = 'backup_oa:' . hash('sha256', implode('|', [
                $sourceClientId,
                $sourceLawyerId,
                $sourceTypeId,
                $row['fecha_solicitud'],
                $row['fecha_programada'],
                $row['mensaje'],
            ]));

            $duplicateCheck->bind_param('s', $reference);
            $duplicateCheck->execute();
            if ($duplicateCheck->get_result()->fetch_assoc()) {
                $skippedAppointments++;
                continue;
            }

            if (!isset($clientCache[$sourceClientId])) {
                $user = getOrCreateHistoricalUser($connection, 'cliente', $sourceClientId);
                $clientCache[$sourceClientId] = $user['id'];
                $createdUsers += $user['created'] ? 1 : 0;
            }
            if (!isset($lawyerCache[$sourceLawyerId])) {
                $user = getOrCreateHistoricalUser($connection, 'abogado', $sourceLawyerId);
                $lawyerCache[$sourceLawyerId] = $user['id'];
                $createdUsers += $user['created'] ? 1 : 0;
            }

            $clientId = $clientCache[$sourceClientId];
            $lawyerId = $lawyerCache[$sourceLawyerId];
            $date = substr($scheduledAt, 0, 10);
            $time = substr($scheduledAt, 11, 8);
            $caseTypeId = $caseTypeIds[$sourceTypes[$sourceTypeId]];
            $message = trim($row['mensaje']);
            $sourceStatus = strtolower(trim($row['estado']));
            $status = $statusMap[$sourceStatus] ?? 'pendiente';
            $confirmedAt = normalizeDate($row['fecha_confirmacion'], $timezone);
            $attendedAt = normalizeDate($row['fecha_atencion'], $timezone);
            $cancelledAt = normalizeDate($row['fecha_cancelacion'], $timezone);
            $cancellationReason = trim($row['motivo_cancelacion']) ?: null;
            $modality = trim($row['modalidad']) ?: null;
            $channel = trim($row['canal_solicitud']) ?: null;
            $firstConsultation = strtolower(trim($row['es_primera_consulta'])) === 'true' ? 1 : 0;
            $origin = 'backup_ia_oa';

            $insertAppointment->bind_param(
                'iississsssssssissss',
                $clientId,
                $lawyerId,
                $date,
                $time,
                $caseTypeId,
                $message,
                $status,
                $requestedAt,
                $confirmedAt,
                $attendedAt,
                $cancelledAt,
                $cancellationReason,
                $modality,
                $channel,
                $firstConsultation,
                $sourceStatus,
                $origin,
                $reference,
                $requestedAt
            );
            $insertAppointment->execute();
            $insertedAppointments++;
        }

        fclose($handle);
    }

    $connection->commit();
} catch (Throwable $exception) {
    $connection->rollback();
    fwrite(STDERR, 'Importacion cancelada: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

echo json_encode([
    'filas_procesadas' => $processedRows,
    'citas_insertadas' => $insertedAppointments,
    'citas_omitidas_por_duplicado' => $skippedAppointments,
    'usuarios_historicos_creados' => $createdUsers,
    'clientes_historicos' => count($clientCache),
    'abogados_historicos' => count($lawyerCache),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
