<?php
declare(strict_types=1);

function save_diagnosis(PDO $pdo, int $recordId, string $name): void
{
    $name = trim($name);
    if ($name === '') return;
    $find = $pdo->prepare('SELECT id FROM diagnosticos WHERE nombre = ? LIMIT 1');
    $find->execute([$name]);
    $id = $find->fetchColumn();
    if (!$id) {
        $insert = $pdo->prepare('INSERT INTO diagnosticos (nombre, descripcion) VALUES (?, ?)');
        $insert->execute([$name, 'Entrada ingresada en el prototipo académico.']);
        $id = (int)$pdo->lastInsertId();
    }
    $link = $pdo->prepare('INSERT IGNORE INTO historia_diagnosticos (historia_clinica_id, diagnostico_id, diagnostico_principal) VALUES (?, ?, 1)');
    $link->execute([$recordId, (int)$id]);
}

function replace_record_details(PDO $pdo, int $recordId, string $diagnosisText, string $treatmentsText): void
{
    $pdo->prepare('DELETE FROM historia_diagnosticos WHERE historia_clinica_id = ?')->execute([$recordId]);
    save_diagnosis($pdo, $recordId, $diagnosisText);
    $pdo->prepare('DELETE FROM historia_tratamientos WHERE historia_clinica_id = ?')->execute([$recordId]);
    $items = preg_split('/\R/u', $treatmentsText) ?: [];
    foreach (array_unique(array_filter(array_map('trim', $items))) as $item) {
        $find = $pdo->prepare('SELECT id FROM tratamientos WHERE nombre = ? LIMIT 1');
        $find->execute([$item]);
        $treatmentId = $find->fetchColumn();
        if (!$treatmentId) {
            $insert = $pdo->prepare('INSERT INTO tratamientos (nombre, descripcion) VALUES (?, ?)');
            $insert->execute([$item, 'Descripción registrada desde el prototipo académico.']);
            $treatmentId = (int)$pdo->lastInsertId();
        }
        $pdo->prepare('INSERT INTO historia_tratamientos (historia_clinica_id, tratamiento_id, fecha_realizacion, resultado) VALUES (?, ?, CURDATE(), ?)')
            ->execute([$recordId, (int)$treatmentId, 'Dato de demostración editable.']);
    }
}

