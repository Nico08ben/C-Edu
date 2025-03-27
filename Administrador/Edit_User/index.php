<?php
include 'conexion.php'; // Conexión a la base de datos

$sql = "SELECT * FROM usuario";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="styles.css"> <!-- Asegúrate de tener un CSS adecuado -->
</head>
<body>
    <h2>Lista de Usuarios</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Institución</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $fila["id_usuario"] . "</td>";
                    echo "<td>" . $fila["email_usuario"] . "</td>";
                    echo "<td>" . $fila["nombre_usuario"] . "</td>";
                    echo "<td>" . ($fila["telefono_usuario"] ? $fila["telefono_usuario"] : "N/A") . "</td>";
                    echo "<td>" . ($fila["id_institucion"] ? $fila["id_institucion"] : "N/A") . "</td>";
                    echo "<td>" . ($fila["id_rol"] == 1 ? "Docente" : "Administrador") . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay usuarios registrados</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
