
<?php
// ========================================
// AGENDA PERSONAL DE CONTACTOS
// Ejemplo de configuración de MySQL
// ========================================

// Datos de conexión
$host = "localhost";
$base_datos = "agenda_contactos";
$usuario = "USUARIO_BD";
$contrasena = "CLAVE_BD";

try {

    // Crear conexión mediante PDO
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("No se pudo conectar con la base de datos.");
}
