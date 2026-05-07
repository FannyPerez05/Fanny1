<?php

session_start();

require_once 'db.php';

$email = $_POST['email'];
$pwd = $_POST['pwd'];

$db = conectarDB();

try {

    $sql = "SELECT id, nombre, password, email
            FROM usuarios
            WHERE email = :email";

    $query = $db->prepare($sql);

    $query->execute([
        'email' => $email
    ]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if($usuario){

        $verify = password_verify($pwd, $usuario['password']);

        if($verify){

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];

            // cookie opcional SOLO para mostrar nombre
            setcookie("usuario", $nombre, time() + (86400 * 7), "/");

            header("Location: dashboard.php");
            exit();

        }else{

            echo "Contraseña incorrecta";

        }

    }else{

        echo "Usuario no encontrado";

    }

}catch(PDOException $e){

    echo $e->getMessage();

}
?>
