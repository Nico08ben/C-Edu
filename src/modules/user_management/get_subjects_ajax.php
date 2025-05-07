<?php
require_once(__DIR__ . '/../../config/database.php');

header('Content-Type: application/json'); // Ponerlo al principio es una buena práctica

if (!$conn) {
    echo json_encode(['error' => 'Error crítico: No se pudo establecer la conexión a la base de datos.']);
    exit();
}

// Si database.php no establece el charset, puedes hacerlo aquí:
// $conn->set_charset("utf8mb4");

// Consulta para obtener las materias
$sql = "SELECT id_materia, nombre_materia FROM materia ORDER BY nombre_materia";
$result = $conn->query($sql);

$materias = [];

if ($result) { // Verificar si la consulta fue exitosa
    if ($result->num_rows > 0) {
        // Convertir resultados a array
        while($row = $result->fetch_assoc()) {
            $materias[] = $row;
        }
    }
    $result->free(); // Liberar el resultado
} else {
    // Enviar un error si la consulta falla, en lugar de un array vacío sin contexto
    echo json_encode(['error' => 'Error al ejecutar la consulta de materias: ' . $conn->error]);
    $conn->close();
    exit();
}

// Devolver datos en formato JSON
echo json_encode($materias);

$conn->close();
?>