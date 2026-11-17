
<?php
// Configurar e iniciar la sesión
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Conexión con la base de datos
require_once __DIR__ . '/config/conexion.php';

// Si ya inició sesión, ir al panel principal
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$correo = "";

// Procesar formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = is_string($_POST["correo"] ?? null)
        ? strtolower(trim($_POST["correo"]))
        : "";

    $password = is_string($_POST["password"] ?? null)
        ? $_POST["password"]
        : "";

    // Validar los datos recibidos
    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 190 ||
        $password === ""
    ) {
        $error = "Correo electrónico o contraseña incorrectos.";
    } else {

        try {
            // Buscar al usuario por su correo
            $sql = "SELECT id, nombre, correo, password
                    FROM usuarios
                    WHERE correo = :correo
                    LIMIT 1";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                "correo" => $correo
            ]);

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            // Verificar las credenciales
            if (
                $usuario &&
                password_verify($password, $usuario["password"])
            ) {

                // Renovar identificador de sesión
                session_regenerate_id(true);

                // Guardar información del usuario en sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                $_SESSION["usuario_correo"] = $usuario["correo"];

                // Redirigir al panel principal
                header("Location: index.php");
                exit;

            } else {
                $error = "Correo electrónico o contraseña incorrectos.";
            }

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $error = "No se pudo procesar el inicio de sesión.";
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

    <title>Iniciar sesión - Agenda de Contactos</title>
</head>
<body>

    <h1>Agenda Personal de Contactos</h1>
    <h2>Inicio de sesión</h2>

    <?php if ($error !== ""): ?>
        <p role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <label for="correo">Correo electrónico:</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="190"
            value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="username"
            required
        >

        <br><br>

        <label for="password">Contraseña:</label>
        <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <br><br>

        <button type="submit">Iniciar sesión</button>

    </form>

    <p>
        ¿No tienes una cuenta?
        <a href="registro.php">Registrarse</a>
    </p>

</body>
</html>
