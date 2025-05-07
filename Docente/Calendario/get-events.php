<?php
include "db.php";

$stmt = $conn->query("SELECT * FROM evento");
$eventos = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fecha = $row["fecha_evento"];
    $hora = $row["hora_evento"];
    $start = $fecha . "T" . $hora;

    $eventos[] = [
        "id" => $row["id_evento"],
        "title" => $row["tipo_evento"],
        "start" => $start,
        "description" => $row["asignacion_evento"],
        "backgroundColor" => "#3eb489"
    ];
}

echo json_encode($eventos);
?>
