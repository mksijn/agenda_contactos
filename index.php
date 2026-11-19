
<?php
// ========================================
// AGENDA PERSONAL DE CONTACTOS
// Panel principal del usuario
// ========================================

// Iniciar la sesión
ini_set('session.use_strict_mode', '1');
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Evitar que el navegador almacene el panel en caché
header("Cache-Control: no-store, private");

// Conectar con la base de datos
require_once __DIR__ . '/config/conexion.php';

// Obtener información del usuario autenticado
$id_usuario = (int) $_SESSION["usuario_id"];
$nombre = $_SESSION["usuario_nombre"];
$correo = $_SESSION["usuario_correo"];

// Generar token de seguridad para cerrar sesión
if (empty($_SESSION["csrf_logout"])) {
    $_SESSION["csrf_logout"] = bin2hex(random_bytes(32));
}

// Generar token de seguridad para eliminar contactos
if (empty($_SESSION["csrf_eliminar"])) {
    $_SESSION["csrf_eliminar"] = bin2hex(random_bytes(32));
}

// ========================================
// MEJORA A: BUSQUEDA DE CONTACTOS
// ========================================

// Obtener el texto ingresado en el formulario de búsqueda
$busqueda = "";

if (isset($_GET["buscar"]) && is_string($_GET["buscar"])) {
    $busqueda = trim($_GET["buscar"]);
}

// ========================================
// CONSULTAR LOS CONTACTOS DEL USUARIO
// ========================================

$contactos = [];
$error_contactos = "";

try {

    // Consultar únicamente los contactos del usuario autenticado
    $sql = "SELECT id, nombre, telefono, correo, ciudad
            FROM contactos
            WHERE id_usuario = :id_usuario";

    // Si se ingresó un texto, buscar en tres campos
    if ($busqueda !== "") {

        $sql .= " AND (
                    nombre LIKE :buscar_nombre
                    OR correo LIKE :buscar_correo
                    OR ciudad LIKE :buscar_ciudad
                  )";
    }

    // Mantener el orden alfabético por nombre
    $sql .= " ORDER BY nombre ASC";

    // Preparar la consulta
    $consulta = $conexion->prepare($sql);

    // Asociar el identificador del usuario autenticado
    $consulta->bindValue(
        ":id_usuario",
        $id_usuario,
        PDO::PARAM_INT
    );

    // Asociar los valores de búsqueda cuando corresponda
    if ($busqueda !== "") {

        // Los porcentajes permiten encontrar coincidencias parciales
        $texto_busqueda = "%" . $busqueda . "%";

        $consulta->bindValue(
            ":buscar_nombre",
            $texto_busqueda,
            PDO::PARAM_STR
        );

        $consulta->bindValue(
            ":buscar_correo",
            $texto_busqueda,
            PDO::PARAM_STR
        );

        $consulta->bindValue(
            ":buscar_ciudad",
            $texto_busqueda,
            PDO::PARAM_STR
        );
    }

    // Ejecutar la consulta
    $consulta->execute();

    // Obtener los contactos encontrados
    $contactos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());

    $error_contactos = "No se pudieron cargar los contactos.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Panel principal - Agenda de Contactos</title>
</head>

<body>

    <!-- Encabezado principal -->
    <h1>Agenda Personal de Contactos</h1>

    <h2>Panel principal</h2>

    <!-- Información del usuario autenticado -->
    <h3>
        Bienvenido,
        <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
    </h3>

    <p>
        <strong>Correo electrónico:</strong>

        <?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <hr>

    <!-- Sección de contactos -->
    <h2>Mis contactos</h2>

    <p>
        Desde esta sección podrás administrar
        tus contactos personales.
    </p>

    <!-- Enlace para registrar contactos -->
    <p>
        <a href="nuevo_contacto.php">
            Agregar nuevo contacto
        </a>
    </p>

    <!-- ========================================
         MEJORA A: FORMULARIO DE BUSQUEDA
         ======================================== -->

    <h3>Buscar contactos</h3>

    <form action="index.php" method="GET">

        <label for="buscar">
            Buscar por nombre, correo o ciudad:
        </label>

        <input
            type="search"
            id="buscar"
            name="buscar"
            maxlength="190"
            placeholder="Escribe tu búsqueda"
            value="<?= htmlspecialchars(
                $busqueda,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <button type="submit">
            Buscar
        </button>

        <!-- Permitir mostrar nuevamente todos los contactos -->
        <?php if ($busqueda !== ""): ?>

            <a href="index.php">
                Limpiar búsqueda
            </a>

        <?php endif; ?>

    </form>

    <hr>

    <!-- Mensaje después de eliminar un contacto -->
    <?php if (($_GET["eliminado"] ?? "") === "1"): ?>

        <p role="status">
            Contacto eliminado correctamente.
        </p>

    <?php elseif (($_GET["eliminado"] ?? "") === "0"): ?>

        <p role="alert">
            No se eliminó el contacto.
            Verifica que exista y te pertenezca.
        </p>

    <?php endif; ?>

    <!-- Mostrar errores de consulta -->
    <?php if ($error_contactos !== ""): ?>

        <p role="alert">
            <?= htmlspecialchars(
                $error_contactos,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    <!-- Mostrar mensaje cuando no hay resultados -->
    <?php elseif (empty($contactos)): ?>

        <?php if ($busqueda !== ""): ?>

            <p>
                No se encontraron contactos
                que coincidan con la búsqueda.
            </p>

        <?php else: ?>

            <p>
                Todavía no tienes contactos registrados.
            </p>

        <?php endif; ?>

    <!-- Mostrar tabla de contactos -->
    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Correo electrónico</th>
                    <th scope="col">Ciudad</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($contactos as $contacto): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $contacto["nombre"],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $contacto["telefono"],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $contacto["correo"],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $contacto["ciudad"],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <!-- Acciones del contacto -->
                        <td>

                            <!-- Formulario para editar -->
                            <form
                                action="editar_contacto.php"
                                method="GET"
                                style="display: inline;"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $contacto["id"] ?>"
                                >

                                <button type="submit">
                                    Editar
                                </button>

                            </form>

                            <!-- Formulario para eliminar -->
                            <form
                                action="eliminar_contacto.php"
                                method="POST"
                                style="display: inline;"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este contacto? Esta acción no se puede deshacer.');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $contacto["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                        $_SESSION["csrf_eliminar"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <button type="submit">
                                    Eliminar
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <hr>

    <!-- Cierre seguro de sesión -->
    <form action="logout.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION["csrf_logout"],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <button type="submit">
            Cerrar sesión
        </button>

    </form>

</body>
</html>
