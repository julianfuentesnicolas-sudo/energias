<?php
require 'includes/auth.php';
require 'config/db.php';
requiereLogin();

if (!esUsuario()) {
    // Solo los usuarios (no admins) gestionan sus citas aquí
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Citaciones';
$idUser = $_SESSION['idUser'];
$mensaje = '';
$tipoMensaje = '';
$hoy = date('Y-m-d');

// --- Crear nueva cita ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $fecha = limpiar($_POST['fecha_cita'] ?? '');
    $motivo = limpiar($_POST['motivo_cita'] ?? '');

    if ($fecha === '') {
        $mensaje = 'La fecha de la cita es obligatoria.';
        $tipoMensaje = 'error';
    } elseif ($fecha < $hoy) {
        $mensaje = 'No puedes solicitar una cita en una fecha pasada.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)");
        $stmt->execute([$idUser, $fecha, $motivo]);
        $mensaje = 'Cita solicitada correctamente.';
        $tipoMensaje = 'ok';
    }
}

// --- Editar cita (solo si es futura y pertenece al usuario) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idCita = (int)($_POST['idCita'] ?? 0);
    $fecha = limpiar($_POST['fecha_cita'] ?? '');
    $motivo = limpiar($_POST['motivo_cita'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita = ? AND idUser = ?");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if (!$cita) {
        $mensaje = 'Cita no encontrada.';
        $tipoMensaje = 'error';
    } elseif ($cita['fecha_cita'] < $hoy) {
        $mensaje = 'No puedes modificar una cita ya realizada.';
        $tipoMensaje = 'error';
    } elseif ($fecha < $hoy) {
        $mensaje = 'La nueva fecha no puede ser pasada.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE citas SET fecha_cita = ?, motivo_cita = ? WHERE idCita = ? AND idUser = ?");
        $stmt->execute([$fecha, $motivo, $idCita, $idUser]);
        $mensaje = 'Cita modificada correctamente.';
        $tipoMensaje = 'ok';
    }
}

// --- Borrar cita (solo si es futura y pertenece al usuario) ---
if (isset($_GET['borrar'])) {
    $idCita = (int)$_GET['borrar'];
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita = ? AND idUser = ?");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if ($cita && $cita['fecha_cita'] >= $hoy) {
        $stmt = $pdo->prepare("DELETE FROM citas WHERE idCita = ? AND idUser = ?");
        $stmt->execute([$idCita, $idUser]);
        $mensaje = 'Cita borrada correctamente.';
        $tipoMensaje = 'ok';
    } else {
        $mensaje = 'No se puede borrar esa cita.';
        $tipoMensaje = 'error';
    }
}

// Cita a editar (si viene por GET)
$citaEditar = null;
if (isset($_GET['editar'])) {
    $idCita = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita = ? AND idUser = ?");
    $stmt->execute([$idCita, $idUser]);
    $citaEditar = $stmt->fetch();
}

// Listado de citas del usuario
$stmt = $pdo->prepare("SELECT * FROM citas WHERE idUser = ? ORDER BY fecha_cita ASC");
$stmt->execute([$idUser]);
$citas = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);">Mis citaciones</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-caja">
        <h2><?= $citaEditar ? 'Modificar cita' : 'Solicitar nueva cita' ?></h2>
        <form method="POST" action="citaciones.php">
            <input type="hidden" name="accion" value="<?= $citaEditar ? 'editar' : 'crear' ?>">
            <?php if ($citaEditar): ?>
                <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">
            <?php endif; ?>
            <div class="campo">
                <label>Fecha de la cita *</label>
                <input type="date" name="fecha_cita" min="<?= $hoy ?>"
                       value="<?= $citaEditar ? $citaEditar['fecha_cita'] : '' ?>" required>
            </div>
            <div class="campo">
                <label>Motivo de la cita</label>
                <textarea name="motivo_cita" rows="3"><?= $citaEditar ? htmlspecialchars($citaEditar['motivo_cita']) : '' ?></textarea>
            </div>
            <button type="submit" class="btn" style="width:100%;"><?= $citaEditar ? 'Guardar cambios' : 'Solicitar cita' ?></button>
            <?php if ($citaEditar): ?>
                <div class="enlace-form"><a href="citaciones.php">Cancelar edición</a></div>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="color:var(--verde-oscuro);margin-top:2.5rem;">Historial de citas</h2>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Motivo</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php if (count($citas) === 0): ?>
                <tr><td colspan="4">No tienes citas registradas.</td></tr>
            <?php endif; ?>
            <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                    <td><?= htmlspecialchars($c['motivo_cita']) ?></td>
                    <td><?= $c['fecha_cita'] < $hoy ? 'Realizada' : 'Pendiente' ?></td>
                    <td class="acciones">
                        <?php if ($c['fecha_cita'] >= $hoy): ?>
                            <a href="citaciones.php?editar=<?= $c['idCita'] ?>" class="editar">Editar</a>
                            <a href="citaciones.php?borrar=<?= $c['idCita'] ?>" class="borrar borrar-confirm"
                               data-mensaje="¿Seguro que deseas borrar esta cita?">Borrar</a>
                        <?php else: ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
