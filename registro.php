<?php
require 'includes/auth.php';
require 'config/db.php';
$tituloPagina = 'Registro';

$errores = [];
$exito = false;

// Valores para repoblar el formulario en caso de error
$valores = [
    'nombre' => '', 'apellidos' => '', 'email' => '', 'telefono' => '',
    'fecha_nacimiento' => '', 'direccion' => '', 'sexo' => '', 'usuario' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($valores as $campo => $v) {
        $valores[$campo] = limpiar($_POST[$campo] ?? '');
    }
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // --- Validación de campos obligatorios ---
    if ($valores['nombre'] === '')            $errores[] = 'El nombre es obligatorio.';
    if ($valores['apellidos'] === '')          $errores[] = 'Los apellidos son obligatorios.';
    if ($valores['email'] === '')              $errores[] = 'El email es obligatorio.';
    elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if ($valores['telefono'] === '')           $errores[] = 'El teléfono es obligatorio.';
    if ($valores['fecha_nacimiento'] === '')   $errores[] = 'La fecha de nacimiento es obligatoria.';
    if ($valores['usuario'] === '')            $errores[] = 'El nombre de usuario es obligatorio.';
    if ($password === '')                      $errores[] = 'La contraseña es obligatoria.';
    if ($password !== $password2)              $errores[] = 'Las contraseñas no coinciden.';
    if ($password !== '' && strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';

    // --- Comprobar duplicados en BD ---
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT idUser FROM users_data WHERE email = ?");
        $stmt->execute([$valores['email']]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe una cuenta registrada con ese email.';
        }

        $stmt = $pdo->prepare("SELECT idLogin FROM users_login WHERE usuario = ?");
        $stmt->execute([$valores['usuario']]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese nombre de usuario ya está en uso.';
        }
    }

    // --- Inserción en BD ---
    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $valores['nombre'], $valores['apellidos'], $valores['email'],
                $valores['telefono'], $valores['fecha_nacimiento'],
                $valores['direccion'], $valores['sexo'] ?: 'Otro'
            ]);
            $idUser = $pdo->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users_login (idUser, usuario, password, rol) VALUES (?, ?, ?, 'user')"
            );
            $stmt->execute([$idUser, $valores['usuario'], $hash]);

            $pdo->commit();
            $exito = true;

            header('refresh:2;url=login.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = 'No se pudo completar el registro. Inténtalo de nuevo.';
        }
    }
}

include 'includes/header.php';
?>

<div class="contenedor">
    <div class="form-caja">
        <h2>Crear una cuenta</h2>

        <?php if ($exito): ?>
            <div class="mensaje ok">¡Registro completado correctamente! Serás redirigido al inicio de sesión...</div>
        <?php else: ?>

            <?php if (!empty($errores)): ?>
                <div class="mensaje error">
                    <ul style="margin:0;padding-left:1.2rem;">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="form-registro" method="POST" action="registro.php">
                <div class="fila-2">
                    <div class="campo">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" value="<?= $valores['nombre'] ?>" required>
                    </div>
                    <div class="campo">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" id="apellidos" name="apellidos" value="<?= $valores['apellidos'] ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= $valores['email'] ?>" required>
                </div>

                <div class="fila-2">
                    <div class="campo">
                        <label for="telefono">Teléfono *</label>
                        <input type="text" id="telefono" name="telefono" value="<?= $valores['telefono'] ?>" required>
                    </div>
                    <div class="campo">
                        <label for="fecha_nacimiento">Fecha de nacimiento *</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $valores['fecha_nacimiento'] ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?= $valores['direccion'] ?>">
                </div>

                <div class="campo">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="Mujer" <?= $valores['sexo'] === 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                        <option value="Hombre" <?= $valores['sexo'] === 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                        <option value="Otro" <?= $valores['sexo'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #e0e0e0;">

                <div class="campo">
                    <label for="usuario">Nombre de usuario *</label>
                    <input type="text" id="usuario" name="usuario" value="<?= $valores['usuario'] ?>" required>
                </div>

                <div class="fila-2">
                    <div class="campo">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="campo">
                        <label for="password2">Repetir contraseña *</label>
                        <input type="password" id="password2" name="password2" required>
                    </div>
                </div>

                <button type="submit" class="btn" style="width:100%;">Registrarme</button>
            </form>

            <div class="enlace-form">
                ¿Ya tienes cuenta? <a href="login.php" style="color:var(--azul-cielo);font-weight:600;">Inicia sesión aquí</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
