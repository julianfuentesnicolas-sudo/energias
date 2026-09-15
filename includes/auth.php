<?php
// ============================================================
// Gestión de sesión y utilidades de autenticación
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaLogueado(): bool {
    return isset($_SESSION['idUser']);
}

function esAdmin(): bool {
    return estaLogueado() && $_SESSION['rol'] === 'admin';
}

function esUsuario(): bool {
    return estaLogueado() && $_SESSION['rol'] === 'user';
}

// Redirige si la página requiere estar logueado
function requiereLogin(): void {
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
}

// Redirige si la página requiere rol admin
function requiereAdmin(): void {
    if (!esAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Limpieza básica de datos de formulario
function limpiar(string $dato): string {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}
