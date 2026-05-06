<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.html");
    exit();
}

include("db.php");
$con = conectarDB();

/* =========================
   GUARDAR LIBRO
========================= */
if (isset($_POST['guardar_libro'])) {

    $titulo = $_POST['titulo'];
    $autor_id = $_POST['autor_id'];

    $sql = "INSERT INTO libros (titulo, autor_id)
            VALUES (:titulo, :autor_id)";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':titulo' => $titulo,
        ':autor_id' => $autor_id
    ]);
}

/* =========================
   ELIMINAR LIBRO
========================= */
if (isset($_GET['eliminar'])) {

    $id = $_GET['eliminar'];

    $sql = "DELETE FROM libros WHERE id=:id";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);
}

/* =========================
   REGISTRAR PRÉSTAMO
========================= */
if (isset($_POST['guardar_prestamo'])) {

    $usuario_id = $_SESSION['id'];
    $libro_id = $_POST['libro_id'];

    $sql = "INSERT INTO prestamos(usuario_id, libro_id, fecha_prestamo)
            VALUES(:usuario, :libro, NOW())";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':usuario' => $usuario_id,
        ':libro' => $libro_id
    ]);
}
?>

<!doctype html>
<html lang="es">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Biblioteca Digital</title>

<link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">

<style>

body{
    background:linear-gradient(135deg,#0d6efd,#6610f2,#d63384);
    background-size:300% 300%;
    animation:fondo 8s ease infinite;
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
}

@keyframes fondo{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.card-box{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.2);
}

.form-control{
    border-radius:12px;
}

.btn-custom{
    background:linear-gradient(135deg,#0d6efd,#6610f2);
    color:white;
    border:none;
    border-radius:12px;
}

.btn-custom:hover{
    color:white;
    transform:translateY(-2px);
}

.navbar-custom{
    background:rgba(0,0,0,.2);
    backdrop-filter:blur(10px);
}

</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-custom px-4">

<h4 class="text-white">
<i class="bi bi-book-fill"></i>
Biblioteca Digital
</h4>

<a href="logout.php" class="btn btn-light">
Salir
</a>

</nav>

<div class="container py-5">

<!-- AGREGAR LIBRO -->
<div class="row justify-content-center mb-5">

<div class="col-md-6">

<div class="card-box">

<h3 class="mb-3 text-center">
Agregar Libro
</h3>

<form method="POST">

<input
type="text"
name="titulo"
class="form-control mb-3"
placeholder="Nombre del libro"
required>

<select
name="autor_id"
class="form-control mb-3"
required>

<option value="">
Selecciona autor
</option>

<?php

$autores = $con->query("SELECT * FROM autores");

foreach($autores as $a){

echo "<option value='".$a['id']."'>".$a['nombre']."</option>";

}

?>

</select>

<button
name="guardar_libro"
class="btn btn-custom w-100">

Guardar Libro

</button>

</form>

</div>

</div>

</div>

<!-- TABLA LIBROS -->
<div class="card-box mb-5">

<h3 class="mb-3">
Libros Registrados
</h3>

<table class="table table-striped">

<thead class="table-primary">

<tr>
<th>ID</th>
<th>Título</th>
<th>Autor</th>
<th>Acción</th>
</tr>

</thead>

<tbody>

<?php

$libros = $con->query("
SELECT libros.*, autores.nombre AS autor
FROM libros
LEFT JOIN autores
ON libros.autor_id = autores.id
");

foreach($libros as $l){

?>

<tr>

<td><?php echo $l['id']; ?></td>

<td><?php echo $l['titulo']; ?></td>

<td><?php echo $l['autor']; ?></td>

<td>

<a
href="?eliminar=<?php echo $l['id']; ?>"
class="btn btn-danger btn-sm">

Eliminar

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- PRÉSTAMOS -->
<div class="row justify-content-center mb-5">

<div class="col-md-6">

<div class="card-box">

<h3 class="mb-3 text-center">
Registrar Préstamo
</h3>

<form method="POST">

<select
name="libro_id"
class="form-control mb-3"
required>

<option value="">
Selecciona libro
</option>

<?php

$libros = $con->query("SELECT * FROM libros");

foreach($libros as $libro){

echo "<option value='".$libro['id']."'>".$libro['titulo']."</option>";

}

?>

</select>

<button
name="guardar_prestamo"
class="btn btn-success w-100">

Prestar Libro

</button>

</form>

</div>

</div>

</div>

<!-- TABLA PRÉSTAMOS -->
<div class="card-box">

<h3 class="mb-3">
Préstamos
</h3>

<table class="table table-striped">

<thead class="table-success">

<tr>
<th>ID</th>
<th>Libro</th>
<th>Usuario</th>
<th>Fecha</th>
</tr>

</thead>

<tbody>

<?php

$prestamos = $con->query("
SELECT prestamos.*, libros.titulo, usuarios.nombre
FROM prestamos
JOIN libros ON prestamos.libro_id = libros.id
JOIN usuarios ON prestamos.usuario_id = usuarios.id
");

foreach($prestamos as $p){

?>

<tr>

<td><?php echo $p['id']; ?></td>

<td><?php echo $p['titulo']; ?></td>

<td><?php echo $p['nombre']; ?></td>

<td><?php echo $p['fecha_prestamo']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</body>
</html>
