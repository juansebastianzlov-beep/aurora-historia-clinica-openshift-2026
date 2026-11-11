<?php
declare(strict_types=1);
date_default_timezone_set('America/Bogota');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/configuracion.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $sent = (string)($_POST['_csrf'] ?? '');
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('La sesión del formulario venció. Regresa, recarga la página e inténtalo de nuevo.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ingresar.php');
        exit;
    }
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $item = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($item) ? $item : null;
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function post_value(string $name, int $max = 4000): string
{
    $value = trim((string)($_POST[$name] ?? ''));
    return mb_substr($value, 0, $max, 'UTF-8');
}

function selected(string $actual, string $option): string
{
    return $actual === $option ? ' selected' : '';
}

