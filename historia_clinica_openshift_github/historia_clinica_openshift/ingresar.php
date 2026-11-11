<?php
declare(strict_types=1);
require_once __DIR__ . '/componentes/iniciar.php';
if (!empty($_SESSION['usuario_id'])) { redirect('index.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $stmt = db()->prepare('SELECT id, nombre_completo, correo, hash_contrasena FROM usuarios_aplicacion WHERE correo = ? AND activo = 1 LIMIT 1');
        $stmt->execute([trim((string)($_POST['correo'] ?? ''))]);
        $user = $stmt->fetch();
        if ($user && password_verify((string)($_POST['contrasena'] ?? ''), $user['hash_contrasena'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$user['id'];
            $_SESSION['nombre_usuario'] = $user['nombre_completo'];
            $_SESSION['correo_usuario'] = $user['correo'];
            redirect('index.php');
        }
        $error = 'Correo o contraseña incorrectos.';
    } catch (Throwable $ex) {
        $error = 'No se pudo conectar con la base de datos. Importa historia_clinica.sql en phpMyAdmin y revisa componentes/configuracion.php.';
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ingresar · Aurora</title><link rel="stylesheet" href="recursos/estilos.css"></head>
<body class="login-body"><div class="demo-strip"><span class="demo-dot"></span> DEMOSTRACIÓN ACADÉMICA · INFORMACIÓN FICTICIA</div><main class="login-layout"><section class="login-art"><a class="brand brand-light" href="ingresar.php"><span class="brand-mark">A</span><span><b>aurora</b><small>HISTORIA CLÍNICA</small></span></a><div class="login-art-content"><span class="eyebrow">ENTORNO LOCAL DE PRÁCTICA</span><h1>Información organizada.<br><em>Atención con contexto.</em></h1><p>Prototipo académico para explorar el registro y consulta de historias clínicas en una base relacional.</p><div class="login-orbit"><div class="orbit-ring ring-one"></div><div class="orbit-ring ring-two"></div><div class="orbit-core">✳</div><span class="orbit-label label-one">REGISTRO</span><span class="orbit-label label-two">SEGUIMIENTO</span><span class="orbit-label label-three">CONSULTA</span></div></div><div class="login-art-foot">PRÁCTICA DE BASES DE DATOS <span>•</span> XAMPP + PHP + MYSQL</div></section><section class="login-panel"><div class="login-card"><span class="eyebrow">BIENVENIDO/A</span><h2>Inicia sesión</h2><p class="muted">Accede al espacio local de demostración.</p><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><label>Correo electrónico<input type="email" name="correo" autocomplete="username" required value="acceso@demostracion.local"></label><label>Contraseña<input type="password" name="contrasena" autocomplete="current-password" required placeholder="Ingresa la contraseña"></label><button class="btn btn-primary btn-full" type="submit">Entrar <span>→</span></button></form><div class="credential-box"><b>Acceso inicial de la demostración</b><span>acceso@demostracion.local</span><span>Clave1234!</span><small>Cámbialo en la tabla <code>usuarios_aplicacion</code> si lo necesitas.</small></div><p class="login-disclaimer">No ingreses información real de pacientes. Los registros del sistema son datos ficticios.</p></div></section></main></body></html>
