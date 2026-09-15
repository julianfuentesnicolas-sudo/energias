<?php
require 'includes/auth.php';
require 'config/db.php';
requiereLogin();
requiereAdmin();
$tituloPagina = 'Administración de noticias';

$mensaje = '';
$tipoMensaje = '';
$carpetaImagenes = 'images/';

/**
 * Procesa la subida de una imagen y devuelve la ruta relativa o null.
 */
function subirImagen(array $archivo, array &$errores, string $carpeta): ?string {
    // Si no se ha enviado ningún archivo, no es un error: se devuelve null
    // para que el código que llama decida (mantener imagen actual o avisar).
    if (empty($archivo) || !isset($archivo['error']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'Error al subir la imagen.';
        return null;
    }

    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $permitidas)) {
        $errores[] = 'Formato de imagen no permitido (usa jpg, png, gif o webp).';
        return null;
    }
    if ($archivo['size'] > 3 * 1024 * 1024) {
        $errores[] = 'La imagen no puede superar los 3 MB.';
        return null;
    }

    $nombreNuevo = 'noticia_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $destino = $carpeta . $nombreNuevo;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        $errores[] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }
    return $destino;
}

// --- Crear noticia ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $errores = [];
    $titulo = limpiar($_POST['titulo'] ?? '');
    $texto  = limpiar($_POST['texto'] ?? '');
    $fecha  = limpiar($_POST['fecha'] ?? '');

    if ($titulo === '') $errores[] = 'El título es obligatorio.';
    if ($texto === '')  $errores[] = 'El texto de la noticia es obligatorio.';
    if ($fecha === '')  $errores[] = 'La fecha es obligatoria.';

    $stmt = $pdo->prepare("SELECT idNoticia FROM noticias WHERE titulo = ?");
    $stmt->execute([$titulo]);
    if ($stmt->fetch()) $errores[] = 'Ya existe una noticia con ese título.';

    $rutaImagen = null;
    // La imagen solo se sube si el resto de datos son válidos, así no quedan
    // archivos huérfanos en el servidor cuando el formulario tiene errores.
    if (empty($errores)) {
        $rutaImagen = subirImagen($_FILES['imagen'] ?? [], $errores, $carpetaImagenes);
        if ($rutaImagen === null && empty($errores)) {
            $errores[] = 'La imagen de la noticia es obligatoria.';
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("INSERT INTO noticias (titulo, imagen, texto, fecha, idUser) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $rutaImagen, $texto, $fecha, $_SESSION['idUser']]);
        $mensaje = 'Noticia creada correctamente.';
        $tipoMensaje = 'ok';
    } else {
        $mensaje = implode(' ', $errores);
        $tipoMensaje = 'error';
    }
}

// --- Modificar noticia ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $errores = [];
    $idNoticia = (int)($_POST['idNoticia'] ?? 0);
    $titulo = limpiar($_POST['titulo'] ?? '');
    $texto  = limpiar($_POST['texto'] ?? '');
    $fecha  = limpiar($_POST['fecha'] ?? '');
    $imagenActual = limpiar($_POST['imagen_actual'] ?? '');

    if ($titulo === '') $errores[] = 'El título es obligatorio.';
    if ($texto === '')  $errores[] = 'El texto de la noticia es obligatorio.';
    if ($fecha === '')  $errores[] = 'La fecha es obligatoria.';

    $stmt = $pdo->prepare("SELECT idNoticia FROM noticias WHERE titulo = ? AND idNoticia != ?");
    $stmt->execute([$titulo, $idNoticia]);
    if ($stmt->fetch()) $errores[] = 'Ya existe otra noticia con ese título.';

    $rutaImagen = $imagenActual;
    if (empty($errores)) {
        $nueva = subirImagen($_FILES['imagen'] ?? [], $errores, $carpetaImagenes);
        if ($nueva !== null) {
            $rutaImagen = $nueva; // se sustituye solo si se ha subido una imagen nueva
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("UPDATE noticias SET titulo=?, imagen=?, texto=?, fecha=? WHERE idNoticia=?");
        $stmt->execute([$titulo, $rutaImagen, $texto, $fecha, $idNoticia]);
        $mensaje = 'Noticia modificada correctamente.';
        $tipoMensaje = 'ok';
    } else {
        $mensaje = implode(' ', $errores);
        $tipoMensaje = 'error';
    }
}

// --- Borrar noticia ---
if (isset($_GET['borrar'])) {
    $idNoticia = (int)$_GET['borrar'];
    $stmt = $pdo->prepare("DELETE FROM noticias WHERE idNoticia = ?");
    $stmt->execute([$idNoticia]);
    $mensaje = 'Noticia borrada correctamente.';
    $tipoMensaje = 'ok';
}

// Noticia a editar
$noticiaEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE idNoticia = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $noticiaEditar = $stmt->fetch();
}

