<?php
include '../../conexion.php'; // Incluir archivo de conexión a la base de datos

// Establecer charset para manejar caracteres especiales
$conn->set_charset("utf8mb4");

// Verificar la conexión
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
}

// Consulta para obtener las materias
$sql = "SELECT id_materia, nombre_materia FROM materia ORDER BY nombre_materia";
$result = $conn->query($sql);

$materias = [];

if ($result->num_rows > 0) {
    // Convertir resultados a array
    while($row = $result->fetch_assoc()) {
        $materias[] = $row;
    }
}

// Devolver datos en formato JSON
header('Content-Type: application/json');
echo json_encode($materias);

$conn->close();
?>