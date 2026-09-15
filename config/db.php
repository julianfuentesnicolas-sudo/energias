<?php
// ============================================================
// Conexión a la base de datos mediante PDO
// ============================================================

$db_host = 'localhost';
$db_name = 'solvolt_energia';
$db_user = 'root';       // <-- cambia por tu usuario de MySQL
$db_pass = '';           // <-- cambia por tu contraseña de MySQL
$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $opciones);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
