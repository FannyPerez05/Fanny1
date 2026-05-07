<?php
session_start();
require_once 'db.php';

$email = $_POST['email'];
$pwd = $_POST['pwd'];

$db = conectarDB();

try {

    $sql = "SELECT id, nombre, password, email, primer_login
            FROM usuarios
            WHERE email = :email";

    $query = $db->prepare($sql);

    $query->execute([
        ':email' => $email
    ]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {

        $verify = password_verify($pwd, $usuario['password']);

        if ($verify) {

            session_regenerate_id(true);

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];

            if ($usuario['primer_login'] == 1) {

                $_SESSION['titulo'] = "Bienvenido";

                $update = $db->prepare("
                    UPDATE usuarios
                    SET primer_login = 0
                    WHERE id = :id
                ");

                $update->execute([
                    ':id' => $usuario['id']
                ]);

            } else {
                $_SESSION['titulo'] = "Bienvenido otra vez";
            }

            header("Location: dashboard.php");
            exit();

        } else {
            echo "Contraseña incorrecta";
        }

    } else {
        echo "Usuario no encontrado";
    }

} catch(PDOException $e) {
    echo $e->getMessage();
}
?>
