<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$contact_id = $_GET['contact_id'];

$query = "SELECT * FROM mensaje WHERE (id_emisor = ? AND id_receptor = ?) OR (id_emisor = ? AND id_receptor = ?) ORDER BY fecha_mensaje ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $contact_id, $contact_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
