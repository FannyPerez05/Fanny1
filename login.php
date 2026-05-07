if ($verify) {

    session_regenerate_id(true);

    $_SESSION['id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];

    // 🔥 NO dependas de sesión vieja
    if ($usuario['primer_login'] == 1) {

        $titulo = "Bienvenido";

        $update = $db->prepare("
            UPDATE usuarios
            SET primer_login = 0
            WHERE id = :id
        ");

        $update->execute([
            ':id' => $usuario['id']
        ]);

    } else {
        $titulo = "Bienvenido otra vez";
    }

    // 🔥 IMPORTANTE: guardar SOLO para esta sesión
    $_SESSION['titulo'] = $titulo;

    header("Location: dashboard.php");
    exit();
}
