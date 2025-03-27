<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$contact_id = $_POST['contact_id'];
$message = $_POST['message'];

$query = "INSERT INTO mensajes (remitente, destinatario, mensaje, fecha) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $user_id, $contact_id, $message);
$stmt->execute();

echo json_encode(["success" => $stmt->affected_rows > 0]);
?>

