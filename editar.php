<?php
declare(strict_types=1);
require_once __DIR__ . '/componentes/iniciar.php';
require_once __DIR__ . '/componentes/clinica.php';
require_login();
$pdo = db();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { flash('El registro solicitado no es válido.', 'error'); redirect('editar_buscar.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $first = post_value('nombres', 80);
    $last = post_value('apellidos', 100);
    $complaint = post_value('motivo_consulta', 240);
    if (!$first || !$last || !$complaint) {
        $error = 'Completa nombres, apellidos y motivo de consulta.';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT paciente_id FROM historias_clinicas WHERE id=?');
            $stmt->execute([(int)$id]);
            $patientId = $stmt->fetchColumn();
            if (!$patientId) throw new RuntimeException('Registro no encontrado');
            $stmt = $pdo->prepare('UPDATE pacientes SET nombres=?,apellidos=?,fecha_nacimiento=?,sexo=?,telefono=?,correo=?,tipo_sangre_id=? WHERE id=?');
            $stmt->execute([$first,$last,post_value('fecha_nacimiento',10) ?: null,post_value('sexo',30),post_value('telefono',30) ?: null,post_value('correo',190) ?: null,(int)($_POST['tipo_sangre_id'] ?? 0) ?: null,(int)$patientId]);
            $when = post_value('fecha_atencion',16) ?: date('Y-m-d\TH:i');
            $stmt = $pdo->prepare('UPDATE historias_clinicas SET fecha_atencion=?,motivo_consulta=?,sintomas=?,afectaciones=?,valoracion=?,notas=?,estado=? WHERE id=?');
            $stmt->execute([str_replace('T',' ',$when) . ':00',$complaint,post_value('sintomas'),post_value('afectaciones'),post_value('valoracion'),post_value('notas'),post_value('estado',30),(int)$id]);
            replace_record_details($pdo,(int)$id,post_value('diagnostico',180),post_value('tratamientos'));
            $pdo->commit();
            flash('Los cambios se guardaron en la base de datos.');
            redirect('consultar.php?tipo_documento=' . rawurlencode(post_value('tipo_documento',5)) . '&numero_documento=' . rawurlencode(post_value('numero_documento',30)));
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'No se pudieron guardar los cambios. Revisa los datos e inténtalo otra vez.';
        }
    }
}
$stmt = $pdo->prepare("SELECT cr.*,p.id AS persona_id,p.tipo_documento,p.numero_documento,p.nombres,p.apellidos,p.fecha_nacimiento,p.sexo,p.telefono,p.correo,p.tipo_sangre_id,b.nombre AS nombre_tipo_sangre,
  (SELECT GROUP_CONCAT(d.nombre SEPARATOR ', ') FROM historia_diagnosticos rd JOIN diagnosticos d ON d.id=rd.diagnostico_id WHERE rd.historia_clinica_id=cr.id) AS nombres_diagnosticos,
  (SELECT GROUP_CONCAT(t.nombre SEPARATOR '\n') FROM historia_tratamientos rt JOIN tratamientos t ON t.id=rt.tratamiento_id WHERE rt.historia_clinica_id=cr.id) AS nombres_tratamientos
  FROM historias_clinicas cr JOIN pacientes p ON p.id=cr.paciente_id LEFT JOIN tipos_sangre b ON b.id=p.tipo_sangre_id WHERE cr.id=? LIMIT 1");
$stmt->execute([(int)$id]);
$record = $stmt->fetch();
if (!$record) { flash('No encontramos la historia solicitada.', 'error'); redirect('editar_buscar.php'); }
$bloodTypes = $pdo->query('SELECT id,nombre FROM tipos_sangre ORDER BY id')->fetchAll();
require __DIR__ . '/componentes/plantilla.php';
page_header('Editar historia', 'editar_buscar');
?>
<div class="page-heading"><a class="back-link" href="editar_buscar.php?tipo_documento=<?= rawurlencode($record['tipo_documento']) ?>&numero_documento=<?= rawurlencode($record['numero_documento']) ?>">← Volver a los registros</a><div class="eyebrow">EDICIÓN DE HISTORIA <span class="eyebrow-line"></span></div><h1>Actualizar registro</h1><p class="lead">Los cambios se guardan en MySQL y se reflejan en la consulta por documento.</p></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="record-form"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$record['id'] ?>"><input type="hidden" name="tipo_documento" value="<?= e($record['tipo_documento']) ?>"><input type="hidden" name="numero_documento" value="<?= e($record['numero_documento']) ?>"><section class="form-section"><div class="section-heading"><span class="section-number">01</span><div><h2>Datos de la persona</h2><p>Identificación: <?= e($record['tipo_documento'] . ' · ' . $record['numero_documento']) ?></p></div></div><div class="fields-grid"><label>Nombres <span class="required">*</span><input name="nombres" maxlength="80" required value="<?= e($record['nombres']) ?>"></label><label>Apellidos <span class="required">*</span><input name="apellidos" maxlength="100" required value="<?= e($record['apellidos']) ?>"></label><label>Fecha de nacimiento<input type="date" name="fecha_nacimiento" value="<?= e($record['fecha_nacimiento'] ?? '') ?>"></label><label>Sexo registrado<select name="sexo"><?php foreach (['Mujer','Hombre','Intersexual','Prefiere no indicar'] as $s): ?><option<?= selected($record['sexo'],$s) ?>><?= e($s) ?></option><?php endforeach; ?></select></label><label>Tipo de sangre<select name="tipo_sangre_id"><option value="">Sin dato</option><?php foreach ($bloodTypes as $blood): ?><option value="<?= (int)$blood['id'] ?>"<?= (int)$record['tipo_sangre_id'] === (int)$blood['id'] ? ' selected' : '' ?>><?= e($blood['nombre']) ?></option><?php endforeach; ?></select></label><label>Teléfono<input name="telefono" maxlength="30" value="<?= e($record['telefono'] ?? '') ?>"></label><label class="field-span-2">Correo<input type="email" name="correo" maxlength="190" value="<?= e($record['correo'] ?? '') ?>"></label></div></section><section class="form-section"><div class="section-heading"><span class="section-number">02</span><div><h2>Detalle de la consulta</h2><p>Historia creada el <?= e(date('d/m/Y',strtotime($record['fecha_atencion']))) ?>.</p></div></div><div class="fields-grid"><label>Fecha y hora<input type="datetime-local" name="fecha_atencion" value="<?= e(substr(str_replace(' ','T',$record['fecha_atencion']),0,16)) ?>"></label><label>Estado<select name="estado"><?php foreach (['Abierta','En seguimiento','Cerrada'] as $s): ?><option<?= selected($record['estado'],$s) ?>><?= e($s) ?></option><?php endforeach; ?></select></label><label class="field-span-2">Problema o motivo de consulta <span class="required">*</span><input name="motivo_consulta" maxlength="240" required value="<?= e($record['motivo_consulta']) ?>"></label><label class="field-span-2">Síntomas reportados<textarea name="sintomas" rows="3"><?= e($record['sintomas']) ?></textarea></label><label class="field-span-2">Afectaciones en actividades cotidianas<textarea name="afectaciones" rows="3"><?= e($record['afectaciones']) ?></textarea></label><label class="field-span-2">Valoración / observaciones<textarea name="valoracion" rows="3"><?= e($record['valoracion']) ?></textarea></label><label class="field-span-2">Diagnóstico o categoría descriptiva<input name="diagnostico" maxlength="180" value="<?= e($record['nombres_diagnosticos'] ?? '') ?>"></label><label class="field-span-2">Tratamientos o acciones realizadas<textarea name="tratamientos" rows="4"><?= e($record['nombres_tratamientos'] ?? '') ?></textarea><small class="field-help">Una acción por línea. Al guardar se actualizan las relaciones de este registro.</small></label><label class="field-span-2">Notas adicionales<textarea name="notas" rows="3"><?= e($record['notas']) ?></textarea></label></div></section><div class="form-actions"><a class="btn btn-ghost" href="consultar.php?tipo_documento=<?= rawurlencode($record['tipo_documento']) ?>&numero_documento=<?= rawurlencode($record['numero_documento']) ?>">Cancelar</a><button class="btn btn-primary" type="submit">Guardar cambios <span>→</span></button></div></form>
<?php page_footer(); ?>
