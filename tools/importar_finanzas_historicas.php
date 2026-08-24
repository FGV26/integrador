<?php

require_once dirname(__DIR__) . '/app/Helpers/Conexion.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este importador solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

$baseDir = $argv[1] ?? dirname(__DIR__) . '/storage/imports/finanzas_2026';

if (!is_dir($baseDir)) {
    fwrite(STDERR, "No existe la carpeta de importacion: {$baseDir}\n");
    exit(1);
}

$connection = (new Conexion())->getConnection();
$connection->set_charset('utf8mb4');
$timezone = new DateTimeZone('America/Lima');

function normalizeImportDate(?string $value, DateTimeZone $timezone): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    return (new DateTimeImmutable($value))->setTimezone($timezone)->format('Y-m-d');
}

function readCsvRows(string $file): array
{
    if (!is_readable($file)) {
        throw new RuntimeException('No se puede leer el archivo: ' . $file);
    }

    $handle = fopen($file, 'rb');
    $headers = fgetcsv($handle);
    if (!$headers) {
        throw new RuntimeException('El CSV no contiene encabezados: ' . $file);
    }
    $headers = array_map(static function ($header): string {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
        return trim(trim($header), "\"'");
    }, $headers);

    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($headers)) {
            throw new RuntimeException('Fila con columnas invalidas en ' . $file);
        }
        $rows[] = array_combine($headers, $values);
    }

    fclose($handle);
    return $rows;
}

function appointmentReferenceFromRow(array $row): string
{
    return 'backup_oa:' . hash('sha256', implode('|', [
        (int) $row['cliente_id'],
        (int) $row['abogado_id'],
        (int) $row['tipo_de_caso_id'],
        $row['fecha_solicitud'],
        $row['fecha_programada'],
        $row['mensaje'],
    ]));
}

function buildAppointmentMap(mysqli $connection, string $baseDir): array
{
    $map = [];
    $findAppointment = $connection->prepare('SELECT id FROM citas WHERE referencia_externa = ? LIMIT 1');

    foreach (glob($baseDir . '/citas_*.csv') as $file) {
        if (!preg_match('/citas_(\d{4})_(\d{2})\.csv$/', str_replace('\\', '/', $file), $match)) {
            continue;
        }

        $prefix = $match[1] . '_' . $match[2];
        $index = 0;
        foreach (readCsvRows($file) as $row) {
            $index++;
            $backupCode = sprintf('%s-%03d', $prefix, $index);
            $externalReference = appointmentReferenceFromRow($row);

            $findAppointment->bind_param('s', $externalReference);
            $findAppointment->execute();
            $appointment = $findAppointment->get_result()->fetch_assoc();

            if ($appointment) {
                $map[$backupCode] = (int) $appointment['id'];
            }
        }
    }

    return $map;
}

function importFinanceFile(
    mysqli $connection,
    string $file,
    array $appointmentMap,
    DateTimeZone $timezone,
    string $table,
    string $amountColumn,
    string $dateColumn
): array {
    $exists = $connection->prepare("SELECT id FROM {$table} WHERE cita_id = ? LIMIT 1");
    $insert = $connection->prepare("INSERT INTO {$table} (cita_id, monto, fecha) VALUES (?, ?, ?)");
    $processed = 0;
    $inserted = 0;
    $skipped = 0;
    $missing = [];

    foreach (readCsvRows($file) as $row) {
        $processed++;
        foreach (['referencia_cita_mes', $amountColumn, $dateColumn] as $requiredColumn) {
            if (!array_key_exists($requiredColumn, $row)) {
                throw new RuntimeException('Falta la columna ' . $requiredColumn . ' en ' . $file);
            }
        }

        $backupCode = trim((string) $row['referencia_cita_mes']);

        if (!isset($appointmentMap[$backupCode])) {
            $missing[] = $backupCode;
            continue;
        }

        $appointmentId = $appointmentMap[$backupCode];
        $exists->bind_param('i', $appointmentId);
        $exists->execute();

        if ($exists->get_result()->fetch_assoc()) {
            $skipped++;
            continue;
        }

        $amount = (float) $row[$amountColumn];
        $date = normalizeImportDate($row[$dateColumn], $timezone);
        if (!$date) {
            throw new RuntimeException('Registro sin fecha valida en ' . $file . ': ' . $backupCode);
        }

        $insert->bind_param('ids', $appointmentId, $amount, $date);
        $insert->execute();
        $inserted++;
    }

    return [
        'processed' => $processed,
        'inserted' => $inserted,
        'skipped' => $skipped,
        'missing' => $missing,
    ];
}

$appointmentMap = buildAppointmentMap($connection, $baseDir);
if (!$appointmentMap) {
    fwrite(STDERR, "No se encontraron citas historicas compatibles para enlazar finanzas.\n");
    exit(1);
}

$connection->begin_transaction();

try {
    $summary = [
        'ingresos' => ['processed' => 0, 'inserted' => 0, 'skipped' => 0, 'missing' => []],
        'perdidas' => ['processed' => 0, 'inserted' => 0, 'skipped' => 0, 'missing' => []],
    ];

    foreach (glob($baseDir . '/ingresos_*.csv') as $file) {
        $result = importFinanceFile($connection, $file, $appointmentMap, $timezone, 'ingresos', 'monto', 'fecha_pago');
        foreach (['processed', 'inserted', 'skipped'] as $key) {
            $summary['ingresos'][$key] += $result[$key];
        }
        $summary['ingresos']['missing'] = array_merge($summary['ingresos']['missing'], $result['missing']);
    }

    foreach (glob($baseDir . '/perdidas_*.csv') as $file) {
        $result = importFinanceFile($connection, $file, $appointmentMap, $timezone, 'perdidas', 'monto_perdido', 'fecha_perdida');
        foreach (['processed', 'inserted', 'skipped'] as $key) {
            $summary['perdidas'][$key] += $result[$key];
        }
        $summary['perdidas']['missing'] = array_merge($summary['perdidas']['missing'], $result['missing']);
    }

    $missingCount = count($summary['ingresos']['missing']) + count($summary['perdidas']['missing']);
    if ($missingCount > 0) {
        throw new RuntimeException('Hay ' . $missingCount . ' registros financieros sin cita enlazada.');
    }

    $connection->commit();

    echo "Importacion de finanzas historicas completada.\n";
    echo "Ingresos: procesados {$summary['ingresos']['processed']}, insertados {$summary['ingresos']['inserted']}, omitidos {$summary['ingresos']['skipped']}.\n";
    echo "Perdidas: procesadas {$summary['perdidas']['processed']}, insertadas {$summary['perdidas']['inserted']}, omitidas {$summary['perdidas']['skipped']}.\n";
} catch (Throwable $exception) {
    $connection->rollback();
    fwrite(STDERR, "Error importando finanzas: " . $exception->getMessage() . "\n");
    exit(1);
}
