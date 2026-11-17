
<?php
// Iniciar la sesión existente
ini_set('session.use_strict_mode', '1');
session_start();

// Permitir el cierre de sesión solo mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Comprobar que la solicitud sea válida
$token = $_POST["csrf_token"] ?? "";

if (
    !isset($_SESSION["usuario_id"], $_SESSION["csrf_logout"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_logout"], $token)
) {
    http_response_code(403);
    exit("Solicitud no válida.");
}

// Eliminar las variables de sesión
$_SESSION = [];

// Eliminar la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $parametros["path"],
        "domain" => $parametros["domain"],
        "secure" => $parametros["secure"],
        "httponly" => $parametros["httponly"],
        "samesite" => $parametros["samesite"] ?? "Lax"
    ]);
}

// Destruir la sesión en el servidor
session_destroy();

// Redirigir al inicio de sesión
header("Location: login.php");
exit;
