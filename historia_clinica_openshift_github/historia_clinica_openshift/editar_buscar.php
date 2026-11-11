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
        $stmt = $pdo->prepare('SELECT id,fecha_atencion,motivo_consulta,estado FROM historias_clinicas WHERE paciente_id=? ORDER BY fecha_atencion DESC');
        $stmt->execute([(int)$patient['id']]);
        $records = $stmt->fetchAll();
    }
}
require __DIR__ . '/componentes/plantilla.php';
page_header('Modificar historias', 'editar_buscar');
?>
<div class="page-heading"><a class="back-link" href="index.php">← Volver al inicio</a><div class="eyebrow">EDICIÓN DE DATOS <span class="eyebrow-line"></span></div><h1>Modificar una historia</h1><p class="lead">Busca por documento y elige cuál consulta deseas actualizar.</p></div>
<section class="search-card"><form method="get" class="search-form"><label>Tipo<select name="tipo_documento"><?php foreach (['CC','CE','TI','PA','OTRO'] as $t): ?><option value="<?= e($t) ?>"<?= selected($docType,$t) ?>><?= e($t) ?></option><?php endforeach; ?></select></label><label class="search-number">Número de documento<input name="numero_documento" value="<?= e($docNo) ?>" required placeholder="Escribe el número registrado"></label><button class="btn btn-primary" type="submit"><span>⌕</span> Buscar para modificar</button></form></section>
<?php if ($searched && !$patient): ?><div class="empty-result"><div class="empty-mark">⌕</div><h2>No encontramos ese registro</h2><p>Revisa el tipo y número de documento usados al crearlo.</p><a class="btn btn-outline" href="consultar.php">Ir a consultas <span>→</span></a></div><?php endif; ?>
<?php if ($patient): ?><section class="patient-summary"><div class="patient-portrait"><?= e(mb_strtoupper(mb_substr($patient['nombres'],0,1,'UTF-8'), 'UTF-8') . mb_strtoupper(mb_substr($patient['apellidos'],0,1,'UTF-8'), 'UTF-8')) ?></div><div class="patient-summary-main"><span class="eyebrow">PERSONA FICTICIA · <?= count($records) ?> REGISTROS</span><h2><?= e($patient['nombres'] . ' ' . $patient['apellidos']) ?></h2><p><?= e($patient['tipo_documento'] . ' · ' . $patient['numero_documento']) ?> <span class="summary-separator">•</span> Sangre <?= e($patient['nombre_tipo_sangre'] ?: 'sin dato') ?></p></div><a class="btn btn-outline" href="consultar.php?tipo_documento=<?= rawurlencode($docType) ?>&numero_documento=<?= rawurlencode($docNo) ?>">Ver historial <span>↗</span></a></section><div class="history-heading"><div><span class="eyebrow">SELECCIONA UN REGISTRO</span><h2>Historias disponibles para editar</h2></div></div><div class="edit-list"><?php foreach ($records as $record): ?><article class="edit-list-item"><div class="edit-date-icon">✎</div><div class="edit-list-main"><span class="history-date"><?= e(date('d M Y · H:i', strtotime($record['fecha_atencion']))) ?></span><h3><?= e($record['motivo_consulta']) ?></h3><span class="state-badge"><?= e($record['estado']) ?></span></div><a class="btn btn-outline" href="editar.php?id=<?= (int)$record['id'] ?>">Editar esta historia <span>→</span></a></article><?php endforeach; ?></div><?php endif; ?>
<?php page_footer(); ?>
