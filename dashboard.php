<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.html");
    exit();
}

include("db.php");

$con = conectarDB();

/* =========================
   NOMBRE USUARIO
========================= */

$nombreCompleto = $_SESSION['nombre'];
$primerNombre = explode(" ", $nombreCompleto)[0];

/* =========================
   GUARDAR AUTOR
========================= */

if(isset($_POST['guardar_autor'])){

    $nombre_autor = $_POST['nombre_autor'];

    $sql = "INSERT INTO autores(nombre)
            VALUES(:nombre)";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':nombre' => $nombre_autor
    ]);
}

/* =========================
   GUARDAR LIBRO
========================= */

if(isset($_POST['guardar_libro'])){

    $titulo = $_POST['titulo'];
    $autor_id = $_POST['autor_id'];

    $sql = "INSERT INTO libros(titulo, autor_id, disponible)
            VALUES(:titulo, :autor_id, 1)";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':titulo' => $titulo,
        ':autor_id' => $autor_id
    ]);
}

/* =========================
   ELIMINAR LIBRO
========================= */

if(isset($_GET['eliminar'])){

    $id = $_GET['eliminar'];

    $sql = "DELETE FROM libros
            WHERE id = :id";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);
}

/* =========================
   REGISTRAR PRESTAMO
========================= */

if(isset($_POST['guardar_prestamo'])){

    $usuario_id = $_SESSION['id'];
    $libro_id = $_POST['libro_id'];

    // Guardar préstamo
    $sql = "INSERT INTO prestamos(usuario_id, libro_id, fecha_prestamo)
            VALUES(:usuario, :libro, NOW())";

    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':usuario' => $usuario_id,
        ':libro' => $libro_id
    ]);

    // Cambiar disponibilidad
    $sql2 = "UPDATE libros
             SET disponible = 0
             WHERE id = :id";

    $stmt2 = $con->prepare($sql2);

    $stmt2->execute([
        ':id' => $libro_id
    ]);
}

/* =========================
   DEVOLVER LIBRO
========================= */

