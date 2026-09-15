<?php
require 'includes/auth.php';
require 'config/db.php';
$tituloPagina = 'Iniciar sesión';

if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$usuarioVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioVal = limpiar($_POST['usuario'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($usuarioVal === '' || $password === '') {
        $error = 'Debes rellenar usuario y contraseña.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT l.idLogin, l.idUser, l.usuario, l.password, l.rol, d.nombre
             FROM users_login l
             JOIN users_data d ON l.idUser = d.idUser
             WHERE l.usuario = ?"
        );
        $stmt->execute([$usuarioVal]);
        $fila = $stmt->fetch();

        if ($fila && password_verify($password, $fila['password'])) {
            $_SESSION['idUser']  = $fila['idUser'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['rol']     = $fila['rol'];
            $_SESSION['nombre']  = $fila['nombre'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}

include 'includes/header.php';
?>

<div class="contenedor">
    <div class="form-caja">
        <h2>Iniciar sesión</h2>

        <?php if ($error): ?>
            <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registrado'])): ?>
            <div class="mensaje ok">Registro completado. Ya puedes iniciar sesión.</div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuarioVal) ?>" required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width:100%;">Entrar</button>
        </form>

        <div class="enlace-form">
            ¿No tienes cuenta? <a href="registro.php" style="color:var(--azul-cielo);font-weight:600;">Regístrate aquí</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
