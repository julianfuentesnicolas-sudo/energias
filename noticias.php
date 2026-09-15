<?php
require 'includes/auth.php';
require 'config/db.php';
$tituloPagina = 'Noticias';
include 'includes/header.php';

$sql = "SELECT n.idNoticia, n.titulo, n.imagen, n.texto, n.fecha,
               u.nombre, u.apellidos
        FROM noticias n
        JOIN users_data u ON n.idUser = u.idUser
        ORDER BY n.fecha DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll();
?>

<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);text-align:center;">Últimas noticias de SolVolt</h1>

    <?php if (count($noticias) === 0): ?>
        <p style="text-align:center;">Todavía no hay noticias publicadas.</p>
    <?php else: ?>
        <?php foreach ($noticias as $n): ?>
            <article class="noticia">
                <img src="<?= htmlspecialchars($n['imagen']) ?>" alt="<?= htmlspecialchars($n['titulo']) ?>"
                     onerror="this.src='images/placeholder-noticia.jpg'">
                <div class="noticia-contenido">
                    <h3><?= htmlspecialchars($n['titulo']) ?></h3>
                    <div class="noticia-meta">
                        Publicado el <?= date('d/m/Y', strtotime($n['fecha'])) ?>
                        por <?= htmlspecialchars($n['nombre'] . ' ' . $n['apellidos']) ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($n['texto'])) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
