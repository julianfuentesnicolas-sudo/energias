<?php
require 'includes/auth.php';
require 'config/db.php';
requiereLogin();
requiereAdmin();
$tituloPagina = 'Administración de citas';

$mensaje = '';
$tipoMensaje = '';
$hoy = date('Y-m-d');

// Usuario seleccionado
$idSeleccionado = isset($_GET['idUser']) ? (int)$_GET['idUser'] : (isset($_POST['idUser']) ? (int)$_POST['idUser'] : 0);

// --- Crear cita para el usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $fecha = limpiar($_POST['fecha_cita'] ?? '');
    $motivo = limpiar($_POST['motivo_cita'] ?? '');

    if ($idSeleccionado === 0 || $fecha === '') {
        $mensaje = 'Debes seleccionar un usuario e indicar la fecha de la cita.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)");
        $stmt->execute([$idSeleccionado, $fecha, $motivo]);
        $mensaje = 'Cita creada correctamente.';
        $tipoMensaje = 'ok';
    }
}

// --- Modificar cita ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idCita = (int)($_POST['idCita'] ?? 0);
    $fecha = limpiar($_POST['fecha_cita'] ?? '');
    $motivo = limpiar($_POST['motivo_cita'] ?? '');

    if ($fecha === '') {
        $mensaje = 'La fecha de la cita es obligatoria.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE citas SET fecha_cita = ?, motivo_cita = ? WHERE idCita = ?");
        $stmt->execute([$fecha, $motivo, $idCita]);
        $mensaje = 'Cita modificada correctamente.';
        $tipoMensaje = 'ok';
    }
}

// --- Borrar cita ---
if (isset($_GET['borrar'])) {
    $idCita = (int)$_GET['borrar'];
    $stmt = $pdo->prepare("DELETE FROM citas WHERE idCita = ?");
    $stmt->execute([$idCita]);
    $mensaje = 'Cita borrada correctamente.';
    $tipoMensaje = 'ok';
}

// Cita a editar
$citaEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $citaEditar = $stmt->fetch();
    if ($citaEditar) {
        $idSeleccionado = (int)$citaEditar['idUser'];
    }
}

// Listado de todos los usuarios para el desplegable
$usuarios = $pdo->query(
    "SELECT d.idUser, d.nombre, d.apellidos, l.usuario
     FROM users_data d JOIN users_login l ON d.idUser = l.idUser
     ORDER BY d.nombre ASC"
)->fetchAll();

// Citas del usuario seleccionado
$citas = [];
$datosUsuario = null;
if ($idSeleccionado > 0) {
    $stmt = $pdo->prepare("SELECT nombre, apellidos FROM users_data WHERE idUser = ?");
    $stmt->execute([$idSeleccionado]);
    $datosUsuario = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idUser = ? ORDER BY fecha_cita DESC");
    $stmt->execute([$idSeleccionado]);
    $citas = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);">Administración de citas</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-caja">
        <h2>Seleccionar usuario</h2>
        <form method="GET" action="citas-administracion.php">
            <div class="campo">
                <label>Usuario</label>
                <select name="idUser" onchange="this.form.submit()">
                    <option value="0">-- Elige un usuario --</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['idUser'] ?>" <?= $idSeleccionado === (int)$u['idUser'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos'] . ' (' . $u['usuario'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn" style="width:100%;">Ver citas</button>
        </form>
    </div>

    <?php if ($idSeleccionado > 0 && $datosUsuario): ?>

        <div class="form-caja">
            <h2><?= $citaEditar ? 'Modificar cita' : 'Crear cita para ' . htmlspecialchars($datosUsuario['nombre']) ?></h2>
            <form method="POST" action="citas-administracion.php">
                <input type="hidden" name="accion" value="<?= $citaEditar ? 'editar' : 'crear' ?>">
                <input type="hidden" name="idUser" value="<?= $idSeleccionado ?>">
                <?php if ($citaEditar): ?>
                    <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">
                <?php endif; ?>
                <div class="campo">
                    <label>Fecha de la cita *</label>
                    <input type="date" name="fecha_cita" value="<?= $citaEditar ? $citaEditar['fecha_cita'] : '' ?>" required>
                </div>
                <div class="campo">
                    <label>Motivo de la cita</label>
                    <textarea name="motivo_cita" rows="3"><?= $citaEditar ? htmlspecialchars($citaEditar['motivo_cita']) : '' ?></textarea>
                </div>
                <button type="submit" class="btn" style="width:100%;"><?= $citaEditar ? 'Guardar cambios' : 'Crear cita' ?></button>
                <?php if ($citaEditar): ?>
                    <div class="enlace-form"><a href="citas-administracion.php?idUser=<?= $idSeleccionado ?>">Cancelar edición</a></div>
                <?php endif; ?>
            </form>
        </div>

        <h2 style="color:var(--verde-oscuro);margin-top:2.5rem;">
            Citas de <?= htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']) ?>
        </h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Fecha</th><th>Motivo</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (count($citas) === 0): ?>
                    <tr><td colspan="5">Este usuario no tiene citas registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($citas as $c): ?>
                    <tr>
                        <td><?= $c['idCita'] ?></td>
                        <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                        <td><?= htmlspecialchars($c['motivo_cita']) ?></td>
                        <td><?= $c['fecha_cita'] < $hoy ? 'Realizada' : 'Pendiente' ?></td>
                        <td class="acciones">
                            <a href="citas-administracion.php?editar=<?= $c['idCita'] ?>" class="editar">Editar</a>
                            <a href="citas-administracion.php?borrar=<?= $c['idCita'] ?>&idUser=<?= $idSeleccionado ?>"
                               class="borrar borrar-confirm" data-mensaje="¿Seguro que deseas borrar esta cita?">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
