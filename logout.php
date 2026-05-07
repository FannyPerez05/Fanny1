<?php
session_start();

// borrar todo de sesión
$_SESSION = [];

// destruir sesión
session_destroy();

// borrar cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

setcookie("usuario", "", time() - 3600, "/");

header("Location: index.html");
exit();
?>
