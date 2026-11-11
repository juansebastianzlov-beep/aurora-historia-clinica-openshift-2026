<?php
declare(strict_types=1);

// XAMPP usa estos valores locales. En OpenShift se leen desde variables de entorno.
const DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
const DB_NAME = getenv('DB_NAME') ?: 'historia_clinica_demostracion';
const DB_USER = getenv('DB_USER') ?: 'root';
const DB_PASS = getenv('DB_PASS') ?: '';
const APP_NAME = 'Aurora · Historia Clínica';
