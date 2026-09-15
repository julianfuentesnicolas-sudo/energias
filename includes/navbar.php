<?php
// Requiere que auth.php ya esté incluido antes de este archivo.
$paginaActual = basename($_SERVER['PHP_SELF']);

function claseActiva(string $pagina, string $actual): string {
    return $pagina === $actual ? 'activo' : '';
}
?>
<nav class="navbar">
    <a href="index.php" class="logo"><span class="sol">☀</span> SolVolt Energía</a>
    <ul>
        <li><a href="index.php" class="<?= claseActiva('index.php', $paginaActual) ?>">Inicio</a></li>
        <li><a href="noticias.php" class="<?= claseActiva('noticias.php', $paginaActual) ?>">Noticias</a></li>

        <?php if (esUsuario()): ?>
            <li><a href="citaciones.php" class="<?= claseActiva('citaciones.php', $paginaActual) ?>">Citaciones</a></li>
            <li><a href="perfil.php" class="<?= claseActiva('perfil.php', $paginaActual) ?>">Perfil</a></li>
        <?php endif; ?>

        <?php if (esAdmin()): ?>
            <li><a href="usuarios-administracion.php" class="<?= claseActiva('usuarios-administracion.php', $paginaActual) ?>">Usuarios</a></li>
            <li><a href="citas-administracion.php" class="<?= claseActiva('citas-administracion.php', $paginaActual) ?>">Citas</a></li>
            <li><a href="noticias-administracion.php" class="<?= claseActiva('noticias-administracion.php', $paginaActual) ?>">Noticias (admin)</a></li>
            <li><a href="perfil.php" class="<?= claseActiva('perfil.php', $paginaActual) ?>">Perfil</a></li>
        <?php endif; ?>

        <?php if (estaLogueado()): ?>
            <li><a href="logout.php" class="cerrar-sesion">Cerrar sesión</a></li>
        <?php else: ?>
            <li><a href="login.php" class="<?= claseActiva('login.php', $paginaActual) ?>">Iniciar sesión</a></li>
            <li><a href="registro.php" class="<?= claseActiva('registro.php', $paginaActual) ?>">Registro</a></li>
        <?php endif; ?>
    </ul>
</nav>
