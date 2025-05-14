<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"];

$stmt = $conn->prepare("DELETE FROM evento WHERE id_evento = ?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
?>
