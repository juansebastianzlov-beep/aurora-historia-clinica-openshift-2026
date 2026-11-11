<?php
declare(strict_types=1);

// XAMPP usa estos valores locales. En OpenShift se leen desde variables de entorno.
$valorDeEntorno = static function (string $nombre, string $predeterminado): string {
    $valor = getenv($nombre);
    return $valor === false ? $predeterminado : $valor;
};

define('DB_HOST', $valorDeEntorno('DB_HOST', '127.0.0.1'));
define('DB_NAME', $valorDeEntorno('DB_NAME', 'historia_clinica_demostracion'));
define('DB_USER', $valorDeEntorno('DB_USER', 'root'));
define('DB_PASS', $valorDeEntorno('DB_PASS', ''));
const APP_NAME = 'Aurora · Historia Clínica';
