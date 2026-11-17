
<?php
require_once __DIR__ . '/config/conexion.php';

$errores = [];
$mensaje = "";
$nombre = "";
$correo = "";

// Procesar el formulario cuando se presione Registrarse
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Obtener los datos ingresados por el usuario
    $nombre = is_string($_POST["nombre"] ?? null)
        ? trim($_POST["nombre"]) : "";

    $correo = is_string($_POST["correo"] ?? null)
        ? strtolower(trim($_POST["correo"])) : "";

    $password = is_string($_POST["password"] ?? null)
        ? $_POST["password"] : "";

    $confirmar_password =
        is_string($_POST["confirmar_password"] ?? null)
        ? $_POST["confirmar_password"] : "";

    // Validaciones del lado del servidor
    if ($nombre === "" || strlen($nombre) > 120) {
        $errores[] = "Ingrese un nombre válido.";
    }

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 190
    ) {
        $errores[] = "Ingrese un correo electrónico válido.";
    }

    if (strlen($password) < 8 || strlen($password) > 72) {
        $errores[] = "La contraseña debe tener entre 8 y 72 bytes.";
    }

    if ($password !== $confirmar_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Continuar únicamente si no existen errores
    if (empty($errores)) {

        try {
            // Comprobar si el correo ya está registrado
            $sql = "SELECT id FROM usuarios
                    WHERE correo = :correo LIMIT 1";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([
                "correo" => $correo
            ]);

            if ($consulta->fetchColumn() !== false) {

                $errores[] = "El correo electrónico ya está registrado.";

            } else {

                // Generar un hash seguro de la contraseña
                $password_segura = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Registrar el nuevo usuario
                $sql = "INSERT INTO usuarios
                        (nombre, correo, password)
                        VALUES (:nombre, :correo, :password)";

                $consulta = $conexion->prepare($sql);

                $consulta->execute([
                    "nombre" => $nombre,
                    "correo" => $correo,
                    "password" => $password_segura
                ]);

                $mensaje = "Usuario registrado correctamente.";

                $nombre = "";
                $correo = "";
            }

        } catch (PDOException $e) {

            // Registrar el error técnico sin mostrarlo al usuario
            error_log($e->getMessage());

            if ($e->getCode() === "23000") {
                $errores[] = "El correo electrónico ya está registrado.";
            } else {
                $errores[] = "No se pudo completar el registro.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registro de usuario - Agenda de Contactos</title>
</head>
<body>

    <h1>Agenda Personal de Contactos</h1>
    <h2>Registro de usuario</h2>

    <?php if ($mensaje !== ""): ?>
        <p role="status">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="registro.php" method="POST">

        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="120"
            value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="name"
            required
        >

        <br><br>

        <label for="correo">Correo electrónico:</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="190"
            value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="email"
            required
        >

        <br><br>

        <label for="password">Contraseña:</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <br><br>

        <label for="confirmar_password">
            Confirmar contraseña:
        </label>
        <input
            type="password"
            id="confirmar_password"
            name="confirmar_password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <br><br>

        <button type="submit">Registrarse</button>

    </form>

    <p>
        ¿Ya tienes una cuenta?
        <a href="login.php">Iniciar sesión</a>
    </p>

</body>
</html>
