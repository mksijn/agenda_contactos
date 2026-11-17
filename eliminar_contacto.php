<?php
// ========================================
// AGENDA PERSONAL DE CONTACTOS
// Eliminación segura de contactos
// ========================================

// Iniciar sesión
ini_set('session.use_strict_mode', '1');
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Permitir únicamente solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

// Validar el token de seguridad CSRF
$token_recibido = $_POST["csrf_token"] ?? "";

if (
    !isset($_SESSION["csrf_eliminar"]) ||
    !is_string($token_recibido) ||
    !hash_equals($_SESSION["csrf_eliminar"], $token_recibido)
) {
    http_response_code(403);
    exit("Solicitud no válida.");
}

// Obtener y validar el identificador del contacto
$id_contacto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$id_contacto) {
    http_response_code(400);
    exit("Identificador de contacto no válido.");
}

// Obtener el identificador del usuario autenticado
$id_usuario = (int) $_SESSION["usuario_id"];

// Conectar con la base de datos
require_once __DIR__ . '/config/conexion.php';

try {

    // Eliminar únicamente contactos del usuario autenticado
    $sql = "DELETE FROM contactos
            WHERE id = :id
            AND id_usuario = :id_usuario";

    $consulta = $conexion->prepare($sql);

    $consulta->bindValue(
        ":id",
        $id_contacto,
        PDO::PARAM_INT
    );

    $consulta->bindValue(
        ":id_usuario",
        $id_usuario,
        PDO::PARAM_INT
    );

    $consulta->execute();

    // Comprobar si se eliminó el contacto
    if ($consulta->rowCount() > 0) {
        header("Location: index.php?eliminado=1");
    } else {
        header("Location: index.php?eliminado=0");
    }

    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("No se pudo eliminar el contacto.");
}
