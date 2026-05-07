<?php
session_start();
session_destroy();

// borrar cookie correctamente (MISMO PATH)
setcookie("usuario", "", time() - 3600, "/");

header("Location: index.html");
exit();
?>
