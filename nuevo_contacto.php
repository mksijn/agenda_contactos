<?php
// Iniciar la sesión
ini_set('session.use_strict_mode', '1');
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Evitar almacenar la página privada en caché
header("Cache-Control: no-store, private");

// Conectar con la base de datos
require_once __DIR__ . '/config/conexion.php';

// Generar un token de seguridad
if (empty($_SESSION["csrf_contacto"])) {
    $_SESSION["csrf_contacto"] = bin2hex(random_bytes(32));
}

// Variables del formulario
$errores = [];
$nombre = "";
$telefono = "";
$correo = "";
$ciudad = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Comprobar el token CSRF
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_contacto"], $token)
    ) {
        http_response_code(403);
        exit("Solicitud no válida.");
    }

    // Obtener los datos enviados
    $nombre = is_string($_POST["nombre"] ?? null)
        ? trim($_POST["nombre"]) : "";

    $telefono = is_string($_POST["telefono"] ?? null)
        ? trim($_POST["telefono"]) : "";

    $correo = is_string($_POST["correo"] ?? null)
        ? strtolower(trim($_POST["correo"])) : "";

    $ciudad = is_string($_POST["ciudad"] ?? null)
        ? trim($_POST["ciudad"]) : "";

    // Validaciones del servidor
    if ($nombre === "" || strlen($nombre) > 120) {
        $errores[] = "Ingrese un nombre válido.";
    }

    if (
        !preg_match('/^\+?[0-9()\s.-]{7,30}$/', $telefono)
    ) {
        $errores[] = "Ingrese un teléfono válido.";
    }

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 190
    ) {
        $errores[] = "Ingrese un correo electrónico válido.";
    }

    if ($ciudad === "" || strlen($ciudad) > 100) {
        $errores[] = "Ingrese una ciudad válida.";
    }

    // Guardar solo si todos los datos son válidos
    if (empty($errores)) {

        try {
            // Identificar al usuario autenticado
            $id_usuario = $_SESSION["usuario_id"];

            // Preparar la consulta SQL
            $sql = "INSERT INTO contactos
                    (id_usuario, nombre, telefono, correo, ciudad)
                    VALUES
                    (:id_usuario, :nombre, :telefono, :correo, :ciudad)";

            $consulta = $conexion->prepare($sql);

            // Ejecutar el registro
            $consulta->execute([
                "id_usuario" => $id_usuario,
                "nombre" => $nombre,
                "telefono" => $telefono,
                "correo" => $correo,
                "ciudad" => $ciudad
            ]);

            // Renovar el token tras guardar correctamente
            unset($_SESSION["csrf_contacto"]);

            // Redirigir para evitar registros duplicados
            header("Location: nuevo_contacto.php?guardado=1");
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errores[] = "No se pudo guardar el contacto.";
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

    <title>Nuevo contacto - Agenda de Contactos</title>
</head>
<body>

    <h1>Agenda Personal de Contactos</h1>
    <h2>Registrar nuevo contacto</h2>

    <?php if (($_GET["guardado"] ?? "") === "1"): ?>
        <p role="status">
            Contacto registrado correctamente.
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

    <form action="nuevo_contacto.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION["csrf_contacto"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="120"
            value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br><br>

        <label for="telefono">Teléfono:</label>
        <input
            type="tel"
            id="telefono"
            name="telefono"
            maxlength="30"
            value="<?= htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8') ?>"
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
            required
        >

        <br><br>

        <label for="ciudad">Ciudad:</label>
        <input
            type="text"
            id="ciudad"
            name="ciudad"
            maxlength="100"
            value="<?= htmlspecialchars($ciudad, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br><br>

        <button type="submit">Guardar contacto</button>

    </form>

    <p>
        <a href="index.php">Volver al panel principal</a>
    </p>

</body>
</html>
