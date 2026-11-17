
<?php
// ========================================
// AGENDA PERSONAL DE CONTACTOS
// Edición y actualización de contactos
// ========================================

// Iniciar sesión
ini_set('session.use_strict_mode', '1');
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Evitar almacenar la página privada en caché
header("Cache-Control: no-store, private");

// Conexión con la base de datos
require_once __DIR__ . '/config/conexion.php';

// Obtener el ID del contacto desde la URL
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$id) {
    http_response_code(400);
    exit("Identificador de contacto no válido.");
}

// Obtener el usuario autenticado
$id_usuario = (int) $_SESSION["usuario_id"];

// Variables para mensajes
$errores = [];

// ========================================
// CONSULTAR EL CONTACTO
// ========================================

try {

    $sql = "SELECT id, nombre, telefono, correo, ciudad
            FROM contactos
            WHERE id = :id
            AND id_usuario = :id_usuario
            LIMIT 1";

    $consulta = $conexion->prepare($sql);

    $consulta->bindValue(
        ":id",
        $id,
        PDO::PARAM_INT
    );

    $consulta->bindValue(
        ":id_usuario",
        $id_usuario,
        PDO::PARAM_INT
    );

    $consulta->execute();

    $contacto = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$contacto) {
        http_response_code(404);
        exit("Contacto no encontrado.");
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("No se pudo consultar el contacto.");
}

// Generar token CSRF
if (empty($_SESSION["csrf_editar"])) {
    $_SESSION["csrf_editar"] = bin2hex(random_bytes(32));
}

// ========================================
// ACTUALIZAR EL CONTACTO
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validar token de seguridad
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_editar"], $token)
    ) {
        http_response_code(403);
        exit("Solicitud no válida.");
    }

    // Obtener información del formulario
    $nombre = is_string($_POST["nombre"] ?? null)
        ? trim($_POST["nombre"]) : "";

    $telefono = is_string($_POST["telefono"] ?? null)
        ? trim($_POST["telefono"]) : "";

    $correo = is_string($_POST["correo"] ?? null)
        ? strtolower(trim($_POST["correo"])) : "";

    $ciudad = is_string($_POST["ciudad"] ?? null)
        ? trim($_POST["ciudad"]) : "";

    // Validar nombre
    if ($nombre === "" || strlen($nombre) > 120) {
        $errores[] = "Ingrese un nombre válido.";
    }

    // Validar teléfono
    if (!preg_match('/^\+?[0-9()\s.-]{7,30}$/', $telefono)) {
        $errores[] = "Ingrese un teléfono válido.";
    }

    // Validar correo electrónico
    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 190
    ) {
        $errores[] = "Ingrese un correo electrónico válido.";
    }

    // Validar ciudad
    if ($ciudad === "" || strlen($ciudad) > 100) {
        $errores[] = "Ingrese una ciudad válida.";
    }

    // Actualizar solo si no existen errores
    if (empty($errores)) {

        try {

            $sql = "UPDATE contactos
                    SET nombre = :nombre,
                        telefono = :telefono,
                        correo = :correo,
                        ciudad = :ciudad
                    WHERE id = :id
                    AND id_usuario = :id_usuario";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                "nombre" => $nombre,
                "telefono" => $telefono,
                "correo" => $correo,
                "ciudad" => $ciudad,
                "id" => $id,
                "id_usuario" => $id_usuario
            ]);

            // Renovar token de seguridad
            $_SESSION["csrf_editar"] = bin2hex(random_bytes(32));

            // Redirigir después de actualizar
            header(
                "Location: editar_contacto.php?id="
                . $id . "&actualizado=1"
            );
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errores[] = "No se pudo actualizar el contacto.";
        }
    }

    // Mantener los datos ingresados si hubo un error
    $contacto["nombre"] = $nombre;
    $contacto["telefono"] = $telefono;
    $contacto["correo"] = $correo;
    $contacto["ciudad"] = $ciudad;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar contacto - Agenda de Contactos</title>
</head>
<body>

    <h1>Agenda Personal de Contactos</h1>
    <h2>Editar contacto</h2>

    <?php if (($_GET["actualizado"] ?? "") === "1"): ?>
        <p role="status">
            Contacto actualizado correctamente.
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

    <form
        action="editar_contacto.php?id=<?= (int) $id ?>"
        method="POST"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION["csrf_editar"],
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
            value="<?= htmlspecialchars(
                $contacto["nombre"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>

        <label for="telefono">Teléfono:</label>
        <input
            type="tel"
            id="telefono"
            name="telefono"
            maxlength="30"
            value="<?= htmlspecialchars(
                $contacto["telefono"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>

        <label for="correo">Correo electrónico:</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="190"
            value="<?= htmlspecialchars(
                $contacto["correo"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>

        <label for="ciudad">Ciudad:</label>
        <input
            type="text"
            id="ciudad"
            name="ciudad"
            maxlength="100"
            value="<?= htmlspecialchars(
                $contacto["ciudad"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>

        <button type="submit">
            Actualizar contacto
        </button>

    </form>

    <p>
        <a href="index.php">
            Volver al panel principal
        </a>
    </p>

</body>
</html>
