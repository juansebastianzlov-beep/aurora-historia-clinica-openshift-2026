<?php
declare(strict_types=1);
require_once __DIR__ . '/componentes/iniciar.php';
require_once __DIR__ . '/componentes/clinica.php';
require_login();
$pdo = db();
$bloodTypes = $pdo->query('SELECT id,nombre FROM tipos_sangre ORDER BY id')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $docType = post_value('tipo_documento', 5);
    $docNo = post_value('numero_documento', 30);
    $first = post_value('nombres', 80);
    $last = post_value('apellidos', 100);
    $complaint = post_value('motivo_consulta', 240);
    if (!$docNo || !$first || !$last || !$complaint) {
        $error = 'Completa documento, nombres, apellidos y motivo de consulta.';
    } else {
        try {
            $pdo->beginTransaction();
            $find = $pdo->prepare('SELECT id FROM pacientes WHERE tipo_documento=? AND numero_documento=? LIMIT 1');
            $find->execute([$docType, $docNo]);
            $patientId = $find->fetchColumn();
            $patientData = [$first, $last, post_value('fecha_nacimiento', 10) ?: null, post_value('sexo', 30), post_value('telefono', 30) ?: null, post_value('correo', 190) ?: null, (int)($_POST['tipo_sangre_id'] ?? 0) ?: null];
            if ($patientId) {
                $stmt = $pdo->prepare('UPDATE pacientes SET nombres=?,apellidos=?,fecha_nacimiento=?,sexo=?,telefono=?,correo=?,tipo_sangre_id=? WHERE id=?');
                $stmt->execute([...$patientData, (int)$patientId]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO pacientes (tipo_documento,numero_documento,nombres,apellidos,fecha_nacimiento,sexo,telefono,correo,tipo_sangre_id,creado_por) VALUES (?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$docType,$docNo,...$patientData,(int)$_SESSION['usuario_id']]);
                $patientId = (int)$pdo->lastInsertId();
            }
            $stmt = $pdo->prepare('INSERT INTO historias_clinicas (paciente_id,usuario_autor_id,fecha_atencion,motivo_consulta,sintomas,afectaciones,valoracion,notas,estado) VALUES (?,?,?, ?,?,?,?,?,?)');
            $date = post_value('fecha_atencion', 16) ?: date('Y-m-d\TH:i');
            $stmt->execute([(int)$patientId,(int)$_SESSION['usuario_id'],str_replace('T',' ',$date) . ':00',$complaint,post_value('sintomas'),post_value('afectaciones'),post_value('valoracion'),post_value('notas'),post_value('estado',30) ?: 'Abierta']);
            $recordId = (int)$pdo->lastInsertId();
            replace_record_details($pdo,$recordId,post_value('diagnostico',180),post_value('tratamientos'));
            $pdo->commit();
            flash('La historia de demostración quedó guardada.');
            redirect('consultar.php?tipo_documento=' . rawurlencode($docType) . '&numero_documento=' . rawurlencode($docNo));
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'No se pudo guardar. Verifica que el documento no esté repetido y vuelve a intentarlo.';
        }
    }
}
require __DIR__ . '/componentes/plantilla.php';
page_header('Nuevo registro', 'nuevo');
?>
<div class="page-heading"><a class="back-link" href="index.php">← Volver al inicio</a><div class="eyebrow">NUEVA ATENCIÓN <span class="eyebrow-line"></span></div><h1>Registrar historia clínica</h1><p class="lead">Crea o identifica una persona y agrega una nueva consulta a su historial.</p></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="record-form"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><section class="form-section"><div class="section-heading"><span class="section-number">01</span><div><h2>Datos de la persona</h2><p>La identificación conecta varias consultas con la misma ficha.</p></div></div><div class="fields-grid"><label>Tipo de documento<select name="tipo_documento" required><?php foreach (['CC','CE','TI','PA','OTRO'] as $t): ?><option value="<?= e($t) ?>"><?= e($t) ?></option><?php endforeach; ?></select></label><label>Número de documento <span class="required">*</span><input name="numero_documento" maxlength="30" required placeholder="Ej. PRUEBA-1004"></label><label>Nombres <span class="required">*</span><input name="nombres" maxlength="80" required placeholder="Nombres"></label><label>Apellidos <span class="required">*</span><input name="apellidos" maxlength="100" required placeholder="Apellidos"></label><label>Fecha de nacimiento<input type="date" name="fecha_nacimiento"></label><label>Sexo registrado<select name="sexo"><option>Mujer</option><option>Hombre</option><option>Intersexual</option><option>Prefiere no indicar</option></select></label><label>Tipo de sangre<select name="tipo_sangre_id"><option value="">Sin dato</option><?php foreach ($bloodTypes as $blood): ?><option value="<?= (int)$blood['id'] ?>"><?= e($blood['nombre']) ?></option><?php endforeach; ?></select></label><label>Teléfono de ejemplo<input name="telefono" maxlength="30" placeholder="Opcional"></label><label class="field-span-2">Correo de ejemplo<input type="email" name="correo" maxlength="190" placeholder="Opcional"></label></div></section>
<section class="form-section"><div class="section-heading"><span class="section-number">02</span><div><h2>Detalle de la consulta</h2><p>Registra el motivo, los síntomas descritos y las acciones anotadas.</p></div></div><div class="fields-grid"><label>Fecha y hora<input type="datetime-local" name="fecha_atencion" value="<?= e(date('Y-m-d\TH:i')) ?>"></label><label>Estado<select name="estado"><option>Abierta</option><option>En seguimiento</option><option>Cerrada</option></select></label><label class="field-span-2">Problema o motivo de consulta <span class="required">*</span><input name="motivo_consulta" maxlength="240" required placeholder="Resumen breve del motivo"></label><label class="field-span-2">Síntomas reportados<textarea name="sintomas" rows="3" placeholder="Descripción registrada en el ejemplo"></textarea></label><label class="field-span-2">Afectaciones en actividades cotidianas<textarea name="afectaciones" rows="3" placeholder="Impacto descrito por la persona"></textarea></label><label class="field-span-2">Valoración / observaciones<textarea name="valoracion" rows="3" placeholder="Nota de valoración; no ingreses datos reales"></textarea></label><label class="field-span-2">Diagnóstico o categoría descriptiva<input name="diagnostico" maxlength="180" placeholder="Ej. Valoración inicial (puede dejarse vacío)"></label><label class="field-span-2">Tratamientos o acciones realizadas<textarea name="tratamientos" rows="3" placeholder="Una acción por línea. Ejemplo: Orientación general"></textarea><small class="field-help">Cada línea se guarda como un tratamiento relacionado a esta consulta.</small></label><label class="field-span-2">Notas adicionales<textarea name="notas" rows="3" placeholder="Información complementaria ficticia"></textarea></label></div></section><div class="form-actions"><a href="index.php" class="btn btn-ghost">Cancelar</a><button class="btn btn-primary" type="submit">Guardar historia <span>→</span></button></div></form>
<?php page_footer(); ?>
