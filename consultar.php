<?php
declare(strict_types=1);
require_once __DIR__ . '/componentes/iniciar.php';
require_login();
$pdo = db();
$docType = trim((string)($_GET['tipo_documento'] ?? 'OTRO'));
$docNo = trim((string)($_GET['numero_documento'] ?? ''));
$searched = $docNo !== '';
$patient = null;
$records = [];
if ($searched) {
    $stmt = $pdo->prepare('SELECT p.*, b.nombre AS nombre_tipo_sangre FROM pacientes p LEFT JOIN tipos_sangre b ON b.id=p.tipo_sangre_id WHERE p.tipo_documento=? AND p.numero_documento=? LIMIT 1');
    $stmt->execute([$docType,$docNo]);
    $patient = $stmt->fetch() ?: null;
    if ($patient) {
        $stmt = $pdo->prepare("SELECT cr.*,
          (SELECT GROUP_CONCAT(d.nombre SEPARATOR ', ') FROM historia_diagnosticos rd JOIN diagnosticos d ON d.id=rd.diagnostico_id WHERE rd.historia_clinica_id=cr.id) AS nombres_diagnosticos,
          (SELECT GROUP_CONCAT(t.nombre SEPARATOR '\n') FROM historia_tratamientos rt JOIN tratamientos t ON t.id=rt.tratamiento_id WHERE rt.historia_clinica_id=cr.id) AS nombres_tratamientos
          FROM historias_clinicas cr WHERE cr.paciente_id=? ORDER BY cr.fecha_atencion DESC");
        $stmt->execute([(int)$patient['id']]);
        $records = $stmt->fetchAll();
    }
}
require __DIR__ . '/componentes/plantilla.php';
page_header('Consultar historias', 'consultar');
?>
<div class="page-heading"><a class="back-link" href="index.php">← Volver al inicio</a><div class="eyebrow">BÚSQUEDA DE HISTORIAL <span class="eyebrow-line"></span></div><h1>Consultar historias</h1><p class="lead">Busca una persona por tipo y número de documento para ver todas sus consultas.</p></div>
<section class="search-card"><form method="get" class="search-form"><label>Tipo<select name="tipo_documento"><?php foreach (['CC','CE','TI','PA','OTRO'] as $t): ?><option value="<?= e($t) ?>"<?= selected($docType,$t) ?>><?= e($t) ?></option><?php endforeach; ?></select></label><label class="search-number">Número de documento<input name="numero_documento" value="<?= e($docNo) ?>" required placeholder="Escribe el número registrado"></label><button class="btn btn-primary" type="submit"><span>⌕</span> Buscar historial</button></form><div class="search-hint"><span>i</span> Usa <b>OTRO / PRUEBA-1001</b> para consultar las dos historias incluidas.</div></section>
<?php if ($searched && !$patient): ?><div class="empty-result"><div class="empty-mark">⌕</div><h2>No encontramos ese registro</h2><p>Verifica el tipo y número de documento, o crea una historia de demostración.</p><a class="btn btn-primary" href="nuevo.php">Registrar persona <span>→</span></a></div><?php endif; ?>
<?php if ($patient): ?><section class="patient-summary"><div class="patient-portrait"><?= e(mb_strtoupper(mb_substr($patient['nombres'],0,1,'UTF-8'), 'UTF-8') . mb_strtoupper(mb_substr($patient['apellidos'],0,1,'UTF-8'), 'UTF-8')) ?></div><div class="patient-summary-main"><span class="eyebrow">FICHA FICTICIA · <?= count($records) ?> <?= count($records) === 1 ? 'HISTORIA' : 'HISTORIAS' ?></span><h2><?= e($patient['nombres'] . ' ' . $patient['apellidos']) ?></h2><p><?= e($patient['tipo_documento'] . ' · ' . $patient['numero_documento']) ?> <span class="summary-separator">•</span> <?= e($patient['sexo']) ?></p></div><div class="patient-summary-blood"><span>TIPO DE SANGRE</span><b><?= e($patient['nombre_tipo_sangre'] ?: 'Sin dato') ?></b></div><div class="patient-summary-action"><a class="btn btn-outline" href="editar_buscar.php?tipo_documento=<?= rawurlencode($docType) ?>&numero_documento=<?= rawurlencode($docNo) ?>">Modificar datos <span>↗</span></a></div></section>
<div class="history-heading"><div><span class="eyebrow">LÍNEA DE TIEMPO</span><h2>Consultas registradas</h2></div><a class="text-link" href="nuevo.php">＋ Agregar consulta</a></div>
<?php foreach ($records as $record): ?><article class="history-card"><div class="history-marker"><span></span></div><div class="history-body"><div class="history-top"><div><span class="history-date"><?= e(date('d M Y · H:i', strtotime($record['fecha_atencion']))) ?></span><span class="state-badge state-<?= e(mb_strtolower(str_replace(' ','-', $record['estado']))) ?>"><?= e($record['estado']) ?></span></div><a class="row-action" href="editar.php?id=<?= (int)$record['id'] ?>" aria-label="Modificar historia">✎</a></div><h3><?= e($record['motivo_consulta']) ?></h3><div class="detail-grid"><div><span class="detail-label">SÍNTOMAS</span><p><?= nl2br(e($record['sintomas'] ?: 'Sin información registrada.')) ?></p></div><div><span class="detail-label">AFECTACIONES</span><p><?= nl2br(e($record['afectaciones'] ?: 'Sin información registrada.')) ?></p></div><div><span class="detail-label">VALORACIÓN</span><p><?= nl2br(e($record['valoracion'] ?: 'Sin información registrada.')) ?></p></div><div><span class="detail-label">DIAGNÓSTICO / CATEGORÍA</span><p><?= e($record['nombres_diagnosticos'] ?: 'Sin categoría registrada.') ?></p></div><div><span class="detail-label">TRATAMIENTOS O ACCIONES</span><p><?= nl2br(e($record['nombres_tratamientos'] ?: 'Sin acciones registradas.')) ?></p></div><div><span class="detail-label">NOTAS</span><p><?= nl2br(e($record['notas'] ?: 'Sin notas adicionales.')) ?></p></div></div></div></article><?php endforeach; ?><?php endif; ?>
<?php page_footer(); ?>
