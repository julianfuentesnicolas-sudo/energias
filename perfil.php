<?php
require 'includes/auth.php';
require 'config/db.php';
requiereLogin();
$tituloPagina = 'Mi perfil';

$idUser = $_SESSION['idUser'];
$mensaje = '';
$tipoMensaje = '';

// --- Actualizar datos personales ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'datos') {
    $nombre = limpiar($_POST['nombre'] ?? '');
    $apellidos = limpiar($_POST['apellidos'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $sexo = limpiar($_POST['sexo'] ?? '');

    if ($nombre === '' || $apellidos === '' || $email === '' || $telefono === '' || $fecha_nacimiento === '') {
        $mensaje = 'Todos los campos obligatorios deben estar rellenos.';
        $tipoMensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El email no es válido.';
        $tipoMensaje = 'error';
    } else {
        // Comprobar que el email no lo usa otro usuario
        $stmt = $pdo->prepare("SELECT idUser FROM users_data WHERE email = ? AND idUser != ?");
        $stmt->execute([$email, $idUser]);
        if ($stmt->fetch()) {
            $mensaje = 'Ese email ya está siendo usado por otra cuenta.';
            $tipoMensaje = 'error';
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users_data SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
                 WHERE idUser=?"
            );
            $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser]);
            $_SESSION['nombre'] = $nombre;
            $mensaje = 'Datos actualizados correctamente.';
            $tipoMensaje = 'ok';
        }
    }
}

// --- Cambiar contraseña ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'password') {
    $actual = $_POST['password_actual'] ?? '';
    $nueva = $_POST['password_nueva'] ?? '';
    $nueva2 = $_POST['password_nueva2'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users_login WHERE idUser = ?");
    $stmt->execute([$idUser]);
    $fila = $stmt->fetch();

    if (!$fila || !password_verify($actual, $fila['password'])) {
        $mensaje = 'La contraseña actual no es correcta.';
        $tipoMensaje = 'error';
    } elseif (strlen($nueva) < 6) {
        $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres.';
        $tipoMensaje = 'error';
    } elseif ($nueva !== $nueva2) {
        $mensaje = 'Las contraseñas nuevas no coinciden.';
        $tipoMensaje = 'error';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users_login SET password = ? WHERE idUser = ?");
        $stmt->execute([$hash, $idUser]);
        $mensaje = 'Contraseña actualizada correctamente.';
        $tipoMensaje = 'ok';
    }
}

// Obtener datos actuales
$stmt = $pdo->prepare(
    "SELECT d.*, l.usuario, l.rol FROM users_data d
     JOIN users_login l ON d.idUser = l.idUser
     WHERE d.idUser = ?"
);
$stmt->execute([$idUser]);
$datos = $stmt->fetch();

include 'includes/header.php';
?>

<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);">Mi perfil</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-caja">
        <h2>Datos personales</h2>
        <p style="text-align:center;color:var(--gris-medio);">
            Usuario: <strong><?= htmlspecialchars($datos['usuario']) ?></strong> (no se puede modificar) &middot;
            Rol: <strong><?= htmlspecialchars($datos['rol']) ?></strong>
        </p>
        <form method="POST" action="perfil.php">
            <input type="hidden" name="accion" value="datos">
            <div class="fila-2">
                <div class="campo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
                </div>
                <div class="campo">
                    <label>Apellidos *</label>
                    <input type="text" name="apellidos" value="<?= htmlspecialchars($datos['apellidos']) ?>" required>
                </div>
            </div>
            <div class="campo">
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($datos['email']) ?>" required>
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label>Teléfono *</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>" required>
                </div>
                <div class="campo">
                    <label>Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($datos['fecha_nacimiento']) ?>" required>
                </div>
            </div>
            <div class="campo">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?= htmlspecialchars($datos['direccion']) ?>">
            </div>
            <div class="campo">
                <label>Sexo</label>
                <select name="sexo">
                    <option value="Mujer" <?= $datos['sexo'] === 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                    <option value="Hombre" <?= $datos['sexo'] === 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                    <option value="Otro" <?= $datos['sexo'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width:100%;">Guardar cambios</button>
        </form>
    </div>

    <div class="form-caja">
        <h2>Cambiar contraseña</h2>
        <form method="POST" action="perfil.php">
            <input type="hidden" name="accion" value="password">
            <div class="campo">
                <label>Contraseña actual *</label>
                <input type="password" name="password_actual" required>
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label>Nueva contraseña *</label>
                    <input type="password" name="password_nueva" required>
                </div>
                <div class="campo">
                    <label>Repetir nueva contraseña *</label>
                    <input type="password" name="password_nueva2" required>
                </div>
            </div>
            <button type="submit" class="btn" style="width:100%;">Actualizar contraseña</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
