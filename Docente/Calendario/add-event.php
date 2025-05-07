<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$fecha = explode("T", $data["start"])[0];
$hora = explode("T", $data["start"])[1];
$titulo = $data["title"];
$descripcion = $data["description"];
$categoria = "Otro"; // Default
$id_responsable = null;
$enlace = null;

$stmt = $conn->prepare("INSERT INTO evento (fecha_evento, hora_evento, tipo_evento, asignacion_evento, categoria_evento, id_responsable, enlace_recurso) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$fecha, $hora, $titulo, $descripcion, $categoria, $id_responsable, $enlace]);

echo json_encode(["success" => true, "id" => $conn->lastInsertId()]);
?>
