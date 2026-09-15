<?php
require 'includes/auth.php';
require 'config/db.php';
requiereLogin();
requiereAdmin();
$tituloPagina = 'Administración de usuarios';

$mensaje = '';
$tipoMensaje = '';

// --- Crear usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $nombre = limpiar($_POST['nombre'] ?? '');
    $apellidos = limpiar($_POST['apellidos'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $sexo = limpiar($_POST['sexo'] ?? '');
    $usuario = limpiar($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'user';

    if ($nombre === '' || $apellidos === '' || $email === '' || $usuario === '' || $password === '') {
        $mensaje = 'Rellena todos los campos obligatorios.';
        $tipoMensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El email no es válido.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT idUser FROM users_data WHERE email = ?");
        $stmt->execute([$email]);
        $stmt2 = $pdo->prepare("SELECT idLogin FROM users_login WHERE usuario = ?");
        $stmt2->execute([$usuario]);

        if ($stmt->fetch()) {
            $mensaje = 'Ya existe un usuario con ese email.';
            $tipoMensaje = 'error';
        } elseif ($stmt2->fetch()) {
            $mensaje = 'Ese nombre de usuario ya está en uso.';
            $tipoMensaje = 'error';
        } else {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo ?: 'Otro']);
            $idUser = $pdo->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users_login (idUser, usuario, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$idUser, $usuario, $hash, $rol]);
            $pdo->commit();

            $mensaje = 'Usuario creado correctamente.';
            $tipoMensaje = 'ok';
        }
    }
}

// --- Modificar usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idUser = (int)($_POST['idUser'] ?? 0);
    $nombre = limpiar($_POST['nombre'] ?? '');
    $apellidos = limpiar($_POST['apellidos'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $sexo = limpiar($_POST['sexo'] ?? '');
    $rol = $_POST['rol'] ?? 'user';
    $nuevaPassword = $_POST['password'] ?? '';

    if ($nombre === '' || $apellidos === '' || $email === '') {
        $mensaje = 'Rellena todos los campos obligatorios.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE users_data SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=? WHERE idUser=?");
        $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser]);

        if ($nuevaPassword !== '') {
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users_login SET rol=?, password=? WHERE idUser=?");
            $stmt->execute([$rol, $hash, $idUser]);
        } else {
            $stmt = $pdo->prepare("UPDATE users_login SET rol=? WHERE idUser=?");
            $stmt->execute([$rol, $idUser]);
        }

        $mensaje = 'Usuario actualizado correctamente.';
        $tipoMensaje = 'ok';
    }
}

// --- Borrar usuario ---
if (isset($_GET['borrar'])) {
    $idUser = (int)$_GET['borrar'];
    if ($idUser === (int)$_SESSION['idUser']) {
        $mensaje = 'No puedes borrar tu propio usuario mientras tienes la sesión iniciada.';
        $tipoMensaje = 'error';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users_data WHERE idUser = ?");
        $stmt->execute([$idUser]);
        $mensaje = 'Usuario borrado correctamente.';
        $tipoMensaje = 'ok';
    }
}

// Usuario a editar
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $idUser = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT d.*, l.usuario, l.rol FROM users_data d JOIN users_login l ON d.idUser = l.idUser WHERE d.idUser = ?");
    $stmt->execute([$idUser]);
    $usuarioEditar = $stmt->fetch();
}

// Listado de usuarios
$usuarios = $pdo->query(
    "SELECT d.*, l.usuario, l.rol FROM users_data d JOIN users_login l ON d.idUser = l.idUser ORDER BY d.idUser ASC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="contenedor">
    <div class="panel-titulo">
        <h1 style="color:var(--verde-oscuro);">Administración de usuarios</h1>
    </div>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="form-caja" style="max-width:720px;">
        <h2><?= $usuarioEditar ? 'Modificar usuario' : 'Crear nuevo usuario' ?></h2>
        <form method="POST" action="usuarios-administracion.php">
            <input type="hidden" name="accion" value="<?= $usuarioEditar ? 'editar' : 'crear' ?>">
            <?php if ($usuarioEditar): ?>
                <input type="hidden" name="idUser" value="<?= $usuarioEditar['idUser'] ?>">
            <?php endif; ?>

            <div class="fila-2">
                <div class="campo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['nombre']) : '' ?>" required>
                </div>
                <div class="campo">
                    <label>Apellidos *</label>
                    <input type="text" name="apellidos" value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['apellidos']) : '' ?>" required>
                </div>
            </div>
            <div class="campo">
                <label>Email *</label>
                <input type="email" name="email" value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['email']) : '' ?>" required>
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['telefono']) : '' ?>">
                </div>
                <div class="campo">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="<?= $usuarioEditar ? $usuarioEditar['fecha_nacimiento'] : '' ?>">
                </div>
            </div>
            <div class="campo">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['direccion']) : '' ?>">
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="Mujer" <?= ($usuarioEditar && $usuarioEditar['sexo'] === 'Mujer') ? 'selected' : '' ?>>Mujer</option>
                        <option value="Hombre" <?= ($usuarioEditar && $usuarioEditar['sexo'] === 'Hombre') ? 'selected' : '' ?>>Hombre</option>
                        <option value="Otro" <?= ($usuarioEditar && $usuarioEditar['sexo'] === 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="campo">
                    <label>Rol *</label>
                    <select name="rol">
                        <option value="user" <?= ($usuarioEditar && $usuarioEditar['rol'] === 'user') ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= ($usuarioEditar && $usuarioEditar['rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>

            <?php if (!$usuarioEditar): ?>
                <div class="campo">
                    <label>Nombre de usuario (login) *</label>
                    <input type="text" name="usuario" required>
                </div>
            <?php else: ?>
                <p style="color:var(--gris-medio);">Usuario de acceso: <strong><?= htmlspecialchars($usuarioEditar['usuario']) ?></strong> (no editable)</p>
            <?php endif; ?>

            <div class="campo">
                <label><?= $usuarioEditar ? 'Nueva contraseña (dejar en blanco para no cambiarla)' : 'Contraseña *' ?></label>
                <input type="password" name="password" <?= $usuarioEditar ? '' : 'required' ?>>
            </div>

            <button type="submit" class="btn" style="width:100%;"><?= $usuarioEditar ? 'Guardar cambios' : 'Crear usuario' ?></button>
            <?php if ($usuarioEditar): ?>
                <div class="enlace-form"><a href="usuarios-administracion.php">Cancelar edición</a></div>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="color:var(--verde-oscuro);margin-top:2.5rem;">Listado de usuarios</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['idUser'] ?></td>
                    <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                    <td><?= htmlspecialchars($u['rol']) ?></td>
                    <td class="acciones">
                        <a href="usuarios-administracion.php?editar=<?= $u['idUser'] ?>" class="editar">Editar</a>
                        <a href="usuarios-administracion.php?borrar=<?= $u['idUser'] ?>" class="borrar borrar-confirm"
                           data-mensaje="¿Seguro que deseas borrar este usuario? Se borrarán también sus citas y noticias.">Borrar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