if(isset($_GET['devolver'])){

    $libro_id = $_GET['devolver'];
    $usuario_id = $_SESSION['id'];

    // Verificar que el préstamo pertenezca al usuario
    $verificar = "SELECT * FROM prestamos
                  WHERE libro_id = :libro
                  AND usuario_id = :usuario";

    $stmtVerificar = $con->prepare($verificar);

    $stmtVerificar->execute([
        ':libro' => $libro_id,
        ':usuario' => $usuario_id
    ]);

    $prestamo = $stmtVerificar->fetch();

    if($prestamo){

        // Volver disponible
        $sql = "UPDATE libros
                SET disponible = 1
                WHERE id = :id";

        $stmt = $con->prepare($sql);

        $stmt->execute([
            ':id' => $libro_id
        ]);

        // Eliminar préstamo
        $sql2 = "DELETE FROM prestamos
                 WHERE libro_id = :id
                 AND usuario_id = :usuario";

        $stmt2 = $con->prepare($sql2);

        $stmt2->execute([
            ':id' => $libro_id,
            ':usuario' => $usuario_id
        ]);
    }
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
    background: linear-gradient(135deg,#0d6efd,#6610f2,#d63384);
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

.navbar-custom{
    background:rgba(0,0,0,.2);
    backdrop-filter:blur(10px);
}

.sidebar{
    background:rgba(255,255,255,.15);
    backdrop-filter:blur(10px);
    border-radius:20px;
    padding:20px;
}

.card-box{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.2);
}

.btn-custom{
    background:linear-gradient(135deg,#0d6efd,#6610f2);
    color:white;
    border:none;
    border-radius:12px;
}

.form-control{
    border-radius:12px;
}

.table{
    border-radius:15px;
    overflow:hidden;
}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-custom px-4 py-3">

<h3 class="text-white m-0">
<i class="bi bi-book-half"></i>
Biblioteca Digital
</h3>

<div class="d-flex align-items-center gap-3">

<span class="text-white fw-bold">
Hola

<?php
$nombre = $_SESSION['nombre'] ?? "Invitado";
$titulo = "Bienvenido";

// detectar primera vez usando SESSION, no cookie
if (!isset($_SESSION['ya_entro'])) {
    $titulo = "Bienvenido";
    $_SESSION['ya_entro'] = true;
} else {
    $titulo = "Bienvenido otra vez";
}
?>

<span class="text-white fw-bold">
👋 <?php echo $titulo; ?>, <?php echo htmlspecialchars($nombre); ?>
</span>

<a href="logout.php" class="btn btn-light">
Salir
</a>

</div>

</nav>

<div class="container-fluid py-4">

<div class="row">

<!-- SIDEBAR -->

<div class="col-md-3 mb-4">

<div class="sidebar text-center">

<h4 class="text-white mb-4">
Panel Biblioteca
</h4>

<div class="d-grid gap-3">

<button class="btn btn-primary"
onclick="mostrarSeccion('libros')">

📚 Libros

</button>

<button class="btn btn-success"
onclick="mostrarSeccion('autores')">

✍ Autores

</button>

<button class="btn btn-warning text-white"
onclick="mostrarSeccion('prestamos')">

🔄 Préstamos

</button>

</div>

<hr class="text-white">

<p class="text-white">
Bienvenid@ a tu biblioteca digital
</p>

</div>

</div>

<!-- CONTENIDO -->

<div class="col-md-9">

<!-- AUTORES -->

<div id="autores" class="card-box mb-4">

<h4 class="mb-3">
Agregar Autor
</h4>

<form method="POST">

<input
type="text"
name="nombre_autor"
class="form-control mb-3"
placeholder="Nombre del autor"
required>

<button
name="guardar_autor"
class="btn btn-success w-100">

Guardar Autor

</button>

</form>

</div>

<!-- LIBROS -->

<div id="libros" class="card-box mb-4">

<h4 class="mb-3">
Agregar Libro
</h4>

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

<hr>

<h4 class="mb-3">
Libros Registrados
</h4>

<table class="table table-hover">

<thead class="table-primary">

<tr>
<th>ID</th>
<th>Título</th>
<th>Autor</th>
<th>Estado</th>
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

<?php if($l['disponible'] == 1){ ?>

<span class="badge bg-success">
Disponible
</span>

<?php } else { ?>

<span class="badge bg-danger">
Prestado
</span>

<?php } ?>

</td>

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

<!-- PRESTAMOS -->

<div id="prestamos" class="card-box">

<h4 class="mb-3">
Registrar Préstamo
</h4>

<form method="POST" class="row g-3">

<div class="col-md-8">

<select
name="libro_id"
class="form-control"
required>

<option value="">
Selecciona libro
</option>

<?php

$libros = $con->query("
SELECT * FROM libros
WHERE disponible = 1
");

foreach($libros as $libro){

echo "<option value='".$libro['id']."'>".$libro['titulo']."</option>";

}

?>

</select>

</div>

<div class="col-md-4">

<button
name="guardar_prestamo"
class="btn btn-warning text-white w-100">

Prestar Libro

</button>

</div>

</form>

<hr>

<h5 class="mb-3">
Historial de Préstamos
</h5>

<table class="table table-striped">

<thead class="table-success">

<tr>
<th>Libro</th>
<th>Usuario</th>
<th>Fecha</th>
<th>Acción</th>
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

<td><?php echo $p['titulo']; ?></td>

<td><?php echo $p['nombre']; ?></td>

<td><?php echo $p['fecha_prestamo']; ?></td>

<td>

<?php if($p['usuario_id'] == $_SESSION['id']){ ?>

<a
href="?devolver=<?php echo $p['libro_id']; ?>"
class="btn btn-success btn-sm">

Devolver

</a>

<?php } else { ?>

<span class="badge bg-secondary">
No disponible
</span>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script>

function mostrarSeccion(id){

document.getElementById('libros').style.display='none';
document.getElementById('autores').style.display='none';
document.getElementById('prestamos').style.display='none';

document.getElementById(id).style.display='block';

}

mostrarSeccion('libros');

</script>

</body>
</html>
