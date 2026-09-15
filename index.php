<?php
require 'includes/auth.php';
$tituloPagina = 'Inicio';
include 'includes/header.php';
?>

<section class="hero">
    <h1>Energía limpia, futuro brillante</h1>
    <p>En SolVolt Energía creemos en un mundo impulsado por fuentes renovables. Solar, eólica e hidráulica: energía responsable para tu hogar y tu empresa.</p>
    <a href="noticias.php" class="btn">Ver noticias</a>
    <?php if (!estaLogueado()): ?>
        <a href="registro.php" class="btn secundario">Únete ahora</a>
    <?php endif; ?>
</section>

<section class="seccion">
    <h2>¿Qué hacemos?</h2>
    <div class="tarjetas">
        <div class="tarjeta">
            <div class="icono">☀️</div>
            <h3>Energía solar</h3>
            <p>Instalamos paneles solares de alta eficiencia para hogares y negocios, reduciendo tu factura y tu huella de carbono.</p>
        </div>
        <div class="tarjeta">
            <div class="icono">💨</div>
            <h3>Energía eólica</h3>
            <p>Desarrollamos parques eólicos terrestres y marinos que aprovechan el viento para generar electricidad limpia.</p>
        </div>
        <div class="tarjeta">
            <div class="icono">💧</div>
            <h3>Energía hidráulica</h3>
            <p>Gestionamos centrales hidroeléctricas sostenibles, aprovechando el agua como fuente inagotable de energía.</p>
        </div>
        <div class="tarjeta">
            <div class="icono">🔋</div>
            <h3>Almacenamiento</h3>
            <p>Soluciones de baterías para almacenar el excedente de energía y usarlo cuando más lo necesites.</p>
        </div>
    </div>
</section>

<section class="seccion" style="background:#fff;">
    <h2>Nuestro compromiso</h2>
    <p style="max-width:800px;margin:0 auto;text-align:center;">
        Desde nuestra fundación, hemos ayudado a más de <strong>200.000 hogares</strong> a hacer la transición hacia
        energías renovables. Trabajamos cada día para reducir la dependencia de los combustibles fósiles y
        construir un planeta más sostenible para las próximas generaciones.
    </p>
    <div style="text-align:center;margin-top:1.5rem;">
        <img src="images/equipo-solvolt.jpg" alt="Equipo de SolVolt instalando paneles solares" style="max-width:600px;margin:0 auto;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
    </div>
</section>

<section class="franja-cta">
    <h2>¿Quieres saber más?</h2>
    <p>Solicita una cita con nuestros expertos y descubre cómo puedes pasarte a la energía limpia.</p>
    <?php if (esUsuario()): ?>
        <a href="citaciones.php" class="btn">Pedir cita</a>
    <?php elseif (!estaLogueado()): ?>
        <a href="registro.php" class="btn">Regístrate para pedir cita</a>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
