<?php
declare(strict_types=1);
function page_header(string $title, string $active = ''): void
{
    $flash = take_flash();
    $userName = e((string)($_SESSION['nombre_usuario'] ?? 'Usuario'));
    $links = [
        'inicio' => ['index.php', '⌂', 'Inicio'],
        'nuevo' => ['nuevo.php', '+', 'Nuevo registro'],
        'consultar' => ['consultar.php', '⌕', 'Consultar'],
        'editar_buscar' => ['editar_buscar.php', '↗', 'Modificar'],
    ];
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#112a3a">
  <title><?= e($title) ?> · Aurora</title>
  <link rel="stylesheet" href="recursos/estilos.css">
</head>
<body>
<div class="demo-strip"><span class="demo-dot"></span> DEMOSTRACIÓN ACADÉMICA <span>·</span> Toda la información de pacientes es ficticia</div>
<div class="app-shell">
  <aside class="sidebar">
    <a class="brand" href="index.php"><span class="brand-mark">A</span><span><b>aurora</b><small>HISTORIA CLÍNICA</small></span></a>
    <div class="side-caption">ESPACIO DE TRABAJO</div>
    <nav class="side-nav">
      <?php foreach ($links as $key => [$href, $icon, $label]): ?>
      <a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="<?= e($href) ?>"><span class="nav-icon"><?= e($icon) ?></span><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-note"><span class="note-icon">✳</span><b>Entorno de práctica</b><p>Usa únicamente los registros de ejemplo. Esta herramienta no reemplaza un sistema clínico real.</p></div>
    <div class="sidebar-bottom"><div class="avatar-small"><?= e(mb_strtoupper(mb_substr((string)($_SESSION['nombre_usuario'] ?? 'U'), 0, 1, 'UTF-8'), 'UTF-8')) ?></div><div class="user-meta"><b><?= $userName ?></b><small>Cuenta local de prueba</small></div><form method="post" action="cerrar_sesion.php"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><button class="logout-icon" aria-label="Cerrar sesión" title="Cerrar sesión">↗</button></form></div>
  </aside>
  <main class="main-area">
    <header class="topbar"><div class="breadcrumb">AURORA <span>/</span> <?= e(mb_strtoupper($title, 'UTF-8')) ?></div><div class="topbar-right"><span class="estado-pill"><i></i> Sistema local</span><span class="top-date"><?= e(date('d M Y')) ?></span></div></header>
    <div class="content-wrap">
      <?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php
}

function page_footer(): void
{
    ?>
    </div>
    <footer class="page-footer"><span>Aurora · Proyecto académico para bases de datos</span><span>Datos de muestra ficticios <b>•</b> No usar para atención clínica</span></footer>
  </main>
</div>
</body>
</html>
<?php
}

