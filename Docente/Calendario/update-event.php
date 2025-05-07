<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"];
$titulo = $data["title"];
$descripcion = $data["description"];
$fecha = explode("T", $data["start"])[0];
$hora = explode("T", $data["start"])[1];

$stmt = $conn->prepare("UPDATE evento SET fecha_evento = ?, hora_evento = ?, tipo_evento = ?, asignacion_evento = ? WHERE id_evento = ?");
$stmt->execute([$fecha, $hora, $titulo, $descripcion, $id]);

echo json_encode(["success" => true]);
?>