// Listado
$noticias = $pdo->query(
    "SELECT n.*, u.nombre, u.apellidos FROM noticias n
     JOIN users_data u ON n.idUser = u.idUser
     ORDER BY n.fecha DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);">Administración de noticias</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-caja" style="max-width:720px;">
        <h2><?= $noticiaEditar ? 'Modificar noticia' : 'Crear nueva noticia' ?></h2>
        <form method="POST" action="noticias-administracion.php" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="<?= $noticiaEditar ? 'editar' : 'crear' ?>">
            <?php if ($noticiaEditar): ?>
                <input type="hidden" name="idNoticia" value="<?= $noticiaEditar['idNoticia'] ?>">
                <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($noticiaEditar['imagen']) ?>">
            <?php endif; ?>

            <div class="campo">
                <label>Título *</label>
                <input type="text" name="titulo" value="<?= $noticiaEditar ? htmlspecialchars($noticiaEditar['titulo']) : '' ?>" required>
            </div>
            <div class="campo">
                <label>Fecha *</label>
                <input type="date" name="fecha" value="<?= $noticiaEditar ? $noticiaEditar['fecha'] : date('Y-m-d') ?>" required>
            </div>
            <div class="campo">
                <label>Imagen <?= $noticiaEditar ? '(deja vacío para mantener la actual)' : '*' ?></label>
                <input type="file" name="imagen" accept="image/*" <?= $noticiaEditar ? '' : 'required' ?>>
                <?php if ($noticiaEditar): ?>
                    <p style="font-size:0.85rem;color:var(--gris-medio);">Imagen actual: <?= htmlspecialchars($noticiaEditar['imagen']) ?></p>
                <?php endif; ?>
            </div>
            <div class="campo">
                <label>Texto de la noticia *</label>
                <textarea name="texto" rows="6" required><?= $noticiaEditar ? htmlspecialchars($noticiaEditar['texto']) : '' ?></textarea>
            </div>

            <button type="submit" class="btn" style="width:100%;"><?= $noticiaEditar ? 'Guardar cambios' : 'Publicar noticia' ?></button>
            <?php if ($noticiaEditar): ?>
                <div class="enlace-form"><a href="noticias-administracion.php">Cancelar edición</a></div>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="color:var(--verde-oscuro);margin-top:2.5rem;">Noticias publicadas</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Título</th><th>Fecha</th><th>Autor</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php if (count($noticias) === 0): ?>
                <tr><td colspan="5">No hay noticias publicadas.</td></tr>
            <?php endif; ?>
            <?php foreach ($noticias as $n): ?>
                <tr>
                    <td><?= $n['idNoticia'] ?></td>
                    <td><?= htmlspecialchars($n['titulo']) ?></td>
                    <td><?= date('d/m/Y', strtotime($n['fecha'])) ?></td>
                    <td><?= htmlspecialchars($n['nombre'] . ' ' . $n['apellidos']) ?></td>
                    <td class="acciones">
                        <a href="noticias-administracion.php?editar=<?= $n['idNoticia'] ?>" class="editar">Editar</a>
                        <a href="noticias-administracion.php?borrar=<?= $n['idNoticia'] ?>" class="borrar borrar-confirm"
                           data-mensaje="¿Seguro que deseas borrar esta noticia?">Borrar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
