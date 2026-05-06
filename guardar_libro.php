<?php
include("db.php");

$titulo = $_POST['titulo'];
$categoria = $_POST['categoria'];
$anio = $_POST['anio'];
$autor_id = $_POST['autor_id'];

mysqli_query($conn,"INSERT INTO libros(titulo,categoria,anio,autor_id)
VALUES('$titulo','$categoria','$anio','$autor_id')");

header("Location: dashboard.php");
?>
